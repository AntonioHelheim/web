<?php
/**
 * POST /api/proyectos/trabajadores-desasociar.php
 * Body JSON: { id_project, id_worker, csrf_token }
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
$idWorker = filter_var($input['id_worker'] ?? null, FILTER_VALIDATE_INT);
if (!$idProject || !$idWorker) {
    responderJSON(false, null, 'Proyecto o trabajador no válido.', 400);
}

try {
    $proyecto = proyectoObtenerPorId($pdo, $idProject);
    if (!$proyecto) {
        responderJSON(false, null, 'Proyecto no encontrado.', 404);
    }

    if (!proyectosIsGlobalAdmin($pdo) && (int) $proyecto['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este proyecto.', 403);
    }

    proyectoDesasociarTrabajador($pdo, $idProject, $idWorker);

    responderJSON(true, null, 'Trabajador desasociado del proyecto correctamente.');
} catch (PDOException $e) {
    error_log('api/proyectos/trabajadores-desasociar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo desasociar el trabajador.', 500);
}
