<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

permisosRequireManagePage($pdo, '../../acceso-denegado.php');
aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');

$keys = [
    'permissions_loading','permissions_error','permissions_no_roles','permissions_no_permissions',
    'permissions_saved','permissions_save','permissions_select_company','permissions_select_role',
    'permissions_selected_count','permissions_protected','permissions_select_all','permissions_clear_all'
];
$strings = [];
foreach ($keys as $key) $strings[$key] = t($key);
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
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('permissions_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
<style>
body{background:radial-gradient(circle at 10% 0%,rgba(0,163,244,.07),transparent 28rem),var(--background);min-height:100vh}.perm-shell{width:min(1180px,100%);margin:0 auto;padding:0 24px 64px}.perm-topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 0}.perm-brand{display:flex;align-items:center;gap:10px}.perm-brand-symbol{width:42px;height:42px;border-radius:12px;background:var(--primary-darkest);display:flex;align-items:center;justify-content:center}.perm-brand-symbol img{width:28px;height:28px;object-fit:contain}.perm-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.language-select{width:auto;min-width:118px}.perm-hero{padding:28px 0 16px}.perm-hero h1{color:var(--primary-darkest);font-weight:850;letter-spacing:-1px}.perm-hero p{max-width:760px;color:var(--text-secondary)}.perm-card{background:#fff;border:1px solid var(--border);border-radius:18px;padding:18px;box-shadow:0 8px 24px rgba(31,45,61,.035)}.perm-filter{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.perm-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin:18px 0 12px}.perm-groups{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.perm-group{border:1px solid var(--border);border-radius:14px;padding:14px;background:var(--background-soft)}.perm-group h3{font-size:14px;font-weight:800;color:var(--primary-darkest);margin:0 0 10px;text-transform:capitalize}.perm-item{display:grid;grid-template-columns:22px minmax(0,1fr);gap:8px;align-items:flex-start;padding:8px 0;border-top:1px solid rgba(0,0,0,.05)}.perm-item:first-of-type{border-top:0}.perm-code{font-size:12px;font-weight:750;color:var(--text);word-break:break-word}.perm-desc{font-size:11px;color:var(--text-secondary);margin-top:2px}.perm-protected{font-size:10px;font-weight:800;color:#b45309}.status-count{font-size:12px;color:var(--text-secondary)}@media(max-width:767.98px){.perm-filter,.perm-groups{grid-template-columns:1fr}.perm-shell{padding-left:16px;padding-right:16px}.perm-topbar{align-items:flex-start}.perm-actions{justify-content:flex-end}}
</style>
</head>
<body>
<div class="perm-shell" data-csrf-token="<?= $csrf ?>">
<div class="perm-topbar">
  <div class="perm-brand"><div class="perm-brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div><div><strong>Safety Control Tower</strong><div class="small text-muted">ETAPA 3 · RBAC</div></div></div>
  <div class="perm-actions">
    <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'),ENT_QUOTES,'UTF-8') ?>"><?php foreach(idiomasDisponiblesConNombre() as $code=>$name): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===idiomaActual()?'selected':'' ?>><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select>
    <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
    <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
  </div>
</div>
<section class="perm-hero">
  <span class="section-label">SAFETY CONTROL TOWER</span>
  <h1><?= htmlspecialchars(t('permissions_title'),ENT_QUOTES,'UTF-8') ?></h1>
  <p><?= htmlspecialchars(t('permissions_intro'),ENT_QUOTES,'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p>
</section>
<div id="permissionsAlert" class="alert d-none" role="alert" aria-live="polite"></div>
<section class="perm-card">
  <div class="perm-filter">
    <div><label for="companySelect" class="form-label"><?= htmlspecialchars(t('permissions_company'),ENT_QUOTES,'UTF-8') ?></label><select id="companySelect" class="form-select"><option value=""><?= htmlspecialchars(t('permissions_select_company'),ENT_QUOTES,'UTF-8') ?></option></select></div>
    <div><label for="roleSelect" class="form-label"><?= htmlspecialchars(t('permissions_role'),ENT_QUOTES,'UTF-8') ?></label><select id="roleSelect" class="form-select" disabled><option value=""><?= htmlspecialchars(t('permissions_select_role'),ENT_QUOTES,'UTF-8') ?></option></select></div>
  </div>
  <div class="perm-toolbar">
    <div><strong id="roleLabel"><?= htmlspecialchars(t('permissions_matrix'),ENT_QUOTES,'UTF-8') ?></strong><div id="selectedCount" class="status-count"></div></div>
    <div class="d-flex gap-2 flex-wrap"><button id="selectAllBtn" type="button" class="btn btn-outline-custom btn-sm" disabled><?= htmlspecialchars(t('permissions_select_all'),ENT_QUOTES,'UTF-8') ?></button><button id="clearAllBtn" type="button" class="btn btn-outline-custom btn-sm" disabled><?= htmlspecialchars(t('permissions_clear_all'),ENT_QUOTES,'UTF-8') ?></button><button id="savePermissionsBtn" type="button" class="btn btn-primary-custom btn-sm" disabled><?= htmlspecialchars(t('permissions_save'),ENT_QUOTES,'UTF-8') ?></button></div>
  </div>
  <div id="permissionsStatus" class="alert alert-info mb-3"><?= htmlspecialchars(t('permissions_loading'),ENT_QUOTES,'UTF-8') ?></div>
  <div id="permissionsGroups" class="perm-groups d-none"></div>
</section>
</div>
<script id="permissionsI18n" type="application/json"><?= json_encode($strings,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>window.SCT_LANG_SWITCHER_I18N = <?= json_encode($langSwitcherStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
<script src="../../js/permisos-admin.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
</body>
</html>
