<?php
/**
 * POST /api/induccion/asignaciones-crear.php
 * Body JSON: { id_test, id_users, deadline (Y-m-d), csrf_token }
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
$idUsuario = trim((string) ($input['id_users'] ?? ''));

if (!$idTest || $idUsuario === '') {
    responderJSON(false, null, 'Curso o usuario no válidos.', 400);
}

$deadline = DateTime::createFromFormat('Y-m-d', (string) ($input['deadline'] ?? ''));
if (!$deadline) {
    responderJSON(false, null, 'El plazo de vencimiento no es válido.', 400);
}

try {
    $curso = cursoObtenerPorId($pdo, $idTest);
    if (!$curso) {
        responderJSON(false, null, 'Curso no encontrado.', 404);
    }

    $idCompany = (int) $curso['id_company'];
    if (!induccionIsGlobalAdmin($pdo) && $idCompany !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para asignar este curso.', 403);
    }

    if (cursoPuntajeMaximo($pdo, $idTest) === 0) {
        responderJSON(false, null, 'Este curso todavía no tiene preguntas — agrega al menos una antes de asignarlo.', 400);
    }

    // El usuario a asignar debe pertenecer a la misma empresa del curso
    // (o el admin global puede asignar igual, ya validado arriba).
    $usuariosValidos = array_column(usuariosActivosDeEmpresa($pdo, $idCompany), 'id_users');
    if (!in_array($idUsuario, $usuariosValidos, true)) {
        responderJSON(false, null, 'El usuario no existe o no pertenece a esta empresa.', 400);
    }

    if (asignacionYaExisteActiva($pdo, $idTest, $idUsuario)) {
        responderJSON(false, null, 'Este usuario ya tiene este curso asignado (pendiente o aprobado).', 409);
    }

    $idNueva = asignacionCrear($pdo, $idTest, $idUsuario, $idCompany, $deadline->format('Y-m-d 23:59:59'), currentUserId());

    responderJSON(true, ['id_user_test_assigned' => $idNueva], 'Curso asignado correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/induccion/asignaciones-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo asignar el curso.', 500);
}
