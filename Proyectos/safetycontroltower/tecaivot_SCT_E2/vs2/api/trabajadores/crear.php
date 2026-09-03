<?php
/**
 * POST /api/trabajadores/crear.php
 * Body JSON: { id_company (solo admin), rut, name, lastname, email,
 *              phone, position, csrf_token }
 *
 * La foto NO se sube acá (este endpoint es JSON, no multipart) — se
 * sube después con subir-foto.php sobre el trabajador ya creado.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/TrabajadorRepository.php';

trabajadoresRequireGestionApi($pdo);

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
$idCompany = trabajadoresResolveCompanyId($pdo, $idCompanySolicitado);

$faltantes = requerirCampos($input, ['rut', 'name', 'lastname']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

if (!validarRutChileno((string) $input['rut'])) {
    responderJSON(false, null, 'El RUT ingresado no es válido.', 400);
}

$email = trim((string) ($input['email'] ?? ''));
if ($email !== '' && !validarEmail($email)) {
    responderJSON(false, null, 'El correo ingresado no es válido.', 400);
}

$datos = [
    'id_company' => $idCompany,
    'rut'        => sanitizarTexto((string) $input['rut']),
    'name'       => sanitizarTexto((string) $input['name']),
    'lastname'   => sanitizarTexto((string) $input['lastname']),
    'email'      => sanitizarTexto($email),
    'phone'      => sanitizarTexto((string) ($input['phone'] ?? '')),
    'position'   => sanitizarTexto((string) ($input['position'] ?? '')),
];

foreach (['name' => 100, 'lastname' => 100, 'position' => 100, 'phone' => 20] as $campo => $largoMax) {
    if (mb_strlen($datos[$campo]) > $largoMax) {
        responderJSON(false, null, "El campo \"$campo\" no puede superar los $largoMax caracteres.", 400);
    }
}

try {
    if (trabajadorExisteRutEnEmpresa($pdo, $idCompany, $datos['rut'])) {
        responderJSON(false, null, 'Ya existe un trabajador con ese RUT en esta empresa.', 409);
    }

    $idNuevo = trabajadorCrear($pdo, $datos, currentUserId());

    responderJSON(true, ['id_worker' => $idNuevo], 'Trabajador creado correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/trabajadores/crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear el trabajador.', 500);
}
