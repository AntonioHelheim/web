<?php
require __DIR__ . '/common.php';
protocoloRequireGestionApi($pdo);

if (!protocoloIsGlobalAdmin($pdo)) {
    responderJSON(true, []);
}

try {
    $stmt = $pdo->query(
        'SELECT id_company,razon_social,state FROM company '
        . 'WHERE state=1 ORDER BY razon_social ASC'
    );
    responderJSON(true, $stmt->fetchAll());
} catch (Throwable $e) {
    error_log('protocolos/empresas-disponibles: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar las empresas.', 500);
}
