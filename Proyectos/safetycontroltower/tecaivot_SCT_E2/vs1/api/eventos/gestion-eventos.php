<?php
require_once __DIR__ . '/common.php';

requireLoginPage('../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = count(array_intersect(currentUserRoles($pdo), EVENTOS_ROLES_GESTION)) > 0;
$isGlobalAdmin = eventosIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos e Incidentes - Safety Control Tower</title>

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
        .form-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .evento-detalle { display: none; }
        .evento-detalle.activo { display: block; }
        .subseccion { border-top: 1px solid var(--border); padding-top: 1.25rem; margin-top: 1.25rem; }
        .crit-baja { color: #16a34a; }
        .crit-media { color: #d97706; }
        .crit-alta { color: #ea580c; }
        .crit-critica { color: #dc2626; font-weight: 700; }
        .estado-1 { color: #dc2626; }
        .estado-2 { color: #d97706; }
        .estado-3 { color: #16a34a; }
        .chip-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.5rem 0.75rem; border-radius: 10px; background: rgba(0,0,0,0.03);
            margin-bottom: 0.5rem; font-size: 0.9rem;
        }
        .chip-row button { border: none; background: none; color: #dc2626; }
        .chip-row a { text-decoration: none; }
    </style>
</head>
<body>

    <div class="container" data-csrf-token="<?php echo $csrfTokenEscaped; ?>"
         data-is-global-admin="<?php echo $isGlobalAdmin ? '1' : '0'; ?>"
         data-puede-gestionar="<?php echo $puedeGestionar ? '1' : '0'; ?>">

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
            <div class="welcome-greeting-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title">Eventos e Incidentes</h1>
            <p class="section-description intro-description-centered">
                Reporta y da seguimiento a eventos de seguridad. Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3">Empresa</h2>
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

            <div id="eventActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

            <div class="feature-card mb-4">
                <h2 class="h5 mb-3">Reportar evento/incidente</h2>
                <form id="eventForm" novalidate>
                    <input type="hidden" id="eventFormMode" value="create">
                    <input type="hidden" id="eventFormTarget" value="">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="eventType" class="form-label">Tipo de evento</label>
                            <select id="eventType" class="form-select" required>
                                <option value="">Selecciona...</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventCenter" class="form-label">Centro/sede</label>
                            <select id="eventCenter" class="form-select" required>
                                <option value="">Selecciona...</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventCriticality" class="form-label">Criticidad</label>
                            <select id="eventCriticality" class="form-select" required>
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                                <option value="critica">Crítica</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventProject" class="form-label">Proyecto (opcional)</label>
                            <select id="eventProject" class="form-select">
                                <option value="">Sin proyecto asociado</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventWorker" class="form-label">Trabajador involucrado (opcional)</label>
                            <select id="eventWorker" class="form-select">
                                <option value="">Sin trabajador asociado</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventDate" class="form-label">Fecha y hora del evento</label>
                            <input type="datetime-local" id="eventDate" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="eventDescription" class="form-label">Descripción</label>
                            <textarea id="eventDescription" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="form-actions mt-3">
                        <button type="submit" id="eventSubmitBtn" class="btn btn-primary-custom btn-sm">Reportar evento</button>
                        <button type="button" id="eventCancelEditBtn" class="btn btn-outline-custom btn-sm d-none">Cancelar edición</button>
                    </div>
                </form>
            </div>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3">Eventos registrados</h2>

                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <select id="filterCriticality" class="form-select form-select-sm">
                            <option value="">Toda criticidad</option>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="critica">Crítica</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select id="filterState" class="form-select form-select-sm">
                            <option value="">Todo estado</option>
                            <option value="1">Abierto</option>
                            <option value="2">En proceso</option>
                            <option value="3">Cerrado</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Buscar en la descripción...">
                    </div>
                </div>

                <div id="eventsStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?php echo $isGlobalAdmin ? 'Selecciona una empresa para ver sus eventos.' : 'Cargando eventos...'; ?>
                </div>
                <div id="eventsTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr><th>Fecha</th><th>Tipo</th><th>Centro</th><th>Criticidad</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody id="eventsTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Detalle del evento: seguimiento y evidencias -->
            <div id="eventDetail" class="feature-card mt-4 evento-detalle">
                <div class="d-flex justify-content-between align-items-start">
                    <h2 class="h5 mb-0">Detalle del evento</h2>
                    <button type="button" id="eventDetailCloseBtn" class="btn btn-outline-custom btn-sm">Cerrar</button>
                </div>
                <div id="eventDetailBody" class="mt-2"></div>

                <div id="detailAlert" class="alert d-none mt-3" role="alert" aria-live="polite"></div>

                <?php if ($puedeGestionar): ?>
                <div class="subseccion">
                    <h3 class="h6">Cambiar estado</h3>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline-custom btn-sm" data-estado="1">Abierto</button>
                        <button type="button" class="btn btn-outline-custom btn-sm" data-estado="2">En proceso</button>
                        <button type="button" class="btn btn-outline-custom btn-sm" data-estado="3">Cerrado</button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="subseccion">
                    <h3 class="h6">Seguimiento</h3>
                    <div id="trackingList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <form id="trackingForm" class="row g-2">
                        <div class="col-12"><textarea id="trackingDescription" class="form-control" rows="2" placeholder="Descripción del avance" required></textarea></div>
                        <div class="col-12 col-md-4"><input type="text" id="trackingPerson" class="form-control" placeholder="Responsable" required></div>
                        <div class="col-6 col-md-4"><label class="form-label form-label-sm">Fecha de compromiso</label><input type="date" id="trackingCommitment" class="form-control" required></div>
                        <div class="col-6 col-md-4"><label class="form-label form-label-sm">Plazo</label><input type="date" id="trackingDeadline" class="form-control" required></div>
                        <div class="col-12"><button type="submit" class="btn btn-outline-custom btn-sm">Agregar seguimiento</button></div>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="subseccion">
                    <h3 class="h6">Evidencias</h3>
                    <div id="evidenceList" class="mb-3"></div>

                    <form id="evidenceForm" class="row g-2">
                        <div class="col-12 col-md-8"><input type="file" id="evidenceFile" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf" required></div>
                        <div class="col-12 col-md-4"><button type="submit" class="btn btn-outline-custom btn-sm w-100">Subir evidencia</button></div>
                        <div class="form-text">Fotos (JPG, PNG, WEBP) o documentos PDF. Máximo 8 MB.</div>
                    </form>
                </div>

            </div>

        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/eventos.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
