<?php
/**
 * GET /api/empresas/detalle.php?id=7
 *
 * Devuelve una empresa por id. cliente/trabajador solo pueden ver su
 * propia empresa; administrador puede ver cualquiera.
 */

require __DIR__ . '/../../session_bootstrap.php';
require __DIR__ . '/../../lib/db.php';
require __DIR__ . '/../../lib/auth.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireRole($pdo, ['administrador', 'cliente', 'trabajador']);

$idCompany = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idCompany) {
    responderJSON(false, null, 'Parámetro "id" inválido.', 400);
}

$esAdministrador = in_array('administrador', currentUserRoles($pdo), true);
if (!$esAdministrador && $idCompany !== currentUserCompanyId($pdo)) {
    // No se distingue "no existe" de "no es tuya": ambos casos devuelven
    // 403 genérico, para no filtrar qué IDs de empresa existen en el sistema.
    responderJSON(false, null, 'No tienes permisos para ver esta empresa.', 403);
}

try {
    $empresa = empresaObtenerPorId($pdo, $idCompany);
    if (!$empresa) {
        responderJSON(false, null, 'Empresa no encontrada.', 404);
    }
    responderJSON(true, $empresa);
} catch (PDOException $e) {
    error_log('api/empresas/detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener la empresa.', 500);
}