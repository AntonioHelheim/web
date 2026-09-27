<?php
/** POST multipart/form-data: id_company, csrf_token, logo */
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
requireCsrfToken($_POST);

$idCompany = filter_var($_POST['id_company'] ?? null, FILTER_VALIDATE_INT);
if (!$idCompany) {
    responderJSON(false, null, 'Empresa no válida.', 400);
}
if (!empresasCanEdit($pdo, (int) $idCompany)) {
    responderJSON(false, null, 'No tienes permisos para modificar el logotipo de esta empresa.', 403);
}

if (empty($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
    responderJSON(false, null, 'Debes seleccionar una imagen.', 400);
}
$archivo = $_FILES['logo'];
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

$extensionesPermitidas = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_WEBP => 'webp',
];
if (!isset($extensionesPermitidas[$infoImagen[2]])) {
    responderJSON(false, null, 'Solo se permiten imágenes JPG, PNG o WEBP.', 400);
}

try {
    if (!empresasLogoColumnAvailable($pdo)) {
        responderJSON(false, null, 'La base de datos del servidor aún no tiene habilitada la columna de logotipo de empresa. Ejecuta la migración correspondiente y vuelve a intentar.', 503);
    }

    $empresa = empresaObtenerPorId($pdo, (int) $idCompany);
    if (!$empresa) {
        responderJSON(false, null, 'Empresa no encontrada.', 404);
    }

    $directorio = __DIR__ . '/../../uploads/empresas';
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        responderJSON(false, null, 'No fue posible preparar el almacenamiento de logotipos.', 500);
    }

    $extension = $extensionesPermitidas[$infoImagen[2]];
    $nombreArchivo = 'company_' . (int) $idCompany . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $rutaDestino = $directorio . '/' . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        responderJSON(false, null, 'No se pudo guardar la imagen.', 500);
    }

    $nuevoPath = 'uploads/empresas/' . $nombreArchivo;
    $anteriorPath = (string) ($empresa['logo_path'] ?? '');

    $stmt = $pdo->prepare('UPDATE company SET logo_path = :logo_path, last_update = NOW() WHERE id_company = :id_company LIMIT 1');
    $stmt->execute(['logo_path' => $nuevoPath, 'id_company' => (int) $idCompany]);

    if ($anteriorPath !== '' && sctStartsWith($anteriorPath, 'uploads/empresas/')) {
        $anterior = __DIR__ . '/../../' . $anteriorPath;
        $realDir = realpath($directorio);
        $realAnterior = is_file($anterior) ? realpath($anterior) : false;
        if ($realDir && $realAnterior && sctStartsWith($realAnterior, $realDir . DIRECTORY_SEPARATOR)) {
            @unlink($realAnterior);
        }
    }

    responderJSON(true, ['logo_path' => $nuevoPath], 'Logotipo actualizado correctamente.');
} catch (Throwable $e) {
    error_log('api/empresas/subir-logo.php: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible actualizar el logotipo.', 500);
}
