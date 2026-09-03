<?php
/**
 * POST /api/trabajadores/cambiar-estado.php
 * Body JSON: { id_worker, state (0 o 1), csrf_token }
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/TrabajadorRepository.php';

trabajadoresRequireGestionApi($pdo);

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

$idWorker = filter_var($input['id_worker'] ?? null, FILTER_VALIDATE_INT);
if (!$idWorker) {
    responderJSON(false, null, 'Trabajador no válido.', 400);
}

$nuevoEstado = filter_var($input['state'] ?? null, FILTER_VALIDATE_INT);
if ($nuevoEstado === false || $nuevoEstado === null || !in_array($nuevoEstado, [0, 1], true)) {
    responderJSON(false, null, 'Estado no válido.', 400);
}

try {
    $trabajador = trabajadorObtenerPorId($pdo, $idWorker);
    if (!$trabajador) {
        responderJSON(false, null, 'Trabajador no encontrado.', 404);
    }

    if (!trabajadoresIsGlobalAdmin($pdo) && (int) $trabajador['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este trabajador.', 403);
    }

    trabajadorCambiarEstado($pdo, $idWorker, $nuevoEstado);

    $mensaje = $nuevoEstado === 1 ? 'Trabajador reactivado correctamente.' : 'Trabajador dado de baja correctamente.';
    responderJSON(true, null, $mensaje);
} catch (PDOException $e) {
    error_log('api/trabajadores/cambiar-estado.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cambiar el estado del trabajador.', 500);
}
