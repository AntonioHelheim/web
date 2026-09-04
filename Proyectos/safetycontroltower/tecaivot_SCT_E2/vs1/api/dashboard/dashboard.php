<?php
require_once __DIR__ . '/common.php';

requireLoginPage('../../acceso-denegado.php');

aplicarCabecerasSeguridad();

$isGlobalAdmin = dashboardIsGlobalAdmin($pdo);
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel General - Safety Control Tower</title>

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

        .stat-card {
            border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem;
            text-align: center; background: var(--white); height: 100%;
        }
        .stat-number { font-size: 2.2rem; font-weight: 800; color: var(--primary-darkest); line-height: 1; }
        .stat-label { color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.4rem; }

        .bar-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.6rem; }
        .bar-label { width: 110px; font-size: 0.85rem; color: var(--text-secondary); flex-shrink: 0; }
        .bar-track { flex-grow: 1; height: 14px; border-radius: 7px; background: rgba(0,0,0,0.06); overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 7px; transition: width 0.4s ease; }
        .bar-value { width: 36px; text-align: right; font-size: 0.85rem; font-weight: 600; flex-shrink: 0; }

        .fill-abierto { background: #dc2626; }
        .fill-en_proceso { background: #d97706; }
        .fill-cerrado { background: #16a34a; }
        .fill-baja { background: #16a34a; }
        .fill-media { background: #d97706; }
        .fill-alta { background: #ea580c; }
        .fill-critica { background: #dc2626; }
        .fill-aprobados { background: #16a34a; }
        .fill-pendientes { background: #d97706; }
        .fill-reprobados { background: #dc2626; }
    </style>
</head>
<body>

    <div class="container" data-is-global-admin="<?php echo $isGlobalAdmin ? '1' : '0'; ?>">

        <div class="welcome-topbar">
            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                </div>
            </div>
            <div class="topbar-actions">
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm">Gestiones <i class="bi bi-clipboard-check"></i></a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm">Cerrar sesión <i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon"><i class="bi bi-speedometer2"></i></div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title">Panel General</h1>
            <p class="section-description intro-description-centered">
                Indicadores de tu operación. Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <div class="row g-3 mb-4 align-items-end">
                <?php if ($isGlobalAdmin): ?>
                <div class="col-12 col-md-4">
                    <label for="companySelect" class="form-label">Empresa</label>
                    <select id="companySelect" class="form-select">
                        <option value="">Selecciona una empresa...</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-12 col-md-4">
                    <label for="projectFilter" class="form-label">Filtrar eventos por proyecto</label>
                    <select id="projectFilter" class="form-select">
                        <option value="">Todos los proyectos</option>
                    </select>
                </div>
            </div>

            <div id="dashAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>
            <div id="dashStatus" class="alert alert-info">
                <?php echo $isGlobalAdmin ? 'Selecciona una empresa para ver sus indicadores.' : 'Cargando indicadores...'; ?>
            </div>

            <div id="dashContent" class="d-none">

                <div class="row g-3 mb-4" id="statCardsRow"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <h2 class="h6 mb-3">Eventos por estado</h2>
                            <div id="barsEstado"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <h2 class="h6 mb-3">Eventos por criticidad</h2>
                            <div id="barsCriticidad"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <h2 class="h6 mb-3">Inducción — estado de asignaciones</h2>
                            <div id="barsInduccion"></div>
                            <p class="text-muted mt-2 mb-0" id="tasaCumplimiento" style="font-size:0.85rem;"></p>
                        </div>
                    </div>
                </div>

            </div>

        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/dashboard.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
