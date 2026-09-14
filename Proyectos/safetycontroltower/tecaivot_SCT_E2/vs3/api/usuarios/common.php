<?php
/**
 * Helpers de dominio para Gestión de Usuarios.
 *
 * IMPORTANTE: la autorización ya no vive en este módulo. Todas las reglas
 * de sesión, jerarquía de roles, alcance multiempresa y permisos están en
 * lib/auth.php. Este archivo se limita a lectura de request y consultas de
 * usuarios/roles.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/validation.php';
require_once __DIR__ . '/../../i18n.php';

function usuariosReadJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return $_POST;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

function usuariosRequireCsrf(array $input): void
{
    requireCsrfToken($input);
}

function usuariosRequireAccessContext(PDO $pdo): array
{
    return currentUserAccessContext($pdo);
}

function usuariosRoleLevelFromName(string $name): ?int
{
    return roleLevelFromName($name);
}

function usuariosRoleNameFromLevel(?int $level): ?string
{
    if ($level === null) {
        return null;
    }
    foreach (SCT_ROLE_POLICY as $name => $policy) {
        if ((int) $policy['level'] === $level) {
            return $name;
        }
    }
    return null;
}

function usuariosRoleLabelFromLevel(?int $level): string
{
    $name = usuariosRoleNameFromLevel($level);
    if ($name === null) {
        return t('role_none');
    }
    $key = 'role_' . $name;
    $translated = t($key);
    return $translated === $key ? roleDisplayLabel($name) : $translated;
}

function usuariosResolveAccessLevelFromRoles(array $roleNames): ?int
{
    $primary = primaryRoleName($roleNames);
    return $primary ? roleLevelFromName($primary) : null;
}

function usuariosAllowedManagedLevels(int $actorLevel): array
{
    foreach (SCT_ROLE_POLICY as $policy) {
        if ((int) $policy['level'] === $actorLevel) {
            return $policy['managed_user_levels'];
        }
    }
    return [];
}

function usuariosAllowedAssignableLevels(int $actorLevel): array
{
    foreach (SCT_ROLE_POLICY as $policy) {
        if ((int) $policy['level'] === $actorLevel) {
            return $policy['assignable_user_levels'];
        }
    }
    return [];
}

function usuariosCanCreateUsers(array $context): bool
{
    return authCanCreateUsers($context);
}

function usuariosCanViewTarget(array $context, array $target): bool
{
    return authCanViewUserTarget($context, $target);
}

function usuariosCanManageTarget(array $context, array $target): bool
{
    return authCanManageUserTarget($context, $target);
}

function usuariosCanAssignLevel(array $context, ?int $targetLevel): bool
{
    return authCanAssignUserLevel($context, $targetLevel);
}

function usuariosGetEditableFieldsForTarget(array $context, array $target): array
{
    return authEditableUserFields($context, $target);
}

function usuariosFindCompany(PDO $pdo, int $companyId): ?array
{
    $stmt = $pdo->prepare('SELECT id_company, razon_social, state FROM company WHERE id_company = :id_company LIMIT 1');
    $stmt->execute(['id_company' => $companyId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function usuariosListVisibleCompanies(PDO $pdo, array $context): array
{
    if (!empty($context['is_global_admin'])) {
        $stmt = $pdo->query('SELECT id_company, razon_social FROM company WHERE state = 1 ORDER BY razon_social ASC');
        $rows = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare('SELECT id_company, razon_social FROM company WHERE id_company = :id_company AND state = 1 LIMIT 1');
        $stmt->execute(['id_company' => (int) $context['company_id']]);
        $rows = $stmt->fetchAll();
    }

    return array_map(static function (array $row): array {
        return [
            'id_company' => (int) $row['id_company'],
            'razon_social' => (string) $row['razon_social'],
        ];
    }, $rows);
}

function usuariosFindAssignableRoles(PDO $pdo, array $context, ?int $companyId = null): array
{
    $allowedLevels = authAssignableUserLevels($context);
    if (!$allowedLevels) {
        return [];
    }

    $params = [];
    $where = ['urg.state = 1'];

    if (empty($context['is_global_admin'])) {
        $where[] = 'urg.id_company = :id_company';
        $params['id_company'] = (int) $context['company_id'];
    } elseif ($companyId !== null) {
        $where[] = 'urg.id_company = :id_company';
        $params['id_company'] = $companyId;
    }

    $stmt = $pdo->prepare(
        'SELECT urg.id_role_group, urg.id_company, urg.name, urg.description
         FROM users_role_group urg
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY urg.id_company ASC, urg.name ASC'
    );
    $stmt->execute($params);

    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $roleName = canonicalRoleName((string) $row['name']);
        $level = roleLevelFromName($roleName);
        if ($level === null || !in_array($level, $allowedLevels, true)) {
            continue;
        }
        $result[] = [
            'id_role_group' => (int) $row['id_role_group'],
            'id_company' => (int) $row['id_company'],
            'name' => $roleName,
            'description' => (string) ($row['description'] ?? ''),
            'access_level' => $level,
            'access_label' => usuariosRoleLabelFromLevel($level),
        ];
    }
    return $result;
}

function usuariosFindActiveRole(PDO $pdo, int $companyId, int $roleGroupId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id_role_group, id_company, name, description
         FROM users_role_group
         WHERE id_role_group = :id_role_group
           AND id_company = :id_company
           AND state = 1
         LIMIT 1'
    );
    $stmt->execute(['id_role_group' => $roleGroupId, 'id_company' => $companyId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function usuariosParseRoleNames(string $raw): array
{
    if (trim($raw) === '' || trim($raw) === 'Sin rol') {
        return [];
    }
    $roles = [];
    foreach (explode(',', $raw) as $item) {
        $role = canonicalRoleName(trim($item));
        if ($role !== '') {
            $roles[] = $role;
        }
    }
    return array_values(array_unique($roles));
}

function usuariosFindUserWithAccess(PDO $pdo, string $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT
            u.id_users,
            u.id_company,
            u.id_worker,
            u.name,
            u.lastname,
            u.rut,
            u.state,
            u.language,
            u.profile_photo_path,
            u.last_access,
            u.created_by,
            u.date_create,
            u.last_update,
            c.razon_social,
            MAX(CASE WHEN uc.credential_status = "active" AND COALESCE(uc.password_hash, "") <> "" THEN 1 ELSE 0 END) AS password_configured,
            COALESCE(MAX(uc.credential_status), "pending_activation") AS credential_status,
            COALESCE(
                NULLIF(
                    GROUP_CONCAT(
                        DISTINCT CASE
                            WHEN ur.state = 1 AND urg.state = 1 AND urg.id_company = u.id_company
                            THEN urg.name
                        END
                        ORDER BY urg.name SEPARATOR ", "
                    ),
                    ""
                ),
                "Sin rol"
            ) AS role_name,
            MIN(CASE
                WHEN ur.state = 1 AND urg.state = 1 AND urg.id_company = u.id_company
                THEN ur.id_role_group ELSE NULL END
            ) AS primary_role_id
         FROM users u
         INNER JOIN company c ON c.id_company = u.id_company
         LEFT JOIN user_credentials uc ON uc.id_users = u.id_users
         LEFT JOIN users_role ur ON ur.id_users = u.id_users
         LEFT JOIN users_role_group urg ON urg.id_role_group = ur.id_role_group
         WHERE u.id_users = :id_users
         GROUP BY
            u.id_users, u.id_company, u.id_worker, u.name, u.lastname, u.rut,
            u.state, u.language, u.profile_photo_path, u.last_access, u.created_by,
            u.date_create, u.last_update, c.razon_social
         LIMIT 1'
    );
    $stmt->execute(['id_users' => $userId]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $roles = usuariosParseRoleNames((string) ($row['role_name'] ?? ''));
    $primaryRole = primaryRoleName($roles);
    $level = $primaryRole ? roleLevelFromName($primaryRole) : null;

    $credentialStatus = strtolower((string) ($row['credential_status'] ?? 'pending_activation'));
    if (!in_array($credentialStatus, ['pending_activation', 'reset_required', 'active'], true)) {
        $credentialStatus = 'pending_activation';
    }

    $row['role_names'] = $roles;
    $row['primary_role'] = $primaryRole;
    $row['access_level'] = $level;
    $row['access_label'] = usuariosRoleLabelFromLevel($level);
    $row['password_configured'] = (bool) ((int) ($row['password_configured'] ?? 0));
    $row['credential_status'] = $credentialStatus;
    $row['access_status'] = $credentialStatus;

    return $row;
}
