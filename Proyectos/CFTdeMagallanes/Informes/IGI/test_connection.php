<?php
// Incluye el archivo de configuración
//require 'db.php';

//Usuario Especifico BD unnffbpk_proyectos
$host = '190.107.177.247';
$dbname = 'unnffbpk_proyectos';
$username = 'unnffbpk_cft23';
$password = '4S1)Xp60]4';

// Variable para almacenar el mensaje
$message = '';
$message_class = '';

try {
    // Intenta la conexión con PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mensaje de éxito
    $message = "Conexión exitosa a la base de datos.";
    $message_class = "alert alert-success"; // Clase de Bootstrap para estilo verde
} catch (PDOException $e) {
    // Mensaje de error
    $message = "Error de conexión: " . $e->getMessage();
    $message_class = "alert alert-danger"; // Clase de Bootstrap para estilo rojo
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="es">
  <!-- Header --->
  <head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="./images/favicon_io/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="180x180" href="./images/favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon_io/favicon-16x16.png">
  <link rel="manifest" href="./images/site.webmanifest">
  <title>UsrTstCnxBD</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./css/normalize.css">
  
  <meta name="author" content="Juan Antonio Concha L. & Maite Lazcano" />
  <meta name="copyright" content="Test Conection"/>
  <meta name="robots" content="index"/>
  <title>.:: CFT de Magallanes ::.</title>
  <meta name="title" content="CFT de Magallanes" />
  <meta name="description" content="Test Conection" />

  <meta property="og:type" content="website">
  <meta property="og:url" content="www.cftdemagallanes.cl">
  <meta property="og:title" content="CFT de Magallanes">

  <!-- Meta Tags Generated with https://metatags.io -->
  </head>
  <!-- END Header --->

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <!-- Mensaje dinámico -->
                <div class="<?= $message_class ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="container p-4">
            <div class="row">
                <!-- Texto del footer -->
                <div class="col-lg-12 col-md-12 mb-4 mb-md-0">
                    <ul class="list-unstyled d-flex justify-content-center">
                        <li class="mx-3">
                            <a href="https://www.cftmagallanes.cl" class="text-decoration-none text-primary" target="_blank">
                                Portal CFT de Magallanes
                            </a>
                        </li>
                        <li class="mx-3">
                            <a href="https://www.helheim.cl" class="text-decoration-none text-primary" target="_blank">
                                Portal Helheim.cl
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Copyright -->
        <div class="text-center p-3 bg-dark text-white">
            &copy; <?= date('Y') ?> Todos los derechos reservados.
        </div>
    </footer>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
