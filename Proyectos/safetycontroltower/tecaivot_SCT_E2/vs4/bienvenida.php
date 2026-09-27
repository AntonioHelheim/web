<?php
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/repositorios/EmpresaRepository.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/partials/info-tip.php';
require_once __DIR__ . '/lib/welcome-live-data.php';

requireLoginPage();
aplicarCabecerasSeguridad();

$perfil = currentUserProfile($pdo) ?: [];
$nombrePila = $perfil['name'] ?? ucfirst(explode('@', $_SESSION['user_email'] ?? 'usuario')[0]);
$nombrePila = capitalizarNombre((string) $nombrePila);
$displayName = htmlspecialchars($nombrePila, ENT_QUOTES, 'UTF-8');
$currentUserId = (string) ($_SESSION['user_email'] ?? '');
$companyId = isset($perfil['id_company']) ? (int) $perfil['id_company'] : 0;

$roles = currentUserRoles($pdo);
$activityRole = 'trabajador';
foreach (['administrador_completo', 'administrador', 'jefatura', 'cliente', 'trabajador'] as $roleCandidate) {
    if (in_array($roleCandidate, $roles, true)) {
        $activityRole = $roleCandidate;
        break;
    }
}

$hour = (int) date('G');
if ($hour < 12) {
    $greeting = t('welcome_greeting_morning');
} elseif ($hour < 19) {
    $greeting = t('welcome_greeting_afternoon');
} else {
    $greeting = t('welcome_greeting_evening');
}

// Usuario Demo / Trabajador conserva el alcance funcional simplificado de P42,
// pero comparte nuevamente el mismo lenguaje visual de Inicio del resto de roles.
if ($activityRole === 'trabajador') {
    require_once __DIR__ . '/lib/personal-activities.php';
    require __DIR__ . '/partials/bienvenida-trabajador.php';
    exit;
}

if (!function_exists('sctWelcomeFormatDate')) {
    function sctWelcomeFormatDate($value)
    {
        if (!$value) return '—';
        $timestamp = strtotime((string) $value);
        return $timestamp === false ? '—' : date('d/m/Y', $timestamp);
    }
}

$isGlobalAdmin = $activityRole === 'administrador_completo'
    && currentUserHasCapability($pdo, 'companies.view_all')
    && currentUserHasCapability($pdo, 'dashboard.global');
$rolEtiqueta = roleDisplayLabel($activityRole);
$empresaNombre = '';
if (!$isGlobalAdmin && $companyId > 0) {
    $empresa = empresaObtenerPorId($pdo, $companyId);
    if ($empresa && !empty($empresa['razon_social'])) {
        $empresaNombre = capitalizarNombre((string) $empresa['razon_social']);
    }
}

$liveWelcomeData = sctBuildWelcomeLiveData($pdo, $activityRole, $perfil, $roles);
$pendingTotal = (int) ($liveWelcomeData['pending_total'] ?? 0);
$nextRelevantDate = $liveWelcomeData['next_due'] ?? null;
$scopeValue = (int) ($liveWelcomeData['scope_value'] ?? 0);

switch ($activityRole) {
    case 'administrador_completo':
        $scopeLabel = t('mgmt_metric_companies_active');
        $scopeIcon = 'bi-buildings';
        break;
    case 'administrador':
        $scopeLabel = t('mgmt_metric_users_active');
        $scopeIcon = 'bi-people';
        break;
    case 'jefatura':
        $scopeLabel = t('mgmt_metric_workers_active');
        $scopeIcon = 'bi-person-badge';
        break;
    case 'cliente':
        $scopeLabel = t('mgmt_metric_projects_active');
        $scopeIcon = 'bi-diagram-3';
        break;
    default:
        $scopeLabel = $rolEtiqueta;
        $scopeIcon = 'bi-person-workspace';
        break;
}

$activityConfig = [
    'events' => ['welcome_role_critical_events', 'welcome_role_high_critical', 'bi-shield-exclamation', 'red', './api/eventos/gestion-eventos.php'],
    'protocols' => ['welcome_role_protocols_due', 'welcome_role_next_30_days', 'bi-calendar-x', 'amber', './api/protocolos/gestion-protocolos.php'],
    'audits' => ['welcome_role_audits_open', 'welcome_role_pending_or_progress', 'bi-clipboard2-check', 'primary', './api/auditorias/gestion-auditorias.php'],
    'induction' => ['welcome_role_team_courses', 'welcome_role_without_completion', 'bi-journal-check', 'blue', './api/induccion/gestion-induccion.php'],
    'programs' => ['welcome_role_programs_active', 'welcome_role_planned_progress', 'bi-bar-chart-steps', 'blue', './api/programas/gestion-programas.php'],
];
$activityCards = [];
foreach (($liveWelcomeData['activities'] ?? []) as $liveActivity) {
    $key = (string) ($liveActivity['key'] ?? '');
    if (!isset($activityConfig[$key])) continue;
    [$labelKey, $metaKey, $icon, $tone, $href] = $activityConfig[$key];
    $activityCards[] = [
        'key' => $key,
        'value' => max(0, (int) ($liveActivity['value'] ?? 0)),
        'label' => t($labelKey),
        'meta' => t($metaKey),
        'icon' => $icon,
        'tone' => $tone,
        'href' => $href,
    ];
}

