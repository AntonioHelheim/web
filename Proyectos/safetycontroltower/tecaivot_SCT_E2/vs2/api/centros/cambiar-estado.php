<?php
/**
 * POST /api/centros/cambiar-estado.php
 * Body JSON: { id_company_center, state (0 o 1), csrf_token }
 */

require __DIR__ . '/common.php';
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

$nuevoEstado = filter_var($input['state'] ?? null, FILTER_VALIDATE_INT);
if ($nuevoEstado === false || $nuevoEstado === null || !in_array($nuevoEstado, [0, 1], true)) {
    responderJSON(false, null, 'Estado no válido.', 400);
}

try {
    $centro = centroObtenerPorId($pdo, $idCentro);
    if (!$centro) {
        responderJSON(false, null, 'Centro/sede no encontrado.', 404);
    }

    if (!centrosIsGlobalAdmin($pdo) && (int) $centro['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este centro/sede.', 403);
    }

    centroCambiarEstado($pdo, $idCentro, $nuevoEstado);

    $mensaje = $nuevoEstado === 1 ? 'Centro/sede reactivado correctamente.' : 'Centro/sede dado de baja correctamente.';
    responderJSON(true, null, $mensaje);
} catch (PDOException $e) {
    error_log('api/centros/cambiar-estado.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cambiar el estado del centro/sede.', 500);
}
