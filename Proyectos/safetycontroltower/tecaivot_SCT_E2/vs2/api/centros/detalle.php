<?php
/**
 * GET /api/centros/detalle.php?id=7
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/CentroRepository.php';

centrosRequireLecturaApi($pdo);

$idCentro = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idCentro) {
    responderJSON(false, null, 'Parámetro "id" inválido.', 400);
}

try {
    $centro = centroObtenerPorId($pdo, $idCentro);
    if (!$centro) {
        responderJSON(false, null, 'Centro/sede no encontrado.', 404);
    }

    if (!centrosIsGlobalAdmin($pdo) && (int) $centro['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para ver este centro/sede.', 403);
    }

    responderJSON(true, $centro);
} catch (PDOException $e) {
    error_log('api/centros/detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener el centro/sede.', 500);
}
