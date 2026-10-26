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
 * E3 VS3 — autogestión transversal del formulario personal.
 *
 * Todos los grupos de perfil autenticados pueden consultar y actualizar
 * exclusivamente SU propio formulario. Los endpoints que usan este helper
 * resuelven siempre currentUserProfile() y no reciben un id_users objetivo.
 *
 * Esta política NO concede lectura/escritura sobre formularios de terceros;
 * esos alcances deben implementarse, si corresponden, mediante endpoints
 * separados con autorización específica.
 */
function saludCanUseOwnForm(PDO $pdo): bool
{
    return currentUserId() !== null;
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
