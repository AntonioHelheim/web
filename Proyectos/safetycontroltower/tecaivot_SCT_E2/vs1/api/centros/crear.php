<?php
/**
 * POST /api/centros/crear.php
 * Body JSON: { id_company (solo admin), name, description, csrf_token }
 *
 * A diferencia de Proyectos, en company_center la columna description
 * es obligatoria (NOT NULL en la tabla) — se valida como campo requerido.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/CentroRepository.php';

centrosRequireGestionApi($pdo);

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
$idCompany = centrosResolveCompanyId($pdo, $idCompanySolicitado);

$faltantes = requerirCampos($input, ['name', 'description']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

$name = sanitizarTexto((string) $input['name']);
if (mb_strlen($name) > 50) {
    responderJSON(false, null, 'El nombre del centro no puede superar los 50 caracteres.', 400);
}

$description = sanitizarTexto((string) $input['description']);
if (mb_strlen($description) > 255) {
    responderJSON(false, null, 'La descripción no puede superar los 255 caracteres.', 400);
}

try {
    if (centroExisteNombreEnEmpresa($pdo, $idCompany, $name)) {
        responderJSON(false, null, 'Ya existe un centro/sede activo con ese nombre en esta empresa.', 409);
    }

    $idNuevo = centroCrear($pdo, [
        'id_company'  => $idCompany,
        'name'        => $name,
        'description' => $description,
    ], currentUserId());

    responderJSON(true, ['id_company_center' => $idNuevo], 'Centro/sede creado correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/centros/crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear el centro/sede.', 500);
}
