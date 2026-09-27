<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

require_once __DIR__ . '/../../partials/info-tip.php';
requireCapabilityPage($pdo, 'audits.view', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
$embeddedActivityMode = isset($_GET['embedded']) && (string) $_GET['embedded'] === '1';
if ($embeddedActivityMode) {
    // El HUB Mi espacio sólo puede embeber actividades desde el mismo origen.
    header('X-Frame-Options: SAMEORIGIN');
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');
$directActivityMode = filter_input(INPUT_GET, 'start', FILTER_VALIDATE_INT) > 0;
$jsKeys = [
    'audit_error_response','audit_result','audit_observations','audit_attempts_used',
    'my_audits_loading','my_audits_empty','my_audits_execute','my_audits_no_attempts','my_audits_due',
    'my_audits_compliant','my_audits_non_compliant','my_audits_pending','my_audits_must_answer',
    'my_audits_completed_ok','my_audits_completed_fail','my_audits_retry','activities_status_label',
    'activity_save_draft','activity_exit','activity_draft_saved','activity_draft_restored',
    'activity_complete_before_submit','activity_confirm_submit','activity_overdue','activity_late_warning','activity_submit_late','activity_confirm_submit_late'
];
$jsStrings = [];
foreach ($jsKeys as $key) $jsStrings[$key] = t($key);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('my_audits_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>&ux=20260920-p35-v53">
<link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
    <style>
        .welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.language-select{min-width:120px}.audit-card{margin:0;min-width:0}.question-block{border-bottom:1px solid var(--border);padding-bottom:1rem;margin-bottom:1rem}.question-block:last-child{border-bottom:0}.result-score{font-size:1.15rem;font-weight:800;color:var(--primary-darkest)}
        @media(max-width:767.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}}
    </style>
</head>
<body class="sct-module-page<?= $embeddedActivityMode ? ' sct-embedded-activity' : '' ?>">
<div class="container sct-main-shell sct-personal-module-shell" data-csrf-token="<?= $csrf ?>" data-activity-user="<?= htmlspecialchars(hash('sha256', (string) ($_SESSION['user_email'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
    <?php
        $sctNavbarBasePath = '../../';
        $sctNavbarBackHref = '../usuarios/gestiones.php';
        if (!$embeddedActivityMode) { require __DIR__ . '/../../partials/app-navbar.php'; }
        ?>

        <section class="welcome-hero sct-main-hero sct-personal-section-hero">
        <div class="sct-main-hero__copy">
            <span class="section-label sct-main-pill"><i class="bi bi-clipboard2-check" aria-hidden="true"></i><?= htmlspecialchars(t('mgmt_filter_personal'), ENT_QUOTES, 'UTF-8') ?></span>
            <h1 class="section-title sct-main-title mb-0"><?= htmlspecialchars(t('my_audits_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="sct-page-intro sct-main-intro"><?= htmlspecialchars(t('my_audits_intro') . ' ' . t('audit_session_as') . ' ' . ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </section>
<?php $sctModuleMode = 'personal'; require __DIR__ . '/../../partials/module-context.php'; ?>
<?php $sctPersonalNavActive = 'audits'; $sctPersonalNavBasePath = '../'; require __DIR__ . '/../../partials/personal-activity-nav.php'; ?>

    <section class="quick-links">
        <div id="myAuditsBrowse" class="sct-activity-browse<?= $directActivityMode ? ' d-none' : '' ?>"<?= $directActivityMode ? ' hidden aria-hidden="true"' : '' ?>>
            <div id="myAuditsAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>
            <div id="myAuditsStatus" class="alert alert-info" role="status"><?= htmlspecialchars(t('my_audits_loading'), ENT_QUOTES, 'UTF-8') ?></div>
            <div id="myAuditsList" class="d-none"></div>
        </div>

        <div id="executePanel" class="feature-card mt-4 d-none">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><span class="section-label"><?= htmlspecialchars(t('my_audits_execute_title'), ENT_QUOTES, 'UTF-8') ?></span><h2 id="executeTitle" class="h5 mb-0">-</h2></div><?php if (!$embeddedActivityMode): ?><button type="button" id="executeCloseBtn" class="btn btn-outline-custom btn-sm"><i class="bi bi-box-arrow-left"></i> <?= htmlspecialchars(t('activity_exit'), ENT_QUOTES, 'UTF-8') ?></button><?php endif; ?></div>
            <div id="executeAlert" class="alert d-none" role="alert" aria-live="polite"></div>
            <div id="executeQuestions"></div>
            <div class="mt-3"><label for="executeObservations" class="form-label"><?= htmlspecialchars(t('my_audits_observations_label'), ENT_QUOTES, 'UTF-8') ?></label><textarea id="executeObservations" class="form-control" rows="4" maxlength="5000"></textarea><div class="form-text"><?= htmlspecialchars(t('my_audits_observations_help'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="sct-activity-actions mt-3"><button type="button" id="executeSaveBtn" class="btn btn-outline-custom"><i class="bi bi-save"></i> <?= htmlspecialchars(t('activity_save_draft'), ENT_QUOTES, 'UTF-8') ?></button><button type="button" id="executeSubmitBtn" class="btn btn-primary-custom"><i class="bi bi-send"></i> <?= htmlspecialchars(t('my_audits_submit'), ENT_QUOTES, 'UTF-8') ?></button></div>
        </div>
    </section>
</div>
<script id="myAuditsI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/sct-activity-session.js?v=20260920-p35-v53"></script>
<script src="../../js/auditorias-mis.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>-p35-v53"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>

    <?php if (!$embeddedActivityMode) { require __DIR__ . '/../../partials/app-footer.php'; } ?>

<script src="../../js/sct-module-ui.js?v=20260920-p43"></script>
</body>
</html>
