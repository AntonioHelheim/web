<?php
/**
 * GET /api/proyectos/trabajadores-listar.php?id_project=7
 *
 * Lista los trabajadores actualmente asociados a un proyecto.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/ProyectoRepository.php';

proyectosRequireLecturaApi($pdo);

$idProject = filter_input(INPUT_GET, 'id_project', FILTER_VALIDATE_INT);
if (!$idProject) {
    responderJSON(false, null, 'Parámetro "id_project" inválido.', 400);
}

try {
    $proyecto = proyectoObtenerPorId($pdo, $idProject);
    if (!$proyecto) {
        responderJSON(false, null, 'Proyecto no encontrado.', 404);
    }

    if (!proyectosIsGlobalAdmin($pdo) && (int) $proyecto['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para ver este proyecto.', 403);
    }

    $trabajadores = proyectoListarTrabajadoresAsociados($pdo, $idProject);

    responderJSON(true, $trabajadores);
} catch (PDOException $e) {
    error_log('api/proyectos/trabajadores-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los trabajadores del proyecto.', 500);
}
