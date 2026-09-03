<?php
require_once __DIR__ . '/common.php';

requireLoginPage('../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = count(array_intersect(currentUserRoles($pdo), CENTROS_ROLES_GESTION)) > 0;
$isGlobalAdmin = centrosIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Centros/Sedes - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= $assetVersionEscaped ?> -->
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>">

    <style>
        .welcome-hero { padding: 4rem 0 2rem; }
        .welcome-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img { height: 32px; }
        .topbar-actions { display: flex; gap: 0.5rem; align-items: center; }
        .welcome-greeting-icon { font-size: 2.5rem; color: #16a34a; margin-bottom: 0.75rem; }
        .quick-links { margin-top: 2rem; margin-bottom: 3rem; }
        .form-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    </style>
</head>
<body>

    <div class="container" data-csrf-token="<?php echo $csrfTokenEscaped; ?>" data-is-global-admin="<?php echo $isGlobalAdmin ? '1' : '0'; ?>">

        <div class="welcome-topbar">
            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="../../images/logos/Logo-SCT-white.svg" alt="Safety Control Tower">
                </div>
            </div>

            <div class="topbar-actions">
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm">
                    Volver a Gestiones
                    <i class="bi bi-arrow-left"></i>
                </a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm">
                    Cerrar sesión
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon">
                <i class="bi bi-geo-alt"></i>
            </div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title">Gestión de Centros/Sedes</h1>
            <p class="section-description intro-description-centered">
                Administra los centros o sedes de trabajo de la empresa.
                Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3">Empresa</h2>
                <p class="text-muted mb-3">
                    Como administrador, elige la empresa cuyos centros/sedes quieres administrar.
                </p>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label">Empresa</label>
                        <select id="companySelect" class="form-select">
                            <option value="">Selecciona una empresa...</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($puedeGestionar): ?>
            <div class="feature-card">
                <h2 class="h5 mb-3">Nuevo centro/sede</h2>

                <div id="centersActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                <form id="centerForm" novalidate>
                    <input type="hidden" id="centerFormMode" value="create">
                    <input type="hidden" id="centerFormTarget" value="">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="centerName" class="form-label">Nombre</label>
                            <input type="text" id="centerName" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="centerDescription" class="form-label">Descripción</label>
                            <input type="text" id="centerDescription" class="form-control" maxlength="255" required>
                        </div>
                    </div>

                    <div class="form-actions mt-3">
                        <button type="submit" id="centerSubmitBtn" class="btn btn-primary-custom btn-sm">Guardar centro/sede</button>
                        <button type="button" id="centerCancelEditBtn" class="btn btn-outline-custom btn-sm d-none">Cancelar edición</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3">Centros/Sedes</h2>

                <div id="centersStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?php echo $isGlobalAdmin ? 'Selecciona una empresa para ver sus centros/sedes.' : 'Cargando centros/sedes...'; ?>
                </div>

                <div id="centersTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="centersTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/centros.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
