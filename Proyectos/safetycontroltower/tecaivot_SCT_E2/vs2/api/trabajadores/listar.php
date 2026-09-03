<?php
/**
 * GET /api/trabajadores/listar.php?id_company=7&q=perez&state=1
 *
 * q y state son opcionales. Mismo criterio de aislamiento por empresa
 * que Proyectos/Centros.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/TrabajadorRepository.php';

trabajadoresRequireLecturaApi($pdo);

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = trabajadoresResolveCompanyId($pdo, $idCompanySolicitado);

$busqueda = trim((string) ($_GET['q'] ?? ''));

$filtroEstado = null;
if (isset($_GET['state']) && $_GET['state'] !== '') {
    $filtroEstado = filter_input(INPUT_GET, 'state', FILTER_VALIDATE_INT);
    if (!in_array($filtroEstado, [0, 1], true)) {
        responderJSON(false, null, 'Filtro de estado no válido.', 400);
    }
}

try {
    $trabajadores = trabajadorListarPorEmpresa($pdo, $idCompany, $busqueda, $filtroEstado);
    responderJSON(true, $trabajadores);
} catch (PDOException $e) {
    error_log('api/trabajadores/listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los trabajadores.', 500);
}
