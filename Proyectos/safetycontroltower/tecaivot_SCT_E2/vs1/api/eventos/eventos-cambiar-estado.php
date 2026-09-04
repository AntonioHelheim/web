<?php
/**
 * POST /api/eventos/eventos-cambiar-estado.php
 * Body JSON: { id_security_events, state (1, 2 o 3), csrf_token }
 * 1 = Abierto, 2 = En proceso, 3 = Cerrado
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

$idEvento = filter_var($input['id_security_events'] ?? null, FILTER_VALIDATE_INT);
if (!$idEvento) {
    responderJSON(false, null, 'Evento no válido.', 400);
}

$nuevoEstado = filter_var($input['state'] ?? null, FILTER_VALIDATE_INT);
if (!in_array($nuevoEstado, [EVENTO_ABIERTO, EVENTO_EN_PROCESO, EVENTO_CERRADO], true)) {
    responderJSON(false, null, 'Estado no válido.', 400);
}

try {
    $evento = eventoObtenerPorId($pdo, $idEvento);
    if (!$evento) {
        responderJSON(false, null, 'Evento no encontrado.', 404);
    }

    if (!eventosIsGlobalAdmin($pdo) && (int) $evento['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este evento.', 403);
    }

    eventoCambiarEstado($pdo, $idEvento, $nuevoEstado);

    $etiquetas = [1 => 'Abierto', 2 => 'En proceso', 3 => 'Cerrado'];
    responderJSON(true, null, 'Evento marcado como "' . $etiquetas[$nuevoEstado] . '".');
} catch (PDOException $e) {
    error_log('api/eventos/eventos-cambiar-estado.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cambiar el estado del evento.', 500);
}
