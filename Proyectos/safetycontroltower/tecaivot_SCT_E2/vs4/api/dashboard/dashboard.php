<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../partials/info-tip.php';

requireCapabilityPage($pdo, 'dashboard.view', '../../acceso-denegado.php');
$accessContext = resolveCurrentUserAccessContext($pdo);
if ($accessContext === null) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

aplicarCabecerasSeguridad();
$isGlobalAdmin = dashboardIsGlobalAdmin($pdo);
$perfil = $accessContext['profile'];
$dashboardRole = (string) ($accessContext['primary_role'] ?? 'trabajador');
if (!in_array($dashboardRole, ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'], true)) {
    $dashboardRole = 'trabajador';
}

$nombreCompleto = trim((string) ($perfil['name'] ?? '') . ' ' . (string) ($perfil['lastname'] ?? ''));
if ($nombreCompleto === '') {
    $nombreCompleto = ucfirst(explode('@', (string) ($_SESSION['user_email'] ?? 'usuario'))[0]);
}
$nombreCompleto = capitalizarNombre($nombreCompleto);

$dashboardTitle = t('dashboard_role_title_' . $dashboardRole);
$dashboardIntro = t('dashboard_role_intro_' . $dashboardRole);
$dashboardViewLabel = t('dashboard_role_view_' . $dashboardRole);
$isWorkerDashboard = $dashboardRole === 'trabajador';

// P57: Usuario Demo / Trabajador usa un panel personal consolidado de sus actividades reales.
// El resto de roles conserva el dashboard operacional, con la misma estructura UX abatible.
if ($isWorkerDashboard) {
    require __DIR__ . '/dashboard-trabajador.php';
    exit;
}

$dashKeys = [
    'error_response','loading','select_company','all_projects','all_centers','no_data','scope_note',
    'kpi_companies','kpi_users','kpi_projects','kpi_centers','kpi_workers','kpi_events','kpi_open_critical','kpi_forms','kpi_protocol_overdue','kpi_protocol_review',
    'events_open','events_in_progress','events_closed','crit_low','crit_medium','crit_high','crit_critical',
    'eval_approved','eval_pending','eval_failed','rate_label','rate_empty','overdue_pending',
    'protocol_active','protocol_suspended','protocol_closed','protocol_cancelled','protocol_pending_review','protocol_conforme','protocol_observado','protocol_no_conforme','protocol_no_aplica',
    'recent_event','recent_form','recent_protocol','recent_audit','recent_empty',
    'trend_events','trend_forms','trend_protocols','trend_evaluations',
    'scope_all_companies','scope_note_consolidated','ranking_title','ranking_intro','ranking_company',
    'ranking_critical_events','ranking_overdue_protocols','ranking_pending_review','ranking_approval_rate','ranking_empty',
    'summary_title','summary_intro','status_title','status_intro','attention_title','attention_intro','attention_empty',
    'personal_progress_title','personal_progress_intro','personal_induction','personal_audits','personal_self','personal_pending','personal_certificates','personal_protocols_due','personal_forms',
    'progress_label','progress_pending','progress_complete','progress_in_progress','approval_company','approval_global','activity_progress',
    'programs_title','programs_intro','programs_average','programs_below_target','programs_in_progress','programs_target','programs_no_data',
    'metric_team_courses','metric_audits_open','metric_programs_below','metric_personal_pending','metric_personal_courses','metric_personal_certificates','metric_personal_protocols','metric_program_progress',
    'priority_critical_events','priority_protocol_overdue','priority_protocol_review','priority_induction_overdue','priority_personal_courses','priority_personal_evaluations','priority_personal_audits','priority_personal_protocols',
    'filter_toggle','filter_intro','filter_current','filter_applies','live_status_waiting','live_status_updated','live_status_error','event_distribution','event_distribution_intro','view_more','view_less','section_summary_title',
    'scope_note_personal','activity_snapshot_total','activity_snapshot_peak','activity_snapshot_empty','steps_empty','calendar_recent','calendar_no_events','calendar_day_empty','calendar_day_schedule','calendar_add_note','calendar_note_modal_title','calendar_note_type','calendar_note_type_note','calendar_note_type_reminder','calendar_note_title','calendar_note_details','calendar_note_date','calendar_note_time','calendar_note_priority','calendar_priority_low','calendar_priority_medium','calendar_priority_high','calendar_priority_critical','calendar_note_save','calendar_note_cancel','calendar_note_saved','calendar_personal_items','calendar_system_items','calendar_hover_hint','calendar_assignments','operations_state_title','operations_state_intro','indicators_key_title','indicators_key_intro','progress_timeline_title','progress_timeline_intro','progress_timeline_explainer','progress_timeline_series','progress_timeline_period_total','progress_timeline_period','activity_trend_explainer','graph_open_module','graph_more_info','graph_detail_title','graph_close_detail','chart_switch_to_bars','chart_switch_to_donut','chart_switch_to_line','chart_switch_to_columns','chart_switch_to_points','chart_switch_to_radial','section_collapse','section_expand','program_progress_chart_title','current_state_title','current_state_intro',
    'worker_to_start','worker_in_progress','worker_overdue','worker_approved','worker_failed','worker_certificates','worker_completed_help','worker_failed_help','worker_overdue_help','worker_in_progress_help','worker_to_start_help','worker_certificates_help','worker_completion','worker_completion_help','worker_approval_rate','worker_approval_help','worker_progress_title','worker_assigned_period','worker_completed_period','worker_period_help','worker_assigned_short','worker_completed_short','worker_approved_short','worker_failed_short','worker_dashboard_summary_title','worker_dashboard_summary_intro','worker_current_title','worker_current_intro','worker_trend_title','worker_trend_intro','worker_steps_title','worker_steps_intro'
];
$dashStrings = [];
foreach ($dashKeys as $key) {
    $dashStrings[$key] = t('dashboard_' . $key);
}

$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('dashboard_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>-p32-v51">
    <link rel="stylesheet" href="../../css/dashboard.css?v=<?= $assetVersionEscaped ?>-p77">
    <link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
</head>
<body class="dashboard-page sct-role-<?= htmlspecialchars($dashboardRole, ENT_QUOTES, 'UTF-8') ?>">
<a class="dashboard-skip-link" href="#dashboard-main"><?= htmlspecialchars(t('welcome_skip_to_content'), ENT_QUOTES, 'UTF-8') ?></a>

<div class="dashboard-shell sct-main-shell"
     data-is-global-admin="<?= $isGlobalAdmin ? '1' : '0' ?>"
     data-role="<?= htmlspecialchars($dashboardRole, ENT_QUOTES, 'UTF-8') ?>"
     data-user-key="<?= htmlspecialchars(hash('sha256', (string) ($_SESSION['user_email'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
     data-company-id="<?= (int) ($perfil['id_company'] ?? 0) ?>">
    <?php
    $sctNavbarBasePath = '../../';
    $sctNavbarBackHref = '../usuarios/gestiones.php';
    $sctNavbarBackLabel = t('dashboard_back');
    $sctNavbarBackIcon = 'bi-arrow-left';
    require __DIR__ . '/../../partials/app-navbar.php';
    ?>

    <main id="dashboard-main" class="dashboard-work-layout<?= $isWorkerDashboard ? ' dashboard-work-layout--worker' : '' ?>">
        <div class="dashboard-workspace">
            <header class="dashboard-hero dashboard-hero--workspace sct-main-hero" aria-labelledby="dashboard-title">
                <div class="dashboard-hero__copy sct-main-hero__copy">
                    <span class="dashboard-hero__kicker sct-main-pill"><i class="bi bi-speedometer2" aria-hidden="true"></i> <?= htmlspecialchars($dashboardViewLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    <h1 id="dashboard-title" class="sct-main-title"><?= htmlspecialchars($dashboardTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="dashboard-hero__intro sct-main-intro"><?= htmlspecialchars($dashboardIntro, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </header>

            <details class="dashboard-filter-panel" id="dashboardFilterPanel">
                <summary>
                    <span class="dashboard-filter-panel__summary-icon" aria-hidden="true"><i class="bi bi-sliders2"></i></span>
                    <span>
                        <strong><?= htmlspecialchars(t('dashboard_filter_toggle'), ENT_QUOTES, 'UTF-8') ?></strong>
                        <small><?= htmlspecialchars(t('dashboard_filter_intro'), ENT_QUOTES, 'UTF-8') ?></small>
                    </span>
                    <i class="bi bi-chevron-down dashboard-filter-panel__chevron" aria-hidden="true"></i>
                </summary>
                <div class="dashboard-filter-panel__content">
                    <div class="dashboard-filter-grid">
                        <?php if ($isGlobalAdmin): ?>
                            <div>
                                <label for="companySelect" class="form-label"><?= htmlspecialchars(t('dashboard_company'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select id="companySelect" class="form-select">
                                    <option value=""><?= htmlspecialchars(t('dashboard_select_company'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="__all__"><?= htmlspecialchars(t('dashboard_scope_all_companies'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div>
                            <label for="periodFilter" class="form-label"><?= htmlspecialchars(t('dashboard_period'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select id="periodFilter" class="form-select">
                                <option value="30"><?= htmlspecialchars(t('dashboard_period_30'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="90" selected><?= htmlspecialchars(t('dashboard_period_90'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="180"><?= htmlspecialchars(t('dashboard_period_180'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="365"><?= htmlspecialchars(t('dashboard_period_365'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="all"><?= htmlspecialchars(t('dashboard_period_all'), ENT_QUOTES, 'UTF-8') ?></option>
                            </select>
                        </div>
                        <?php if (!$isWorkerDashboard): ?>
                        <div id="projectFilterWrap">
                            <label for="projectFilter" class="form-label"><?= htmlspecialchars(t('dashboard_project'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select id="projectFilter" class="form-select"><option value=""><?= htmlspecialchars(t('dashboard_all_projects'), ENT_QUOTES, 'UTF-8') ?></option></select>
                        </div>
                        <div id="centerFilterWrap">
                            <label for="centerFilter" class="form-label"><?= htmlspecialchars(t('dashboard_center'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select id="centerFilter" class="form-select"><option value=""><?= htmlspecialchars(t('dashboard_all_centers'), ENT_QUOTES, 'UTF-8') ?></option></select>
                        </div>
                        <?php endif; ?>
                        <div class="dashboard-filter-actions">
                            <button type="button" id="resetFilters" class="btn btn-outline-custom">
                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                <?= htmlspecialchars(t('dashboard_reset'), ENT_QUOTES, 'UTF-8') ?>
                            </button>
                        </div>
                    </div>
                    <p class="dashboard-filter-note" id="filterScopeNote"
                       data-default="<?= htmlspecialchars($isWorkerDashboard ? t('dashboard_scope_note_personal') : t('dashboard_scope_note'), ENT_QUOTES, 'UTF-8') ?>"
                       data-consolidated="<?= htmlspecialchars(t('dashboard_scope_note_consolidated'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($isWorkerDashboard ? t('dashboard_scope_note_personal') : t('dashboard_scope_note'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="dashboard-live-status" id="dashboardLiveStatus" aria-live="polite"><span class="dashboard-live-status__dot" aria-hidden="true"></span><span><?= htmlspecialchars(t('dashboard_live_status_waiting'), ENT_QUOTES, 'UTF-8') ?></span></div>
                </div>
            </details>

            <?php if (!$isWorkerDashboard): ?>
            <nav class="dashboard-page-tabs sct-page-tabs" aria-label="<?= htmlspecialchars(t('dashboard_tab_nav_label'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="dashboard-page-tabs__track sct-page-tabs__track" role="tablist">
                    <button type="button" class="dashboard-page-tab sct-page-tab is-active" role="tab" aria-selected="true" data-dashboard-tab="summary" aria-controls="dashboard-tab-summary"><i class="bi bi-stars" aria-hidden="true"></i><span><?= htmlspecialchars(t('dashboard_tab_summary'), ENT_QUOTES, 'UTF-8') ?></span></button>
                    <button type="button" class="dashboard-page-tab sct-page-tab" role="tab" aria-selected="false" data-dashboard-tab="tracking" aria-controls="dashboard-tab-tracking"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i><span><?= htmlspecialchars(t('dashboard_tab_tracking'), ENT_QUOTES, 'UTF-8') ?></span></button>
                    <?php if (!$isWorkerDashboard): ?>
                    <button type="button" class="dashboard-page-tab sct-page-tab" role="tab" aria-selected="false" data-dashboard-tab="compliance" aria-controls="dashboard-tab-compliance"><i class="bi bi-clipboard2-check" aria-hidden="true"></i><span><?= htmlspecialchars(t('dashboard_tab_compliance'), ENT_QUOTES, 'UTF-8') ?></span></button>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>

            <div id="dashAlert" class="alert d-none" role="alert" aria-live="polite"></div>
            <div id="dashStatus" class="dashboard-loading-card" aria-live="polite">
                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <span><?= htmlspecialchars($isGlobalAdmin ? t('dashboard_select_company') : t('dashboard_loading'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <div id="dashContent" class="d-none">
                <section class="dashboard-tab-panel is-active" id="dashboard-tab-summary" role="tabpanel" data-dashboard-tab-panel="summary">
                    <section class="dashboard-section dashboard-section--summary dashboard-section--key" aria-labelledby="dashboard-summary-title">
                        <div class="dashboard-section-head dashboard-section-head--with-action">
                            <div class="sct-title-with-help dashboard-heading-block">
                                <h2 id="dashboard-summary-title"><?= htmlspecialchars(t('dashboard_progress_timeline_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                                <?= sctInfoTip(t('dashboard_progress_timeline_intro'), t('info_more_label')) ?>
                            </div>
                            <button type="button"
                                    class="dashboard-panel-access-btn"
                                    data-dashboard-detail-trigger="progress"
                                    aria-label="<?= htmlspecialchars(t('dashboard_graph_more_info'), ENT_QUOTES, 'UTF-8') ?>">
                                <i class="bi bi-info-lg" aria-hidden="true"></i>
                            </button>
                        </div>
                        <article class="dashboard-panel dashboard-panel--tone-ice dashboard-progress-timeline-panel"
                                 data-dashboard-detail="progress"
                                 tabindex="0"
                                 role="button"
                                 aria-label="<?= htmlspecialchars(t('dashboard_graph_more_info') . ': ' . t('dashboard_progress_timeline_title'), ENT_QUOTES, 'UTF-8') ?>">
                            <div id="keyProgressChart" class="dashboard-progress-timeline-wrap" aria-live="polite"></div>
                            <div id="keyProgressMeta" class="dashboard-progress-timeline-meta" aria-live="polite"></div>
                        </article>
                    </section>

                    <section class="dashboard-section dashboard-section--current" aria-labelledby="dashboard-current-state-title">
                        <div class="dashboard-section-head"><div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-current-state-title"><?= htmlspecialchars($isWorkerDashboard ? t('dashboard_worker_current_title') : t('dashboard_current_state_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip($isWorkerDashboard ? t('dashboard_worker_current_intro') : t('dashboard_current_state_intro'), t('info_more_label')) ?></div></div>
                        <div class="dashboard-focus-grid dashboard-focus-grid--priority" aria-label="<?= htmlspecialchars($isWorkerDashboard ? t('dashboard_worker_steps_title') : t('dashboard_attention_title'), ENT_QUOTES, 'UTF-8') ?>">
                        <article class="dashboard-panel dashboard-panel--tone-cyan dashboard-status-panel"
                                 aria-labelledby="dashboard-status-title"
                                 data-dashboard-smart-nav="status"
                                 tabindex="0">
                            <header class="dashboard-panel__head">
                                <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-status-title"><?= htmlspecialchars($isWorkerDashboard ? t('dashboard_worker_progress_title') : t('dashboard_status_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_status_intro'), t('info_more_label')) ?></div>
                            </header>
                            <div id="roleStatusContent"></div>
                        </article>

                        <article class="dashboard-panel dashboard-panel--tone-warm dashboard-attention-panel" aria-labelledby="dashboard-attention-title">
                            <header class="dashboard-panel__head">
                                <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-attention-title"><?= htmlspecialchars($isWorkerDashboard ? t('dashboard_worker_steps_title') : t('dashboard_attention_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip($isWorkerDashboard ? t('dashboard_worker_steps_intro') : t('dashboard_attention_intro'), t('info_more_label')) ?></div>
                            </header>
                            <div id="priorityList" class="dashboard-priority-list"></div>
                        </article>

                        <?php if (!$isWorkerDashboard): ?>
                        <article class="dashboard-panel dashboard-panel--tone-ice dashboard-operations-panel"
                                 aria-labelledby="dashboard-operations-state-title"
                                 data-dashboard-detail="operations"
                                 tabindex="0"
                                 role="button">
                            <header class="dashboard-panel__head">
                                <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-operations-state-title"><?= htmlspecialchars(t('dashboard_operations_state_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_operations_state_intro'), t('info_more_label')) ?></div>
                                <button type="button" class="dashboard-panel-access-btn" data-dashboard-detail-trigger="operations" aria-label="<?= htmlspecialchars(t('dashboard_graph_more_info'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-info-lg" aria-hidden="true"></i></button>
                            </header>
                            <div id="operationsState" class="dashboard-operations-state"></div>
                        </article>
                        <?php endif; ?>
                        </div>
                    </section>

                    <section class="dashboard-section dashboard-section--board" aria-labelledby="dashboard-overview-title">
                        <div class="dashboard-section-head">
                            <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-overview-title"><?= htmlspecialchars($isWorkerDashboard ? t('dashboard_worker_dashboard_summary_title') : t('dashboard_overview_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip($isWorkerDashboard ? t('dashboard_worker_dashboard_summary_intro') : t('dashboard_overview_intro'), t('info_more_label')) ?></div>
                        </div>

                        <div class="dashboard-summary-board<?= $isWorkerDashboard ? ' dashboard-summary-board--worker' : '' ?>">
                            <article class="dashboard-panel dashboard-panel--tone-ice dashboard-summary-board__activity"
                                     aria-labelledby="dashboard-activity-snapshot-title">
                                <header class="dashboard-panel__head">
                                    <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-activity-snapshot-title"><?= htmlspecialchars($isWorkerDashboard ? t('dashboard_worker_trend_title') : t('dashboard_activity_snapshot_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip($isWorkerDashboard ? t('dashboard_worker_trend_intro') : t('dashboard_activity_snapshot_text'), t('info_more_label')) ?></div>
                                </header>
                                <div id="summaryActivity"></div>
                            </article>

                            <?php if (!$isWorkerDashboard): ?>
                            <article class="dashboard-panel dashboard-panel--tone-aqua dashboard-summary-board__steps" aria-labelledby="dashboard-steps-title">
                                <header class="dashboard-panel__head">
                                    <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-steps-title"><?= htmlspecialchars($isWorkerDashboard ? t('dashboard_worker_steps_title') : t('dashboard_steps_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip($isWorkerDashboard ? t('dashboard_worker_steps_intro') : t('dashboard_steps_intro'), t('info_more_label')) ?></div>
                                </header>
                                <div id="summarySteps"></div>
                            </article>
                            <?php endif; ?>

                            <?php if (!$isWorkerDashboard): ?>
                            <div class="dashboard-summary-board__side">
                                <article class="dashboard-panel dashboard-panel--tone-ice" aria-labelledby="dashboard-calendar-card-title">
                                    <header class="dashboard-panel__head">
                                        <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-calendar-card-title"><?= htmlspecialchars(t('dashboard_calendar_card_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_calendar_card_intro'), t('info_more_label')) ?></div>
                                    </header>
                                    <div id="summaryCalendar"></div>
                                </article>

                                <article class="dashboard-panel dashboard-panel--tone-warm" aria-labelledby="dashboard-notifications-card-title">
                                    <header class="dashboard-panel__head">
                                        <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-notifications-card-title"><?= htmlspecialchars(t('dashboard_notifications_card_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_notifications_card_intro'), t('info_more_label')) ?></div>
                                    </header>
                                    <div id="summaryNotifications"></div>
                                </article>
                            </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="dashboard-section d-none" id="personalProgressSection" aria-labelledby="dashboard-personal-title">
                        <div class="dashboard-section-head"><div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-personal-title"><?= htmlspecialchars(t('dashboard_personal_progress_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_personal_progress_intro'), t('info_more_label')) ?></div></div>
                        <div id="personalProgressGrid" class="dashboard-progress-grid"></div>
                    </section>
                </section>

                <section class="dashboard-tab-panel" id="dashboard-tab-tracking" role="tabpanel" data-dashboard-tab-panel="tracking" hidden>
                    <section class="dashboard-section d-none" id="programsSection" aria-labelledby="dashboard-programs-title">
                        <div class="dashboard-section-head dashboard-section-head--with-action">
                            <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-programs-title"><?= htmlspecialchars(t('dashboard_programs_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_programs_intro'), t('info_more_label')) ?></div>
                            
                        </div>
                        <div class="dashboard-program-layout">
                            <div class="dashboard-panel dashboard-panel--tone-cyan dashboard-program-summary" id="programSummary" data-dashboard-nav="programs" tabindex="0" role="link"></div>
                            <div class="dashboard-panel dashboard-panel--tone-ice" data-dashboard-nav="programs" tabindex="0" role="link"><div id="programProgressList" class="dashboard-program-list"></div></div>
                        </div>
                    </section>

                    <section class="dashboard-section" aria-labelledby="dashboard-activity-title">
                        <div class="dashboard-section-head dashboard-section-head--with-action">
                            <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-activity-title"><?= htmlspecialchars(t('dashboard_activity_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_activity_intro'), t('info_more_label')) ?></div>
                            <button type="button" class="dashboard-panel-access-btn" data-dashboard-detail-trigger="trend" aria-label="<?= htmlspecialchars(t('dashboard_graph_more_info'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-info-lg" aria-hidden="true"></i></button>
                        </div>
                        <div class="dashboard-panel dashboard-panel--tone-ice" data-dashboard-detail="trend" tabindex="0" role="button"><?php if (!$isWorkerDashboard): ?><p class="dashboard-chart-explainer"><i class="bi bi-info-circle" aria-hidden="true"></i><span><?= htmlspecialchars(t('dashboard_activity_trend_explainer'), ENT_QUOTES, 'UTF-8') ?></span></p><?php endif; ?><div class="dashboard-trend-wrap" id="trendChart"></div><div class="dashboard-trend-legend" id="trendLegend"></div></div>
                    </section>

                    <section class="dashboard-section d-none" id="rankingSection">
                        <div class="dashboard-section-head"><div class="sct-title-with-help dashboard-heading-block"><h2><?= htmlspecialchars(t('dashboard_ranking_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_ranking_intro'), t('info_more_label')) ?></div></div>
                        <div class="dashboard-panel dashboard-panel--tone-ice"><div class="table-responsive"><table class="table dashboard-ranking-table align-middle mb-0"><thead><tr>
                            <th><?= htmlspecialchars(t('dashboard_ranking_company'), ENT_QUOTES, 'UTF-8') ?></th>
                            <th class="text-end"><?= htmlspecialchars(t('dashboard_ranking_critical_events'), ENT_QUOTES, 'UTF-8') ?></th>
                            <th class="text-end"><?= htmlspecialchars(t('dashboard_ranking_overdue_protocols'), ENT_QUOTES, 'UTF-8') ?></th>
                            <th class="text-end"><?= htmlspecialchars(t('dashboard_ranking_pending_review'), ENT_QUOTES, 'UTF-8') ?></th>
                            <th class="text-end"><?= htmlspecialchars(t('dashboard_ranking_approval_rate'), ENT_QUOTES, 'UTF-8') ?></th>
                        </tr></thead><tbody id="rankingBody"></tbody></table></div></div>
                    </section>
                </section>

                <?php if (!$isWorkerDashboard): ?>
                <section class="dashboard-tab-panel" id="dashboard-tab-compliance" role="tabpanel" data-dashboard-tab-panel="compliance" hidden>
                    <section class="dashboard-section per-module" aria-labelledby="dashboard-events-title">
                        <div class="dashboard-section-head dashboard-section-head--with-action">
                            <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-events-title"><?= htmlspecialchars(t('dashboard_events_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_events_intro'), t('info_more_label')) ?></div>
                            
                        </div>
                        <div class="dashboard-panel-grid dashboard-panel-grid--3">
                            <article class="dashboard-panel dashboard-panel--tone-cyan" data-dashboard-nav="events" tabindex="0" role="link">
                                <header class="dashboard-panel__head"><div class="sct-title-with-help dashboard-heading-block"><h3><?= htmlspecialchars(t('dashboard_event_distribution'), ENT_QUOTES, 'UTF-8') ?></h3><?= sctInfoTip(t('dashboard_event_distribution_intro'), t('info_more_label')) ?></div></header><div id="eventsState"></div>
                            </article>
                            <article class="dashboard-panel dashboard-panel--tone-warm" data-dashboard-nav="events" tabindex="0" role="link">
                                <header class="dashboard-panel__head"><h3><?= htmlspecialchars(t('dashboard_events_criticality'), ENT_QUOTES, 'UTF-8') ?></h3></header><div id="eventsCriticality"></div>
                            </article>
                            <article class="dashboard-panel dashboard-panel--tone-aqua" data-dashboard-nav="events" tabindex="0" role="link">
                                <header class="dashboard-panel__head"><h3><?= htmlspecialchars(t('dashboard_events_types'), ENT_QUOTES, 'UTF-8') ?></h3></header><div id="eventsTypes"></div>
                            </article>
                        </div>
                    </section>

                    <section class="dashboard-section per-module" aria-labelledby="dashboard-evaluations-title">
                        <div class="dashboard-section-head"><div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-evaluations-title"><?= htmlspecialchars(t('dashboard_evaluations_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_evaluations_intro'), t('info_more_label')) ?></div></div>
                        <div class="dashboard-panel-grid dashboard-panel-grid--3">
                            <article class="dashboard-panel dashboard-panel--tone-green" data-dashboard-nav="induction" tabindex="0" role="link">
                                <header class="dashboard-panel__head"><h3><?= htmlspecialchars(t('dashboard_induction'), ENT_QUOTES, 'UTF-8') ?></h3></header>
                                <div id="evalInduction"></div><div id="rateInduction" class="dashboard-rate-line"></div>
                            </article>
                            <article class="dashboard-panel dashboard-panel--tone-cyan" data-dashboard-nav="audits" tabindex="0" role="link">
                                <header class="dashboard-panel__head"><h3><?= htmlspecialchars(t('dashboard_audits'), ENT_QUOTES, 'UTF-8') ?></h3></header>
                                <div id="evalAudits"></div><div id="rateAudits" class="dashboard-rate-line"></div>
                            </article>
                            <article class="dashboard-panel dashboard-panel--tone-ice" data-dashboard-nav="self" tabindex="0" role="link">
                                <header class="dashboard-panel__head"><h3><?= htmlspecialchars(t('dashboard_self_assessments'), ENT_QUOTES, 'UTF-8') ?></h3></header>
                                <div id="evalSelf"></div><div id="rateSelf" class="dashboard-rate-line"></div>
                            </article>
                        </div>
                    </section>

                    <section class="dashboard-section per-module" aria-labelledby="dashboard-protocols-title">
                        <div class="dashboard-section-head dashboard-section-head--with-action">
                            <div class="sct-title-with-help dashboard-heading-block"><h2 id="dashboard-protocols-title"><?= htmlspecialchars(t('dashboard_protocols_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_protocols_intro'), t('info_more_label')) ?></div>
                            
                        </div>
                        <div class="dashboard-panel-grid dashboard-panel-grid--2">
                            <article class="dashboard-panel dashboard-panel--tone-warm" data-dashboard-nav="protocols" tabindex="0" role="link">
                                <div class="dashboard-summary-strip"><div><strong id="protocolVisible">0</strong><span><?= htmlspecialchars(t('dashboard_protocols_visible'), ENT_QUOTES, 'UTF-8') ?></span></div><div><strong id="protocolOverdue">0</strong><span><?= htmlspecialchars(t('dashboard_protocols_overdue'), ENT_QUOTES, 'UTF-8') ?></span></div><div><strong id="trackingOverdue">0</strong><span><?= htmlspecialchars(t('dashboard_tracking_overdue'), ENT_QUOTES, 'UTF-8') ?></span></div></div>
                                <header class="dashboard-panel__head"><h3><?= htmlspecialchars(t('dashboard_protocol_assignments'), ENT_QUOTES, 'UTF-8') ?></h3></header><div id="protocolAssignments"></div>
                            </article>
                            <article class="dashboard-panel dashboard-panel--tone-green" data-dashboard-nav="protocols" tabindex="0" role="link"><header class="dashboard-panel__head"><h3><?= htmlspecialchars(t('dashboard_protocol_results'), ENT_QUOTES, 'UTF-8') ?></h3></header><div id="protocolResults"></div></article>
                        </div>
                    </section>

                    <section class="dashboard-section per-module">
                        <div class="dashboard-panel-grid dashboard-panel-grid--2">
                            <div>
                                <div class="dashboard-section-head dashboard-section-head--compact dashboard-section-head--with-action"><div class="sct-title-with-help dashboard-heading-block"><h2><?= htmlspecialchars(t('dashboard_forms_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_forms_intro'), t('info_more_label')) ?></div></div>
                                <article class="dashboard-panel dashboard-panel--tone-aqua" data-dashboard-nav="forms" tabindex="0" role="link"><div class="dashboard-summary-strip"><div><strong id="formsActive">0</strong><span><?= htmlspecialchars(t('dashboard_forms_active'), ENT_QUOTES, 'UTF-8') ?></span></div><div><strong id="formsSent">0</strong><span><?= htmlspecialchars(t('dashboard_forms_sent'), ENT_QUOTES, 'UTF-8') ?></span></div><div><strong id="formsUsers">0</strong><span><?= htmlspecialchars(t('dashboard_forms_users'), ENT_QUOTES, 'UTF-8') ?></span></div></div><header class="dashboard-panel__head"><h3><?= htmlspecialchars(t('dashboard_forms_top'), ENT_QUOTES, 'UTF-8') ?></h3></header><div id="formsTop"></div></article>
                            </div>
                            <div>
                                <div class="dashboard-section-head dashboard-section-head--compact"><div class="sct-title-with-help dashboard-heading-block"><h2><?= htmlspecialchars(t('dashboard_recent_title'), ENT_QUOTES, 'UTF-8') ?></h2><?= sctInfoTip(t('dashboard_recent_intro'), t('info_more_label')) ?></div></div>
                                <article class="dashboard-panel dashboard-panel--tone-ice"><div id="recentList" class="dashboard-recent-list"></div></article>
                            </div>
                        </div>
                    </section>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<div class="modal fade dashboard-reminder-modal" id="dashboardReminderModal" tabindex="-1" aria-labelledby="dashboardReminderModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="dashboardReminderForm">
                <div class="modal-header">
                    <div>
                        <span class="dashboard-reminder-modal__eyebrow"><i class="bi bi-calendar-plus" aria-hidden="true"></i><?= htmlspecialchars(t('dashboard_calendar_add_note'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2 class="modal-title fs-5" id="dashboardReminderModalTitle"><?= htmlspecialchars(t('dashboard_calendar_note_modal_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(t('common_close'), ENT_QUOTES, 'UTF-8') ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="dashboardReminderType"><?= htmlspecialchars(t('dashboard_calendar_note_type'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select class="form-select" id="dashboardReminderType" required>
                                <option value="note"><?= htmlspecialchars(t('dashboard_calendar_note_type_note'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="reminder"><?= htmlspecialchars(t('dashboard_calendar_note_type_reminder'), ENT_QUOTES, 'UTF-8') ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label" for="dashboardReminderTitle"><?= htmlspecialchars(t('dashboard_calendar_note_title'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input class="form-control" id="dashboardReminderTitle" maxlength="120" required>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label" for="dashboardReminderDate"><?= htmlspecialchars(t('dashboard_calendar_note_date'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input class="form-control" type="date" id="dashboardReminderDate" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="dashboardReminderTime"><?= htmlspecialchars(t('dashboard_calendar_note_time'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input class="form-control" type="time" id="dashboardReminderTime">
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="dashboardReminderPriority"><?= htmlspecialchars(t('dashboard_calendar_note_priority'), ENT_QUOTES, 'UTF-8') ?></label>
                            <div class="dashboard-reminder-priority-field">
                                <select class="form-select" id="dashboardReminderPriority" required>
                                    <option value="low"><?= htmlspecialchars(t('dashboard_calendar_priority_low'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="medium" selected><?= htmlspecialchars(t('dashboard_calendar_priority_medium'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="high"><?= htmlspecialchars(t('dashboard_calendar_priority_high'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="critical"><?= htmlspecialchars(t('dashboard_calendar_priority_critical'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                                <span class="dashboard-reminder-priority-preview is-medium" id="dashboardReminderPriorityPreview" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label" for="dashboardReminderDetails"><?= htmlspecialchars(t('dashboard_calendar_note_details'), ENT_QUOTES, 'UTF-8') ?></label>
                            <textarea class="form-control" id="dashboardReminderDetails" rows="3" maxlength="500"></textarea>
                        </div>
                    </div>
                    <div id="dashboardReminderFeedback" class="dashboard-reminder-feedback d-none" role="status" aria-live="polite"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal"><?= htmlspecialchars(t('dashboard_calendar_note_cancel'), ENT_QUOTES, 'UTF-8') ?></button>
                    <button type="submit" class="btn btn-primary-custom"><i class="bi bi-check2-circle" aria-hidden="true"></i><?= htmlspecialchars(t('dashboard_calendar_note_save'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script id="dashboardI18n" type="application/json"><?= json_encode($dashStrings, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/dashboard.js?v=<?= $assetVersionEscaped ?>-p79"></script>
<script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
<?php
$dashboardFooter = __DIR__ . '/../../partials/app-footer.php';
if (is_file($dashboardFooter)) {
    require $dashboardFooter;
}
?>
</body>
</html>
