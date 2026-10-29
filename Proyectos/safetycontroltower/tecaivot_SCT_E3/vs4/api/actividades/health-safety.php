<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../app/Induction/HealthSafetyContent.php';

requireLoginPage('../../acceso-denegado.php');
if (!currentUserHasAnyCapability($pdo, ['protocols.view','dynamic_forms.view','audits.view','self_assessments.view'])) {
    header('Location: ../../acceso-denegado.php'); exit;
}
aplicarCabecerasSeguridad();
$roles = currentUserRoles($pdo);
$currentUserId = currentUserId() ?? '';
$primaryRole = primaryRoleName($roles) ?? 'usuario';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrfToken = (string)$_SESSION['csrf_token'];
$induction = SctHealthSafetyContent::manifest();

function hsModuleOnePassed(PDO $pdo, string $userId, string $role, string $inductionId): bool {
    try {
        $check = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='induction_module_evaluation_attempts'");
        if ((int)$check->fetchColumn() < 1) return false;
        $stmt=$pdo->prepare('SELECT 1 FROM induction_module_evaluation_attempts WHERE id_users=:u AND role_name=:r AND induction_id=:i AND module_id=\'module-01\' AND evaluation_id=\'module-01-evaluation-01\' AND passed=1 LIMIT 1');
        $stmt->execute(['u'=>$userId,'r'=>$role,'i'=>$inductionId]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) { error_log('health-safety module unlock: '.$e->getMessage()); return false; }
}

$moduleOnePassed = hsModuleOnePassed($pdo,$currentUserId,$primaryRole,(string)$induction['id']);
$requestedModule = max(1, min(7, (int)($_GET['module'] ?? 1)));
if ($requestedModule === 2 && !$moduleOnePassed) $requestedModule = 1;
if ($requestedModule > 2) $requestedModule = 1;
$moduleId = sprintf('module-%02d',$requestedModule);
$module = SctHealthSafetyContent::module($induction,$moduleId) ?? $induction['modules'][0];
$moduleSlideNumbers = array_values(array_map('intval',$module['slide_numbers'] ?? []));
$slides = array_values(array_filter($induction['slides'], static fn($slide)=>in_array((int)$slide['number'],$moduleSlideNumbers,true)));
$firstSlide=$slides[0]??null;
$evaluation = !empty($module['evaluation_id']) ? SctHealthSafetyContent::evaluation($induction,(string)$module['evaluation_id']) : null;
?>
<!doctype html>
<html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($induction['title'],ENT_QUOTES,'UTF-8') ?> - SCT</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
<link rel="stylesheet" href="../../css/health-safety-induction.css?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
<script>document.documentElement.classList.add('sct-app-frontend');try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark')}catch(e){}</script>
</head><body>
<?php $sctNavbarBasePath='../../'; require __DIR__.'/../../partials/app-navbar.php'; ?>
<main class="hs-induction" data-hs-induction
 data-induction-id="<?= htmlspecialchars($induction['id'],ENT_QUOTES,'UTF-8') ?>"
 data-part="<?= (int)$induction['part'] ?>" data-user-id="<?= htmlspecialchars($currentUserId,ENT_QUOTES,'UTF-8') ?>"
 data-user-role="<?= htmlspecialchars($primaryRole,ENT_QUOTES,'UTF-8') ?>" data-module-id="<?= htmlspecialchars($module['id'],ENT_QUOTES,'UTF-8') ?>"
 data-module-start="<?= $moduleSlideNumbers ? (int)$moduleSlideNumbers[0] : 0 ?>" data-module-end="<?= $moduleSlideNumbers ? (int)$moduleSlideNumbers[count($moduleSlideNumbers)-1] : 0 ?>"
 data-csrf-token="<?= htmlspecialchars($csrfToken,ENT_QUOTES,'UTF-8') ?>" data-progress-url="health-safety-progress.php"
 data-evaluation-url="health-safety-evaluation.php" data-has-evaluation="<?= $evaluation ? '1':'0' ?>">
<header class="hs-induction__header">
 <div><span class="hs-induction__eyebrow"><i class="bi bi-shield-check"></i> Health &amp; Safety · <?= htmlspecialchars($induction['company'],ENT_QUOTES,'UTF-8') ?></span>
 <h1><?= htmlspecialchars($induction['title'],ENT_QUOTES,'UTF-8') ?></h1><p class="hs-induction__subtitle">Material oficial de inducción integrado en Safety Control Tower.</p></div>
 <nav class="hs-induction__modules" aria-label="Módulos de la inducción">
 <?php foreach($induction['modules'] as $m): $n=(int)$m['number']; $hasSlides=!empty($m['slide_numbers']); $enabled=$n===1 || ($n===2 && $moduleOnePassed); $active=$n===$requestedModule; ?>
  <?php if($enabled && $hasSlides): ?><a class="hs-induction__module-tab<?= $active?' is-active':'' ?>" href="health-safety.php?module=<?= $n ?>&slide=<?= (int)$m['slide_numbers'][0] ?>"<?= $active?' aria-current="page"':'' ?>>Módulo <?= $n ?></a>
  <?php else: ?><span class="hs-induction__module-tab is-disabled" aria-disabled="true" title="<?= $n===2?'Aprueba el Módulo 1 para continuar.':'Contenido pendiente de incorporar.' ?>">Módulo <?= $n ?></span><?php endif; ?>
 <?php endforeach; ?>
 <small><?= $requestedModule===1?'11 láminas + evaluación':count($slides).' láminas incorporadas · contenido en desarrollo' ?></small>
 </nav>
</header>
<div class="hs-induction__workspace">
<section class="hs-induction__viewer" data-hs-viewer aria-label="Visor de inducción">
 <div class="hs-induction__stage" data-hs-stage>
 <?php if($firstSlide): ?><img class="hs-induction__slide" data-hs-slide src="<?= htmlspecialchars($firstSlide['asset'],ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($firstSlide['title'],ENT_QUOTES,'UTF-8') ?>" decoding="async"><?php endif; ?>
 <div class="hs-induction__animation-layer" data-hs-animation-layer aria-hidden="true"></div><div class="hs-induction__media-layer" data-hs-media-layer aria-live="polite"></div><div class="hs-induction__loading" data-hs-loading role="status">Cargando lámina…</div>
 </div>
 <?php if($evaluation): ?>
 <section class="hs-induction__evaluation" data-hs-evaluation hidden aria-labelledby="hsEvaluationTitle">
   <div class="onboarding-card__heading onboarding-card__heading--assessment"><span class="section-label">Evaluación del módulo</span><h2 id="hsEvaluationTitle"><?= htmlspecialchars($evaluation['title'],ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars($evaluation['intro'],ENT_QUOTES,'UTF-8') ?></p></div>
   <form data-hs-evaluation-form>
    <div class="onboarding-questions-grid">
    <?php foreach($evaluation['questions'] as $qi=>$question): ?><fieldset class="onboarding-question" data-accent="<?= ($qi%5)+1 ?>"><legend><span><?= $qi+1 ?></span><?= htmlspecialchars($question['text'],ENT_QUOTES,'UTF-8') ?></legend><div class="onboarding-options">
     <?php foreach($question['options'] as $option): ?><label class="onboarding-option"><input type="radio" name="<?= htmlspecialchars($question['id'],ENT_QUOTES,'UTF-8') ?>" value="<?= htmlspecialchars($option['id'],ENT_QUOTES,'UTF-8') ?>" required><span><?= htmlspecialchars($option['text'],ENT_QUOTES,'UTF-8') ?></span></label><?php endforeach; ?>
    </div></fieldset><?php endforeach; ?>
    </div>
    <div class="hs-induction__evaluation-actions"><button type="button" class="btn btn-outline-custom" data-hs-evaluation-back><i class="bi bi-arrow-left"></i> Volver a láminas</button><button type="submit" class="btn btn-primary-custom" data-hs-evaluation-submit>Enviar evaluación <i class="bi bi-check2-circle"></i></button></div>
    <div class="hs-induction__evaluation-result" data-hs-evaluation-result hidden role="status" aria-live="polite"></div>
   </form>
   <section class="hs-induction__evaluation-history" data-hs-evaluation-history aria-labelledby="hsEvaluationHistoryTitle">
    <div class="hs-induction__evaluation-history-head">
     <div><span class="section-label">Trazabilidad personal</span><h3 id="hsEvaluationHistoryTitle">Historial de intentos</h3></div>
     <span class="hs-induction__evaluation-history-count" data-hs-evaluation-history-count>0 intentos</span>
    </div>
    <div class="hs-induction__evaluation-history-body" data-hs-evaluation-history-body>
     <p class="hs-induction__evaluation-history-empty">Cargando historial…</p>
    </div>
   </section>
 </section>
 <?php endif; ?>
 <div class="hs-induction__toolbar">
  <div class="hs-induction__toolbar-group"><button class="hs-induction__button" type="button" data-hs-prev><i class="bi bi-chevron-left"></i><span>Anterior</span></button><button class="hs-induction__button" type="button" data-hs-next><span>Siguiente</span><i class="bi bi-chevron-right"></i></button><button class="hs-induction__button" type="button" data-hs-restart hidden><i class="bi bi-arrow-counterclockwise"></i><span>Reiniciar</span></button><button class="hs-induction__button hs-induction__button--media" type="button" data-hs-media-button hidden><i class="bi bi-play-circle-fill"></i><span data-hs-media-label>Reproducir multimedia</span></button></div>
  <div class="hs-induction__progress-wrap"><div class="hs-induction__progress-meta"><span data-hs-title><?= $firstSlide?htmlspecialchars($firstSlide['title'],ENT_QUOTES,'UTF-8'):'' ?></span><span class="hs-induction__build-state" data-hs-build-state hidden></span><strong data-hs-current>1 / <?= count($slides) ?></strong></div><div class="hs-induction__progress" aria-hidden="true"><div class="hs-induction__progress-bar" data-hs-progress></div></div></div>
  <div class="hs-induction__toolbar-group"><button class="hs-induction__button" type="button" data-hs-index-toggle aria-pressed="false" aria-label="Mostrar u ocultar índice"><i class="bi bi-layout-sidebar-inset-reverse"></i><span>Ocultar índice</span></button><button class="hs-induction__button hs-induction__button--icon" type="button" data-hs-fullscreen aria-label="Pantalla completa"><i class="bi bi-fullscreen"></i></button></div>
 </div>
</section>
<aside class="hs-induction__sidebar" aria-label="Índice de láminas"><div class="hs-induction__sidebar-head"><strong>Módulo <?= (int)$module['number'] ?></strong><span><?= count($slides) ?> láminas<?= $evaluation?' + evaluación':'' ?></span></div><div class="hs-induction__thumbs">
<?php foreach($slides as $slide): ?><button class="hs-induction__thumb<?= $slide['number']===$firstSlide['number']?' is-active':'' ?>" type="button" data-hs-thumb data-slide-number="<?= (int)$slide['number'] ?>" aria-current="<?= $slide['number']===$firstSlide['number']?'true':'false' ?>"><span class="hs-induction__thumb-media"><img src="<?= htmlspecialchars($slide['thumb']??$slide['asset'],ENT_QUOTES,'UTF-8') ?>" alt="" loading="lazy" decoding="async"><span class="hs-induction__thumb-number"><?= (int)$slide['number'] ?></span><?php if(!empty($slide['media'])):$mt=$slide['media']['type'];?><span class="hs-induction__thumb-media-badge"><i class="bi <?= $mt==='video'?'bi-play-fill':($mt==='audio'?'bi-volume-up-fill':'bi-arrow-repeat') ?>"></i></span><?php endif;?></span><span class="hs-induction__thumb-title"><?= htmlspecialchars($slide['title'],ENT_QUOTES,'UTF-8') ?></span></button><?php endforeach; ?>
<?php if($evaluation): ?><button class="hs-induction__thumb hs-induction__thumb--evaluation" type="button" data-hs-evaluation-open disabled><span class="hs-induction__thumb-media hs-induction__thumb-media--evaluation"><i class="bi bi-ui-checks-grid"></i></span><span class="hs-induction__thumb-title">Evaluación Módulo 1 · 4 preguntas</span></button><?php endif; ?>
</div></aside>
</div><p class="hs-induction__hint" aria-hidden="true"><i class="bi bi-keyboard"></i> Navegación rápida: <kbd>←</kbd><kbd>→</kbd>; <kbd>Home</kbd> inicio y <kbd>End</kbd> último contenido desbloqueado.</p>
<script type="application/json" data-hs-manifest><?= json_encode($slides,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
</main><script src="../../js/health-safety-induction.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script></body></html>
