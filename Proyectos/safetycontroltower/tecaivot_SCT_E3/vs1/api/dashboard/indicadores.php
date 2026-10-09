<?php
/**
 * GET /api/dashboard/indicadores.php
 *   ?id_company=4&id_project=7&id_center=8&period=90
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/DashboardRepository.php';

requireCapability($pdo, 'dashboard.view');

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = dashboardResolveCompanyId($pdo, $idCompanySolicitado);
$idProject = filter_input(INPUT_GET, 'id_project', FILTER_VALIDATE_INT) ?: null;
$idCenter = filter_input(INPUT_GET, 'id_center', FILTER_VALIDATE_INT) ?: null;
$period = dashboardResolvePeriod((string) ($_GET['period'] ?? '90'));

try {
    if ($idProject !== null && !dashboardProyectoPerteneceEmpresa($pdo, $idCompany, $idProject)) {
        responderJSON(false, null, 'El proyecto seleccionado no pertenece a la empresa.', 400);
    }
    if ($idCenter !== null && !dashboardCentroPerteneceEmpresa($pdo, $idCompany, $idCenter)) {
        responderJSON(false, null, 'El centro seleccionado no pertenece a la empresa.', 400);
    }

    $induccion = dashboardIndicadoresEvaluacion($pdo, $idCompany, 'induccion', $period['desde'], $period['hasta']);
    $auditorias = dashboardIndicadoresAuditorias($pdo, $idCompany, $period['desde'], $period['hasta']);
    $autoevaluaciones = dashboardIndicadoresEvaluacion($pdo, $idCompany, 'autoevaluacion', $period['desde'], $period['hasta']);
    $eventos = dashboardIndicadoresEventos($pdo, $idCompany, $idProject, $idCenter, $period['desde'], $period['hasta']);
    $formularios = dashboardIndicadoresFormularios($pdo, $idCompany, $period['desde'], $period['hasta']);
    $protocolos = dashboardIndicadoresProtocolos($pdo, $idCompany, $idProject, $idCenter, $period['desde'], $period['hasta']);

    $datos = [
        'scope' => [
            'id_company' => $idCompany,
            'id_project' => $idProject,
            'id_center' => $idCenter,
            'period' => $period['key'],
            'desde' => $period['desde'],
            'hasta' => $period['hasta'],
            'operation_filters_note' => 'Los filtros Proyecto y Centro aplican a Eventos y Protocolos. Evaluaciones y Formularios no almacenan proyecto/centro en su modelo actual.',
        ],
        'totales' => [
            'proyectos' => dashboardTotalProyectos($pdo, $idCompany),
            'centros' => dashboardTotalCentros($pdo, $idCompany),
            'trabajadores' => dashboardTotalTrabajadores($pdo, $idCompany),
        ],
        'eventos' => $eventos,
        'induccion' => $induccion,
        'auditorias' => $auditorias,
        'autoevaluaciones' => $autoevaluaciones,
        'formularios' => $formularios,
        'protocolos' => $protocolos,
        'tendencia' => dashboardTendenciaMensual($pdo, $idCompany, $idProject, $idCenter, $period['desde'], $period['hasta']),
        'actividad_reciente' => dashboardActividadReciente($pdo, $idCompany, $idProject, $idCenter, $period['desde'], $period['hasta'], 10),
    ];

    if (dashboardIsGlobalAdmin($pdo)) {
        $datos['totales']['empresas'] = dashboardTotalEmpresas($pdo);
    }

    responderJSON(true, $datos);
} catch (PDOException $e) {
    error_log('api/dashboard/indicadores.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los indicadores del dashboard.', 500);
} catch (Throwable $e) {
    error_log('api/dashboard/indicadores.php runtime: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron procesar los indicadores del dashboard.', 500);
}
