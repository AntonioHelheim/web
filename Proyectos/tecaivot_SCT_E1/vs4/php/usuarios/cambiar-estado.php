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
    $context = usuariosRequireAdminContext($pdo);

    if ($idUsers === $context['session_user_id']) {
        usuariosJsonResponse(400, false, 'No puedes cambiar el estado de tu propia cuenta desde esta interfaz.');
    }

    $targetUser = usuariosFindUserInCompany($pdo, $idUsers, $context['company_id']);

    if (!$targetUser) {
        usuariosJsonResponse(404, false, 'Usuario no encontrado en tu empresa.');
    }

    $updateSql = '
        UPDATE users
        SET state = :state,
            last_update = NOW()
        WHERE id_users = :id_users
          AND id_company = :id_company
        LIMIT 1
    ';

    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([
        'state' => $newState,
        'id_users' => $idUsers,
        'id_company' => $context['company_id'],
    ]);

    usuariosJsonResponse(200, true, $newState === 1 ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
} catch (PDOException $e) {
    error_log('php/usuarios/cambiar-estado.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al cambiar estado del usuario.');
}
