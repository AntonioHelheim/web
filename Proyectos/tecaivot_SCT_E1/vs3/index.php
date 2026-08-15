<?php
/**
 * index.php
 * Ensambla la página principal a partir de los partials en /partials.
 * El orden de los require es el mismo orden en que aparecía cada
 * sección en el index.html original — no se alteró contenido ni maquetación.
 */
require __DIR__ . '/partials/head.php';
?>

<body>

<?php
require __DIR__ . '/partials/navbar.php';
require __DIR__ . '/partials/hero.php';          // incluye la apertura de <main>
require __DIR__ . '/partials/trust-bar.php';
require __DIR__ . '/partials/problema.php';
require __DIR__ . '/partials/producto.php';
require __DIR__ . '/partials/funcionalidades.php';
require __DIR__ . '/partials/precios.php';
require __DIR__ . '/partials/demo-section.php';
require __DIR__ . '/partials/como-funciona.php';
require __DIR__ . '/partials/beneficios.php';
require __DIR__ . '/partials/cta.php';
require __DIR__ . '/partials/faq.php';
require __DIR__ . '/partials/contacto.php';      // incluye el cierre de </main>
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/login-modal.php';
require __DIR__ . '/partials/demo-modal.php';
require __DIR__ . '/partials/scripts.php';       // incluye Bootstrap JS, main.js, auth.js, </body></html>
