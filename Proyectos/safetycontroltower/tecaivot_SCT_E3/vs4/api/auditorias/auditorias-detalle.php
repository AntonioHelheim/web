<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'audits.view');

$idTest = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idTest) responderJSON(false, null, 'Parámetro inválido.', 400);

try {
    $test = cursoObtenerPorId($pdo, $idTest);
    if (!$test) responderJSON(false, null, 'Auditoría no encontrada.', 404);
    auditoriaAssertTest($test);
    auditoriaAssertCompanyAccess($pdo, (int) $test['id_company']);
    $test['preguntas'] = cursoListarPreguntas($pdo, $idTest);
    $test['puntaje_maximo'] = cursoPuntajeMaximo($pdo, $idTest);
    responderJSON(true, $test);
} catch (PDOException $e) {
    error_log('api/auditorias/auditorias-detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener la auditoría.', 500);
}
