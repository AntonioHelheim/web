<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/passwords.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);
$idUsers = strtolower(trim((string) ($input['id_users'] ?? '')));

if (!filter_var($idUsers, FILTER_VALIDATE_EMAIL) || mb_strlen($idUsers) > 50) {
    responderJSON(false, null, 'Usuario objetivo inválido.', 400);
}

try {
    $context = usuariosRequireAccessContext($pdo);
    if (empty($context['can_access_users'])) responderJSON(false, null, 'No tienes permisos para gestionar accesos de usuarios.', 403);
    $targetUser = usuariosFindUserWithAccess($pdo, $idUsers);
    if (!$targetUser) {
        responderJSON(false, null, 'Usuario no encontrado.', 404);
    }

    $target = [
        'id_users' => $idUsers,
        'id_company' => (int) $targetUser['id_company'],
        'access_level' => $targetUser['access_level'],
    ];
    if (!authCanManageUserTarget($context, $target)) {
        responderJSON(false, null, 'No tienes permisos para gestionar el acceso de este usuario.', 403);
    }
    if ((int) $targetUser['state'] !== 1) {
        responderJSON(false, null, 'Activa la cuenta antes de enviar un enlace de acceso.', 400);
    }

    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!passwordsCanIssueToken($pdo, $idUsers, $ip)) {
        responderJSON(false, null, 'Se alcanzó el límite temporal de enlaces para esta cuenta. Intenta más tarde.', 429);
    }

    $credential = passwordsEnsureCredentialRecord($pdo, $idUsers);
    $credentialStatus = passwordsNormalizeCredentialStatus($credential['credential_status'] ?? null);
    $purpose = passwordsPurposeForCredentialStatus($credentialStatus);
    $ttl = passwordsTtlForPurpose($purpose);
    $tokenData = passwordsCreateToken($pdo, $idUsers, $ip, $ttl, $purpose);
    $activation = $purpose === PASSWORD_PURPOSE_ACTIVATION;

    $data = [
        'id_users' => $idUsers,
        'mode' => $activation ? 'activation' : 'reset',
        'email_sent' => false,
    ];
    auditTrailLogAction($pdo, (int)$targetUser['id_company'], 'usuarios', 'users', $idUsers, 'access_process',
        null, $data['mode'], 'issue_access', trim((string)$targetUser['name'].' '.(string)$targetUser['lastname']),
        (string)$context['session_user_id']);

    if (passwordsIsLocal()) {
        // Solo para depuración. La UI deliberadamente no muestra esta URL.
        $data['dev_url'] = $tokenData['url'];
        responderJSON(true, $data, 'En desarrollo, el usuario puede completar el proceso desde “¿Olvidaste tu contraseña?”.');
    }

    $sent = passwordsSendLinkEmail($idUsers, $tokenData['url'], $purpose);
    $data['email_sent'] = $sent;
    if (!$sent) {
        responderJSON(false, $data, 'El enlace fue generado, pero no fue posible enviar el correo. Intenta nuevamente.', 502);
    }

    responderJSON(true, $data, $activation
        ? 'Enlace de activación enviado correctamente.'
        : 'Enlace de restablecimiento enviado correctamente.');
} catch (Throwable $e) {
    error_log('api/usuarios/enviar-acceso.php: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible generar el enlace de acceso.', 500);
}
