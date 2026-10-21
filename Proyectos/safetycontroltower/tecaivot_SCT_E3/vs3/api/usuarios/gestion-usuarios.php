<?php
require_once __DIR__ . '/common.php';

requireLoginPage('../../acceso-denegado.php');
if (!currentUserHasAnyCapability($pdo, ['users.view','users.manage'])) {
    header('Location: ../../acceso-denegado.php');
    exit;
}
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

$jsKeys = [
    'common_active','common_inactive','common_all','common_all_f','common_no_access','common_view','common_edit',
    'users_loading','users_empty','users_select_company','users_select_role','users_status_configured','users_status_pending',
    'users_status_reset','users_action_activation','users_action_reset','users_action_activate','users_action_deactivate',
    'users_confirm_activate','users_confirm_deactivate','users_confirm_access_activation','users_confirm_access_reset',
    'users_saved','users_state_updated','users_access_sent','users_error_init','users_error_load','users_error_get',
    'users_error_save','users_error_state','users_error_access','users_required','users_new_title','users_edit_title',
    'users_photo_none','users_photo_updated','users_photo_removed','users_photo_invalid','users_supervision_title','users_supervision_help','users_supervision_none',
    'users_worker_step_title','users_worker_step_intro','users_worker_search_label','users_worker_search_placeholder','users_worker_search','users_worker_searching','users_worker_none','users_worker_available','users_worker_linked','users_worker_select','users_worker_selected','users_worker_continue_without','users_worker_must_validate','users_worker_lookup_error','users_worker_lookup_min','users_worker_lookup_company','users_worker_already_user','users_worker_invalid','users_worker_email_mismatch','users_create_existing_user','users_account_step_title','users_account_step_intro','users_profile_photo_title','users_access_link_working','role_superusuario',
    'role_gerente','role_administrador_cliente','role_jefatura','role_usuario','role_paramedico','role_contratista','role_none'
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
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
    <style>
        .welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}
        .welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}
        .quick-links{margin:1.5rem 0 3rem}.users-toolbar{display:flex;justify-content:space-between;align-items:end;gap:1rem;flex-wrap:wrap}.users-toolbar-title{margin:0;color:var(--text-secondary);font-size:.95rem}.users-actions{display:flex;gap:.5rem;flex-wrap:wrap}
        .users-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem 1rem}.users-detail-grid dt{font-size:.82rem;color:var(--text-secondary);margin-bottom:.15rem}.users-detail-grid dd{margin:0;font-weight:600}
        .user-photo{width:72px;height:72px;border-radius:50%;object-fit:cover;border:1px solid var(--border);background:var(--background-soft)}.user-photo.placeholder{display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--text-secondary)}
        .photo-editor{display:flex;gap:1rem;align-items:center;flex-wrap:wrap;padding:1rem;border:1px solid var(--border);border-radius:var(--radius-md);background:var(--background-soft)}.photo-editor-controls{flex:1;min-width:220px}
        .language-select{min-width:120px}.table-user{display:flex;align-items:center;gap:.55rem}.table-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;background:var(--background-soft);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:var(--primary-dark)}
        @media(max-width:767.98px){.users-detail-grid{grid-template-columns:1fr}.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:70%}}
    </style>
<script>document.documentElement.classList.add('sct-app-frontend');try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark');}catch(e){}</script>
</head>
<body>
<?php
$sctNavbarBasePath = '../../';
require __DIR__ . '/../../partials/app-navbar.php';
?>

