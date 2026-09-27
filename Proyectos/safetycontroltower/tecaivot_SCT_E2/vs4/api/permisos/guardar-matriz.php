<?php
require __DIR__ . '/common.php';
permisosRequireManageApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
$input = permisosReadJson();
requireCsrfToken($input);

$idRoleGroup = (int) ($input['id_role_group'] ?? 0);
$permissionIds = $input['permission_ids'] ?? [];
if ($idRoleGroup <= 0 || !is_array($permissionIds)) {
    responderJSON(false, null, 'Datos de permisos inválidos.', 400);
}

try {
    $role = permisoObtenerRol($pdo, $idRoleGroup);
    if (!$role) responderJSON(false, null, 'El rol seleccionado no existe o no está activo.', 400);
    $beforeIds = permisoListarIdsRol($pdo, $idRoleGroup);
    $result = permisoGuardarMatrizRol($pdo, $idRoleGroup, $permissionIds, (string) currentUserId());
    $afterIds = permisoListarIdsRol($pdo, $idRoleGroup);
    $added = array_values(array_diff($afterIds, $beforeIds));
    $removed = array_values(array_diff($beforeIds, $afterIds));
    $codes = permisoCodigosPorIds($pdo, array_merge($added, $removed));
    $requestId = auditTrailRequestId();
    foreach ($added as $idPermission) {
        auditTrailLogAction($pdo, (int)$role['id_company'], 'permisos', 'role_permissions', $idRoleGroup,
            'permission', null, $codes[$idPermission] ?? (string)$idPermission, 'assign', (string)$role['display_name'], (string)currentUserId(), $requestId);
    }
    foreach ($removed as $idPermission) {
        auditTrailLogAction($pdo, (int)$role['id_company'], 'permisos', 'role_permissions', $idRoleGroup,
            'permission', $codes[$idPermission] ?? (string)$idPermission, null, 'unassign', (string)$role['display_name'], (string)currentUserId(), $requestId);
    }
    responderJSON(true, $result, 'Matriz de permisos actualizada correctamente.');
} catch (RuntimeException $e) {
    responderJSON(false, null, $e->getMessage(), 400);
} catch (Throwable $e) {
    error_log('permisos/guardar-matriz: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar la matriz de permisos.', 500);
}
