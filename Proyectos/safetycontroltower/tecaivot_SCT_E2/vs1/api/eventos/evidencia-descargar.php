<?php
/**
 * GET /api/eventos/evidencia-descargar.php?id_evidence=7
 *
 * A diferencia de la foto de trabajadores (pública dentro de la carpeta
 * protegida contra ejecución de PHP), acá la evidencia puede incluir
 * fotos sensibles de un incidente — se gatea por sesión igual que un
 * certificado, no basta con un nombre de archivo no adivinable.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireReportarApi($pdo);

$idEvidencia = filter_input(INPUT_GET, 'id_evidence', FILTER_VALIDATE_INT);
if (!$idEvidencia) {
    responderJSON(false, null, 'Parámetro "id_evidence" inválido.', 400);
}

try {
    $evidencia = evidenciaObtenerPorId($pdo, $idEvidencia);
    if (!$evidencia) {
        responderJSON(false, null, 'Evidencia no encontrada.', 404);
    }

    $evento = eventoObtenerPorId($pdo, (int) $evidencia['id_security_events']);
    if (!$evento || (!eventosIsGlobalAdmin($pdo) && (int) $evento['id_company'] !== currentUserCompanyId($pdo))) {
        responderJSON(false, null, 'No tienes permisos para ver esta evidencia.', 403);
    }

    $rutaArchivo = __DIR__ . '/../../' . $evidencia['file_path'];
    if (!is_file($rutaArchivo)) {
        error_log('evidencia-descargar.php: archivo no encontrado en disco: ' . $rutaArchivo);
        responderJSON(false, null, 'El archivo no está disponible.', 500);
    }

    $mime = $evidencia['file_type'] === 'documento' ? 'application/pdf' : mime_content_type($rutaArchivo);

    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . basename($evidencia['file_path']) . '"');
    header('Content-Length: ' . filesize($rutaArchivo));
    readfile($rutaArchivo);
    exit;
} catch (PDOException $e) {
    error_log('api/eventos/evidencia-descargar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener la evidencia.', 500);
}
