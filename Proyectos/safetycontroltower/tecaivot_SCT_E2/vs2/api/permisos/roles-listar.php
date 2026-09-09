<?php
require __DIR__ . '/common.php';
permisosRequireManageApi($pdo);

$idCompany = (int) ($_GET['id_company'] ?? 0);
if ($idCompany <= 0) responderJSON(false, null, 'Empresa inválida.', 400);

try {
    responderJSON(true, permisoListarRolesEmpresa($pdo, $idCompany));
} catch (Throwable $e) {
    error_log('permisos/roles-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar los roles.', 500);
}
