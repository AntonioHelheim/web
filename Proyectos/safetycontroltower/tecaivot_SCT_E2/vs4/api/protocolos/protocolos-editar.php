<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idProtocol = filter_var($input['id_protocol'] ?? null, FILTER_VALIDATE_INT);
if (!$idProtocol) responderJSON(false, null, 'Protocolo no válido.', 400);
$data = protocoloValidateDefinitionPayload($input);

try {
    $protocol = protocoloObtener($pdo, (int) $idProtocol);
    if (!$protocol) responderJSON(false, null, 'Protocolo no encontrado.', 404);

    protocoloAssertDefinitionEditable($pdo, $protocol);

    $sql = 'SELECT COUNT(*) FROM protocols WHERE id_protocol<>:id_protocol AND code=:code AND ';
    $params = [
        'id_protocol' => (int) $idProtocol,
        'code' => $data['code'],
    ];
    if ($protocol['id_company'] === null) {
        $sql .= 'id_company IS NULL';
    } else {
        $sql .= 'id_company=:id_company';
        $params['id_company'] = (int) $protocol['id_company'];
    }
    $check = $pdo->prepare($sql);
    $check->execute($params);
    if ((int) $check->fetchColumn() > 0) {
        responderJSON(false, null, 'Ya existe otro protocolo con ese código en el mismo alcance.', 409);
    }

    protocoloEditar(
        $pdo,
        (int) $idProtocol,
        $data['code'],
        $data['name'],
        $data['description'],
        $data['version'],
        $data['authority'],
        $data['normativeReference'],
        $data['sourceUrl'],
        $data['effectiveFrom'],
        $data['effectiveUntil'],
        $data['parameters']
    );

    auditTrailLogChanges($pdo, $protocol['id_company']!==null?(int)$protocol['id_company']:null, 'protocolos', 'protocols', $idProtocol,
        $protocol, array_merge($protocol,[
            'code'=>$data['code'],'name'=>$data['name'],'description'=>$data['description'],'version'=>$data['version'],
            'authority'=>$data['authority'],'normative_reference'=>$data['normativeReference'],'source_url'=>$data['sourceUrl'],
            'effective_date_from'=>$data['effectiveFrom'],'effective_date_until'=>$data['effectiveUntil'],'parameters'=>$data['parameters']
        ]), 'update', $data['name'], ['code','name','description','version','authority','normative_reference','source_url','effective_date_from','effective_date_until','parameters']);
    responderJSON(true, null, 'Protocolo actualizado correctamente.');
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) {
        responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    }
    error_log('protocolos/protocolos-editar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar el protocolo.', 500);
}
