<?php
/**
 * GET /api/dashboard/indicadores.php?id_company=7&id_project=3
 *
 * Devuelve todos los indicadores de una sola vez (no un endpoint por
 * cada número) — a diferencia de los módulos CRUD, acá todo es lectura
 * y se consume junto en la misma pantalla, así que consolidar evita
 * varios round-trips para cargar una sola vista.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/DashboardRepository.php';

requireLogin();

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = dashboardResolveCompanyId($pdo, $idCompanySolicitado);

$idProject = filter_input(INPUT_GET, 'id_project', FILTER_VALIDATE_INT) ?: null;

try {
    $datos = [
        'totales' => [
            'proyectos'     => dashboardTotalProyectos($pdo, $idCompany),
            'centros'       => dashboardTotalCentros($pdo, $idCompany),
            'trabajadores'  => dashboardTotalTrabajadores($pdo, $idCompany),
        ],
        'induccion' => dashboardIndicadoresInduccion($pdo, $idCompany),
        'eventos'   => dashboardIndicadoresEventos($pdo, $idCompany, $idProject),
    ];

    // "Total de empresas registradas" es un indicador de plataforma, no
    // de una empresa en particular — solo tiene sentido para quien
    // administra varias empresas, así que solo se agrega para el admin
    // global en vez de mostrarle "1" a todos los demás usuarios.
    if (dashboardIsGlobalAdmin($pdo)) {
        $datos['totales']['empresas'] = dashboardTotalEmpresas($pdo);
    }

    responderJSON(true, $datos);
} catch (PDOException $e) {
    error_log('api/dashboard/indicadores.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los indicadores.', 500);
}
