<?php
/** Baja lógica de empresa: state=0. Nunca elimina físicamente. */
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
    if ((int) $empresa['state'] === 0) {
        responderJSON(true, ['state' => 0], 'La empresa ya se encuentra inactiva.');
    }

    // No permitir dar de baja la empresa a la que pertenece el super admin
    // que ejecuta la operación: evita dejar su propia cuenta sin contexto.
    if (currentUserCompanyId($pdo) === (int) $idCompany) {
        responderJSON(false, null, 'No puedes dar de baja la empresa asociada a tu propia cuenta.', 409);
    }

    empresaDesactivar($pdo, (int) $idCompany);
    auditTrailLogAction($pdo, (int) $idCompany, 'empresas', 'company', (int) $idCompany,
        'state', $empresa['state'], 0, 'state_change', (string) $empresa['razon_social']);
    responderJSON(true, ['state' => 0], 'Empresa dada de baja correctamente.');
} catch (PDOException $e) {
    error_log('api/empresas/baja.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo dar de baja la empresa.', 500);
}
