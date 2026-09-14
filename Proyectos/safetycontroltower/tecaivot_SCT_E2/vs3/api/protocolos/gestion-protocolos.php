<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'protocols.view', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$isGlobalAdmin = protocoloIsGlobalAdmin($pdo);
$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');

$jsStrings = [
    'loading' => t('protocols_loading'),
    'empty' => t('protocols_empty'),
    'select_company' => t('protocols_select_company'),
    'global' => t('protocols_global'),
    'company' => t('protocols_company'),
    'active' => t('common_active'),
    'inactive' => t('common_inactive'),
    'manage' => t('protocols_manage'),
    'edit' => t('common_edit'),
    'view' => t('protocols_view'),
    'deactivate' => t('protocols_deactivate'),
    'reactivate' => t('protocols_reactivate'),
    'locked' => t('protocols_locked'),
    'required' => t('protocols_required'),
    'optional' => t('protocols_optional'),
    'remove' => t('protocols_remove'),
    'no_forms' => t('protocols_no_forms'),
    'no_assignments' => t('protocols_no_assignments'),
    'no_executions' => t('protocols_no_executions'),
    'no_tracking' => t('protocols_no_tracking'),
    'pending_review' => t('protocols_result_pending_review'),
    'conforme' => t('protocols_result_conforme'),
    'observado' => t('protocols_result_observado'),
    'no_conforme' => t('protocols_result_no_conforme'),
    'no_aplica' => t('protocols_result_no_aplica'),
    'overdue' => t('protocols_overdue'),
    'state_activa' => t('protocols_state_active'),
    'state_suspendida' => t('protocols_state_suspended'),
    'state_cerrada' => t('protocols_state_closed'),
    'state_cancelada' => t('protocols_state_cancelled'),
    'tracking_pendiente' => t('protocols_tracking_pending'),
    'tracking_en_curso' => t('protocols_tracking_in_progress'),
    'tracking_completado' => t('protocols_tracking_completed'),
    'tracking_cancelado' => t('protocols_tracking_cancelled'),
    'suspend' => t('protocols_suspend'),
    'activate' => t('protocols_activate'),
    'close_assignment' => t('protocols_close_assignment'),
    'cancel_assignment' => t('protocols_cancel_assignment'),
    'confirm_remove_form' => t('protocols_confirm_remove_form'),
    'confirm_state' => t('protocols_confirm_state'),
    'error_response' => t('protocols_error_response'),
    'created' => t('protocols_created'),
    'linked' => t('protocols_linked'),
    'assigned' => t('protocols_assigned'),
    'reviewed' => t('protocols_reviewed'),
    'tracking_created' => t('protocols_tracking_created'),
    'global_readonly' => t('protocols_global_readonly'),
    'company_wide' => t('protocols_company_wide'),
    'cycle' => t('protocols_cycle'),
    'submitted' => t('protocols_submitted'),
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('protocols_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
<style>
.welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.language-select{min-width:120px}.protocol-detail{display:none}.protocol-detail.active{display:block}.meta-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem}.meta-box{border:1px solid var(--border);border-radius:var(--radius-md);padding:.8rem;background:var(--background-soft)}.meta-box small{display:block;color:var(--text-secondary);font-size:.72rem;text-transform:uppercase;letter-spacing:.05em}.meta-box strong{display:block;margin-top:.2rem;overflow-wrap:anywhere}.scope-badge,.status-pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:999px;padding:.25rem .55rem;font-size:.73rem;font-weight:700;background:rgba(0,163,244,.12);color:var(--primary-dark)}.scope-badge.global{background:rgba(124,58,237,.1);color:#7c3aed}.status-pill.danger{background:rgba(220,38,38,.10);color:#b91c1c}.status-pill.warn{background:rgba(217,119,6,.12);color:#b45309}.status-pill.ok{background:rgba(22,163,74,.10);color:#15803d}.protocol-form-row,.assignment-row,.execution-row,.tracking-row{border:1px solid var(--border);border-radius:var(--radius-md);padding:.9rem 1rem;margin-bottom:.65rem;background:#fff}.subsection{border-top:1px solid var(--border);padding-top:1.25rem;margin-top:1.25rem}.code-box{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.8rem;background:var(--background-soft);border:1px solid var(--border);border-radius:var(--radius-md);padding:.75rem;white-space:pre-wrap;overflow-wrap:anywhere}.review-panel,.assignment-detail{display:none}.review-panel.active,.assignment-detail.active{display:block}.answer-line{padding:.65rem 0;border-bottom:1px solid var(--border)}.answer-line:last-child{border-bottom:0}.answer-label{font-weight:700}.answer-value{white-space:pre-wrap;overflow-wrap:anywhere}
@media(max-width:991.98px){.meta-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:575.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}.meta-grid{grid-template-columns:1fr}.table-responsive{font-size:.88rem}}
</style>
</head>
<body>
<div class="container" data-csrf-token="<?= $csrf ?>" data-is-global-admin="<?= $isGlobalAdmin ? '1' : '0' ?>">
<div class="welcome-topbar">
<div class="brand-wrapper"><div class="brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div></div>
<div class="topbar-actions">
<select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'), ENT_QUOTES, 'UTF-8') ?>"><?php foreach (idiomasDisponiblesConNombre() as $code=>$name): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===idiomaActual()?'selected':'' ?>><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select>
<a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
<a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
</div>
</div>

