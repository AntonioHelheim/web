<?php
/**
 * POST /api/eventos/evidencia-subir.php  (multipart/form-data)
 * Campos: id_security_events, csrf_token, archivo
 *
 * Mismas validaciones de seguridad que ya se usan para la foto de
 * trabajadores (api/trabajadores/subir-foto.php), extendidas para
 * aceptar también documentos PDF, no solo imágenes:
 * - Tamaño máximo 8 MB (las evidencias de incidentes suelen ser fotos
 *   de mayor resolución que una foto de perfil).
 * - Imágenes: se verifica que sea una imagen real con getimagesize().
 * - PDF: se verifica la firma mágica %PDF al inicio del archivo (no
 *   alcanza con la extensión).
 * - Nombre de archivo regenerado, nunca el original.
 * - uploads/eventos/ tiene su propio .htaccess que bloquea ejecución
 *   de PHP, igual que uploads/trabajadores/ y uploads/certificados/.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireReportarApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

$idEvento = filter_var($_POST['id_security_events'] ?? null, FILTER_VALIDATE_INT);
if (!$idEvento) {
    responderJSON(false, null, 'Evento no válido.', 400);
}

if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) {
    responderJSON(false, null, 'Debes seleccionar un archivo.', 400);
}

$archivo = $_FILES['archivo'];
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    responderJSON(false, null, 'Hubo un problema al subir el archivo.', 400);
}

const TAMANO_MAXIMO_EVIDENCIA = 8 * 1024 * 1024; // 8 MB
if ($archivo['size'] > TAMANO_MAXIMO_EVIDENCIA) {
    responderJSON(false, null, 'El archivo no puede superar los 8 MB.', 400);
}

$extensionesImagen = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
$infoImagen = @getimagesize($archivo['tmp_name']);

$tipoArchivo = null;
$extension = null;

if ($infoImagen !== false && isset($extensionesImagen[$infoImagen[2]])) {
    $tipoArchivo = 'imagen';
    $extension = $extensionesImagen[$infoImagen[2]];
} else {
    // No es una imagen reconocida: ¿es un PDF real? (firma mágica, no
    // la extensión del nombre del archivo).
    $primerosBytes = file_get_contents($archivo['tmp_name'], false, null, 0, 5);
    if ($primerosBytes !== false && strpos($primerosBytes, '%PDF') === 0) {
        $tipoArchivo = 'documento';
        $extension = 'pdf';
    }
}

if ($tipoArchivo === null) {
    responderJSON(false, null, 'Solo se permiten imágenes (JPG, PNG, WEBP) o documentos PDF.', 400);
}

try {
    $evento = eventoObtenerPorId($pdo, $idEvento);
    if (!$evento) {
        responderJSON(false, null, 'Evento no encontrado.', 404);
    }

    if (!eventosIsGlobalAdmin($pdo) && (int) $evento['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este evento.', 403);
    }

    $directorioUploads = __DIR__ . '/../../uploads/eventos';
    if (!is_dir($directorioUploads)) {
        mkdir($directorioUploads, 0755, true);
    }

    $nombreArchivo = 'evento_' . $idEvento . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $rutaDestino = $directorioUploads . '/' . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        error_log('api/eventos/evidencia-subir.php: move_uploaded_file falló para evento ' . $idEvento);
        responderJSON(false, null, 'No se pudo guardar el archivo.', 500);
    }

    $nombreOriginal = mb_substr(basename($archivo['name']), 0, 255);
    $rutaRelativa = 'uploads/eventos/' . $nombreArchivo;

    $idEvidencia = evidenciaCrear($pdo, $idEvento, $rutaRelativa, $nombreOriginal, $tipoArchivo, currentUserId());

    responderJSON(true, ['id_evidence' => $idEvidencia], 'Evidencia subida correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/eventos/evidencia-subir.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo registrar la evidencia.', 500);
}
