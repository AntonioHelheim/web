<?php
require __DIR__ . '/common.php';
permisosRequireManageApi($pdo);

$idRoleGroup = (int) ($_GET['id_role_group'] ?? 0);
if ($idRoleGroup <= 0) responderJSON(false, null, 'Rol inválido.', 400);

try {
    $role = permisoObtenerRol($pdo, $idRoleGroup);
    if (!$role) responderJSON(false, null, 'Rol no encontrado.', 404);

    responderJSON(true, [
        'role' => $role,
        'permissions' => permisoListarCatalogo($pdo),
        'assigned_ids' => permisoListarIdsRol($pdo, $idRoleGroup),
        'protected_codes' => $role['canonical_name'] === 'administrador_completo'
            ? ['permissions.manage','companies.view_all','dashboard.global','dashboard.view']
            : [],
    ]);
} catch (Throwable $e) {
    error_log('permisos/permisos-listar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cargar la matriz de permisos.', 500);
}
