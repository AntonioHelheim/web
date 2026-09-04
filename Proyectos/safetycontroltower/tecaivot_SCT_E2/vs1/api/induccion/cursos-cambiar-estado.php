<?php
/**
 * POST /api/induccion/cursos-cambiar-estado.php
 * Body JSON: { id_test, state (0 o 1), csrf_token }
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

$idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
if (!$idTest) {
    responderJSON(false, null, 'Curso no válido.', 400);
}

$nuevoEstado = filter_var($input['state'] ?? null, FILTER_VALIDATE_INT);
if ($nuevoEstado === false || $nuevoEstado === null || !in_array($nuevoEstado, [0, 1], true)) {
    responderJSON(false, null, 'Estado no válido.', 400);
}

try {
    $curso = cursoObtenerPorId($pdo, $idTest);
    if (!$curso) {
        responderJSON(false, null, 'Curso no encontrado.', 404);
    }

    if (!induccionIsGlobalAdmin($pdo) && (int) $curso['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este curso.', 403);
    }

    cursoCambiarEstado($pdo, $idTest, $nuevoEstado);

    $mensaje = $nuevoEstado === 1 ? 'Curso reactivado correctamente.' : 'Curso dado de baja correctamente.';
    responderJSON(true, null, $mensaje);
} catch (PDOException $e) {
    error_log('api/induccion/cursos-cambiar-estado.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cambiar el estado del curso.', 500);
}
