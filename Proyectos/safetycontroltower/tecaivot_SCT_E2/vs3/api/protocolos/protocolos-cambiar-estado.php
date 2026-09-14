<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.state');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idProtocol = filter_var($input['id_protocol'] ?? null, FILTER_VALIDATE_INT);
$state = filter_var($input['state'] ?? null, FILTER_VALIDATE_INT);
if (!$idProtocol || !in_array($state, [0,1], true)) {
    responderJSON(false, null, 'Datos no válidos.', 400);
}

try {
    $protocol = protocoloObtener($pdo, (int) $idProtocol);
    if (!$protocol) responderJSON(false, null, 'Protocolo no encontrado.', 404);

    if ($protocol['id_company'] === null && !protocoloIsGlobalAdmin($pdo)) {
        responderJSON(false, null, 'Solo Administrador Completo puede cambiar el estado de una plantilla global.', 403);
    }
    if ($protocol['id_company'] !== null && !protocoloIsGlobalAdmin($pdo)
        && (int) $protocol['id_company'] !== protocoloCurrentCompany($pdo)) {
        responderJSON(false, null, 'El protocolo pertenece a otra empresa.', 403);
    }

    protocoloCambiarEstado($pdo, (int) $idProtocol, (int) $state);
    auditTrailLogAction($pdo, $protocol['id_company']!==null?(int)$protocol['id_company']:null, 'protocolos', 'protocols', $idProtocol,
        'state', $protocol['state'], $state, 'state_change', (string)$protocol['name']);
    responderJSON(true, null, $state === 1 ? 'Protocolo activado.' : 'Protocolo desactivado.');
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/protocolos-cambiar-estado: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cambiar el estado del protocolo.', 500);
}
