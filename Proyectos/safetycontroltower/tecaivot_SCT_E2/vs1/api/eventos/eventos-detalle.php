<?php
/**
 * GET /api/eventos/eventos-detalle.php?id=7
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireReportarApi($pdo);

$idEvento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idEvento) {
    responderJSON(false, null, 'Parámetro "id" inválido.', 400);
}

try {
    $evento = eventoObtenerPorId($pdo, $idEvento);
    if (!$evento) {
        responderJSON(false, null, 'Evento no encontrado.', 404);
    }

    if (!eventosIsGlobalAdmin($pdo) && (int) $evento['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para ver este evento.', 403);
    }

    $evento['seguimiento'] = trackingListarPorEvento($pdo, $idEvento);
    $evento['evidencias'] = evidenciaListarPorEvento($pdo, $idEvento);

    responderJSON(true, $evento);
} catch (PDOException $e) {
    error_log('api/eventos/eventos-detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener el evento.', 500);
}
