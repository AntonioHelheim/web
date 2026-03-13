<?php
/*
|--------------------------------------------------------------------------
| Configuración de errores
|--------------------------------------------------------------------------
*/
if (getenv('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}

/*
|--------------------------------------------------------------------------
| Conexión y dependencias
|--------------------------------------------------------------------------
*/
require __DIR__ . '/pages/dblocalhost.php';/*LocalHost*/
/*require __DIR__ . '/pages/db.php';/*Produccion*/
date_default_timezone_set('America/Santiago');

/*
|--------------------------------------------------------------------------
| Procesamiento de búsqueda (lógica de negocio centralizada aquí)
|--------------------------------------------------------------------------
*/
$results  = [];
$error    = null;
$searched = false;

$patente = strtoupper(trim($_GET['patente'] ?? ''));
$patente = preg_replace('/[^A-Z0-9]/', '', $patente);

if ($patente !== '') {
    $searched = true;
    if (!preg_match('/^[A-Z]{4}[0-9]{2}$|^[A-Z]{2}[0-9]{4}$/', $patente)) {
        $error = "Formato de patente inválido.";
    } else {
        $stmt = $conn->prepare("
            SELECT
                field_279,
                field_280,
                field_281,
                field_282,
                field_327,
                field_331
            FROM app_entity_28
            WHERE field_279 = ?
            ORDER BY date_added DESC
        ");
        if (!$stmt) {
            $error = "Error interno al preparar la consulta.";
        } else {
            $stmt->bind_param("s", $patente);
            if ($stmt->execute()) {
                $result  = $stmt->get_result();
                $results = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            } else {
                $error = "Error al ejecutar la consulta.";
            }
            $stmt->close();
        }
    }
}

/*
|--------------------------------------------------------------------------
| Helper: formatear fecha desde timestamp
|--------------------------------------------------------------------------
*/
function formatearFecha($valor) {
    if (empty($valor) || !is_numeric($valor)) return '-';
    $timestamp = (int)$valor;
    if ($timestamp > 9999999999) $timestamp /= 1000;
    $fecha = new DateTime();
    $fecha->setTimestamp($timestamp);
    static $formatter = null;
    if ($formatter === null) {
        $formatter = new IntlDateFormatter(
            'es_CL',
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT,
            'America/Santiago',
            IntlDateFormatter::GREGORIAN,
            "d 'de' MMMM 'de' yyyy - HH:mm 'hrs'"
        );
    }
    return $formatter->format($fecha);
}

/*
|--------------------------------------------------------------------------
| Pasar variables al scope de los componentes vía JSON (para JS si se necesita)
|--------------------------------------------------------------------------
*/
$pageData = json_encode([
    'patente'  => $patente,
    'searched' => $searched,
    'error'    => $error,
    'count'    => count($results),
]);

$VERSION = '2026-03-12';
//JACL

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="author"    content="Juan Antonio Concha Loyola"/>
    <meta name="copyright" content="Helheim Tierra del Fuego"/>
    <meta name="robots"    content="index"/>

    <!-- Favicon -->
    <link rel="shortcut icon"          href="https://www.helheim.cl/images/favicon.ico"              type="image/x-icon">
    <link rel="apple-touch-icon"       sizes="180x180" href="https://www.helheim.cl/images/apple-touch-icon.png">
    <link rel="icon" type="image/png"  sizes="32x32"   href="https://www.helheim.cl/images/favicon-32x32.png">
    <link rel="icon" type="image/png"  sizes="16x16"   href="https://www.helheim.cl/images/favicon-16x16.png">

    <!-- SEO / OG / Twitter -->
    <title>Helheim.cl</title>
    <meta name="title" content="Helheim Tierra del Fuego"/>
    <meta name="description" content="Gestion de proyectos, Desarrollo Web, Diseño Industrial y Diseño Grafico"/>

    <meta property="og:type"        content="website"/>
    <meta property="og:url"         content="https://www.helheim.cl"/>
    <meta property="og:title"       content="Helheim Tierra del Fuego"/>
    <meta property="og:description" content="Consultoria TI y Diseño Industrial"/>
    <meta property="og:image"       content="https://www.helheim.cl/images/Logo_Helheim-Metadata_1200x640.png"/>
    <meta property="twitter:card"        content="summary_large_image"/>
    <meta property="twitter:url"         content="https://www.helheim.cl"/>
    <meta property="twitter:title"       content="Helheim Tierra del Fuego"/>
    <meta property="twitter:description" content="Consultoria TI y Diseño Industrial"/>
    <meta property="twitter:image"       content="https://www.helheim.cl/images/Logo_Helheim-Metadata_1200x640.png"/>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,500;1,600&family=Bebas+Neue&family=Barlow:wght@300;400;500;600&family=Barlow+Condensed:wght@400;700&display=swap" rel="stylesheet">

    <!--Normalizador de estilos para consistencia cross-browser-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">

    <!-- =============================================
         DESIGN SYSTEM — orden de carga obligatorio
         1. tokens globales (variables CSS)
         2. CSS de cada componente
    ============================================== -->
    
   
    <link rel="stylesheet" href="./shared/globals/variables.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./components/navbar/navbar.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./components/hero/hero.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./components/busqueda/busqueda.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./components/resultados/resultados.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./components/competencias/competencias.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./components/servicios/servicios.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./components/footer/footer.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./shared/scroll-top/scroll-top.css?v=<?= $VERSION ?>">
    <link rel="stylesheet" href="./shared/whatsapp-btn/whatsapp-btn.css?v=<?= $VERSION ?>">
</head>


<body>

    <!-- ── NAVBAR ───────────────────────────────── -->
    <?php include __DIR__ . '/components/navbar/navbar.php'; ?>

    <!-- ── HERO (Carousels) ─────────────────────── -->
    <?php include __DIR__ . '/components/hero/hero.php'; ?>

    <!-- ── MAIN CONTENT ─────────────────────────── -->
    <main class="main-section">
        <div class="container">

            <!-- Encabezado de página -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-8 text-center text-md-start">
                    <h1 class="section-heading">Escáner <span>Vehicular</span></h1>
                    <div class="section-rule mx-auto mx-md-0"></div>
                </div>
            </div>

            <!-- ── BÚSQUEDA ──────────────────────── -->
            <?php include __DIR__ . '/components/busqueda/busqueda.php'; ?>

            <!-- ── RESULTADOS ───────────────────── -->
            <?php include __DIR__ . '/components/resultados/resultados.php'; ?>

        </div>
    </main>

    <!-- ── COMPETENCIAS ─────────────────────────── -->
    <?php include __DIR__ . '/components/competencias/competencias.php'; ?>

    <!-- ── SERVICIOS ESCÁNER ────────────────────── -->
    <?php include __DIR__ . '/components/servicios/servicios.php'; ?>

    <!-- ── FOOTER ───────────────────────────────── -->
    <?php include __DIR__ . '/components/footer/footer.php'; ?>

    <!-- ── UTILIDADES FLOTANTES ─────────────────── -->
    <?php include __DIR__ . '/shared/scroll-top/scroll-top.php'; ?>
    <?php include __DIR__ . '/shared/whatsapp-btn/whatsapp-btn.php'; ?>

    <!-- =============================================
         SCRIPTS — orden de carga obligatorio
         1. Bootstrap bundle
         2. JS de cada componente
         3. main.js (inicialización global)
    ============================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>

    <script src="./components/navbar/navbar.js?v=<?= $VERSION ?>"></script>
    <script src="./components/busqueda/busqueda.js?v=<?= $VERSION ?>"></script>
    <script src="./shared/scroll-top/scroll-top.js?v=<?= $VERSION ?>"></script>
    <script src="./js/main.js?v=<?= $VERSION ?>"></script>

</body>
</html>
<?php $conn->close(); ?>