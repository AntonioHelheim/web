<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'audits.view', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');
$jsKeys = [
    'audit_error_response','audit_result','audit_observations','audit_attempts_used',
    'my_audits_loading','my_audits_empty','my_audits_execute','my_audits_no_attempts','my_audits_due',
    'my_audits_compliant','my_audits_non_compliant','my_audits_pending','my_audits_must_answer',
    'my_audits_completed_ok','my_audits_completed_fail','my_audits_retry'
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
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
    <style>
        .welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.language-select{min-width:120px}.audit-card{border:1px solid var(--border);border-radius:var(--radius-md);padding:1.15rem;margin-bottom:.9rem;background:#fff}.audit-card.completed{border-left:4px solid #16a34a}.audit-card.failed{border-left:4px solid #dc2626}.audit-card.pending{border-left:4px solid #d97706}.question-block{border-bottom:1px solid var(--border);padding-bottom:1rem;margin-bottom:1rem}.question-block:last-child{border-bottom:0}.result-score{font-size:1.15rem;font-weight:800;color:var(--primary-darkest)}
        @media(max-width:767.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}}
    </style>
</head>
<body>
<div class="container" data-csrf-token="<?= $csrf ?>">
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
        <div class="welcome-greeting-icon"><i class="bi bi-clipboard2-check"></i></div>
        <span class="section-label">SAFETY CONTROL TOWER</span>
        <h1 class="section-title"><?= htmlspecialchars(t('my_audits_title'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="section-description intro-description-centered"><?= htmlspecialchars(t('my_audits_intro'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(t('audit_session_as'), ENT_QUOTES, 'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p>
    </section>

    <section class="quick-links">
        <div id="myAuditsAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>
        <div id="myAuditsStatus" class="alert alert-info" role="status"><?= htmlspecialchars(t('my_audits_loading'), ENT_QUOTES, 'UTF-8') ?></div>
        <div id="myAuditsList" class="d-none"></div>

        <div id="executePanel" class="feature-card mt-4 d-none">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><span class="section-label"><?= htmlspecialchars(t('my_audits_execute_title'), ENT_QUOTES, 'UTF-8') ?></span><h2 id="executeTitle" class="h5 mb-0">-</h2></div><button type="button" id="executeCloseBtn" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('my_audits_close'), ENT_QUOTES, 'UTF-8') ?></button></div>
            <div id="executeAlert" class="alert d-none" role="alert" aria-live="polite"></div>
            <div id="executeQuestions"></div>
            <div class="mt-3"><label for="executeObservations" class="form-label"><?= htmlspecialchars(t('my_audits_observations_label'), ENT_QUOTES, 'UTF-8') ?></label><textarea id="executeObservations" class="form-control" rows="4" maxlength="5000"></textarea><div class="form-text"><?= htmlspecialchars(t('my_audits_observations_help'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <button type="button" id="executeSubmitBtn" class="btn btn-primary-custom mt-3"><?= htmlspecialchars(t('my_audits_submit'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
    </section>
</div>
<script id="myAuditsI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/auditorias-mis.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
