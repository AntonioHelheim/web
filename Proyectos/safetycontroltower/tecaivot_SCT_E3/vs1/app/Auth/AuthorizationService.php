<?php
final class SctAuthorizationService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function rolesForUser(string $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT g.name
             FROM users u
             INNER JOIN users_role ur ON ur.id_users=u.id_users AND ur.state=1
             INNER JOIN users_role_group g ON g.id_role_group=ur.id_role_group AND g.state=1 AND g.id_company=u.id_company
             WHERE u.id_users=:id_users AND u.state=1'
        );
        $stmt->execute(['id_users'=>$userId]);
        $roles=[];
        foreach ($stmt->fetchAll() as $row) {
            $role=SctRolePolicy::canonical((string)($row['name']??''));
            if ($role!=='' && SctRolePolicy::exists($role)) $roles[]=$role;
        }
        return array_values(array_unique($roles));
    }

    public function permissionsDatabaseEnabled(): bool
    {
        // E3-VS1 only treats the DB matrix as authoritative after the migration
        // itself is registered. This avoids switching authorization on from a
        // partially applied permissions seed.
        try {
            $stmt=$this->pdo->prepare(
                'SELECT 1 FROM schema_migrations WHERE version=:version LIMIT 1'
            );
            $stmt->execute(['version'=>'2026-10-07-e3-vs1']);
            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            // Before E3-VS1 the table does not exist: the safe fallback policy is used.
            return false;
        }
    }

    public function capabilitiesForUser(string $userId): array
    {
        $stmt=$this->pdo->prepare(
            'SELECT DISTINCT p.code
             FROM users u
             INNER JOIN users_role ur ON ur.id_users=u.id_users AND ur.state=1
             INNER JOIN users_role_group g ON g.id_role_group=ur.id_role_group AND g.id_company=u.id_company AND g.state=1
             INNER JOIN role_permissions rp ON rp.id_role_group=g.id_role_group
             INNER JOIN permissions p ON p.id_permission=rp.id_permission
             WHERE u.id_users=:id_users AND u.state=1 AND p.code<>:marker'
        );
        $stmt->execute(['id_users'=>$userId,'marker'=>'system.permissions.enabled']);
        $codes=[];
        foreach ($stmt->fetchAll() as $row) {
            $code=trim((string)($row['code']??''));
            if ($code!=='') $codes[]=$code;
        }
        return array_values(array_unique($codes));
    }

    public function hasCapability(string $userId, string $capability): bool
    {
        if ($this->permissionsDatabaseEnabled()) {
            try {
                return in_array($capability,$this->capabilitiesForUser($userId),true);
            } catch (Throwable $e) {
                // Once the DB matrix is enabled authorization fails closed.
                error_log('SctAuthorizationService hasCapability: '.$e->getMessage());
                return false;
            }
        }
        foreach ($this->rolesForUser($userId) as $role) {
            if (SctRolePolicy::fallbackHas($role,$capability)) return true;
        }
        return false;
    }

    public function resolveContext(string $userId): ?SctUserContext
    {
        $stmt=$this->pdo->prepare('SELECT * FROM users WHERE id_users=:id_users AND state=1 LIMIT 1');
        $stmt->execute(['id_users'=>$userId]);
        $profile=$stmt->fetch();
        if (!$profile) return null;
        $roles=$this->rolesForUser($userId);
        $primary=SctRolePolicy::primary($roles);
        if ($primary===null) return null;
        $level=SctRolePolicy::level($primary);
        if ($level===null) return null;
        return new SctUserContext(
            (string)$profile['id_users'],
            (int)$profile['id_company'],
            $roles,
            $primary,
            $level,
            (bool)SctRolePolicy::ROLES[$primary]['global_scope'],
            $profile
        );
    }

    public function userHasActiveRecognizedRole(string $userId): bool
    {
        return count($this->rolesForUser($userId))>0;
    }
}
