<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/repositorios/SaludRepository.php';
require_once __DIR__ . '/../../i18n.php';

function saludReadJsonInput(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        return $_POST;
    }

    $decoded = json_decode($raw, true);

    return is_array($decoded)
        ? $decoded
        : $_POST;
}

/**
 * E3 VS2 — acceso al formulario personal.
 *
 * HEALTH_FORM_ROLES mantiene la política de usuarios a quienes el sistema
 * puede exigir el formulario mediante el health gate.
 *
 * Administrador Cliente y Jefatura se agregan aquí sólo como acceso
 * voluntario a SU propio formulario. Los endpoints
 * formulario-obtener/formulario-guardar resuelven siempre
 * currentUserProfile() y no admiten un id_users objetivo, por lo que esta
 * excepción no habilita lectura/escritura de salud de terceros.
 */
function saludCanUseOwnForm(PDO $pdo): bool
{
    foreach (currentUserRoles($pdo) as $role) {
        $canonical = canonicalRoleName((string)$role);

        if (in_array($canonical, HEALTH_FORM_ROLES, true)) {
            return true;
        }

        if (
            $canonical === 'administrador_cliente'
            || $canonical === 'administrador'
            || $canonical === 'jefatura'
        ) {
            return true;
        }
    }

    return false;
}

function saludRequireWorker(PDO $pdo): array
{
    requireLogin();

    if (!saludCanUseOwnForm($pdo)) {
        responderJSON(
            false,
            null,
            t('health_no_permission'),
            403
        );
    }

    $profile = currentUserProfile($pdo);

    if (!$profile) {
        responderJSON(
            false,
            null,
            t('health_profile_not_found'),
            404
        );
    }

    return $profile;
}
