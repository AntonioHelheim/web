<?php
/** GET /api/dashboard/centros-disponibles.php?id_company=7 */
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

requireCapability($pdo, 'dashboard.view');
$dashboardAccessContext = resolveCurrentUserAccessContext($pdo);
if ($dashboardAccessContext === null || (string) ($dashboardAccessContext['primary_role'] ?? '') === 'trabajador') {
    responderJSON(false, null, 'Este catálogo no está disponible para tu rol.', 403);
}
$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = dashboardResolveCompanyId($pdo, $idCompanySolicitado);

try {
    responderJSON(true, centrosActivosDeEmpresa($pdo, $idCompany));
} catch (PDOException $e) {
    error_log('api/dashboard/centros-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los centros.', 500);
}
