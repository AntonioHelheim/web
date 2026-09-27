<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

require_once __DIR__ . '/../../partials/info-tip.php';
requireCapabilityPage($pdo, 'protocols.view', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');

$keys = [
    'protocols_my_loading','protocols_my_empty','protocols_execute','protocols_pending_review',
    'protocols_history','protocols_history_empty','protocols_result_pending_review',
    'protocols_result_conforme','protocols_result_observado','protocols_result_no_conforme',
    'protocols_result_no_aplica','protocols_overdue','protocols_state_active',
    'protocols_state_suspended','protocols_state_closed','protocols_state_cancelled',
    'protocols_source_official','protocols_cycle','protocols_submitted','protocols_reviewed_at',
    'protocols_target','protocols_company_wide','protocols_error_response','protocols_view','activities_result_label','activities_next_due_label','activities_status_scheduled','activities_available_from','activities_scheduled_action'
];
$jsStrings = [];
foreach ($keys as $key) $jsStrings[$key] = t($key);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('protocols_my_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>&ux=20260920-p32-v51">
<link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
<style>
.welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.language-select{min-width:120px}.protocol-card{margin:0;min-width:0}.meta-line{display:flex;gap:.45rem;flex-wrap:wrap;align-items:center;color:var(--text-secondary);font-size:.84rem}.pill{display:inline-flex;align-items:center;border-radius:999px;padding:.22rem .5rem;font-size:.72rem;font-weight:700;background:rgba(0,123,197,.11);color:var(--primary-dark)}.pill.warn{background:rgba(255,140,71,.12);color:#BC5921}.pill.danger{background:rgba(219,55,53,.10);color:#AA2424}.pill.ok{background:rgba(0,136,54,.10);color:#006725}.history-panel{display:none;margin-top:.9rem;border-top:1px solid var(--border);padding-top:.9rem}.history-panel.active{display:block}.execution-item{border:1px solid var(--border);border-radius:12px;padding:.75rem;margin-bottom:.55rem;background:var(--background-soft)}@media(max-width:767.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}.protocol-card{padding:1rem}}
</style>
</head>
<body class="sct-module-page">
<div class="container sct-main-shell sct-personal-module-shell" data-csrf-token="<?= $csrf ?>">
<?php
        $sctNavbarBasePath = '../../';
        $sctNavbarBackHref = '../usuarios/gestiones.php';
        require __DIR__ . '/../../partials/app-navbar.php';
        ?>

        <section class="welcome-hero sct-main-hero sct-personal-section-hero"><div class="sct-main-hero__copy"><span class="section-label sct-main-pill"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i><?= htmlspecialchars(t('mgmt_filter_personal'),ENT_QUOTES,'UTF-8') ?></span><h1 class="section-title sct-main-title mb-0"><?= htmlspecialchars(t('protocols_my_title'),ENT_QUOTES,'UTF-8') ?></h1><p class="sct-page-intro sct-main-intro"><?= htmlspecialchars(t('protocols_my_intro') . ' ' . ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></div></section>
<?php $sctModuleMode = 'personal'; require __DIR__ . '/../../partials/module-context.php'; ?>
<?php $sctPersonalNavActive = 'protocols'; $sctPersonalNavBasePath = '../'; require __DIR__ . '/../../partials/personal-activity-nav.php'; ?>
<section class="quick-links"><div id="myProtocolsAlert" class="alert d-none"></div><div id="myProtocolsStatus" class="alert alert-info"><?= htmlspecialchars(t('protocols_my_loading'),ENT_QUOTES,'UTF-8') ?></div><div id="myProtocolsList" class="d-none"></div></section>
</div>
<script id="myProtocolsI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/protocolos-mis.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>-p76"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>

    <?php require __DIR__ . '/../../partials/app-footer.php'; ?>

<script src="../../js/sct-module-ui.js?v=20260920-p43"></script>
</body></html>
