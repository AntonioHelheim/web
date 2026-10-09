<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.assign');

$idProtocol = filter_input(INPUT_GET, 'id_protocol', FILTER_VALIDATE_INT);
$idCompany = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT);
if (!$idProtocol) responderJSON(false, null, 'Protocolo no válido.', 400);

try {
    $company = protocoloResolveCompany($pdo, $idCompany ? (int) $idCompany : null);
    responderJSON(true, protocoloAsignacionesListar($pdo, (int) $idProtocol, $company));
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/asignaciones-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar las asignaciones.', 500);
}
