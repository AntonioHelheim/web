<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../partials/bulk-assignment-picker.php';
require_once __DIR__ . '/../../partials/test-builder-management.php';

requireCapabilityPage($pdo, 'self_assessments.manage', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$isGlobalAdmin = autoevaluacionIsGlobalAdmin($pdo);
$canBank = currentUserHasCapability($pdo, 'questions.manage') || currentUserHasCapability($pdo, 'self_assessments.questions');
$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$asset = htmlspecialchars((string) $ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('self_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= $asset ?>-p75">
    <link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
    <link rel="stylesheet" href="../../css/activity-builder.css?v=20260923-p79">
</head>
<body class="sct-module-page sct-management-module-page sct-builder-page">
<div class="container sct-main-shell sct-management-shell" data-csrf-token="<?= $csrf ?>">
    <?php
    $sctNavbarBasePath = '../../';
    $sctNavbarBackHref = '../usuarios/gestiones.php';
    require __DIR__ . '/../../partials/app-navbar.php';
    ?>
    <section class="welcome-hero text-center sct-main-hero sct-builder-hero">
        <div class="welcome-greeting-icon"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i></div>
        <span class="section-label sct-main-pill">SAFETY CONTROL TOWER</span>
        <h1 class="section-title sct-main-title"><?= htmlspecialchars(t('self_title'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="section-description intro-description-centered sct-main-intro"><?= htmlspecialchars(t('activity_builder_self_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>
    <?php $sctModuleMode = 'manage'; require __DIR__ . '/../../partials/module-context.php'; ?>
    <section class="quick-links sct-builder-management-shell">
        <?php sctTestBuilderManagement([
            'module' => 'self',
            'test_type' => 'autoevaluacion',
            'title' => t('self_title'),
            'catalog_title' => t('activity_builder_self_catalog'),
            'create_label' => t('activity_builder_create_self'),
            'assignment_label' => t('activity_builder_users'),
            'is_global_admin' => $isGlobalAdmin,
            'can_manage' => true,
            'can_bank' => $canBank,
            'default_attempts' => 1,
            'default_approval' => 70,
            'endpoints' => [
            'companies' => './empresas-disponibles.php',
            'list' => './autoevaluaciones-listar.php',
            'create' => './autoevaluaciones-crear.php',
            'edit' => './autoevaluaciones-editar.php',
            'state' => './autoevaluaciones-cambiar-estado.php',
            'users' => './usuarios-disponibles.php',
            'assignments_list' => './asignaciones-listar.php',
            'assignments_create' => './asignaciones-crear.php'
            ],
        ]); ?>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/sct-bulk-assignment.js?v=20260923-p75"></script>
<script src="../../js/test-builder-admin.js?v=20260923-p79"></script>
<script src="../../js/lang-switcher.js?v=<?= $asset ?>"></script>
<?php require __DIR__ . '/../../partials/app-footer.php'; ?>
<script src="../../js/sct-module-ui.js?v=20260923-p75"></script>
</body>
</html>
