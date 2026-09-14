<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

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
    'protocols_target','protocols_company_wide','protocols_error_response','protocols_view'
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
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
<style>
.welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.language-select{min-width:120px}.protocol-card{border:1px solid var(--border);border-radius:var(--radius-md);padding:1.1rem 1.2rem;margin-bottom:.9rem;background:#fff}.protocol-card.overdue{border-left:4px solid #dc2626}.protocol-card.pending-review{border-left:4px solid #d97706}.protocol-card.active{border-left:4px solid var(--primary)}.meta-line{display:flex;gap:.45rem;flex-wrap:wrap;align-items:center;color:var(--text-secondary);font-size:.84rem}.pill{display:inline-flex;align-items:center;border-radius:999px;padding:.22rem .5rem;font-size:.72rem;font-weight:700;background:rgba(0,163,244,.11);color:var(--primary-dark)}.pill.warn{background:rgba(217,119,6,.12);color:#b45309}.pill.danger{background:rgba(220,38,38,.10);color:#b91c1c}.pill.ok{background:rgba(22,163,74,.10);color:#15803d}.history-panel{display:none;margin-top:.9rem;border-top:1px solid var(--border);padding-top:.9rem}.history-panel.active{display:block}.execution-item{border:1px solid var(--border);border-radius:12px;padding:.75rem;margin-bottom:.55rem;background:var(--background-soft)}@media(max-width:767.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}.protocol-card{padding:1rem}}
</style>
</head>
<body>
<div class="container" data-csrf-token="<?= $csrf ?>">
<div class="welcome-topbar">
<div class="brand-wrapper"><div class="brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div></div>
<div class="topbar-actions">
<select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'),ENT_QUOTES,'UTF-8') ?>"><?php foreach(idiomasDisponiblesConNombre() as $code=>$name): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===idiomaActual()?'selected':'' ?>><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select>
<a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
<a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
</div></div>
<section class="welcome-hero text-center"><div class="welcome-greeting-icon"><i class="bi bi-clipboard2-pulse"></i></div><span class="section-label">SAFETY CONTROL TOWER</span><h1 class="section-title"><?= htmlspecialchars(t('protocols_my_title'),ENT_QUOTES,'UTF-8') ?></h1><p class="section-description intro-description-centered"><?= htmlspecialchars(t('protocols_my_intro'),ENT_QUOTES,'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p></section>
<section class="quick-links"><div id="myProtocolsAlert" class="alert d-none"></div><div id="myProtocolsStatus" class="alert alert-info"><?= htmlspecialchars(t('protocols_my_loading'),ENT_QUOTES,'UTF-8') ?></div><div id="myProtocolsList" class="d-none"></div></section>
</div>
<script id="myProtocolsI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/protocolos-mis.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body></html>
