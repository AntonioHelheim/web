<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

try {
    $context = usuariosRequireAdminContext($pdo);
    $companyId = $context['company_id'];

    $listadoSql = '
        SELECT
            u.id_users,
            u.name,
            u.lastname,
            u.id_company,
            c.razon_social,
            u.state,
            u.last_access,
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
            ) AS role_name
        FROM users u
        INNER JOIN company c
            ON c.id_company = u.id_company
        LEFT JOIN users_role ur
            ON ur.id_users = u.id_users
        LEFT JOIN users_role_group urg
            ON urg.id_role_group = ur.id_role_group
        WHERE u.id_company = :id_company
        GROUP BY
            u.id_users,
            u.name,
            u.lastname,
            u.id_company,
            c.razon_social,
            u.state,
            u.last_access
        ORDER BY u.lastname ASC, u.name ASC, u.id_users ASC
    ';

    $listadoStmt = $pdo->prepare($listadoSql);
    $listadoStmt->execute([
        'id_company' => $companyId,
    ]);

    $rows = $listadoStmt->fetchAll();

    $data = array_map(static function (array $row): array {
        return [
            'id_users' => (string) $row['id_users'],
            'name' => (string) $row['name'],
            'lastname' => (string) $row['lastname'],
            'id_company' => (int) $row['id_company'],
            'razon_social' => (string) $row['razon_social'],
            'state' => (int) $row['state'],
            'last_access' => $row['last_access'],
            'role_name' => (string) $row['role_name'],
        ];
    }, $rows);

    usuariosJsonResponse(200, true, '', $data);
} catch (PDOException $e) {
    error_log('php/usuarios/listar.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al cargar usuarios.');
}
