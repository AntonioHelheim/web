<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/AuditoriaRepository.php';

requireCapability($pdo, 'audits.questions');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
$idPregunta = filter_var($input['id_question'] ?? null, FILTER_VALIDATE_INT);
$puntaje = filter_var($input['assigned_score'] ?? null, FILTER_VALIDATE_INT);
if (!$idTest || !$idPregunta || $puntaje === false || $puntaje < 1 || $puntaje > 10000) responderJSON(false, null, 'Datos inválidos.', 400);

try {
    $test = cursoObtenerPorId($pdo, $idTest);
    if (!$test) responderJSON(false, null, 'Auditoría no encontrada.', 404);
    auditoriaAssertTest($test);
    auditoriaAssertCompanyAccess($pdo, (int) $test['id_company']);
    if (auditoriaTestTieneAsignaciones($pdo, $idTest)) {
        responderJSON(false, null, 'No puedes modificar las preguntas de una auditoría que ya tiene ejecuciones asignadas.', 409);
    }
    if (!preguntaObtenerPorId($pdo, $idPregunta)) responderJSON(false, null, 'Pregunta no encontrada.', 404);
    if (cursoPreguntaYaAgregada($pdo, $idTest, $idPregunta)) responderJSON(false, null, 'La pregunta ya pertenece a esta auditoría.', 409);
    $id = cursoAgregarPregunta($pdo, $idTest, $idPregunta, $puntaje, (string) currentUserId());
    auditTrailLogAction($pdo, (int)$test['id_company'], 'auditorias', 'company_test_rel_questions', $id,
        'id_question', null, $idPregunta . ' (puntaje ' . $puntaje . ')', 'assign', (string)$test['name']);
    responderJSON(true, ['id_rel' => $id], 'Pregunta agregada.');
} catch (PDOException $e) {
    error_log('api/auditorias/auditoria-preguntas-agregar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo agregar la pregunta.', 500);
}
