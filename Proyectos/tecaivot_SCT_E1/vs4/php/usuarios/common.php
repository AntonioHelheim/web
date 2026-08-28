<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require __DIR__ . '/../../config.php';

const USUARIOS_ROLE_LEVELS = [
    'administrador_completo' => 1,
    'administrador' => 2,
    'cliente' => 3,
    'jefatura' => 4,
    'trabajador' => 5,
];

const USUARIOS_LEVEL_LABELS = [
    1 => 'Administrador Completo',
    2 => 'Administrador por Empresa',
    3 => 'Gerente Empresa',
    4 => 'Jefatura Empresa',
    5 => 'Trabajador',
];

function usuariosJsonResponse(int $httpStatus, bool $ok, string $message = '', ?array $data = null): void
{
    http_response_code($httpStatus);
    header('Content-Type: application/json; charset=utf-8');

    $payload = [
        'ok' => $ok,
    ];

    if ($message !== '') {
        $payload['message'] = $message;
    }

    if ($data !== null) {
        $payload['data'] = $data;
    }

    echo json_encode($payload);
    exit;
}

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
    $csrfToken = (string) ($input['csrf_token'] ?? '');

    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        usuariosJsonResponse(403, false, 'Token CSRF inválido o expirado.');
    }
}

function usuariosRequireAdminContext(PDO $pdo): array
{
    if (empty($_SESSION['logged_in'])) {
        usuariosJsonResponse(401, false, 'Sesión no válida.');
    }

    $sessionUserId = (string) ($_SESSION['user_id'] ?? $_SESSION['user_email'] ?? '');

    if ($sessionUserId === '') {
        usuariosJsonResponse(401, false, 'Sesión no válida.');
    }

    $sql = '
        SELECT
            u.id_users,
            u.id_company,
            MAX(
                CASE
                    WHEN ur.state = 1
                     AND urg.state = 1
                     AND urg.name = :admin_role
                     AND urg.id_company = u.id_company
                    THEN 1
                    ELSE 0
                END
            ) AS is_admin
        FROM users u
        LEFT JOIN users_role ur
            ON ur.id_users = u.id_users
        LEFT JOIN users_role_group urg
            ON urg.id_role_group = ur.id_role_group
        WHERE u.id_users = :user_id
        GROUP BY u.id_users, u.id_company
        LIMIT 1
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'admin_role' => 'administrador',
        'user_id' => $sessionUserId,
    ]);

    $row = $stmt->fetch();

    if (!$row) {
        usuariosJsonResponse(403, false, 'No autorizado.');
    }

    if ((int) ($row['is_admin'] ?? 0) !== 1) {
        usuariosJsonResponse(403, false, 'No tienes permisos para gestionar usuarios.');
    }

    return [
        'session_user_id' => (string) $row['id_users'],
        'company_id' => (int) $row['id_company'],
    ];
}

function usuariosCanonicalRoleKey(string $name): string
{
    $normalized = mb_strtolower(trim($name));
    $normalized = str_replace(['-', ' '], '_', $normalized);

    if ($normalized === 'admin_completo') {
        return 'administrador_completo';
    }

    return $normalized;
}

function usuariosRoleLevelFromName(string $name): ?int
{
    $key = usuariosCanonicalRoleKey($name);
    return USUARIOS_ROLE_LEVELS[$key] ?? null;
}

function usuariosRoleLabelFromLevel(?int $level): string
{
    if ($level === null) {
        return 'Sin nivel';
    }

    return USUARIOS_LEVEL_LABELS[$level] ?? 'Sin nivel';
}

function usuariosAllowedManagedLevels(int $actorLevel): array
{
    if ($actorLevel === 1) {
        return [1, 2, 3, 4, 5];
    }

    if ($actorLevel === 2) {
        return [2, 3, 4, 5];
    }

    if ($actorLevel === 3) {
        return [4, 5];
    }

    if ($actorLevel === 4) {
        return [5];
    }

    return [];
}

