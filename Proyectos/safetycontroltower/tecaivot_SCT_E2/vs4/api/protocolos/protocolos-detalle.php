<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.view');

$idProtocol = filter_input(INPUT_GET, 'id_protocol', FILTER_VALIDATE_INT);
if (!$idProtocol) responderJSON(false, null, 'Protocolo no válido.', 400);

$requestedCompany = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT);

try {
    $protocol = protocoloObtener($pdo, (int) $idProtocol);
    if (!$protocol) responderJSON(false, null, 'Protocolo no encontrado.', 404);

    if (protocoloIsGlobalAdmin($pdo)) {
        $company = ($requestedCompany && $requestedCompany > 0)
            ? (int) $requestedCompany
            : (($protocol['id_company'] !== null) ? (int) $protocol['id_company'] : 0);
    } else {
        $company = protocoloCurrentCompany($pdo);
        $owner = $protocol['id_company'];
        if ($owner !== null && (int) $owner !== $company) {
            responderJSON(false, null, 'El protocolo pertenece a otra empresa.', 403);
        }
    }

    $protocol['implementation_company'] = $company > 0 ? $company : null;
    $protocol['definition_locked'] = protocoloTieneAsignaciones(
        $pdo,
        (int) $idProtocol,
        $protocol['id_company'] !== null ? (int) $protocol['id_company'] : null
    );
    $protocol['formularios'] = $company > 0
        ? protocoloFormularios($pdo, (int) $idProtocol, $company)
        : protocoloFormularios($pdo, (int) $idProtocol, 0);
    $protocol['asignaciones'] = $company > 0
        ? protocoloAsignacionesListar($pdo, (int) $idProtocol, $company)
        : [];

    responderJSON(true, $protocol);
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/protocolos-detalle: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cargar el detalle del protocolo.', 500);
}
