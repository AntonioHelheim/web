<?php
require_once __DIR__ . '/common.php';

requireCapabilityPage($pdo, 'workers.view', '../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = currentUserHasCapability($pdo, 'workers.manage');
$isGlobalAdmin = trabajadoresIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');

$jsKeys = [
    'workers_companies_error','workers_select_to_view','workers_loading','workers_load_error','workers_no_match',
    'common_active','common_inactive','workers_deactivate','workers_reactivate','common_edit',
    'workers_edit_rut_help','workers_new_title','workers_edit_title','workers_save','workers_save_changes',
    'workers_required','workers_rut_required','workers_select_company_first','workers_save_error','workers_saved',
    'workers_confirm_reactivate','workers_confirm_deactivate','workers_state_error','workers_state_updated',
    'workers_photo_save_first','workers_photo_select_first','workers_photo_error','workers_photo_updated',
    'workers_error_response','workers_rut_help_default',
];
$jsStrings = [];
foreach ($jsKeys as $k) $jsStrings[$k] = t($k);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tt('workers_page_title') ?></title>

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
                <i class="bi bi-person-badge"></i>
            </div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title"><?= tt('workers_title') ?></h1>
            <p class="section-description intro-description-centered">
                <?= tt('workers_intro') ?>
                <?= tt('users_session_as') ?> <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3"><?= tt('workers_company_section_title') ?></h2>
                <p class="text-muted mb-3">
                    <?= tt('workers_company_section_text') ?>
                </p>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label"><?= tt('workers_company_section_title') ?></label>
                        <select id="companySelect" class="form-select">
                            <option value=""><?= tt('workers_select_company') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($puedeGestionar): ?>
            <div class="feature-card">
                <h2 class="h5 mb-3" id="workerFormTitle"><?= tt('workers_new_title') ?></h2>

                <div id="workersActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                <form id="workerForm" novalidate>
                    <input type="hidden" id="workerFormMode" value="create">
                    <input type="hidden" id="workerFormTarget" value="">

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="workerRut" class="form-label"><?= tt('workers_rut') ?></label>
                            <input type="text" id="workerRut" class="form-control" placeholder="12345678-9" maxlength="12" required>
                            <div class="form-text" id="workerRutHelp"><?= tt('workers_rut_help_default') ?></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerName" class="form-label"><?= tt('workers_name') ?></label>
                            <input type="text" id="workerName" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerLastname" class="form-label"><?= tt('workers_lastname') ?></label>
                            <input type="text" id="workerLastname" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerEmail" class="form-label"><?= tt('workers_email') ?></label>
                            <input type="email" id="workerEmail" class="form-control" maxlength="150">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerPhone" class="form-label"><?= tt('workers_phone') ?></label>
                            <input type="text" id="workerPhone" class="form-control" maxlength="20">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerPosition" class="form-label"><?= tt('workers_position') ?></label>
                            <input type="text" id="workerPosition" class="form-control" maxlength="100">
                        </div>
                    </div>

                    <div class="form-actions mt-3">
                        <button type="submit" id="workerSubmitBtn" class="btn btn-primary-custom btn-sm"><?= tt('workers_save') ?></button>
                        <button type="button" id="workerCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= tt('projects_cancel_edit') ?></button>
                    </div>
                </form>

                <div id="workerPhotoSection" class="mt-4 d-none">
                    <hr>
                    <h3 class="h6 mb-2"><?= tt('workers_photo_title') ?></h3>
                    <div class="d-flex align-items-center gap-3">
                        <img id="workerPhotoPreview" class="worker-photo-thumb" style="width:64px;height:64px;" src="" alt="">
                        <div>
                            <input type="file" id="workerPhotoInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text"><?= tt('workers_photo_help') ?></div>
                        </div>
                        <button type="button" id="workerPhotoUploadBtn" class="btn btn-outline-custom btn-sm"><?= tt('workers_photo_upload') ?></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3"><?= tt('workers_list_title') ?></h2>

                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-6">
                        <input type="text" id="workerSearchInput" class="form-control" placeholder="<?= tt('workers_search_placeholder') ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <select id="workerStateFilter" class="form-select">
                            <option value=""><?= tt('workers_all_states') ?></option>
                            <option value="1"><?= tt('workers_only_active') ?></option>
                            <option value="0"><?= tt('workers_only_inactive') ?></option>
                        </select>
                    </div>
                </div>

                <div id="workersStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?php echo $isGlobalAdmin ? tt('workers_select_to_view') : tt('workers_loading'); ?>
                </div>

                <div id="workersTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col"><?= tt('workers_rut') ?></th>
                                <th scope="col"><?= tt('workers_name') ?></th>
                                <th scope="col"><?= tt('workers_position') ?></th>
                                <th scope="col"><?= tt('workers_contact') ?></th>
                                <th scope="col"><?= tt('common_state') ?></th>
                                <th scope="col"><?= tt('common_actions') ?></th>
                            </tr>
                        </thead>
                        <tbody id="workersTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>

    <script id="workersI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/trabajadores.js?v=<?= $assetVersionEscaped ?>"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
