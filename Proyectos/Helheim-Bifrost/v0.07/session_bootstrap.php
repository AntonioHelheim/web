<?php
/**
 * session_bootstrap.php
 *
 * Arranca la sesión de forma consistente en todo Bifrost:
 * - Cookie httponly / secure (si hay HTTPS) / samesite=Strict
 * - Cierre automático por inactividad (SESSION_IDLE_TIMEOUT)
 * - Token CSRF listo para usar en formularios (login, registro)
 * - $ASSET_VERSION centralizado aquí (antes vivía duplicado en
 *   index.php y game.php — bastaba con olvidar subirlo en uno de los
 *   dos para volver a servir una versión vieja en caché)
 *
 * Inclúyelo con require al inicio de CUALQUIER archivo .php que
 * necesite sesión: páginas del sitio (index.php, game.php,
 * acceso-denegado.php) y api/config.php (que lo usan a su vez todos
 * los endpoints de la API).
 */

// Sube este valor cada vez que actualices archivos .js/.css y los subas,
// para forzar que el navegador (y el hosting) descarten la versión en
// caché en vez de seguir usando una copia vieja. Es la causa más probable
// de "hice el cambio pero se sigue viendo/comportando igual". Al vivir
// acá (un solo lugar), ya no hay riesgo de subirlo en un archivo y
// olvidarlo en otro.
$ASSET_VERSION = '2026-08-30-11';

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
        // Reinicia una sesión vacía para poder setear el flag de expirada,
        // que acceso-denegado.php usa para mostrar el mensaje correcto.
        session_start();
        $_SESSION['session_expired'] = true;
    } else {
        $_SESSION['last_activity'] = time();
    }
}

// --- Token CSRF: uno por sesión, reutilizado en todos los formularios ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Cabeceras de seguridad básicas para páginas HTML autenticadas o públicas.
 * Llamar explícitamente donde corresponda (los endpoints de la API que
 * responden JSON no la necesitan, ya tienen su propio Content-Type).
 */
function aplicarCabecerasSeguridad(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
