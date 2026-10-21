<?php
require __DIR__ . '/common.php';
historialRequireViewApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
if (!auditTrailSchemaReady($pdo)) {
    responderJSON(false, ['migration_required' => true], 'Debes ejecutar la migración de Historial de cambios.', 409);
}

$requestedCompany = isset($_GET['id_company']) && $_GET['id_company'] !== '' ? (int) $_GET['id_company'] : null;
$idCompany = historialResolveCompany($pdo, $requestedCompany);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = max(10, min(100, (int) ($_GET['per_page'] ?? 50)));
$offset = ($page - 1) * $perPage;
$dateFromRaw = trim((string) ($_GET['date_from'] ?? ''));
$dateToRaw = trim((string) ($_GET['date_to'] ?? ''));
$dateFrom = historialValidDate($dateFromRaw);
$dateTo = historialValidDate($dateToRaw);
if ($dateFromRaw !== '' && $dateFrom === null) responderJSON(false, null, 'Fecha inicial no válida.', 400);
if ($dateToRaw !== '' && $dateTo === null) responderJSON(false, null, 'Fecha final no válida.', 400);
if ($dateFrom && $dateTo && $dateFrom > $dateTo) responderJSON(false, null, 'El rango de fechas no es válido.', 400);

$module = trim((string) ($_GET['module'] ?? ''));
$action = trim((string) ($_GET['action'] ?? ''));
$actor = trim((string) ($_GET['changed_by'] ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));
if (sctTextLength($module) > 50 || sctTextLength($action) > 30 || sctTextLength($actor) > 50 || sctTextLength($search) > 120) {
    responderJSON(false, null, 'Uno o más filtros no son válidos.', 400);
}

$filters = [
    'id_company' => $idCompany,
    'module' => $module,
    'action' => $action,
    'changed_by' => $actor,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'search' => $search,
];

try {
    $total = historialContar($pdo, $filters);
    $rows = historialListar($pdo, $filters, $perPage, $offset);
    $summary = historialResumen($pdo, $filters);
    $catalogs = historialCatalogos($pdo, $idCompany);
    responderJSON(true, [
        'rows' => $rows,
        'summary' => $summary,
        'catalogs' => $catalogs,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ],
        'scope' => [
            'global' => historialIsGlobal($pdo),
            'id_company' => $idCompany,
        ],
    ]);
} catch (Throwable $e) {
    error_log('historial/historial-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible cargar el historial.', 500);
}
