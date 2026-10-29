<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.execute');

try {
    protocoloRequireSchema($pdo);
    responderJSON(true, protocoloAsignacionesUsuario($pdo, (string) currentUserId()));
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/mis-asignaciones: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar tus protocolos.', 500);
}
