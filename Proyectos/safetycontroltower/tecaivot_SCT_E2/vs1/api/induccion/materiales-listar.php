<?php
/**
 * GET /api/induccion/materiales-listar.php?id_test=7
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireLecturaApi($pdo);

$idTest = filter_input(INPUT_GET, 'id_test', FILTER_VALIDATE_INT);
if (!$idTest) {
    responderJSON(false, null, 'Parámetro "id_test" inválido.', 400);
}

try {
    $curso = cursoObtenerPorId($pdo, $idTest);
    if (!$curso) {
        responderJSON(false, null, 'Curso no encontrado.', 404);
    }

    if (!induccionIsGlobalAdmin($pdo) && (int) $curso['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para ver este curso.', 403);
    }

    responderJSON(true, materialListarPorCurso($pdo, $idTest));
} catch (PDOException $e) {
    error_log('api/induccion/materiales-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los materiales.', 500);
}