function usuariosAllowedAssignableLevels(int $actorLevel): array
{
    return usuariosAllowedManagedLevels($actorLevel);
}

function usuariosCanCreateUsers(array $context): bool
{
    return in_array((int) $context['actor_level'], [1, 2, 3, 4], true);
}

function usuariosCanViewTarget(array $context, array $target): bool
{
    $actorLevel = (int) $context['actor_level'];
    $actorCompanyId = (int) $context['company_id'];
    $targetCompanyId = (int) $target['id_company'];
    $isSelf = (string) $target['id_users'] === (string) $context['session_user_id'];

    if ($actorLevel === 1) {
        return true;
    }

    if ($actorLevel === 5) {
        return $isSelf;
    }

    if ($actorCompanyId !== $targetCompanyId) {
        return false;
    }

    if ($actorLevel === 4) {
        $targetLevel = $target['access_level'] ?? null;
        return $isSelf || $targetLevel === 5;
    }

    return true;
}

function usuariosCanManageTarget(array $context, array $target): bool
{
    $actorLevel = (int) $context['actor_level'];
    $actorCompanyId = (int) $context['company_id'];
    $targetCompanyId = (int) $target['id_company'];
    $targetLevel = $target['access_level'] ?? null;

    if ($actorLevel === 5) {
        return false;
    }

    if ($actorLevel !== 1 && $actorCompanyId !== $targetCompanyId) {
        return false;
    }

    if ($targetLevel === null) {
        return $actorLevel === 1;
    }

    return in_array((int) $targetLevel, usuariosAllowedManagedLevels($actorLevel), true);
}

function usuariosCanAssignLevel(array $context, ?int $targetLevel): bool
{
    if ($targetLevel === null) {
        return false;
    }

    return in_array((int) $targetLevel, usuariosAllowedAssignableLevels((int) $context['actor_level']), true);
}

function usuariosGetEditableFieldsForTarget(array $context, array $target): array
{
    $isSelf = (string) $target['id_users'] === (string) $context['session_user_id'];
    $actorLevel = (int) $context['actor_level'];

    if ($actorLevel === 5 && $isSelf) {
        return ['name', 'lastname', 'language'];
    }

    if (!usuariosCanManageTarget($context, $target)) {
        return [];
    }

    $fields = ['name', 'lastname', 'rut', 'language', 'id_role_group'];

    if ($actorLevel === 1) {
        $fields[] = 'id_company';
    }

    return $fields;
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
    $actorLevel = (int) $context['actor_level'];
    $companyId = (int) $context['company_id'];

    if ($actorLevel === 1) {
        $stmt = $pdo->query('SELECT id_company, razon_social FROM company WHERE state = 1 ORDER BY razon_social ASC');
        $rows = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare('SELECT id_company, razon_social FROM company WHERE id_company = :id_company LIMIT 1');
        $stmt->execute(['id_company' => $companyId]);
        $rows = $stmt->fetchAll();
    }

    return array_map(static function (array $row): array {
        return [
            'id_company' => (int) $row['id_company'],
            'razon_social' => (string) $row['razon_social'],
        ];
    }, $rows);
}

function usuariosResolveAccessLevelFromRoles(array $roleNames): ?int
{
    $levels = [];

    foreach ($roleNames as $roleName) {
        $level = usuariosRoleLevelFromName((string) $roleName);
        if ($level !== null) {
            $levels[] = $level;
        }
    }

    if (count($levels) === 0) {
        return null;
    }

    sort($levels, SORT_NUMERIC);
    return (int) $levels[0];
}

