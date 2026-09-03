<?php
/**
 * GET /api/centros/empresas-disponibles.php
 * Ver el comentario en api/proyectos/empresas-disponibles.php — mismo
 * motivo para no reutilizar api/empresas/listar.php tal cual.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireRole($pdo, ['administrador', 'administrador_completo']);

try {
    $empresas = empresaListar($pdo);
    responderJSON(true, $empresas);
} catch (PDOException $e) {
    error_log('api/centros/empresas-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las empresas.', 500);
}
