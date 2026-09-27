<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $requestedCompanyId = isset($_GET['id_company']) ? (int) $_GET['id_company'] : null;
    if ($requestedCompanyId !== null && $requestedCompanyId <= 0) {
        $requestedCompanyId = null;
    }

    if ($requestedCompanyId !== null && empty($context['is_global_admin']) && $requestedCompanyId !== (int) $context['company_id']) {
        responderJSON(false, null, 'No puedes consultar roles de otra empresa.', 403);
    }

    responderJSON(true, [
        'context' => [
            'current_user_id' => (string) $context['session_user_id'],
            'company_id' => (int) $context['company_id'],
            'actor_level' => (int) $context['actor_level'],
            'primary_role' => (string) $context['primary_role'],
            'is_global_admin' => (bool) $context['is_global_admin'],
            'can_create_user' => authCanCreateUsers($context),
            'editable_self_fields' => ['name', 'lastname', 'language', 'profile_photo_path'],
        ],
        'companies' => usuariosListVisibleCompanies($pdo, $context),
        'role_options' => usuariosFindAssignableRoles($pdo, $context, $requestedCompanyId),
    ]);
} catch (PDOException $e) {
    error_log('api/usuarios/roles.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al cargar roles.', 500);
}
