<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);
$idUsers = strtolower(trim((string) ($input['id_users'] ?? '')));
$newState = isset($input['state']) ? (int) $input['state'] : -1;

if (!filter_var($idUsers, FILTER_VALIDATE_EMAIL) || $newState < 0 || $newState > 1) {
    responderJSON(false, null, 'Datos de usuario o estado inválidos.', 400);
}

try {
    $context = usuariosRequireAccessContext($pdo);
    if (empty($context['can_state_users'])) responderJSON(false, null, 'No tienes permisos para cambiar estados de usuarios.', 403);
    $targetUser = usuariosFindUserWithAccess($pdo, $idUsers);
    if (!$targetUser) {
        responderJSON(false, null, 'Usuario no encontrado.', 404);
    }
    if ($idUsers === (string) $context['session_user_id']) {
        responderJSON(false, null, 'No puedes cambiar el estado de tu propia cuenta.', 400);
    }

    $target = [
        'id_users' => $idUsers,
        'id_company' => (int) $targetUser['id_company'],
        'access_level' => $targetUser['access_level'],
    ];
    if (!authCanManageUserTarget($context, $target)) {
        responderJSON(false, null, 'No tienes permisos para cambiar el estado de este usuario.', 403);
    }

    $stmt = $pdo->prepare('UPDATE users SET state = :state, last_update = NOW() WHERE id_users = :id_users LIMIT 1');
    $stmt->execute(['state' => $newState, 'id_users' => $idUsers]);
    auditTrailLogAction($pdo, (int)$targetUser['id_company'], 'usuarios', 'users', $idUsers, 'state',
        $targetUser['state'] ?? null, $newState, 'state_change', trim((string)$targetUser['name'].' '.(string)$targetUser['lastname']),
        (string)$context['session_user_id']);
    responderJSON(true, ['state' => $newState], $newState === 1 ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
} catch (PDOException $e) {
    error_log('api/usuarios/cambiar-estado.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al cambiar el estado del usuario.', 500);
}
