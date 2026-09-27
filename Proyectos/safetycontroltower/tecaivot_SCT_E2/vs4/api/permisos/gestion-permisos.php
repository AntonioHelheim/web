<?php
/**
 * api/permisos/gestion-permisos.php
 *
 * Módulo de permisos granulares (Etapa 3) temporalmente desactivado para
 * priorizar estabilidad. No depende de PermisoRepository.php ni de las
 * tablas permissions/role_permissions — solo requiere sesión activa.
 * Se puede reactivar más adelante sin afectar el resto del sistema.
 */
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'permissions.manage', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('permissions_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>-p51">
<link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
<style>
body{background:radial-gradient(circle at 10% 0%,rgba(0,123,197,.07),transparent 28rem),var(--background);min-height:100vh}
.perm-shell{width:100%;margin:0 auto;padding:0 24px 64px}
.perm-empty{max-width:760px;margin:24px auto 0;background:#fff;border:1px solid var(--border);border-radius:18px;padding:48px 32px;text-align:center}
.perm-empty i{font-size:2.5rem;color:var(--primary);margin-bottom:1rem}
</style>
</head>
<body class="sct-module-page sct-management-module-page">
<div class="perm-shell sct-main-shell sct-management-shell">
<?php
$sctNavbarBasePath = '../../';
$sctNavbarBackHref = '../usuarios/gestiones.php';
require __DIR__ . '/../../partials/app-navbar.php';
?>

<section class="welcome-hero sct-main-hero"><div class="sct-main-hero__copy"><span class="section-label sct-main-pill"><i class="bi bi-shield-lock" aria-hidden="true"></i>Safety Control Tower</span><h1 class="section-title sct-main-title"><?= htmlspecialchars(t('permissions_title'), ENT_QUOTES, 'UTF-8') ?></h1></div></section>
<div class="perm-empty">
  <i class="bi bi-tools"></i>
  <p class="text-muted mb-0"><?= htmlspecialchars(t('permissions_unavailable_notice'), ENT_QUOTES, 'UTF-8') ?></p>
</div>
</div>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>

    <?php require __DIR__ . '/../../partials/app-footer.php'; ?>

<script src="../../js/sct-module-ui.js?v=20260920-p43"></script>
</body>
</html>
