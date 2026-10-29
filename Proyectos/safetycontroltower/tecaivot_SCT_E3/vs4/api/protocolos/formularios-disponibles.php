<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/FormularioRepository.php';
requireCapability($pdo, 'protocols.forms');

$idCompany = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT);

try {
    if (protocoloIsGlobalAdmin($pdo) && (!$idCompany || $idCompany <= 0)) {
        responderJSON(true,formularioListarGlobalesActivos($pdo));
    }

    $company = protocoloResolveCompany($pdo, $idCompany ? (int) $idCompany : null);
    responderJSON(true, protocoloFormulariosDisponibles($pdo, $company));
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/formularios-disponibles: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar los formularios.', 500);
}
