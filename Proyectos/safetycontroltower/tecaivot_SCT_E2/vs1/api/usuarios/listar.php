<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $actorLevel = (int) $context['actor_level'];

    $search = trim((string) ($_GET['q'] ?? ''));
    $filterStateRaw = (string) ($_GET['state'] ?? 'all');
    $filterCompanyId = isset($_GET['id_company']) ? (int) $_GET['id_company'] : 0;
    $filterAccessLevel = isset($_GET['access_level']) ? (int) $_GET['access_level'] : 0;

    $params = [];
    $where = ['1=1'];

    if ($actorLevel === 1) {
        if ($filterCompanyId > 0) {
            $where[] = 'u.id_company = :id_company';
            $params['id_company'] = $filterCompanyId;
        }
    } else {
        $where[] = 'u.id_company = :id_company';
        $params['id_company'] = (int) $context['company_id'];
    }

    if ($filterStateRaw === '0' || $filterStateRaw === '1') {
        $where[] = 'u.state = :state';
        $params['state'] = (int) $filterStateRaw;
    }

    if ($search !== '') {
        $where[] = '(
            u.id_users LIKE :search
            OR u.name LIKE :search
            OR u.lastname LIKE :search
            OR c.razon_social LIKE :search
            OR urg.name LIKE :search
        )';
        $params['search'] = '%' . $search . '%';
    }

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
        WHERE ' . implode(' AND ', $where) . '
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
    $listadoStmt->execute($params);

    $rows = $listadoStmt->fetchAll();

    $data = [];
    foreach ($rows as $row) {
        $roleNames = [];
        $rawRoleName = trim((string) ($row['role_name'] ?? ''));
        if ($rawRoleName !== '' && $rawRoleName !== 'Sin rol') {
            $roleNames = array_values(array_filter(array_map('trim', explode(',', $rawRoleName)), static function (string $item): bool {
                return $item !== '';
            }));
        }

        $accessLevel = usuariosResolveAccessLevelFromRoles($roleNames);

        if ($filterAccessLevel > 0 && $accessLevel !== $filterAccessLevel) {
            continue;
        }

        $target = [
            'id_users' => (string) $row['id_users'],
            'id_company' => (int) $row['id_company'],
            'access_level' => $accessLevel,
        ];

        if (!usuariosCanViewTarget($context, $target)) {
            continue;
        }

        $editableFields = usuariosGetEditableFieldsForTarget($context, $target);
        $canManageTarget = usuariosCanManageTarget($context, $target);

        $data[] = [
            'id_users' => (string) $row['id_users'],
            'name' => (string) $row['name'],
            'lastname' => (string) $row['lastname'],
            'id_company' => (int) $row['id_company'],
            'razon_social' => (string) $row['razon_social'],
            'state' => (int) $row['state'],
            'last_access' => $row['last_access'],
            'role_name' => (string) $row['role_name'],
            'access_level' => $accessLevel,
            'access_label' => usuariosRoleLabelFromLevel($accessLevel),
            'can_edit' => count($editableFields) > 0,
            'can_change_state' => $canManageTarget && ((string) $row['id_users'] !== (string) $context['session_user_id']),
        ];
    }

    usuariosJsonResponse(200, true, '', [
        'users' => $data,
    ]);
} catch (PDOException $e) {
    error_log('api/usuarios/listar.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al cargar usuarios.');
}
