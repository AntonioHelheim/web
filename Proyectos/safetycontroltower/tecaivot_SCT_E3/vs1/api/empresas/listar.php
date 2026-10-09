<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireLogin();
if (!currentUserHasAnyCapability($pdo, ['companies.view_all','companies.view_own'])) {
    responderJSON(false, null, 'No tienes permisos para consultar empresas.', 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

try {
    $isGlobal = currentUserHasCapability($pdo, 'companies.view_all');
    $companyId = $isGlobal ? null : currentUserCompanyId($pdo);
    if (!$isGlobal && !$companyId) responderJSON(false, null, 'Tu cuenta no tiene una empresa asociada.', 403);
    $rows = empresaListar($pdo, $companyId, $isGlobal);
    foreach ($rows as &$row) {
        $id = (int)$row['id_company'];
        $row['can_edit'] = empresasCanEdit($pdo, $id);
        $row['can_state'] = currentUserHasCapability($pdo, 'companies.state_all');
    }
    unset($row);
    responderJSON(true, $rows);
} catch (PDOException $e) {
    error_log('api/empresas/listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las empresas.', 500);
}
