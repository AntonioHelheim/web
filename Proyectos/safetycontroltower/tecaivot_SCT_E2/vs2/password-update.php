<?php
/**
 * password-update.php
 * Finaliza activación/restablecimiento desde el flujo inline del index.
 *
 * El navegador solo recibe el token directamente en entorno local. Este
 * endpoint, de todos modos, exige token válido, CSRF, usuario activo y la
 * misma política de contraseña utilizada por restablecer-password.php.
 */
require __DIR__ . '/session_bootstrap.php';
aplicarCabecerasSeguridad();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Referrer-Policy: no-referrer');

require __DIR__ . '/lib/response.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/passwords.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    responderJSON(false, null, 'Solicitud inválida.', 400);
}

$csrfToken = (string) ($input['csrf_token'] ?? '');
$token = trim((string) ($input['token'] ?? ''));
$newPassword = (string) ($input['password'] ?? '');
$confirmPassword = (string) ($input['password_confirm'] ?? '');

if (
    $csrfToken === '' ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    responderJSON(false, null, 'Tu sesión expiró. Vuelve a iniciar la recuperación.', 403);
}

if ($token === '') {
    responderJSON(false, null, 'La solicitud de recuperación ya no es válida. Vuelve a solicitarla.', 400);
}

if (!hash_equals($newPassword, $confirmPassword)) {
    responderJSON(false, null, 'Las contraseñas no coinciden.', 400);
}

try {
    $tokenRow = passwordsFindValidToken($pdo, $token);
    if (!$tokenRow) {
        responderJSON(false, null, 'La solicitud venció o ya fue utilizada. Vuelve a solicitar una nueva.', 400);
    }

    $policyError = passwordsValidateNewPassword(
        $newPassword,
        (string) $tokenRow['id_users'],
        (string) ($tokenRow['rut'] ?? '')
    );
    if ($policyError !== null) {
        responderJSON(false, null, $policyError, 400);
    }

    $updatedUser = passwordsConsumeToken($pdo, $token, $newPassword);
    if ($updatedUser === null) {
        responderJSON(false, null, 'La solicitud venció o ya fue utilizada. Vuelve a solicitar una nueva.', 400);
    }

    // El cambio de contraseña es un hito de seguridad: rotamos el CSRF, pero
    // devolvemos el nuevo token para que el usuario pueda iniciar sesión sin
    // tener que recargar el index.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    responderJSON(true, [
        'email' => (string) $updatedUser,
        'csrf_token' => $_SESSION['csrf_token'],
    ], 'Contraseña actualizada correctamente.');

} catch (Throwable $e) {
    error_log('password-update.php: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible actualizar la contraseña. Intenta nuevamente.', 500);
}
