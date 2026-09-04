<?php
/**
 * POST /api/induccion/curso-preguntas-quitar.php
 * Body JSON: { id_rel, csrf_token }
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

$idRel = filter_var($input['id_rel'] ?? null, FILTER_VALIDATE_INT);
if (!$idRel) {
    responderJSON(false, null, 'Referencia no válida.', 400);
}

try {
    $rel = cursoObtenerRelPorId($pdo, $idRel);
    if (!$rel) {
        responderJSON(false, null, 'Esa pregunta ya no está en el curso.', 404);
    }

    $curso = cursoObtenerPorId($pdo, (int) $rel['id_test']);
    if (!$curso) {
        responderJSON(false, null, 'Curso no encontrado.', 404);
    }

    if (!induccionIsGlobalAdmin($pdo) && (int) $curso['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este curso.', 403);
    }

    if (cursoPreguntaTieneRespuestas($pdo, $idRel)) {
        responderJSON(false, null, 'No se puede quitar: ya hay trabajadores que respondieron esta pregunta en el curso. Puedes bajar el puntaje a 0 en su lugar o dar de baja el curso completo.', 409);
    }

    cursoQuitarPregunta($pdo, $idRel);

    responderJSON(true, null, 'Pregunta quitada del curso.');
} catch (PDOException $e) {
    error_log('api/induccion/curso-preguntas-quitar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo quitar la pregunta del curso.', 500);
}
