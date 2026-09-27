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

// Compatibilidad: el endpoint acepta el antiguo id_worker y el nuevo id_workers[].
$legacySingle = !array_key_exists('id_workers', $input);
$rawWorkers = array_key_exists('id_workers', $input) ? $input['id_workers'] : ($input['id_worker'] ?? []);
if (!is_array($rawWorkers)) $rawWorkers = [$rawWorkers];
$workerIds = [];
foreach ($rawWorkers as $rawWorker) {
    if ($rawWorker === null || $rawWorker === '') continue;
    $idWorker = filter_var($rawWorker, FILTER_VALIDATE_INT);
    if ($idWorker && $idWorker > 0) $workerIds[(int) $idWorker] = true;
}
$workerIds = array_keys($workerIds);

try {
    $protocol = protocoloObtener($pdo, (int) $idProtocol);
    if (!$protocol) responderJSON(false, null, 'Protocolo no encontrado.', 404);

    $company = protocoloResolveCompany($pdo, $requestedCompany ? (int) $requestedCompany : null);
    $owner = $protocol['id_company'];
    if ($owner !== null && (int) $owner !== $company) responderJSON(false, null, 'El protocolo pertenece a otra empresa.', 403);

    // Sin trabajadores seleccionados conserva el comportamiento anterior:
    // crea una asignación de alcance empresa/centro/proyecto con id_worker NULL.
    $targets = $workerIds ? $workerIds : [null];
    $validated = [];
    foreach ($targets as $workerId) {
        $payload = $input;
        $payload['id_worker'] = $workerId;
        unset($payload['id_workers']);
        $validated[] = protocoloValidateAssignmentPayload($pdo, $payload, $protocol, $company);
    }

    $check = $pdo->prepare(
        "SELECT COUNT(*) FROM protocol_assignments "
        . "WHERE id_protocol=:id_protocol AND id_company=:id_company "
        . "AND responsible_user=:responsible_user "
        . "AND COALESCE(id_company_center,0)=COALESCE(:id_center,0) "
        . "AND COALESCE(id_project,0)=COALESCE(:id_project,0) "
        . "AND COALESCE(id_worker,0)=COALESCE(:id_worker,0) "
        . "AND state IN ('activa','suspendida')"
    );

    if ($legacySingle && count($validated) === 1) {
        $single = $validated[0];
        $check->execute([
            'id_protocol' => (int) $idProtocol,
            'id_company' => $company,
            'responsible_user' => $single['responsible'],
            'id_center' => $single['idCenter'],
            'id_project' => $single['idProject'],
            'id_worker' => $single['idWorker'],
        ]);
        if ((int) $check->fetchColumn() > 0) {
            responderJSON(false, null, 'Ya existe una asignación activa equivalente.', 409);
        }
    }

    $creados = [];
    $omitidos = [];
    $pdo->beginTransaction();
    foreach ($validated as $data) {
        $check->execute([
            'id_protocol' => (int) $idProtocol,
            'id_company' => $company,
            'responsible_user' => $data['responsible'],
            'id_center' => $data['idCenter'],
            'id_project' => $data['idProject'],
            'id_worker' => $data['idWorker'],
        ]);
        if ((int) $check->fetchColumn() > 0) {
            $omitidos[] = $data['idWorker'];
            continue;
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
        $creados[] = ['id_protocol_assignment' => $id, 'id_worker' => $data['idWorker']];
        auditTrailLogAction($pdo, $company, 'protocolos', 'protocol_assignments', $id, 'assignment', null,
            ($data['responsible'] ?: 'sin responsable') . ' · ' . $data['startAt'] . ' → ' . $data['nextDueAt'],
            'assign', (string) $protocol['name']);
    }
    $pdo->commit();

    $mensaje = count($creados) . ' asignación(es) de protocolo creada(s).';
    if ($omitidos) $mensaje .= ' ' . count($omitidos) . ' equivalente(s) ya existente(s) fueron omitida(s).';
    responderJSON(true, [
        'created_count' => count($creados),
        'skipped_count' => count($omitidos),
        'assignments' => $creados,
        'skipped_workers' => $omitidos,
        'id_protocol_assignment' => $creados ? $creados[0]['id_protocol_assignment'] : null,
    ], $mensaje, $creados ? 201 : 200);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/asignaciones-crear: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear la asignación.', 500);
}
