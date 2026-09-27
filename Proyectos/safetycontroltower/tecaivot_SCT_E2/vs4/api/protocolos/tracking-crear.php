<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.tracking');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idAssignment = filter_var($input['id_protocol_assignment'] ?? null, FILTER_VALIDATE_INT);
$idExecution = filter_var($input['id_protocol_execution'] ?? null, FILTER_VALIDATE_INT);
$description = trim((string) ($input['description'] ?? ''));
$responsible = trim((string) ($input['responsible_user'] ?? ''));
$commitment = protocoloDateTime((string) ($input['commitment_date'] ?? ''), 'Fecha de compromiso');
$deadline = protocoloDateTime((string) ($input['deadline'] ?? ''), 'Plazo');

if (!$idAssignment || $description === '' || sctTextLength($description) > 10000) {
    responderJSON(false, null, 'Completa una descripción válida.', 400);
}
if ($deadline < $commitment) {
    responderJSON(false, null, 'El plazo no puede ser anterior al compromiso.', 400);
}

try {
    $assignment = protocoloAsignacionObtener($pdo, (int) $idAssignment);
    if (!$assignment) responderJSON(false, null, 'Asignación no encontrada.', 404);
    protocoloAssertAssignmentManageable($pdo, $assignment);

    if ($idExecution) {
        $execution = protocoloEjecucionObtener($pdo, (int) $idExecution);
        if (!$execution || (int) $execution['id_protocol_assignment'] !== (int) $idAssignment) {
            responderJSON(false, null, 'La ejecución no corresponde a la asignación.', 400);
        }
    }

    $responsibleValue = null;
    if ($responsible !== '') {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM users WHERE id_users=:id_users AND id_company=:id_company AND state=1'
        );
        $stmt->execute([
            'id_users' => $responsible,
            'id_company' => (int) $assignment['id_company'],
        ]);
        if ((int) $stmt->fetchColumn() !== 1) {
            responderJSON(false, null, 'El responsable de seguimiento no pertenece a la empresa.', 400);
        }
        $responsibleValue = $responsible;
    }

    $id = protocoloTrackingCrear(
        $pdo,
        (int) $idAssignment,
        $idExecution ? (int) $idExecution : null,
        $description,
        $responsibleValue,
        $commitment,
        $deadline,
        (string) currentUserId()
    );
    auditTrailLogAction($pdo, (int)$assignment['id_company'], 'protocolos', 'protocol_tracking', $id,
        'description', null, $description, 'track', (string)($assignment['protocol_name'] ?? ('Asignación #' . $idAssignment)));
    responderJSON(true, ['id_protocol_tracking' => $id], 'Seguimiento creado correctamente.', 201);
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/tracking-crear: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear el seguimiento.', 500);
}
