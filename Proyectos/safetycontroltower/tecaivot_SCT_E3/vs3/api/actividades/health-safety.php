<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../i18n.php';
requireLoginPage('../../acceso-denegado.php');
if (!currentUserHasAnyCapability($pdo, ['protocols.view','dynamic_forms.view','audits.view','self_assessments.view'])) {
    header('Location: ../../acceso-denegado.php'); exit;
}
aplicarCabecerasSeguridad();
$perfil=currentUserProfile($pdo)?:[];$roles=currentUserRoles($pdo);
$items=[];
$add=static function(&$items,$cap,$title,$href,$icon) use($pdo){if(currentUserHasCapability($pdo,$cap))$items[]=['title'=>$title,'href'=>$href,'icon'=>$icon];};
$add($items,'protocols.view',t('nav_protocols'),'../protocolos/mis-protocolos.php','bi-clipboard2-heart');
$add($items,'dynamic_forms.view',t('nav_forms'),'../formularios/mis-formularios.php','bi-card-checklist');
$add($items,'audits.view',t('nav_audits'),'../auditorias/mis-auditorias.php','bi-clipboard2-check');
$add($items,'self_assessments.view',t('nav_self_assessments'),'../autoevaluaciones/mis-autoevaluaciones.php','bi-ui-checks-grid');
?>
<!doctype html><html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= htmlspecialchars(t('home_module_hub'),ENT_QUOTES,'UTF-8') ?> - SCT</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"><link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"><script>document.documentElement.classList.add('sct-app-frontend');try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark');}catch(e){}</script></head><body><?php $sctNavbarBasePath='../../';require __DIR__.'/../../partials/app-navbar.php';?><main class="worker-home"><section class="worker-home__greeting"><div><span class="worker-home__eyebrow">Safety Control Tower</span><h1><?= htmlspecialchars(t('home_module_hub'),ENT_QUOTES,'UTF-8') ?></h1><p><?= htmlspecialchars(t('home_module_hub_desc'),ENT_QUOTES,'UTF-8') ?></p></div></section><section class="worker-module-section"><div class="worker-module-grid"><?php foreach($items as $item): ?><a class="home-module-card" href="<?= htmlspecialchars($item['href'],ENT_QUOTES,'UTF-8') ?>"><span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span><h3><?= htmlspecialchars($item['title'],ENT_QUOTES,'UTF-8') ?></h3><i class="bi bi-arrow-right home-module-card__arrow"></i></a><?php endforeach; ?></div></section></main></body></html>
