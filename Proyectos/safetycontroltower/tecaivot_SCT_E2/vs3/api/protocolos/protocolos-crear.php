<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    responderJSON(false, null, 'Solicitud inválida.', 400);
}
requireCsrfToken($input);

$data = protocoloValidateDefinitionPayload($input);
$requestedCompany = filter_var($input['id_company'] ?? null, FILTER_VALIDATE_INT);
$ownerCompany = protocoloResolveCatalogOwner(
    $pdo,
    $requestedCompany ? (int) $requestedCompany : null
);

try {
    protocoloRequireSchema($pdo);

    $sql = 'SELECT COUNT(*) FROM protocols WHERE code=:code AND ';
    $params = ['code' => $data['code']];
    if ($ownerCompany === null) {
        $sql .= 'id_company IS NULL';
    } else {
        $sql .= 'id_company=:id_company';
        $params['id_company'] = $ownerCompany;
    }
    $check = $pdo->prepare($sql);
    $check->execute($params);
    if ((int) $check->fetchColumn() > 0) {
        responderJSON(false, null, 'Ya existe un protocolo con ese código en el alcance seleccionado.', 409);
    }

    $id = protocoloCrear(
        $pdo,
        $ownerCompany,
        $data['code'],
        $data['name'],
        $data['description'],
        $data['version'],
        $data['authority'],
        $data['normativeReference'],
        $data['sourceUrl'],
        $data['effectiveFrom'],
        $data['effectiveUntil'],
        $data['parameters'],
        (string) currentUserId()
    );

    auditTrailLogChanges($pdo, $ownerCompany, 'protocolos', 'protocols', $id, [], [
        'code'=>$data['code'],'name'=>$data['name'],'description'=>$data['description'],'version'=>$data['version'],
        'authority'=>$data['authority'],'normative_reference'=>$data['normativeReference'],'source_url'=>$data['sourceUrl'],
        'effective_date_from'=>$data['effectiveFrom'],'effective_date_until'=>$data['effectiveUntil'],'parameters'=>$data['parameters'],'state'=>1
    ], 'create', $data['name']);
    responderJSON(true, ['id_protocol' => $id], 'Protocolo creado correctamente.', 201);
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) {
        responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    }
    error_log('protocolos/protocolos-crear: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear el protocolo.', 500);
}