<section class="welcome-hero text-center">
<div class="welcome-greeting-icon"><i class="bi bi-shield-check"></i></div>
<span class="section-label">SAFETY CONTROL TOWER</span>
<h1 class="section-title"><?= htmlspecialchars(t('protocols_title'),ENT_QUOTES,'UTF-8') ?></h1>
<p class="section-description intro-description-centered"><?= htmlspecialchars(t('protocols_intro'),ENT_QUOTES,'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p>
</section>

<section class="quick-links">
<?php if ($isGlobalAdmin): ?>
<div class="feature-card mb-4">
<div class="row g-3 align-items-end">
<div class="col-12 col-md-6">
<label for="companySelect" class="form-label"><?= htmlspecialchars(t('protocols_scope'),ENT_QUOTES,'UTF-8') ?></label>
<select id="companySelect" class="form-select">
<option value="global"><?= htmlspecialchars(t('protocols_global_catalog'),ENT_QUOTES,'UTF-8') ?></option>
</select>
</div>
<div class="col-12 col-md-6"><p class="text-muted small mb-0"><?= htmlspecialchars(t('protocols_scope_help'),ENT_QUOTES,'UTF-8') ?></p></div>
</div>
</div>
<?php endif; ?>

<div id="protocolAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

<div class="feature-card mb-4">
<div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
<h2 class="h5 mb-0" id="protocolFormTitle"><?= htmlspecialchars(t('protocols_new'),ENT_QUOTES,'UTF-8') ?></h2>
<span class="text-muted small"><?= htmlspecialchars(t('protocols_reference_notice'),ENT_QUOTES,'UTF-8') ?></span>
</div>
<form id="protocolForm" novalidate>
<input type="hidden" id="protocolMode" value="create">
<input type="hidden" id="protocolTarget" value="">
<div class="row g-3">
<div class="col-12 col-md-3"><label class="form-label" for="protocolCode"><?= htmlspecialchars(t('protocols_code'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolCode" class="form-control" maxlength="50" required></div>
<div class="col-12 col-md-6"><label class="form-label" for="protocolName"><?= htmlspecialchars(t('protocols_name'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolName" class="form-control" maxlength="150" required></div>
<div class="col-12 col-md-3"><label class="form-label" for="protocolVersion"><?= htmlspecialchars(t('protocols_version'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolVersion" type="number" min="1" max="9999" value="1" class="form-control" required></div>
<div class="col-12 col-md-4"><label class="form-label" for="protocolAuthority"><?= htmlspecialchars(t('protocols_authority'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolAuthority" class="form-control" maxlength="100" value="Ministerio de Salud de Chile"></div>
<div class="col-12 col-md-8"><label class="form-label" for="protocolReference"><?= htmlspecialchars(t('protocols_normative_reference'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolReference" class="form-control" maxlength="255"></div>
<div class="col-12"><label class="form-label" for="protocolSource"><?= htmlspecialchars(t('protocols_source'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolSource" type="url" class="form-control" maxlength="500" placeholder="https://www.minsal.cl/..."></div>
<div class="col-6 col-md-3"><label class="form-label" for="protocolFrom"><?= htmlspecialchars(t('protocols_effective_from'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolFrom" type="date" class="form-control"></div>
<div class="col-6 col-md-3"><label class="form-label" for="protocolUntil"><?= htmlspecialchars(t('protocols_effective_until'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolUntil" type="date" class="form-control"></div>
<div class="col-12 col-md-6"><label class="form-label" for="protocolDescription"><?= htmlspecialchars(t('protocols_description'),ENT_QUOTES,'UTF-8') ?></label><textarea id="protocolDescription" class="form-control" rows="2"></textarea></div>
<div class="col-12"><label class="form-label" for="protocolParameters"><?= htmlspecialchars(t('protocols_parameters'),ENT_QUOTES,'UTF-8') ?></label><textarea id="protocolParameters" class="form-control font-monospace" rows="4" placeholder='{"rules_mode":"manual"}'></textarea><div class="form-text"><?= htmlspecialchars(t('protocols_parameters_help'),ENT_QUOTES,'UTF-8') ?></div></div>
</div>
<div class="d-flex gap-2 mt-3 flex-wrap"><button id="protocolSubmitBtn" class="btn btn-primary-custom btn-sm" type="submit"><?= htmlspecialchars(t('protocols_save'),ENT_QUOTES,'UTF-8') ?></button><button id="protocolCancelEdit" class="btn btn-outline-custom btn-sm d-none" type="button"><?= htmlspecialchars(t('forms_cancel_edit'),ENT_QUOTES,'UTF-8') ?></button></div>
</form>
</div>

<div class="feature-card">
<h2 class="h5 mb-3"><?= htmlspecialchars(t('protocols_catalog'),ENT_QUOTES,'UTF-8') ?></h2>
<div id="protocolStatus" class="alert alert-info mb-0"><?= htmlspecialchars(t('protocols_loading'),ENT_QUOTES,'UTF-8') ?></div>
<div id="protocolTableWrap" class="table-responsive mt-3 d-none">
<table class="table table-hover align-middle mb-0">
<thead><tr><th><?= htmlspecialchars(t('protocols_code'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('protocols_name'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('protocols_scope'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('protocols_forms'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('protocols_assignments'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_state'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_actions'),ENT_QUOTES,'UTF-8') ?></th></tr></thead>
<tbody id="protocolTableBody"></tbody>
</table>
</div>
</div>

<div id="protocolDetail" class="feature-card mt-4 protocol-detail">
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
<div><span class="section-label"><?= htmlspecialchars(t('protocols_detail'),ENT_QUOTES,'UTF-8') ?></span><h2 class="h5 mb-1" id="detailName">-</h2><div id="detailScope"></div></div>
<button id="detailClose" type="button" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('self_close'),ENT_QUOTES,'UTF-8') ?></button>
</div>
<div id="detailAlert" class="alert d-none mt-3"></div>
<div id="detailLocked" class="alert alert-warning d-none mt-3"><?= htmlspecialchars(t('protocols_locked_help'),ENT_QUOTES,'UTF-8') ?></div>

<div class="meta-grid mt-4">
<div class="meta-box"><small><?= htmlspecialchars(t('protocols_authority'),ENT_QUOTES,'UTF-8') ?></small><strong id="detailAuthority">—</strong></div>
<div class="meta-box"><small><?= htmlspecialchars(t('protocols_normative_reference'),ENT_QUOTES,'UTF-8') ?></small><strong id="detailReference">—</strong></div>
<div class="meta-box"><small><?= htmlspecialchars(t('protocols_version'),ENT_QUOTES,'UTF-8') ?></small><strong id="detailVersion">—</strong></div>
<div class="meta-box"><small><?= htmlspecialchars(t('protocols_source'),ENT_QUOTES,'UTF-8') ?></small><strong id="detailSource">—</strong></div>
</div>
<div class="mt-3"><small class="text-muted"><?= htmlspecialchars(t('protocols_parameters'),ENT_QUOTES,'UTF-8') ?></small><div id="detailParameters" class="code-box">{}</div></div>

<div class="subsection">
<h3 class="h6"><?= htmlspecialchars(t('protocols_forms_title'),ENT_QUOTES,'UTF-8') ?></h3>
<div id="protocolFormsList"></div>
<form id="protocolFormLink" class="row g-2 align-items-end mt-2">
<div class="col-12 col-md-6"><label class="form-label" for="linkFormSelect"><?= htmlspecialchars(t('protocols_form'),ENT_QUOTES,'UTF-8') ?></label><select id="linkFormSelect" class="form-select" required></select></div>
<div class="col-6 col-md-2"><label class="form-label" for="linkOrder"><?= htmlspecialchars(t('forms_field_order'),ENT_QUOTES,'UTF-8') ?></label><input id="linkOrder" type="number" min="0" value="1" class="form-control"></div>
<div class="col-6 col-md-2"><div class="form-check pb-2"><input id="linkRequired" class="form-check-input" type="checkbox" checked><label class="form-check-label" for="linkRequired"><?= htmlspecialchars(t('protocols_required'),ENT_QUOTES,'UTF-8') ?></label></div></div>
<div class="col-12 col-md-2"><button type="submit" class="btn btn-primary-custom btn-sm w-100"><?= htmlspecialchars(t('protocols_link'),ENT_QUOTES,'UTF-8') ?></button></div>
</form>
</div>

<div id="assignmentSection" class="subsection">
<h3 class="h6"><?= htmlspecialchars(t('protocols_assignments'),ENT_QUOTES,'UTF-8') ?></h3>
<form id="assignmentForm" class="row g-3">
<div class="col-12 col-md-3"><label class="form-label" for="assignCenter"><?= htmlspecialchars(t('protocols_center'),ENT_QUOTES,'UTF-8') ?></label><select id="assignCenter" class="form-select"><option value="">—</option></select></div>
<div class="col-12 col-md-3"><label class="form-label" for="assignProject"><?= htmlspecialchars(t('protocols_project'),ENT_QUOTES,'UTF-8') ?></label><select id="assignProject" class="form-select"><option value="">—</option></select></div>
<div class="col-12 col-md-3"><label class="form-label" for="assignWorker"><?= htmlspecialchars(t('protocols_worker'),ENT_QUOTES,'UTF-8') ?></label><select id="assignWorker" class="form-select"><option value="">—</option></select></div>
<div class="col-12 col-md-3"><label class="form-label" for="assignResponsible"><?= htmlspecialchars(t('protocols_responsible'),ENT_QUOTES,'UTF-8') ?></label><select id="assignResponsible" class="form-select" required></select></div>
<div class="col-6 col-md-3"><label class="form-label" for="assignStart"><?= htmlspecialchars(t('protocols_start'),ENT_QUOTES,'UTF-8') ?></label><input id="assignStart" type="datetime-local" class="form-control" required></div>
<div class="col-6 col-md-3"><label class="form-label" for="assignDue"><?= htmlspecialchars(t('protocols_due'),ENT_QUOTES,'UTF-8') ?></label><input id="assignDue" type="datetime-local" class="form-control" required></div>
<div class="col-6 col-md-3"><label class="form-label" for="assignRecurrence"><?= htmlspecialchars(t('protocols_recurrence'),ENT_QUOTES,'UTF-8') ?></label><select id="assignRecurrence" class="form-select"><option value="none"><?= htmlspecialchars(t('protocols_recurrence_none'),ENT_QUOTES,'UTF-8') ?></option><option value="days"><?= htmlspecialchars(t('protocols_days'),ENT_QUOTES,'UTF-8') ?></option><option value="weeks"><?= htmlspecialchars(t('protocols_weeks'),ENT_QUOTES,'UTF-8') ?></option><option value="months"><?= htmlspecialchars(t('protocols_months'),ENT_QUOTES,'UTF-8') ?></option><option value="years"><?= htmlspecialchars(t('protocols_years'),ENT_QUOTES,'UTF-8') ?></option></select></div>
<div class="col-6 col-md-3"><label class="form-label" for="assignInterval"><?= htmlspecialchars(t('protocols_interval'),ENT_QUOTES,'UTF-8') ?></label><input id="assignInterval" type="number" min="1" value="1" class="form-control" disabled></div>
<div class="col-12 col-md-6"><label class="form-label" for="assignOverrides"><?= htmlspecialchars(t('protocols_assignment_parameters'),ENT_QUOTES,'UTF-8') ?></label><textarea id="assignOverrides" class="form-control font-monospace" rows="2" placeholder="{}"></textarea></div>
<div class="col-12 col-md-6"><label class="form-label" for="assignNotes"><?= htmlspecialchars(t('protocols_notes'),ENT_QUOTES,'UTF-8') ?></label><textarea id="assignNotes" class="form-control" rows="2"></textarea></div>
<div class="col-12"><button type="submit" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('protocols_assign'),ENT_QUOTES,'UTF-8') ?></button></div>
</form>
<div id="assignmentsList" class="mt-3"></div>
</div>

<div id="assignmentDetail" class="subsection assignment-detail">
<div class="d-flex justify-content-between align-items-start gap-3"><div><span class="section-label"><?= htmlspecialchars(t('protocols_assignment_detail'),ENT_QUOTES,'UTF-8') ?></span><h3 id="assignmentDetailTitle" class="h6 mb-0">-</h3></div><button id="assignmentDetailClose" type="button" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('self_close'),ENT_QUOTES,'UTF-8') ?></button></div>

<div class="mt-3"><h4 class="h6"><?= htmlspecialchars(t('protocols_executions'),ENT_QUOTES,'UTF-8') ?></h4><div id="executionsList"></div></div>

<div id="executionReview" class="review-panel mt-3 border rounded p-3">
<h4 class="h6"><?= htmlspecialchars(t('protocols_review'),ENT_QUOTES,'UTF-8') ?></h4>
<div id="executionAnswers" class="mb-3"></div>
<form id="reviewForm" class="row g-2">
<input type="hidden" id="reviewExecutionId">
<div class="col-12 col-md-4"><label class="form-label" for="reviewResult"><?= htmlspecialchars(t('protocols_result'),ENT_QUOTES,'UTF-8') ?></label><select id="reviewResult" class="form-select"><option value="conforme"><?= htmlspecialchars(t('protocols_result_conforme'),ENT_QUOTES,'UTF-8') ?></option><option value="observado"><?= htmlspecialchars(t('protocols_result_observado'),ENT_QUOTES,'UTF-8') ?></option><option value="no_conforme"><?= htmlspecialchars(t('protocols_result_no_conforme'),ENT_QUOTES,'UTF-8') ?></option><option value="no_aplica"><?= htmlspecialchars(t('protocols_result_no_aplica'),ENT_QUOTES,'UTF-8') ?></option></select></div>
<div class="col-12 col-md-8"><label class="form-label" for="reviewNotes"><?= htmlspecialchars(t('protocols_review_notes'),ENT_QUOTES,'UTF-8') ?></label><textarea id="reviewNotes" class="form-control" rows="2"></textarea></div>
<div class="col-12"><button type="submit" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('protocols_review_save'),ENT_QUOTES,'UTF-8') ?></button></div>
</form>
</div>

<div class="mt-4"><h4 class="h6"><?= htmlspecialchars(t('protocols_tracking'),ENT_QUOTES,'UTF-8') ?></h4><div id="trackingList"></div>
<form id="trackingForm" class="row g-2 mt-2">
<input type="hidden" id="trackingExecutionId">
<div class="col-12"><label class="form-label" for="trackingDescription"><?= htmlspecialchars(t('protocols_tracking_description'),ENT_QUOTES,'UTF-8') ?></label><textarea id="trackingDescription" class="form-control" rows="2" required></textarea></div>
<div class="col-12 col-md-4"><label class="form-label" for="trackingResponsible"><?= htmlspecialchars(t('protocols_responsible'),ENT_QUOTES,'UTF-8') ?></label><select id="trackingResponsible" class="form-select"><option value="">—</option></select></div>
<div class="col-6 col-md-3"><label class="form-label" for="trackingCommitment"><?= htmlspecialchars(t('protocols_commitment'),ENT_QUOTES,'UTF-8') ?></label><input id="trackingCommitment" type="datetime-local" class="form-control" required></div>
<div class="col-6 col-md-3"><label class="form-label" for="trackingDeadline"><?= htmlspecialchars(t('protocols_due'),ENT_QUOTES,'UTF-8') ?></label><input id="trackingDeadline" type="datetime-local" class="form-control" required></div>
<div class="col-12 col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary-custom btn-sm w-100"><?= htmlspecialchars(t('protocols_tracking_add'),ENT_QUOTES,'UTF-8') ?></button></div>
</form>
</div>
</div>
</div>
</section>
</div>

<script id="protocolsI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/protocolos-admin.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
