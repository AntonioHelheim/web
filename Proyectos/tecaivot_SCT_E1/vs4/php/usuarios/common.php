<?php

require __DIR__ . '/../../session_bootstrap.php';
require __DIR__ . '/../../config.php';

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

function usuariosFindActiveRole(PDO $pdo, int $companyId, int $roleGroupId): ?array
{
    $sql = '
        SELECT id_role_group, name
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
