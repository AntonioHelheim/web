<?php

final class SctAdminManagementRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function schemaReady(): bool
    {
        try {
            $stmt=$this->pdo->query(
                "SELECT COUNT(*)
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA=DATABASE()
                   AND TABLE_NAME='user_management_assignments'"
            );

            return (int)$stmt->fetchColumn()===1;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function gerencias(int $companyId): array
    {
        $stmt=$this->pdo->prepare(
            "SELECT DISTINCT
                u.id_users,
                u.name,
                u.lastname
             FROM users u
             INNER JOIN users_role ur
               ON ur.id_users=u.id_users
              AND ur.state=1
             INNER JOIN users_role_group rg
               ON rg.id_role_group=ur.id_role_group
              AND rg.state=1
             WHERE u.id_company=:id_company
               AND u.state=1
               AND (
                   LOWER(TRIM(rg.name)) LIKE '%gerente%'
                   OR LOWER(TRIM(rg.name)) LIKE '%gerencia%'
                   OR LOWER(TRIM(rg.name))='cliente'
               )
             ORDER BY u.name,u.lastname,u.id_users"
        );
        $stmt->execute(['id_company'=>$companyId]);

        return $stmt->fetchAll() ?: [];
    }

    public function validGerencia(
        int $companyId,
        string $managerId
    ): bool {
        foreach ($this->gerencias($companyId) as $row) {
            if (
                strcasecmp(
                    (string)$row['id_users'],
                    $managerId
                )===0
            ) {
                return true;
            }
        }

        return false;
    }

    public function currentForWorker(
        int $companyId,
        int $workerId
    ): array {
        if (!$this->schemaReady() || $workerId<=0) {
            return [];
        }

        $stmt=$this->pdo->prepare(
            'SELECT
                a.id_manager_users,
                u.name,
                u.lastname
             FROM user_management_assignments a
             INNER JOIN users u
               ON u.id_users=a.id_manager_users
              AND u.id_company=a.id_company
             WHERE a.id_company=:id_company
               AND a.id_worker=:id_worker
               AND a.state=1
             ORDER BY u.name,u.lastname,u.id_users'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_worker'=>$workerId,
        ]);

        return $stmt->fetchAll() ?: [];
    }

    public function replacePrimary(
        int $companyId,
        int $workerId,
        string $managerId,
        string $actor
    ): void {
        if (!$this->validGerencia($companyId,$managerId)) {
            throw new InvalidArgumentException(
                t('admin_users_err_gerencia')
            );
        }

        $stmt=$this->pdo->prepare(
            'UPDATE user_management_assignments
             SET state=0,last_update=NOW()
             WHERE id_company=:id_company
               AND id_worker=:id_worker
               AND state=1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_worker'=>$workerId,
        ]);

        $stmt=$this->pdo->prepare(
            'INSERT INTO user_management_assignments (
                id_company,
                id_manager_users,
                id_worker,
                state,
                created_by,
                date_create,
                last_update
             ) VALUES (
                :id_company,
                :manager,
                :id_worker,
                1,
                :actor,
                NOW(),
                NOW()
             )
             ON DUPLICATE KEY UPDATE
                state=1,
                created_by=VALUES(created_by),
                last_update=NOW()'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'manager'=>$managerId,
            'id_worker'=>$workerId,
            'actor'=>$actor,
        ]);
    }

    public function clearPrimary(
        int $companyId,
        int $workerId
    ): void {
        if (!$this->schemaReady() || $workerId<=0) {
            return;
        }

        $stmt=$this->pdo->prepare(
            'UPDATE user_management_assignments
             SET state=0,last_update=NOW()
             WHERE id_company=:id_company
               AND id_worker=:id_worker
               AND state=1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_worker'=>$workerId,
        ]);
    }
}
