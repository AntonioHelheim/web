<?php
/**
 * index.php
 * Ensambla la página principal a partir de los partials en /partials.
 * El orden de los require es el mismo orden en que aparecía cada
 * sección en el index.html original — no se alteró contenido ni maquetación.
 */

// Cabeceras de seguridad básicas (sin iniciar sesión: la home no necesita
// $_SESSION todavía, y abrir sesión para cada visitante anónimo sería
// overhead innecesario en la página más visitada del sitio).
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

const PARTIALS_DIR = __DIR__ . '/partials';

/**
 * Carga un partial de forma segura: si el archivo no existe, registra
 * el problema en el log del servidor (nunca lo muestra al usuario) y
 * detiene la ejecución con un mensaje genérico, en vez de dejar que
 * PHP imprima un Fatal Error con rutas internas del servidor.
 */
function cargarPartial(string $nombre): void
{
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
cargarPartial('trust-bar.php');
cargarPartial('problema.php');
cargarPartial('producto.php');
cargarPartial('funcionalidades.php');
cargarPartial('precios.php');
cargarPartial('demo-section.php');
cargarPartial('como-funciona.php');
cargarPartial('beneficios.php');
cargarPartial('cta.php');
cargarPartial('faq.php');
cargarPartial('contacto.php');       // incluye el cierre de </main>
cargarPartial('footer.php');
cargarPartial('login-modal.php');
cargarPartial('demo-modal.php');
cargarPartial('scripts.php');        // incluye Bootstrap JS, main.js, auth.js, </body></html>