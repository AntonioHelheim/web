<?php
/**
 * GET /api/induccion/cursos-detalle.php?id=7
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireLecturaApi($pdo);

$idTest = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idTest) {
    responderJSON(false, null, 'Parámetro "id" inválido.', 400);
}

try {
    $curso = cursoObtenerPorId($pdo, $idTest);
    if (!$curso) {
        responderJSON(false, null, 'Curso no encontrado.', 404);
    }

    if (!induccionIsGlobalAdmin($pdo) && (int) $curso['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para ver este curso.', 403);
    }

    $curso['preguntas'] = cursoListarPreguntas($pdo, $idTest);
    $curso['puntaje_maximo'] = cursoPuntajeMaximo($pdo, $idTest);
    $curso['materiales'] = materialListarPorCurso($pdo, $idTest);

    responderJSON(true, $curso);
} catch (PDOException $e) {
    error_log('api/induccion/cursos-detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener el curso.', 500);
}
