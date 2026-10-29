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

    $companyFilter = null;
    if (!empty($context['is_global_admin'])) {
        if ($filterCompanyId > 0) $companyFilter = $filterCompanyId;
    } else {
        $companyFilter = (int)$context['company_id'];
    }
    $stateFilter = ($filterStateRaw === '0' || $filterStateRaw === '1') ? (int)$filterStateRaw : null;
    $rows = usuariosRepository($pdo)->listUsers($companyFilter, $stateFilter, $search);

    $data = [];
    foreach ($rows as $row) {
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

        // En la grilla de administración, los perfiles de alcance "assigned"
        // ven exclusivamente relaciones explícitas; su propio perfil se edita
        // desde "Datos de usuario".
        if (
            authUserScopeMode($context) === 'assigned'
            && !authIsAssignedUserTarget($pdo,$context,$target)
        ) {
            continue;
        }

        if (!authCanViewUserTarget($context, $target, $pdo)) {
            continue;
        }

        $editableFields = authEditableUserFields($context, $target, $pdo);
        $canManage = authCanManageUserTarget($context, $target, $pdo)
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
            'can_change_state' => $canManage
                && currentUserHasCapability($pdo,'users.state')
                && (string)$row['id_users'] !== (string)$context['session_user_id'],
            'can_send_access' => $canManage
                && currentUserHasCapability($pdo,'users.access')
                && (int)$row['state'] === 1,
        ];
    }

    responderJSON(true, ['users' => $data]);
} catch (PDOException $e) {
    error_log('api/usuarios/listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al cargar usuarios.', 500);
}
