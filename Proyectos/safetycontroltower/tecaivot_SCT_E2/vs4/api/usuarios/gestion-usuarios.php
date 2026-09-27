<?php
require_once __DIR__ . '/common.php';

requireLoginPage('../../acceso-denegado.php');
$accessContext = resolveCurrentUserAccessContext($pdo);
if ($accessContext === null) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');
$sessionUserIdEscaped = htmlspecialchars((string) $accessContext['session_user_id'], ENT_QUOTES, 'UTF-8');
$actorLevelEscaped = htmlspecialchars((string) $accessContext['actor_level'], ENT_QUOTES, 'UTF-8');
$actorCompanyEscaped = htmlspecialchars((string) $accessContext['company_id'], ENT_QUOTES, 'UTF-8');
$csrfTokenEscaped = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$currentPhoto = (string) ($accessContext['profile']['profile_photo_path'] ?? '');
$canCreateUsers = !empty($accessContext['can_create_user']);
$isSelfProfileRequested = isset($_GET['self']) && (string) $_GET['self'] === '1';
$isSelfServiceUser = (int) ($accessContext['actor_level'] ?? 5) === 5 || $isSelfProfileRequested;

$jsKeys = [
    'common_active','common_inactive','common_all','common_all_f','common_no_access','common_view','common_edit',
    'users_loading','users_empty','users_select_company','users_select_role','users_status_configured','users_status_pending',
    'users_status_reset','users_action_activation','users_action_reset','users_action_activate','users_action_deactivate',
    'users_confirm_activate','users_confirm_deactivate','users_confirm_access_activation','users_confirm_access_reset',
    'users_saved','users_state_updated','users_access_sent','users_error_init','users_error_load','users_error_get',
    'users_error_save','users_error_state','users_error_access','users_required','users_new_title','users_edit_title',
    'users_photo_none','users_photo_updated','users_photo_removed','users_photo_invalid','role_administrador_completo',
    'role_administrador','role_cliente','role_jefatura','role_trabajador','role_none'
];
$jsStrings = [];
foreach ($jsKeys as $key) {
    $jsStrings[$key] = t($key);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('users_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION . '-p56', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
    <style>
        .sct-users-page .quick-links { margin: .9rem 0 2.5rem !important; }
        .sct-users-page .users-toolbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
        .sct-users-page .users-toolbar-title { margin:0; color:var(--text-secondary); font-size:.86rem; line-height:1.45; }
        .sct-users-page .users-actions { display:flex; gap:.45rem; flex-wrap:wrap; }
        .sct-users-page .users-self-notice { margin:0 0 1rem; padding:.72rem .82rem; border:1px solid rgba(0,123,197,.12); border-radius:13px; background:rgba(0,123,197,.045); color:var(--text-secondary); font-size:.8rem; line-height:1.45; }
        .sct-users-page .users-detail-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem 1rem; }
        .sct-users-page .users-detail-grid dt { font-size:.82rem; color:var(--text-secondary); margin-bottom:.15rem; }
        .sct-users-page .users-detail-grid dd { margin:0; font-weight:600; overflow-wrap:anywhere; }
        .sct-users-page .user-photo { width:72px; height:72px; border-radius:50%; object-fit:cover; border:1px solid var(--border); background:var(--background-soft); }
        .sct-users-page .user-photo.placeholder { display:flex; align-items:center; justify-content:center; font-size:1.6rem; color:var(--text-secondary); }
        .sct-users-page .photo-editor { display:flex; gap:1rem; align-items:center; flex-wrap:wrap; padding:1rem; border:1px solid var(--border); border-radius:var(--radius-md); background:var(--background-soft); }
        .sct-users-page .photo-editor-controls { flex:1; min-width:220px; }
        .sct-users-page .language-select { min-width:120px; }
        .sct-users-page .table-user { display:flex; align-items:center; gap:.55rem; min-width:0; }
        .sct-users-page .table-user > span { min-width:0; overflow-wrap:anywhere; line-height:1.3; }
        .sct-users-page .table-avatar { width:32px!important; height:32px!important; min-width:32px; max-width:32px; min-height:32px; max-height:32px; aspect-ratio:1/1; border-radius:50%!important; object-fit:cover; object-position:center; background:var(--background-soft); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; color:var(--primary-dark); overflow:hidden; flex:0 0 32px; }
        .sct-users-page img.table-avatar { display:block!important; padding:0; }
        .sct-users-page #usersTableWrapper { border:1px solid rgba(24,38,99,.08); border-radius:16px; background:#fff; }
        .sct-users-page .users-table { min-width:1080px; table-layout:fixed; }
        .sct-users-page .users-table thead th { padding:.78rem .7rem; background:var(--background-soft,#f6f8f9); color:var(--primary-darkest,#182663); font-size:.69rem; font-weight:800; letter-spacing:.025em; vertical-align:middle; white-space:nowrap; }
        .sct-users-page .users-table tbody td { padding:.75rem .7rem; font-size:.78rem; vertical-align:middle; border-color:rgba(24,38,99,.07); }
        .sct-users-page .users-table th:nth-child(1), .sct-users-page .users-table td:nth-child(1) { width:22%; min-width:210px; }
        .sct-users-page .users-table th:nth-child(2), .sct-users-page .users-table td:nth-child(2) { width:13%; min-width:130px; }
        .sct-users-page .users-table th:nth-child(3), .sct-users-page .users-table td:nth-child(3) { width:14%; min-width:140px; }
        .sct-users-page .users-table th:nth-child(4), .sct-users-page .users-table td:nth-child(4) { width:11%; min-width:115px; }
        .sct-users-page .users-table th:nth-child(5), .sct-users-page .users-table td:nth-child(5) { width:9%; min-width:95px; }
        .sct-users-page .users-table th:nth-child(6), .sct-users-page .users-table td:nth-child(6) { width:13%; min-width:155px; }
        .sct-users-page .users-table th:nth-child(7), .sct-users-page .users-table td:nth-child(7) { width:11%; min-width:130px; }
        .sct-users-page .users-table th:nth-child(8), .sct-users-page .users-table td:nth-child(8) { width:7%; min-width:94px; }
        .sct-users-page .users-table .badge { white-space:normal; text-align:center; line-height:1.2; }
        .sct-users-page .users-table .users-actions { flex-wrap:nowrap; justify-content:flex-start; }
        .sct-users-page .users-table .users-actions .btn { width:36px; min-width:36px; padding:.35rem; }
        @media (min-width:1280px) {
            body.sct-users-page.sct-mode-personal .quick-links { max-width:none !important; margin-inline:0 !important; }
        }
        @media (max-width:1279.98px) {
            .sct-users-page .users-table { min-width:980px; }
        }
        @media (max-width:767.98px) {
            .sct-users-page .users-detail-grid { grid-template-columns:1fr; }
            .sct-users-page .users-toolbar { align-items:stretch; }
            .sct-users-page .users-toolbar > * { width:100%; }
            .sct-users-page #usersTableWrapper { margin-inline:-.15rem; border-radius:14px; }
            .sct-users-page .users-table { min-width:920px; }
            .sct-users-page .users-table thead th, .sct-users-page .users-table tbody td { padding:.68rem .6rem; }
        }
    </style>
</head>
<body class="sct-module-page sct-management-module-page sct-users-page">
<div class="container sct-main-shell sct-management-shell" data-current-user="<?= $sessionUserIdEscaped ?>" data-csrf-token="<?= $csrfTokenEscaped ?>" data-actor-level="<?= $actorLevelEscaped ?>" data-actor-company="<?= $actorCompanyEscaped ?>" data-current-lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>" data-self-profile="<?= $isSelfServiceUser ? '1' : '0' ?>">
    <?php
        $sctNavbarBasePath = '../../';
        $sctNavbarBackHref = 'gestiones.php';
        require __DIR__ . '/../../partials/app-navbar.php';
        ?>

        <section class="welcome-hero sct-main-hero">
            <div class="sct-main-hero__copy">
                <span class="section-label sct-main-pill"><i class="bi bi-people" aria-hidden="true"></i><?= htmlspecialchars(t('mgmt_hub_title'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 class="section-title sct-main-title"><?= htmlspecialchars(t($isSelfServiceUser ? 'worker_management_profile_title' : 'users_title'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="section-description sct-main-intro"><?= htmlspecialchars(t($isSelfServiceUser ? 'users_self_intro' : 'users_intro'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(t('users_session_as'), ENT_QUOTES, 'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p>
            </div>
        </section>
<?php $sctModuleMode = $isSelfServiceUser ? 'personal' : 'manage'; require __DIR__ . '/../../partials/module-context.php'; ?>

    <section class="quick-links">
        <div class="feature-card">
            <?php if (!$isSelfServiceUser): ?>
            <div class="users-toolbar mb-3">
                <p class="users-toolbar-title"><?= htmlspecialchars(t('users_toolbar'), ENT_QUOTES, 'UTF-8') ?></p>
                <button type="button" id="usersCreateBtn" class="btn btn-primary-custom btn-sm<?= $canCreateUsers ? '' : ' d-none' ?>"><?= htmlspecialchars(t('users_new'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-person-plus"></i></button>
            </div>
            <?php else: ?>
            <button type="button" id="usersCreateBtn" class="d-none" aria-hidden="true"></button>
            <?php endif; ?>

            <div class="row g-3 align-items-end<?= $isSelfServiceUser ? ' d-none' : '' ?>" id="usersManagementFilters">
                <div class="col-12 col-lg-4"><label for="usersSearch" class="form-label"><?= htmlspecialchars(t('users_search_label'), ENT_QUOTES, 'UTF-8') ?></label><input type="search" id="usersSearch" class="form-control" placeholder="<?= htmlspecialchars(t('users_search_placeholder'), ENT_QUOTES, 'UTF-8') ?>" autocomplete="off"></div>
                <div class="col-12 col-md-6 col-lg-2" id="usersCompanyFilterWrap"><label for="usersCompanyFilter" class="form-label"><?= htmlspecialchars(t('users_company'), ENT_QUOTES, 'UTF-8') ?></label><select id="usersCompanyFilter" class="form-select"><option value="all"><?= htmlspecialchars(t('common_all_f'), ENT_QUOTES, 'UTF-8') ?></option></select></div>
                <div class="col-12 col-md-6 col-lg-3"><label for="usersStateFilter" class="form-label"><?= htmlspecialchars(t('common_state'), ENT_QUOTES, 'UTF-8') ?></label><select id="usersStateFilter" class="form-select"><option value="all"><?= htmlspecialchars(t('common_all'), ENT_QUOTES, 'UTF-8') ?></option><option value="1"><?= htmlspecialchars(t('common_active'), ENT_QUOTES, 'UTF-8') ?></option><option value="0"><?= htmlspecialchars(t('common_inactive'), ENT_QUOTES, 'UTF-8') ?></option></select></div>
                <div class="col-12 col-md-6 col-lg-3"><label for="usersRoleFilter" class="form-label"><?= htmlspecialchars(t('users_access_type'), ENT_QUOTES, 'UTF-8') ?></label><select id="usersRoleFilter" class="form-select"><option value="all"><?= htmlspecialchars(t('common_all'), ENT_QUOTES, 'UTF-8') ?></option></select></div>
            </div>
            <?php if ($isSelfServiceUser): ?>
            <p class="users-self-notice"><?= htmlspecialchars(t('users_self_service_notice'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div id="usersActionAlert" class="alert d-none mt-3 mb-0" role="alert" aria-live="polite"></div>
            <div id="usersStatus" class="alert alert-info mt-4 mb-0" role="status" aria-live="polite"><?= htmlspecialchars(t('users_loading'), ENT_QUOTES, 'UTF-8') ?></div>
            <div id="usersTableWrapper" class="table-responsive mt-4 d-none">
                <table class="table table-hover align-middle mb-0 users-table"><thead><tr>
                    <th><?= htmlspecialchars(t('users_col_user'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_name'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_company'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_access'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_state'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_login'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_last_access'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                </tr></thead><tbody id="usersTableBody"></tbody></table>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="userViewModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content login-modal"><div class="modal-body">
    <div class="d-flex justify-content-between align-items-start mb-3"><h2 class="mb-0"><?= htmlspecialchars(t('users_detail_title'), ENT_QUOTES, 'UTF-8') ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="d-flex align-items-center gap-3 mb-4">
        <img id="viewUserPhoto" class="user-photo d-none" alt="">
        <div id="viewUserPhotoPlaceholder" class="user-photo placeholder" aria-hidden="true"><i class="bi bi-person"></i></div>
        <div class="small text-muted"><?= htmlspecialchars(t('users_photo_help'), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <dl class="users-detail-grid">
        <div><dt><?= htmlspecialchars(t('users_email'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserEmail">-</dd></div><div><dt><?= htmlspecialchars(t('users_firstname'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserName">-</dd></div><div><dt><?= htmlspecialchars(t('users_lastname'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserLastname">-</dd></div><div><dt><?= htmlspecialchars(t('users_rut'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserRut">-</dd></div><div><dt><?= htmlspecialchars(t('users_company'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserCompany">-</dd></div><div><dt><?= htmlspecialchars(t('users_roles'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserRoles">-</dd></div><div><dt><?= htmlspecialchars(t('users_access_level'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserAccess">-</dd></div><div><dt><?= htmlspecialchars(t('common_state'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserState">-</dd></div><div><dt><?= htmlspecialchars(t('users_col_login'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserPasswordStatus">-</dd></div><div><dt><?= htmlspecialchars(t('users_language'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserLanguage">-</dd></div><div><dt><?= htmlspecialchars(t('users_col_last_access'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserLastAccess">-</dd></div>
    </dl>
</div></div></div></div>

<div class="modal fade" id="userFormModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content login-modal"><div class="modal-body">
    <div class="d-flex justify-content-between align-items-start mb-3"><h2 id="userFormModalLabel" class="mb-0"><?= htmlspecialchars(t('users_new_title'), ENT_QUOTES, 'UTF-8') ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div id="userFormAlert" class="alert d-none mb-3"></div>
    <form id="userForm" novalidate><input type="hidden" id="userFormMode" value="create"><input type="hidden" id="userFormTarget" value="">
        <div class="row g-3">
            <div class="col-12 col-md-6"><label for="userEmail" class="form-label"><?= htmlspecialchars(t('users_email'), ENT_QUOTES, 'UTF-8') ?></label><input type="email" id="userEmail" class="form-control" maxlength="50" required></div>
            <div class="col-12 col-md-6"><label for="userRole" class="form-label"><?= htmlspecialchars(t('users_role'), ENT_QUOTES, 'UTF-8') ?></label><select id="userRole" class="form-select" required></select></div>
            <div class="col-12 col-md-6" id="userCompanyGroup"><label for="userCompany" class="form-label"><?= htmlspecialchars(t('users_company'), ENT_QUOTES, 'UTF-8') ?></label><select id="userCompany" class="form-select" required></select></div>
            <div class="col-12 col-md-6"><label for="userName" class="form-label"><?= htmlspecialchars(t('users_firstname'), ENT_QUOTES, 'UTF-8') ?></label><input type="text" id="userName" class="form-control" maxlength="50" required></div>
            <div class="col-12 col-md-6"><label for="userLastname" class="form-label"><?= htmlspecialchars(t('users_lastname'), ENT_QUOTES, 'UTF-8') ?></label><input type="text" id="userLastname" class="form-control" maxlength="50" required></div>
            <div class="col-12 col-md-6"><label for="userRut" class="form-label"><?= htmlspecialchars(t('users_rut'), ENT_QUOTES, 'UTF-8') ?></label><input type="text" id="userRut" class="form-control" maxlength="12" required></div>
            <div class="col-12 col-md-6"><label for="userLanguage" class="form-label"><?= htmlspecialchars(t('users_language'), ENT_QUOTES, 'UTF-8') ?></label><select id="userLanguage" class="form-select" required><?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?><option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        </div>
        <div id="userPhotoEditor" class="photo-editor d-none mt-3">
            <div>
                <img id="editUserPhoto" class="user-photo d-none" alt="">
                <div id="editUserPhotoPlaceholder" class="user-photo placeholder" aria-hidden="true"><i class="bi bi-person"></i></div>
            </div>
            <div class="photo-editor-controls">
                <label for="userPhotoFile" class="form-label mb-1"><?= htmlspecialchars(t('users_photo_upload'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="file" id="userPhotoFile" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                <div class="users-actions mt-2">
                    <button type="button" id="userPhotoUploadBtn" class="btn btn-outline-custom btn-sm"><i class="bi bi-upload"></i> <?= htmlspecialchars(t('users_photo_upload'), ENT_QUOTES, 'UTF-8') ?></button>
                    <button type="button" id="userPhotoRemoveBtn" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> <?= htmlspecialchars(t('users_photo_remove'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
                <small class="text-muted d-block mt-2"><?= htmlspecialchars(t('users_photo_help'), ENT_QUOTES, 'UTF-8') ?></small>
            </div>
        </div>
        <div class="users-actions mt-4"><button type="submit" id="userFormSubmit" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('common_save'), ENT_QUOTES, 'UTF-8') ?></button><button type="button" class="btn btn-outline-custom btn-sm" data-bs-dismiss="modal"><?= htmlspecialchars(t('common_cancel'), ENT_QUOTES, 'UTF-8') ?></button></div>
    </form>
</div></div></div></div>

<div class="modal fade" id="userStateModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content login-modal"><div class="modal-body"><h2 class="mb-3"><?= htmlspecialchars(t('users_state_modal_title'), ENT_QUOTES, 'UTF-8') ?></h2><p id="userStateModalText" class="mb-4"><?= htmlspecialchars(t('users_state_modal_text'), ENT_QUOTES, 'UTF-8') ?></p><div class="users-actions"><button type="button" id="userStateConfirmBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('common_confirm'), ENT_QUOTES, 'UTF-8') ?></button><button type="button" class="btn btn-outline-custom btn-sm" data-bs-dismiss="modal"><?= htmlspecialchars(t('common_cancel'), ENT_QUOTES, 'UTF-8') ?></button></div></div></div></div></div>

<script id="usersI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/usuarios.js?v=<?= htmlspecialchars($ASSET_VERSION . '-p56', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION . '-p56', ENT_QUOTES, 'UTF-8') ?>"></script>

    <?php require __DIR__ . '/../../partials/app-footer.php'; ?>

<script src="../../js/sct-module-ui.js?v=20260920-p43"></script>
</body>
</html>
