<?php
// Incluye el archivo para la conexion a la BD
// require 'db.php';

// Incluye el archivo para probar la conexion a la BD
// Require 'test_connection.php';

// Cerrar la conexión (aunque PHP la cerrará automáticamente al finalizar el script
// $pdo = null;
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
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./css/normalize.css">
  
  <meta name="author" content="Juan Antonio Concha L. & Maite Lazcano" />
  <meta name="copyright" content="Grafica Titulados CFT de Magallanes"/>
  <meta name="robots" content="index"/>
  <title>.:: CFT de Magallanes ::.</title>
  <meta name="title" content="CFT de Magallanes" />
  <meta name="description" content="Grafica Titulados CFT de Magallanes" />

  <meta property="og:type" content="website">
  <meta property="og:url" content="www.cftdemagallanes.cl">
  <meta property="og:title" content="CFT de Magallanes">
  
  <!-- Meta Tags Generated with https://metatags.io -->
  </head>
  <!-- END Header --->

  <body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Sistema de seguimiento de denuncias de violencia de genero</h1>
        <div class="input-group mb-4">
            <input type="text" class="form-control" id="NumDenuncia" placeholder="Ingrese el número de denuncia">
            <button class="btn btn-primary" id="BuscarDenuncia">Buscar denuncia</button>
        </div>
        <div id="resultado" class="card">
            <div class="card-body">
                <!-- Aquí se mostrarán los resultados de la búsqueda -->
                <p class="text-muted">Los resultados de la búsqueda aparecerán aquí.</p>
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
                            <a href="https://cftdemagallanes.cl/genero-e-inclusion/" class="text-decoration-none text-primary" target="_blank">
                                Portal Genero e Inclusion CFT de Magallanes
                            </a>
                        </li>
                        <li class="mx-3">
                            <a href="https://cftdemagallanes.cl/" class="text-decoration-none text-primary" target="_blank">
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

    <!--Java Script -->
    <script>
        document.getElementById('BuscarDenuncia').addEventListener('click', function () {
            const numDenuncia = document.getElementById('NumDenuncia').value;

            if (numDenuncia) {
                fetch('buscar_denuncia.php?id=' + encodeURIComponent(numDenuncia))
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('resultado').innerHTML = data;
                    })
                    .catch(error => {
                        document.getElementById('resultado').innerHTML = '<p class="text-danger">Error al procesar la solicitud.</p>';
                    });
            } else {
                document.getElementById('resultado').innerHTML = '<p class="text-danger">Por favor, ingrese un número de denuncia.</p>';
            }
        });
    </script>

</body>
</html>