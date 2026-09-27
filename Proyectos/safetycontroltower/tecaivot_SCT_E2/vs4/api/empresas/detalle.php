<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
$idCompany = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idCompany) {
    responderJSON(false, null, 'Parámetro "id" inválido.', 400);
}
if (!empresasCanView($pdo, (int) $idCompany)) {
    responderJSON(false, null, 'No tienes permisos para ver esta empresa.', 403);
}

try {
    $empresa = empresaObtenerPorId($pdo, (int) $idCompany);
    if (!$empresa) {
        responderJSON(false, null, 'Empresa no encontrada.', 404);
    }
    responderJSON(true, $empresa);
} catch (PDOException $e) {
    error_log('api/empresas/detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener la empresa.', 500);
}
