<?php
/**
 * GET /api/induccion/empresas-disponibles.php
 * Ver el comentario equivalente en api/proyectos/empresas-disponibles.php.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireRole($pdo, ['administrador', 'administrador_completo']);

try {
    $empresas = empresaListar($pdo);
    responderJSON(true, $empresas);
} catch (PDOException $e) {
    error_log('api/induccion/empresas-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las empresas.', 500);
}
