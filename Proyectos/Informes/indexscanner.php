<?php
/*
|--------------------------------------------------------------------------
| Configuración de errores (CAMBIAR EN PRODUCCIÓN)
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
| Conexión
|--------------------------------------------------------------------------
*/
/*require __DIR__ . '/pages/dblocalhost.php';/*LocalHost*/
require __DIR__ . '/pages/db.php';/*Produccion*/
date_default_timezone_set('America/Santiago');

/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
*/
$results = [];
$error = null;
$searched = false;

/*
|--------------------------------------------------------------------------
| Procesamiento de búsqueda
|--------------------------------------------------------------------------
*/
$patente = strtoupper(trim($_GET['patente'] ?? ''));
$patente = preg_replace('/[^A-Z0-9]/', '', $patente);
if ($patente !== '') {
    $searched = true;
    // Validación formato chileno común
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
                $result = $stmt->get_result();
                $results = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            } else {
                $error = "Error al ejecutar la consulta.";
            }
            $stmt->close();
        }
    }
}

function formatearFecha($valor) {
    if (empty($valor) || !is_numeric($valor)) {
        return '-';
    }
    $timestamp = (int)$valor;

    if ($timestamp > 9999999999) {
        $timestamp /= 1000;
    }
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
    return $formatter->format($fecha);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="author" content="Juan Antonio Concha L. & Maite Lazcano"/>
    <meta name="copyright" content="Helheim Tierra del Fuego"/>
    <meta name="robots" content="index"/>

<!--==================== Favicon Generated with https://favicon.io/favicon-converter/ ====================-->
<link rel="shortcut icon" href="https://www.helheim.cl//images/favicon.ico" type="image/x-icon">
<link rel="apple-touch-icon" sizes="180x180" href="https://www.helheim.cl//images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="https://www.helheim.cl//images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="https://www.helheim.cl//images/favicon-16x16.png">

<!--==================== END Favicon Generated with https://favicon.io/favicon-converter/ ====================-->

<!--==================== Meta Tags Generated with https://metatags.io ====================-->

    <!-- Primary Meta Tags -->
    <title>Helheim</title>
    <meta name="title" content="Helheim Tierra del Fuego"/>
    <meta name="description" content="Gestion de proyectos, Desarrollo Web, Diseño Industrial y Diseño Grafico"/>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="https://www.helheim.cl"/>
    <meta property="og:title" content="Helheim Tierra del Fuego"/>
    <meta property="og:description" content="Gestion de proyectos, Desarrollo Web, Diseño Industrial y Diseño Grafico"/>
    <meta property="og:image" content="https://www.helheim.cl/images/Logo_Helheim-Metadata_1200x640.png"/>

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image"/>
    <meta property="twitter:url" content="https://www.helheim.cl"/>
    <meta property="twitter:title" content="Helheim Tierra del Fuego"/>
    <meta property="twitter:description" content="Gestion de proyectos, Desarrollo Web, Diseño Industrial y Diseño Grafico"/>
    <meta property="twitter:image" content="https://www.helheim.cl/images/Logo_Helheim-Metadata_1200x640.png"/>


<!--==================== END Meta Tags Generated with https://metatags.io ====================-->

<!--=============== BootStrap CSS ===============-->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<!--=============== END BootStrap CSS ===============-->
<!-- Styles -->
 <!-- Fuente elegante para la cita -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,500;1,600&display=swap" rel="stylesheet">
<link href="https://www.helheim.cl/css/styles_v_1.css?v=2025-10-02-1925" rel="stylesheet"/>


</head> 

<body class="bg-black text-light">

<!--==================== HEADER ====================-->
<header class="header" id="header">
  <!--==================== NavBar ====================--> 
  <nav class="navbar bg-black navbar-dark fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Helheim Tierra del Fuego</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto ">
            <li class="nav-item">
              <a href="https://www.helheim.cl" class="nav-link text-success fs-6">
                <i class="bi bi-globe"></i> INICIO
              </a>
            </li>
          <li class="nav-item">
            <a href="https://www.helheim.cl/section/01_CTI_ISDW_IS.html" class="nav-link text-success fs-6">
              <i class="bi bi-laptop me-1"></i> CONSULTORÍA TI
            </a>
          </li>
          <li class="nav-item">
            <a href="https://www.helheim.cl/section/10_DIN_IyC_DG.html" class="nav-link text-success fs-6">
              <i class="bi bi-palette me-1"></i> DISEÑO INDUSTRIAL
            </a>
          </li>
          <li class="nav-item">
            <a href="#formcontacto" class="nav-link text-success fs-6">
              <i class="bi bi-envelope me-1"></i> FORMULARIO DE CONTACTO
            </a>
          </li>
          <li class="nav-item">
            <a href="https://helheim.cl/ruko/index.php?module=users/login" target="_blank" class="nav-link text-success fs-6">
              <i class="bi bi-box-arrow-in-right me-1"></i>INTRANET HELHEIM
            </a>
          </li>
          <li class="nav-item">
            <a href="https://helheim.cl:2096/" target="_blank" class="nav-link text-success fs-6">
              <i class="bi bi-envelope-open me-1"></i> CORREO HELHEIM
            </a>
          </li>

          <!-- Separador -->
          <li class="mt-4 text-decoration-none text-success fs-6">
            Escríbenos a nuestro WhatsApp o Redes Sociales
          </li>

          <li class="nav-item">
            <div class="row">
              <div class="col-2 "></div>
              <div class="col-2 text-center fs-3">
                <a href="https://wa.me/56958453672?text=Hola, quiero saber mas sobre los Helheim y sus servicios" 
                   target="_blank" 
                   class="nav__link text-decoration-none text-danger">
                  <i class="bi bi-whatsapp"></i>
                </a>
              </div>
              <div class="col-2 text-center fs-3">
                <a href="https://www.instagram.com/helheim_tierra_del_fuego/" target="_blank" 
                class="nav__link text-decoration-none text-danger">
                  <i class="bi bi-instagram"></i>
                </a>
              </div>
              <div class="col-2 text-center fs-3">
                <a href="https://www.linkedin.com/company/103153651/" target="_blank" 
                   class="nav__link text-decoration-none text-danger">
                  <i class="bi bi-linkedin"></i>
                </a>
              </div>
              <div class="col-2 text-center fs-3">
                <a href="https://web.facebook.com/HelheimTierradelfuego/" target="_blank" 
                class="nav__link text-decoration-none text-danger">
                  <i class="bi bi-facebook"></i>
                </a>
              </div>
              <div class="col-2 "></div>
            </div>
          </li>

        </ul>
      </div>
    </div>
  </nav>
</header>


    <!--==================== END HEADER ====================-->

<div class="container mt-5 pt-5">

    <!-- ==================== BUSQUEDA ==================== -->
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">

                    <h5 class="text-center mb-3">Buscar Vehículo por Patente</h5>

                    <form method="GET">
                        <div class="input-group">
                            <input 
                                type="text"
                                name="patente"
                                class="form-control text-uppercase"
                                placeholder="Ej: ABCD12"
                                value="<?= htmlspecialchars($patente, ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                            <button class="btn btn-success" type="submit">
                                Buscar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- ==================== RESULTADOS ==================== -->
    <?php if ($searched): ?>
        <div class="mt-4">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php else: ?>

                <div class="mb-2">
                    <strong>Resultados para:</strong> <?= htmlspecialchars($patente) ?><br>
                    <small><?= count($results) ?> registro(s) encontrado(s)</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-striped table-bordered align-middle">
                        <thead class="table-success text-dark">
                            <tr>
                                <th>Patente</th>
                                <th>Fecha ejecucion</th>
                                <th>Vin</th>
                                <th>ThinkCar</th>
                                <th>Conclusion Tecnica</th>
                                <th>Propietario</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php if (!empty($results)): ?>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['field_279']) ?></td>
                                    
<td><?= htmlspecialchars(formatearFecha($row['field_280'])) ?></td>

                                    <td><?= htmlspecialchars($row['field_281']) ?></td>
                                    <td><?= htmlspecialchars($row['field_282']) ?></td>
                                    <td><?= htmlspecialchars($row['field_327']) ?></td>
                                    <td><?= htmlspecialchars($row['field_331']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No existen registros para esta patente.
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<footer class="footer bg-dark text-white py-4 mt-5 text-center">
    <small>
        &copy; <?= date('Y') ?> Helheim Tierra del Fuego
    </small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>