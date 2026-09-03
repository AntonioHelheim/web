<?php
/**
 * POST /api/proyectos/editar.php
 * Body JSON: { id_project, name, description, csrf_token }
 *
 * administrador/administrador_completo puede editar cualquier proyecto.
 * cliente/jefatura solo pueden editar proyectos de su propia empresa.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
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

try {
    $proyecto = proyectoObtenerPorId($pdo, $idProject);
    if (!$proyecto) {
        responderJSON(false, null, 'Proyecto no encontrado.', 404);
    }

    if (!proyectosIsGlobalAdmin($pdo) && (int) $proyecto['id_company'] !== currentUserCompanyId($pdo)) {
        // No se distingue "no existe" de "no es tuyo": ambos casos devuelven
        // 403 genérico, para no filtrar qué IDs de proyecto existen.
        responderJSON(false, null, 'No tienes permisos para editar este proyecto.', 403);
    }

    $faltantes = requerirCampos($input, ['name']);
    if ($faltantes) {
        responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
    }

    $name = sanitizarTexto((string) $input['name']);
    if (mb_strlen($name) > 150) {
        responderJSON(false, null, 'El nombre del proyecto no puede superar los 150 caracteres.', 400);
    }

    $description = sanitizarTexto((string) ($input['description'] ?? ''));
    if (mb_strlen($description) > 255) {
        responderJSON(false, null, 'La descripción no puede superar los 255 caracteres.', 400);
    }

    if (proyectoExisteNombreEnEmpresa($pdo, (int) $proyecto['id_company'], $name, $idProject)) {
        responderJSON(false, null, 'Ya existe otro proyecto activo con ese nombre en esta empresa.', 409);
    }

    proyectoActualizar($pdo, $idProject, [
        'name'        => $name,
        'description' => $description,
    ]);

    responderJSON(true, null, 'Proyecto actualizado correctamente.');
} catch (PDOException $e) {
    error_log('api/proyectos/editar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar el proyecto.', 500);
}