function usuariosRequireAccessContext(PDO $pdo): array
{
    if (empty($_SESSION['logged_in'])) {
        usuariosJsonResponse(401, false, 'Sesión no válida.');
    }

    $sessionUserId = (string) ($_SESSION['user_id'] ?? $_SESSION['user_email'] ?? '');

    if ($sessionUserId === '') {
        usuariosJsonResponse(401, false, 'Sesión no válida.');
    }

    $sql = '
        SELECT
            u.id_users,
            u.id_company,
            u.state,
            COALESCE(
                NULLIF(
                    GROUP_CONCAT(
                        DISTINCT CASE
                            WHEN ur.state = 1
                             AND urg.state = 1
                             AND urg.id_company = u.id_company
                            THEN urg.name
                        END
                        ORDER BY urg.name
                        SEPARATOR ","
                    ),
                    ""
                ),
                ""
            ) AS active_roles
        FROM users u
        LEFT JOIN users_role ur
            ON ur.id_users = u.id_users
        LEFT JOIN users_role_group urg
            ON urg.id_role_group = ur.id_role_group
        WHERE u.id_users = :id_users
          AND u.state = 1
        GROUP BY u.id_users, u.id_company, u.state
        LIMIT 1
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_users' => $sessionUserId]);
    $row = $stmt->fetch();

    if (!$row) {
        usuariosJsonResponse(403, false, 'No autorizado.');
    }

    $roleNames = [];
    $rawRoles = trim((string) ($row['active_roles'] ?? ''));
    if ($rawRoles !== '') {
        $roleNames = array_values(array_filter(array_map('trim', explode(',', $rawRoles)), static function (string $name): bool {
            return $name !== '';
        }));
    }

    $actorLevel = usuariosResolveAccessLevelFromRoles($roleNames);

    if ($actorLevel === null || !in_array($actorLevel, [1, 2, 3, 4, 5], true)) {
        usuariosJsonResponse(403, false, 'No tienes permisos para gestionar usuarios.');
    }

    return [
        'session_user_id' => (string) $row['id_users'],
        'company_id' => (int) $row['id_company'],
        'actor_level' => $actorLevel,
        'actor_label' => usuariosRoleLabelFromLevel($actorLevel),
        'actor_roles' => $roleNames,
        'is_global_admin' => $actorLevel === 1,
        'can_create_user' => usuariosCanCreateUsers(['actor_level' => $actorLevel]),
    ];
}

function usuariosFindAssignableRoles(PDO $pdo, array $context, ?int $companyId = null): array
{
    $actorLevel = (int) $context['actor_level'];
    $allowedLevels = usuariosAllowedAssignableLevels($actorLevel);

    if (count($allowedLevels) === 0) {
        return [];
    }

    $params = [];
    $where = ['urg.state = 1'];

    if ($actorLevel !== 1) {
        $where[] = 'urg.id_company = :id_company';
        $params['id_company'] = (int) $context['company_id'];
    } elseif ($companyId !== null) {
        $where[] = 'urg.id_company = :id_company';
        $params['id_company'] = (int) $companyId;
    }

    $sql = '
        SELECT urg.id_role_group, urg.id_company, urg.name, urg.description
        FROM users_role_group urg
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY urg.id_company ASC, urg.name ASC
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $level = usuariosRoleLevelFromName((string) $row['name']);
        if ($level === null || !in_array($level, $allowedLevels, true)) {
            continue;
        }

        $result[] = [
            'id_role_group' => (int) $row['id_role_group'],
            'id_company' => (int) $row['id_company'],
            'name' => (string) $row['name'],
            'description' => (string) ($row['description'] ?? ''),
            'access_level' => $level,
            'access_label' => usuariosRoleLabelFromLevel($level),
        ];
    }

    return $result;
}

function usuariosFindActiveRole(PDO $pdo, int $companyId, int $roleGroupId): ?array
{
    $sql = '
                SELECT id_role_group, id_company, name, description
        FROM users_role_group
        WHERE id_role_group = :id_role_group
          AND id_company = :id_company
          AND state = 1
        LIMIT 1
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id_role_group' => $roleGroupId,
        'id_company' => $companyId,
    ]);

    $row = $stmt->fetch();
    return $row ?: null;
}

