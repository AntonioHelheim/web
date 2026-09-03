<?php
require_once __DIR__ . '/common.php';

requireLoginPage('../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = count(array_intersect(currentUserRoles($pdo), TRABAJADORES_ROLES_GESTION)) > 0;
$isGlobalAdmin = trabajadoresIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Trabajadores - Safety Control Tower</title>

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
        .worker-photo-thumb {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            background: rgba(0,0,0,0.06);
        }
        .worker-photo-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.06);
            color: rgba(0,0,0,0.35);
        }
    </style>
</head>
<body>

    <div class="container" data-csrf-token="<?php echo $csrfTokenEscaped; ?>" data-is-global-admin="<?php echo $isGlobalAdmin ? '1' : '0'; ?>">

        <div class="welcome-topbar">
            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
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
                <i class="bi bi-person-badge"></i>
            </div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title">Gestión de Trabajadores</h1>
            <p class="section-description intro-description-centered">
                Administra la ficha de los trabajadores de la empresa.
                Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3">Empresa</h2>
                <p class="text-muted mb-3">
                    Como administrador, elige la empresa cuyos trabajadores quieres administrar.
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
                <h2 class="h5 mb-3" id="workerFormTitle">Nuevo trabajador</h2>

                <div id="workersActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                <form id="workerForm" novalidate>
                    <input type="hidden" id="workerFormMode" value="create">
                    <input type="hidden" id="workerFormTarget" value="">

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="workerRut" class="form-label">RUT</label>
                            <input type="text" id="workerRut" class="form-control" placeholder="12345678-9" maxlength="12" required>
                            <div class="form-text" id="workerRutHelp">No se puede editar después de creado.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerName" class="form-label">Nombre</label>
                            <input type="text" id="workerName" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerLastname" class="form-label">Apellido</label>
                            <input type="text" id="workerLastname" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerEmail" class="form-label">Correo (opcional)</label>
                            <input type="email" id="workerEmail" class="form-control" maxlength="150">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerPhone" class="form-label">Teléfono (opcional)</label>
                            <input type="text" id="workerPhone" class="form-control" maxlength="20">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerPosition" class="form-label">Cargo (opcional)</label>
                            <input type="text" id="workerPosition" class="form-control" maxlength="100">
                        </div>
                    </div>

                    <div class="form-actions mt-3">
                        <button type="submit" id="workerSubmitBtn" class="btn btn-primary-custom btn-sm">Guardar trabajador</button>
                        <button type="button" id="workerCancelEditBtn" class="btn btn-outline-custom btn-sm d-none">Cancelar edición</button>
                    </div>
                </form>

                <div id="workerPhotoSection" class="mt-4 d-none">
                    <hr>
                    <h3 class="h6 mb-2">Foto del trabajador</h3>
                    <div class="d-flex align-items-center gap-3">
                        <img id="workerPhotoPreview" class="worker-photo-thumb" style="width:64px;height:64px;" src="" alt="">
                        <div>
                            <input type="file" id="workerPhotoInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG o WEBP. Máximo 3 MB.</div>
                        </div>
                        <button type="button" id="workerPhotoUploadBtn" class="btn btn-outline-custom btn-sm">Subir foto</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3">Trabajadores</h2>

                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-6">
                        <input type="text" id="workerSearchInput" class="form-control" placeholder="Buscar por RUT, nombre, apellido o cargo...">
                    </div>
                    <div class="col-12 col-md-3">
                        <select id="workerStateFilter" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="1">Solo activos</option>
                            <option value="0">Solo inactivos</option>
                        </select>
                    </div>
                </div>

                <div id="workersStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?php echo $isGlobalAdmin ? 'Selecciona una empresa para ver sus trabajadores.' : 'Cargando trabajadores...'; ?>
                </div>

                <div id="workersTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col">RUT</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Cargo</th>
                                <th scope="col">Contacto</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="workersTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/trabajadores.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
