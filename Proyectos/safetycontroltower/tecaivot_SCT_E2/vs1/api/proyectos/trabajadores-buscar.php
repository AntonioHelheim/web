<?php
/**
 * GET /api/proyectos/trabajadores-buscar.php?id_project=7&q=perez
 *
 * Busca trabajadores activos de la empresa del proyecto que todavía no
 * estén asociados a él, por RUT, nombre o apellido. Alimenta el buscador
 * de "agregar trabajador" en la ficha del proyecto.
 *
 * Nota: hasta que exista el módulo de Trabajadores con datos reales,
 * esta búsqueda va a devolver una lista vacía (la tabla workers está
 * vacía) — no es un error del endpoint, es el estado esperado hoy.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/ProyectoRepository.php';

proyectosRequireGestionApi($pdo);

$idProject = filter_input(INPUT_GET, 'id_project', FILTER_VALIDATE_INT);
if (!$idProject) {
    responderJSON(false, null, 'Parámetro "id_project" inválido.', 400);
}

$busqueda = trim((string) ($_GET['q'] ?? ''));
if (mb_strlen($busqueda) < 2) {
    responderJSON(false, null, 'Ingresa al menos 2 caracteres para buscar.', 400);
}

try {
    $proyecto = proyectoObtenerPorId($pdo, $idProject);
    if (!$proyecto) {
        responderJSON(false, null, 'Proyecto no encontrado.', 404);
    }

    $idCompany = (int) $proyecto['id_company'];
    if (!proyectosIsGlobalAdmin($pdo) && $idCompany !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este proyecto.', 403);
    }

    $resultados = proyectoBuscarTrabajadoresDisponibles($pdo, $idCompany, $idProject, $busqueda);

    responderJSON(true, $resultados);
} catch (PDOException $e) {
    error_log('api/proyectos/trabajadores-buscar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo realizar la búsqueda.', 500);
}
