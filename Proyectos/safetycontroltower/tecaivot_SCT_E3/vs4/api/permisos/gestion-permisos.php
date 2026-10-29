<?php
/**
 * Safety Control Tower E3-VS1 — administración de matriz RBAC.
 *
 * La UI usa exactamente la misma fuente que AuthorizationService:
 * permissions + role_permissions. El alcance de empresa y jerarquía se valida
 * nuevamente en cada endpoint del módulo; ocultar opciones en frontend nunca
 * sustituye la autorización backend.
 */
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

$accessContext = permisosRequireManagePage($pdo, '../../acceso-denegado.php');
aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$jsStrings = [
    'permissions_error' => t('permissions_error'),
    'permissions_selected_count' => t('permissions_selected_count'),
    'permissions_protected' => t('permissions_protected'),
    'permissions_select_company' => t('permissions_select_company'),
    'permissions_select_role' => t('permissions_select_role'),
    'permissions_loading' => t('permissions_loading'),
    'permissions_no_roles' => t('permissions_no_roles'),
    'permissions_saved' => t('permissions_saved'),
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
<script>document.documentElement.classList.add('sct-app-frontend');try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark');}catch(e){}</script>
</head>
<body>
<?php
$sctNavbarBasePath = '../../';
require __DIR__ . '/../../partials/app-navbar.php';
?>

<main class="sct-page-shell perm-shell" data-csrf-token="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <section class="worker-home__greeting">
        <div>
            <span class="worker-home__eyebrow">Safety Control Tower · E3-VS1</span>
            <h1><?= htmlspecialchars(t('permissions_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars(t('permissions_intro'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string)($accessContext['primary_role_label'] ?? roleDisplayLabel((string)($accessContext['primary_role'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </section>

    <section class="feature-card perm-controls-card">
        <div id="permissionsAlert" class="alert d-none" role="alert"></div>
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-5">
                <label class="form-label" for="companySelect"><?= htmlspecialchars(t('permissions_company'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="companySelect" class="form-select">
                    <option value=""><?= htmlspecialchars(t('permissions_select_company'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>
            <div class="col-12 col-lg-5">
                <label class="form-label" for="roleSelect"><?= htmlspecialchars(t('permissions_role'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="roleSelect" class="form-select" disabled>
                    <option value=""><?= htmlspecialchars(t('permissions_select_role'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>
            <div class="col-12 col-lg-2">
                <div class="perm-session-chip"><i class="bi bi-person-lock"></i><span><?= htmlspecialchars(roleDisplayLabel((string)($accessContext['primary_role'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
        </div>
    </section>

    <section class="feature-card mt-3">
        <div class="perm-toolbar">
            <div>
                <h2 class="h5 mb-1"><?= htmlspecialchars(t('permissions_matrix'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div id="roleLabel" class="text-muted small"></div>
            </div>
            <div class="perm-toolbar__actions">
                <span id="selectedCount" class="perm-count"><?= htmlspecialchars(str_replace('{count}', '0', t('permissions_selected_count')), ENT_QUOTES, 'UTF-8') ?></span>
                <button id="selectAllBtn" type="button" class="btn btn-outline-custom btn-sm" disabled><?= htmlspecialchars(t('permissions_select_all'), ENT_QUOTES, 'UTF-8') ?></button>
                <button id="clearAllBtn" type="button" class="btn btn-outline-custom btn-sm" disabled><?= htmlspecialchars(t('permissions_clear_all'), ENT_QUOTES, 'UTF-8') ?></button>
                <button id="savePermissionsBtn" type="button" class="btn btn-primary-custom btn-sm" disabled><i class="bi bi-shield-check"></i> <?= htmlspecialchars(t('permissions_save'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
        </div>

        <div id="permissionsStatus" class="alert alert-info mt-3 mb-0"><?= htmlspecialchars(t('permissions_select_company'), ENT_QUOTES, 'UTF-8') ?></div>
        <div id="permissionsGroups" class="perm-groups d-none mt-3"></div>
    </section>
</main>

<script id="permissionsI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/permisos-admin.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
