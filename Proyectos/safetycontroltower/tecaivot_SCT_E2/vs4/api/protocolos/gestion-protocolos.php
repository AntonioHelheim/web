<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../partials/activity-builder-flow.php';
require_once __DIR__ . '/../../partials/bulk-assignment-picker.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'protocols.manage', '../../acceso-denegado.php');
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
    'manage_readonly_title' => t('activity_builder_manage_readonly_title'),
    'manage_readonly_help' => t('activity_builder_manage_readonly_help'),
    'basic_information' => t('activity_builder_basic_information'),
    'support_materials' => t('activity_builder_support_materials'),
    'assigned_people' => t('activity_builder_assigned_people'),
    'edit_activity' => t('activity_builder_edit_activity'),
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
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>-p51">
<link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
<link rel="stylesheet" href="../../css/activity-builder.css?v=20260923-p79">
<style>
.welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.language-select{min-width:120px}.protocol-detail{display:none}.protocol-detail.active{display:block}.meta-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem}.meta-box{border:1px solid var(--border);border-radius:var(--radius-md);padding:.8rem;background:var(--background-soft)}.meta-box small{display:block;color:var(--text-secondary);font-size:.72rem;text-transform:uppercase;letter-spacing:.05em}.meta-box strong{display:block;margin-top:.2rem;overflow-wrap:anywhere}.scope-badge,.status-pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:999px;padding:.25rem .55rem;font-size:.73rem;font-weight:700;background:rgba(0,123,197,.12);color:var(--primary-dark)}.scope-badge.global{background:rgba(116,66,153,.1);color:#744299}.status-pill.danger{background:rgba(219,55,53,.10);color:#AA2424}.status-pill.warn{background:rgba(255,140,71,.12);color:#BC5921}.status-pill.ok{background:rgba(0,136,54,.10);color:#006725}.protocol-form-row,.assignment-row,.execution-row,.tracking-row{border:1px solid var(--border);border-radius:var(--radius-md);padding:.9rem 1rem;margin-bottom:.65rem;background:#fff}.subsection{border-top:1px solid var(--border);padding-top:1.25rem;margin-top:1.25rem}.code-box{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.8rem;background:var(--background-soft);border:1px solid var(--border);border-radius:var(--radius-md);padding:.75rem;white-space:pre-wrap;overflow-wrap:anywhere}.review-panel,.assignment-detail{display:none}.review-panel.active,.assignment-detail.active{display:block}.answer-line{padding:.65rem 0;border-bottom:1px solid var(--border)}.answer-line:last-child{border-bottom:0}.answer-label{font-weight:700}.answer-value{white-space:pre-wrap;overflow-wrap:anywhere}
@media(max-width:991.98px){.meta-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:575.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}.meta-grid{grid-template-columns:1fr}.table-responsive{font-size:.88rem}}
</style>
</head>
<body class="sct-module-page sct-management-module-page">
<div class="container sct-main-shell sct-management-shell" data-csrf-token="<?= $csrf ?>" data-is-global-admin="<?= $isGlobalAdmin ? '1' : '0' ?>">
<?php
        $sctNavbarBasePath = '../../';
        $sctNavbarBackHref = '../usuarios/gestiones.php';
        require __DIR__ . '/../../partials/app-navbar.php';
        ?>

        <section class="welcome-hero text-center sct-main-hero sct-builder-hero">
<div class="welcome-greeting-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></div>
<span class="section-label sct-main-pill">SAFETY CONTROL TOWER</span>
<h1 class="section-title sct-main-title"><?= htmlspecialchars(t('protocols_title'),ENT_QUOTES,'UTF-8') ?></h1>
<p class="section-description intro-description-centered sct-main-intro"><?= htmlspecialchars(t('activity_builder_protocol_intro'),ENT_QUOTES,'UTF-8') ?></p>
</section>
<?php $sctModuleMode = 'manage'; require __DIR__ . '/../../partials/module-context.php'; ?>

