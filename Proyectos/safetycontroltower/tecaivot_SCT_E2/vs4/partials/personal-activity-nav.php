<?php
/**
 * Navegación contextual entre actividades personales.
 * Variables opcionales:
 * - $sctPersonalNavActive: all|induction|audits|self|forms|protocols
 * - $sctPersonalNavBasePath: ruta desde la página actual a /api/ (por defecto ../)
 */
$sctPersonalNavActive = isset($sctPersonalNavActive) ? (string) $sctPersonalNavActive : '';
$sctPersonalNavBasePath = isset($sctPersonalNavBasePath) ? (string) $sctPersonalNavBasePath : '../';

$sctPersonalNavItems = [
    'all' => [
        'permission' => ['dashboard.view', 'induction.view', 'audits.view', 'self_assessments.view', 'dynamic_forms.view', 'protocols.view'],
        'href' => 'usuarios/mis-actividades.php',
        'icon' => 'bi-grid-1x2',
        'label' => function_exists('t') ? t('activities_title') : 'Mis actividades',
    ],
    'induction' => [
        'permission' => ['induction.view', 'induction.execute'],
        'href' => 'induccion/mis-induccion.php',
        'icon' => 'bi-journal-check',
        'label' => function_exists('t') ? t('mgmt_my_induction_title') : 'Mis inducciones',
    ],
    'audits' => [
        'permission' => ['audits.view', 'audits.execute'],
        'href' => 'auditorias/mis-auditorias.php',
        'icon' => 'bi-clipboard-check',
        'label' => function_exists('t') ? t('mgmt_my_audits_title') : 'Mis auditorías',
    ],
    'self' => [
        'permission' => ['self_assessments.view', 'self_assessments.execute'],
        'href' => 'autoevaluaciones/mis-autoevaluaciones.php',
        'icon' => 'bi-person-check',
        'label' => function_exists('t') ? t('mgmt_my_self_title') : 'Mis autoevaluaciones',
    ],
    'forms' => [
        'permission' => ['dynamic_forms.view', 'dynamic_forms.submit'],
        'href' => 'formularios/mis-formularios.php',
        'icon' => 'bi-card-text',
        'label' => function_exists('t') ? t('mgmt_my_forms_title') : 'Mis formularios',
    ],
    'protocols' => [
        'permission' => ['protocols.view', 'protocols.execute'],
        'href' => 'protocolos/mis-protocolos.php',
        'icon' => 'bi-clipboard2-pulse',
        'label' => function_exists('t') ? t('mgmt_my_protocols_title') : 'Mis protocolos',
    ],
];

$sctPersonalNavRole = 'trabajador';
if (isset($pdo) && $pdo instanceof PDO && function_exists('currentUserRoles') && function_exists('primaryRoleName')) {
    $sctPersonalNavRole = primaryRoleName(currentUserRoles($pdo)) ?: 'trabajador';
}
if ($sctPersonalNavRole === 'trabajador') {
    // Usuario Demo usa un único hub personal: "Mis actividades".
    // Cursos, auditorías, autoevaluaciones, formularios y protocolos personales
    // se abren desde Mi espacio o Bienvenida sin duplicar navegación principal.
    unset($sctPersonalNavItems['induction'], $sctPersonalNavItems['audits'], $sctPersonalNavItems['self'], $sctPersonalNavItems['forms'], $sctPersonalNavItems['protocols']);
}

$visiblePersonalNavItems = [];
foreach ($sctPersonalNavItems as $key => $item) {
    $allowed = false;
    if (isset($pdo) && $pdo instanceof PDO && function_exists('currentUserHasCapability')) {
        foreach ($item['permission'] as $permissionCode) {
            if (currentUserHasCapability($pdo, $permissionCode)) {
                $allowed = true;
                break;
            }
        }
    }
    if ($allowed) {
        $visiblePersonalNavItems[$key] = $item;
    }
}
?>
<?php if (count($visiblePersonalNavItems) > 1): ?>
<nav class="sct-personal-activity-nav" aria-label="<?= htmlspecialchars(function_exists('t') ? t('personal_activity_nav_label') : 'Mis actividades', ENT_QUOTES, 'UTF-8') ?>">
    <div class="sct-personal-activity-nav__track">
        <?php foreach ($visiblePersonalNavItems as $key => $item): ?>
            <a class="sct-personal-activity-nav__pill<?= $key === $sctPersonalNavActive ? ' is-active' : '' ?>"
               href="<?= htmlspecialchars($sctPersonalNavBasePath . $item['href'], ENT_QUOTES, 'UTF-8') ?>"
               <?= $key === $sctPersonalNavActive ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
<?php endif; ?>
