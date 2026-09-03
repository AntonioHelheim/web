<?php
/**
 * GET /api/proyectos/detalle.php?id=7
 *
 * cliente/jefatura/trabajador solo pueden ver proyectos de su propia
 * empresa; administrador/administrador_completo puede ver cualquiera.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/ProyectoRepository.php';

proyectosRequireLecturaApi($pdo);

$idProject = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idProject) {
    responderJSON(false, null, 'Parámetro "id" inválido.', 400);
}

try {
    $proyecto = proyectoObtenerPorId($pdo, $idProject);
    if (!$proyecto) {
        responderJSON(false, null, 'Proyecto no encontrado.', 404);
    }

    if (!proyectosIsGlobalAdmin($pdo) && (int) $proyecto['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para ver este proyecto.', 403);
    }

    responderJSON(true, $proyecto);
} catch (PDOException $e) {
    error_log('api/proyectos/detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener el proyecto.', 500);
}
