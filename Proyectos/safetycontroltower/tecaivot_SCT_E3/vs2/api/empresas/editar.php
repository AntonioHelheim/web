<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/validation.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}
requireCsrfToken($input);

$idCompany = filter_var($input['id_company'] ?? null, FILTER_VALIDATE_INT);
if (!$idCompany) {
    responderJSON(false, null, 'Empresa no válida.', 400);
}
if (!empresasCanEdit($pdo, (int) $idCompany)) {
    responderJSON(false, null, 'No tienes permisos para editar esta empresa.', 403);
}

$faltantes = requerirCampos($input, ['razon_social', 'address', 'email']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}
if (!validarEmail((string) $input['email'])) {
    responderJSON(false, null, 'Correo electrónico no válido.', 400);
}

$datos = [
    'razon_social' => trim((string) $input['razon_social']),
    'address' => trim((string) $input['address']),
    'email' => strtolower(trim((string) $input['email'])),
];
if (mb_strlen($datos['razon_social']) > 150 || mb_strlen($datos['address']) > 255 || mb_strlen($datos['email']) > 50) {
    responderJSON(false, null, 'Uno o más campos superan el largo permitido.', 400);
}

try {
    $empresa = empresaObtenerPorId($pdo, (int) $idCompany);
    if (!$empresa) {
        responderJSON(false, null, 'Empresa no encontrada.', 404);
    }
    empresaActualizar($pdo, (int) $idCompany, $datos);
    auditTrailLogChanges($pdo, (int) $idCompany, 'empresas', 'company', (int) $idCompany,
        $empresa, array_merge($empresa, $datos), 'update', $datos['razon_social'],
        ['razon_social','address','email']);
    responderJSON(true, null, 'Empresa actualizada correctamente.');
} catch (PDOException $e) {
    error_log('api/empresas/editar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar la empresa.', 500);
}
