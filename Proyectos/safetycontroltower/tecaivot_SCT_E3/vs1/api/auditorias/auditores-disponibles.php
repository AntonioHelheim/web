<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../app/Users/UserRepository.php';

requireCapability($pdo, 'audits.assign');

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;

try {
    $repo = new SctUserRepository($pdo);
    if (auditoriaIsGlobalAdmin($pdo)) {
        $rows = $repo->listActiveWithCompany(null);
    } else {
        $idCompany = auditoriaResolveCompanyId($pdo, $idCompanySolicitado);
        $rows = $repo->listActiveWithCompany($idCompany);
    }
    responderJSON(true, $rows);
} catch (PDOException $e) {
    error_log('api/auditorias/auditores-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los auditores disponibles.', 500);
}
