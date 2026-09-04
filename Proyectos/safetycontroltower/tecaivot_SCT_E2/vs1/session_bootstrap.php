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

/* =========================================================
   VERSIÓN DE ASSETS (cache-busting)
   ========================================================= */

/**
 * $ASSET_VERSION se agrega como "?v=" a cada CSS/JS propio del proyecto
 * en todas las páginas, para forzar al navegador (y a cualquier caché
 * intermedia del hosting) a pedir la copia nueva de un archivo después
 * de subir un cambio, en vez de servir una versión vieja cacheada.
 *
 * Uso en cualquier página que ya haga require de session_bootstrap.php:
 *
 *   <!-- build: <?= $ASSET_VERSION ?> -->
 *   <link rel="stylesheet" href="css/style.css?v=<?= $ASSET_VERSION ?>">
 *   <script src="js/main.js?v=<?= $ASSET_VERSION ?>"></script>
 *
 * Se calcula solo, tomando la fecha de modificación más reciente entre
 * todos los CSS/JS propios del proyecto (no los de CDN externos, esos ya
 * llevan su propia versión en la URL) — así nadie tiene que acordarse de
 * subir un número a mano en cada deploy: basta con subir el archivo
 * nuevo y este número cambia solo.
 *
 * Si se sube un archivo nuevo y este valor NO cambia en la página, es
 * señal de que el hosting (o el navegador) está sirviendo una copia
 * vieja en caché del propio archivo PHP, no del CSS/JS — por eso el
 * comentario "build:" conviene dejarlo visible en el HTML de cada
 * página, es la forma más rápida de diagnosticar caché vieja desde
 * "Ver código fuente" del navegador, sin tener que entrar al servidor.
 */
function calcularAssetVersion(): string
{
    $raiz = __DIR__;
    $carpetas = ['css', 'js'];
    $masReciente = 0;

    foreach ($carpetas as $carpeta) {
        $ruta = $raiz . '/' . $carpeta;
        if (!is_dir($ruta)) {
            continue;
        }

        $archivos = glob($ruta . '/*.{css,js}', GLOB_BRACE);
        foreach ($archivos ?: [] as $archivo) {
            $mtime = filemtime($archivo);
            if ($mtime !== false && $mtime > $masReciente) {
                $masReciente = $mtime;
            }
        }
    }

    // Respaldo si por algún motivo no se pudo leer ningún archivo (permisos,
    // carpeta inexistente, etc.): nunca dejar la página sin versión ni
    // frenar la ejecución por esto.
    return $masReciente > 0 ? date('YmdHis', $masReciente) : date('YmdHis');
}

$ASSET_VERSION = calcularAssetVersion();