<?php
/**
 * POST /api/trabajadores/subir-foto.php  (multipart/form-data)
 * Campos: id_worker, csrf_token, foto (archivo)
 *
 * Va separado de crear.php/editar.php (que son JSON) porque subir un
 * archivo requiere multipart/form-data, no tiene sentido mezclar los
 * dos formatos en el mismo endpoint.
 *
 * Validaciones de seguridad:
 * - Tamaño máximo 3 MB.
 * - Se verifica que el archivo sea una imagen real con getimagesize()
 *   (no alcanza con mirar la extensión: un .php renombrado a .jpg no
 *   pasa esta validación).
 * - Nombre de archivo regenerado (no se usa el nombre original del
 *   usuario) para evitar path traversal y colisiones.
 * - La carpeta uploads/trabajadores/ tiene su propio .htaccess que
 *   bloquea la ejecución de PHP ahí adentro, como defensa adicional
 *   por si algún archivo igual se cuela.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/TrabajadorRepository.php';

trabajadoresRequireGestionApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

$idWorker = filter_var($_POST['id_worker'] ?? null, FILTER_VALIDATE_INT);
if (!$idWorker) {
    responderJSON(false, null, 'Trabajador no válido.', 400);
}

if (empty($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
    responderJSON(false, null, 'Debes seleccionar una imagen.', 400);
}

$archivo = $_FILES['foto'];

if ($archivo['error'] !== UPLOAD_ERR_OK) {
    responderJSON(false, null, 'Hubo un problema al subir el archivo.', 400);
}

const TAMANO_MAXIMO_FOTO = 3 * 1024 * 1024; // 3 MB
if ($archivo['size'] > TAMANO_MAXIMO_FOTO) {
    responderJSON(false, null, 'La imagen no puede superar los 3 MB.', 400);
}

$infoImagen = @getimagesize($archivo['tmp_name']);
if ($infoImagen === false) {
    responderJSON(false, null, 'El archivo no es una imagen válida.', 400);
}

$extensionesPermitidas = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_WEBP => 'webp',
];

if (!isset($extensionesPermitidas[$infoImagen[2]])) {
    responderJSON(false, null, 'Solo se permiten imágenes JPG, PNG o WEBP.', 400);
}

$extension = $extensionesPermitidas[$infoImagen[2]];

try {
    $trabajador = trabajadorObtenerPorId($pdo, $idWorker);
    if (!$trabajador) {
        responderJSON(false, null, 'Trabajador no encontrado.', 404);
    }

    if (!trabajadoresIsGlobalAdmin($pdo) && (int) $trabajador['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este trabajador.', 403);
    }

    $directorioUploads = __DIR__ . '/../../uploads/trabajadores';
    if (!is_dir($directorioUploads)) {
        mkdir($directorioUploads, 0755, true);
    }

    // Nombre no adivinable: id_worker + token aleatorio, nunca el nombre
    // original del archivo que mandó el navegador.
    $nombreArchivo = 'worker_' . $idWorker . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $rutaDestino = $directorioUploads . '/' . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        error_log('api/trabajadores/subir-foto.php: move_uploaded_file falló para worker ' . $idWorker);
        responderJSON(false, null, 'No se pudo guardar la imagen.', 500);
    }

    $photoPathNuevo = 'uploads/trabajadores/' . $nombreArchivo;

    // Borra la foto anterior si existía, para no dejar archivos huérfanos
    // acumulándose en el servidor.
    if (!empty($trabajador['photo_path'])) {
        $rutaAnterior = __DIR__ . '/../../' . $trabajador['photo_path'];
        if (is_file($rutaAnterior) && strpos(realpath($rutaAnterior) ?: '', realpath($directorioUploads)) === 0) {
            @unlink($rutaAnterior);
        }
    }

    trabajadorActualizarFoto($pdo, $idWorker, $photoPathNuevo);

    responderJSON(true, ['photo_path' => $photoPathNuevo], 'Foto actualizada correctamente.');
} catch (PDOException $e) {
    error_log('api/trabajadores/subir-foto.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar la foto.', 500);
}
