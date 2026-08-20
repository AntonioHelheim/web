<?php
/**
 * session_bootstrap.php
 * Arranca la sesión de forma consistente en todo el sitio:
 * - Cookie httponly / secure (si hay HTTPS) / samesite=Strict
 * - Cierre automático por inactividad (SESSION_IDLE_TIMEOUT)
 *
 * Inclúyelo con require al inicio de CUALQUIER archivo .php que
 * necesite sesión (login.php, bienvenida.php, futuras páginas privadas).
 */

const SESSION_IDLE_TIMEOUT = 1800; // 30 minutos de inactividad -> cierre automático

if (session_status() === PHP_SESSION_NONE) {

    $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $esHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

// --- Cierre automático por inactividad ---
if (!empty($_SESSION['logged_in'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_IDLE_TIMEOUT)) {
        $_SESSION = [];
        session_destroy();
        // Reinicia una sesión vacía para poder setear el flag de expirada
        session_start();
        $_SESSION['session_expired'] = true;
    } else {
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Cabeceras de seguridad básicas para páginas autenticadas.
 * Llamar explícitamente donde corresponda (no todas las páginas la necesitan,
 * por ejemplo login.php responde JSON y no necesita X-Frame-Options).
 */
function aplicarCabecerasSeguridad(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}