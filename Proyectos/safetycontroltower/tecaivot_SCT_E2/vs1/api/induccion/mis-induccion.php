<?php
require_once __DIR__ . '/common.php';

requireLoginPage('../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Inducciones - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= $assetVersionEscaped ?> -->
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>">

    <style>
        .welcome-hero { padding: 4rem 0 2rem; }
        .welcome-topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.25rem 0; border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img { height: 32px; }
        .topbar-actions { display: flex; gap: 0.5rem; align-items: center; }
        .welcome-greeting-icon { font-size: 2.5rem; color: #16a34a; margin-bottom: 0.75rem; }
        .quick-links { margin-top: 2rem; margin-bottom: 3rem; }
        .curso-card { border: 1px solid var(--border); border-radius: 16px; padding: 1.25rem; margin-bottom: 1rem; }
        .rendir-pregunta { border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1rem; }
        .rendir-pregunta:last-child { border-bottom: none; }
    </style>
</head>
<body>

    <div class="container" data-csrf-token="<?php echo $csrfTokenEscaped; ?>">

        <div class="welcome-topbar">
            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                </div>
            </div>
            <div class="topbar-actions">
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm">Volver a Gestiones <i class="bi bi-arrow-left"></i></a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm">Cerrar sesión <i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon"><i class="bi bi-mortarboard"></i></div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title">Mis Inducciones</h1>
            <p class="section-description intro-description-centered">
                Cursos asignados a tu cuenta. Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <div id="misAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

            <div id="misStatus" class="alert alert-info" role="status">Cargando tus cursos asignados...</div>

            <div id="misLista" class="d-none"></div>

            <!-- Panel para rendir el curso -->
            <div id="rendirPanel" class="feature-card mt-4 d-none">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h2 class="h5 mb-0" id="rendirTitulo">-</h2>
                    <button type="button" id="rendirCerrarBtn" class="btn btn-outline-custom btn-sm">Cerrar</button>
                </div>
                <div id="rendirAlert" class="alert d-none" role="alert" aria-live="polite"></div>
                <div id="rendirPreguntas"></div>
                <button type="button" id="rendirEnviarBtn" class="btn btn-primary-custom mt-2">Enviar respuestas</button>
            </div>

        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/induccion-mis.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
