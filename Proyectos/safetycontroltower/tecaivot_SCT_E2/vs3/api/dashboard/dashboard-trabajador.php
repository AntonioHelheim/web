<?php
$workerDashAsset = htmlspecialchars($ASSET_VERSION . '-p64-category-heading', ENT_QUOTES, 'UTF-8');
$workerDashStrings = [
    'loading' => t('dashboard_loading'),
    'error' => t('dashboard_error_response'),
    'no_data' => t('dashboard_no_data'),
    'protocols' => t('worker_dashboard_timeline_protocols'),
    'forms' => t('worker_dashboard_timeline_forms'),
    'updated' => t('worker_dashboard_updated'),
    'course' => t('welcome_activity_type_course'),
    'audit' => t('welcome_activity_type_audit'),
    'self' => t('welcome_activity_type_self'),
    'form' => t('mgmt_my_forms_title'),
    'protocol' => t('mgmt_my_protocols_title'),
    'completed' => t('activities_filter_complete'),
    'pending' => t('activities_filter_incomplete'),
    'in_progress' => t('worker_status_in_progress'),
    'overdue' => t('worker_status_overdue'),
    'approved' => t('activities_color_completed'),
    'failed' => t('worker_status_failed'),
    'due_short' => t('worker_summary_due_caption'),
    'no_due' => t('worker_summary_no_due_caption'),
    'activity_detail' => t('worker_dashboard_activity_detail_title'),
    'activity_progress' => t('worker_dashboard_activity_progress'),
    'deadline' => t('worker_induction_due'),
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('dashboard_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= $workerDashAsset ?>">
    <link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
    <link rel="stylesheet" href="../../css/dashboard.css?v=<?= $workerDashAsset ?>">
</head>
<body class="dashboard-page dashboard-page--worker sct-role-trabajador">
<a class="dashboard-skip-link" href="#worker-dashboard-main"><?= htmlspecialchars(t('welcome_skip_to_content'), ENT_QUOTES, 'UTF-8') ?></a>
<div class="dashboard-shell sct-main-shell" data-worker-dashboard>
    <?php
    $sctNavbarBasePath = '../../';
    $sctNavbarBackHref = '../../bienvenida.php';
    $sctNavbarBackLabel = t('nav_home');
    $sctNavbarBackIcon = 'bi-arrow-left';
    require __DIR__ . '/../../partials/app-navbar.php';
    ?>

    <main id="worker-dashboard-main" class="dashboard-work-layout dashboard-work-layout--worker">
        <div class="dashboard-workspace">
            <header class="dashboard-hero dashboard-hero--workspace sct-main-hero" aria-labelledby="worker-dashboard-title">
                <div class="dashboard-hero__copy sct-main-hero__copy">
                    <span class="dashboard-hero__kicker sct-main-pill"><i class="bi bi-speedometer2" aria-hidden="true"></i><span><?= htmlspecialchars(t('worker_dashboard_kicker'), ENT_QUOTES, 'UTF-8') ?></span></span>
                    <h1 id="worker-dashboard-title" class="sct-main-title"><?= htmlspecialchars(t('worker_dashboard_title'), ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="dashboard-hero__intro sct-main-intro"><?= htmlspecialchars(t('worker_dashboard_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </header>

            <div id="workerDashAlert" class="alert alert-danger d-none" role="alert" aria-live="assertive"></div>
            <div id="workerDashLoading" class="dashboard-loading-card" aria-live="polite"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span><?= htmlspecialchars(t('dashboard_loading'), ENT_QUOTES, 'UTF-8') ?></span></div>

            <div id="workerDashContent" class="d-none dashboard-worker-content">
                <div class="dashboard-worker-overview-grid">
                    <div class="dashboard-worker-overview-main">
                <section class="dashboard-section dashboard-section--summary dashboard-section--key dashboard-section--collapsible-card dashboard-section--worker-static" data-sct-no-collapse aria-labelledby="worker-current-state-title">
                    <div class="dashboard-section-head dashboard-section-head--worker">
                        <div class="dashboard-section-head__copy">
                            <div class="sct-title-with-help dashboard-heading-block">
                                <h2 id="worker-current-state-title"><?= htmlspecialchars(t('worker_dashboard_current_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                                <?= sctInfoTip(t('worker_dashboard_current_help'), t('info_more_label')) ?>
                            </div>
                            <p><?= htmlspecialchars(t('worker_dashboard_current_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <article id="worker-current-state-body" class="dashboard-panel dashboard-worker-current-panel" aria-label="<?= htmlspecialchars(t('worker_dashboard_current_title'), ENT_QUOTES, 'UTF-8') ?>">
                        <div class="dashboard-worker-current-visual">
                            <div id="workerStateDonut" class="dashboard-worker-state-donut" style="--state-overdue:0;--state-progress:0;--state-approved:0;--state-failed:0" role="img" aria-label="<?= htmlspecialchars(t('worker_dashboard_current_title'), ENT_QUOTES, 'UTF-8') ?>">
                                <span><strong id="workerMetricTotal">0</strong><small><?= htmlspecialchars(t('worker_dashboard_total'), ENT_QUOTES, 'UTF-8') ?></small></span>
                            </div>
                            <div class="dashboard-worker-current-due" id="workerNextDeadline">
                                <span class="dashboard-worker-current-due__icon" aria-hidden="true"><i class="bi bi-calendar-event"></i></span>
                                <span class="dashboard-worker-current-due__copy"><small><?= htmlspecialchars(t('worker_next_deadline'), ENT_QUOTES, 'UTF-8') ?></small><strong>—</strong><em><?= htmlspecialchars(t('worker_no_upcoming_deadline_text'), ENT_QUOTES, 'UTF-8') ?></em></span>
                            </div>
                        </div>
                        <div class="dashboard-worker-current-bars" role="list">
                            <div class="dashboard-worker-current-row dashboard-worker-current-row--in-progress" role="listitem">
                                <div class="dashboard-worker-current-row__head"><span><?= htmlspecialchars(t('worker_status_in_progress'), ENT_QUOTES, 'UTF-8') ?></span><strong id="workerMetricInProgress">0</strong></div>
                                <div class="dashboard-worker-current-track" aria-hidden="true"><i id="workerInProgressBar" style="width:0%"></i></div>
                            </div>
                            <div class="dashboard-worker-current-row dashboard-worker-current-row--overdue" role="listitem">
                                <div class="dashboard-worker-current-row__head"><span><?= htmlspecialchars(t('activities_filter_overdue'), ENT_QUOTES, 'UTF-8') ?></span><strong id="workerMetricOverdue">0</strong></div>
                                <div class="dashboard-worker-current-track" aria-hidden="true"><i id="workerOverdueBar" style="width:0%"></i></div>
                            </div>
                            <div class="dashboard-worker-current-row dashboard-worker-current-row--complete" role="listitem">
                                <div class="dashboard-worker-current-row__head"><span><?= htmlspecialchars(t('activities_color_completed'), ENT_QUOTES, 'UTF-8') ?></span><strong id="workerMetricApproved">0</strong></div>
                                <div class="dashboard-worker-current-track" aria-hidden="true"><i id="workerApprovedBar" style="width:0%"></i></div>
                            </div>
                            <div class="dashboard-worker-current-row dashboard-worker-current-row--failed" role="listitem">
                                <div class="dashboard-worker-current-row__head"><span><?= htmlspecialchars(t('worker_status_failed'), ENT_QUOTES, 'UTF-8') ?></span><strong id="workerMetricFailed">0</strong></div>
                                <div class="dashboard-worker-current-track" aria-hidden="true"><i id="workerFailedBar" style="width:0%"></i></div>
                            </div>
                        </div>
                    </article>
                </section>

                <section class="dashboard-section dashboard-section--worker-progress dashboard-section--collapsible-card dashboard-section--worker-static" data-sct-no-collapse aria-labelledby="worker-progress-title">
                    <div class="dashboard-section-head dashboard-section-head--worker">
                        <div class="dashboard-section-head__copy">
                            <div class="sct-title-with-help dashboard-heading-block"><h2 id="worker-progress-title"><?= htmlspecialchars(t('worker_dashboard_progress_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('worker_dashboard_progress_help'), t('info_more_label')) ?></div>
                            <p><?= htmlspecialchars(t('worker_dashboard_progress_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <article id="worker-progress-body" class="dashboard-panel dashboard-panel--tone-cyan dashboard-worker-progress-panel">
                        <div id="workerProgressRing" class="dashboard-donut dashboard-worker-progress-ring" style="--progress:0" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                            <span><strong class="dashboard-donut__value">0%</strong><small class="dashboard-donut__label"><?= htmlspecialchars(t('worker_dashboard_progress_label'), ENT_QUOTES, 'UTF-8') ?></small></span>
                        </div>
                        <div class="dashboard-worker-progress-copy">
                            <div class="dashboard-worker-progress-track" aria-hidden="true"><span id="workerProgressBar" style="width:0%"></span></div>
                            <div id="workerProgressCategoryList" class="dashboard-worker-progress-list" aria-live="polite"></div>
                        </div>
                    </article>
                </section>
                    </div>

                <section class="dashboard-section dashboard-section--worker-category-side dashboard-section--collapsible-card dashboard-section--worker-static" data-sct-no-collapse aria-labelledby="worker-category-title">
                    <div class="dashboard-section-head dashboard-section-head--worker dashboard-section-head--category">
                        <div class="dashboard-section-head__copy">
                            <div class="sct-title-with-help dashboard-heading-block"><h2 id="worker-category-title"><?= htmlspecialchars(t('worker_dashboard_category_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('worker_dashboard_category_help'), t('info_more_label')) ?></div>
                            <p><?= htmlspecialchars(t('worker_dashboard_category_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="dashboard-section-head-actions">
                            <a class="dashboard-worker-section-link" href="../usuarios/mis-actividades.php"><?= htmlspecialchars(t('worker_open_space'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <article id="worker-category-body" class="dashboard-panel dashboard-worker-category-panel">
                        <div id="workerCategoryGrid" class="dashboard-worker-category-grid" aria-live="polite"></div>
                    </article>
                </section>
                </div>

                <section class="dashboard-section dashboard-section--collapsible-card dashboard-section--worker-trend dashboard-section--worker-static" data-sct-no-collapse aria-labelledby="worker-trend-title">
                    <div class="dashboard-section-head dashboard-section-head--worker dashboard-section-head--trend">
                        <div class="dashboard-section-head__copy">
                            <div class="sct-title-with-help dashboard-heading-block"><h2 id="worker-trend-title"><?= htmlspecialchars(t('worker_dashboard_history_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('worker_dashboard_history_help'), t('info_more_label')) ?></div>
                            <p><?= htmlspecialchars(t('worker_dashboard_history_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="dashboard-section-head-actions dashboard-section-head-actions--trend">
                            <div class="dashboard-worker-history-tools">
                                <label for="workerPeriod"><?= htmlspecialchars(t('dashboard_period'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select id="workerPeriod" class="form-select form-select-sm" aria-label="<?= htmlspecialchars(t('dashboard_period'), ENT_QUOTES, 'UTF-8') ?>">
                                    <option value="today"><?= htmlspecialchars(t('dashboard_period_today'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="7"><?= htmlspecialchars(t('dashboard_period_7'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="15"><?= htmlspecialchars(t('dashboard_period_15'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="30"><?= htmlspecialchars(t('dashboard_period_30'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="90" selected><?= htmlspecialchars(t('dashboard_period_90'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="180"><?= htmlspecialchars(t('dashboard_period_180'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="365"><?= htmlspecialchars(t('dashboard_period_365'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="all"><?= htmlspecialchars(t('dashboard_period_all'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                                <small id="workerDashUpdated" class="dashboard-worker-updated" aria-live="polite"></small>
                            </div>
                        </div>
                    </div>
                    <article id="worker-trend-body" class="dashboard-panel dashboard-worker-trend-panel">
                        <div id="workerTrendChart" class="dashboard-worker-line-chart dashboard-worker-line-chart--points"></div>
                        <div id="workerTrendLegend" class="dashboard-worker-line-legend" aria-label="<?= htmlspecialchars(t('worker_dashboard_history_title'), ENT_QUOTES, 'UTF-8') ?>"></div>
                    </article>
                </section>
            </div>
        </div>
    </main>
</div>
<script id="workerDashboardI18n" type="application/json"><?= json_encode($workerDashStrings, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script src="../../js/dashboard-worker.js?v=<?= $workerDashAsset ?>"></script>
<?php require __DIR__ . '/../../partials/app-footer.php'; ?>
</body>
</html>
