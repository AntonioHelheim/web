<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/personal-activities.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../partials/info-tip.php';

requireLoginPage('../../acceso-denegado.php');
aplicarCabecerasSeguridad();

$accessContext = resolveCurrentUserAccessContext($pdo);
if ($accessContext === null) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

$perfil = isset($accessContext['profile']) && is_array($accessContext['profile'])
    ? $accessContext['profile']
    : (currentUserProfile($pdo) ?: []);
$userId = (string) ($_SESSION['user_email'] ?? '');
$companyId = (int) ($perfil['id_company'] ?? 0);
$isGlobalAdmin = !empty($accessContext['is_global_admin']);
$roleKey = (string) ($accessContext['primary_role'] ?? 'trabajador');
$isWorkerHub = $roleKey === 'trabajador';

$activitiesUserName = trim((string) ($perfil['name'] ?? '') . ' ' . (string) ($perfil['lastname'] ?? ''));
if ($activitiesUserName === '') {
    $activitiesUserName = ucfirst(explode('@', $userId !== '' ? $userId : 'usuario')[0]);
}
$activitiesUserName = capitalizarNombre($activitiesUserName);

$items = sctPersonalActivityItems($pdo, $companyId, $userId, $roleKey, $isGlobalAdmin);
$categoryCounts = ['all' => count($items), 'induction' => 0, 'audits' => 0, 'self' => 0, 'forms' => 0, 'protocols' => 0];
$statusCounts = ['all' => count($items), 'overdue' => 0, 'in_progress' => 0, 'completed' => 0, 'failed' => 0];

foreach ($items as &$item) {
    $category = (string) ($item['category'] ?? '');
    $state = $item['state'] ?? null;
    $attemptsUsed = (int) ($item['attempts_used'] ?? 0);
    $overdue = !empty($item['overdue']);

    $item['type_label'] = t((string) ($item['type_label_key'] ?? 'welcome_activity_type_other'));
    $item['status'] = sctPersonalActivityStatusLabel($item);

    $item['detail_status'] = sctPersonalActivityDetailStatus($item);
    $detailStatus = (string) $item['detail_status'];
    if ($detailStatus === 'overdue') $statusCounts['overdue']++;
    elseif ($detailStatus === 'in_progress') $statusCounts['in_progress']++;
    elseif ($detailStatus === 'failed') $statusCounts['failed']++;
    elseif ($detailStatus === 'approved' || $detailStatus === 'complete') $statusCounts['completed']++;
    $item['hub_href'] = $isWorkerHub
        ? sctPersonalActivityHubHref($item, 'mis-actividades.php')
        : (string) ($item['href'] ?? 'mis-actividades.php');
    $item['module_href'] = sctPersonalActivityModuleHref($item, '../');

    if (isset($categoryCounts[$category])) $categoryCounts[$category]++;
}
unset($item);
$initialCategory = (string) ($_GET['category'] ?? 'all');
if (!array_key_exists($initialCategory, $categoryCounts)) $initialCategory = 'all';
$initialStatus = (string) ($_GET['activity_status'] ?? 'all');
if (!array_key_exists($initialStatus, $statusCounts)) $initialStatus = 'all';
$initialSort = (string) ($_GET['sort'] ?? 'priority');
if (!in_array($initialSort, ['priority','deadline','status'], true)) $initialSort = 'priority';

$selectedSource = preg_replace('/[^a-z_]/', '', (string) ($_GET['activity_source'] ?? ''));
$selectedId = (int) ($_GET['activity_id'] ?? 0);
$selectedItem = null;
if ($isWorkerHub && $selectedSource !== '' && $selectedId > 0) {
    foreach ($items as $candidate) {
        if ((string) ($candidate['source'] ?? '') === $selectedSource && (int) ($candidate['source_id'] ?? 0) === $selectedId) {
            $selectedItem = $candidate;
            break;
        }
    }
}
$initialActivityMode = $selectedItem !== null && empty($selectedItem['scheduled']);
$initialFrom = preg_replace('/[^a-z_\-]/', '', (string) ($_GET['from'] ?? ''));

