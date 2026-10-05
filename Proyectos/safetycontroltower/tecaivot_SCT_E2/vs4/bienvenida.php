<?php
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/repositorios/EmpresaRepository.php';
require __DIR__ . '/lib/repositorios/LogrosRepository.php';
require_once __DIR__ . '/i18n.php';

requireLoginPage();
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$userEmailRaw = (string)($_SESSION['user_email'] ?? '');
$perfil = currentUserProfile($pdo) ?: [];
$roles = currentUserRoles($pdo);
$rolPrincipal = primaryRoleName($roles) ?: 'trabajador';
$rolEtiqueta = roleDisplayLabel($rolPrincipal);
$actorLevel = roleLevelFromName($rolPrincipal) ?? 5;
$esTrabajador = $actorLevel === 5;
$esSuperAdmin = in_array('administrador_completo',$roles,true);

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

$empresa = null;
$empresaNombre = null;
$empresaLogoSrc = '';
if (!$esSuperAdmin && !empty($perfil['id_company'])) {
    $empresa = empresaObtenerPorId($pdo,(int)$perfil['id_company']);
    if ($empresa) {
        $empresaNombre = capitalizarNombre((string)$empresa['razon_social']);
        $empresaLogo = trim((string)($empresa['logo_path'] ?? ''));
        if ($empresaLogo !== '') {
            $empresaLogoSrc = preg_match('~^(?:https?:)?//|^data:|^/~i',$empresaLogo) ? $empresaLogo : './' . ltrim($empresaLogo,'./');
        }
    }
}
$hour=(int)date('G');
$greeting=$hour<12?t('welcome_greeting_morning'):($hour<19?t('welcome_greeting_afternoon'):t('welcome_greeting_evening'));
$logros = logrosPorModulo($pdo,$userEmailRaw);
$healthRequired = $esTrabajador && healthUserRequiresForm($pdo,$userEmailRaw);

