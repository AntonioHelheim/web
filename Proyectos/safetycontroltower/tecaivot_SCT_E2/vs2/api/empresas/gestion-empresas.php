<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'companies.view_all', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfTokenEscaped = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');

$jsKeys = [
    'common_active','common_inactive','common_edit','companies_loading','companies_empty','companies_action_edit',
    'companies_action_deactivate','companies_action_reactivate','companies_confirm_deactivate','companies_confirm_reactivate',
    'companies_saved','companies_state_updated','companies_error_load','companies_error_save','companies_error_state',
    'companies_form_new','companies_form_edit','companies_save','companies_update'
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
    <title><?= htmlspecialchars(t('companies_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
    <style>
        .welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.form-actions,.company-actions{display:flex;gap:.5rem;flex-wrap:wrap}.language-select{min-width:120px}.company-state-filter{max-width:180px}
        @media(max-width:767.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:70%}}
    </style>
</head>
<body>
<div class="container" data-csrf-token="<?= $csrfTokenEscaped ?>" data-current-lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="welcome-topbar">
        <div class="brand-wrapper"><div class="brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div></div>
        <div class="topbar-actions">
            <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'), ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?><option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
            </select>
            <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
            <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>

    <section class="welcome-hero text-center">
        <div class="welcome-greeting-icon"><i class="bi bi-building"></i></div>
        <span class="section-label">SAFETY CONTROL TOWER</span>
        <h1 class="section-title"><?= htmlspecialchars(t('companies_title'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="section-description intro-description-centered"><?= htmlspecialchars(t('companies_intro'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(t('companies_session_as'), ENT_QUOTES, 'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p>
    </section>

    <section class="quick-links">
        <div class="feature-card">
            <h2 id="companyFormTitle" class="h5 mb-3"><?= htmlspecialchars(t('companies_form_new'), ENT_QUOTES, 'UTF-8') ?></h2>
            <div id="companiesActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>
            <form id="companyForm" novalidate>
                <input type="hidden" id="companyFormMode" value="create"><input type="hidden" id="companyId" value="">
                <div class="row g-3">
                    <div class="col-12 col-md-6"><label for="companyRut" class="form-label"><?= htmlspecialchars(t('companies_rut'), ENT_QUOTES, 'UTF-8') ?></label><input type="text" id="companyRut" class="form-control" maxlength="12" required></div>
                    <div class="col-12 col-md-6"><label for="companyName" class="form-label"><?= htmlspecialchars(t('companies_name'), ENT_QUOTES, 'UTF-8') ?></label><input type="text" id="companyName" class="form-control" maxlength="150" required></div>
                    <div class="col-12 col-md-6"><label for="companyAddress" class="form-label"><?= htmlspecialchars(t('companies_address'), ENT_QUOTES, 'UTF-8') ?></label><input type="text" id="companyAddress" class="form-control" maxlength="255" required></div>
                    <div class="col-12 col-md-6"><label for="companyEmail" class="form-label"><?= htmlspecialchars(t('companies_email'), ENT_QUOTES, 'UTF-8') ?></label><input type="email" id="companyEmail" class="form-control" maxlength="50" required></div>
                </div>
                <div class="form-actions mt-3"><button type="submit" id="companySubmitBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('companies_save'), ENT_QUOTES, 'UTF-8') ?></button><button type="button" id="companyCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= htmlspecialchars(t('companies_cancel_edit'), ENT_QUOTES, 'UTF-8') ?></button></div>
            </form>
        </div>

        <div class="feature-card mt-4">
            <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-3"><h2 class="h5 mb-0"><?= htmlspecialchars(t('companies_list_title'), ENT_QUOTES, 'UTF-8') ?></h2><select id="companiesStateFilter" class="form-select form-select-sm company-state-filter"><option value="all"><?= htmlspecialchars(t('common_all'), ENT_QUOTES, 'UTF-8') ?></option><option value="1"><?= htmlspecialchars(t('common_active'), ENT_QUOTES, 'UTF-8') ?></option><option value="0"><?= htmlspecialchars(t('common_inactive'), ENT_QUOTES, 'UTF-8') ?></option></select></div>
            <div id="companiesStatus" class="alert alert-info mb-0" role="status" aria-live="polite"><?= htmlspecialchars(t('companies_loading'), ENT_QUOTES, 'UTF-8') ?></div>
            <div id="companiesTableWrapper" class="table-responsive mt-3 d-none"><table class="table table-hover align-middle mb-0"><thead><tr><th><?= htmlspecialchars(t('companies_col_id'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_rut'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_name'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_address'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_email'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_state'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_actions'), ENT_QUOTES, 'UTF-8') ?></th></tr></thead><tbody id="companiesTableBody"></tbody></table></div>
        </div>
    </section>
</div>
<script id="companiesI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>window.SCT_LANG_SWITCHER_I18N = <?= json_encode($langSwitcherStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/empresas.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
