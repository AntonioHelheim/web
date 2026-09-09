<?php
/** POST multipart/form-data: id_users, csrf_token, foto */
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

usuariosRequireCsrf($_POST);
$idUsers = strtolower(trim((string) ($_POST['id_users'] ?? currentUserId() ?? '')));
if (!filter_var($idUsers, FILTER_VALIDATE_EMAIL) || mb_strlen($idUsers) > 50) {
    responderJSON(false, null, 'Usuario objetivo inválido.', 400);
}

if (empty($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
    responderJSON(false, null, 'Debes seleccionar una imagen.', 400);
}
$archivo = $_FILES['foto'];
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    responderJSON(false, null, 'Hubo un problema al subir la imagen.', 400);
}
if ((int) $archivo['size'] > 3 * 1024 * 1024) {
    responderJSON(false, null, 'La imagen no puede superar los 3 MB.', 400);
}

$infoImagen = @getimagesize($archivo['tmp_name']);
if ($infoImagen === false || ($infoImagen[0] ?? 0) < 32 || ($infoImagen[1] ?? 0) < 32) {
    responderJSON(false, null, 'El archivo no es una imagen válida.', 400);
}
if (($infoImagen[0] ?? 0) > 5000 || ($infoImagen[1] ?? 0) > 5000) {
    responderJSON(false, null, 'La imagen excede las dimensiones permitidas.', 400);
}

$extensionesPermitidas = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
if (!isset($extensionesPermitidas[$infoImagen[2]])) {
    responderJSON(false, null, 'Solo se permiten imágenes JPG, PNG o WEBP.', 400);
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
    $editableFields = authEditableUserFields($context, $target);
    if (!in_array('profile_photo_path', $editableFields, true)) {
        responderJSON(false, null, 'No tienes permisos para modificar la fotografía de este usuario.', 403);
    }

    $directorio = __DIR__ . '/../../uploads/usuarios';
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        responderJSON(false, null, 'No fue posible preparar el almacenamiento de fotografías.', 500);
    }

    $extension = $extensionesPermitidas[$infoImagen[2]];
    $nombreArchivo = 'user_' . hash('sha256', $idUsers) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $rutaDestino = $directorio . '/' . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        responderJSON(false, null, 'No se pudo guardar la imagen.', 500);
    }

    $nuevoPath = 'uploads/usuarios/' . $nombreArchivo;
    $anteriorPath = (string) ($targetUser['profile_photo_path'] ?? '');

    $stmt = $pdo->prepare('UPDATE users SET profile_photo_path = :photo_path, last_update = NOW() WHERE id_users = :id_users LIMIT 1');
    $stmt->execute(['photo_path' => $nuevoPath, 'id_users' => $idUsers]);
    auditTrailLogAction($pdo, (int)$targetUser['id_company'], 'usuarios', 'users', $idUsers, 'profile_photo_path',
        $anteriorPath, $nuevoPath, 'upload', trim((string)$targetUser['name'].' '.(string)$targetUser['lastname']));

    if ($anteriorPath !== '' && str_starts_with($anteriorPath, 'uploads/usuarios/')) {
        $anterior = __DIR__ . '/../../' . $anteriorPath;
        $realDir = realpath($directorio);
        $realAnterior = is_file($anterior) ? realpath($anterior) : false;
        if ($realDir && $realAnterior && str_starts_with($realAnterior, $realDir . DIRECTORY_SEPARATOR)) {
            @unlink($realAnterior);
        }
    }

    responderJSON(true, ['profile_photo_path' => $nuevoPath], 'Fotografía actualizada correctamente.');
} catch (Throwable $e) {
    error_log('api/usuarios/subir-foto.php: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible actualizar la fotografía.', 500);
}
