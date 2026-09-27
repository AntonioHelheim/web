<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../partials/info-tip.php';

requireLoginPage('../../acceso-denegado.php');
$accessContext = resolveCurrentUserAccessContext($pdo);
if ($accessContext === null) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

aplicarCabecerasSeguridad();

$isGlobalAdmin = !empty($accessContext['is_global_admin']);
$rolPrincipal = (string) ($accessContext['primary_role'] ?? 'trabajador');
$esSoloAutogestion = ((int) ($accessContext['actor_level'] ?? 5) === 5);

// Usuario Demo / Trabajador conserva la vista mínima definida en P42:
// Mi espacio (Mis actividades) + Mis datos. No se muestran módulos de supervisión.
if ($rolPrincipal === 'trabajador' || $esSoloAutogestion) {
    require __DIR__ . '/gestiones-trabajador.php';
    exit;
}

if (!function_exists('sctMgmtHasAnyCapability')) {
    function sctMgmtHasAnyCapability($pdo, array $codes)
    {
        foreach ($codes as $code) {
            if (currentUserHasCapability($pdo, (string) $code)) {
                return true;
            }
        }
        return false;
    }
}

$can = [
    'workers' => currentUserHasCapability($pdo, 'workers.manage'),
    'projects' => currentUserHasCapability($pdo, 'projects.manage'),
    'centers' => currentUserHasCapability($pdo, 'centers.manage'),
    'events' => sctMgmtHasAnyCapability($pdo, ['events.manage', 'events.view', 'events.create']),
    'programs' => currentUserHasCapability($pdo, 'programs.view'),
    'induction' => currentUserHasCapability($pdo, 'induction.manage'),
    'audits' => currentUserHasCapability($pdo, 'audits.manage'),
    'self' => currentUserHasCapability($pdo, 'self_assessments.manage'),
    'forms' => currentUserHasCapability($pdo, 'dynamic_forms.manage'),
    'protocols' => currentUserHasCapability($pdo, 'protocols.manage'),
    'users' => sctMgmtHasAnyCapability($pdo, ['users.manage', 'users.view']),
    'permissions' => currentUserHasCapability($pdo, 'permissions.manage'),
    'history' => currentUserHasCapability($pdo, 'change_history.view'),
    'companies' => $isGlobalAdmin && currentUserHasCapability($pdo, 'companies.manage_all'),
];

// La capability técnica no define por sí sola la navegación principal.
// Esta matriz limita Gestiones al foco funcional solicitado para cada rol.
$roleModulePolicy = [
    'jefatura' => [
        'workers', 'projects', 'centers', 'events', 'programs',
        'induction', 'audits', 'self', 'forms', 'protocols',
    ],
    'cliente' => [
        'projects', 'workers', 'centers', 'events', 'programs',
        'induction', 'audits', 'self', 'forms', 'protocols',
    ],
    'administrador' => [
        'users', 'workers', 'projects', 'centers', 'events', 'programs',
        'induction', 'audits', 'self', 'forms', 'protocols', 'history',
    ],
    'administrador_completo' => [
        'companies', 'users', 'permissions', 'history',
        'workers', 'projects', 'centers', 'events', 'programs',
        'induction', 'audits', 'self', 'forms', 'protocols',
    ],
];
$allowedForRole = $roleModulePolicy[$rolPrincipal] ?? [];

$moduleCatalog = [
    'workers' => ['category' => 'operation', 'href' => '../trabajadores/gestion-trabajadores.php', 'icon' => 'bi-person-badge', 'title' => 'mgmt_workers_title', 'text' => 'mgmt_workers_text', 'tone' => 'cyan'],
    'projects' => ['category' => 'operation', 'href' => '../proyectos/gestion-proyectos.php', 'icon' => 'bi-diagram-3', 'title' => 'mgmt_projects_title', 'text' => 'mgmt_projects_text', 'tone' => 'navy'],
    'centers' => ['category' => 'operation', 'href' => '../centros/gestion-centros.php', 'icon' => 'bi-geo-alt', 'title' => 'mgmt_centers_title', 'text' => 'mgmt_centers_text', 'tone' => 'aqua'],
    'events' => ['category' => 'operation', 'href' => '../eventos/gestion-eventos.php', 'icon' => 'bi-exclamation-triangle', 'title' => 'mgmt_events_title', 'text' => 'mgmt_events_text', 'tone' => 'rose'],
    'programs' => ['category' => 'operation', 'href' => '../programas/gestion-programas.php', 'icon' => 'bi-calendar3-range', 'title' => 'mgmt_programs_title', 'text' => 'mgmt_programs_text', 'tone' => 'aqua'],

    'induction' => ['category' => 'compliance', 'href' => '../induccion/gestion-induccion.php', 'icon' => 'bi-mortarboard', 'title' => 'mgmt_induction_title', 'text' => 'mgmt_induction_text', 'tone' => 'cyan'],
    'audits' => ['category' => 'compliance', 'href' => '../auditorias/gestion-auditorias.php', 'icon' => 'bi-clipboard2-check', 'title' => 'mgmt_audits_title', 'text' => 'mgmt_audits_text', 'tone' => 'amber'],
    'self' => ['category' => 'compliance', 'href' => '../autoevaluaciones/gestion-autoevaluaciones.php', 'icon' => 'bi-ui-checks-grid', 'title' => 'mgmt_self_title', 'text' => 'mgmt_self_text', 'tone' => 'aqua'],
    'forms' => ['category' => 'compliance', 'href' => '../formularios/gestion-formularios.php', 'icon' => 'bi-card-checklist', 'title' => 'mgmt_forms_title', 'text' => 'mgmt_forms_text', 'tone' => 'ice'],
    'protocols' => ['category' => 'compliance', 'href' => '../protocolos/gestion-protocolos.php', 'icon' => 'bi-clipboard2-pulse', 'title' => 'mgmt_protocols_title', 'text' => 'mgmt_protocols_text', 'tone' => 'navy'],

    'users' => ['category' => 'admin', 'href' => './gestion-usuarios.php', 'icon' => 'bi-people', 'title' => 'mgmt_users_title', 'text' => 'mgmt_users_text', 'tone' => 'navy'],
    'permissions' => ['category' => 'admin', 'href' => '../permisos/gestion-permisos.php', 'icon' => 'bi-shield-lock', 'title' => 'mgmt_permissions_title', 'text' => 'mgmt_permissions_text', 'tone' => 'amber'],
    'history' => ['category' => 'admin', 'href' => '../historial/gestion-historial.php', 'icon' => 'bi-clock-history', 'title' => 'mgmt_history_title', 'text' => 'mgmt_history_text', 'tone' => 'ice'],
    'companies' => ['category' => 'admin', 'href' => '../empresas/gestion-empresas.php', 'icon' => 'bi-buildings', 'title' => 'mgmt_companies_title', 'text' => 'mgmt_companies_text', 'tone' => 'cyan'],
];

