<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

try {
    $context = usuariosRequireAccessContext($pdo);

    if (!usuariosCanAssignSupervision($context)) {
        responderJSON(false, null, 'No tienes permisos para administrar asignaciones de usuarios.', 403);
    }

    $companyId = isset($_GET['id_company'])
        ? (int)$_GET['id_company']
        : (int)$context['company_id'];

    if ($companyId <= 0) {
        responderJSON(false, null, 'Empresa inválida.', 400);
    }

    if (
        empty($context['is_global_admin'])
        && $companyId !== (int)$context['company_id']
    ) {
        responderJSON(false, null, 'No puedes consultar responsables de otra empresa.', 403);
    }

    $rows = usuariosSupervisionCandidates($pdo,$context,$companyId);

    $data = array_map(static function (array $row): array {
        $role = canonicalRoleName((string)$row['role_name']);
        return [
            'id_users'=>(string)$row['id_users'],
            'name'=>(string)$row['name'],
            'lastname'=>(string)$row['lastname'],
            'role_name'=>$role,
            'role_label'=>roleDisplayLabel($role),
        ];
    }, $rows);

    responderJSON(true, ['supervisors'=>$data]);
} catch (PDOException $e) {
    error_log('api/usuarios/supervisores.php: '.$e->getMessage());
    responderJSON(false, null, 'Error al cargar responsables.', 500);
}
