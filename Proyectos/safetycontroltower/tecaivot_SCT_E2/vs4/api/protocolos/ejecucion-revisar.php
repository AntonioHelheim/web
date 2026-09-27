<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.review');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idExecution = filter_var($input['id_protocol_execution'] ?? null, FILTER_VALIDATE_INT);
$result = strtolower(trim((string) ($input['result'] ?? '')));
$notes = protocoloNullableText($input['review_notes'] ?? null, 10000, 'Observaciones');

if (!$idExecution || !in_array($result, ['conforme','observado','no_conforme','no_aplica'], true)) {
    responderJSON(false, null, 'Resultado de revisión no válido.', 400);
}

try {
    $execution = protocoloEjecucionObtener($pdo, (int) $idExecution);
    if (!$execution) responderJSON(false, null, 'Ejecución no encontrada.', 404);

    $assignment = protocoloAsignacionObtener($pdo, (int) $execution['id_protocol_assignment']);
    if (!$assignment) responderJSON(false, null, 'Asignación no encontrada.', 404);
    protocoloAssertAssignmentManageable($pdo, $assignment);

    if ((string) $execution['result'] !== 'pendiente_revision') {
        responderJSON(false, null, 'La ejecución ya fue revisada.', 409);
    }

    $pdo->beginTransaction();

    // Evita que dos gestores revisen simultáneamente la misma ejecución.
    $lock = $pdo->prepare(
        'SELECT result FROM protocol_executions '
        . 'WHERE id_protocol_execution=:id_execution FOR UPDATE'
    );
    $lock->execute(['id_execution' => (int) $idExecution]);
    $lockedResult = $lock->fetchColumn();
    if ($lockedResult !== 'pendiente_revision') {
        $pdo->rollBack();
        responderJSON(false, null, 'La ejecución ya fue revisada por otro usuario.', 409);
    }

    protocoloEjecucionRevisar(
        $pdo,
        (int) $idExecution,
        $result,
        $notes,
        (string) currentUserId()
    );

    $next = protocoloNextDueAfterReview(
        (string) $assignment['recurrence_unit'],
        $assignment['recurrence_interval'] !== null ? (int) $assignment['recurrence_interval'] : null
    );

    // La revisión no debe reactivar una asignación que fue suspendida,
    // cerrada o cancelada durante el proceso de revisión.
    $currentState = (string) $assignment['state'];
    if ($currentState === 'cancelada' || $currentState === 'cerrada') {
        $newState = $currentState;
        $next = null;
    } elseif ($currentState === 'suspendida') {
        $newState = 'suspendida';
    } else {
        $newState = $next === null ? 'cerrada' : 'activa';
    }

    protocoloAsignacionActualizarProximoVencimiento(
        $pdo,
        (int) $assignment['id_protocol_assignment'],
        $next,
        $newState
    );

    $requestId = auditTrailRequestId();
    auditTrailLogAction($pdo, (int)$assignment['id_company'], 'protocolos', 'protocol_executions', $idExecution,
        'result', $execution['result'], $result, 'review', (string)($assignment['protocol_name'] ?? ('Ejecución #' . $idExecution)),
        (string)currentUserId(), $requestId);
    if (($execution['review_notes'] ?? null) !== $notes) {
        auditTrailLogAction($pdo, (int)$assignment['id_company'], 'protocolos', 'protocol_executions', $idExecution,
            'review_notes', $execution['review_notes'] ?? null, $notes, 'review', (string)($assignment['protocol_name'] ?? ('Ejecución #' . $idExecution)),
            (string)currentUserId(), $requestId);
    }
    if ((string)$assignment['state'] !== $newState) {
        auditTrailLogAction($pdo, (int)$assignment['id_company'], 'protocolos', 'protocol_assignments', (int)$assignment['id_protocol_assignment'],
            'state', $assignment['state'], $newState, 'state_change', (string)($assignment['protocol_name'] ?? ('Asignación #' . $assignment['id_protocol_assignment'])),
            (string)currentUserId(), $requestId);
    }
    auditTrailLogAction($pdo, (int)$assignment['id_company'], 'protocolos', 'protocol_assignments', (int)$assignment['id_protocol_assignment'],
        'next_due_at', $assignment['next_due_at'] ?? null, $next, 'update', (string)($assignment['protocol_name'] ?? ('Asignación #' . $assignment['id_protocol_assignment'])),
        (string)currentUserId(), $requestId);

    $pdo->commit();

    responderJSON(true, [
        'assignment_state' => $newState,
        'next_due_at' => $next,
    ], 'Ejecución revisada correctamente.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/ejecucion-revisar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo revisar la ejecución.', 500);
}
