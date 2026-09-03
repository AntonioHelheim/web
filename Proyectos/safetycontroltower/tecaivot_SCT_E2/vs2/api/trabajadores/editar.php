<?php
/**
 * POST /api/trabajadores/editar.php
 * Body JSON: { id_worker, name, lastname, email, phone, position, csrf_token }
 *
 * El RUT no se edita acá a propósito: es lo que identifica al trabajador
 * frente a la empresa (uq_worker_rut_company) y ya puede estar
 * referenciado desde otros módulos (worker_projects, eventos, etc.) —
 * cambiarlo en caliente es más riesgo que beneficio para un dato que
 * casi nunca cambia. Si alguna vez hace falta corregir un RUT mal
 * tipeado, mejor dar de baja el registro y crear uno nuevo.
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

$idWorker = filter_var($input['id_worker'] ?? null, FILTER_VALIDATE_INT);
if (!$idWorker) {
    responderJSON(false, null, 'Trabajador no válido.', 400);
}

try {
    $trabajador = trabajadorObtenerPorId($pdo, $idWorker);
    if (!$trabajador) {
        responderJSON(false, null, 'Trabajador no encontrado.', 404);
    }

    if (!trabajadoresIsGlobalAdmin($pdo) && (int) $trabajador['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para editar este trabajador.', 403);
    }

    $faltantes = requerirCampos($input, ['name', 'lastname']);
    if ($faltantes) {
        responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
    }

    $email = trim((string) ($input['email'] ?? ''));
    if ($email !== '' && !validarEmail($email)) {
        responderJSON(false, null, 'El correo ingresado no es válido.', 400);
    }

    $datos = [
        'name'     => sanitizarTexto((string) $input['name']),
        'lastname' => sanitizarTexto((string) $input['lastname']),
        'email'    => sanitizarTexto($email),
        'phone'    => sanitizarTexto((string) ($input['phone'] ?? '')),
        'position' => sanitizarTexto((string) ($input['position'] ?? '')),
    ];

    foreach (['name' => 100, 'lastname' => 100, 'position' => 100, 'phone' => 20] as $campo => $largoMax) {
        if (mb_strlen($datos[$campo]) > $largoMax) {
            responderJSON(false, null, "El campo \"$campo\" no puede superar los $largoMax caracteres.", 400);
        }
    }

    trabajadorActualizar($pdo, $idWorker, $datos);

    responderJSON(true, null, 'Trabajador actualizado correctamente.');
} catch (PDOException $e) {
    error_log('api/trabajadores/editar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar el trabajador.', 500);
}
