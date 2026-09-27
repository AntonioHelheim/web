<?php
/** @var PDO $pdo */
$workerUserId = (string) ($_SESSION['user_email'] ?? '');
$workerCompanyId = isset($perfil['id_company']) ? (int) $perfil['id_company'] : 0;
$workerItems = sctPersonalActivityItems($pdo, $workerCompanyId, $workerUserId, 'trabajador', false);
$workerSummary = sctPersonalActivitySummary($workerItems);
$workerOpenItems = array_values(array_filter($workerItems, static function (array $item): bool {
    return empty($item['complete']);
}));

$workerEmail = (string) ($perfil['id_users'] ?? $workerUserId);
$workerCompanyName = '';
if ($workerCompanyId > 0) {
    try {
        $stmtWorkerCompany = $pdo->prepare('SELECT razon_social FROM company WHERE id_company = :id_company AND state = 1 LIMIT 1');
        $stmtWorkerCompany->execute([':id_company' => $workerCompanyId]);
        $workerCompanyName = capitalizarNombre((string) ($stmtWorkerCompany->fetchColumn() ?: ''));
    } catch (Throwable $e) {
        $workerCompanyName = '';
    }
}

$workerNextDeadline = '';
if (!empty($workerSummary['next_due'])) {
    $workerTs = strtotime((string) $workerSummary['next_due']);
    if ($workerTs !== false) $workerNextDeadline = date('d/m/Y', $workerTs);
}

$workerCategoryMeta = [
    'induction' => ['label' => t('welcome_activity_type_course'), 'icon' => 'bi-journal-check'],
    'audits' => ['label' => t('welcome_activity_type_audit'), 'icon' => 'bi-clipboard2-check'],
    'self' => ['label' => t('welcome_activity_type_self'), 'icon' => 'bi-person-check'],
    'forms' => ['label' => t('mgmt_my_forms_title'), 'icon' => 'bi-card-text'],
    'protocols' => ['label' => t('mgmt_my_protocols_title'), 'icon' => 'bi-clipboard2-pulse'],
];
$workerOpenByCategory = [];
foreach ($workerOpenItems as $workerOpenItem) {
    $workerCategory = (string) ($workerOpenItem['category'] ?? '');
    if ($workerCategory === '') continue;
    if (!isset($workerOpenByCategory[$workerCategory])) $workerOpenByCategory[$workerCategory] = 0;
    $workerOpenByCategory[$workerCategory]++;
}

$workerActivityStatusSummary = [];
$workerActivityStatusCategories = array_merge(['all'], array_keys($workerCategoryMeta));
foreach ($workerActivityStatusCategories as $workerStatusCategory) {
    $workerActivityStatusSummary[$workerStatusCategory] = [
        'overdue' => 0,
        'in_progress' => 0,
        'approved' => 0,
        'failed' => 0,
    ];
}
foreach ($workerItems as $workerStatusItem) {
    $workerStatusCategory = (string) ($workerStatusItem['category'] ?? '');
    $workerDetailStatus = sctPersonalActivityDetailStatus($workerStatusItem);
    $workerStatusSlot = $workerDetailStatus === 'complete' ? 'approved' : $workerDetailStatus;
    if (!isset($workerActivityStatusSummary['all'][$workerStatusSlot])) continue;
    $workerActivityStatusSummary['all'][$workerStatusSlot]++;
    if (isset($workerActivityStatusSummary[$workerStatusCategory][$workerStatusSlot])) {
        $workerActivityStatusSummary[$workerStatusCategory][$workerStatusSlot]++;
    }
}
$workerActivityStatusLabels = [
    'all' => [
        'overdue' => t('worker_status_overdue'),
        'in_progress' => t('worker_status_in_progress'),
        'approved' => t('activities_filter_complete'),
        'failed' => t('worker_status_failed'),
    ],
    'induction' => [
        'overdue' => t('worker_status_overdue'),
        'in_progress' => t('worker_status_in_progress'),
        'approved' => t('worker_status_approved'),
        'failed' => t('worker_status_failed'),
    ],
    'audits' => [
        'overdue' => t('worker_status_overdue'),
        'in_progress' => t('worker_status_in_progress'),
        'approved' => t('my_audits_compliant'),
        'failed' => t('my_audits_non_compliant'),
    ],
    'self' => [
        'overdue' => t('worker_status_overdue'),
        'in_progress' => t('worker_status_in_progress'),
        'approved' => t('my_self_approved'),
        'failed' => t('my_self_failed'),
    ],
    'forms' => [
        'overdue' => t('worker_status_overdue'),
        'in_progress' => t('activities_status_available'),
        'approved' => t('activities_status_complete'),
        'failed' => t('worker_status_failed'),
    ],
    'protocols' => [
        'overdue' => t('worker_status_overdue'),
        'in_progress' => t('activities_status_pending'),
        'approved' => t('activities_status_complete'),
        'failed' => t('worker_status_failed'),
    ],
];
$workerActivityStatusCards = [
    'overdue' => ['icon' => 'bi-exclamation-octagon-fill'],
    'in_progress' => ['icon' => 'bi-play-circle-fill'],
    'approved' => ['icon' => 'bi-check-circle-fill'],
    'failed' => ['icon' => 'bi-x-circle-fill'],
];

