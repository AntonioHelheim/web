<?php
/**
 * POST /api/induccion/materiales-eliminar.php
 * Body JSON: { id_material, csrf_token }
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireGestionApi($pdo);

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

$idMaterial = filter_var($input['id_material'] ?? null, FILTER_VALIDATE_INT);
if (!$idMaterial) {
    responderJSON(false, null, 'Material no válido.', 400);
}

try {
    $material = materialObtenerPorId($pdo, $idMaterial);
    if (!$material) {
        responderJSON(false, null, 'Material no encontrado.', 404);
    }

    $curso = cursoObtenerPorId($pdo, (int) $material['id_test']);
    if (!induccionIsGlobalAdmin($pdo) && (!$curso || (int) $curso['id_company'] !== currentUserCompanyId($pdo))) {
        responderJSON(false, null, 'No tienes permisos para modificar este curso.', 403);
    }

    materialEliminar($pdo, $idMaterial);

    responderJSON(true, null, 'Material eliminado correctamente.');
} catch (PDOException $e) {
    error_log('api/induccion/materiales-eliminar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo eliminar el material.', 500);
}
