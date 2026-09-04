<?php
/**
 * GET /api/dashboard/empresas-disponibles.php
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireRole($pdo, ['administrador', 'administrador_completo']);

try {
    responderJSON(true, empresaListar($pdo));
} catch (PDOException $e) {
    error_log('api/dashboard/empresas-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las empresas.', 500);
}
