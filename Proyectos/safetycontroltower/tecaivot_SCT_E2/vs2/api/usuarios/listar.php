<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

try {
    $context = usuariosRequireAccessContext($pdo);

    $search = trim((string) ($_GET['q'] ?? ''));
    $filterStateRaw = (string) ($_GET['state'] ?? 'all');
    $filterCompanyId = (int) ($_GET['id_company'] ?? 0);
    $filterAccessLevel = (int) ($_GET['access_level'] ?? 0);

    $params = [];
    $where = ['1=1'];

    if (!empty($context['is_global_admin'])) {
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
            OR u.rut LIKE :search
            OR c.razon_social LIKE :search
            OR urg.name LIKE :search
        )';
        $params['search'] = '%' . $search . '%';
    }

    $stmt = $pdo->prepare(
        'SELECT
            u.id_users, u.name, u.lastname, u.id_company, u.rut, u.profile_photo_path,
            c.razon_social, u.state, u.last_access,
            MAX(CASE WHEN uc.credential_status = "active" AND COALESCE(uc.password_hash, "") <> "" THEN 1 ELSE 0 END) AS password_configured,
            COALESCE(MAX(uc.credential_status), "pending_activation") AS credential_status,
            COALESCE(NULLIF(GROUP_CONCAT(
                DISTINCT CASE WHEN ur.state = 1 AND urg.state = 1 AND urg.id_company = u.id_company THEN urg.name END
                ORDER BY urg.name SEPARATOR ", "
            ), ""), "Sin rol") AS role_name
         FROM users u
         INNER JOIN company c ON c.id_company = u.id_company
         LEFT JOIN user_credentials uc ON uc.id_users = u.id_users
         LEFT JOIN users_role ur ON ur.id_users = u.id_users
         LEFT JOIN users_role_group urg ON urg.id_role_group = ur.id_role_group
         WHERE ' . implode(' AND ', $where) . '
         GROUP BY u.id_users, u.name, u.lastname, u.id_company, u.rut, u.profile_photo_path,
                  c.razon_social, u.state, u.last_access
         ORDER BY u.lastname ASC, u.name ASC, u.id_users ASC'
    );
    $stmt->execute($params);

    $data = [];
    foreach ($stmt->fetchAll() as $row) {
        $roleNames = usuariosParseRoleNames((string) $row['role_name']);
        $primaryRole = primaryRoleName($roleNames);
        $accessLevel = $primaryRole ? roleLevelFromName($primaryRole) : null;

        if ($filterAccessLevel > 0 && $accessLevel !== $filterAccessLevel) {
            continue;
        }

        $target = [
            'id_users' => (string) $row['id_users'],
            'id_company' => (int) $row['id_company'],
            'access_level' => $accessLevel,
        ];
        if (!authCanViewUserTarget($context, $target)) {
            continue;
        }

        $editableFields = authEditableUserFields($context, $target);
        $canManage = authCanManageUserTarget($context, $target)
            && currentUserHasAnyCapability($pdo, ['users.edit','users.state','users.access','users.photo']);
        $credentialStatus = (string) ($row['credential_status'] ?? 'pending_activation');

        $data[] = [
            'id_users' => (string) $row['id_users'],
            'name' => (string) $row['name'],
            'lastname' => (string) $row['lastname'],
            'rut' => (string) $row['rut'],
            'profile_photo_path' => $row['profile_photo_path'] ?: null,
            'id_company' => (int) $row['id_company'],
            'razon_social' => (string) $row['razon_social'],
            'state' => (int) $row['state'],
            'last_access' => $row['last_access'],
            'password_configured' => (bool) ((int) $row['password_configured']),
            'credential_status' => $credentialStatus,
            'role_name' => (string) $row['role_name'],
            'role_names' => $roleNames,
            'primary_role' => $primaryRole,
            'access_level' => $accessLevel,
            'access_label' => usuariosRoleLabelFromLevel($accessLevel),
            'can_edit' => count($editableFields) > 0,
            'can_change_state' => $canManage && (string) $row['id_users'] !== (string) $context['session_user_id'],
            'can_send_access' => $canManage && (int) $row['state'] === 1,
        ];
    }

    responderJSON(true, ['users' => $data]);
} catch (PDOException $e) {
    error_log('api/usuarios/listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al cargar usuarios.', 500);
}