$categoryLabels = [
    'all' => t('activities_filter_all'),
    'induction' => t('welcome_activity_type_course'),
    'audits' => t('welcome_activity_type_audit'),
    'self' => t('welcome_activity_type_self'),
    'forms' => t('mgmt_my_forms_title'),
    'protocols' => t('mgmt_my_protocols_title'),
];
$categoryIcons = [
    'all' => 'bi-grid',
    'induction' => 'bi-journal-check',
    'audits' => 'bi-clipboard2-check',
    'self' => 'bi-person-check',
    'forms' => 'bi-card-text',
    'protocols' => 'bi-clipboard2-pulse',
];
$statusLabels = [
    'all' => t('activities_filter_all'),
    'overdue' => t('worker_status_overdue'),
    'in_progress' => t('worker_status_in_progress'),
    'completed' => t('activities_color_completed'),
    'failed' => t('worker_status_failed'),
];
$statusIcons = [
    'all' => 'bi-list-check',
    'overdue' => 'bi-exclamation-octagon-fill',
    'in_progress' => 'bi-play-circle-fill',
    'completed' => 'bi-check-circle-fill',
    'failed' => 'bi-x-circle-fill',
];
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION . '-p76', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('activities_page_title'),ENT_QUOTES,'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>">
<link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
</head>
<body class="sct-module-page sct-personal-hub-page<?= $initialActivityMode ? ' sct-hub-activity-mode' : '' ?>">
<?php $sctNavbarBasePath='../../'; $sctNavbarBackHref='../../bienvenida.php'; require __DIR__.'/../../partials/app-navbar.php'; ?>
<main class="container sct-personal-hub sct-main-shell"
      data-activity-hub
      data-initial-category="<?= htmlspecialchars($initialCategory, ENT_QUOTES, 'UTF-8') ?>"
      data-initial-status="<?= htmlspecialchars($initialStatus, ENT_QUOTES, 'UTF-8') ?>"
      data-initial-sort="<?= htmlspecialchars($initialSort, ENT_QUOTES, 'UTF-8') ?>"
      data-initial-source="<?= htmlspecialchars($selectedSource, ENT_QUOTES, 'UTF-8') ?>"
      data-initial-id="<?= (int) $selectedId ?>"
      data-initial-from="<?= htmlspecialchars($initialFrom, ENT_QUOTES, 'UTF-8') ?>">

    <div data-activity-browse>
        <header class="sct-personal-page-header sct-main-hero sct-personal-section-hero">
            <div class="sct-main-hero__copy">
                <span class="section-label sct-main-pill"><i class="bi bi-grid-1x2" aria-hidden="true"></i><?= htmlspecialchars(t('mgmt_filter_personal'),ENT_QUOTES,'UTF-8') ?></span>
                <h1 class="section-title sct-main-title mb-0"><?= htmlspecialchars(t('activities_title'),ENT_QUOTES,'UTF-8') ?></h1>
                <p class="sct-page-intro sct-main-intro"><?= htmlspecialchars($isWorkerHub ? t('activities_intro_worker') : t('activities_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                <p class="sct-personal-page-header__identity"><?= htmlspecialchars($activitiesUserName,ENT_QUOTES,'UTF-8') ?></p>
            </div>
        </header>

        <?php $sctModuleMode='personal'; require __DIR__.'/../../partials/module-context.php'; ?>
        <?php if (!$isWorkerHub): ?>
            <?php $sctPersonalNavActive='all'; $sctPersonalNavBasePath='../'; require __DIR__.'/../../partials/personal-activity-nav.php'; ?>
        <?php endif; ?>

        <?php if ($isWorkerHub): ?>
        <section class="sct-activity-filter-panel" aria-label="<?= htmlspecialchars(t('activities_filter_label'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="sct-activity-filter-group sct-activity-filter-group--type">
                <div class="sct-activity-filter-group__head">
                    <span class="sct-activity-filter-group__eyebrow"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i><?= htmlspecialchars(t('activities_type_filter_title'), ENT_QUOTES, 'UTF-8') ?></span>
                    <p><?= htmlspecialchars(t('activities_type_filter_help'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <nav class="sct-activity-type-filters" aria-label="<?= htmlspecialchars(t('activities_type_filter_title'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($categoryCounts as $categoryKey => $categoryCount): ?>
                        <?php if ($categoryKey !== 'all' && $categoryCount <= 0) continue; ?>
                        <button type="button" class="sct-activity-type-filter<?= $initialCategory===$categoryKey?' is-active':'' ?>" data-category-filter="<?= htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8') ?>" aria-pressed="<?= $initialCategory===$categoryKey?'true':'false' ?>">
                            <span class="sct-activity-type-filter__icon"><i class="bi <?= htmlspecialchars($categoryIcons[$categoryKey] ?? 'bi-list-task', ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
                            <span class="sct-activity-type-filter__label"><?= htmlspecialchars($categoryLabels[$categoryKey] ?? $categoryKey, ENT_QUOTES, 'UTF-8') ?></span>
                            <strong data-category-count><?= (int) $categoryCount ?></strong>
                        </button>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="sct-activity-filter-footer">
                <div class="sct-activity-status-group">
                    <div class="sct-activity-status-group__head">
                        <span><?= htmlspecialchars(t('activities_status_filter_title'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="sct-activity-status-filters" role="group" aria-label="<?= htmlspecialchars(t('activities_status_filter_title'), ENT_QUOTES, 'UTF-8') ?>">
                        <?php foreach ($statusCounts as $statusKey => $statusCount): ?>
                            <button type="button" class="sct-activity-status-filter sct-activity-status-filter--<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?><?= $initialStatus===$statusKey?' is-active':'' ?>" data-status-filter="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" aria-pressed="<?= $initialStatus===$statusKey?'true':'false' ?>">
                                <span class="sct-activity-status-filter__icon"><i class="bi <?= htmlspecialchars($statusIcons[$statusKey] ?? 'bi-circle', ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
                                <span class="sct-activity-status-filter__copy"><small><?= htmlspecialchars($statusLabels[$statusKey] ?? $statusKey, ENT_QUOTES, 'UTF-8') ?></small><strong data-status-count><?= (int) $statusCount ?></strong></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <label class="sct-activity-sort">
                    <span><?= htmlspecialchars(t('activities_sort_label'), ENT_QUOTES, 'UTF-8') ?></span>
                    <select class="form-select form-select-sm" data-activity-sort>
                        <option value="priority"<?= $initialSort === 'priority' ? ' selected' : '' ?>><?= htmlspecialchars(t('activities_sort_priority'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="deadline"<?= $initialSort === 'deadline' ? ' selected' : '' ?>><?= htmlspecialchars(t('activities_sort_deadline'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="status"<?= $initialSort === 'status' ? ' selected' : '' ?>><?= htmlspecialchars(t('activities_sort_status'), ENT_QUOTES, 'UTF-8') ?></option>
                    </select>
                </label>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($items): ?>
        <section id="activities-list" class="sct-personal-card-grid" data-personal-card-grid>
            <?php foreach ($items as $item): ?>
                <?php
                $deadlineDisplay = '';
                if (!empty($item['deadline'])) {
                    $deadlineTs = strtotime((string) $item['deadline']);
                    if ($deadlineTs !== false) $deadlineDisplay = date('d/m/Y', $deadlineTs);
                }
                $completedDisplay = '';
                if (!empty($item['completed_at'])) {
                    $completedTs = strtotime((string) $item['completed_at']);
                    if ($completedTs !== false) $completedDisplay = date('d/m/Y H:i', $completedTs);
                }
                $availableDisplay = '';
                if (!empty($item['available_at'])) {
                    $availableTs = strtotime((string) $item['available_at']);
                    if ($availableTs !== false) $availableDisplay = date('d/m/Y H:i', $availableTs);
                }
                $itemHubHref = $isWorkerHub ? sctPersonalActivityHubHref($item, 'mis-actividades.php') : (string) ($item['href'] ?? '#');
                ?>
                <article class="sct-personal-activity-card sct-personal-activity-card--status-<?= htmlspecialchars((string) $item['detail_status'], ENT_QUOTES, 'UTF-8') ?><?= $item['overdue']?' is-overdue':'' ?><?= $item['complete']?' is-complete':'' ?>"
                         data-personal-card
                         data-category="<?= htmlspecialchars((string) $item['category'],ENT_QUOTES,'UTF-8') ?>"
                         data-status="<?= htmlspecialchars((string) $item['status_bucket'],ENT_QUOTES,'UTF-8') ?>"
                         data-detail-status="<?= htmlspecialchars((string) $item['detail_status'],ENT_QUOTES,'UTF-8') ?>"
                         data-sort-priority="<?= !empty($item['overdue']) ? '0' : (!empty($item['complete']) ? ((string) ($item['detail_status'] ?? '') === 'failed' ? '2' : '3') : '1') ?>"
                         data-sort-status="<?= ((string) ($item['detail_status'] ?? '') === 'overdue') ? '0' : (((string) ($item['detail_status'] ?? '') === 'in_progress') ? '1' : (((string) ($item['detail_status'] ?? '') === 'failed') ? '2' : '3')) ?>"
                         data-sort-deadline="<?= !empty($item['deadline']) && strtotime((string) $item['deadline']) !== false ? (int) strtotime((string) $item['deadline']) : 9999999999 ?>"
                         data-activity-source="<?= htmlspecialchars((string) $item['source'],ENT_QUOTES,'UTF-8') ?>"
                         data-activity-id="<?= (int) $item['source_id'] ?>"
                         data-activity-name="<?= htmlspecialchars((string) $item['name'],ENT_QUOTES,'UTF-8') ?>"
                         data-activity-type="<?= htmlspecialchars((string) $item['type_label'],ENT_QUOTES,'UTF-8') ?>"
                         data-activity-status-label="<?= htmlspecialchars((string) $item['status'],ENT_QUOTES,'UTF-8') ?>"
                         data-activity-deadline="<?= htmlspecialchars($deadlineDisplay,ENT_QUOTES,'UTF-8') ?>"
                         data-activity-completed="<?= htmlspecialchars($completedDisplay,ENT_QUOTES,'UTF-8') ?>"
                         data-activity-complete="<?= !empty($item['complete']) ? '1' : '0' ?>"
                         data-activity-scheduled="<?= !empty($item['scheduled']) ? '1' : '0' ?>"
                         data-activity-result="<?= htmlspecialchars((string) ($item['result'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                         data-module-href="<?= htmlspecialchars((string) $item['module_href'], ENT_QUOTES, 'UTF-8') ?>"
                         data-hub-href="<?= htmlspecialchars($itemHubHref, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="sct-personal-activity-card__top">
                        <span class="sct-personal-activity-card__icon"><i class="bi <?= htmlspecialchars((string) $item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
                        <span class="sct-personal-activity-card__type"><?= htmlspecialchars((string) $item['type_label'],ENT_QUOTES,'UTF-8') ?></span>
                    </div>
                    <div class="sct-personal-activity-card__body">
                        <h2><?= htmlspecialchars((string) $item['name'],ENT_QUOTES,'UTF-8') ?></h2>
                        <?php if ($item['description']!==''): ?><p><?= htmlspecialchars((string) $item['description'],ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
                        <dl class="sct-personal-activity-card__facts">
                            <div><dt><?= htmlspecialchars(t('activities_status_label'),ENT_QUOTES,'UTF-8') ?></dt><dd><?= htmlspecialchars((string) $item['status'],ENT_QUOTES,'UTF-8') ?></dd></div>
                            <?php if (!empty($item['scheduled']) && $availableDisplay!==''): ?><div><dt><?= htmlspecialchars(t('activities_available_from'),ENT_QUOTES,'UTF-8') ?></dt><dd><?= htmlspecialchars($availableDisplay,ENT_QUOTES,'UTF-8') ?></dd></div><?php endif; ?>
                            <?php if ($deadlineDisplay!==''): ?><div><dt><?= htmlspecialchars(t('activities_due_label'),ENT_QUOTES,'UTF-8') ?></dt><dd><?= htmlspecialchars($deadlineDisplay,ENT_QUOTES,'UTF-8') ?></dd></div><?php endif; ?>
                            <?php if ($item['attempts_allowed']!==null): ?><div><dt><?= htmlspecialchars(t('activities_attempts_label'),ENT_QUOTES,'UTF-8') ?></dt><dd><?= (int)$item['attempts_used'] ?>/<?= (int)$item['attempts_allowed'] ?></dd></div><?php endif; ?>
                        </dl>
                    </div>
                    <?php if (!empty($item['scheduled'])): ?>
                        <button type="button" class="btn btn-outline-secondary sct-personal-activity-card__action" disabled aria-disabled="true"><?= htmlspecialchars(t('activities_scheduled_action'),ENT_QUOTES,'UTF-8') ?><i class="bi bi-clock"></i></button>
                    <?php else: ?>
                        <a class="btn btn-primary-custom sct-personal-activity-card__action"
                           href="<?= htmlspecialchars($itemHubHref,ENT_QUOTES,'UTF-8') ?>"
                           <?= $isWorkerHub ? 'data-open-hub-activity' : '' ?>><?= htmlspecialchars(!empty($item['complete'])?t('activities_review_action'):t('activities_open_action'),ENT_QUOTES,'UTF-8') ?><i class="bi bi-arrow-right"></i></a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
        <div id="activityFilterEmpty" class="sct-personal-empty sct-personal-filter-empty" hidden>
            <i class="bi bi-check2-circle" aria-hidden="true"></i>
            <div><strong><?= htmlspecialchars(t('activities_filter_empty_title'),ENT_QUOTES,'UTF-8') ?></strong><p><?= htmlspecialchars(t('activities_filter_empty_text'),ENT_QUOTES,'UTF-8') ?></p></div>
        </div>
        <?php else: ?>
            <div class="sct-personal-empty"><i class="bi bi-check2-circle"></i><div><strong><?= htmlspecialchars(t('activities_empty_title'),ENT_QUOTES,'UTF-8') ?></strong><p><?= htmlspecialchars(t('activities_empty_text'),ENT_QUOTES,'UTF-8') ?></p></div></div>
        <?php endif; ?>
    </div>

    <?php if ($isWorkerHub): ?>
    <section class="sct-hub-activity-workspace" data-activity-workspace<?= !$initialActivityMode ? ' hidden' : '' ?> aria-live="polite">
        <header class="sct-hub-activity-workspace__header">
            <div class="sct-hub-activity-workspace__heading">
                <span class="section-label" data-workspace-type><?= htmlspecialchars((string) ($selectedItem['type_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 class="sct-hub-activity-workspace__title" data-workspace-title><?= htmlspecialchars((string) ($selectedItem['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
                <span class="sct-hub-activity-workspace__status" data-workspace-status><?= htmlspecialchars((string) ($selectedItem['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <button type="button" class="btn btn-outline-custom btn-sm sct-hub-activity-back" data-activity-back><i class="bi bi-box-arrow-left" aria-hidden="true"></i><?= htmlspecialchars(t('activity_exit'), ENT_QUOTES, 'UTF-8') ?></button>
        </header>

        <div class="sct-hub-activity-complete" data-complete-detail hidden>
            <div class="sct-hub-activity-complete__icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></div>
            <div class="sct-hub-activity-complete__copy">
                <h2 data-complete-title></h2>
                <dl class="sct-hub-activity-complete__facts">
                    <div><dt><?= htmlspecialchars(t('activities_status_label'), ENT_QUOTES, 'UTF-8') ?></dt><dd data-complete-status></dd></div>
                    <div data-complete-deadline-row><dt><?= htmlspecialchars(t('activities_due_label'), ENT_QUOTES, 'UTF-8') ?></dt><dd data-complete-deadline></dd></div>
                    <div data-complete-date-row><dt><?= htmlspecialchars(t('history_date'), ENT_QUOTES, 'UTF-8') ?></dt><dd data-complete-date></dd></div>
                </dl>
            </div>
        </div>

        <div class="sct-hub-activity-frame-shell" data-frame-shell hidden>
            <div class="sct-hub-activity-loading" data-frame-loading><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span><?= htmlspecialchars(t('my_induction_loading'), ENT_QUOTES, 'UTF-8') ?></span></div>
            <iframe class="sct-hub-activity-frame" data-activity-frame title="<?= htmlspecialchars(t('activities_title'), ENT_QUOTES, 'UTF-8') ?>"></iframe>
        </div>
    </section>
    <?php endif; ?>
</main>

<script src="../../js/mis-actividades.js?v=<?= $assetVersionEscaped ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
<?php require __DIR__.'/../../partials/app-footer.php'; ?>
<script src="../../js/sct-module-ui.js?v=20260920-p43"></script>
</body></html>
