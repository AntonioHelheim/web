<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'self_assessments.view');
$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = autoevaluacionResolveCompanyId($pdo, $idCompanySolicitado);
try {
    responderJSON(true, cursoListarPorEmpresa($pdo, $idCompany, AUTOEVALUACION_TIPO));
} catch (PDOException $e) {
    error_log('api/autoevaluaciones/autoevaluaciones-listar.php: '.$e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las autoevaluaciones.', 500);
}
