<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

$userId = trim((string) ($_GET['id_users'] ?? ''));

if ($userId === '') {
    usuariosJsonResponse(400, false, 'Debes indicar el usuario a consultar.');
}

if (mb_strlen($userId) > 50) {
    usuariosJsonResponse(400, false, 'Identificador de usuario inválido.');
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $user = usuariosFindUserWithAccess($pdo, $userId);

    if (!$user) {
        usuariosJsonResponse(404, false, 'Usuario no encontrado.');
    }

    $target = [
        'id_users' => (string) $user['id_users'],
        'id_company' => (int) $user['id_company'],
        'access_level' => $user['access_level'],
    ];

    if (!usuariosCanViewTarget($context, $target)) {
        usuariosJsonResponse(403, false, 'No tienes permisos para ver este usuario.');
    }

    $canManageTarget = usuariosCanManageTarget($context, $target);
    $editableFields = usuariosGetEditableFieldsForTarget($context, $target);
    $isSelf = (string) $user['id_users'] === (string) $context['session_user_id'];

    $data = [
        'id_users' => (string) $user['id_users'],
        'name' => (string) $user['name'],
        'lastname' => (string) $user['lastname'],
        'id_company' => (int) $user['id_company'],
        'rut' => (string) $user['rut'],
        'language' => (string) $user['language'],
        'state' => (int) $user['state'],
        'last_access' => $user['last_access'],
        'razon_social' => (string) $user['razon_social'],
        'role_name' => (string) $user['role_name'],
        'primary_role_id' => isset($user['primary_role_id']) ? (int) $user['primary_role_id'] : null,
        'access_level' => $user['access_level'],
        'access_label' => (string) $user['access_label'],
        'permissions' => [
            'is_self' => $isSelf,
            'can_edit' => count($editableFields) > 0,
            'can_change_state' => $canManageTarget && !$isSelf,
            'can_assign_role' => $canManageTarget && ((int) $context['actor_level'] !== 5),
            'editable_fields' => $editableFields,
        ],
    ];

    usuariosJsonResponse(200, true, '', $data);
} catch (PDOException $e) {
    error_log('php/usuarios/obtener.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al obtener usuario.');
}
