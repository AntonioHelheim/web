<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'centers.view', '../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = currentUserHasCapability($pdo, 'centers.manage');
$isGlobalAdmin = centrosIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');

$jsKeys = [
    'common_edit', 'common_deactivate', 'common_reactivate', 'common_active', 'common_inactive',
    'common_select_company_placeholder',
    'centers_select_company_first', 'centers_loading', 'centers_empty', 'centers_error_load',
    'centers_error_load_companies', 'centers_confirm_deactivate', 'centers_confirm_reactivate',
    'centers_saved', 'centers_state_updated', 'centers_error_save', 'centers_error_state',
    'centers_required_fields', 'centers_select_company_before_create', 'centers_save', 'centers_save_changes',
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
    <title><?= htmlspecialchars(t('centers_page_title'), ENT_QUOTES, 'UTF-8') ?></title>

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
                <i class="bi bi-geo-alt"></i>
            </div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title"><?= htmlspecialchars(t('centers_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="section-description intro-description-centered">
                <?= htmlspecialchars(t('centers_intro'), ENT_QUOTES, 'UTF-8') ?>
                <?= htmlspecialchars(t('companies_session_as'), ENT_QUOTES, 'UTF-8') ?> <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3"><?= htmlspecialchars(t('centers_company_section_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-muted mb-3">
                    <?= htmlspecialchars(t('centers_company_section_text'), ENT_QUOTES, 'UTF-8') ?>
                </p>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label"><?= htmlspecialchars(t('centers_company'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="companySelect" class="form-select">
                            <option value=""><?= htmlspecialchars(t('common_select_company_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($puedeGestionar): ?>
            <div class="feature-card">
                <h2 class="h5 mb-3"><?= htmlspecialchars(t('centers_form_new'), ENT_QUOTES, 'UTF-8') ?></h2>

                <div id="centersActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                <form id="centerForm" novalidate>
                    <input type="hidden" id="centerFormMode" value="create">
                    <input type="hidden" id="centerFormTarget" value="">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="centerName" class="form-label"><?= htmlspecialchars(t('centers_name'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="centerName" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="centerDescription" class="form-label"><?= htmlspecialchars(t('centers_description'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="centerDescription" class="form-control" maxlength="255" required>
                        </div>
                    </div>

                    <div class="form-actions mt-3">
                        <button type="submit" id="centerSubmitBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('centers_save'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="button" id="centerCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= htmlspecialchars(t('centers_cancel_edit'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3"><?= htmlspecialchars(t('centers_list_title'), ENT_QUOTES, 'UTF-8') ?></h2>

                <div id="centersStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?= htmlspecialchars($isGlobalAdmin ? t('centers_select_company_first') : t('centers_loading'), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div id="centersTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col"><?= htmlspecialchars(t('centers_col_id'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('centers_col_name'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('centers_col_description'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('common_state'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col"><?= htmlspecialchars(t('common_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>
                        </thead>
                        <tbody id="centersTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>

    <script id="centersI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script>window.SCT_LANG_SWITCHER_I18N = <?= json_encode($langSwitcherStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
    <script src="../../js/centros.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
