<?php
/**
 * POST /api/proyectos/cambiar-estado.php
 * Body JSON: { id_project, state (0 o 1), csrf_token }
 *
 * Baja lógica (o reactivación) de un proyecto. Este endpoint es el
 * equivalente que hoy le falta a Empresas (empresaDesactivar() existe en
 * su repositorio pero no está expuesto en ningún endpoint) — acá se
 * construye desde el principio para no repetir ese pendiente.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/ProyectoRepository.php';

proyectosRequireGestionApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$csrfToken = (string) ($input['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

$idProject = filter_var($input['id_project'] ?? null, FILTER_VALIDATE_INT);
if (!$idProject) {
    responderJSON(false, null, 'Proyecto no válido.', 400);
}

$nuevoEstado = filter_var($input['state'] ?? null, FILTER_VALIDATE_INT);
if ($nuevoEstado === false || $nuevoEstado === null || !in_array($nuevoEstado, [0, 1], true)) {
    responderJSON(false, null, 'Estado no válido.', 400);
}

try {
    $proyecto = proyectoObtenerPorId($pdo, $idProject);
    if (!$proyecto) {
        responderJSON(false, null, 'Proyecto no encontrado.', 404);
    }

    if (!proyectosIsGlobalAdmin($pdo) && (int) $proyecto['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este proyecto.', 403);
    }

    proyectoCambiarEstado($pdo, $idProject, $nuevoEstado);

    $mensaje = $nuevoEstado === 1 ? 'Proyecto reactivado correctamente.' : 'Proyecto dado de baja correctamente.';
    responderJSON(true, null, $mensaje);
} catch (PDOException $e) {
    error_log('api/proyectos/cambiar-estado.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cambiar el estado del proyecto.', 500);
}
