<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../partials/info-tip.php';

requireLoginPage('../../acceso-denegado.php');
aplicarCabecerasSeguridad();

$accessContext = resolveCurrentUserAccessContext($pdo);
if (!$accessContext || (int) ($accessContext['actor_level'] ?? 5) >= 5) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

$types = [
    'induction' => [
        'capability' => 'induction.create',
        'href' => '../induccion/gestion-induccion.php?action=create',
        'icon' => 'bi-mortarboard',
        'title' => 'mgmt_induction_title',
        'text' => 'mgmt_induction_text',
    ],
    'audits' => [
        'capability' => 'audits.create',
        'href' => '../auditorias/gestion-auditorias.php?action=create',
        'icon' => 'bi-clipboard2-check',
        'title' => 'mgmt_audits_title',
        'text' => 'mgmt_audits_text',
    ],
    'self' => [
        'capability' => 'self_assessments.create',
        'href' => '../autoevaluaciones/gestion-autoevaluaciones.php?action=create',
        'icon' => 'bi-ui-checks-grid',
        'title' => 'mgmt_self_title',
        'text' => 'mgmt_self_text',
    ],
    'forms' => [
        'capability' => 'dynamic_forms.create',
        'href' => '../formularios/gestion-formularios.php?action=create',
        'icon' => 'bi-card-checklist',
        'title' => 'mgmt_forms_title',
        'text' => 'mgmt_forms_text',
    ],
    'protocols' => [
        'capability' => 'protocols.create',
        'href' => '../protocolos/gestion-protocolos.php?action=create',
        'icon' => 'bi-clipboard2-pulse',
        'title' => 'mgmt_protocols_title',
        'text' => 'mgmt_protocols_text',
    ],
];

$available = [];
foreach ($types as $id => $type) {
    if (currentUserHasCapability($pdo, $type['capability'])) {
        $type['id'] = $id;
        $available[] = $type;
    }
}
if (!$available) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

$role = (string) ($accessContext['primary_role'] ?? '');
$roleLabel = roleDisplayLabel($role);
$assetVersion = htmlspecialchars($ASSET_VERSION . '-p65', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('activity_builder_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
    <link rel="stylesheet" href="../../css/activity-builder.css?v=20260923-p79">
</head>
<body class="sct-module-page sct-management-module-page">
<div class="container sct-main-shell sct-management-shell">
    <?php
    $sctNavbarBasePath = '../../';
    $sctNavbarBackHref = '../usuarios/gestiones.php';
    require __DIR__ . '/../../partials/app-navbar.php';
    ?>

    <section class="welcome-hero sct-main-hero">
        <div class="sct-main-hero__copy">
            <span class="sct-main-pill"><i class="bi bi-plus-square" aria-hidden="true"></i><?= htmlspecialchars(t('activity_builder_kicker'), ENT_QUOTES, 'UTF-8') ?></span>
            <h1 class="sct-main-title"><?= htmlspecialchars(t('activity_builder_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="sct-main-intro"><?= htmlspecialchars(t('activity_builder_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </section>

    <?php $sctModuleMode = 'manage'; require __DIR__ . '/../../partials/module-context.php'; ?>

    <main class="activity-builder-hub quick-links">
        <div class="activity-builder-intro">
            <span class="activity-builder-card__icon" aria-hidden="true"><i class="bi bi-diagram-3"></i></span>
            <div>
                <strong><?= htmlspecialchars(t('activity_builder_create_title'), ENT_QUOTES, 'UTF-8') ?></strong>
                <p><?= htmlspecialchars(t('activity_builder_create_text'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <span class="badge rounded-pill text-bg-light"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <nav class="sct-builder-flow" aria-label="<?= htmlspecialchars(t('activity_builder_title'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="sct-builder-flow__step"><span class="sct-builder-flow__index">1</span><span class="sct-builder-flow__copy"><strong><?= htmlspecialchars(t('activity_builder_step_definition'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars(t('activity_builder_step_definition_text'), ENT_QUOTES, 'UTF-8') ?></small></span></div>
            <div class="sct-builder-flow__step"><span class="sct-builder-flow__index">2</span><span class="sct-builder-flow__copy"><strong><?= htmlspecialchars(t('activity_builder_step_content'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars(t('activity_builder_step_content_text'), ENT_QUOTES, 'UTF-8') ?></small></span></div>
            <div class="sct-builder-flow__step"><span class="sct-builder-flow__index">3</span><span class="sct-builder-flow__copy"><strong><?= htmlspecialchars(t('activity_builder_step_assignment'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars(t('activity_builder_step_assignment_text'), ENT_QUOTES, 'UTF-8') ?></small></span></div>
            <div class="sct-builder-flow__step"><span class="sct-builder-flow__index">4</span><span class="sct-builder-flow__copy"><strong><?= htmlspecialchars(t('activity_builder_step_tracking'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars(t('activity_builder_step_tracking_text'), ENT_QUOTES, 'UTF-8') ?></small></span></div>
        </nav>

        <section class="activity-builder-grid" aria-label="<?= htmlspecialchars(t('activity_builder_create_title'), ENT_QUOTES, 'UTF-8') ?>">
            <?php foreach ($available as $type): ?>
                <article class="activity-builder-card activity-builder-card--<?= htmlspecialchars($type['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="activity-builder-card__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($type['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                    <h2><?= htmlspecialchars(t($type['title']), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars(t($type['text']), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="activity-builder-card__steps" aria-hidden="true">
                        <span><?= htmlspecialchars(t('activity_builder_step_definition'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= htmlspecialchars(t('activity_builder_step_content'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= htmlspecialchars($type['id'] === 'forms' ? t('forms_submissions') : t('activity_builder_step_assignment'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <?php if ($type['id'] === 'forms'): ?>
                        <small class="text-muted mb-3"><?= htmlspecialchars(t('activity_builder_forms_distribution_note'), ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                    <a class="btn btn-primary-custom" href="<?= htmlspecialchars($type['href'], ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i><?= htmlspecialchars(t('activity_builder_open'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</div>
<?php require __DIR__ . '/../../partials/app-footer.php'; ?>
</body>
</html>
