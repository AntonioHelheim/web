<?php
require_once __DIR__ . '/common.php';

requireLogin();
$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);

$valid = ['achs','mutual','ist','isl'];
$code = strtolower(trim((string)($input['mutual_code'] ?? '')));
if (!in_array($code, $valid, true)) {
    responderJSON(false, null, t('nav_mutuality_error'), 422);
}

$idUsers = currentUserId();
if (!$idUsers) responderJSON(false, null, t('nav_mutuality_error'), 401);

try {
    if (!authColumnExists($pdo, 'users', 'mutual_code')) {
        responderJSON(false, null, t('nav_mutuality_error'), 503);
    }
    $stmt = $pdo->prepare('SELECT id_company, mutual_code FROM users WHERE id_users = :id_users AND state = 1 LIMIT 1');
    $stmt->execute(['id_users' => $idUsers]);
    $before = $stmt->fetch();
    if (!$before) responderJSON(false, null, t('nav_mutuality_error'), 404);

    $update = $pdo->prepare('UPDATE users SET mutual_code = :mutual_code, last_update = NOW() WHERE id_users = :id_users');
    $update->execute(['mutual_code' => $code, 'id_users' => $idUsers]);

    auditTrailLogAction(
        $pdo,
        (int)$before['id_company'],
        'usuarios',
        'users',
        $idUsers,
        'mutual_code',
        $before['mutual_code'] ?? null,
        $code,
        'update',
        $idUsers,
        $idUsers
    );
    responderJSON(true, ['mutual_code' => $code], t('nav_mutuality_saved'));
} catch (Throwable $e) {
    error_log('api/usuarios/mutualidad.php: ' . $e->getMessage());
    responderJSON(false, null, t('nav_mutuality_error'), 500);
}
