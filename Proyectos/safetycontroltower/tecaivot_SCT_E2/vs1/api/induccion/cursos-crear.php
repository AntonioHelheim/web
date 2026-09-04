<?php
/**
 * POST /api/induccion/cursos-crear.php
 * Body JSON: { id_company (solo admin), name, description, attempts_allowed,
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

$idCompanySolicitado = isset($input['id_company']) ? (int) $input['id_company'] : null;
$idCompany = induccionResolveCompanyId($pdo, $idCompanySolicitado);

$faltantes = requerirCampos($input, ['name', 'description', 'attempts_allowed', 'approval_percentage', 'effective_date_from', 'effective_date_until']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

$name = sanitizarTexto((string) $input['name']);
if (mb_strlen($name) > 50) {
    responderJSON(false, null, 'El nombre del curso no puede superar los 50 caracteres.', 400);
}

$description = sanitizarTexto((string) $input['description']);
if (mb_strlen($description) > 255) {
    responderJSON(false, null, 'La descripción no puede superar los 255 caracteres.', 400);
}

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

try {
    if (cursoExisteNombreEnEmpresa($pdo, $idCompany, $name)) {
        responderJSON(false, null, 'Ya existe un curso activo con ese nombre en esta empresa.', 409);
    }

    $idNuevo = cursoCrear($pdo, [
        'id_company'           => $idCompany,
        'name'                 => $name,
        'type'                 => 'induccion',
        'description'          => $description,
        'attempts_allowed'     => $attemptsAllowed,
        'approval_percentage'  => $approvalPercentage,
        'effective_date_from'  => $fechaDesde->format('Y-m-d 00:00:00'),
        'effective_date_until' => $fechaHasta->format('Y-m-d 23:59:59'),
    ], currentUserId());

    responderJSON(true, ['id_test' => $idNuevo], 'Curso creado correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/induccion/cursos-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear el curso.', 500);
}