$modulesByCategory = ['operation' => [], 'compliance' => [], 'admin' => []];
foreach ($allowedForRole as $moduleId) {
    if (!isset($moduleCatalog[$moduleId]) || empty($can[$moduleId])) {
        continue;
    }
    $module = $moduleCatalog[$moduleId];
    $module['id'] = $moduleId;
    $modulesByCategory[$module['category']][] = $module;
}

$sectionMeta = [
    'operation' => ['title' => t('mgmt_section_operation_title'), 'text' => t('mgmt_section_operation_text'), 'icon' => 'bi-briefcase'],
    'compliance' => ['title' => t('mgmt_section_compliance_title'), 'text' => t('mgmt_section_compliance_text'), 'icon' => 'bi-clipboard2-data'],
    'admin' => ['title' => t('mgmt_section_admin_title'), 'text' => t('mgmt_section_admin_text'), 'icon' => 'bi-sliders2'],
];

$sectionOrder = in_array($rolPrincipal, ['administrador', 'administrador_completo'], true)
    ? ['admin', 'operation', 'compliance']
    : ['operation', 'compliance'];

$roleIntroKey = 'mgmt_hub_intro_' . $rolPrincipal;
$assetVersion = htmlspecialchars($ASSET_VERSION . '-p66', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('mgmt_hub_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
    <link rel="stylesheet" href="../../css/gestiones.css?v=<?= $assetVersion ?>-p67">
</head>
<body class="management-hub-page sct-role-<?= htmlspecialchars($rolPrincipal, ENT_QUOTES, 'UTF-8') ?>">
<a class="management-skip-link" href="#management-main"><?= htmlspecialchars(t('welcome_skip_to_content'), ENT_QUOTES, 'UTF-8') ?></a>

<?php
$sctNavbarBasePath = '../../';
$sctNavbarBackHref = '../../bienvenida.php';
$sctNavbarBackLabel = t('nav_home');
$sctNavbarBackIcon = 'bi-arrow-left';
require __DIR__ . '/../../partials/app-navbar.php';
?>

<main id="management-main" class="management-shell sct-main-shell">
    <header class="management-hero sct-main-hero" aria-labelledby="management-title">
        <div class="sct-main-hero__copy">
            <span class="sct-main-pill"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i><span><?= htmlspecialchars(t('mgmt_hub_kicker'), ENT_QUOTES, 'UTF-8') ?></span></span>
            <h1 id="management-title" class="sct-main-title"><?= htmlspecialchars(t('mgmt_hub_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="sct-main-intro"><?= htmlspecialchars(t($roleIntroKey), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </header>


    <div class="management-directory" aria-label="<?= htmlspecialchars(t('mgmt_hub_title'), ENT_QUOTES, 'UTF-8') ?>">
        <?php foreach ($sectionOrder as $category): ?>
            <?php $categoryModules = $modulesByCategory[$category] ?? []; ?>
            <?php if (empty($categoryModules)) continue; ?>
            <section class="management-module-section management-module-section--<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" aria-labelledby="management-section-<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>-title">
                <header class="management-section-head">
                    <span class="management-section-head__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($sectionMeta[$category]['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                    <div class="management-section-head__copy">
                        <div class="sct-title-with-help management-heading-block">
                            <h2 id="management-section-<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>-title"><?= htmlspecialchars($sectionMeta[$category]['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <?= sctInfoTip($sectionMeta[$category]['text'], t('info_more_label')) ?>
                        </div>
                        <p><?= htmlspecialchars($sectionMeta[$category]['text'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </header>

                <div class="management-module-grid">
                    <?php foreach ($categoryModules as $module): ?>
                        <a href="<?= htmlspecialchars($module['href'], ENT_QUOTES, 'UTF-8') ?>" class="management-module-card management-module-card--<?= htmlspecialchars($module['tone'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="management-module-card__top">
                                <span class="management-module-card__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($module['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                <span class="management-module-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
                            </div>
                            <div class="management-module-card__body">
                                <h3><?= htmlspecialchars(t($module['title']), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars(t($module['text']), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <span class="management-module-card__cta"><?= htmlspecialchars(t('mgmt_open_action'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</main>

<?php require __DIR__ . '/../../partials/app-footer.php'; ?>
</body>
</html>
