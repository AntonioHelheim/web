<?php
/**
 * POST /api/proyectos/crear.php
 * Body JSON: { id_company (solo admin), name, description, csrf_token }
 *
 * administrador/administrador_completo: deben indicar id_company.
 * cliente/jefatura: crean proyectos solo en su propia empresa (se ignora
 * cualquier id_company que venga en el body).
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

$idCompanySolicitado = isset($input['id_company']) ? (int) $input['id_company'] : null;
$idCompany = proyectosResolveCompanyId($pdo, $idCompanySolicitado);

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

$datos = [
    'id_company'  => $idCompany,
    'name'        => $name,
    'description' => $description,
];

try {
    if (proyectoExisteNombreEnEmpresa($pdo, $idCompany, $name)) {
        responderJSON(false, null, 'Ya existe un proyecto activo con ese nombre en esta empresa.', 409);
    }

    $idNuevo = proyectoCrear($pdo, $datos, currentUserId());

    responderJSON(true, ['id_project' => $idNuevo], 'Proyecto creado correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/proyectos/crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear el proyecto.', 500);
}
