<?php
/**
 * POST /api/induccion/cursos-editar.php
 * Body JSON: { id_test, name, description, attempts_allowed,
 *              approval_percentage, effective_date_from, effective_date_until, csrf_token }
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
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
if (!$idTest) {
    responderJSON(false, null, 'Curso no válido.', 400);
}

try {
    $curso = cursoObtenerPorId($pdo, $idTest);
    if (!$curso) {
        responderJSON(false, null, 'Curso no encontrado.', 404);
    }

    if (!induccionIsGlobalAdmin($pdo) && (int) $curso['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para editar este curso.', 403);
    }

    $faltantes = requerirCampos($input, ['name', 'description', 'attempts_allowed', 'approval_percentage', 'effective_date_from', 'effective_date_until']);
    if ($faltantes) {
        responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
    }

    $name = sanitizarTexto((string) $input['name']);
    $description = sanitizarTexto((string) $input['description']);

    $attemptsAllowed = filter_var($input['attempts_allowed'], FILTER_VALIDATE_INT);
    if ($attemptsAllowed === false || $attemptsAllowed < 1 || $attemptsAllowed > 20) {
        responderJSON(false, null, 'Los intentos permitidos deben ser un número entre 1 y 20.', 400);
    }

    $approvalPercentage = filter_var($input['approval_percentage'], FILTER_VALIDATE_INT);
    if ($approvalPercentage === false || $approvalPercentage < 1 || $approvalPercentage > 100) {
        responderJSON(false, null, 'El porcentaje de aprobación debe ser un número entre 1 y 100.', 400);
    }

    $fechaDesde = DateTime::createFromFormat('Y-m-d', (string) $input['effective_date_from']);
    $fechaHasta = DateTime::createFromFormat('Y-m-d', (string) $input['effective_date_until']);
    if (!$fechaDesde || !$fechaHasta) {
        responderJSON(false, null, 'Las fechas de vigencia no son válidas.', 400);
    }
    if ($fechaHasta < $fechaDesde) {
        responderJSON(false, null, 'La fecha "hasta" no puede ser anterior a la fecha "desde".', 400);
    }

    if (cursoExisteNombreEnEmpresa($pdo, (int) $curso['id_company'], $name, $idTest)) {
        responderJSON(false, null, 'Ya existe otro curso activo con ese nombre en esta empresa.', 409);
    }

    cursoActualizar($pdo, $idTest, [
        'name'                 => $name,
        'description'          => $description,
        'attempts_allowed'     => $attemptsAllowed,
        'approval_percentage'  => $approvalPercentage,
        'effective_date_from'  => $fechaDesde->format('Y-m-d 00:00:00'),
        'effective_date_until' => $fechaHasta->format('Y-m-d 23:59:59'),
    ]);

    responderJSON(true, null, 'Curso actualizado correctamente.');
} catch (PDOException $e) {
    error_log('api/induccion/cursos-editar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar el curso.', 500);
}
