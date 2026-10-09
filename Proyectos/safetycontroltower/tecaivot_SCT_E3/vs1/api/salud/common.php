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
    if ($raw === false || trim($raw) === '') return $_POST;
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

function saludRequireWorker(PDO $pdo): array
{
    requireLogin();
    $roles = currentUserRoles($pdo);
    if (!array_intersect($roles, HEALTH_FORM_ROLES)) {
        responderJSON(false, null, t('health_no_permission'), 403);
    }
    $profile = currentUserProfile($pdo);
    if (!$profile) responderJSON(false, null, t('health_profile_not_found'), 404);
    return $profile;
}
