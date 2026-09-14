<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.execute');

$idAssignment = filter_input(INPUT_GET, 'id_protocol_assignment', FILTER_VALIDATE_INT);
if (!$idAssignment) {
    responderJSON(false, null, 'Asignación no válida.', 400);
}

try {
    $assignment = protocoloAsignacionObtener($pdo, (int) $idAssignment);
    if (!$assignment) {
        responderJSON(false, null, 'Asignación no encontrada.', 404);
    }
    if (!protocoloUserCanExecuteAssignment($pdo, $assignment)) {
        responderJSON(false, null, 'Esta asignación no corresponde a tu cuenta.', 403);
    }

    responderJSON(true, protocoloEjecucionesAsignacion($pdo, (int) $idAssignment));
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) {
        responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    }
    error_log('protocolos/mis-ejecuciones: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cargar el historial del protocolo.', 500);
}
