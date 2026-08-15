<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .denied-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .denied-icon {
            font-size: 3.5rem;
            color: #dc2626;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <div class="denied-wrapper">

        <div>

            <div class="denied-icon">
                <i class="bi bi-x-circle-fill"></i>
            </div>

            <h1>Acceso denegado</h1>

            <p class="lead">
                No tienes una sesión activa o no cuentas con permisos
                para ver esta página.
            </p>

            <p class="text-muted">
                Inicia sesión para continuar.
            </p>

            <a href="index.html" class="btn btn-primary-custom mt-3">
                Volver a intentar
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

    </div>

</body>
</html>