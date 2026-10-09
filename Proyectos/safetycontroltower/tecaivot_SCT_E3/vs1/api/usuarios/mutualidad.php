<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../app/Config/MutualityOptions.php';

requireLogin();
$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);

$code = strtolower(trim((string)($input['mutual_code'] ?? '')));
if (!SctMutualityOptions::isValid($code)) {
    responderJSON(false, null, t('nav_mutuality_error'), 422);
}

$idUsers = currentUserId();
if (!$idUsers) responderJSON(false, null, t('nav_mutuality_error'), 401);

try {
    if (!authColumnExists($pdo, 'users', 'mutual_code')) {
        responderJSON(false, null, t('nav_mutuality_error'), 503);
    }
    $repo = usuariosRepository($pdo);
    $before = $repo->findCompanyAndMutual($idUsers);
    if (!$before) responderJSON(false, null, t('nav_mutuality_error'), 404);

    $repo->setMutualCode($idUsers, $code);

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