$summaryCards = [
    [
        'icon' => $scopeIcon,
        'value' => (string) $scopeValue,
        'label' => $scopeLabel,
        'meta' => $isGlobalAdmin ? t('welcome_role_scope_global') : ($empresaNombre !== '' ? $empresaNombre : $rolEtiqueta),
        'tone' => 'scope',
        'key' => 'scope',
    ],
    [
        'icon' => 'bi-list-check',
        'value' => (string) $pendingTotal,
        'label' => t('welcome_worker_pending'),
        'meta' => t('welcome_role_summary_pending_' . $activityRole),
        'tone' => 'pending',
        'key' => 'pending',
    ],
    [
        'icon' => 'bi-calendar-event',
        'value' => sctWelcomeFormatDate($nextRelevantDate),
        'label' => t('welcome_worker_next_due'),
        'meta' => t('welcome_role_summary_due_' . $activityRole),
        'tone' => 'due',
        'key' => 'next_due',
    ],
];

$puedeVerDashboard = currentUserHasCapability($pdo, 'dashboard.view');
$assetVersion = htmlspecialchars($ASSET_VERSION . '-p77', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('welcome_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="css/sct-main-sections.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="css/bienvenida.css?v=<?= $assetVersion ?>">
</head>
<body class="welcome-page welcome-role-<?= htmlspecialchars($activityRole, ENT_QUOTES, 'UTF-8') ?>">
<a class="welcome-skip-link" href="#main-content"><?= htmlspecialchars(t('welcome_skip_to_content'), ENT_QUOTES, 'UTF-8') ?></a>
<?php
$sctNavbarBasePath = './';
$sctNavbarBackHref = null;
require __DIR__ . '/partials/app-navbar.php';
?>

<main class="welcome-shell sct-main-shell" id="main-content">
    <header class="welcome-hero welcome-hero--compact sct-main-hero" aria-labelledby="welcome-title">
        <div class="welcome-hero__copy sct-main-hero__copy">
            <span class="welcome-hero__kicker sct-main-pill"><i class="bi bi-house-door-fill" aria-hidden="true"></i><span><?= htmlspecialchars(t('nav_home'), ENT_QUOTES, 'UTF-8') ?></span></span>
            <h1 id="welcome-title" class="sct-main-title"
                data-greeting-morning="<?= htmlspecialchars(t('welcome_greeting_morning'), ENT_QUOTES, 'UTF-8') ?>"
                data-greeting-afternoon="<?= htmlspecialchars(t('welcome_greeting_afternoon'), ENT_QUOTES, 'UTF-8') ?>"
                data-greeting-evening="<?= htmlspecialchars(t('welcome_greeting_evening'), ENT_QUOTES, 'UTF-8') ?>">
                <span data-welcome-greeting><?= htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') ?></span>, <?= $displayName ?>
            </h1>
            <p class="sct-main-intro"><?= htmlspecialchars(t('welcome_role_activity_text_' . $activityRole), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </header>

    <section class="welcome-unified-section" aria-labelledby="welcome-summary-title">
        <header class="welcome-section-head">
            <div class="welcome-section-title-block">
                <div class="sct-title-with-help welcome-section-title-with-tip">
                    <h2 id="welcome-summary-title"><?= htmlspecialchars(t('welcome_role_summary_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <?= sctInfoTip(t('welcome_role_summary_pending_' . $activityRole), t('info_more_label')) ?>
                </div>
                <p><?= htmlspecialchars(t('welcome_role_summary_pending_' . $activityRole), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </header>
        <div class="welcome-role-summary welcome-role-summary--overview welcome-role-summary--uniform">
            <?php foreach ($summaryCards as $summaryCard): ?>
                <article class="welcome-role-summary__item welcome-role-summary__item--<?= htmlspecialchars($summaryCard['tone'], ENT_QUOTES, 'UTF-8') ?>" data-welcome-live-summary="<?= htmlspecialchars($summaryCard['key'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="welcome-role-summary__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($summaryCard['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                    <span class="welcome-role-summary__copy">
                        <strong><?= htmlspecialchars($summaryCard['value'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($summaryCard['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <small><?= htmlspecialchars($summaryCard['meta'], ENT_QUOTES, 'UTF-8') ?></small>
                    </span>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="welcome-live-status" data-welcome-live-status aria-live="polite"><span class="welcome-live-status__dot" aria-hidden="true"></span><span><?= htmlspecialchars(t('dashboard_live_status_waiting'), ENT_QUOTES, 'UTF-8') ?></span></div>
    </section>

    <section class="welcome-unified-section welcome-unified-section--activities" aria-labelledby="welcome-activity-title">
        <header class="welcome-section-head">
            <div class="welcome-section-title-block">
                <div class="sct-title-with-help welcome-section-title-with-tip">
                    <h2 id="welcome-activity-title"><?= htmlspecialchars(t('welcome_role_activity_title_' . $activityRole), ENT_QUOTES, 'UTF-8') ?></h2>
                    <?= sctInfoTip(t('welcome_role_activity_text_' . $activityRole), t('info_more_label')) ?>
                </div>
                <p><?= htmlspecialchars(t('welcome_role_activity_text_' . $activityRole), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a class="welcome-activity-quick-link" href="./api/usuarios/gestiones.php"><i class="bi bi-grid-1x2" aria-hidden="true"></i><span><?= htmlspecialchars(t('welcome_open_gestiones'), ENT_QUOTES, 'UTF-8') ?></span></a>
        </header>

        <?php if (!empty($activityCards)): ?>
            <div class="welcome-role-focus welcome-role-focus--top">
                <div class="welcome-role-focus__grid welcome-role-focus__grid--uniform">
                    <?php foreach ($activityCards as $card): ?>
                        <a href="<?= htmlspecialchars($card['href'], ENT_QUOTES, 'UTF-8') ?>" class="welcome-role-card welcome-role-card--<?= htmlspecialchars($card['tone'], ENT_QUOTES, 'UTF-8') ?>" data-welcome-live-activity="<?= htmlspecialchars($card['key'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="welcome-role-card__top"><span class="welcome-role-card__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span><span class="welcome-role-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                            <strong class="welcome-role-card__value"><?= (int) $card['value'] ?></strong>
                            <span class="welcome-role-card__label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            <small><?= htmlspecialchars($card['meta'], ENT_QUOTES, 'UTF-8') ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="welcome-role-focus welcome-role-focus--empty" aria-live="polite">
                <div class="welcome-role-focus__empty"><span aria-hidden="true"><i class="bi bi-check2-circle"></i></span><div><strong><?= htmlspecialchars(t('welcome_metric_day_ok'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars(t('welcome_upcoming_empty'), ENT_QUOTES, 'UTF-8') ?></small></div></div>
            </div>
        <?php endif; ?>
    </section>

    <section class="welcome-unified-section welcome-unified-section--access" aria-labelledby="welcome-access-title">
        <header class="welcome-section-head">
            <div class="welcome-section-title-block">
                <div class="sct-title-with-help welcome-section-title-with-tip">
                    <h2 id="welcome-access-title"><?= htmlspecialchars(t('welcome_quick_access_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <?= sctInfoTip(t('welcome_quick_access_text'), t('info_more_label')) ?>
                </div>
                <p><?= htmlspecialchars(t('welcome_quick_access_text'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </header>
        <div class="welcome-access-grid welcome-access-grid--uniform<?= $puedeVerDashboard ? '' : ' welcome-access-grid--single' ?>">
            <?php if ($puedeVerDashboard): ?>
                <a href="./api/dashboard/dashboard.php" class="welcome-action-card welcome-action-card--primary">
                    <div class="welcome-action-card__top"><span class="welcome-action-card__icon" aria-hidden="true"><i class="bi bi-speedometer2"></i></span><span class="welcome-action-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                    <div class="welcome-action-card__body"><span class="welcome-action-card__eyebrow"><?= htmlspecialchars(t('welcome_card_panel_kicker'), ENT_QUOTES, 'UTF-8') ?></span><h3><?= htmlspecialchars(t('welcome_card_panel_title'), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars(t('welcome_card_panel_text'), ENT_QUOTES, 'UTF-8') ?></p></div>
                    <span class="welcome-action-card__cta"><?= htmlspecialchars(t('welcome_open_panel'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            <?php endif; ?>
            <a href="./api/usuarios/gestiones.php" class="welcome-action-card welcome-action-card--secondary">
                <div class="welcome-action-card__top"><span class="welcome-action-card__icon" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span><span class="welcome-action-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                <div class="welcome-action-card__body"><span class="welcome-action-card__eyebrow"><?= htmlspecialchars(t('welcome_card_management_kicker'), ENT_QUOTES, 'UTF-8') ?></span><h3><?= htmlspecialchars(t('welcome_card_gestiones_title'), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars(t('welcome_card_gestiones_text'), ENT_QUOTES, 'UTF-8') ?></p></div>
                <span class="welcome-action-card__cta"><?= htmlspecialchars(t('welcome_open_gestiones'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
            </a>
        </div>
    </section>
</main>

<script src="js/bienvenida-live.js?v=<?= $assetVersion ?>"></script>
<?php require __DIR__ . '/partials/app-footer.php'; ?>
</body>
</html>
