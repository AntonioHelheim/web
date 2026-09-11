<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'dashboard.view', '../../acceso-denegado.php');
$accessContext = resolveCurrentUserAccessContext($pdo);
if ($accessContext === null) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

aplicarCabecerasSeguridad();
$isGlobalAdmin = dashboardIsGlobalAdmin($pdo);
$perfil = $accessContext['profile'];
$rolEtiqueta = roleDisplayLabel((string) $accessContext['primary_role']);
$empresaNombre = null;
if (!$isGlobalAdmin && !empty($perfil['id_company'])) {
    $empresa = empresaObtenerPorId($pdo, (int) $perfil['id_company']);
    if ($empresa) $empresaNombre = capitalizarNombre((string) $empresa['razon_social']);
}

$nombreCompleto = trim((string) ($perfil['name'] ?? '') . ' ' . (string) ($perfil['lastname'] ?? ''));
if ($nombreCompleto === '') $nombreCompleto = ucfirst(explode('@', (string) ($_SESSION['user_email'] ?? 'usuario'))[0]);
$nombreCompleto = capitalizarNombre($nombreCompleto);

$iniciales = '';
if (!empty($perfil['name'])) $iniciales .= sctTextSubstr((string) $perfil['name'], 0, 1);
if (!empty($perfil['lastname'])) $iniciales .= sctTextSubstr((string) $perfil['lastname'], 0, 1);
if ($iniciales === '') $iniciales = sctTextSubstr((string) ($_SESSION['user_email'] ?? 'US'), 0, 2);
$iniciales = sctTextUpper($iniciales);
$profilePhoto = (string) ($perfil['profile_photo_path'] ?? '');

