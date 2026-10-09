<?php
/**
 * POST /api/induccion/preguntas-crear.php
 * Body JSON: {
 *   question, url_add_material, difficulty, points, add_expl_question,
 *   opciones: [ { text_option, is_it_co, add_expl_opt }, ... ],
 *   csrf_token
 * }
 *
 * Exige mínimo 2 alternativas y exactamente una marcada como correcta
 * (preguntas de alternativa única, no de selección múltiple).
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireBancoPreguntasApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    responderJSON(false, null, 'Cuerpo de la solicitud inválido.', 400);
}

$csrfToken = (string) ($input['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

$faltantes = requerirCampos($input, ['question', 'difficulty', 'points']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

$question = sanitizarTexto((string) $input['question']);
$urlMaterial = sanitizarTexto((string) ($input['url_add_material'] ?? ''));
$explicacion = sanitizarTexto((string) ($input['add_expl_question'] ?? ''));

$difficulty = filter_var($input['difficulty'], FILTER_VALIDATE_INT);
if ($difficulty === false || $difficulty < 1 || $difficulty > 5) {
    responderJSON(false, null, 'La dificultad debe ser un número entre 1 y 5.', 400);
}

$points = filter_var($input['points'], FILTER_VALIDATE_INT);
if ($points === false || $points < 1) {
    responderJSON(false, null, 'El puntaje debe ser un número mayor a 0.', 400);
}

$opcionesInput = $input['opciones'] ?? [];
if (!is_array($opcionesInput) || count($opcionesInput) < 2) {
    responderJSON(false, null, 'La pregunta debe tener al menos 2 alternativas.', 400);
}

$opciones = [];
$correctas = 0;
foreach ($opcionesInput as $opcion) {
    $texto = sanitizarTexto((string) ($opcion['text_option'] ?? ''));
    if ($texto === '') {
        responderJSON(false, null, 'Todas las alternativas deben tener texto.', 400);
    }
    $esCorrecta = !empty($opcion['is_it_co']);
    if ($esCorrecta) {
        $correctas++;
    }
    $opciones[] = [
        'text_option' => $texto,
        'is_it_co'    => $esCorrecta,
        'add_expl_opt' => sanitizarTexto((string) ($opcion['add_expl_opt'] ?? '')),
    ];
}

if ($correctas !== 1) {
    responderJSON(false, null, 'Debes marcar exactamente una alternativa como correcta.', 400);
}

try {
    $idNueva = preguntaCrear($pdo, [
        'question'          => $question,
        'url_add_material'  => $urlMaterial,
        'difficulty'        => $difficulty,
        'points'            => $points,
        'add_expl_question' => $explicacion,
    ], $opciones, currentUserId());

    responderJSON(true, ['id_questions' => $idNueva], 'Pregunta creada correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/induccion/preguntas-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear la pregunta.', 500);
}
