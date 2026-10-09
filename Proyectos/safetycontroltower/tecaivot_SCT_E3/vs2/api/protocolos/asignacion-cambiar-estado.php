<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.assign');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idAssignment = filter_var($input['id_protocol_assignment'] ?? null, FILTER_VALIDATE_INT);
$state = strtolower(trim((string) ($input['state'] ?? '')));
if (!$idAssignment || !in_array($state, PROTOCOLO_ASSIGNMENT_STATES, true)) {
    responderJSON(false, null, 'Estado de asignación no válido.', 400);
}

try {
    $assignment = protocoloAsignacionObtener($pdo, (int) $idAssignment);
    if (!$assignment) responderJSON(false, null, 'Asignación no encontrada.', 404);
    protocoloAssertAssignmentManageable($pdo, $assignment);

    $current = (string) $assignment['state'];
    $allowedTransitions = [
        'activa' => ['suspendida','cerrada','cancelada'],
        'suspendida' => ['activa','cerrada','cancelada'],
        'cerrada' => [],
        'cancelada' => [],
    ];
    if ($state !== $current && !in_array($state, $allowedTransitions[$current] ?? [], true)) {
        responderJSON(false, null, 'La transición de estado solicitada no está permitida.', 409);
    }

    protocoloAsignacionCambiarEstado($pdo, (int) $idAssignment, $state);
    auditTrailLogAction($pdo, (int)$assignment['id_company'], 'protocolos', 'protocol_assignments', $idAssignment,
        'state', $current, $state, 'state_change', (string)($assignment['protocol_name'] ?? ('Asignación #' . $idAssignment)));
    responderJSON(true, null, 'Estado de asignación actualizado.');
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/asignacion-cambiar-estado: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar la asignación.', 500);
}