$workerAssetVersion = htmlspecialchars($ASSET_VERSION . '-p77', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('welcome_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?= $workerAssetVersion ?>">
    <link rel="stylesheet" href="css/sct-main-sections.css?v=<?= $workerAssetVersion ?>">
    <link rel="stylesheet" href="css/bienvenida.css?v=<?= $workerAssetVersion ?>">
</head>
<body class="welcome-page welcome-role-trabajador">
<a class="welcome-skip-link" href="#main-content"><?= htmlspecialchars(t('welcome_skip_to_content'), ENT_QUOTES, 'UTF-8') ?></a>
<?php
$sctNavbarBasePath = './';
$sctNavbarBackHref = null;
require __DIR__ . '/app-navbar.php';
?>

<main id="main-content" class="welcome-shell sct-main-shell">
    <header class="welcome-hero welcome-hero--compact sct-main-hero" aria-labelledby="welcome-title">
        <div class="welcome-hero__copy sct-main-hero__copy">
            <span class="welcome-hero__kicker sct-main-pill"><i class="bi bi-house-door-fill" aria-hidden="true"></i><span><?= htmlspecialchars(t('nav_home'), ENT_QUOTES, 'UTF-8') ?></span></span>
            <h1 id="welcome-title" class="sct-main-title" data-worker-greeting
                data-morning="<?= htmlspecialchars(t('welcome_greeting_morning'), ENT_QUOTES, 'UTF-8') ?>"
                data-afternoon="<?= htmlspecialchars(t('welcome_greeting_afternoon'), ENT_QUOTES, 'UTF-8') ?>"
                data-evening="<?= htmlspecialchars(t('welcome_greeting_evening'), ENT_QUOTES, 'UTF-8') ?>">
                <span data-worker-greeting-label><?= htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') ?>,</span>
                <span class="welcome-worker-name"><?= htmlspecialchars($nombrePila, ENT_QUOTES, 'UTF-8') ?></span>
            </h1>
            <p class="sct-main-intro"><?= htmlspecialchars(t('worker_welcome_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </header>

    <section class="welcome-unified-section" aria-labelledby="worker-summary-title">
        <header class="welcome-section-head">
            <div class="welcome-section-title-block">
                <div class="sct-title-with-help welcome-section-title-with-tip">
                    <h2 id="worker-summary-title"><?= htmlspecialchars(t('welcome_role_summary_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <?= sctInfoTip(t('worker_activity_summary_help'), t('info_more_label')) ?>
                </div>
                <p><?= htmlspecialchars(t('worker_activity_summary_intro'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </header>

        <div class="welcome-role-summary welcome-role-summary--overview welcome-role-summary--uniform welcome-role-summary--worker-activities">
            <a class="welcome-role-summary__item welcome-role-summary__item--scope welcome-role-summary__item--link" href="./api/usuarios/mis-actividades.php">
                <span class="welcome-role-summary__icon" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span>
                <span class="welcome-role-summary__copy welcome-role-summary__copy--total"><strong><?= (int) $workerSummary['total'] ?> <?= htmlspecialchars(t('worker_total_activities'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars(t('worker_summary_total_caption'), ENT_QUOTES, 'UTF-8') ?></small></span>
                <i class="bi bi-arrow-right welcome-role-summary__arrow" aria-hidden="true"></i>
            </a>
            <a class="welcome-role-summary__item welcome-role-summary__item--attention welcome-role-summary__item--link" href="#worker-activities">
                <span class="welcome-role-summary__icon" aria-hidden="true"><i class="bi bi-list-check"></i></span>
                <span class="welcome-role-summary__copy"><strong><?= (int) $workerSummary['pending'] ?></strong><span><?= htmlspecialchars(t('worker_pending_activities_title'), ENT_QUOTES, 'UTF-8') ?></span><small><?= (int) $workerSummary['overdue'] ?> <?= htmlspecialchars(t('activities_filter_overdue'), ENT_QUOTES, 'UTF-8') ?></small></span>
                <i class="bi bi-arrow-down welcome-role-summary__arrow" aria-hidden="true"></i>
            </a>
            <article class="welcome-role-summary__item welcome-role-summary__item--due">
                <span class="welcome-role-summary__icon" aria-hidden="true"><i class="bi bi-calendar-event"></i></span>
                <span class="welcome-role-summary__copy"><strong class="welcome-role-summary__value--date"><?= htmlspecialchars($workerNextDeadline !== '' ? $workerNextDeadline : '—', ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars(t('worker_next_deadline'), ENT_QUOTES, 'UTF-8') ?></span><small><?= htmlspecialchars($workerNextDeadline !== '' ? t('worker_summary_due_caption') : t('worker_summary_no_due_caption'), ENT_QUOTES, 'UTF-8') ?></small></span>
            </article>
            <a class="welcome-role-summary__item welcome-role-summary__item--progress welcome-role-summary__item--link" href="./api/dashboard/dashboard.php">
                <span class="welcome-role-summary__icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span>
                <span class="welcome-role-summary__copy"><strong><?= htmlspecialchars((string) $workerSummary['progress'], ENT_QUOTES, 'UTF-8') ?>%</strong><span><?= htmlspecialchars(t('worker_dashboard_progress_title'), ENT_QUOTES, 'UTF-8') ?></span><small><?= (int) $workerSummary['complete'] ?> / <?= (int) $workerSummary['total'] ?> <?= htmlspecialchars(t('activities_filter_complete'), ENT_QUOTES, 'UTF-8') ?></small></span>
                <i class="bi bi-arrow-right welcome-role-summary__arrow" aria-hidden="true"></i>
            </a>
        </div>
    </section>

    <section id="worker-activities" class="welcome-unified-section welcome-unified-section--activities" aria-labelledby="worker-activities-title">
        <header class="welcome-section-head welcome-section-head--activities">
            <div class="welcome-section-title-block">
                <div class="sct-title-with-help welcome-section-title-with-tip">
                    <h2 id="worker-activities-title"><?= htmlspecialchars(t('worker_pending_activities_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <?= sctInfoTip(t('worker_pending_activities_help'), t('info_more_label')) ?>
                </div>
                <p><?= htmlspecialchars(t('worker_pending_activities_intro'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <aside class="welcome-activities-status welcome-activities-status--collapsed" aria-label="<?= htmlspecialchars(t('activities_status_filter_title'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="welcome-status-guide welcome-status-guide--inline welcome-status-guide--activity-summary" role="list" aria-label="<?= htmlspecialchars(t('activities_status_filter_title'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($workerActivityStatusCards as $workerStatusKey => $workerStatusCard): ?>
                        <article class="welcome-status-guide__item welcome-status-guide__item--<?= htmlspecialchars($workerStatusKey, ENT_QUOTES, 'UTF-8') ?>" role="listitem" data-worker-status-summary="<?= htmlspecialchars($workerStatusKey, ENT_QUOTES, 'UTF-8') ?>">
                            <span class="welcome-status-guide__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($workerStatusCard['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                            <span class="welcome-status-guide__copy">
                                <strong data-worker-status-count><?= (int) ($workerActivityStatusSummary['all'][$workerStatusKey] ?? 0) ?></strong>
                                <span data-worker-status-label><?= htmlspecialchars($workerActivityStatusLabels['all'][$workerStatusKey], ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </aside>
            <a class="welcome-activity-quick-link" href="./api/usuarios/mis-actividades.php#activities-list">
                <i class="bi bi-grid-1x2" aria-hidden="true"></i>
                <span><?= htmlspecialchars(t('worker_open_space'), ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        </header>

        <div class="welcome-activities-desktop-layout">
            <div class="welcome-activities-main">
                <?php if ($workerOpenItems): ?>
                    <div class="welcome-activity-toolbar welcome-worker-filter-toolbar">
                        <div class="welcome-activity-filter" role="group" aria-label="<?= htmlspecialchars(t('worker_activity_filter_label'), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="button" class="welcome-activity-filter__pill is-active" data-worker-activity-filter="all" aria-pressed="true"><span><?= htmlspecialchars(t('activities_filter_all'), ENT_QUOTES, 'UTF-8') ?></span><b><?= count($workerOpenItems) ?></b></button>
                            <?php foreach ($workerCategoryMeta as $workerCategoryKey => $workerCategoryInfo): ?>
                                <?php if (empty($workerOpenByCategory[$workerCategoryKey])) continue; ?>
                                <button type="button" class="welcome-activity-filter__pill" data-worker-activity-filter="<?= htmlspecialchars($workerCategoryKey, ENT_QUOTES, 'UTF-8') ?>" aria-pressed="false"><span><?= htmlspecialchars($workerCategoryInfo['label'], ENT_QUOTES, 'UTF-8') ?></span><b><?= (int) $workerOpenByCategory[$workerCategoryKey] ?></b></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="welcome-course-grid welcome-course-grid--worker" data-worker-activity-grid data-worker-mobile-limit="3">
                        <?php foreach ($workerOpenItems as $item): ?>
                            <?php
                            $deadlineText = '';
                            if (!empty($item['deadline'])) {
                                $deadlineTs = strtotime((string) $item['deadline']);
                                if ($deadlineTs !== false) $deadlineText = date('d/m/Y', $deadlineTs);
                            }
                            $category = (string) ($item['category'] ?? '');
                            $categoryMeta = $workerCategoryMeta[$category] ?? ['label' => t('welcome_activity_type_other'), 'icon' => 'bi-list-task'];
                            $detailStatus = sctPersonalActivityDetailStatus($item);
                            $isOverdue = $detailStatus === 'overdue';
                            $stateLabel = sctPersonalActivityStatusLabel($item);
                            $stateIconMap = [
                                'overdue' => 'bi-exclamation-octagon-fill',
                                'in_progress' => 'bi-play-circle-fill',
                                'approved' => 'bi-check-circle-fill',
                                'failed' => 'bi-x-circle-fill',
                                'complete' => 'bi-check2-circle',
                            ];
                            $stateIcon = $stateIconMap[$detailStatus] ?? $categoryMeta['icon'];
                            $workerItemHref = sctPersonalActivityHubHref($item, './api/usuarios/mis-actividades.php', 'welcome');
                            ?>
                            <article class="welcome-course-card welcome-course-card--activity welcome-course-card--status-<?= htmlspecialchars($detailStatus, ENT_QUOTES, 'UTF-8') ?>" data-worker-activity-item data-category="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" data-detail-status="<?= htmlspecialchars($detailStatus, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="welcome-course-card__top">
                                    <span class="welcome-course-card__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($stateIcon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                    <span class="welcome-course-card__type"><?= htmlspecialchars($categoryMeta['label'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($stateLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="welcome-course-card__body">
                                    <h3><?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <?php if (!empty($item['description'])): ?><p class="welcome-course-card__description"><?= htmlspecialchars((string) $item['description'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                                    <?php if ($deadlineText !== ''): ?><p class="welcome-course-card__deadline"><i class="bi bi-calendar3" aria-hidden="true"></i><?= htmlspecialchars(t('worker_induction_due') . ' ' . $deadlineText, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                                </div>
                                <div class="welcome-course-card__footer">
                                    <a href="<?= htmlspecialchars($workerItemHref, ENT_QUOTES, 'UTF-8') ?>"
                                       class="welcome-course-card__action<?= $isOverdue ? ' welcome-course-card__action--overdue' : '' ?>"
                                       data-start-activity data-requires-confirmation="1"
                                       data-activity-name="<?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                       data-activity-type-label="<?= htmlspecialchars($categoryMeta['label'], ENT_QUOTES, 'UTF-8') ?>"
                                       data-activity-deadline="<?= htmlspecialchars($deadlineText, ENT_QUOTES, 'UTF-8') ?>"
                                       data-activity-overdue="<?= $isOverdue ? '1' : '0' ?>">
                                        <?= htmlspecialchars(t('activities_open_action'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="welcome-activity-empty" data-worker-activity-empty hidden><span class="welcome-empty-state__icon" aria-hidden="true"><i class="bi bi-info-circle"></i></span><div><strong><?= htmlspecialchars(t('worker_activity_filter_empty_title'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars(t('worker_activity_filter_empty_text'), ENT_QUOTES, 'UTF-8') ?></span></div></div>
                <?php else: ?>
                    <div class="welcome-activity-empty"><span class="welcome-empty-state__icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span><div><strong><?= htmlspecialchars(t('worker_activity_empty_title'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars(t('worker_activity_empty_text'), ENT_QUOTES, 'UTF-8') ?></span></div></div>
                <?php endif; ?>
                <a class="welcome-activity-footer-link" href="./api/usuarios/mis-actividades.php#activities-list">
                    <span><?= htmlspecialchars(t('worker_open_space'), ENT_QUOTES, 'UTF-8') ?></span><i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            <aside class="welcome-activities-status welcome-activities-status--expanded" aria-label="<?= htmlspecialchars(t('activities_status_filter_title'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="welcome-status-guide welcome-status-guide--inline welcome-status-guide--activity-summary" role="list" aria-label="<?= htmlspecialchars(t('activities_status_filter_title'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($workerActivityStatusCards as $workerStatusKey => $workerStatusCard): ?>
                        <article class="welcome-status-guide__item welcome-status-guide__item--<?= htmlspecialchars($workerStatusKey, ENT_QUOTES, 'UTF-8') ?>" role="listitem" data-worker-status-summary="<?= htmlspecialchars($workerStatusKey, ENT_QUOTES, 'UTF-8') ?>">
                            <span class="welcome-status-guide__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($workerStatusCard['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                            <span class="welcome-status-guide__copy">
                                <strong data-worker-status-count><?= (int) ($workerActivityStatusSummary['all'][$workerStatusKey] ?? 0) ?></strong>
                                <span data-worker-status-label><?= htmlspecialchars($workerActivityStatusLabels['all'][$workerStatusKey], ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                        </article>
                    <?php endforeach; ?>
                </div>
                <script id="workerActivityStatusData" type="application/json"><?= json_encode(['counts' => $workerActivityStatusSummary, 'labels' => $workerActivityStatusLabels], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
            </aside>
        </div>
    </section>

    <section class="welcome-unified-section welcome-unified-section--access" aria-labelledby="worker-access-title">
        <header class="welcome-section-head">
            <div class="welcome-section-title-block">
                <div class="sct-title-with-help welcome-section-title-with-tip"><h2 id="worker-access-title"><?= htmlspecialchars(t('welcome_quick_access_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('welcome_quick_access_text'), t('info_more_label')) ?></div>
                <p><?= htmlspecialchars(t('welcome_quick_access_text'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </header>
        <div class="welcome-access-grid welcome-access-grid--worker welcome-access-grid--uniform">
            <a href="./api/usuarios/mis-actividades.php" class="welcome-action-card welcome-action-card--space">
                <div class="welcome-action-card__top"><span class="welcome-action-card__icon" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span><span class="welcome-action-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                <div class="welcome-action-card__body"><h3><?= htmlspecialchars(t('worker_space_title'), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars(t('worker_space_text'), ENT_QUOTES, 'UTF-8') ?></p></div>
                <span class="welcome-action-card__cta"><?= htmlspecialchars(t('worker_open_space'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="./api/dashboard/dashboard.php" class="welcome-action-card welcome-action-card--primary">
                <div class="welcome-action-card__top"><span class="welcome-action-card__icon" aria-hidden="true"><i class="bi bi-speedometer2"></i></span><span class="welcome-action-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                <div class="welcome-action-card__body"><h3><?= htmlspecialchars(t('welcome_card_panel_title'), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars(t('welcome_worker_panel_text'), ENT_QUOTES, 'UTF-8') ?></p></div>
                <span class="welcome-action-card__cta"><?= htmlspecialchars(t('welcome_open_panel'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="./api/usuarios/gestiones.php" class="welcome-action-card welcome-action-card--secondary">
                <div class="welcome-action-card__top"><span class="welcome-action-card__icon" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span><span class="welcome-action-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                <div class="welcome-action-card__body"><h3><?= htmlspecialchars(t('welcome_card_gestiones_title'), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars(t('welcome_worker_gestiones_text'), ENT_QUOTES, 'UTF-8') ?></p></div>
                <span class="welcome-action-card__cta"><?= htmlspecialchars(t('welcome_open_gestiones'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="./api/usuarios/gestion-usuarios.php?self=1" class="welcome-action-card welcome-action-card--account welcome-action-card--profile">
                <div class="welcome-action-card__top"><span class="welcome-action-card__icon" aria-hidden="true"><i class="bi bi-person-gear"></i></span><span class="welcome-action-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                <div class="welcome-action-card__body"><h3><?= htmlspecialchars(t('worker_management_profile_title'), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars($workerEmail, ENT_QUOTES, 'UTF-8') ?><?php if ($workerCompanyName !== ''): ?> · <?= htmlspecialchars($workerCompanyName, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p></div>
                <span class="welcome-action-card__cta"><?= htmlspecialchars(t('worker_manage_info'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </section>
</main>

<dialog class="welcome-start-dialog" id="welcomeActivityStartDialog" aria-labelledby="welcomeActivityStartTitle">
    <div class="welcome-start-dialog__panel">
        <div class="welcome-start-dialog__icon" aria-hidden="true"><i class="bi bi-play-circle"></i></div>
        <div class="welcome-start-dialog__content">
            <span class="welcome-start-dialog__eyebrow" data-start-dialog-type></span>
            <h2 id="welcomeActivityStartTitle"><?= htmlspecialchars(t('welcome_start_dialog_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="welcome-start-dialog__activity" data-start-dialog-name></p>
            <p><?= htmlspecialchars(t('welcome_start_dialog_intro'), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="welcome-start-dialog__warning" data-start-dialog-overdue hidden><i class="bi bi-clock-history" aria-hidden="true"></i><span><?= htmlspecialchars(t('welcome_start_dialog_expired'), ENT_QUOTES, 'UTF-8') ?></span></div>
            <div class="welcome-start-dialog__instructions"><strong><?= htmlspecialchars(t('welcome_start_dialog_instructions_title'), ENT_QUOTES, 'UTF-8') ?></strong><ol><li><?= htmlspecialchars(t('welcome_start_dialog_instruction_1'), ENT_QUOTES, 'UTF-8') ?></li><li><?= htmlspecialchars(t('welcome_start_dialog_instruction_2'), ENT_QUOTES, 'UTF-8') ?></li><li><?= htmlspecialchars(t('welcome_start_dialog_instruction_3'), ENT_QUOTES, 'UTF-8') ?></li></ol></div>
        </div>
        <div class="welcome-start-dialog__actions"><button type="button" class="btn btn-outline-custom" data-start-dialog-cancel><?= htmlspecialchars(t('welcome_start_dialog_cancel'), ENT_QUOTES, 'UTF-8') ?></button><button type="button" class="btn btn-primary-custom" data-start-dialog-confirm><?= htmlspecialchars(t('welcome_start_dialog_confirm'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right"></i></button></div>
    </div>
</dialog>

<script src="js/bienvenida-worker.js?v=<?= $workerAssetVersion ?>"></script>
<?php require __DIR__ . '/app-footer.php'; ?>
</body>
</html>
