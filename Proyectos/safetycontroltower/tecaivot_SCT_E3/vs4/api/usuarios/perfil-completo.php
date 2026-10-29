<?php
require_once __DIR__ . '/admin-directorio-common.php';

requireLoginPage('../../acceso-denegado.php');
$context=sctAdminDirectoryContext($pdo);
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));

$companyId=(int)$context['company_id'];
$selfId=(string)$context['session_user_id'];
$targetId=trim((string)($_GET['id_users'] ?? $selfId));
$repo=sctAdminDirectoryRepo($pdo);
try {
    $profile=$repo->profile($targetId,$companyId);
} catch (Throwable $e) {
    error_log(
        'perfil-completo.php profile: '
        . get_class($e)
        . ' - '
        . $e->getMessage()
    );
    header('Location: ../../bienvenida.php?profile_error=1');
    exit;
}

if (!$profile) {
    header('Location: ../../acceso-denegado.php');
    exit;
}
$targetLevel=isset($profile['access_level']) ? (int)$profile['access_level'] : null;
if (!sctDirectoryTargetAllowed($pdo,$context,$profile)) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

$projects=$repo->projects($companyId);
$contractors=$repo->contractors($companyId);
$supervisionRepo=new SctAdminSupervisionRepository($pdo);
$managementRepo=new SctAdminManagementRepository($pdo);
$creationService=new SctAdminPersonCreationService(
    $pdo,
    $repo,
    $supervisionRepo,
    $managementRepo
);
$roleOptions=$creationService->availableRoles($companyId,(string)$context['directory_role']);
$positionOptions=$repo->positions($companyId);
if (
    !empty($profile['position'])
    && !in_array((string)$profile['position'],$positionOptions,true)
) {
    $positionOptions[]=(string)$profile['position'];
    natcasesort($positionOptions);
}
$jefaturaOptions=$supervisionRepo->jefaturas($companyId);
if ((string)$context['directory_role']==='jefatura') {
    $jefaturaOptions=array_values(array_filter(
        $jefaturaOptions,
        fn(array $row): bool => strcasecmp(
            (string)$row['id_users'],
            (string)$context['session_user_id']
        )===0
    ));
}
$gerenciaOptions=$managementRepo->gerencias($companyId);
$currentJefaturas=!empty($profile['id_worker'])
    ? $supervisionRepo->currentForWorker(
        $companyId,
        (int)$profile['id_worker']
    )
    : [];
$currentJefatura=(string)(
    $currentJefaturas[0]['id_supervisor_users'] ?? ''
);
$currentGerencias=!empty($profile['id_worker'])
    ? $managementRepo->currentForWorker(
        $companyId,
        (int)$profile['id_worker']
    )
    : [];
$currentGerencia=(string)(
    $currentGerencias[0]['id_manager_users'] ?? ''
);
$isSelf=$targetId===$selfId;
?>
<!doctype html>
<html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>" class="sct-app-frontend">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars(t('admin_user_profile_title'),ENT_QUOTES,'UTF-8') ?> - SCT</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
<script>try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark');}catch(e){}</script>
</head>
<body class="worker-home-page">
<?php $sctNavbarBasePath='../../'; require __DIR__.'/../../partials/app-navbar.php'; ?>
<main class="worker-home admin-full-profile">
<section class="worker-home__greeting">
  <div>
    <span class="worker-home__eyebrow"><?= htmlspecialchars($isSelf?t('admin_user_profile_self_eyebrow'):t('admin_user_profile_edit_eyebrow'),ENT_QUOTES,'UTF-8') ?></span>
    <h1><?= htmlspecialchars(t('admin_user_profile_title'),ENT_QUOTES,'UTF-8') ?></h1>
    <p><?= htmlspecialchars((string)$profile['id_users'],ENT_QUOTES,'UTF-8') ?></p>
    <span class="admin-profile-role-badge" data-role-level="<?= (int)($profile['access_level']??0) ?>">
      <?= htmlspecialchars((string)($profile['role_label']??''),ENT_QUOTES,'UTF-8') ?>
    </span>
  </div>
</section>

