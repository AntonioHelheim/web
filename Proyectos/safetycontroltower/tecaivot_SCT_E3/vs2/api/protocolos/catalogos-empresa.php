<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.assign');

$idCompany = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT);

try {
    $company = protocoloResolveCompany($pdo, $idCompany ? (int) $idCompany : null);

    responderJSON(true, protocoloCatalogosEmpresa($pdo,(int)$company));
} catch (Throwable $e) {
    error_log('protocolos/catalogos-empresa: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar los catálogos de la empresa.', 500);
}
