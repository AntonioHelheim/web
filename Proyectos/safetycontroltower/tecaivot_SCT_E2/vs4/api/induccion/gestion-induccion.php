<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../partials/bulk-assignment-picker.php';
require_once __DIR__ . '/../../partials/test-builder-management.php';

requireCapabilityPage($pdo, 'induction.manage', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$isGlobalAdmin = induccionIsGlobalAdmin($pdo);
$canBank = currentUserHasCapability($pdo, 'questions.manage') || currentUserHasCapability($pdo, 'induction.questions');
$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$asset = htmlspecialchars((string) $ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('induction_title'), ENT_QUOTES, 'UTF-8') ?></title>
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
        <div class="welcome-greeting-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></div>
        <span class="section-label sct-main-pill">SAFETY CONTROL TOWER</span>
        <h1 class="section-title sct-main-title"><?= htmlspecialchars(t('induction_title'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="section-description intro-description-centered sct-main-intro"><?= htmlspecialchars(t('activity_builder_induction_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>
    <?php $sctModuleMode = 'manage'; require __DIR__ . '/../../partials/module-context.php'; ?>
    <section class="quick-links sct-builder-management-shell">
        <?php sctTestBuilderManagement([
            'module' => 'induction',
            'test_type' => 'induccion',
            'title' => t('induction_title'),
            'catalog_title' => t('activity_builder_induction_catalog'),
            'create_label' => t('activity_builder_create_course'),
            'create_icon' => '',
            'assignment_label' => t('activity_builder_users'),
            'is_global_admin' => $isGlobalAdmin,
            'can_manage' => true,
            'can_bank' => $canBank,
            'default_attempts' => 3,
            'default_approval' => 70,
            'endpoints' => [
            'companies' => './empresas-disponibles.php',
            'list' => './cursos-listar.php',
            'create' => './cursos-crear.php',
            'edit' => './cursos-editar.php',
            'state' => './cursos-cambiar-estado.php',
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
