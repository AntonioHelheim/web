<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$userId = strtolower(trim((string) ($_GET['id_users'] ?? '')));
if ($userId === '' || sctTextLength($userId) > 50) {
    responderJSON(false, null, 'Debes indicar un usuario válido.', 400);
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $user = usuariosFindUserWithAccess($pdo, $userId);
    if (!$user) {
        responderJSON(false, null, 'Usuario no encontrado.', 404);
    }

    $target = [
        'id_users' => (string) $user['id_users'],
        'id_company' => (int) $user['id_company'],
        'access_level' => $user['access_level'],
    ];
    if (!authCanViewUserTarget($context, $target)) {
        responderJSON(false, null, 'No tienes permisos para ver este usuario.', 403);
    }

    $editableFields = authEditableUserFields($context, $target);
    $isSelf = (string) $user['id_users'] === (string) $context['session_user_id'];
    $canManage = authCanManageUserTarget($context, $target);

    responderJSON(true, [
        'id_users' => (string) $user['id_users'],
        'name' => (string) $user['name'],
        'lastname' => (string) $user['lastname'],
        'id_company' => (int) $user['id_company'],
        'rut' => (string) $user['rut'],
        'language' => (string) $user['language'],
        'profile_photo_path' => $user['profile_photo_path'] ?: null,
        'state' => (int) $user['state'],
        'last_access' => $user['last_access'],
        'password_configured' => (bool) $user['password_configured'],
        'credential_status' => (string) $user['credential_status'],
        'razon_social' => (string) $user['razon_social'],
        'role_name' => (string) $user['role_name'],
        'role_names' => $user['role_names'],
        'primary_role' => $user['primary_role'],
        'primary_role_id' => isset($user['primary_role_id']) ? (int) $user['primary_role_id'] : null,
        'access_level' => $user['access_level'],
        'access_label' => (string) $user['access_label'],
        'permissions' => [
            'is_self' => $isSelf,
            'can_edit' => count($editableFields) > 0,
            'can_change_state' => $canManage && !$isSelf,
            'can_send_access' => $canManage && (int) $user['state'] === 1,
            'can_assign_role' => $canManage && authRoleHasCapability((string) $context['primary_role'], 'users.assign_roles'),
            'can_manage_photo' => in_array('profile_photo_path', $editableFields, true),
            'editable_fields' => $editableFields,
        ],
    ]);
} catch (PDOException $e) {
    error_log('api/usuarios/obtener.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al obtener usuario.', 500);
}