$dashKeys = [
    'error_response','loading','select_company','all_projects','all_centers','no_data','scope_note',
    'kpi_companies','kpi_projects','kpi_centers','kpi_workers','kpi_events','kpi_open_critical','kpi_forms','kpi_protocol_overdue','kpi_protocol_review',
    'events_open','events_in_progress','events_closed','crit_low','crit_medium','crit_high','crit_critical',
    'eval_approved','eval_pending','eval_failed','rate_label','rate_empty','overdue_pending',
    'protocol_active','protocol_suspended','protocol_closed','protocol_cancelled','protocol_pending_review','protocol_conforme','protocol_observado','protocol_no_conforme','protocol_no_aplica',
    'recent_event','recent_form','recent_protocol','recent_audit','recent_empty',
    'trend_events','trend_forms','trend_protocols','trend_evaluations',
    'scope_all_companies','scope_note_consolidated','ranking_title','ranking_intro','ranking_company',
    'ranking_critical_events','ranking_overdue_protocols','ranking_pending_review','ranking_approval_rate','ranking_empty'
];
$dashStrings = [];
foreach ($dashKeys as $key) $dashStrings[$key] = t('dashboard_' . $key);
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('dashboard_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>">
    <style>
        body{background:radial-gradient(circle at 10% 0%,rgba(0,163,244,.07),transparent 28rem),var(--background);min-height:100vh}
        .dashboard-shell{width:min(1320px,100%);margin:0 auto;padding:0 24px 64px}
        .dash-topbar{position:sticky;top:0;z-index:30;display:flex;justify-content:space-between;align-items:center;gap:16px;padding:16px 0;background:color-mix(in srgb,var(--background) 88%,transparent);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px)}
        .dash-brand{display:flex;align-items:center;gap:10px;min-width:0}.dash-brand-symbol{width:42px;height:42px;border-radius:12px;background:linear-gradient(145deg,var(--primary-darkest),var(--primary-darker));display:flex;align-items:center;justify-content:center;box-shadow:0 8px 22px rgba(0,64,96,.14);flex-shrink:0}.dash-brand-symbol img{width:28px;height:28px;object-fit:contain}.dash-brand strong{display:block;font-size:15px;color:var(--primary-darkest);line-height:1}.dash-brand small{display:block;margin-top:4px;font-size:10px;letter-spacing:2px;color:var(--primary)}
        .dash-actions{display:flex;align-items:center;gap:8px}.dash-identity{text-align:right;line-height:1.25}.dash-identity strong{display:block;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;color:var(--text)}.dash-identity span{font-size:12px;color:var(--text-secondary)}.dash-avatar,.dash-icon-btn{width:40px;height:40px;flex-shrink:0}.dash-avatar{border-radius:50%;overflow:hidden;background:linear-gradient(135deg,var(--primary),var(--primary-darker));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}.dash-avatar img{width:100%;height:100%;object-fit:cover}.dash-icon-btn{border-radius:50%;border:1px solid var(--border);background:rgba(255,255,255,.92);color:var(--text-secondary);display:flex;align-items:center;justify-content:center;text-decoration:none;transition:.2s}.dash-icon-btn:hover{background:var(--primary);color:#fff;border-color:var(--primary)}.language-select{width:auto;min-width:118px}
        .dash-hero{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:24px;align-items:end;padding:34px 0 18px}.dash-kicker{display:inline-flex;align-items:center;gap:7px;margin-bottom:8px;color:var(--primary-dark);font-size:11px;font-weight:800;letter-spacing:1.3px;text-transform:uppercase}.dash-hero h1{margin:0;color:var(--primary-darkest);font-size:clamp(30px,4vw,44px);font-weight:850;letter-spacing:-1.5px;line-height:1.05}.dash-hero p{max-width:720px;margin:10px 0 0;color:var(--text-secondary);font-size:14px;line-height:1.55}.context-pill{display:inline-flex;align-items:center;gap:8px;max-width:380px;padding:8px 14px 8px 8px;background:#fff;border:1px solid var(--border);border-radius:999px;font-size:12px;color:var(--text-secondary);box-shadow:0 8px 24px rgba(31,45,61,.05)}.context-pill .dot{width:30px;height:30px;border-radius:50%;background:rgba(0,163,244,.12);color:var(--primary-dark);display:flex;align-items:center;justify-content:center}.context-pill strong{color:var(--text)}
        .filter-card,.panel-card,.metric-card{background:rgba(255,255,255,.96);border:1px solid var(--border);box-shadow:0 8px 24px rgba(31,45,61,.035)}.filter-card{border-radius:18px;padding:16px;margin-bottom:16px}.filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr)) auto;gap:12px;align-items:end}.filter-note{margin:10px 0 0;font-size:11.5px;color:var(--text-secondary)}
        .metric-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin:16px 0 26px}.metric-card{border-radius:16px;padding:15px;min-width:0;position:relative;overflow:hidden}.metric-card::after{content:"";position:absolute;right:-30px;bottom:-35px;width:90px;height:90px;border-radius:50%;background:rgba(0,163,244,.05)}.metric-top{display:flex;align-items:center;justify-content:space-between;gap:10px}.metric-icon{width:35px;height:35px;border-radius:10px;background:rgba(0,163,244,.11);color:var(--primary-dark);display:flex;align-items:center;justify-content:center}.metric-value{margin-top:12px;font-size:27px;font-weight:850;color:var(--primary-darkest);line-height:1}.metric-label{margin-top:5px;font-size:11.5px;color:var(--text-secondary);line-height:1.3}.metric-card.attention .metric-icon{background:rgba(220,38,38,.09);color:#b91c1c}.metric-card.warning .metric-icon{background:rgba(217,119,6,.10);color:#b45309}
        .dash-section{margin-top:26px}.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:10px}.section-head h2{margin:0;font-size:18px;font-weight:800;color:var(--primary-darkest)}.section-head p{margin:3px 0 0;font-size:12px;color:var(--text-secondary)}.panel-grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.panel-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.panel-card{border-radius:18px;padding:17px;min-width:0}.panel-card h3{margin:0 0 13px;font-size:14px;font-weight:800;color:var(--text)}
        .bar-row{display:grid;grid-template-columns:minmax(92px,145px) minmax(80px,1fr) 38px;gap:9px;align-items:center;margin-bottom:9px}.bar-label{font-size:11.5px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.bar-track{height:10px;border-radius:999px;background:rgba(0,0,0,.06);overflow:hidden}.bar-fill{display:block;height:100%;border-radius:999px;background:var(--primary);min-width:0}.bar-value{text-align:right;font-size:11.5px;font-weight:800;color:var(--text)}.fill-good{background:#16a34a}.fill-warn{background:#d97706}.fill-bad{background:#dc2626}.fill-neutral{background:#64748b}.fill-info{background:#0284c7}.fill-purple{background:#7c3aed}
        .rate-line{display:flex;justify-content:space-between;gap:10px;margin-top:12px;padding-top:11px;border-top:1px solid var(--border);font-size:11.5px;color:var(--text-secondary)}.rate-line strong{color:var(--text)}
        .trend-wrap{overflow-x:auto}.trend-svg{width:100%;min-width:620px;height:260px;display:block}.trend-legend{display:flex;gap:14px;flex-wrap:wrap;margin-top:8px;font-size:11px;color:var(--text-secondary)}.legend-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:5px}.trend-empty{padding:48px 12px;text-align:center;color:var(--text-secondary);font-size:12px}
        .recent-list{display:grid;gap:8px}.recent-item{display:grid;grid-template-columns:34px minmax(0,1fr) auto;gap:10px;align-items:center;padding:10px;border:1px solid var(--border);border-radius:12px;background:var(--background-soft)}.recent-icon{width:34px;height:34px;border-radius:10px;background:rgba(0,163,244,.10);color:var(--primary-dark);display:flex;align-items:center;justify-content:center}.recent-title{font-size:12px;font-weight:750;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.recent-detail{font-size:10.5px;color:var(--text-secondary);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.recent-date{font-size:10.5px;color:var(--text-secondary);white-space:nowrap}
        .summary-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:12px}.summary-mini{padding:10px;border-radius:12px;background:var(--background-soft);border:1px solid var(--border)}.summary-mini strong{display:block;font-size:18px;color:var(--primary-darkest)}.summary-mini span{font-size:10.5px;color:var(--text-secondary)}
        .ranking-table{font-size:12.5px}.ranking-table th{font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--text-secondary);border-bottom-width:1px}.ranking-table td,.ranking-table th{white-space:nowrap}.ranking-table td:first-child,.ranking-table th:first-child{white-space:normal}.ranking-cell-bad{color:#dc2626;font-weight:800}.ranking-cell-warn{color:#b45309;font-weight:800}
        @media(max-width:1199.98px){.metric-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.filter-grid{grid-template-columns:repeat(2,minmax(0,1fr)) auto}.panel-grid-3{grid-template-columns:1fr 1fr}.panel-grid-3>.panel-card:last-child{grid-column:1/-1}}
        @media(max-width:767.98px){.dashboard-shell{padding:0 13px 40px}.dash-topbar{padding:11px 0}.dash-identity{display:none}.language-select{display:none}.dash-avatar,.dash-icon-btn{width:36px;height:36px}.dash-brand-symbol{width:38px;height:38px}.dash-brand-symbol img{width:25px;height:25px}.dash-hero{grid-template-columns:1fr;padding:24px 0 14px;gap:12px}.dash-hero h1{font-size:31px}.context-pill{width:100%;max-width:none;border-radius:14px}.filter-grid{grid-template-columns:1fr 1fr}.filter-actions{grid-column:1/-1;display:flex}.filter-actions .btn{flex:1}.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.metric-card{padding:13px}.metric-value{font-size:23px}.panel-grid-2,.panel-grid-3{grid-template-columns:1fr}.panel-grid-3>.panel-card:last-child{grid-column:auto}.bar-row{grid-template-columns:100px minmax(70px,1fr) 34px}.recent-item{grid-template-columns:32px minmax(0,1fr)}.recent-date{grid-column:2}.summary-strip{grid-template-columns:1fr 1fr 1fr}}
        @media(max-width:390px){.filter-grid{grid-template-columns:1fr}.metric-grid{grid-template-columns:1fr 1fr}.dash-brand>div:last-child{display:none}.summary-strip{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="dashboard-shell" data-is-global-admin="<?= $isGlobalAdmin ? '1' : '0' ?>">
    <header class="dash-topbar">
        <div class="dash-brand">
            <div class="dash-brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div>
            <div><strong>Safety Control</strong><small>TOWER</small></div>
        </div>
        <div class="dash-actions">
            <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'),ENT_QUOTES,'UTF-8') ?>">
                <?php foreach (idiomasDisponiblesConNombre() as $code=>$name): ?>
                    <option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===idiomaActual()?'selected':'' ?>><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <div class="dash-identity"><strong><?= htmlspecialchars($nombreCompleto,ENT_QUOTES,'UTF-8') ?></strong><span><?= htmlspecialchars($rolEtiqueta,ENT_QUOTES,'UTF-8') ?></span></div>
            <div class="dash-avatar">
                <?php if ($profilePhoto !== ''): ?><img src="../../<?= htmlspecialchars($profilePhoto,ENT_QUOTES,'UTF-8') ?>" alt=""><?php else: ?><?= htmlspecialchars($iniciales,ENT_QUOTES,'UTF-8') ?><?php endif; ?>
            </div>
            <a href="../usuarios/gestiones.php" class="dash-icon-btn" aria-label="<?= htmlspecialchars(t('dashboard_back'),ENT_QUOTES,'UTF-8') ?>" title="<?= htmlspecialchars(t('dashboard_back'),ENT_QUOTES,'UTF-8') ?>"><i class="bi bi-grid"></i></a>
            <a href="../../logout.php" class="dash-icon-btn" aria-label="<?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?>" title="<?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?>"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </header>

    <section class="dash-hero">
        <div>
            <div class="dash-kicker"><i class="bi bi-speedometer2"></i> <?= htmlspecialchars(t('dashboard_kicker'),ENT_QUOTES,'UTF-8') ?></div>
            <h1><?= htmlspecialchars(t('dashboard_title'),ENT_QUOTES,'UTF-8') ?></h1>
            <p><?= htmlspecialchars(t('dashboard_intro'),ENT_QUOTES,'UTF-8') ?></p>
        </div>
        <div class="context-pill"><span class="dot"><i class="bi <?= $isGlobalAdmin?'bi-stars':'bi-building' ?>"></i></span><span><strong><?= htmlspecialchars($rolEtiqueta,ENT_QUOTES,'UTF-8') ?></strong><?php if ($isGlobalAdmin): ?> — <?= htmlspecialchars(t('dashboard_all_companies'),ENT_QUOTES,'UTF-8') ?><?php elseif ($empresaNombre): ?> — <?= htmlspecialchars($empresaNombre,ENT_QUOTES,'UTF-8') ?><?php endif; ?></span></div>
    </section>

    <section class="filter-card" aria-label="<?= htmlspecialchars(t('dashboard_filters'),ENT_QUOTES,'UTF-8') ?>">
        <div class="filter-grid">
            <?php if ($isGlobalAdmin): ?>
            <div><label for="companySelect" class="form-label small"><?= htmlspecialchars(t('dashboard_company'),ENT_QUOTES,'UTF-8') ?></label><select id="companySelect" class="form-select"><option value=""><?= htmlspecialchars(t('dashboard_select_company'),ENT_QUOTES,'UTF-8') ?></option><option value="__all__"><?= htmlspecialchars(t('dashboard_scope_all_companies'),ENT_QUOTES,'UTF-8') ?></option></select></div>
            <?php endif; ?>
            <div><label for="periodFilter" class="form-label small"><?= htmlspecialchars(t('dashboard_period'),ENT_QUOTES,'UTF-8') ?></label><select id="periodFilter" class="form-select"><option value="30"><?= htmlspecialchars(t('dashboard_period_30'),ENT_QUOTES,'UTF-8') ?></option><option value="90" selected><?= htmlspecialchars(t('dashboard_period_90'),ENT_QUOTES,'UTF-8') ?></option><option value="180"><?= htmlspecialchars(t('dashboard_period_180'),ENT_QUOTES,'UTF-8') ?></option><option value="365"><?= htmlspecialchars(t('dashboard_period_365'),ENT_QUOTES,'UTF-8') ?></option><option value="all"><?= htmlspecialchars(t('dashboard_period_all'),ENT_QUOTES,'UTF-8') ?></option></select></div>
            <div id="projectFilterWrap"><label for="projectFilter" class="form-label small"><?= htmlspecialchars(t('dashboard_project'),ENT_QUOTES,'UTF-8') ?></label><select id="projectFilter" class="form-select"><option value=""><?= htmlspecialchars(t('dashboard_all_projects'),ENT_QUOTES,'UTF-8') ?></option></select></div>
            <div id="centerFilterWrap"><label for="centerFilter" class="form-label small"><?= htmlspecialchars(t('dashboard_center'),ENT_QUOTES,'UTF-8') ?></label><select id="centerFilter" class="form-select"><option value=""><?= htmlspecialchars(t('dashboard_all_centers'),ENT_QUOTES,'UTF-8') ?></option></select></div>
            <div class="filter-actions"><button type="button" id="resetFilters" class="btn btn-outline-custom"><i class="bi bi-arrow-counterclockwise"></i> <?= htmlspecialchars(t('dashboard_reset'),ENT_QUOTES,'UTF-8') ?></button></div>
        </div>
        <p class="filter-note" id="filterScopeNote" data-default="<?= htmlspecialchars(t('dashboard_scope_note'),ENT_QUOTES,'UTF-8') ?>" data-consolidated="<?= htmlspecialchars(t('dashboard_scope_note_consolidated'),ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars(t('dashboard_scope_note'),ENT_QUOTES,'UTF-8') ?></p>
    </section>

    <div id="dashAlert" class="alert d-none" role="alert" aria-live="polite"></div>
    <div id="dashStatus" class="alert alert-info"><?= htmlspecialchars($isGlobalAdmin?t('dashboard_select_company'):t('dashboard_loading'),ENT_QUOTES,'UTF-8') ?></div>

    <main id="dashContent" class="d-none">
        <section><div id="metricGrid" class="metric-grid"></div></section>

        <section class="dash-section d-none" id="rankingSection">
            <div class="section-head"><div><h2><?= htmlspecialchars(t('dashboard_ranking_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('dashboard_ranking_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
            <div class="panel-card">
                <div class="table-responsive">
                    <table class="table ranking-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars(t('dashboard_ranking_company'),ENT_QUOTES,'UTF-8') ?></th>
                                <th class="text-end"><?= htmlspecialchars(t('dashboard_ranking_critical_events'),ENT_QUOTES,'UTF-8') ?></th>
                                <th class="text-end"><?= htmlspecialchars(t('dashboard_ranking_overdue_protocols'),ENT_QUOTES,'UTF-8') ?></th>
                                <th class="text-end"><?= htmlspecialchars(t('dashboard_ranking_pending_review'),ENT_QUOTES,'UTF-8') ?></th>
                                <th class="text-end"><?= htmlspecialchars(t('dashboard_ranking_approval_rate'),ENT_QUOTES,'UTF-8') ?></th>
                            </tr>
                        </thead>
                        <tbody id="rankingBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="dash-section">
            <div class="section-head"><div><h2><?= htmlspecialchars(t('dashboard_activity_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('dashboard_activity_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
            <div class="panel-card"><div class="trend-wrap" id="trendChart"></div><div class="trend-legend" id="trendLegend"></div></div>
        </section>

        <section class="dash-section per-module">
            <div class="section-head"><div><h2><?= htmlspecialchars(t('dashboard_events_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('dashboard_events_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
            <div class="panel-grid-3">
                <div class="panel-card"><h3><?= htmlspecialchars(t('dashboard_events_state'),ENT_QUOTES,'UTF-8') ?></h3><div id="eventsState"></div></div>
                <div class="panel-card"><h3><?= htmlspecialchars(t('dashboard_events_criticality'),ENT_QUOTES,'UTF-8') ?></h3><div id="eventsCriticality"></div></div>
                <div class="panel-card"><h3><?= htmlspecialchars(t('dashboard_events_types'),ENT_QUOTES,'UTF-8') ?></h3><div id="eventsTypes"></div></div>
            </div>
        </section>

        <section class="dash-section per-module">
            <div class="section-head"><div><h2><?= htmlspecialchars(t('dashboard_evaluations_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('dashboard_evaluations_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
            <div class="panel-grid-3">
                <div class="panel-card"><h3><?= htmlspecialchars(t('dashboard_induction'),ENT_QUOTES,'UTF-8') ?></h3><div id="evalInduction"></div><div id="rateInduction" class="rate-line"></div></div>
                <div class="panel-card"><h3><?= htmlspecialchars(t('dashboard_audits'),ENT_QUOTES,'UTF-8') ?></h3><div id="evalAudits"></div><div id="rateAudits" class="rate-line"></div></div>
                <div class="panel-card"><h3><?= htmlspecialchars(t('dashboard_self_assessments'),ENT_QUOTES,'UTF-8') ?></h3><div id="evalSelf"></div><div id="rateSelf" class="rate-line"></div></div>
            </div>
        </section>

        <section class="dash-section per-module">
            <div class="section-head"><div><h2><?= htmlspecialchars(t('dashboard_protocols_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('dashboard_protocols_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
            <div class="panel-grid-2">
                <div class="panel-card"><div class="summary-strip"><div class="summary-mini"><strong id="protocolVisible">0</strong><span><?= htmlspecialchars(t('dashboard_protocols_visible'),ENT_QUOTES,'UTF-8') ?></span></div><div class="summary-mini"><strong id="protocolOverdue">0</strong><span><?= htmlspecialchars(t('dashboard_protocols_overdue'),ENT_QUOTES,'UTF-8') ?></span></div><div class="summary-mini"><strong id="trackingOverdue">0</strong><span><?= htmlspecialchars(t('dashboard_tracking_overdue'),ENT_QUOTES,'UTF-8') ?></span></div></div><h3><?= htmlspecialchars(t('dashboard_protocol_assignments'),ENT_QUOTES,'UTF-8') ?></h3><div id="protocolAssignments"></div></div>
                <div class="panel-card"><h3><?= htmlspecialchars(t('dashboard_protocol_results'),ENT_QUOTES,'UTF-8') ?></h3><div id="protocolResults"></div></div>
            </div>
        </section>

        <section class="dash-section per-module">
            <div class="panel-grid-2">
                <div>
                    <div class="section-head"><div><h2><?= htmlspecialchars(t('dashboard_forms_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('dashboard_forms_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
                    <div class="panel-card"><div class="summary-strip"><div class="summary-mini"><strong id="formsActive">0</strong><span><?= htmlspecialchars(t('dashboard_forms_active'),ENT_QUOTES,'UTF-8') ?></span></div><div class="summary-mini"><strong id="formsSent">0</strong><span><?= htmlspecialchars(t('dashboard_forms_sent'),ENT_QUOTES,'UTF-8') ?></span></div><div class="summary-mini"><strong id="formsUsers">0</strong><span><?= htmlspecialchars(t('dashboard_forms_users'),ENT_QUOTES,'UTF-8') ?></span></div></div><h3><?= htmlspecialchars(t('dashboard_forms_top'),ENT_QUOTES,'UTF-8') ?></h3><div id="formsTop"></div></div>
                </div>
                <div>
                    <div class="section-head"><div><h2><?= htmlspecialchars(t('dashboard_recent_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('dashboard_recent_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
                    <div class="panel-card"><div id="recentList" class="recent-list"></div></div>
                </div>
            </div>
        </section>
    </main>
</div>
<script id="dashboardI18n" type="application/json"><?= json_encode($dashStrings, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/dashboard.js?v=<?= $assetVersionEscaped ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
