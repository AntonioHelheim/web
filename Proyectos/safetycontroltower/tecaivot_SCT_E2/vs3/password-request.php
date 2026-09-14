<?php
/**
 * Solicitud pública de activación/restablecimiento de contraseña.
 *
 * Este endpoint es usado por el flujo inline del index. Existe además
 * recuperar-password.php como fallback sin JavaScript.
 *
 * La respuesta pública es deliberadamente genérica para no revelar si un
 * correo está registrado. Una cuenta activa, aunque sea histórica y todavía
 * no tenga fila en user_credentials, puede entrar al ciclo de recuperación.
 */
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/response.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/passwords.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$email = strtolower(trim((string) ($input['email'] ?? '')));
$csrfToken = (string) ($input['csrf_token'] ?? '');
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

if (
    empty($_SESSION['csrf_token']) ||
    $csrfToken === '' ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    responderJSON(false, null, 'Tu sesión expiró. Recarga la página e intenta nuevamente.', 403);
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || passwordsStringLength($email) > 50) {
    responderJSON(false, null, 'Ingresa un correo electrónico válido.', 400);
}

$genericMessage = 'Si el correo corresponde a una cuenta activa, recibirás un enlace para definir una nueva contraseña. Revisa también la carpeta de spam.';

try {
    // Rate limit por origen. No depende de que la cuenta exista.
    if (passwordsRecentRequestsByIp($pdo, $ip) >= PASSWORD_RESET_MAX_PER_IP) {
        responderJSON(false, null, 'Demasiadas solicitudes desde esta conexión. Intenta nuevamente más tarde.', 429);
    }

    $stmt = $pdo->prepare(
        'SELECT id_users
         FROM users
         WHERE id_users = :id_users
           AND state = 1
         LIMIT 1'
    );
    $stmt->execute(['id_users' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Misma respuesta que para una cuenta válida.
        responderJSON(true, null, $genericMessage);
    }

    $idUsers = (string) $user['id_users'];

    if (passwordsRecentRequestsByUser($pdo, $idUsers) >= PASSWORD_RESET_MAX_PER_USER) {
        // No revelar el estado/registro de la cuenta.
        responderJSON(true, null, $genericMessage);
    }

    // Repara de forma segura cuentas históricas que nunca recibieron una fila
    // en user_credentials. No establece ninguna contraseña automáticamente.
    $credential = passwordsEnsureCredentialRecord($pdo, $idUsers);
    $credentialStatus = passwordsNormalizeCredentialStatus($credential['credential_status'] ?? null);
    $purpose = passwordsPurposeForCredentialStatus($credentialStatus);
    $ttl = passwordsTtlForPurpose($purpose);

    $tokenData = passwordsCreateToken($pdo, $idUsers, $ip, $ttl, $purpose);

    if (passwordsIsLocal()) {
        // En desarrollo local no enviamos al usuario una URL técnica. El token
        // se entrega únicamente a la interfaz local para que pueda mostrar los
        // campos de nueva contraseña en el mismo flujo del index.
        responderJSON(true, [
            'local_token' => $tokenData['token'],
            'mode' => $purpose,
        ], $genericMessage);
    }

    if (!passwordsSendLinkEmail($idUsers, $tokenData['url'], $purpose)) {
        error_log('password-request.php: mail() no pudo aceptar el mensaje de recuperación para entrega.');
        // Mantener respuesta genérica. El administrador puede reintentar el
        // envío desde Gestión de Usuarios, donde sí se informa el fallo SMTP.
    }

    responderJSON(true, null, $genericMessage);

} catch (Throwable $e) {
    error_log('password-request.php: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible procesar la solicitud. Intenta nuevamente.', 500);
}
