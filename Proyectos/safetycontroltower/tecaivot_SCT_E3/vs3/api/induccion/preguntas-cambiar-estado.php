<?php
/**
 * POST /api/induccion/preguntas-cambiar-estado.php
 * Body JSON: { id_questions, state (0 o 1), csrf_token }
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireBancoPreguntasApi($pdo);

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

$idPregunta = filter_var($input['id_questions'] ?? null, FILTER_VALIDATE_INT);
if (!$idPregunta) {
    responderJSON(false, null, 'Pregunta no válida.', 400);
}

$nuevoEstado = filter_var($input['state'] ?? null, FILTER_VALIDATE_INT);
if ($nuevoEstado === false || $nuevoEstado === null || !in_array($nuevoEstado, [0, 1], true)) {
    responderJSON(false, null, 'Estado no válido.', 400);
}

try {
    preguntaCambiarEstado($pdo, $idPregunta, $nuevoEstado);
    $mensaje = $nuevoEstado === 1 ? 'Pregunta reactivada correctamente.' : 'Pregunta dada de baja correctamente.';
    responderJSON(true, null, $mensaje);
} catch (PDOException $e) {
    error_log('api/induccion/preguntas-cambiar-estado.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cambiar el estado de la pregunta.', 500);
}
