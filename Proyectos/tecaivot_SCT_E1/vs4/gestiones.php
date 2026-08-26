<?php
require __DIR__ . '/session_bootstrap.php';

// Si no hay sesión activa, no se puede ver esta página.
if (empty($_SESSION['logged_in'])) {
    header('Location: accesso-denegado.php');
    exit;
}

aplicarCabecerasSeguridad();

$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestiones - Safety Control Tower</title>

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
        .topbar-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
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

        <div class="welcome-topbar">

            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="./images/logos/Logo-SCT-white.svg" alt="Safety Control Tower">
                </div>
            </div>

            <div class="topbar-actions">
                <a href="bienvenida.php" class="btn btn-outline-custom btn-sm">
                    Volver
                    <i class="bi bi-arrow-left"></i>
                </a>

                <a href="logout.php" class="btn btn-outline-custom btn-sm">
                    Cerrar sesión
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>

        </div>

        <section class="welcome-hero text-center">

            <div class="welcome-greeting-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>

            <span class="section-label">SAFETY CONTROL TOWER</span>

            <h1 class="section-title">Gestiones</h1>

            <p class="section-description intro-description-centered">
                Selecciona una gestión para continuar. Sesión iniciada como
                <strong><?php echo $userEmail; ?></strong>.
            </p>

        </section>

        <section class="quick-links mb-5">

            <div class="row g-4 justify-content-center">

                <div class="col-md-6 col-lg-5">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3>Gestión de Usuarios</h3>
                        <p>
                            Administra cuentas de acceso, estado de usuarios y
                            asignación de roles.
                        </p>
                        <a href="gestion-usuarios.php" class="btn btn-outline-custom btn-sm">
                            Ingresar
                        </a>
                    </div>
                </div>

            </div>

        </section>

    </div>

</body>
</html>
