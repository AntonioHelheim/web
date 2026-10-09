<?php
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/repositorios/EmpresaRepository.php';
require __DIR__ . '/lib/repositorios/LogrosRepository.php';
require_once __DIR__ . '/app/Navigation/NavigationRegistry.php';
require_once __DIR__ . '/app/Evaluations/UserEvaluationRepository.php';
require_once __DIR__ . '/lib/repositorios/SaludRepository.php';
require_once __DIR__ . '/i18n.php';

requireLoginPage();
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$userEmailRaw = (string)(currentUserId() ?? '');
$perfil = currentUserProfile($pdo) ?: [];
$roles = currentUserRoles($pdo);
$rolPrincipal = primaryRoleName($roles);
if ($rolPrincipal === null) { header('Location: acceso-denegado.php'); exit; }
$rolEtiqueta = roleDisplayLabel($rolPrincipal);
$esSuperUsuario = $rolPrincipal === 'superusuario';
$esUsuarioPerfil = $rolPrincipal === 'usuario';

$nombrePila = $perfil['name'] ?? ucfirst(explode('@',$userEmailRaw !== '' ? $userEmailRaw : 'usuario')[0]);
$nombrePila = capitalizarNombre((string)$nombrePila);
$nombreCompleto = trim((string)($perfil['name'] ?? '') . ' ' . (string)($perfil['lastname'] ?? ''));
if ($nombreCompleto === '') $nombreCompleto = $nombrePila;
$nombreCompleto = capitalizarNombre($nombreCompleto);
$iniciales = '';
if (!empty($perfil['name'])) $iniciales .= sctTextSubstr((string)$perfil['name'],0,1);
if (!empty($perfil['lastname'])) $iniciales .= sctTextSubstr((string)$perfil['lastname'],0,1);
if ($iniciales === '') $iniciales = sctTextSubstr($userEmailRaw !== '' ? $userEmailRaw : 'US',0,2);
$iniciales = sctTextUpper($iniciales);
$profilePhoto = (string)($perfil['profile_photo_path'] ?? '');

$empresaNombre = null; $empresaLogoSrc = '';
if (!$esSuperUsuario && !empty($perfil['id_company'])) {
    $empresa = empresaObtenerPorId($pdo,(int)$perfil['id_company']);
    if ($empresa) {
        $empresaNombre = capitalizarNombre((string)$empresa['razon_social']);
        $logo = trim((string)($empresa['logo_path'] ?? ''));
        if ($logo !== '') $empresaLogoSrc = preg_match('~^(?:https?:)?//|^data:|^/~i',$logo) ? $logo : './'.ltrim($logo,'./');
    }
}
$hour=(int)date('G');
$greeting=$hour<12?t('welcome_greeting_morning'):($hour<19?t('welcome_greeting_afternoon'):t('welcome_greeting_evening'));
$logros = logrosPorModulo($pdo,$userEmailRaw);

$directModules = SctNavigationRegistry::direct($pdo,'./');
$userOperationalModules = [];

if ($esUsuarioPerfil) {
    $directModules = [];
    $userOperationalModules = SctNavigationRegistry::userOperationalModules($pdo,'./');
}

if (!$esUsuarioPerfil) {
    $directModules = array_values(array_filter(
        $directModules,
        static fn(array $item): bool => ($item['id'] ?? '') !== 'my_data'
    ));
}

$managementModules = SctNavigationRegistry::management($pdo,'./');

$latestEvaluation = null;
try {
    $latestEvaluation = (new SctUserEvaluationRepository($pdo))
        ->latestCompletedForUser($userEmailRaw);
} catch (Throwable $e) {
    $latestEvaluation = null;
}

$latestEvaluationTone = 'neutral';
$latestEvaluationOutcome = null;