$managementModules=[];
$addManagement=static function(&$bucket,$cap,$title,$description,$href,$icon,$tone='blue') use($pdo){
    if(!currentUserHasCapability($pdo,$cap)) return;
    $bucket[]=['title'=>$title,'description'=>$description,'href'=>$href,'icon'=>$icon,'tone'=>$tone];
};
$addManagement($managementModules,'users.manage',t('nav_users'),t('users_intro'),'./api/usuarios/gestion-usuarios.php','bi-people','blue');
$addManagement($managementModules,'workers.manage',t('nav_workers'),t('workers_intro'),'./api/trabajadores/gestion-trabajadores.php','bi-person-badge','cyan');
$addManagement($managementModules,'projects.manage',t('nav_projects'),t('projects_intro'),'./api/proyectos/gestion-proyectos.php','bi-diagram-3','violet');
$addManagement($managementModules,'centers.manage',t('nav_centers'),t('centers_intro'),'./api/centros/gestion-centros.php','bi-geo-alt','green');
$addManagement($managementModules,'induction.manage',t('mgmt_induction_title'),t('mgmt_induction_text'),'./api/induccion/gestion-induccion.php','bi-mortarboard-fill','blue');
$addManagement($managementModules,'audits.manage',t('mgmt_audits_title'),t('mgmt_audits_text'),'./api/auditorias/gestion-auditorias.php','bi-clipboard2-data','cyan');
$addManagement($managementModules,'self_assessments.manage',t('mgmt_self_title'),t('mgmt_self_text'),'./api/autoevaluaciones/gestion-autoevaluaciones.php','bi-list-check','violet');
$addManagement($managementModules,'protocols.manage',t('mgmt_protocols_title'),t('mgmt_protocols_text'),'./api/protocolos/gestion-protocolos.php','bi-clipboard2-pulse','rose');
$addManagement($managementModules,'dynamic_forms.manage',t('mgmt_forms_title'),t('mgmt_forms_text'),'./api/formularios/gestion-formularios.php','bi-card-list','green');
if($esSuperAdmin)$managementModules[]=['title'=>t('nav_companies'),'description'=>t('companies_intro'),'href'=>'./api/empresas/gestion-empresas.php','icon'=>'bi-buildings','tone'=>'amber'];
$addManagement($managementModules,'permissions.manage',t('nav_permissions'),t('permissions_unavailable_notice'),'./api/permisos/gestion-permisos.php','bi-shield-lock','violet');
$addManagement($managementModules,'change_history.view',t('nav_history'),t('history_intro'),'./api/historial/gestion-historial.php','bi-clock-history','cyan');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('welcome_page_title'),ENT_QUOTES,'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
<script>document.documentElement.classList.add('sct-vs4-frontend');try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark');}catch(e){}</script>
<link rel="stylesheet" href="css/sct-v3-frontend.css?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
</head>
<body class="worker-home-page">
<?php $sctNavbarBasePath='./'; require __DIR__.'/partials/app-navbar.php'; ?>
<main class="worker-home">
  <section class="worker-home__greeting" aria-labelledby="welcomeTitle">
    <div class="worker-home__greeting-copy"><span class="worker-home__eyebrow"><?= htmlspecialchars($rolEtiqueta,ENT_QUOTES,'UTF-8') ?></span><h1 id="welcomeTitle"><?= htmlspecialchars($greeting,ENT_QUOTES,'UTF-8') ?>, <?= htmlspecialchars($nombrePila,ENT_QUOTES,'UTF-8') ?></h1><p><?= htmlspecialchars($empresaNombre ?: 'Safety Control Tower',ENT_QUOTES,'UTF-8') ?></p></div>
    <?php if($empresaLogoSrc!==''): ?><img class="worker-home__company-logo" src="<?= htmlspecialchars($empresaLogoSrc,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($empresaNombre ?: 'Empresa',ENT_QUOTES,'UTF-8') ?>"><?php endif; ?>
  </section>

  <section class="worker-top-grid" aria-label="<?= htmlspecialchars(t('home_direct_access'),ENT_QUOTES,'UTF-8') ?>">
    <article class="worker-home-card worker-quick-card">
      <div class="worker-card-title"><span><?= htmlspecialchars(t('home_direct_access'),ENT_QUOTES,'UTF-8') ?></span></div>
      <?php $eventsAllowed=currentUserHasCapability($pdo,'events.view'); ?>
      <?php if($eventsAllowed): ?>
      <a class="worker-action worker-action--report" href="api/eventos/gestion-eventos.php?new=1"><i class="bi bi-megaphone"></i><span><?= htmlspecialchars(t('home_report'),ENT_QUOTES,'UTF-8') ?></span><small><?= htmlspecialchars(t('nav_events'),ENT_QUOTES,'UTF-8') ?></small></a>
      <a class="worker-action worker-action--mine" href="api/eventos/gestion-eventos.php?mine=1"><i class="bi bi-journal-text"></i><span><?= htmlspecialchars(t('home_my_reports'),ENT_QUOTES,'UTF-8') ?></span><small><?= htmlspecialchars(t('nav_events'),ENT_QUOTES,'UTF-8') ?></small></a>
      <?php else: ?>
      <span class="worker-action is-disabled" aria-disabled="true" title="<?= htmlspecialchars(t('home_no_events_permission'),ENT_QUOTES,'UTF-8') ?>"><i class="bi bi-megaphone"></i><span><?= htmlspecialchars(t('home_report'),ENT_QUOTES,'UTF-8') ?></span></span>
      <span class="worker-action is-disabled" aria-disabled="true" title="<?= htmlspecialchars(t('home_no_events_permission'),ENT_QUOTES,'UTF-8') ?>"><i class="bi bi-journal-text"></i><span><?= htmlspecialchars(t('home_my_reports'),ENT_QUOTES,'UTF-8') ?></span></span>
      <?php endif; ?>
    </article>

    <article class="worker-home-card worker-achievements-card">
      <div class="worker-card-title"><span><?= htmlspecialchars(t('home_achievements'),ENT_QUOTES,'UTF-8') ?></span><i class="bi bi-trophy"></i></div>
      <?php foreach([
        ['health_safety','home_health_safety'],['estandar_16','home_standard_16'],['ti','home_it'],['medio_ambiente','home_environment']
      ] as $item): $key=$item[0];$pct=(int)($logros[$key]??0);$future=$key!=='health_safety'; ?>
      <div class="achievement-row"><div><span><?= htmlspecialchars(t($item[1]),ENT_QUOTES,'UTF-8') ?></span><strong><?= $future?htmlspecialchars(t('home_achievement_soon'),ENT_QUOTES,'UTF-8'):$pct.'%' ?></strong></div><div class="progress" role="progressbar" aria-label="<?= htmlspecialchars(t($item[1]),ENT_QUOTES,'UTF-8') ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $future?0:$pct ?>"><div class="progress-bar achievement-bar achievement-bar--<?= htmlspecialchars($key,ENT_QUOTES,'UTF-8') ?>" style="width:<?= $future?0:$pct ?>%"></div></div></div>
      <?php endforeach; ?>
    </article>

    <a class="worker-home-card worker-profile-card" href="api/usuarios/mi-perfil.php" title="<?= htmlspecialchars(t('home_edit_profile'),ENT_QUOTES,'UTF-8') ?>">
      <div class="worker-profile-card__edit"><i class="bi bi-pencil"></i></div>
      <div class="worker-profile-card__avatar"><?php if($profilePhoto!==''): ?><img src="<?= htmlspecialchars($profilePhoto,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($nombreCompleto,ENT_QUOTES,'UTF-8') ?>"><?php else: ?><span><?= htmlspecialchars($iniciales,ENT_QUOTES,'UTF-8') ?></span><?php endif; ?></div>
      <strong><?= htmlspecialchars($nombreCompleto,ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('home_edit_profile'),ENT_QUOTES,'UTF-8') ?></small>
    </a>
  </section>

  <section class="worker-module-section"><div class="worker-section-head"><div><h2><?= htmlspecialchars(t('home_direct_access'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('welcome_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
    <div class="worker-module-grid">
      <a class="home-module-card" data-brand="navy" href="api/actividades/health-safety.php"><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi bi-heart-pulse"></i></span><h3><?= htmlspecialchars(t('home_health_safety'),ENT_QUOTES,'UTF-8') ?></h3></span><i class="bi bi-arrow-right home-module-card__arrow"></i></a>
      <?php $soonItems=[['home_standard_16','bi-shield-check','cyan'],['home_it','bi-pc-display','gold'],['home_environment','bi-tree','navy']]; foreach($soonItems as $soon): ?><div class="home-module-card is-disabled" data-brand="<?= htmlspecialchars($soon[2],ENT_QUOTES,'UTF-8') ?>" aria-disabled="true"><span class="home-module-card__badge"><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></span><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi <?= $soon[1] ?>"></i></span><h3><?= htmlspecialchars(t($soon[0]),ENT_QUOTES,'UTF-8') ?></h3></span></div><?php endforeach; ?>
      <a class="home-module-card" data-brand="cyan" href="api/induccion/mis-induccion.php"><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi bi-mortarboard"></i></span><h3><?= htmlspecialchars(t('home_courses'),ENT_QUOTES,'UTF-8') ?></h3></span><i class="bi bi-arrow-right home-module-card__arrow"></i></a>
      <a class="home-module-card" data-brand="gold" href="api/induccion/mis-certificados.php"><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi bi-award"></i></span><h3><?= htmlspecialchars(t('home_certificates'),ENT_QUOTES,'UTF-8') ?></h3></span><i class="bi bi-arrow-right home-module-card__arrow"></i></a>
      <a class="home-module-card" data-brand="navy" href="api/usuarios/mi-perfil.php"><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi bi-person-vcard"></i></span><h3><?= htmlspecialchars(t('home_my_data'),ENT_QUOTES,'UTF-8') ?></h3></span><i class="bi bi-arrow-right home-module-card__arrow"></i></a>
      <a class="home-module-card" data-brand="cyan" href="mailto:contacto@safetycontroltower.cl"><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi bi-headset"></i></span><h3><?= htmlspecialchars(t('home_support'),ENT_QUOTES,'UTF-8') ?></h3></span><i class="bi bi-arrow-right home-module-card__arrow"></i></a>
    </div>
  </section>

  <?php if(!$esTrabajador && $managementModules): ?><section class="worker-module-section"><div class="worker-section-head"><div><h2><?= htmlspecialchars(t('mgmt_title'),ENT_QUOTES,'UTF-8') ?></h2></div></div><div class="management-module-grid"><?php foreach($managementModules as $module): ?><a class="management-module-card" data-tone="<?= htmlspecialchars($module['tone'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars($module['href'],ENT_QUOTES,'UTF-8') ?>"><span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($module['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span><div><h3><?= htmlspecialchars($module['title'],ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars($module['description'],ENT_QUOTES,'UTF-8') ?></p></div><i class="bi bi-arrow-right"></i></a><?php endforeach; ?></div></section><?php endif; ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<?php if($esTrabajador): $healthFormForced=$healthRequired; $healthBasePath='./'; require __DIR__.'/partials/health-form-modal.php'; endif; ?>
</body></html>
