<?php
require __DIR__ . '/common.php';
historialRequireViewApi($pdo);
if (!historialIsGlobal($pdo)) {
    responderJSON(false, null, 'No tienes permisos para consultar empresas globalmente.', 403);
}
responderJSON(true, historialListarEmpresas($pdo));
