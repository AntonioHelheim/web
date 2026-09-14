<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.tracking');

$idAssignment = filter_input(INPUT_GET, 'id_protocol_assignment', FILTER_VALIDATE_INT);
if (!$idAssignment) responderJSON(false, null, 'Asignación no válida.', 400);

try {
    $assignment = protocoloAsignacionObtener($pdo, (int) $idAssignment);
    if (!$assignment) responderJSON(false, null, 'Asignación no encontrada.', 404);
    protocoloAssertAssignmentManageable($pdo, $assignment);
    responderJSON(true, protocoloTrackingListar($pdo, (int) $idAssignment));
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/tracking-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cargar el seguimiento.', 500);
}
