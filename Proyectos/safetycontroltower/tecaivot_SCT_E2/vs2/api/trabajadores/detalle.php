<?php
/**
 * GET /api/trabajadores/detalle.php?id=7
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/TrabajadorRepository.php';

trabajadoresRequireLecturaApi($pdo);

$idWorker = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idWorker) {
    responderJSON(false, null, 'Parámetro "id" inválido.', 400);
}

try {
    $trabajador = trabajadorObtenerPorId($pdo, $idWorker);
    if (!$trabajador) {
        responderJSON(false, null, 'Trabajador no encontrado.', 404);
    }

    if (!trabajadoresIsGlobalAdmin($pdo) && (int) $trabajador['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para ver este trabajador.', 403);
    }

    responderJSON(true, $trabajador);
} catch (PDOException $e) {
    error_log('api/trabajadores/detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener el trabajador.', 500);
}
