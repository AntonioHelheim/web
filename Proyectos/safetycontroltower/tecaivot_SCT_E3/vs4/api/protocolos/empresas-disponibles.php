<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';
protocoloRequireGestionApi($pdo);

if (!protocoloIsGlobalAdmin($pdo)) {
    responderJSON(true, []);
}

try {
    responderJSON(true,empresaListar($pdo,null,false));
} catch (Throwable $e) {
    error_log('protocolos/empresas-disponibles: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar las empresas.', 500);
}
