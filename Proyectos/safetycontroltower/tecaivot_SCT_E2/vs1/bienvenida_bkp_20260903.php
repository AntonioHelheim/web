<?php
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/auth.php';

// Si no hay sesión activa, no se puede ver esta página (redirige, no es un endpoint JSON).
requireLoginPage();

aplicarCabecerasSeguridad();

$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');

// Nombre a mostrar: usa lo que viene antes del @ del correo como saludo
$displayName = htmlspecialchars(
    ucfirst(explode('@', $_SESSION['user_email'] ?? 'usuario')[0]),
    ENT_QUOTES,
    'UTF-8'
);

// Saludo según la hora del día
$hour = (int) date('G');
if ($hour < 12) {
    $greeting = 'Buenos días';
} elseif ($hour < 19) {
    $greeting = 'Buenas tardes';
} else {
    $greeting = 'Buenas noches';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .welcome-hero {
            padding: 4rem 0 2rem;
        }
        .welcome-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img {
            height: 32px;
        }
        .welcome-greeting-icon {
            font-size: 2.5rem;
            color: #16a34a;
            margin-bottom: 0.75rem;
        }
        .quick-links {
            margin-top: 3rem;
        }
    </style>
</head>
<body>

    <div class="container">

        <!-- Barra superior simple -->
        <div class="welcome-topbar">

            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="./images/logos/Logo-SCT-white.svg" alt="Safety Control Tower">
                </div>
            </div>

            <a href="logout.php" class="btn btn-outline-custom btn-sm">
                Cerrar sesión
                <i class="bi bi-box-arrow-right"></i>
            </a>

        </div>


        <!-- Saludo principal -->
        <section class="welcome-hero text-center">

            <div class="welcome-greeting-icon">
                <i class="bi bi-shield-check"></i>
            </div>

            <span class="section-label">SAFETY CONTROL TOWER</span>

            <h1 class="section-title">
                <?php echo $greeting; ?>, <span><?php echo $displayName; ?></span>
            </h1>

            <p class="section-description">
                Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
                Este es tu punto de partida para monitorear y gestionar
                la seguridad de tu operación.
            </p>

        </section>


        <!-- Accesos rápidos -->
        <section class="quick-links">

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <h3>Panel general</h3>
                        <p>
                            Revisa el estado actual de tu operación y los
                            indicadores más relevantes.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <h3>Gestiones</h3>
                        <p>
                            Da seguimiento a tareas, incidentes y procesos
                            pendientes de tu equipo.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h3>Alertas</h3>
                        <p>
                            Consulta notificaciones y elementos que
                            requieren tu atención.
                        </p>
                    </div>
                </div>

            </div>

        </section>


        <!-- Ayuda / soporte -->
        <section class="text-center mt-5 mb-5">

            <p class="text-muted">
                ¿Necesitas ayuda para comenzar?
                <a href="mailto:contacto@tecaivot.cl">Contacta a soporte</a>
            </p>

        </section>

    </div>

</body>
</html>