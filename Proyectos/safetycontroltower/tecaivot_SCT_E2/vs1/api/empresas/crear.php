<?php
/**
 * POST /api/empresas/crear.php
 * Body JSON: { rut, razon_social, address, email, csrf_token }
 *
 * Solo administrador puede registrar empresas nuevas.
 */

require __DIR__ . '/../../session_bootstrap.php';
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

empresasRequireGlobalAdminApi($pdo);

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

    $roles = [
        ['name' => 'administrador_completo', 'description' => 'Administrador global'],
        ['name' => 'administrador', 'description' => 'Acceso completo a la plataforma'],
        ['name' => 'cliente', 'description' => 'Representante de la empresa cliente'],
        ['name' => 'jefatura', 'description' => 'Jefatura de empresa'],
        ['name' => 'trabajador', 'description' => 'Trabajador en terreno'],
    ];

    $insertRoleStmt = $pdo->prepare(
        'INSERT INTO users_role_group (id_company, name, description, state, create_by, date_create, last_update)
         SELECT :id_company, :name, :description, 1, :create_by, NOW(), NOW()
         FROM DUAL
         WHERE NOT EXISTS (
             SELECT 1
             FROM users_role_group
             WHERE id_company = :id_company_check
               AND name = :name_check
         )'
    );

    foreach ($roles as $role) {
        $insertRoleStmt->execute([
            'id_company' => $idNuevo,
            'name' => $role['name'],
            'description' => $role['description'],
            'create_by' => currentUserId(),
            'id_company_check' => $idNuevo,
            'name_check' => $role['name'],
        ]);
    }

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