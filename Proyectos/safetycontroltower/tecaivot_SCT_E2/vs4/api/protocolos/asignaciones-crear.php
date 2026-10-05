<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.assign');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idProtocol = filter_var($input['id_protocol'] ?? null, FILTER_VALIDATE_INT);
$requestedCompany = filter_var($input['id_company'] ?? null, FILTER_VALIDATE_INT);
if (!$idProtocol) responderJSON(false, null, 'Protocolo no válido.', 400);

try {
    $protocol = protocoloObtener($pdo, (int) $idProtocol);
    if (!$protocol) responderJSON(false, null, 'Protocolo no encontrado.', 404);

    $company = protocoloResolveCompany($pdo, $requestedCompany ? (int) $requestedCompany : null);
    $owner = $protocol['id_company'];
    if ($owner !== null && (int) $owner !== $company) {
        responderJSON(false, null, 'El protocolo pertenece a otra empresa.', 403);
    }

    $data = protocoloValidateAssignmentPayload($pdo, $input, $protocol, $company);

    // Evita duplicados operativos del mismo responsable/alcance.
    $check = $pdo->prepare(
        "SELECT COUNT(*) FROM protocol_assignments "
        . "WHERE id_protocol=:id_protocol AND id_company=:id_company "
        . "AND responsible_user=:responsible_user "
        . "AND COALESCE(id_company_center,0)=COALESCE(:id_center,0) "
        . "AND COALESCE(id_project,0)=COALESCE(:id_project,0) "
        . "AND COALESCE(id_worker,0)=COALESCE(:id_worker,0) "
        . "AND state IN ('activa','suspendida')"
    );
    $check->execute([
        'id_protocol' => (int) $idProtocol,
        'id_company' => $company,
        'responsible_user' => $data['responsible'],
        'id_center' => $data['idCenter'],
        'id_project' => $data['idProject'],
        'id_worker' => $data['idWorker'],
    ]);
    if ((int) $check->fetchColumn() > 0) {
        responderJSON(false, null, 'Ya existe una asignación activa equivalente.', 409);
    }

    $id = protocoloAsignacionCrear(
        $pdo,
        (int) $idProtocol,
        $company,
        $data['idCenter'],
        $data['idProject'],
        $data['idWorker'],
        $data['responsible'],
        $data['startAt'],
        $data['nextDueAt'],
        $data['unit'],
        $data['interval'],
        $data['overrides'],
        $data['notes'],
        (string) currentUserId()
    );

    auditTrailLogAction($pdo, $company, 'protocolos', 'protocol_assignments', $id, 'assignment', null,
        ($data['responsible'] ?: 'sin responsable') . ' · ' . $data['startAt'] . ' → ' . $data['nextDueAt'],
        'assign', (string)$protocol['name']);
    responderJSON(true, ['id_protocol_assignment' => $id], 'Protocolo asignado correctamente.', 201);
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/asignaciones-crear: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear la asignación.', 500);
}
