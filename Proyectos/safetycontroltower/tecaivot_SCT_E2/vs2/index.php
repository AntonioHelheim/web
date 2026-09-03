<?php
/**
 * index.php
 * Ensambla la página principal de Tecaivot a partir de los partials en /partials.
 */

// Inicia la sesión ANTES de cualquier salida (obligatorio: login-modal.php
// necesita $_SESSION['csrf_token'] disponible, y session_start() debe
// ejecutarse antes de que se envíe HTML o cualquier otra salida).
// También aplica las cabeceras de seguridad básicas (X-Frame-Options, etc.)
require __DIR__ . '/session_bootstrap.php';
aplicarCabecerasSeguridad();

// Selección de idioma (es/en/pt/fr/zh) y función t(). Debe ir después de
// iniciar sesión (usa $_SESSION) y antes de cualquier partial (todos usan t()).
require __DIR__ . '/i18n.php';

const PARTIALS_DIR = __DIR__ . '/partials';

/**
 * Carga un partial de forma segura: si el archivo no existe, registra
 * el problema en el log del servidor (nunca lo muestra al usuario) y
 * detiene la ejecución con un mensaje genérico, en vez de dejar que
 * PHP imprima un Fatal Error con rutas internas del servidor.
 */
function cargarPartial(string $nombre): void
{
    // Los partials se incluyen desde acá adentro (scope de función), así
    // que cualquier variable global que necesiten -como $ASSET_VERSION,
    // calculada en session_bootstrap.php- hay que declararla acá antes
    // del require, o el partial no la va a ver.
    global $ASSET_VERSION;

    $ruta = PARTIALS_DIR . '/' . $nombre;

    if (!file_exists($ruta)) {
        error_log("index.php: falta el partial '{$nombre}' en " . PARTIALS_DIR);
        if (!headers_sent()) {
            http_response_code(500);
        }
        die('Ocurrió un problema al cargar la página. Intenta nuevamente en unos minutos.');
    }

    require $ruta;
}

cargarPartial('head.php');
?>

<body>

<?php
cargarPartial('navbar.php');
cargarPartial('hero.php');           // incluye la apertura de <main>
cargarPartial('nosotros.php');
cargarPartial('productos.php');
cargarPartial('contacto.php');       // incluye el cierre de </main>
cargarPartial('footer.php');
cargarPartial('login-modal.php');
cargarPartial('scripts.php');        // incluye Bootstrap JS, main.js, auth.js, </body></html>