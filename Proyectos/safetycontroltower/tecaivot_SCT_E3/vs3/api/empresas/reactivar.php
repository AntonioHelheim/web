<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireCapability($pdo, 'companies.state_all');
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

try {
    $empresa = empresaObtenerPorId($pdo, (int) $idCompany);
    if (!$empresa) {
        responderJSON(false, null, 'Empresa no encontrada.', 404);
    }
    empresaReactivar($pdo, (int) $idCompany);
    auditTrailLogAction($pdo, (int) $idCompany, 'empresas', 'company', (int) $idCompany,
        'state', $empresa['state'], 1, 'state_change', (string) $empresa['razon_social']);
    responderJSON(true, ['state' => 1], 'Empresa reactivada correctamente.');
} catch (PDOException $e) {
    error_log('api/empresas/reactivar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo reactivar la empresa.', 500);
}
