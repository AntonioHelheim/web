<?php
require __DIR__ . '/common.php';

requireCapability($pdo, 'self_assessments.assign');
$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;

try {
    $idCompany = autoevaluacionResolveCompanyId($pdo, $idCompanySolicitado);
    $rows = (new SctUserRepository($pdo))->listActiveWithCompany((int)$idCompany);
    responderJSON(true, $rows);
} catch (PDOException $e) {
    error_log('api/autoevaluaciones/usuarios-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los usuarios disponibles.', 500);
}
