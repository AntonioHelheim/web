<?php
/**
 * GET /api/eventos/trabajadores-disponibles.php?id_company=7
 * Campo opcional del evento (id_worker).
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireReportarApi($pdo);

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = eventosResolveCompanyId($pdo, $idCompanySolicitado);

try {
    responderJSON(true, trabajadoresActivosDeEmpresa($pdo, $idCompany));
} catch (PDOException $e) {
    error_log('api/eventos/trabajadores-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los trabajadores.', 500);
}
