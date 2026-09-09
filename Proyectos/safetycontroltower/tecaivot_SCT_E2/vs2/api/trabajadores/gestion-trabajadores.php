<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

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
    'common_edit', 'common_deactivate', 'common_reactivate', 'common_active', 'common_inactive',
    'common_invalid_server_response',
    'workers_form_new', 'workers_form_edit', 'workers_save', 'workers_save_changes',
    'workers_rut_help_create', 'workers_rut_help_edit',
    'workers_select_company_first', 'workers_loading', 'workers_empty_search', 'workers_error_load',
    'workers_error_load_companies', 'workers_required_name_lastname', 'workers_required_rut',
    'workers_select_company_before_create', 'workers_error_save', 'workers_saved',
    'workers_confirm_reactivate', 'workers_confirm_deactivate', 'workers_error_state', 'workers_state_updated',
    'workers_photo_save_first', 'workers_photo_select_first', 'workers_error_photo_upload', 'workers_photo_updated',
];
$jsStrings = [];
foreach ($jsKeys as $key) {
    $jsStrings[$key] = t($key);
}

$langSwitcherStrings = [
    'title' => t('common_confirm_language_title'),
    'text' => t('common_confirm_language_text'),
    'confirm' => t('common_confirm'),
    'cancel' => t('common_cancel'),
    'updated' => t('common_language_updated'),
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('workers_page_title'), ENT_QUOTES, 'UTF-8') ?></title>

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
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img { height: 32px; }
        .topbar-actions { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .language-select { min-width: 120px; }
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
                <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm">
                    <?= htmlspecialchars(t('mgmt_back'), ENT_QUOTES, 'UTF-8') ?>
                    <i class="bi bi-arrow-left"></i>
                </a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm">
                    <?= htmlspecialchars(t('common_logout'), ENT_QUOTES, 'UTF-8') ?>
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon">
                <i class="bi bi-person-badge"></i>
            </div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title"><?= htmlspecialchars(t('workers_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="section-description intro-description-centered">
                <?= htmlspecialchars(t('workers_intro'), ENT_QUOTES, 'UTF-8') ?>
                <?= htmlspecialchars(t('companies_session_as'), ENT_QUOTES, 'UTF-8') ?> <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3"><?= htmlspecialchars(t('common_company_section_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-muted mb-3">
                    <?= htmlspecialchars(t('workers_company_section_text'), ENT_QUOTES, 'UTF-8') ?>
                </p>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label"><?= htmlspecialchars(t('common_company_section_title'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="companySelect" class="form-select">
                            <option value=""><?= htmlspecialchars(t('common_select_company_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($puedeGestionar): ?>
            <div class="feature-card">
                <h2 class="h5 mb-3" id="workerFormTitle"><?= htmlspecialchars(t('workers_form_new'), ENT_QUOTES, 'UTF-8') ?></h2>

                <div id="workersActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                <form id="workerForm" novalidate>
                    <input type="hidden" id="workerFormMode" value="create">
                    <input type="hidden" id="workerFormTarget" value="">

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="workerRut" class="form-label"><?= htmlspecialchars(t('workers_rut'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="workerRut" class="form-control" placeholder="12345678-9" maxlength="12" required>
                            <div class="form-text" id="workerRutHelp"><?= htmlspecialchars(t('workers_rut_help_create'), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerName" class="form-label"><?= htmlspecialchars(t('common_name'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="workerName" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerLastname" class="form-label"><?= htmlspecialchars(t('workers_lastname'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="workerLastname" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerEmail" class="form-label"><?= htmlspecialchars(t('workers_email_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="email" id="workerEmail" class="form-control" maxlength="150">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerPhone" class="form-label"><?= htmlspecialchars(t('workers_phone_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="workerPhone" class="form-control" maxlength="20">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="workerPosition" class="form-label"><?= htmlspecialchars(t('workers_position_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="workerPosition" class="form-control" maxlength="100">
                        </div>
                    </div>

                    <div class="form-actions mt-3">
                        <button type="submit" id="workerSubmitBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('workers_save'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="button" id="workerCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= htmlspecialchars(t('common_cancel_edit'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>

                <div id="workerPhotoSection" class="mt-4 d-none">
                    <hr>
                    <h3 class="h6 mb-2"><?= htmlspecialchars(t('workers_photo_section_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <div class="d-flex align-items-center gap-3">
                        <img id="workerPhotoPreview" class="worker-photo-thumb" style="width:64px;height:64px;" src="" alt="">
                        <div>
                            <input type="file" id="workerPhotoInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text"><?= htmlspecialchars(t('workers_photo_help'), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <button type="button" id="workerPhotoUploadBtn" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('workers_photo_upload_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3"><?= htmlspecialchars(t('workers_list_title'), ENT_QUOTES, 'UTF-8') ?></h2>

                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-6">
                        <input type="text" id="workerSearchInput" class="form-control" placeholder="<?= htmlspecialchars(t('workers_search_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <select id="workerStateFilter" class="form-select">
                            <option value=""><?= htmlspecialchars(t('workers_filter_all_states'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="1"><?= htmlspecialchars(t('workers_filter_active_only'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="0"><?= htmlspecialchars(t('workers_filter_inactive_only'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>
                </div>

                <div id="workersStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?= htmlspecialchars($isGlobalAdmin ? t('workers_select_company_first') : t('workers_loading'), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div id="workersTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col"><?= htmlspecialchars(t('workers_rut'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('common_name'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('workers_col_position'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('workers_col_contact'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('common_state'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('common_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>
                        </thead>
                        <tbody id="workersTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>

    <script id="workersI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script>window.SCT_LANG_SWITCHER_I18N = <?= json_encode($langSwitcherStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
    <script src="../../js/trabajadores.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
