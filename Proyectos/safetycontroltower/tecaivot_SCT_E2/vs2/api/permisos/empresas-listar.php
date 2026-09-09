<?php
require __DIR__ . '/common.php';
permisosRequireManageApi($pdo);

try {
    responderJSON(true, permisoListarEmpresas($pdo));
} catch (Throwable $e) {
    error_log('permisos/empresas-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar las empresas.', 500);
}
