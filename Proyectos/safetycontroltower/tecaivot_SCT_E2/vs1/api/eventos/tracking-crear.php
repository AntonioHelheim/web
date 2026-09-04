<?php
/**
 * POST /api/eventos/tracking-crear.php
 * Body JSON: { id_security_events, tracking_description, person_charge,
 *              commitment_date, deadline, csrf_token }
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
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

$faltantes = requerirCampos($input, ['tracking_description', 'person_charge', 'commitment_date', 'deadline']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

$descripcion = sanitizarTexto((string) $input['tracking_description']);
$responsable = sanitizarTexto((string) $input['person_charge']);

$fechaCompromiso = DateTime::createFromFormat('Y-m-d', (string) $input['commitment_date']);
$fechaPlazo = DateTime::createFromFormat('Y-m-d', (string) $input['deadline']);
if (!$fechaCompromiso || !$fechaPlazo) {
    responderJSON(false, null, 'Las fechas ingresadas no son válidas.', 400);
}

try {
    $evento = eventoObtenerPorId($pdo, $idEvento);
    if (!$evento) {
        responderJSON(false, null, 'Evento no encontrado.', 404);
    }

    if (!eventosIsGlobalAdmin($pdo) && (int) $evento['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este evento.', 403);
    }

    $idNuevo = trackingCrear($pdo, [
        'id_security_events'    => $idEvento,
        'tracking_description'  => $descripcion,
        'person_charge'         => $responsable,
        'commitment_date'       => $fechaCompromiso->format('Y-m-d 00:00:00'),
        'deadline'              => $fechaPlazo->format('Y-m-d 23:59:59'),
    ], currentUserId());

    // Un seguimiento nuevo normalmente significa que el evento ya está
    // siendo trabajado — si todavía figuraba "Abierto", se pasa
    // automáticamente a "En proceso" (no aplica si ya estaba Cerrado).
    if ((int) $evento['state'] === EVENTO_ABIERTO) {
        eventoCambiarEstado($pdo, $idEvento, EVENTO_EN_PROCESO);
    }

    responderJSON(true, ['id_security_event_tracking' => $idNuevo], 'Seguimiento agregado correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/eventos/tracking-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo agregar el seguimiento.', 500);
}
