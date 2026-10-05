<?php
/**
 * GET /api/dashboard/indicadores-consolidados.php
 *   ?period=90
 *
 * Vista de KPIs agregados y consolidación de indicadores (Etapa 3): totales
 * y tendencia sumados sobre TODAS las empresas activas, más un ranking
 * comparativo por empresa. Solo disponible para administradores globales;
 * un administrador de una sola empresa ya ve sus propios indicadores en
 * indicadores.php y no tiene nada que "consolidar".
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/DashboardRepository.php';

requireCapability($pdo, 'dashboard.view');

if (!dashboardIsGlobalAdmin($pdo)) {
    responderJSON(false, null, 'Esta vista consolidada solo está disponible para administradores globales.', 403);
}

$period = dashboardResolvePeriod((string) ($_GET['period'] ?? '90'));

try {
    $datos = [
        'scope' => [
            'consolidated' => true,
            'period' => $period['key'],
            'desde' => $period['desde'],
            'hasta' => $period['hasta'],
        ],
        'totales' => dashboardConsolidadoTotales($pdo),
        'eventos' => dashboardConsolidadoEventos($pdo, $period['desde'], $period['hasta']),
        'formularios' => dashboardConsolidadoFormularios($pdo, $period['desde'], $period['hasta']),
        'protocolos' => dashboardConsolidadoProtocolos($pdo),
        'tendencia' => dashboardConsolidadoTendenciaMensual($pdo, $period['desde'], $period['hasta']),
        'ranking_empresas' => dashboardRankingEmpresas($pdo, $period['desde'], $period['hasta']),
    ];

    responderJSON(true, $datos);
} catch (PDOException $e) {
    error_log('api/dashboard/indicadores-consolidados.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los indicadores consolidados.', 500);
} catch (Throwable $e) {
    error_log('api/dashboard/indicadores-consolidados.php runtime: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron procesar los indicadores consolidados.', 500);
}
