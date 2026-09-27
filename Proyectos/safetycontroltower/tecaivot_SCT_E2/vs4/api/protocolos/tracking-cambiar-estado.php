<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.tracking');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idTracking = filter_var($input['id_protocol_tracking'] ?? null, FILTER_VALIDATE_INT);
$status = strtolower(trim((string) ($input['status'] ?? '')));
if (!$idTracking || !in_array($status, PROTOCOLO_TRACKING_STATES, true)) {
    responderJSON(false, null, 'Estado de seguimiento no válido.', 400);
}

try {
    $tracking = protocoloTrackingObtener($pdo, (int) $idTracking);
    if (!$tracking) responderJSON(false, null, 'Seguimiento no encontrado.', 404);

    $assignment = protocoloAsignacionObtener($pdo, (int) $tracking['id_protocol_assignment']);
    if (!$assignment) responderJSON(false, null, 'Asignación no encontrada.', 404);
    protocoloAssertAssignmentManageable($pdo, $assignment);

    protocoloTrackingCambiarEstado($pdo, (int) $idTracking, $status);
    auditTrailLogAction($pdo, (int)$assignment['id_company'], 'protocolos', 'protocol_tracking', $idTracking,
        'status', $tracking['status'], $status, 'state_change', (string)($assignment['protocol_name'] ?? ('Seguimiento #' . $idTracking)));
    responderJSON(true, null, 'Seguimiento actualizado correctamente.');
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/tracking-cambiar-estado: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar el seguimiento.', 500);
}