<section class="admin-profile-editor">
<div id="adminProfileAlert" class="alert d-none" role="alert"></div>
<form id="adminFullProfileForm" data-csrf="<?= htmlspecialchars((string)$_SESSION['csrf_token'],ENT_QUOTES,'UTF-8') ?>" data-user="<?= htmlspecialchars($targetId,ENT_QUOTES,'UTF-8') ?>">
  <div class="admin-profile-block" data-tone="cyan">
    <div class="admin-profile-block__head"><i class="bi bi-person-vcard"></i><div><h2><?= htmlspecialchars(t('admin_user_profile_identity'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('admin_user_profile_identity_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
    <div class="row g-3">
      <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('profile_name'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="name" required maxlength="50" value="<?= htmlspecialchars((string)$profile['name'],ENT_QUOTES,'UTF-8') ?>"></div>
      <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('profile_lastname'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="lastname" required maxlength="50" value="<?= htmlspecialchars((string)$profile['lastname'],ENT_QUOTES,'UTF-8') ?>"></div>
      <div class="col-12 col-md-6"><label class="form-label">RUT</label><input class="form-control" name="rut" required maxlength="10" value="<?= htmlspecialchars((string)$profile['rut'],ENT_QUOTES,'UTF-8') ?>"></div>
      <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('profile_language'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="language"><?php foreach(idiomasDisponiblesConNombre() as $code=>$label): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===(string)$profile['language']?'selected':'' ?>><?= htmlspecialchars($label,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></div>
      <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('profile_phone'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="phone" required maxlength="20" value="<?= htmlspecialchars((string)($profile['phone']??''),ENT_QUOTES,'UTF-8') ?>"></div>

    </div>
  </div>

  <div class="admin-profile-block" data-tone="violet">
    <div class="admin-profile-block__head"><i class="bi bi-diagram-3"></i><div><h2><?= htmlspecialchars(t('admin_user_org_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('admin_user_org_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label"><?= htmlspecialchars(t('admin_user_profile_project'),ENT_QUOTES,'UTF-8') ?></label>
        <select class="form-select" name="confirmed_project_id" required>
          <option value=""><?= htmlspecialchars(t('admin_user_profile_project_select'),ENT_QUOTES,'UTF-8') ?></option>
          <?php foreach($projects as $p): ?>
          <option value="<?= (int)$p['id_project'] ?>" <?= (int)($profile['confirmed_project_id']??0)===(int)$p['id_project']?'selected':'' ?>><?= htmlspecialchars(trim(($p['gbu']??'').' · '.($p['site']??'').' · '.$p['name'],' ·'),ENT_QUOTES,'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <label class="form-label"><?= htmlspecialchars(t('admin_user_profile_position'),ENT_QUOTES,'UTF-8') ?></label>
        <select class="form-select" name="position" required>
          <option value="">—</option>
          <?php foreach($positionOptions as $position): ?>
          <option value="<?= htmlspecialchars((string)$position,ENT_QUOTES,'UTF-8') ?>" <?= strcasecmp((string)($profile['position']??''),(string)$position)===0?'selected':'' ?>><?= htmlspecialchars((string)$position,ENT_QUOTES,'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <label class="form-label"><?= htmlspecialchars(t('admin_users_profile_group'),ENT_QUOTES,'UTF-8') ?></label>
        <select class="form-select" name="id_role_group" data-profile-role <?= $isSelf?'disabled':'' ?> required>
          <?php foreach($roleOptions as $role): ?>
          <option data-level="<?= (int)$role['access_level'] ?>" value="<?= (int)$role['id_role_group'] ?>" <?= (int)($profile['access_level']??0)===(int)$role['access_level']?'selected':'' ?>><?= htmlspecialchars((string)$role['label'],ENT_QUOTES,'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
        <?php if($isSelf): ?><input type="hidden" name="id_role_group" value=""><?php endif; ?>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <label class="form-label"><?= htmlspecialchars(t('admin_users_jefatura'),ENT_QUOTES,'UTF-8') ?></label>
        <select class="form-select" name="id_jefatura" data-profile-jefatura>
          <option value="">—</option>
          <?php foreach($jefaturaOptions as $j): ?>
          <option value="<?= htmlspecialchars((string)$j['id_users'],ENT_QUOTES,'UTF-8') ?>" <?= strcasecmp($currentJefatura,(string)$j['id_users'])===0?'selected':'' ?>><?= htmlspecialchars(trim((string)$j['name'].' '.(string)$j['lastname'].' · '.(string)$j['id_users']),ENT_QUOTES,'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <label class="form-label"><?= htmlspecialchars(t('admin_users_gerencia'),ENT_QUOTES,'UTF-8') ?></label>
        <select class="form-select" name="id_gerencia" data-profile-gerencia>
          <option value="">—</option>
          <?php foreach($gerenciaOptions as $g): ?>
          <option value="<?= htmlspecialchars((string)$g['id_users'],ENT_QUOTES,'UTF-8') ?>" <?= strcasecmp($currentGerencia,(string)$g['id_users'])===0?'selected':'' ?>><?= htmlspecialchars(trim((string)$g['name'].' '.(string)$g['lastname'].' · '.(string)$g['id_users']),ENT_QUOTES,'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label"><?= htmlspecialchars(t('admin_user_profile_contractor_flag'),ENT_QUOTES,'UTF-8') ?></label>
        <select class="form-select" name="hired_by_contractor" data-contractor-flag>
          <option value="no" <?= empty($profile['hired_by_contractor'])?'selected':'' ?>><?= htmlspecialchars(t('common_no'),ENT_QUOTES,'UTF-8') ?></option>
          <option value="yes" <?= !empty($profile['hired_by_contractor'])?'selected':'' ?>><?= htmlspecialchars(t('common_yes'),ENT_QUOTES,'UTF-8') ?></option>
        </select>
      </div>
      <div class="col-12 col-md-6" data-contractor-wrap>
        <label class="form-label"><?= htmlspecialchars(t('admin_user_profile_contractor'),ENT_QUOTES,'UTF-8') ?></label>
        <select class="form-select" name="id_contractor_company">
          <option value=""><?= htmlspecialchars(t('admin_user_profile_contractor_select'),ENT_QUOTES,'UTF-8') ?></option>
          <?php foreach($contractors as $c): ?>
          <option value="<?= (int)$c['id_contractor_company'] ?>" <?= (int)($profile['id_contractor_company']??0)===(int)$c['id_contractor_company']?'selected':'' ?>><?= htmlspecialchars((string)$c['business_name'],ENT_QUOTES,'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="admin-profile-block" data-tone="green">
    <div class="admin-profile-block__head"><i class="bi bi-heart-pulse"></i><div><h2><?= htmlspecialchars(t('admin_user_profile_health_affiliation'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('admin_user_profile_health_affiliation_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
    <div class="row g-3">
      <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_mutual'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="mutual_code">
        <option value="">—</option>
        <option value="achs" <?= ($profile['mutual_code']??'')==='achs'?'selected':'' ?>>ACHS — Asociación Chilena de Seguridad</option>
        <option value="isl" <?= ($profile['mutual_code']??'')==='isl'?'selected':'' ?>>ISL</option>
        <option value="ist" <?= ($profile['mutual_code']??'')==='ist'?'selected':'' ?>>IST</option>
        <option value="mutual" <?= ($profile['mutual_code']??'')==='mutual'?'selected':'' ?>>Mutual de Seguridad</option>
      </select></div>
      <div class="col-12 col-md-6">
        <label class="form-label"><?= htmlspecialchars(t('admin_user_profile_experience'),ENT_QUOTES,'UTF-8') ?></label>
        <input class="form-control" type="number" min="0" max="80" name="years_experience_current_role" value="<?= htmlspecialchars((string)($profile['years_experience_current_role']??''),ENT_QUOTES,'UTF-8') ?>">
      </div>
    </div>
  </div>

  <div class="admin-profile-block" data-tone="gold">
    <div class="admin-profile-block__head"><i class="bi bi-telephone-plus"></i><div><h2><?= htmlspecialchars(t('admin_user_profile_emergency'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('admin_user_profile_emergency_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
    <div class="row g-3">
      <div class="col-12 col-md-5"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_emergency_name'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="emergency_contact_name" maxlength="150" value="<?= htmlspecialchars((string)($profile['emergency_contact_name']??''),ENT_QUOTES,'UTF-8') ?>"></div>
      <div class="col-12 col-md-4"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_emergency_phone'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="emergency_contact_phone" maxlength="30" value="<?= htmlspecialchars((string)($profile['emergency_contact_phone']??''),ENT_QUOTES,'UTF-8') ?>"></div>
      <div class="col-12 col-md-3"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_emergency_relation'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="emergency_contact_relation"><option value="">—</option><?php foreach(['spouse','parent','child','sibling','partner','friend','other'] as $rel): ?><option value="<?= $rel ?>" <?= ($profile['emergency_contact_relation']??'')===$rel?'selected':'' ?>><?= htmlspecialchars(t('profile_relation_'.$rel),ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></div>
    </div>
  </div>

  <div class="admin-profile-actions">
    <a class="btn btn-outline-custom" href="../../bienvenida.php#admin-user-management"><?= htmlspecialchars(t('common_back'),ENT_QUOTES,'UTF-8') ?></a>
    <button class="btn btn-primary-custom" type="submit"><i class="bi bi-check2-circle"></i> <?= htmlspecialchars(t('profile_save'),ENT_QUOTES,'UTF-8') ?></button>
  </div>
</form>
</section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/admin-full-profile.js?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
</body>
</html>
