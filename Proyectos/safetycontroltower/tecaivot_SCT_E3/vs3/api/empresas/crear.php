<?php
/**
 * POST /api/empresas/crear.php
 * Body JSON: { rut, razon_social, address, email, csrf_token }
 *
 * Solo SuperUsuario puede registrar empresas nuevas (companies.create_all).
 */

require __DIR__ . '/../../session_bootstrap.php';
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';
require_once __DIR__ . '/../../app/Auth/RoleProvisioningService.php';

requireCapability($pdo, 'companies.create_all');

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

$faltantes = requerirCampos($input, ['rut', 'razon_social', 'address', 'email']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

if (!validarRutChileno($input['rut'])) {
    responderJSON(false, null, 'RUT no válido.', 400);
}

if (!validarEmail($input['email'])) {
    responderJSON(false, null, 'Correo electrónico no válido.', 400);
}

$datos = [
    'rut'          => sanitizarTexto($input['rut']),
    'razon_social' => sanitizarTexto($input['razon_social']),
    'address'      => sanitizarTexto($input['address']),
    'email'        => trim($input['email']),
];

try {
    if (empresaObtenerPorRut($pdo, $datos['rut'])) {
        responderJSON(false, null, 'Ya existe una empresa registrada con ese RUT.', 409);
    }

    $pdo->beginTransaction();

    $idNuevo = empresaCrear($pdo, $datos, currentUserId());

    (new SctRoleProvisioningService($pdo))->provisionCompany($idNuevo, (string)currentUserId());

    auditTrailLogChanges($pdo, $idNuevo, 'empresas', 'company', $idNuevo, [], [
        'rut' => $datos['rut'], 'razon_social' => $datos['razon_social'],
        'address' => $datos['address'], 'email' => $datos['email'], 'state' => 1,
    ], 'create', $datos['razon_social']);

    $pdo->commit();

    responderJSON(true, ['id_company' => $idNuevo], 'Empresa creada correctamente.', 201);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // El detalle real queda en el log del servidor; al cliente solo un mensaje genérico.
    error_log('api/empresas/crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear la empresa.', 500);
}