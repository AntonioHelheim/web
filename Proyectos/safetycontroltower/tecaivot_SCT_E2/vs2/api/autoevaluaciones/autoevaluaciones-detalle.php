<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'self_assessments.view');
$idTest = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idTest) responderJSON(false, null, 'Parámetro inválido.', 400);
try {
    $test = cursoObtenerPorId($pdo, $idTest);
    if (!$test) responderJSON(false, null, 'Autoevaluación no encontrada.', 404);
    autoevaluacionAssertTest($test);
    autoevaluacionAssertCompanyAccess($pdo, (int)$test['id_company']);
    $test['preguntas'] = cursoListarPreguntas($pdo, $idTest);
    $test['puntaje_maximo'] = cursoPuntajeMaximo($pdo, $idTest);
    responderJSON(true, $test);
} catch (PDOException $e) {
    error_log('api/autoevaluaciones/autoevaluaciones-detalle.php: '.$e->getMessage());
    responderJSON(false, null, 'No se pudo obtener la autoevaluación.', 500);
}
