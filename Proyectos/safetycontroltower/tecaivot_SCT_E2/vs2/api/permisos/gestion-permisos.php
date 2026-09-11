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

requireLoginPage('../../acceso-denegado.php');
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
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
<style>
body{background:radial-gradient(circle at 10% 0%,rgba(0,163,244,.07),transparent 28rem),var(--background);min-height:100vh}
.perm-shell{width:min(760px,100%);margin:0 auto;padding:0 24px 64px}
.perm-topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 0}
.perm-brand{display:flex;align-items:center;gap:10px}
.perm-brand-symbol{width:42px;height:42px;border-radius:12px;background:var(--primary-darkest);display:flex;align-items:center;justify-content:center}
.perm-brand-symbol img{width:28px;height:28px;object-fit:contain}
.perm-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.language-select{width:auto;min-width:118px}
.perm-empty{background:#fff;border:1px solid var(--border);border-radius:18px;padding:48px 32px;text-align:center;margin-top:40px}
.perm-empty i{font-size:2.5rem;color:var(--primary);margin-bottom:1rem}
</style>
</head>
<body>
<div class="perm-shell">
<div class="perm-topbar">
  <div class="perm-brand"><div class="perm-brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div><div><strong>Safety Control Tower</strong></div></div>
  <div class="perm-actions">
    <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'),ENT_QUOTES,'UTF-8') ?>"><?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===idiomaActual()?'selected':'' ?>><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select>
    <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
    <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
  </div>
</div>

<div class="perm-empty">
  <i class="bi bi-tools"></i>
  <h1 class="h4"><?= htmlspecialchars(t('permissions_title'), ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="text-muted mb-0"><?= htmlspecialchars(t('permissions_unavailable_notice'), ENT_QUOTES, 'UTF-8') ?></p>
</div>
</div>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
</body>
</html>