<section class="quick-links sct-builder-management-shell">
<?php if ($isGlobalAdmin): ?>
<div class="feature-card sct-builder-company-card mb-3"><div class="row g-3 align-items-end"><div class="col-12 col-md-6 col-xl-4"><label for="companySelect" class="form-label"><?= htmlspecialchars(t('protocols_scope'),ENT_QUOTES,'UTF-8') ?></label><select id="companySelect" class="form-select"><option value="global"><?= htmlspecialchars(t('protocols_global_catalog'),ENT_QUOTES,'UTF-8') ?></option></select></div><div class="col-12 col-md-6"><p class="text-muted small mb-0"><?= htmlspecialchars(t('protocols_scope_help'),ENT_QUOTES,'UTF-8') ?></p></div></div></div>
<?php endif; ?>
<div id="protocolAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

<section class="feature-card sct-builder-catalog">
<div class="sct-builder-catalog__head"><div><span class="section-label"><?= htmlspecialchars(t('activity_builder_current_items'),ENT_QUOTES,'UTF-8') ?></span><h2 class="h5 mb-1"><?= htmlspecialchars(t('activity_builder_protocol_catalog'),ENT_QUOTES,'UTF-8') ?></h2><p class="sct-builder-muted mb-0"><?= htmlspecialchars(t('activity_builder_protocol_catalog_help'),ENT_QUOTES,'UTF-8') ?></p></div><div class="sct-builder-catalog__actions"><select id="protocolStateFilter" class="form-select form-select-sm" aria-label="<?= htmlspecialchars(t('activity_builder_filter_state'),ENT_QUOTES,'UTF-8') ?>"><option value="active"><?= htmlspecialchars(t('activity_builder_filter_active'),ENT_QUOTES,'UTF-8') ?></option><option value="inactive"><?= htmlspecialchars(t('activity_builder_filter_inactive'),ENT_QUOTES,'UTF-8') ?></option><option value="all"><?= htmlspecialchars(t('activity_builder_filter_all'),ENT_QUOTES,'UTF-8') ?></option></select><button type="button" id="protocolCreateBtn" class="btn btn-primary-custom"><i class="bi bi-plus-lg"></i> <?= htmlspecialchars(t('activity_builder_create_protocol'),ENT_QUOTES,'UTF-8') ?></button></div></div>
<div id="protocolStatus" class="alert alert-info mb-0 mt-3"><?= htmlspecialchars(t('protocols_loading'),ENT_QUOTES,'UTF-8') ?></div>
<div id="protocolTableWrap" class="table-responsive mt-3 d-none"><table class="table table-hover align-middle mb-0 sct-builder-table"><thead><tr><th><?= htmlspecialchars(t('protocols_code'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('protocols_name'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('protocols_scope'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('protocols_forms'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('protocols_assignments'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_state'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_actions'),ENT_QUOTES,'UTF-8') ?></th></tr></thead><tbody id="protocolTableBody"></tbody></table></div>
<div id="protocolMobileCards" class="sct-builder-mobile-cards"></div>
</section>

<div class="modal fade sct-builder-modal" id="protocolWizardModal" tabindex="-1" aria-labelledby="protocolWizardTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content"><div class="modal-header sct-builder-modal__header"><div><span class="section-label"><?= htmlspecialchars(t('activity_builder_protocol_label'),ENT_QUOTES,'UTF-8') ?></span><h2 class="modal-title h5 mb-0" id="protocolWizardTitle"><?= htmlspecialchars(t('activity_builder_create_protocol'),ENT_QUOTES,'UTF-8') ?></h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(t('common_close'),ENT_QUOTES,'UTF-8') ?>"></button></div>
<div class="sct-builder-progress-wrap"><div class="sct-builder-progress" aria-label="<?= htmlspecialchars(t('activity_builder_progress'),ENT_QUOTES,'UTF-8') ?>">
<?php foreach([[1,'activity_builder_step_basic'],[2,'activity_builder_step_material'],[3,'activity_builder_protocol_forms_step'],[4,'activity_builder_step_assignment']] as $st): ?><button type="button" class="sct-builder-progress__step" data-protocol-step-go="<?= $st[0] ?>"><span class="sct-builder-progress__title"><?= htmlspecialchars(t($st[1]),ENT_QUOTES,'UTF-8') ?></span></button><?php endforeach; ?>
</div></div><div class="modal-body sct-builder-modal__body">
<section class="sct-builder-step" data-protocol-step="1"><div class="sct-builder-step__heading"><div><h3 id="protocolFormTitle"><?= htmlspecialchars(t('protocols_new'),ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars(t('activity_builder_protocol_basic_help'),ENT_QUOTES,'UTF-8') ?></p></div></div><p class="text-muted small mb-3"><?= htmlspecialchars(t('protocols_reference_notice'),ENT_QUOTES,'UTF-8') ?></p><form id="protocolForm" novalidate><input type="hidden" id="protocolMode" value="create"><input type="hidden" id="protocolTarget" value=""><div class="row g-3">
<div class="col-12 col-md-3"><label class="form-label" for="protocolCode"><?= htmlspecialchars(t('protocols_code'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolCode" class="form-control" maxlength="50" required></div><div class="col-12 col-md-6"><label class="form-label" for="protocolName"><?= htmlspecialchars(t('protocols_name'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolName" class="form-control" maxlength="150" required></div><div class="col-12 col-md-3"><label class="form-label" for="protocolVersion"><?= htmlspecialchars(t('protocols_version'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolVersion" type="number" min="1" max="9999" value="1" class="form-control" required></div>
<div class="col-12 col-md-4"><label class="form-label" for="protocolAuthority"><?= htmlspecialchars(t('protocols_authority'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolAuthority" class="form-control" maxlength="100" value="Ministerio de Salud de Chile"></div><div class="col-12 col-md-8"><label class="form-label" for="protocolReference"><?= htmlspecialchars(t('protocols_normative_reference'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolReference" class="form-control" maxlength="255"></div><div class="col-12"><label class="form-label" for="protocolSource"><?= htmlspecialchars(t('protocols_source'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolSource" type="url" class="form-control" maxlength="500" placeholder="https://www.minsal.cl/..."></div>
<div class="col-6 col-md-3"><label class="form-label" for="protocolFrom"><?= htmlspecialchars(t('protocols_effective_from'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolFrom" type="date" class="form-control"></div><div class="col-6 col-md-3"><label class="form-label" for="protocolUntil"><?= htmlspecialchars(t('protocols_effective_until'),ENT_QUOTES,'UTF-8') ?></label><input id="protocolUntil" type="date" class="form-control"></div><div class="col-12 col-md-6"><label class="form-label" for="protocolDescription"><?= htmlspecialchars(t('protocols_description'),ENT_QUOTES,'UTF-8') ?></label><textarea id="protocolDescription" class="form-control" rows="2" required></textarea></div><div class="col-12"><label class="form-label" for="protocolParameters"><?= htmlspecialchars(t('protocols_parameters'),ENT_QUOTES,'UTF-8') ?></label><textarea id="protocolParameters" class="form-control font-monospace" rows="4" placeholder='{"rules_mode":"manual"}'></textarea><div class="form-text"><?= htmlspecialchars(t('protocols_parameters_help'),ENT_QUOTES,'UTF-8') ?></div></div></div><button id="protocolSubmitBtn" class="btn btn-primary-custom mt-3" type="submit"><?= htmlspecialchars(t('activity_builder_next'),ENT_QUOTES,'UTF-8') ?></button><button id="protocolCancelEdit" class="btn btn-outline-custom mt-3 d-none" type="button"><?= htmlspecialchars(t('forms_cancel_edit'),ENT_QUOTES,'UTF-8') ?></button></form></section>

<section class="sct-builder-step d-none" data-protocol-step="2"><div class="sct-builder-step__heading"><div><h3><?= htmlspecialchars(t('activity_builder_step_material'),ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars(t('activity_builder_protocol_material_help'),ENT_QUOTES,'UTF-8') ?></p></div></div><div class="sct-builder-upload-zone" id="protocolMaterialDropZone"><input id="protocolMaterialFiles" type="file" class="visually-hidden" multiple accept="application/pdf,image/*,video/*,.doc,.docx,.xls,.xlsx,.ppt,.pptx"><div class="sct-builder-upload-zone__icon"><i class="bi bi-cloud-arrow-up"></i></div><div><strong><?= htmlspecialchars(t('activity_builder_upload_files'),ENT_QUOTES,'UTF-8') ?></strong><p><?= htmlspecialchars(t('activity_builder_upload_files_help'),ENT_QUOTES,'UTF-8') ?></p></div><button type="button" id="protocolMaterialChoose" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('activity_builder_choose_files'),ENT_QUOTES,'UTF-8') ?></button></div><div id="protocolMaterialPending" class="sct-builder-file-pending d-none"></div><div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mt-3"><h4 class="h6 mb-0"><?= htmlspecialchars(t('activity_builder_uploaded_files'),ENT_QUOTES,'UTF-8') ?></h4><button type="button" id="protocolMaterialUpload" class="btn btn-primary-custom btn-sm d-none"><i class="bi bi-upload"></i> <?= htmlspecialchars(t('activity_builder_upload_selected'),ENT_QUOTES,'UTF-8') ?></button></div><div id="protocolMaterialsList" class="sct-builder-file-list mt-2"></div><button type="button" class="btn btn-primary-custom mt-3" data-protocol-next><?= htmlspecialchars(t('activity_builder_next'),ENT_QUOTES,'UTF-8') ?></button></section>

<section class="sct-builder-step d-none" data-protocol-step="3"><div class="sct-builder-step__heading"><div><h3><?= htmlspecialchars(t('activity_builder_protocol_forms_step'),ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars(t('activity_builder_protocol_forms_help'),ENT_QUOTES,'UTF-8') ?></p></div></div><div id="detailAlert" class="alert d-none"></div><div id="detailLocked" class="alert alert-warning d-none"><?= htmlspecialchars(t('protocols_locked_help'),ENT_QUOTES,'UTF-8') ?></div><div id="protocolFormsList"></div><form id="protocolFormLink" class="row g-2 align-items-end mt-2"><div class="col-12 col-md-6"><label class="form-label" for="linkFormSelect"><?= htmlspecialchars(t('protocols_form'),ENT_QUOTES,'UTF-8') ?></label><select id="linkFormSelect" class="form-select" required></select></div><div class="col-6 col-md-2"><label class="form-label" for="linkOrder"><?= htmlspecialchars(t('forms_field_order'),ENT_QUOTES,'UTF-8') ?></label><input id="linkOrder" type="number" min="0" value="1" class="form-control"></div><div class="col-6 col-md-2"><div class="form-check pb-2"><input id="linkRequired" class="form-check-input" type="checkbox" checked><label class="form-check-label" for="linkRequired"><?= htmlspecialchars(t('protocols_required'),ENT_QUOTES,'UTF-8') ?></label></div></div><div class="col-12 col-md-2"><button type="submit" class="btn btn-primary-custom btn-sm w-100"><?= htmlspecialchars(t('protocols_link'),ENT_QUOTES,'UTF-8') ?></button></div></form><button type="button" class="btn btn-primary-custom mt-3" data-protocol-next><?= htmlspecialchars(t('activity_builder_next'),ENT_QUOTES,'UTF-8') ?></button></section>

<section id="assignmentSection" class="sct-builder-step d-none" data-protocol-step="4"><div class="sct-builder-step__heading"><div><h3><?= htmlspecialchars(t('activity_builder_step_assignment'),ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars(t('activity_builder_protocol_assignment_help'),ENT_QUOTES,'UTF-8') ?></p></div></div><form id="assignmentForm" class="row g-3"><div class="col-12 col-md-3"><label class="form-label" for="assignCenter"><?= htmlspecialchars(t('protocols_center'),ENT_QUOTES,'UTF-8') ?></label><select id="assignCenter" class="form-select"><option value="">—</option></select></div><div class="col-12 col-md-3"><label class="form-label" for="assignProject"><?= htmlspecialchars(t('protocols_project'),ENT_QUOTES,'UTF-8') ?></label><select id="assignProject" class="form-select"><option value="">—</option></select></div><div class="col-12 col-lg-7"><select id="assignWorker" class="sct-bulk-picker__source" multiple aria-hidden="true" tabindex="-1"></select><?php sctBulkAssignmentPicker('assignWorker', ['label' => t('activity_builder_bulk_workers')]); ?></div><div class="col-12 col-lg-5"><div class="sct-builder-assignment-box h-100"><h4 class="h6"><?= htmlspecialchars(t('activity_builder_groups'),ENT_QUOTES,'UTF-8') ?></h4><p class="sct-builder-muted small"><?= htmlspecialchars(t('activity_builder_protocol_groups_help'),ENT_QUOTES,'UTF-8') ?></p><div id="protocolGroupsList" class="sct-builder-groups"></div></div></div><div class="col-12 col-lg-6"><label class="form-label" for="assignResponsible"><?= htmlspecialchars(t('protocols_responsible'),ENT_QUOTES,'UTF-8') ?></label><select id="assignResponsible" class="form-select" required></select></div><div class="col-6 col-md-3"><label class="form-label" for="assignStart"><?= htmlspecialchars(t('protocols_start'),ENT_QUOTES,'UTF-8') ?></label><input id="assignStart" type="datetime-local" class="form-control" required></div><div class="col-6 col-md-3"><label class="form-label" for="assignDue"><?= htmlspecialchars(t('protocols_due'),ENT_QUOTES,'UTF-8') ?></label><input id="assignDue" type="datetime-local" class="form-control" required></div><div class="col-6 col-md-3"><label class="form-label" for="assignRecurrence"><?= htmlspecialchars(t('protocols_recurrence'),ENT_QUOTES,'UTF-8') ?></label><select id="assignRecurrence" class="form-select"><option value="none"><?= htmlspecialchars(t('protocols_recurrence_none'),ENT_QUOTES,'UTF-8') ?></option><option value="days"><?= htmlspecialchars(t('protocols_days'),ENT_QUOTES,'UTF-8') ?></option><option value="weeks"><?= htmlspecialchars(t('protocols_weeks'),ENT_QUOTES,'UTF-8') ?></option><option value="months"><?= htmlspecialchars(t('protocols_months'),ENT_QUOTES,'UTF-8') ?></option><option value="years"><?= htmlspecialchars(t('protocols_years'),ENT_QUOTES,'UTF-8') ?></option></select></div><div class="col-6 col-md-3"><label class="form-label" for="assignInterval"><?= htmlspecialchars(t('protocols_interval'),ENT_QUOTES,'UTF-8') ?></label><input id="assignInterval" type="number" min="1" value="1" class="form-control" disabled></div><div class="col-12 col-md-6"><label class="form-label" for="assignOverrides"><?= htmlspecialchars(t('protocols_assignment_parameters'),ENT_QUOTES,'UTF-8') ?></label><textarea id="assignOverrides" class="form-control font-monospace" rows="2" placeholder="{}"></textarea></div><div class="col-12 col-md-6"><label class="form-label" for="assignNotes"><?= htmlspecialchars(t('protocols_notes'),ENT_QUOTES,'UTF-8') ?></label><textarea id="assignNotes" class="form-control" rows="2"></textarea></div><div class="col-12"><button type="submit" class="btn btn-primary-custom"><?= htmlspecialchars(t('protocols_assign'),ENT_QUOTES,'UTF-8') ?></button></div></form><div id="assignmentsList" class="mt-3"></div></section>
</div><div class="modal-footer sct-builder-modal__footer"><div><button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal"><?= htmlspecialchars(t('common_cancel'),ENT_QUOTES,'UTF-8') ?></button><button type="button" id="protocolSaveDraft" class="btn btn-outline-custom d-none"><i class="bi bi-save"></i> <?= htmlspecialchars(t('activity_builder_save_draft'),ENT_QUOTES,'UTF-8') ?></button></div><div class="d-flex gap-2 flex-wrap"><button type="button" id="protocolWizardPrev" class="btn btn-outline-custom d-none"><i class="bi bi-arrow-left"></i> <?= htmlspecialchars(t('activity_builder_previous'),ENT_QUOTES,'UTF-8') ?></button><button type="button" id="protocolPublish" class="btn btn-primary-custom d-none"><i class="bi bi-check2-circle"></i> <?= htmlspecialchars(t('activity_builder_publish'),ENT_QUOTES,'UTF-8') ?></button></div></div></div></div></div>

<div class="modal fade sct-builder-modal sct-builder-manage-modal" id="protocolManageModal" tabindex="-1" aria-labelledby="protocolManageTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
<div class="modal-header sct-builder-modal__header"><div><span class="section-label"><?= htmlspecialchars(t('protocols_manage'),ENT_QUOTES,'UTF-8') ?></span><h2 id="protocolManageTitle" class="modal-title mb-1"><?= htmlspecialchars(t('activity_builder_manage_readonly_title'),ENT_QUOTES,'UTF-8') ?></h2><p class="sct-builder-muted mb-0"><?= htmlspecialchars(t('activity_builder_manage_readonly_help'),ENT_QUOTES,'UTF-8') ?></p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(t('common_close'),ENT_QUOTES,'UTF-8') ?>"></button></div>
<div class="modal-body sct-builder-modal__body"><div id="protocolManageAlert" class="alert d-none" role="alert"></div>
<section class="sct-builder-readonly-section"><h3><?= htmlspecialchars(t('activity_builder_basic_information'),ENT_QUOTES,'UTF-8') ?></h3><div id="protocolManageBasic" class="sct-builder-readonly-grid"></div></section>
<section class="sct-builder-readonly-section"><h3><?= htmlspecialchars(t('activity_builder_support_materials'),ENT_QUOTES,'UTF-8') ?></h3><div id="protocolManageMaterials" class="sct-builder-readonly-list"></div></section>
<section class="sct-builder-readonly-section"><h3><?= htmlspecialchars(t('activity_builder_protocol_forms_step'),ENT_QUOTES,'UTF-8') ?></h3><div id="protocolManageForms" class="sct-builder-readonly-list"></div></section>
<section class="sct-builder-readonly-section"><div class="sct-builder-readonly-section__head"><h3><?= htmlspecialchars(t('activity_builder_assigned_people'),ENT_QUOTES,'UTF-8') ?></h3><span id="protocolManageStats" class="badge text-bg-light"></span></div><div id="protocolManageAssignments" class="sct-builder-readonly-list"></div></section>
</div><div class="modal-footer sct-builder-modal__footer"><button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal"><?= htmlspecialchars(t('common_close'),ENT_QUOTES,'UTF-8') ?></button><button type="button" id="protocolManageEdit" class="btn btn-primary-custom"><i class="bi bi-pencil"></i> <?= htmlspecialchars(t('activity_builder_edit_activity'),ENT_QUOTES,'UTF-8') ?></button></div>
</div></div></div>

<div id="protocolDetail" class="feature-card mt-4 protocol-detail"><div class="d-flex justify-content-between align-items-start gap-3 flex-wrap"><div><span class="section-label"><?= htmlspecialchars(t('protocols_detail'),ENT_QUOTES,'UTF-8') ?></span><h2 class="h5 mb-1" id="detailName">-</h2><div id="detailScope"></div></div><button id="detailClose" type="button" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('self_close'),ENT_QUOTES,'UTF-8') ?></button></div><div class="meta-grid mt-4"><div class="meta-box"><small><?= htmlspecialchars(t('protocols_authority'),ENT_QUOTES,'UTF-8') ?></small><strong id="detailAuthority">—</strong></div><div class="meta-box"><small><?= htmlspecialchars(t('protocols_normative_reference'),ENT_QUOTES,'UTF-8') ?></small><strong id="detailReference">—</strong></div><div class="meta-box"><small><?= htmlspecialchars(t('protocols_version'),ENT_QUOTES,'UTF-8') ?></small><strong id="detailVersion">—</strong></div><div class="meta-box"><small><?= htmlspecialchars(t('protocols_source'),ENT_QUOTES,'UTF-8') ?></small><strong id="detailSource">—</strong></div></div><div class="mt-3"><small class="text-muted"><?= htmlspecialchars(t('protocols_parameters'),ENT_QUOTES,'UTF-8') ?></small><div id="detailParameters" class="code-box">{}</div></div>
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
<script src="../../js/sct-bulk-assignment.js?v=20260923-p75"></script>
<script src="../../js/activity-builder-ui.js?v=20260923-p75"></script>
<script src="../../js/protocolos-admin.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>-p75"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>

    <?php require __DIR__ . '/../../partials/app-footer.php'; ?>

<script src="../../js/sct-module-ui.js?v=20260923-p73"></script>
</body>
</html>
