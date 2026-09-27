<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../api/formularios/common.php';
requireLogin();

$idExecution = filter_input(INPUT_GET, 'id_protocol_execution', FILTER_VALIDATE_INT);
if (!$idExecution) responderJSON(false, null, 'Ejecución no válida.', 400);

try {
    $execution = protocoloEjecucionObtener($pdo, (int) $idExecution);
    if (!$execution) responderJSON(false, null, 'Ejecución no encontrada.', 404);

    $assignment = protocoloAsignacionObtener($pdo, (int) $execution['id_protocol_assignment']);
    if (!$assignment) responderJSON(false, null, 'Asignación no encontrada.', 404);

    $allowed = protocoloUserCanExecuteAssignment($pdo, $assignment);
    if (!$allowed && currentUserHasCapability($pdo, 'protocols.manage')) {
        $allowed = protocoloIsGlobalAdmin($pdo)
            || (int) $assignment['id_company'] === protocoloCurrentCompany($pdo);
    }
    if (!$allowed) responderJSON(false, null, 'No tienes permisos para consultar esta ejecución.', 403);

    foreach ($execution['submissions'] as &$submission) {
        $detail = formularioEnvioObtener($pdo, (int) $submission['id_submission']);
        if ($detail) {
            $answers = formularioRespuestasEnvio($pdo, (int) $submission['id_submission']);
            foreach ($answers as &$answer) {
                $answer['display_value'] = formularioDecodeStoredValue($answer);
            }
            unset($answer);
            $detail['respuestas'] = $answers;
        }
        $submission['detalle'] = $detail;
    }
    unset($submission);

    $execution['tracking'] = protocoloTrackingListar($pdo, (int) $assignment['id_protocol_assignment']);
    responderJSON(true, $execution);
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/ejecucion-detalle: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cargar el detalle de la ejecución.', 500);
}
