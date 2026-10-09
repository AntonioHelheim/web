<?php
require __DIR__ . '/common.php';
$context = permisosRequireManageApi($pdo);
try {
    if (!empty($context['is_global_admin'])) {
        $rows = permisoListarEmpresas($pdo);
    } else {
        $rows = array_values(array_filter(permisoListarEmpresas($pdo), static fn($r)=>(int)$r['id_company']===(int)$context['company_id']));
    }
    responderJSON(true, $rows);
} catch (Throwable $e) {
    error_log('permisos/empresas-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar las empresas.', 500);
}
