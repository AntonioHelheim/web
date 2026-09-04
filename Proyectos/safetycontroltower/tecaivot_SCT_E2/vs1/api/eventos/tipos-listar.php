<?php
/**
 * GET /api/eventos/tipos-listar.php
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireReportarApi($pdo);

try {
    responderJSON(true, eventoTipoListar($pdo, 'seguridad'));
} catch (PDOException $e) {
    error_log('api/eventos/tipos-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los tipos de evento.', 500);
}
