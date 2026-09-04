<?php
/**
 * GET /api/eventos/eventos-listar.php?id_company=7&id_project=3&criticality=alta&state=1&q=...
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireReportarApi($pdo);

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = eventosResolveCompanyId($pdo, $idCompanySolicitado);

$filtros = [];
if (!empty($_GET['id_project'])) {
    $filtros['id_project'] = filter_input(INPUT_GET, 'id_project', FILTER_VALIDATE_INT);
}
if (!empty($_GET['criticality'])) {
    $criticidadesValidas = ['baja', 'media', 'alta', 'critica'];
    if (in_array($_GET['criticality'], $criticidadesValidas, true)) {
        $filtros['criticality'] = $_GET['criticality'];
    }
}
if (!empty($_GET['state'])) {
    $filtros['state'] = filter_input(INPUT_GET, 'state', FILTER_VALIDATE_INT);
}
if (!empty($_GET['q'])) {
    $filtros['busqueda'] = trim((string) $_GET['q']);
}

try {
    responderJSON(true, eventoListarPorEmpresa($pdo, $idCompany, $filtros));
} catch (PDOException $e) {
    error_log('api/eventos/eventos-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los eventos.', 500);
}
