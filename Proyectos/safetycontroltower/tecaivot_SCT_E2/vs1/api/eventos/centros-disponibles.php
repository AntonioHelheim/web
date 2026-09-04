<?php
/**
 * GET /api/eventos/centros-disponibles.php?id_company=7
 *
 * id_company_center es obligatorio en security_events — sin al menos
 * un centro/sede activo, no se puede reportar un evento. Ver el
 * comentario de diseño en EventoRepository.php.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireReportarApi($pdo);

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = eventosResolveCompanyId($pdo, $idCompanySolicitado);

try {
    responderJSON(true, centrosActivosDeEmpresa($pdo, $idCompany));
} catch (PDOException $e) {
    error_log('api/eventos/centros-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los centros/sedes.', 500);
}
