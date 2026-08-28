<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

try {
    $context = usuariosRequireAccessContext($pdo);

    $requestedCompanyId = isset($_GET['id_company']) ? (int) $_GET['id_company'] : null;
    if ($requestedCompanyId !== null && $requestedCompanyId <= 0) {
        $requestedCompanyId = null;
    }

    $companies = usuariosListVisibleCompanies($pdo, $context);
    $roles = usuariosFindAssignableRoles($pdo, $context, $requestedCompanyId);

    $data = [
        'context' => [
            'current_user_id' => (string) $context['session_user_id'],
            'company_id' => (int) $context['company_id'],
            'actor_level' => (int) $context['actor_level'],
            'actor_label' => (string) $context['actor_label'],
            'is_global_admin' => (bool) $context['is_global_admin'],
            'can_create_user' => (bool) $context['can_create_user'],
            'editable_self_fields' => ((int) $context['actor_level'] === 5) ? ['name', 'lastname', 'language'] : ['name', 'lastname', 'rut', 'language'],
        ],
        'companies' => $companies,
        'role_options' => $roles,
    ];

    usuariosJsonResponse(200, true, '', $data);
} catch (PDOException $e) {
    error_log('php/usuarios/roles.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al cargar roles.');
}