function usuariosFindUserInCompany(PDO $pdo, string $userId, int $companyId): ?array
{
    $sql = '
        SELECT
            u.id_users,
            u.id_company,
            u.id_worker,
            u.name,
            u.lastname,
            u.rut,
            u.state,
            u.language,
            u.last_access,
            u.created_by,
            u.date_create,
            u.last_update,
            c.razon_social,
            COALESCE(
                NULLIF(
                    GROUP_CONCAT(
                        DISTINCT CASE
                            WHEN ur.state = 1
                             AND urg.state = 1
                             AND urg.id_company = u.id_company
                            THEN urg.name
                        END
                        ORDER BY urg.name
                        SEPARATOR ", "
                    ),
                    ""
                ),
                "Sin rol"
            ) AS role_name,
            MIN(
                CASE
                    WHEN ur.state = 1
                     AND urg.state = 1
                     AND urg.id_company = u.id_company
                    THEN ur.id_role_group
                    ELSE NULL
                END
            ) AS primary_role_id
        FROM users u
        INNER JOIN company c
            ON c.id_company = u.id_company
        LEFT JOIN users_role ur
            ON ur.id_users = u.id_users
        LEFT JOIN users_role_group urg
            ON urg.id_role_group = ur.id_role_group
        WHERE u.id_users = :id_users
          AND u.id_company = :id_company
        GROUP BY
            u.id_users,
            u.id_company,
            u.id_worker,
            u.name,
            u.lastname,
            u.rut,
            u.state,
            u.language,
            u.last_access,
            u.created_by,
            u.date_create,
            u.last_update,
            c.razon_social
        LIMIT 1
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id_users' => $userId,
        'id_company' => $companyId,
    ]);

    $row = $stmt->fetch();
    return $row ?: null;
}

function usuariosFindUserWithAccess(PDO $pdo, string $userId): ?array
{
    $sql = '
        SELECT
            u.id_users,
            u.id_company,
            u.id_worker,
            u.name,
            u.lastname,
            u.rut,
            u.state,
            u.language,
            u.last_access,
            u.created_by,
            u.date_create,
            u.last_update,
            c.razon_social,
            COALESCE(
                NULLIF(
                    GROUP_CONCAT(
                        DISTINCT CASE
                            WHEN ur.state = 1
                             AND urg.state = 1
                             AND urg.id_company = u.id_company
                            THEN urg.name
                        END
                        ORDER BY urg.name
                        SEPARATOR ", "
                    ),
                    ""
                ),
                "Sin rol"
            ) AS role_name,
            MIN(
                CASE
                    WHEN ur.state = 1
                     AND urg.state = 1
                     AND urg.id_company = u.id_company
                    THEN ur.id_role_group
                    ELSE NULL
                END
            ) AS primary_role_id
        FROM users u
        INNER JOIN company c
            ON c.id_company = u.id_company
        LEFT JOIN users_role ur
            ON ur.id_users = u.id_users
        LEFT JOIN users_role_group urg
            ON urg.id_role_group = ur.id_role_group
        WHERE u.id_users = :id_users
        GROUP BY
            u.id_users,
            u.id_company,
            u.id_worker,
            u.name,
            u.lastname,
            u.rut,
            u.state,
            u.language,
            u.last_access,
            u.created_by,
            u.date_create,
            u.last_update,
            c.razon_social
        LIMIT 1
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_users' => $userId]);

    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $roleNames = [];
    $rawRoleName = trim((string) ($row['role_name'] ?? ''));
    if ($rawRoleName !== '' && $rawRoleName !== 'Sin rol') {
        $roleNames = array_values(array_filter(array_map('trim', explode(',', $rawRoleName)), static function (string $item): bool {
            return $item !== '';
        }));
    }

    $level = usuariosResolveAccessLevelFromRoles($roleNames);

    $row['access_level'] = $level;
    $row['access_label'] = usuariosRoleLabelFromLevel($level);

    return $row;
}
