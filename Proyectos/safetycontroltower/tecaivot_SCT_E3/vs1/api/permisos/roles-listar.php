<?php
require __DIR__ . '/common.php';
$context = permisosRequireManageApi($pdo);
$idCompany = (int) ($_GET['id_company'] ?? 0);
permisosAssertCompanyScope($context, $idCompany);
try {
    $rows = permisoListarRolesEmpresa($pdo, $idCompany);
    if (empty($context['is_global_admin'])) {
        $managed = authManagedUserLevels($context);
        $rows = array_values(array_filter($rows, static fn($r)=>in_array(roleLevelFromName((string)$r['canonical_name']), $managed, true)));
    }
    responderJSON(true, $rows);
} catch (Throwable $e) {
    error_log('permisos/roles-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar los roles.', 500);
}
