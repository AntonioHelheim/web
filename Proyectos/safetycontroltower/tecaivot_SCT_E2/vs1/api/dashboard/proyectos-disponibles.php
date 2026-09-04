<?php
/**
 * GET /api/dashboard/proyectos-disponibles.php?id_company=7
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

requireLogin();

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = dashboardResolveCompanyId($pdo, $idCompanySolicitado);

try {
    responderJSON(true, proyectosActivosDeEmpresa($pdo, $idCompany));
} catch (PDOException $e) {
    error_log('api/dashboard/proyectos-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los proyectos.', 500);
}
