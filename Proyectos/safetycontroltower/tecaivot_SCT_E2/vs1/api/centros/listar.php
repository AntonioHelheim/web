<?php
/**
 * GET /api/centros/listar.php?id_company=7
 * Mismo criterio de aislamiento que /api/proyectos/listar.php.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/CentroRepository.php';

centrosRequireLecturaApi($pdo);

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = centrosResolveCompanyId($pdo, $idCompanySolicitado);

try {
    $centros = centroListarPorEmpresa($pdo, $idCompany);
    responderJSON(true, $centros);
} catch (PDOException $e) {
    error_log('api/centros/listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los centros/sedes.', 500);
}
