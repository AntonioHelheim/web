<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

historialRequireViewPage($pdo, '../../acceso-denegado.php');
aplicarCabecerasSeguridad();

$isGlobal = historialIsGlobal($pdo);
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');
$keys = [
    'history_loading','history_error','history_empty','history_all','history_apply','history_clear',
    'history_company','history_module','history_action','history_actor','history_from','history_to','history_search',
    'history_total','history_operations','history_actors','history_today','history_date','history_record','history_field',
    'history_before','history_after','history_user','history_details','history_page','history_previous','history_next',
    'history_migration_required'
];
$strings=[]; foreach($keys as $key) $strings[$key]=t($key);
?>
<!doctype html>
<html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars(t('history_page_title'),ENT_QUOTES,'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
<style>
body{background:radial-gradient(circle at 10% 0%,rgba(0,163,244,.07),transparent 28rem),var(--background);min-height:100vh}.history-shell{width:min(1280px,100%);margin:0 auto;padding:0 24px 64px}.history-topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 0}.history-brand{display:flex;align-items:center;gap:10px}.history-brand-symbol{width:42px;height:42px;border-radius:12px;background:var(--primary-darkest);display:flex;align-items:center;justify-content:center}.history-brand-symbol img{width:28px;height:28px;object-fit:contain}.history-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.language-select{width:auto;min-width:118px}.history-hero{padding:28px 0 16px}.history-hero h1{color:var(--primary-darkest);font-weight:850;letter-spacing:-1px}.history-hero p{max-width:820px;color:var(--text-secondary)}.history-card{background:#fff;border:1px solid var(--border);border-radius:18px;padding:18px;box-shadow:0 8px 24px rgba(31,45,61,.035)}.history-filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.history-filters .span2{grid-column:span 2}.history-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:16px 0}.history-kpi{background:#fff;border:1px solid var(--border);border-radius:16px;padding:16px}.history-kpi .value{font-size:26px;font-weight:850;color:var(--primary-darkest)}.history-kpi .label{font-size:12px;color:var(--text-secondary)}.history-table td,.history-table th{vertical-align:middle}.history-table .value-preview{max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.history-tag{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:var(--background-soft);font-size:11px;font-weight:750}.history-mobile{display:none}.history-mobile-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;margin-bottom:10px}.history-mobile-card .change{display:grid;grid-template-columns:1fr;gap:4px;margin-top:10px}.history-value{white-space:pre-wrap;word-break:break-word;background:var(--background-soft);border:1px solid var(--border);border-radius:10px;padding:10px;min-height:44px}.history-pager{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:14px}.filter-actions{display:flex;gap:8px;align-items:end}@media(max-width:991.98px){.history-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.history-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:767.98px){.history-shell{padding-left:16px;padding-right:16px}.history-topbar{align-items:flex-start}.history-actions{justify-content:flex-end}.history-filters{grid-template-columns:1fr}.history-filters .span2{grid-column:span 1}.history-table-wrap{display:none}.history-mobile{display:block}}@media(max-width:460px){.history-kpis{grid-template-columns:1fr 1fr}.history-kpi .value{font-size:22px}}
</style>
</head>
<body>
<div class="history-shell" data-is-global="<?= $isGlobal?'1':'0' ?>">
<div class="history-topbar"><div class="history-brand"><div class="history-brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div><div><strong>Safety Control Tower</strong><div class="small text-muted">ETAPA 3 · AUDIT TRAIL</div></div></div><div class="history-actions"><select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'),ENT_QUOTES,'UTF-8') ?>"><?php foreach(idiomasDisponiblesConNombre() as $code=>$name): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===idiomaActual()?'selected':'' ?>><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select><a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-arrow-left"></i></a><a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a></div></div>
<section class="history-hero"><span class="section-label">SAFETY CONTROL TOWER</span><h1><?= htmlspecialchars(t('history_title'),ENT_QUOTES,'UTF-8') ?></h1><p><?= htmlspecialchars(t('history_intro'),ENT_QUOTES,'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p></section>
<div id="historyAlert" class="alert d-none" role="alert" aria-live="polite"></div>
<section class="history-card">
<div class="history-filters">
<?php if($isGlobal): ?><div><label class="form-label" for="companySelect"><?= htmlspecialchars(t('history_company'),ENT_QUOTES,'UTF-8') ?></label><select id="companySelect" class="form-select"><option value=""><?= htmlspecialchars(t('history_all'),ENT_QUOTES,'UTF-8') ?></option></select></div><?php endif; ?>
<div><label class="form-label" for="moduleSelect"><?= htmlspecialchars(t('history_module'),ENT_QUOTES,'UTF-8') ?></label><select id="moduleSelect" class="form-select"><option value=""><?= htmlspecialchars(t('history_all'),ENT_QUOTES,'UTF-8') ?></option></select></div>
<div><label class="form-label" for="actionSelect"><?= htmlspecialchars(t('history_action'),ENT_QUOTES,'UTF-8') ?></label><select id="actionSelect" class="form-select"><option value=""><?= htmlspecialchars(t('history_all'),ENT_QUOTES,'UTF-8') ?></option></select></div>
<div><label class="form-label" for="actorSelect"><?= htmlspecialchars(t('history_actor'),ENT_QUOTES,'UTF-8') ?></label><select id="actorSelect" class="form-select"><option value=""><?= htmlspecialchars(t('history_all'),ENT_QUOTES,'UTF-8') ?></option></select></div>
<div><label class="form-label" for="dateFrom"><?= htmlspecialchars(t('history_from'),ENT_QUOTES,'UTF-8') ?></label><input id="dateFrom" type="date" class="form-control"></div>
<div><label class="form-label" for="dateTo"><?= htmlspecialchars(t('history_to'),ENT_QUOTES,'UTF-8') ?></label><input id="dateTo" type="date" class="form-control"></div>
<div class="span2"><label class="form-label" for="searchInput"><?= htmlspecialchars(t('history_search'),ENT_QUOTES,'UTF-8') ?></label><input id="searchInput" class="form-control" maxlength="120" placeholder="<?= htmlspecialchars(t('history_search_placeholder'),ENT_QUOTES,'UTF-8') ?>"></div>
<div class="filter-actions"><button id="applyFilters" type="button" class="btn btn-primary-custom"><?= htmlspecialchars(t('history_apply'),ENT_QUOTES,'UTF-8') ?></button><button id="clearFilters" type="button" class="btn btn-outline-custom"><?= htmlspecialchars(t('history_clear'),ENT_QUOTES,'UTF-8') ?></button></div>
</div>
</section>
<div class="history-kpis"><div class="history-kpi"><div id="kpiTotal" class="value">—</div><div class="label"><?= htmlspecialchars(t('history_total'),ENT_QUOTES,'UTF-8') ?></div></div><div class="history-kpi"><div id="kpiOperations" class="value">—</div><div class="label"><?= htmlspecialchars(t('history_operations'),ENT_QUOTES,'UTF-8') ?></div></div><div class="history-kpi"><div id="kpiActors" class="value">—</div><div class="label"><?= htmlspecialchars(t('history_actors'),ENT_QUOTES,'UTF-8') ?></div></div><div class="history-kpi"><div id="kpiToday" class="value">—</div><div class="label"><?= htmlspecialchars(t('history_today'),ENT_QUOTES,'UTF-8') ?></div></div></div>
<section class="history-card">
<div id="historyStatus" class="alert alert-info mb-3"><?= htmlspecialchars(t('history_loading'),ENT_QUOTES,'UTF-8') ?></div>
<div class="history-table-wrap table-responsive"><table class="table table-hover history-table align-middle"><thead><tr><th><?= htmlspecialchars(t('history_date'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('history_module'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('history_action'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('history_record'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('history_field'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('history_before'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('history_after'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('history_user'),ENT_QUOTES,'UTF-8') ?></th><th></th></tr></thead><tbody id="historyTbody"></tbody></table></div>
<div id="historyMobile" class="history-mobile"></div>
<div class="history-pager"><button id="prevPage" class="btn btn-outline-custom btn-sm" type="button"><i class="bi bi-chevron-left"></i> <?= htmlspecialchars(t('history_previous'),ENT_QUOTES,'UTF-8') ?></button><span id="pageInfo" class="small text-muted"></span><button id="nextPage" class="btn btn-outline-custom btn-sm" type="button"><?= htmlspecialchars(t('history_next'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-chevron-right"></i></button></div>
</section>
</div>
<div class="modal fade" id="historyDetailModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5"><?= htmlspecialchars(t('history_details'),ENT_QUOTES,'UTF-8') ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="historyDetailBody"></div></div></div></div>
<script id="historyI18n" type="application/json"><?= json_encode($strings,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/historial.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
</body></html>
