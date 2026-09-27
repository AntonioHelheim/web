<?php
require __DIR__ . '/../../session_bootstrap.php';
require __DIR__ . '/../../lib/db.php';
require __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/welcome-live-data.php';

requireLoginPage();
aplicarCabecerasSeguridad();
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    $profile = currentUserProfile($pdo) ?: [];
    $roles = currentUserRoles($pdo);
    $role = 'trabajador';
    foreach (['administrador_completo', 'administrador', 'jefatura', 'cliente', 'trabajador'] as $candidate) {
        if (in_array($candidate, $roles, true)) {
            $role = $candidate;
            break;
        }
    }

    if ($role === 'trabajador') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vista no disponible para este perfil.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    echo json_encode(['success' => true, 'data' => sctBuildWelcomeLiveData($pdo, $role, $profile, $roles)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('api/usuarios/bienvenida-vivo.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar Inicio.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