<div class="container" data-current-user="<?= $sessionUserIdEscaped ?>" data-csrf-token="<?= $csrfTokenEscaped ?>" data-actor-level="<?= $actorLevelEscaped ?>" data-actor-company="<?= $actorCompanyEscaped ?>" data-current-lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="welcome-topbar">
        <div class="brand-wrapper"><div class="brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div></div>
        <div class="topbar-actions">
            <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'), ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                    <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <a href="gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
            <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>

    <section class="welcome-hero text-center">
        <div class="welcome-greeting-icon"><i class="bi bi-people"></i></div>
        <span class="section-label">SAFETY CONTROL TOWER</span>
        <h1 class="section-title"><?= htmlspecialchars(t('users_title'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="section-description intro-description-centered"><?= htmlspecialchars(t('users_intro'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(t('users_session_as'), ENT_QUOTES, 'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p>
    </section>

    <section class="quick-links">
        <div class="feature-card users-management-card">
            <div class="users-toolbar mb-3">
                <p class="users-toolbar-title"><?= htmlspecialchars(t('users_toolbar'), ENT_QUOTES, 'UTF-8') ?></p>
                <button type="button" id="usersCreateBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('users_new'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-person-plus"></i></button>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-4"><label for="usersSearch" class="form-label"><?= htmlspecialchars(t('users_search_label'), ENT_QUOTES, 'UTF-8') ?></label><input type="search" id="usersSearch" class="form-control" placeholder="<?= htmlspecialchars(t('users_search_placeholder'), ENT_QUOTES, 'UTF-8') ?>" autocomplete="off"></div>
                <div class="col-12 col-md-6 col-lg-2" id="usersCompanyFilterWrap"><label for="usersCompanyFilter" class="form-label"><?= htmlspecialchars(t('users_company'), ENT_QUOTES, 'UTF-8') ?></label><select id="usersCompanyFilter" class="form-select"><option value="all"><?= htmlspecialchars(t('common_all_f'), ENT_QUOTES, 'UTF-8') ?></option></select></div>
                <div class="col-12 col-md-6 col-lg-3"><label for="usersStateFilter" class="form-label"><?= htmlspecialchars(t('common_state'), ENT_QUOTES, 'UTF-8') ?></label><select id="usersStateFilter" class="form-select"><option value="all"><?= htmlspecialchars(t('common_all'), ENT_QUOTES, 'UTF-8') ?></option><option value="1"><?= htmlspecialchars(t('common_active'), ENT_QUOTES, 'UTF-8') ?></option><option value="0"><?= htmlspecialchars(t('common_inactive'), ENT_QUOTES, 'UTF-8') ?></option></select></div>
                <div class="col-12 col-md-6 col-lg-3"><label for="usersRoleFilter" class="form-label"><?= htmlspecialchars(t('users_access_type'), ENT_QUOTES, 'UTF-8') ?></label><select id="usersRoleFilter" class="form-select"><option value="all"><?= htmlspecialchars(t('common_all'), ENT_QUOTES, 'UTF-8') ?></option></select></div>
            </div>

            <div id="usersActionAlert" class="alert d-none mt-3 mb-0" role="alert" aria-live="polite"></div>
            <div id="usersStatus" class="alert alert-info mt-4 mb-0" role="status" aria-live="polite"><?= htmlspecialchars(t('users_loading'), ENT_QUOTES, 'UTF-8') ?></div>
            <div id="usersTableWrapper" class="table-responsive mt-4 d-none">
                <table class="table table-hover align-middle mb-0"><thead><tr>
                    <th><?= htmlspecialchars(t('users_col_user'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_name'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_company'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_access'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_state'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_login'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_last_access'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('users_col_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                </tr></thead><tbody id="usersTableBody"></tbody></table>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="userViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content users-onboarding-modal">
      <div class="modal-body">
        <div class="users-modal-head">
          <div class="users-modal-head__icon" data-accent="1"><i class="bi bi-person-vcard"></i></div>
          <div>
            <span class="section-label">Safety Control Tower</span>
            <h2 class="mb-0"><?= htmlspecialchars(t('users_detail_title'), ENT_QUOTES, 'UTF-8') ?></h2>
          </div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <section class="users-onboarding-section" data-accent="1">
          <div class="users-profile-summary">
            <img id="viewUserPhoto" class="user-photo d-none" alt="">
            <div id="viewUserPhotoPlaceholder" class="user-photo placeholder">
              <i class="bi bi-person"></i>
            </div>
            <div>
              <strong id="viewUserName">-</strong>
              <span id="viewUserLastname">-</span>
              <small id="viewUserEmail">-</small>
            </div>
          </div>
        </section>

        <section class="users-onboarding-section" data-accent="2">
          <dl class="users-detail-grid">
            <div><dt><?= htmlspecialchars(t('users_rut'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserRut">-</dd></div>
            <div><dt><?= htmlspecialchars(t('users_company'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserCompany">-</dd></div>
            <div><dt><?= htmlspecialchars(t('users_roles'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserRoles">-</dd></div>
            <div><dt><?= htmlspecialchars(t('users_access_level'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserAccess">-</dd></div>
            <div><dt><?= htmlspecialchars(t('common_state'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserState">-</dd></div>
            <div><dt><?= htmlspecialchars(t('users_col_login'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserPasswordStatus">-</dd></div>
            <div><dt><?= htmlspecialchars(t('users_language'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserLanguage">-</dd></div>
            <div><dt><?= htmlspecialchars(t('users_col_last_access'), ENT_QUOTES, 'UTF-8') ?></dt><dd id="viewUserLastAccess">-</dd></div>
          </dl>
        </section>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="userFormModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content users-onboarding-modal">
      <div class="modal-body">
        <div class="users-modal-head">
          <div class="users-modal-head__icon" data-accent="3"><i class="bi bi-person-gear"></i></div>
          <div>
            <span class="section-label">Safety Control Tower</span>
            <h2 id="userFormModalLabel" class="mb-0"><?= htmlspecialchars(t('users_new_title'), ENT_QUOTES, 'UTF-8') ?></h2>
          </div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <div id="userFormAlert" class="alert d-none mb-3"></div>

        <form id="userForm" novalidate>
          <input type="hidden" id="userFormMode" value="create">
          <input type="hidden" id="userFormTarget" value="">
          <input type="hidden" id="userWorkerId" value="">

          <section class="users-onboarding-section" id="userWorkerStep" data-accent="1">
            <div class="users-onboarding-section__head">
              <span>01</span>
              <div>
                <h3><?= htmlspecialchars(t('users_worker_step_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars(t('users_worker_step_intro'), ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-12 col-md-6" id="userCompanyGroup">
                <label for="userCompany" class="form-label"><?= htmlspecialchars(t('users_company'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="userCompany" class="form-select" required></select>
              </div>

              <div class="col-12" id="userWorkerLookupGroup">
                <label for="userWorkerSearch" class="form-label"><?= htmlspecialchars(t('users_worker_search_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <div class="input-group">
                  <input
                    type="search"
                    id="userWorkerSearch"
                    class="form-control"
                    placeholder="<?= htmlspecialchars(t('users_worker_search_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                  >
                  <button type="button" id="userWorkerSearchBtn" class="btn btn-primary-custom">
                    <i class="bi bi-search"></i>
                    <?= htmlspecialchars(t('users_worker_search'), ENT_QUOTES, 'UTF-8') ?>
                  </button>
                </div>
                <div id="userWorkerLookupStatus" class="form-text mt-2"></div>
                <div id="userWorkerResults" class="users-worker-results mt-3"></div>

                <div class="users-worker-continue mt-3">
                  <button type="button" id="userWorkerContinueWithout" class="btn btn-outline-custom d-none">
                    <?= htmlspecialchars(t('users_worker_continue_without'), ENT_QUOTES, 'UTF-8') ?>
                  </button>
                </div>
              </div>
            </div>
          </section>

          <div id="userAccountStep" class="d-none">
            <section class="users-onboarding-section" data-accent="2">
              <div class="users-onboarding-section__head">
                <span>02</span>
                <div>
                  <h3><?= htmlspecialchars(t('users_account_step_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                  <p><?= htmlspecialchars(t('users_account_step_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label for="userEmail" class="form-label"><?= htmlspecialchars(t('users_email'), ENT_QUOTES, 'UTF-8') ?></label>
                  <input type="email" id="userEmail" class="form-control" maxlength="50" required>
                </div>
                <div class="col-12 col-md-6">
                  <label for="userRole" class="form-label"><?= htmlspecialchars(t('users_role'), ENT_QUOTES, 'UTF-8') ?></label>
                  <select id="userRole" class="form-select" required></select>
                </div>
                <div class="col-12 col-md-6">
                  <label for="userName" class="form-label"><?= htmlspecialchars(t('users_firstname'), ENT_QUOTES, 'UTF-8') ?></label>
                  <input type="text" id="userName" class="form-control" maxlength="50" required>
                </div>
                <div class="col-12 col-md-6">
                  <label for="userLastname" class="form-label"><?= htmlspecialchars(t('users_lastname'), ENT_QUOTES, 'UTF-8') ?></label>
                  <input type="text" id="userLastname" class="form-control" maxlength="50" required>
                </div>
                <div class="col-12 col-md-6">
                  <label for="userRut" class="form-label"><?= htmlspecialchars(t('users_rut'), ENT_QUOTES, 'UTF-8') ?></label>
                  <input type="text" id="userRut" class="form-control" maxlength="12" required>
                </div>
                <div class="col-12 col-md-6">
                  <label for="userLanguage" class="form-label"><?= htmlspecialchars(t('users_language'), ENT_QUOTES, 'UTF-8') ?></label>
                  <select id="userLanguage" class="form-select" required>
                    <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                      <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12 d-none" id="userSupervisionGroup">
                  <label for="userSupervisors" class="form-label"><?= htmlspecialchars(t('users_supervision_title'), ENT_QUOTES, 'UTF-8') ?></label>
                  <select id="userSupervisors" class="form-select users-supervision-select" multiple size="5"></select>
                  <div class="form-text"><?= htmlspecialchars(t('users_supervision_help'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
            </section>

            <section class="users-onboarding-section d-none" id="userPhotoEditor" data-accent="3">
              <div class="users-onboarding-section__head">
                <span>03</span>
                <div>
                  <h3><?= htmlspecialchars(t('users_profile_photo_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                </div>
              </div>

              <div class="photo-editor">
                <img id="editUserPhoto" class="user-photo d-none" alt="">
                <div id="editUserPhotoPlaceholder" class="user-photo placeholder">
                  <i class="bi bi-person"></i>
                </div>
                <div class="photo-editor-controls">
                  <input type="file" id="userPhotoFile" class="form-control mb-2" accept="image/jpeg,image/png,image/webp">
                  <div class="users-actions">
                    <button type="button" id="userPhotoUploadBtn" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('users_photo_upload'), ENT_QUOTES, 'UTF-8') ?></button>
                    <button type="button" id="userPhotoRemoveBtn" class="btn btn-outline-secondary btn-sm"><?= htmlspecialchars(t('users_photo_remove'), ENT_QUOTES, 'UTF-8') ?></button>
                  </div>
                </div>
              </div>
            </section>

            <div class="users-actions mt-4">
              <button type="submit" id="userFormSubmit" class="btn btn-primary-custom">
                <?= htmlspecialchars(t('common_save'), ENT_QUOTES, 'UTF-8') ?>
              </button>
              <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">
                <?= htmlspecialchars(t('common_cancel'), ENT_QUOTES, 'UTF-8') ?>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="userStateModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content login-modal"><div class="modal-body"><h2 class="mb-3"><?= htmlspecialchars(t('users_state_modal_title'), ENT_QUOTES, 'UTF-8') ?></h2><p id="userStateModalText" class="mb-4"><?= htmlspecialchars(t('users_state_modal_text'), ENT_QUOTES, 'UTF-8') ?></p><div class="users-actions"><button type="button" id="userStateConfirmBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('common_confirm'), ENT_QUOTES, 'UTF-8') ?></button><button type="button" class="btn btn-outline-custom btn-sm" data-bs-dismiss="modal"><?= htmlspecialchars(t('common_cancel'), ENT_QUOTES, 'UTF-8') ?></button></div></div></div></div></div>

<script id="usersI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/usuarios.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
