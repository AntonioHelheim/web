<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.view');

$requested = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT);
$idCompany = protocoloIsGlobalAdmin($pdo)
    ? (($requested && $requested > 0) ? (int) $requested : null)
    : protocoloCurrentCompany($pdo);

try {
    responderJSON(true, protocoloListarGestion($pdo, $idCompany, protocoloIsGlobalAdmin($pdo)));
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) {
        responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    }
    error_log('protocolos/protocolos-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los protocolos.', 500);
}
