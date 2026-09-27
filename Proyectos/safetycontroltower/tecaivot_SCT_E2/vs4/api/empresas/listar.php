<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireCapability($pdo, 'companies.view_all');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

try {
    responderJSON(true, empresaListar($pdo, null, true));
} catch (PDOException $e) {
    error_log('api/empresas/listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las empresas.', 500);
}
