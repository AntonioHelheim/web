<?php
/**
 * GET /api/empresas/listar.php
 *
 * Devuelve las empresas visibles para el usuario autenticado:
 * - administrador: todas las empresas activas.
 * - cliente / trabajador: únicamente su propia empresa (aislamiento
 *   multiempresa vía id_company).
 */

require __DIR__ . '/../../session_bootstrap.php';
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

empresasRequireGlobalAdminApi($pdo);

try {
    $empresas = empresaListar($pdo);

    responderJSON(true, $empresas);
} catch (PDOException $e) {
    error_log('api/empresas/listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las empresas.', 500);
}