<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

auditoriaRequireGestionApi($pdo);
requireCapability($pdo, 'questions.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$question = trim((string) ($input['question'] ?? ''));
$difficulty = filter_var($input['difficulty'] ?? 1, FILTER_VALIDATE_INT);
$points = filter_var($input['points'] ?? 10, FILTER_VALIDATE_INT);
$opciones = $input['opciones'] ?? [];

if ($question === '' || sctTextLength($question) > 2000) responderJSON(false, null, 'Pregunta no válida.', 400);
if ($difficulty === false || $difficulty < 1 || $difficulty > 5 || $points === false || $points < 1 || $points > 10000) {
    responderJSON(false, null, 'Dificultad o puntaje no válidos.', 400);
}
if (!is_array($opciones) || count($opciones) < 2) responderJSON(false, null, 'Debes registrar al menos dos alternativas.', 400);

$normalizadas = [];
$correctas = 0;
foreach ($opciones as $opcion) {
    $texto = trim((string) ($opcion['text_option'] ?? ''));
    $correcta = !empty($opcion['is_it_co']);
    if ($texto === '' || sctTextLength($texto) > 50) responderJSON(false, null, 'Las alternativas deben tener entre 1 y 50 caracteres.', 400);
    if ($correcta) $correctas++;
    $normalizadas[] = ['text_option' => $texto, 'is_it_co' => $correcta, 'add_expl_opt' => ''];
}
if ($correctas !== 1) responderJSON(false, null, 'Debe existir exactamente una alternativa correcta.', 400);

try {
    $id = preguntaCrear($pdo, [
        'question' => $question,
        'url_add_material' => '',
        'difficulty' => $difficulty,
        'points' => $points,
        'add_expl_question' => '',
    ], $normalizadas, (string) currentUserId());
    responderJSON(true, ['id_questions' => $id], 'Pregunta creada.', 201);
} catch (PDOException $e) {
    error_log('api/auditorias/preguntas-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear la pregunta.', 500);
}
