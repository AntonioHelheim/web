<?php
require_once __DIR__ . '/common.php';

// Página HTML normal (no endpoint JSON): si no hay sesión, redirige.
requireCapabilityPage($pdo, 'projects.view', '../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = currentUserHasCapability($pdo, 'projects.manage');
$isGlobalAdmin = proyectosIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');

$jsKeys = [
    'projects_companies_error','projects_select_to_view','projects_loading','projects_load_error','projects_empty',
    'common_active','common_inactive','projects_deactivate','projects_reactivate','common_edit','projects_workers_btn',
    'projects_save_changes','projects_save','projects_required','projects_select_company_first',
    'projects_save_error','projects_saved','projects_confirm_reactivate','projects_confirm_deactivate',
    'projects_state_error','projects_state_updated','projects_error_response',
    'projects_workers_modal_title','projects_workers_loading','projects_workers_load_error','projects_workers_empty',
    'projects_workers_remove_title','projects_workers_remove_confirm','projects_workers_remove_error',
    'projects_workers_search_min_chars','projects_workers_search_error','projects_workers_search_empty',
    'projects_workers_associate_error',
];
$jsStrings = [];
foreach ($jsKeys as $k) $jsStrings[$k] = t($k);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tt('projects_page_title') ?></title>

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
        .worker-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.6rem;
            border-radius: 999px;
            background: rgba(22, 163, 74, 0.08);
            font-size: 0.85rem;
        }
        .worker-chip button {
            border: none;
            background: none;
            color: #dc2626;
            line-height: 1;
            padding: 0;
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
                <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= tt('common_language') ?>">
                    <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm">
                    <?= tt('mgmt_back') ?>
                    <i class="bi bi-arrow-left"></i>
                </a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm">
                    <?= tt('common_logout') ?>
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon">
                <i class="bi bi-diagram-3"></i>
            </div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title"><?= tt('projects_title') ?></h1>
            <p class="section-description intro-description-centered">
                <?= tt('projects_intro') ?>
                <?= tt('users_session_as') ?> <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3"><?= tt('projects_company_section_title') ?></h2>
                <p class="text-muted mb-3">
                    <?= tt('projects_company_section_text') ?>
                </p>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label"><?= tt('projects_company_section_title') ?></label>
                        <select id="companySelect" class="form-select">
                            <option value=""><?= tt('projects_select_company') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($puedeGestionar): ?>
            <div class="feature-card">
                <h2 class="h5 mb-3"><?= tt('projects_new_title') ?></h2>

                <div id="projectsActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                <form id="projectForm" novalidate>
                    <input type="hidden" id="projectFormMode" value="create">
                    <input type="hidden" id="projectFormTarget" value="">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="projectName" class="form-label"><?= tt('projects_name') ?></label>
                            <input type="text" id="projectName" class="form-control" maxlength="150" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="projectDescription" class="form-label"><?= tt('projects_description') ?></label>
                            <input type="text" id="projectDescription" class="form-control" maxlength="255">
                        </div>
                    </div>

                    <div class="form-actions mt-3">
                        <button type="submit" id="projectSubmitBtn" class="btn btn-primary-custom btn-sm"><?= tt('projects_save') ?></button>
                        <button type="button" id="projectCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= tt('projects_cancel_edit') ?></button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3"><?= tt('projects_list_title') ?></h2>

                <div id="projectsStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?php echo $isGlobalAdmin ? tt('projects_select_to_view') : tt('projects_loading'); ?>
                </div>

                <div id="projectsTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col"><?= tt('projects_col_id') ?></th>
                                <th scope="col"><?= tt('projects_name') ?></th>
                                <th scope="col"><?= tt('projects_col_description') ?></th>
                                <th scope="col"><?= tt('common_state') ?></th>
                                <th scope="col"><?= tt('common_actions') ?></th>
                            </tr>
                        </thead>
                        <tbody id="projectsTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>

    <!-- Panel de trabajadores asociados a un proyecto -->
    <div class="modal fade" id="projectWorkersModal" tabindex="-1" aria-labelledby="projectWorkersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 id="projectWorkersModalLabel" class="mb-0"><?= tt('projects_workers_modal_title') ?></h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= tt('projects_workers_close') ?>"></button>
                    </div>

                    <p class="text-muted" id="projectWorkersModalSubtitle">-</p>

                    <div id="projectWorkersAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                    <?php if ($puedeGestionar): ?>
                    <div class="mb-4">
                        <label for="workerSearchInput" class="form-label"><?= tt('projects_workers_search_label') ?></label>
                        <div class="input-group">
                            <input type="text" id="workerSearchInput" class="form-control" placeholder="<?= tt('projects_workers_search_placeholder') ?>" autocomplete="off">
                            <button type="button" id="workerSearchBtn" class="btn btn-outline-custom"><?= tt('projects_workers_search_btn') ?></button>
                        </div>
                        <div id="workerSearchResults" class="list-group mt-2"></div>
                        <div class="form-text">
                            <?= tt('projects_workers_search_help') ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <h3 class="h6"><?= tt('projects_workers_associated_title') ?></h3>
                    <div id="projectWorkersList">
                        <p class="text-muted mb-0"><?= tt('projects_workers_loading') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script id="projectsI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/proyectos.js?v=<?= $assetVersionEscaped ?>"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
