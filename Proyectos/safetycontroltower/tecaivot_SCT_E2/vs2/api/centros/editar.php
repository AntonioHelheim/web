<?php
/**
 * POST /api/centros/editar.php
 * Body JSON: { id_company_center, name, description, csrf_token }
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

$idCentro = filter_var($input['id_company_center'] ?? null, FILTER_VALIDATE_INT);
if (!$idCentro) {
    responderJSON(false, null, 'Centro/sede no válido.', 400);
}

try {
    $centro = centroObtenerPorId($pdo, $idCentro);
    if (!$centro) {
        responderJSON(false, null, 'Centro/sede no encontrado.', 404);
    }

    if (!centrosIsGlobalAdmin($pdo) && (int) $centro['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para editar este centro/sede.', 403);
    }

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

    if (centroExisteNombreEnEmpresa($pdo, (int) $centro['id_company'], $name, $idCentro)) {
        responderJSON(false, null, 'Ya existe otro centro/sede activo con ese nombre en esta empresa.', 409);
    }

    centroActualizar($pdo, $idCentro, [
        'name'        => $name,
        'description' => $description,
    ]);

    responderJSON(true, null, 'Centro/sede actualizado correctamente.');
} catch (PDOException $e) {
    error_log('api/centros/editar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar el centro/sede.', 500);
}
