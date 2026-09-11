<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'self_assessments.view', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$isGlobalAdmin = autoevaluacionIsGlobalAdmin($pdo);
$puedeBanco = currentUserHasCapability($pdo, 'questions.manage');
$csrf = htmlspecialchars((string)$_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string)($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');
$jsKeys = [
    'common_active','common_inactive','common_edit',
    'self_loading','self_empty','self_action_manage','self_action_deactivate','self_action_reactivate',
    'self_form_new','self_form_edit','self_save','self_update','self_error_response','self_error_load',
    'self_error_detail','self_error_assignments','self_select_company_first','self_max_score','self_remove_question',
    'self_question_added','self_question_created','self_no_assignments','self_result','self_attempts_used',
    'self_status_pending','self_status_in_progress','self_status_approved','self_status_failed',
    'self_assignment_success','self_confirm_deactivate','self_confirm_reactivate','self_user_placeholder',
    'self_points','self_difficulty'
];
$jsStrings=[]; foreach($jsKeys as $key)$jsStrings[$key]=t($key);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars(t('self_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
<style>
.welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.form-actions{display:flex;gap:.5rem;flex-wrap:wrap}.language-select{min-width:120px}.self-detail{display:none}.self-detail.active{display:block}.subsection{border-top:1px solid var(--border);padding-top:1.25rem;margin-top:1.25rem}.question-chip{display:flex;gap:1rem;justify-content:space-between;align-items:center;padding:.75rem 1rem;border:1px solid var(--border);border-radius:var(--radius-md);margin-bottom:.6rem;background:var(--background-soft)}.question-chip .meta{font-size:.78rem;color:var(--text-secondary)}.assignment-card{border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem;margin-bottom:.75rem}.assignment-card.approved{border-left:4px solid #16a34a}.assignment-card.failed{border-left:4px solid #dc2626}.assignment-card.in-progress{border-left:4px solid var(--primary)}.assignment-card.pending{border-left:4px solid #d97706}.score-pill{font-weight:700;color:var(--primary-darkest)}
@media(max-width:767.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}}
</style>
</head>
<body>
<div class="container" data-csrf-token="<?= $csrf ?>" data-is-global-admin="<?= $isGlobalAdmin?'1':'0' ?>" data-puede-banco="<?= $puedeBanco?'1':'0' ?>">
<div class="welcome-topbar">
<div class="brand-wrapper"><div class="brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div></div>
<div class="topbar-actions">
<select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'), ENT_QUOTES, 'UTF-8') ?>"><?php foreach(idiomasDisponiblesConNombre() as $code=>$name): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===idiomaActual()?'selected':'' ?>><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select>
<a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
<a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
</div></div>
<section class="welcome-hero text-center"><div class="welcome-greeting-icon"><i class="bi bi-ui-checks-grid"></i></div><span class="section-label">SAFETY CONTROL TOWER</span><h1 class="section-title"><?= htmlspecialchars(t('self_title'),ENT_QUOTES,'UTF-8') ?></h1><p class="section-description intro-description-centered"><?= htmlspecialchars(t('self_intro'),ENT_QUOTES,'UTF-8') ?> <?= htmlspecialchars(t('self_session_as'),ENT_QUOTES,'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p></section>
<section class="quick-links">
<?php if($isGlobalAdmin): ?><div class="feature-card mb-4"><div class="row g-3 align-items-end"><div class="col-12 col-md-6"><label for="companySelect" class="form-label"><?= htmlspecialchars(t('self_company'),ENT_QUOTES,'UTF-8') ?></label><select id="companySelect" class="form-select"><option value=""><?= htmlspecialchars(t('self_company_placeholder'),ENT_QUOTES,'UTF-8') ?></option></select></div></div></div><?php endif; ?>
<div id="selfActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>
<div class="feature-card mb-4"><h2 class="h5 mb-3" id="selfFormTitle"><?= htmlspecialchars(t('self_form_new'),ENT_QUOTES,'UTF-8') ?></h2>
<form id="selfForm" novalidate><input type="hidden" id="selfFormMode" value="create"><input type="hidden" id="selfFormTarget" value="">
<div class="row g-3">
<div class="col-12 col-md-4"><label for="selfName" class="form-label"><?= htmlspecialchars(t('self_name'),ENT_QUOTES,'UTF-8') ?></label><input type="text" id="selfName" class="form-control" maxlength="50" required></div>
<div class="col-12 col-md-8"><label for="selfDescription" class="form-label"><?= htmlspecialchars(t('self_description'),ENT_QUOTES,'UTF-8') ?></label><input type="text" id="selfDescription" class="form-control" maxlength="255" required></div>
<div class="col-6 col-md-3"><label for="selfAttempts" class="form-label"><?= htmlspecialchars(t('self_attempts'),ENT_QUOTES,'UTF-8') ?></label><input type="number" id="selfAttempts" class="form-control" min="1" max="20" value="1" required></div>
<div class="col-6 col-md-3"><label for="selfThreshold" class="form-label"><?= htmlspecialchars(t('self_threshold'),ENT_QUOTES,'UTF-8') ?></label><input type="number" id="selfThreshold" class="form-control" min="1" max="100" value="70" required></div>
<div class="col-6 col-md-3"><label for="selfFrom" class="form-label"><?= htmlspecialchars(t('self_from'),ENT_QUOTES,'UTF-8') ?></label><input type="date" id="selfFrom" class="form-control" required></div>
<div class="col-6 col-md-3"><label for="selfUntil" class="form-label"><?= htmlspecialchars(t('self_until'),ENT_QUOTES,'UTF-8') ?></label><input type="date" id="selfUntil" class="form-control" required></div>
</div><div class="form-actions mt-3"><button type="submit" id="selfSubmitBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('self_save'),ENT_QUOTES,'UTF-8') ?></button><button type="button" id="selfCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= htmlspecialchars(t('self_cancel_edit'),ENT_QUOTES,'UTF-8') ?></button></div></form></div>

<div class="feature-card"><h2 class="h5 mb-3"><?= htmlspecialchars(t('self_list_title'),ENT_QUOTES,'UTF-8') ?></h2><div id="selfStatus" class="alert alert-info mb-0" role="status"><?= htmlspecialchars($isGlobalAdmin?t('self_select_company_first'):t('self_loading'),ENT_QUOTES,'UTF-8') ?></div><div id="selfTableWrapper" class="table-responsive mt-3 d-none"><table class="table table-hover align-middle mb-0"><thead><tr><th><?= htmlspecialchars(t('self_name'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('self_threshold'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('self_attempts'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('self_questions_title'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_state'),ENT_QUOTES,'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_actions'),ENT_QUOTES,'UTF-8') ?></th></tr></thead><tbody id="selfTableBody"></tbody></table></div></div>

<div id="selfDetail" class="feature-card mt-4 self-detail"><div class="d-flex justify-content-between align-items-start gap-3"><div><span class="section-label"><?= htmlspecialchars(t('self_detail_title'),ENT_QUOTES,'UTF-8') ?></span><h2 class="h5 mb-0" id="selfDetailName">-</h2></div><button type="button" id="selfDetailCloseBtn" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('self_close'),ENT_QUOTES,'UTF-8') ?></button></div><div id="detailAlert" class="alert d-none mt-3" role="alert" aria-live="polite"></div>
<div class="subsection"><h3 class="h6"><?= htmlspecialchars(t('self_questions_title'),ENT_QUOTES,'UTF-8') ?> <span id="selfMaxScore" class="text-muted"></span></h3><div id="selfQuestionsList" class="mb-3"></div><div class="input-group mb-2"><input type="text" id="questionSearchInput" class="form-control" placeholder="<?= htmlspecialchars(t('self_question_search'),ENT_QUOTES,'UTF-8') ?>"><button type="button" id="questionSearchBtn" class="btn btn-outline-custom"><?= htmlspecialchars(t('self_search'),ENT_QUOTES,'UTF-8') ?></button></div><div id="questionSearchResults" class="mb-3"></div>
<?php if($puedeBanco): ?><details class="mb-2"><summary style="cursor:pointer"><?= htmlspecialchars(t('self_new_question'),ENT_QUOTES,'UTF-8') ?></summary><form id="newQuestionForm" class="mt-3"><div class="mb-2"><label class="form-label"><?= htmlspecialchars(t('self_question_text'),ENT_QUOTES,'UTF-8') ?></label><textarea id="newQuestionText" class="form-control" rows="2" required></textarea></div><div id="newQuestionOptions"><div class="row g-2 mb-2 option-row"><div class="col-8"><input type="text" class="form-control option-text" required></div><div class="col-4 form-check mt-2"><input type="radio" name="selfCorrectOption" class="form-check-input option-correct" value="0" checked><label class="form-check-label"><?= htmlspecialchars(t('self_correct'),ENT_QUOTES,'UTF-8') ?></label></div></div><div class="row g-2 mb-2 option-row"><div class="col-8"><input type="text" class="form-control option-text" required></div><div class="col-4 form-check mt-2"><input type="radio" name="selfCorrectOption" class="form-check-input option-correct" value="1"><label class="form-check-label"><?= htmlspecialchars(t('self_correct'),ENT_QUOTES,'UTF-8') ?></label></div></div></div><button type="button" id="addOptionRowBtn" class="btn btn-outline-custom btn-sm mb-2">+ <?= htmlspecialchars(t('self_question_text'),ENT_QUOTES,'UTF-8') ?></button><div class="row g-2"><div class="col-6"><label class="form-label"><?= htmlspecialchars(t('self_difficulty'),ENT_QUOTES,'UTF-8') ?></label><input type="number" id="newQuestionDifficulty" class="form-control" min="1" max="5" value="1"></div><div class="col-6"><label class="form-label"><?= htmlspecialchars(t('self_points'),ENT_QUOTES,'UTF-8') ?></label><input type="number" id="newQuestionPoints" class="form-control" min="1" value="10"></div></div><button type="submit" class="btn btn-primary-custom btn-sm mt-2"><?= htmlspecialchars(t('self_create_question'),ENT_QUOTES,'UTF-8') ?></button></form></details><?php endif; ?></div>
<div class="subsection"><h3 class="h6"><?= htmlspecialchars(t('self_assignments_title'),ENT_QUOTES,'UTF-8') ?></h3><div id="selfAssignmentsList" class="mb-3"></div><form id="assignForm" class="row g-2 align-items-end"><div class="col-12 col-md-7"><label for="assignUserSelect" class="form-label"><?= htmlspecialchars(t('self_user'),ENT_QUOTES,'UTF-8') ?></label><select id="assignUserSelect" class="form-select" required><option value=""><?= htmlspecialchars(t('self_user_placeholder'),ENT_QUOTES,'UTF-8') ?></option></select></div><div class="col-8 col-md-3"><label for="assignDeadline" class="form-label"><?= htmlspecialchars(t('self_deadline'),ENT_QUOTES,'UTF-8') ?></label><input type="date" id="assignDeadline" class="form-control" required></div><div class="col-4 col-md-2"><button type="submit" class="btn btn-outline-custom btn-sm w-100"><?= htmlspecialchars(t('self_assign'),ENT_QUOTES,'UTF-8') ?></button></div></form></div>
</div>
</section></div>
<script id="selfI18n" type="application/json"><?= json_encode($jsStrings,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/autoevaluaciones-admin.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
</body></html>