if ($latestEvaluation) {
    $latestStatus = strtolower((string)($latestEvaluation['status'] ?? ''));
    $latestPercentage = $latestEvaluation['result_percentage'];

    if ($latestStatus === 'approved') {
        $latestEvaluationTone = 'success';
        $latestEvaluationOutcome = t('evaluations_status_approved');
    } elseif ($latestStatus === 'failed') {
        $latestEvaluationTone = 'danger';
        $latestEvaluationOutcome = t('evaluations_status_failed');
    } elseif ($latestPercentage !== null) {
        $latestEvaluationTone = (float)$latestPercentage >= 60 ? 'success' : 'danger';
        $latestEvaluationOutcome = $latestEvaluationTone === 'success'
            ? t('evaluations_status_approved')
            : t('evaluations_status_failed');
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>" class="sct-app-frontend">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('welcome_page_title'),ENT_QUOTES,'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
<script>try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark');}catch(e){}</script>
</head>
<body class="worker-home-page">
<?php $sctNavbarBasePath='./'; require __DIR__.'/partials/app-navbar.php'; ?>
<main class="worker-home">
  <section class="worker-home__greeting" aria-labelledby="welcomeTitle">
    <div class="worker-home__greeting-copy"><span class="worker-home__eyebrow"><?= htmlspecialchars($rolEtiqueta,ENT_QUOTES,'UTF-8') ?></span><h1 id="welcomeTitle"><?= htmlspecialchars($greeting,ENT_QUOTES,'UTF-8') ?>, <?= htmlspecialchars($nombrePila,ENT_QUOTES,'UTF-8') ?></h1><p><?= htmlspecialchars($empresaNombre ?: 'Safety Control Tower',ENT_QUOTES,'UTF-8') ?></p></div>
    <?php if($empresaLogoSrc!==''): ?><img class="worker-home__company-logo" src="<?= htmlspecialchars($empresaLogoSrc,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($empresaNombre ?: 'Empresa',ENT_QUOTES,'UTF-8') ?>"><?php endif; ?>
  </section>

  <section class="worker-module-section worker-user-shortcuts" aria-label="<?= htmlspecialchars(t('home_user_shortcuts'),ENT_QUOTES,'UTF-8') ?>">
    <div class="worker-module-grid worker-module-grid--user-shortcuts">

      <?php if(currentUserHasCapability($pdo,'events.view')): ?>
      <a
        class="home-module-card home-module-card--user-shortcut"
        data-brand="cyan"
        href="api/eventos/gestion-eventos.php?mine=1"
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-journal-text"></i></span>
          <span>
            <h3><?= htmlspecialchars(t('home_my_reports'),ENT_QUOTES,'UTF-8') ?></h3>
            <small><?= htmlspecialchars(t('home_my_reports_intro'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
        </span>
        <i class="bi bi-arrow-right home-module-card__arrow"></i>
      </a>
      <?php else: ?>
      <div
        class="home-module-card home-module-card--user-shortcut is-disabled"
        data-brand="cyan"
        aria-disabled="true"
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-journal-text"></i></span>
          <span>
            <h3><?= htmlspecialchars(t('home_my_reports'),ENT_QUOTES,'UTF-8') ?></h3>
            <small><?= htmlspecialchars(t('home_no_events_permission'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
        </span>
      </div>
      <?php endif; ?>

      <a
        class="home-module-card home-module-card--user-shortcut home-module-card--evaluation"
        data-brand="gold"
        href="api/evaluaciones/mis-evaluaciones.php"
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-clipboard2-check"></i></span>
          <span class="user-shortcut-evaluation">
            <h3><?= htmlspecialchars(t('home_my_evaluations'),ENT_QUOTES,'UTF-8') ?></h3>

            <?php if($latestEvaluation): ?>
              <?php
              $latestName = !empty($latestEvaluation['name_key'])
                  ? t((string)$latestEvaluation['name_key'])
                  : (string)$latestEvaluation['name'];
              $latestResult = $latestEvaluation['result_percentage'];
              ?>
              <small><?= htmlspecialchars(t('home_latest_evaluation'),ENT_QUOTES,'UTF-8') ?></small>
              <strong class="user-shortcut-evaluation__name"><?= htmlspecialchars($latestName,ENT_QUOTES,'UTF-8') ?></strong>

              <span class="user-shortcut-evaluation__result is-<?= htmlspecialchars($latestEvaluationTone,ENT_QUOTES,'UTF-8') ?>">
                <?php if($latestResult!==null): ?>
                  <strong><?= htmlspecialchars(number_format((float)$latestResult,1),ENT_QUOTES,'UTF-8') ?>%</strong>
                <?php endif; ?>
                <?php if($latestEvaluationOutcome): ?>
                  <small><?= htmlspecialchars($latestEvaluationOutcome,ENT_QUOTES,'UTF-8') ?></small>
                <?php endif; ?>
              </span>
            <?php else: ?>
              <small><?= htmlspecialchars(t('home_no_completed_evaluations'),ENT_QUOTES,'UTF-8') ?></small>
            <?php endif; ?>
          </span>
        </span>
        <i class="bi bi-arrow-right home-module-card__arrow"></i>
      </a>

      <?php if($esUsuarioPerfil): ?>
      <button
        class="home-module-card home-module-card--user-shortcut home-module-card--button"
        data-brand="navy"
        type="button"
        data-open-health-form
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-person-vcard"></i></span>
          <span>
            <h3><?= htmlspecialchars(t('home_user_data'),ENT_QUOTES,'UTF-8') ?></h3>
            <small><?= htmlspecialchars(t('home_user_data_edit_intro'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
        </span>
        <i class="bi bi-pencil-square home-module-card__arrow"></i>
      </button>
      <?php else: ?>
      <a
        class="home-module-card home-module-card--user-shortcut"
        data-brand="navy"
        href="api/usuarios/mi-perfil.php"
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-person-vcard"></i></span>
          <span>
            <h3><?= htmlspecialchars(t('home_user_data'),ENT_QUOTES,'UTF-8') ?></h3>
            <small><?= htmlspecialchars(t('home_user_data_general_intro'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
        </span>
        <i class="bi bi-pencil-square home-module-card__arrow"></i>
      </a>
      <?php endif; ?>

    </div>
  </section>

  <?php if($esUsuarioPerfil): ?>
  <section class="worker-module-section" id="modulos">
    <div class="worker-section-head">
      <div>
        <h2><?= htmlspecialchars(t('home_user_modules_title'),ENT_QUOTES,'UTF-8') ?></h2>
        <p><?= htmlspecialchars(t('home_user_modules_intro'),ENT_QUOTES,'UTF-8') ?></p>
      </div>
    </div>

    <div class="worker-module-grid worker-module-grid--operational">
      <?php foreach($userOperationalModules as $item): ?>
        <?php if(!empty($item['disabled'])): ?>
          <div class="home-module-card is-disabled" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" aria-disabled="true">
            <span class="home-module-card__badge"><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></span>
            <span class="home-module-card__main">
              <span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
              <h3><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></h3>
            </span>
          </div>
        <?php else: ?>
          <a class="home-module-card" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars($item['href'],ENT_QUOTES,'UTF-8') ?>">
            <span class="home-module-card__main">
              <span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
              <h3><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></h3>
            </span>
            <i class="bi bi-arrow-right home-module-card__arrow"></i>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if(!$esUsuarioPerfil): ?>
  <section class="worker-module-section"><div class="worker-section-head"><div><h2><?= htmlspecialchars(t('home_direct_access'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('welcome_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
    <div class="worker-module-grid">
      <?php foreach($directModules as $item): ?>
        <?php if(!empty($item['disabled'])): ?>
        <div class="home-module-card is-disabled" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" aria-disabled="true"><span class="home-module-card__badge"><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></span><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span><h3><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></h3></span></div>
        <?php else: ?>
        <a class="home-module-card" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars($item['href'],ENT_QUOTES,'UTF-8') ?>"><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span><h3><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></h3></span><i class="bi bi-arrow-right home-module-card__arrow"></i></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if(!$esUsuarioPerfil && $managementModules): ?><section class="worker-module-section"><div class="worker-section-head"><div><h2><?= htmlspecialchars(t('mgmt_title'),ENT_QUOTES,'UTF-8') ?></h2></div></div><div class="management-module-grid"><?php foreach($managementModules as $module): ?><a class="management-module-card" data-tone="<?= htmlspecialchars($module['tone'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars($module['href'],ENT_QUOTES,'UTF-8') ?>"><span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($module['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span><div><h3><?= htmlspecialchars($module['label'],ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars($module['description'] ?? '',ENT_QUOTES,'UTF-8') ?></p></div><i class="bi bi-arrow-right"></i></a><?php endforeach; ?></div></section><?php endif; ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<?php if($esUsuarioPerfil): $healthFormForced=false; $healthBasePath='./'; require __DIR__.'/partials/health-form-modal.php'; endif; ?>
</body></html>
