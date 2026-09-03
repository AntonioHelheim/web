<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

function empresasIsGlobalAdmin(PDO $pdo): bool
{
    $roles = currentUserRoles($pdo);
    return in_array('administrador_completo', $roles, true);
}

function empresasRequireGlobalAdminApi(PDO $pdo): void
{
    requireLogin();

    if (!empresasIsGlobalAdmin($pdo)) {
        responderJSON(false, null, 'No tienes permisos para gestionar empresas.', 403);
    }
}

function empresasRequireGlobalAdminPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireLoginPage($redirectTo);

    if (!empresasIsGlobalAdmin($pdo)) {
        header('Location: ' . $redirectTo);
        exit;
    }
}
