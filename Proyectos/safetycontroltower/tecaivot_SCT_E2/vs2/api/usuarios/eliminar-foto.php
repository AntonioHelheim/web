<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);
$idUsers = strtolower(trim((string) ($input['id_users'] ?? currentUserId() ?? '')));
if (!filter_var($idUsers, FILTER_VALIDATE_EMAIL)) {
    responderJSON(false, null, 'Usuario objetivo inválido.', 400);
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $targetUser = usuariosFindUserWithAccess($pdo, $idUsers);
    if (!$targetUser) {
        responderJSON(false, null, 'Usuario no encontrado.', 404);
    }

    $target = [
        'id_users' => $idUsers,
        'id_company' => (int) $targetUser['id_company'],
        'access_level' => $targetUser['access_level'],
    ];
    if (!in_array('profile_photo_path', authEditableUserFields($context, $target), true)) {
        responderJSON(false, null, 'No tienes permisos para modificar la fotografía de este usuario.', 403);
    }

    $path = (string) ($targetUser['profile_photo_path'] ?? '');
    $stmt = $pdo->prepare('UPDATE users SET profile_photo_path = NULL, last_update = NOW() WHERE id_users = :id_users LIMIT 1');
    $stmt->execute(['id_users' => $idUsers]);
    auditTrailLogAction($pdo, (int)$targetUser['id_company'], 'usuarios', 'users', $idUsers, 'profile_photo_path',
        $path, null, 'remove', trim((string)$targetUser['name'].' '.(string)$targetUser['lastname']));

    if ($path !== '' && strpos($path, 'uploads/usuarios/') === 0) {
        $directorio = __DIR__ . '/../../uploads/usuarios';
        $archivo = __DIR__ . '/../../' . $path;
        $realDir = realpath($directorio);
        $realFile = is_file($archivo) ? realpath($archivo) : false;
        if ($realDir && $realFile && strpos($realFile, $realDir . DIRECTORY_SEPARATOR) === 0) {
            @unlink($realFile);
        }
    }

    responderJSON(true, null, 'Fotografía eliminada correctamente.');
} catch (Throwable $e) {
    error_log('api/usuarios/eliminar-foto.php: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible eliminar la fotografía.', 500);
}
