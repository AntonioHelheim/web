<?php
/**
 * POST /api/eventos/evidencia-eliminar.php
 * Body JSON: { id_evidence, csrf_token }
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireGestionApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$csrfToken = (string) ($input['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

$idEvidencia = filter_var($input['id_evidence'] ?? null, FILTER_VALIDATE_INT);
if (!$idEvidencia) {
    responderJSON(false, null, 'Evidencia no válida.', 400);
}

try {
    $evidencia = evidenciaObtenerPorId($pdo, $idEvidencia);
    if (!$evidencia) {
        responderJSON(false, null, 'Evidencia no encontrada.', 404);
    }

    $evento = eventoObtenerPorId($pdo, (int) $evidencia['id_security_events']);
    if (!$evento || (!eventosIsGlobalAdmin($pdo) && (int) $evento['id_company'] !== currentUserCompanyId($pdo))) {
        responderJSON(false, null, 'No tienes permisos para modificar este evento.', 403);
    }

    $directorioUploads = realpath(__DIR__ . '/../../uploads/eventos');
    $rutaArchivo = __DIR__ . '/../../' . $evidencia['file_path'];
    if (is_file($rutaArchivo) && $directorioUploads && strpos(realpath($rutaArchivo) ?: '', $directorioUploads) === 0) {
        @unlink($rutaArchivo);
    }

    evidenciaEliminar($pdo, $idEvidencia);

    responderJSON(true, null, 'Evidencia eliminada correctamente.');
} catch (PDOException $e) {
    error_log('api/eventos/evidencia-eliminar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo eliminar la evidencia.', 500);
}
