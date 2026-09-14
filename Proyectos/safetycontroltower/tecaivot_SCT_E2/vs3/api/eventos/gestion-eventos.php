<?php
require_once __DIR__ . '/common.php';

requireCapabilityPage($pdo, 'events.view', '../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = currentUserHasCapability($pdo, 'events.manage');
$isGlobalAdmin = eventosIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');

$jsKeys = [
    'events_no_centers_warning','events_select_to_view','events_loading','events_load_error','events_no_match',
    'events_state_open','events_state_in_progress','events_state_closed','events_view_detail',
    'events_no_project','events_no_worker','events_select_placeholder',
    'events_required_fields','events_select_company_first','events_save_error','events_saved','events_submit',
    'events_detail_loading','events_detail_load_error','events_tracking_empty','events_tracking_deadline_prefix',
    'events_tracking_add_error','events_evidence_empty','events_evidence_remove_title','events_evidence_remove_confirm',
    'events_evidence_select_file','events_evidence_upload_error','events_error_response',
    'events_detail_project_label','events_detail_worker_label','events_detail_criticality_label','common_state',
];
$jsStrings = [];
foreach ($jsKeys as $k) $jsStrings[$k] = t($k);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tt('events_page_title') ?></title>

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
                <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= tt('common_language') ?>">
                    <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= tt('mgmt_back') ?> <i class="bi bi-arrow-left"></i></a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= tt('common_logout') ?> <i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title"><?= tt('events_title') ?></h1>
            <p class="section-description intro-description-centered">
                <?= tt('events_intro') ?> <?= tt('users_session_as') ?> <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3"><?= tt('events_company_section_title') ?></h2>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label"><?= tt('events_company_section_title') ?></label>
                        <select id="companySelect" class="form-select">
                            <option value=""><?= tt('events_select_company') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div id="eventActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

            <div class="feature-card mb-4">
                <h2 class="h5 mb-3"><?= tt('events_report_title') ?></h2>
                <form id="eventForm" novalidate>
                    <input type="hidden" id="eventFormMode" value="create">
                    <input type="hidden" id="eventFormTarget" value="">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="eventType" class="form-label"><?= tt('events_type_label') ?></label>
                            <select id="eventType" class="form-select" required>
                                <option value=""><?= tt('events_select_placeholder') ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventCenter" class="form-label"><?= tt('events_center_label') ?></label>
                            <select id="eventCenter" class="form-select" required>
                                <option value=""><?= tt('events_select_placeholder') ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventCriticality" class="form-label"><?= tt('events_criticality_label') ?></label>
                            <select id="eventCriticality" class="form-select" required>
                                <option value="baja"><?= tt('events_crit_low') ?></option>
                                <option value="media" selected><?= tt('events_crit_medium') ?></option>
                                <option value="alta"><?= tt('events_crit_high') ?></option>
                                <option value="critica"><?= tt('events_crit_critical') ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventProject" class="form-label"><?= tt('events_project_label') ?></label>
                            <select id="eventProject" class="form-select">
                                <option value=""><?= tt('events_no_project') ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventWorker" class="form-label"><?= tt('events_worker_label') ?></label>
                            <select id="eventWorker" class="form-select">
                                <option value=""><?= tt('events_no_worker') ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="eventDate" class="form-label"><?= tt('events_date_label') ?></label>
                            <input type="datetime-local" id="eventDate" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="eventDescription" class="form-label"><?= tt('events_description_label') ?></label>
                            <textarea id="eventDescription" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="form-actions mt-3">
                        <button type="submit" id="eventSubmitBtn" class="btn btn-primary-custom btn-sm"><?= tt('events_submit') ?></button>
                        <button type="button" id="eventCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= tt('events_cancel_edit') ?></button>
                    </div>
                </form>
            </div>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3"><?= tt('events_list_title') ?></h2>

                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <select id="filterCriticality" class="form-select form-select-sm">
                            <option value=""><?= tt('events_all_criticality') ?></option>
                            <option value="baja"><?= tt('events_crit_low') ?></option>
                            <option value="media"><?= tt('events_crit_medium') ?></option>
                            <option value="alta"><?= tt('events_crit_high') ?></option>
                            <option value="critica"><?= tt('events_crit_critical') ?></option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select id="filterState" class="form-select form-select-sm">
                            <option value=""><?= tt('events_all_state') ?></option>
                            <option value="1"><?= tt('events_state_open') ?></option>
                            <option value="2"><?= tt('events_state_in_progress') ?></option>
                            <option value="3"><?= tt('events_state_closed') ?></option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="<?= tt('events_search_placeholder') ?>">
                    </div>
                </div>

                <div id="eventsStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?php echo $isGlobalAdmin ? tt('events_select_to_view') : tt('events_loading'); ?>
                </div>
                <div id="eventsTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr><th><?= tt('events_col_date') ?></th><th><?= tt('events_col_type') ?></th><th><?= tt('events_col_center') ?></th><th><?= tt('events_col_criticality') ?></th><th><?= tt('common_state') ?></th><th><?= tt('common_actions') ?></th></tr>
                        </thead>
                        <tbody id="eventsTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Detalle del evento: seguimiento y evidencias -->
            <div id="eventDetail" class="feature-card mt-4 evento-detalle">
                <div class="d-flex justify-content-between align-items-start">
                    <h2 class="h5 mb-0"><?= tt('events_detail_title') ?></h2>
                    <button type="button" id="eventDetailCloseBtn" class="btn btn-outline-custom btn-sm"><?= tt('events_close') ?></button>
                </div>
                <div id="eventDetailBody" class="mt-2"></div>

                <div id="detailAlert" class="alert d-none mt-3" role="alert" aria-live="polite"></div>

                <?php if ($puedeGestionar): ?>
                <div class="subseccion">
                    <h3 class="h6"><?= tt('events_change_state_title') ?></h3>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline-custom btn-sm" data-estado="1"><?= tt('events_state_open') ?></button>
                        <button type="button" class="btn btn-outline-custom btn-sm" data-estado="2"><?= tt('events_state_in_progress') ?></button>
                        <button type="button" class="btn btn-outline-custom btn-sm" data-estado="3"><?= tt('events_state_closed') ?></button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="subseccion">
                    <h3 class="h6"><?= tt('events_tracking_title') ?></h3>
                    <div id="trackingList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <form id="trackingForm" class="row g-2">
                        <div class="col-12"><textarea id="trackingDescription" class="form-control" rows="2" placeholder="<?= tt('events_tracking_desc_placeholder') ?>" required></textarea></div>
                        <div class="col-12 col-md-4"><input type="text" id="trackingPerson" class="form-control" placeholder="<?= tt('events_tracking_person_placeholder') ?>" required></div>
                        <div class="col-6 col-md-4"><label class="form-label form-label-sm"><?= tt('events_tracking_commitment_label') ?></label><input type="date" id="trackingCommitment" class="form-control" required></div>
                        <div class="col-6 col-md-4"><label class="form-label form-label-sm"><?= tt('events_tracking_deadline_label') ?></label><input type="date" id="trackingDeadline" class="form-control" required></div>
                        <div class="col-12"><button type="submit" class="btn btn-outline-custom btn-sm"><?= tt('events_tracking_add') ?></button></div>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="subseccion">
                    <h3 class="h6"><?= tt('events_evidence_title') ?></h3>
                    <div id="evidenceList" class="mb-3"></div>

                    <form id="evidenceForm" class="row g-2">
                        <div class="col-12 col-md-8"><input type="file" id="evidenceFile" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf" required></div>
                        <div class="col-12 col-md-4"><button type="submit" class="btn btn-outline-custom btn-sm w-100"><?= tt('events_evidence_upload') ?></button></div>
                        <div class="form-text"><?= tt('events_evidence_help') ?></div>
                    </form>
                </div>

            </div>

        </section>

    </div>

    <script id="eventsI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/eventos.js?v=<?= $assetVersionEscaped ?>"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
