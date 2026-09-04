<?php
/**
 * GET /api/induccion/cursos-listar.php?id_company=7
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireLecturaApi($pdo);

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = induccionResolveCompanyId($pdo, $idCompanySolicitado);

try {
    $cursos = cursoListarPorEmpresa($pdo, $idCompany, 'induccion');
    responderJSON(true, $cursos);
} catch (PDOException $e) {
    error_log('api/induccion/cursos-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los cursos.', 500);
}
