<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/sct-notifications.php';
require_once __DIR__ . '/../../i18n.php';

requireLogin();
aplicarCabecerasSeguridad();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$context = resolveCurrentUserAccessContext($pdo);
if ($context === null) {
    responderJSON(false, null, 'Tu cuenta no tiene un rol activo válido.', 403);
}

$userId = (string) ($_SESSION['user_email'] ?? '');
$roles = isset($context['actor_roles']) && is_array($context['actor_roles']) ? $context['actor_roles'] : [];
$profile = isset($context['profile']) && is_array($context['profile']) ? $context['profile'] : [];

$items = sctBuildPendingNotifications($pdo, $userId, $roles, $profile, '../../');
$count = 0;
foreach ($items as $item) {
    $count += max(1, (int) ($item['count'] ?? 1));
}

responderJSON(true, [
    'items' => $items,
    'count' => $count,
    'badge' => $count > 99 ? '99+' : (string) $count,
], 'OK');
