<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);

$idUsers = strtolower(trim((string) ($input['id_users'] ?? '')));
$newState = isset($input['state']) ? (int) $input['state'] : -1;

if ($idUsers === '' || !filter_var($idUsers, FILTER_VALIDATE_EMAIL)) {
    usuariosJsonResponse(400, false, 'Usuario objetivo inválido.');
}

if ($newState !== 0 && $newState !== 1) {
    usuariosJsonResponse(400, false, 'Estado inválido.');
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $targetUser = usuariosFindUserWithAccess($pdo, $idUsers);

    if (!$targetUser) {
        usuariosJsonResponse(404, false, 'Usuario no encontrado.');
    }

    $target = [
        'id_users' => (string) $targetUser['id_users'],
        'id_company' => (int) $targetUser['id_company'],
        'access_level' => $targetUser['access_level'],
    ];

    if ($idUsers === $context['session_user_id']) {
        usuariosJsonResponse(400, false, 'No puedes cambiar el estado de tu propia cuenta desde esta interfaz.');
    }

    if (!usuariosCanManageTarget($context, $target)) {
        usuariosJsonResponse(403, false, 'No tienes permisos para cambiar estado de este usuario.');
    }

    $updateSql = '
        UPDATE users
        SET state = :state,
            last_update = NOW()
        WHERE id_users = :id_users
        LIMIT 1
    ';

    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([
        'state' => $newState,
        'id_users' => $idUsers,
    ]);

    usuariosJsonResponse(200, true, $newState === 1 ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
} catch (PDOException $e) {
    error_log('php/usuarios/cambiar-estado.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al cambiar estado del usuario.');
}
