<?php
/**
 * POST /api/empresas/editar.php
 * Body JSON: { id_company, razon_social, address, email, csrf_token }
 *
 * administrador puede editar cualquier empresa; cliente solo la propia
 * (un trabajador no tiene permiso para editar datos de la empresa).
 */

require __DIR__ . '/../../session_bootstrap.php';
require __DIR__ . '/../../lib/db.php';
require __DIR__ . '/../../lib/auth.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireRole($pdo, ['administrador', 'cliente']);

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

$idCompany = filter_var($input['id_company'] ?? null, FILTER_VALIDATE_INT);
if (!$idCompany) {
    responderJSON(false, null, 'Empresa no válida.', 400);
}

$esAdministrador = in_array('administrador', currentUserRoles($pdo), true);
if (!$esAdministrador && $idCompany !== currentUserCompanyId($pdo)) {
    responderJSON(false, null, 'No tienes permisos para editar esta empresa.', 403);
}

$faltantes = requerirCampos($input, ['razon_social', 'address', 'email']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

if (!validarEmail($input['email'])) {
    responderJSON(false, null, 'Correo electrónico no válido.', 400);
}

$datos = [
    'razon_social' => sanitizarTexto($input['razon_social']),
    'address'      => sanitizarTexto($input['address']),
    'email'        => trim($input['email']),
];

try {
    if (!empresaObtenerPorId($pdo, $idCompany)) {
        responderJSON(false, null, 'Empresa no encontrada.', 404);
    }

    empresaActualizar($pdo, $idCompany, $datos);
    responderJSON(true, null, 'Empresa actualizada correctamente.');

} catch (PDOException $e) {
    error_log('api/empresas/editar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar la empresa.', 500);
}