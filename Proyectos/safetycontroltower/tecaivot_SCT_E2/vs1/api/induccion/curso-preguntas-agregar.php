<?php
/**
 * POST /api/induccion/curso-preguntas-agregar.php
 * Body JSON: { id_test, id_question, assigned_score, csrf_token }
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
$idPregunta = filter_var($input['id_question'] ?? null, FILTER_VALIDATE_INT);
$puntaje = filter_var($input['assigned_score'] ?? null, FILTER_VALIDATE_INT);

if (!$idTest || !$idPregunta) {
    responderJSON(false, null, 'Curso o pregunta no válidos.', 400);
}
if ($puntaje === false || $puntaje === null || $puntaje < 1) {
    responderJSON(false, null, 'El puntaje debe ser un número mayor a 0.', 400);
}

try {
    $curso = cursoObtenerPorId($pdo, $idTest);
    if (!$curso) {
        responderJSON(false, null, 'Curso no encontrado.', 404);
    }

    if (!induccionIsGlobalAdmin($pdo) && (int) $curso['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este curso.', 403);
    }

    $pregunta = preguntaObtenerPorId($pdo, $idPregunta);
    if (!$pregunta || (int) $pregunta['state'] !== 1) {
        responderJSON(false, null, 'La pregunta no existe o está inactiva.', 400);
    }

    if (cursoPreguntaYaAgregada($pdo, $idTest, $idPregunta)) {
        responderJSON(false, null, 'Esa pregunta ya está agregada a este curso.', 409);
    }

    $idRel = cursoAgregarPregunta($pdo, $idTest, $idPregunta, $puntaje, currentUserId());

    responderJSON(true, ['id_rel' => $idRel], 'Pregunta agregada al curso.', 201);
} catch (PDOException $e) {
    error_log('api/induccion/curso-preguntas-agregar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo agregar la pregunta al curso.', 500);
}
