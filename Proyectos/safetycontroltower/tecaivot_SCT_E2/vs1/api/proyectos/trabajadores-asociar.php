<?php
/**
 * POST /api/proyectos/trabajadores-asociar.php
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

    $idCompany = (int) $proyecto['id_company'];
    if (!proyectosIsGlobalAdmin($pdo) && $idCompany !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este proyecto.', 403);
    }

    if (!proyectoTrabajadorPerteneceAEmpresa($pdo, $idWorker, $idCompany)) {
        responderJSON(false, null, 'El trabajador no existe o no pertenece a esta empresa.', 400);
    }

    if (proyectoTrabajadorYaAsociado($pdo, $idProject, $idWorker)) {
        responderJSON(false, null, 'El trabajador ya está asociado a este proyecto.', 409);
    }

    proyectoAsociarTrabajador($pdo, $idProject, $idWorker);

    responderJSON(true, null, 'Trabajador asociado al proyecto correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/proyectos/trabajadores-asociar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo asociar el trabajador.', 500);
}
