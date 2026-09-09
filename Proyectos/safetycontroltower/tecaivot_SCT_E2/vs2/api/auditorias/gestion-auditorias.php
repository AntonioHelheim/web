<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'audits.view', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$isGlobalAdmin = auditoriaIsGlobalAdmin($pdo);
$puedeBanco = currentUserHasCapability($pdo, 'questions.manage');
$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');

$jsKeys = [
    'common_active','common_inactive','common_edit',
    'audit_loading','audit_empty','audit_action_manage','audit_action_deactivate','audit_action_reactivate',
    'audit_form_new','audit_form_edit','audit_save','audit_update','audit_created','audit_updated',
    'audit_error_response','audit_error_load','audit_error_detail','audit_error_assignments','audit_select_company_first',
    'audit_max_score','audit_remove_question','audit_question_added','audit_question_created',
    'audit_no_assignments','audit_result','audit_observations','audit_attempts_used',
    'audit_status_pending','audit_status_in_progress','audit_status_completed','audit_status_cancelled',
    'audit_assignment_success','audit_confirm_deactivate','audit_confirm_reactivate','audit_auditor_placeholder'
];
$jsStrings = [];
foreach ($jsKeys as $key) $jsStrings[$key] = t($key);
$langSwitcherStrings = [
    'title' => t('common_confirm_language_title'),
    'text' => t('common_confirm_language_text'),
    'confirm' => t('common_confirm'),
    'cancel' => t('common_cancel'),
    'updated' => t('common_language_updated'),
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('audit_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
    <style>
        .welcome-hero{padding:3rem 0 1.5rem}.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.quick-links{margin:1.5rem 0 3rem}.form-actions{display:flex;gap:.5rem;flex-wrap:wrap}.language-select{min-width:120px}.audit-detail{display:none}.audit-detail.active{display:block}.subsection{border-top:1px solid var(--border);padding-top:1.25rem;margin-top:1.25rem}.question-chip{display:flex;gap:1rem;justify-content:space-between;align-items:center;padding:.75rem 1rem;border:1px solid var(--border);border-radius:var(--radius-md);margin-bottom:.6rem;background:var(--background-soft)}.question-chip .meta{font-size:.78rem;color:var(--text-secondary)}.assignment-card{border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem;margin-bottom:.75rem}.assignment-card.completed{border-left:4px solid #16a34a}.assignment-card.in-progress{border-left:4px solid var(--primary)}.assignment-card.pending{border-left:4px solid #d97706}.score-pill{font-weight:700;color:var(--primary-darkest)}
        @media(max-width:767.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}}
    </style>
</head>
<body>
<div class="container" data-csrf-token="<?= $csrf ?>" data-is-global-admin="<?= $isGlobalAdmin ? '1' : '0' ?>" data-puede-banco="<?= $puedeBanco ? '1' : '0' ?>">
    <div class="welcome-topbar">
        <div class="brand-wrapper"><div class="brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div></div>
        <div class="topbar-actions">
            <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'), ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?><option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
            </select>
            <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
            <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>

    <section class="welcome-hero text-center">
        <div class="welcome-greeting-icon"><i class="bi bi-clipboard2-check"></i></div>
        <span class="section-label">SAFETY CONTROL TOWER</span>
        <h1 class="section-title"><?= htmlspecialchars(t('audit_title'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="section-description intro-description-centered"><?= htmlspecialchars(t('audit_intro'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(t('audit_session_as'), ENT_QUOTES, 'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p>
    </section>

    <section class="quick-links">
        <?php if ($isGlobalAdmin): ?>
        <div class="feature-card mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="companySelect" class="form-label"><?= htmlspecialchars(t('audit_company'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select id="companySelect" class="form-select"><option value=""><?= htmlspecialchars(t('audit_company_placeholder'), ENT_QUOTES, 'UTF-8') ?></option></select>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div id="auditActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

        <div class="feature-card mb-4">
            <h2 class="h5 mb-3" id="auditFormTitle"><?= htmlspecialchars(t('audit_form_new'), ENT_QUOTES, 'UTF-8') ?></h2>
            <form id="auditForm" novalidate>
                <input type="hidden" id="auditFormMode" value="create"><input type="hidden" id="auditFormTarget" value="">
                <div class="row g-3">
                    <div class="col-12 col-md-4"><label for="auditName" class="form-label"><?= htmlspecialchars(t('audit_name'), ENT_QUOTES, 'UTF-8') ?></label><input type="text" id="auditName" class="form-control" maxlength="50" required></div>
                    <div class="col-12 col-md-8"><label for="auditDescription" class="form-label"><?= htmlspecialchars(t('audit_description'), ENT_QUOTES, 'UTF-8') ?></label><input type="text" id="auditDescription" class="form-control" maxlength="255" required></div>
                    <div class="col-6 col-md-3"><label for="auditAttempts" class="form-label"><?= htmlspecialchars(t('audit_attempts'), ENT_QUOTES, 'UTF-8') ?></label><input type="number" id="auditAttempts" class="form-control" min="1" max="20" value="1" required></div>
                    <div class="col-6 col-md-3"><label for="auditThreshold" class="form-label"><?= htmlspecialchars(t('audit_threshold'), ENT_QUOTES, 'UTF-8') ?></label><input type="number" id="auditThreshold" class="form-control" min="1" max="100" value="80" required></div>
                    <div class="col-6 col-md-3"><label for="auditFrom" class="form-label"><?= htmlspecialchars(t('audit_from'), ENT_QUOTES, 'UTF-8') ?></label><input type="date" id="auditFrom" class="form-control" required></div>
                    <div class="col-6 col-md-3"><label for="auditUntil" class="form-label"><?= htmlspecialchars(t('audit_until'), ENT_QUOTES, 'UTF-8') ?></label><input type="date" id="auditUntil" class="form-control" required></div>
                </div>
                <div class="form-actions mt-3"><button type="submit" id="auditSubmitBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('audit_save'), ENT_QUOTES, 'UTF-8') ?></button><button type="button" id="auditCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= htmlspecialchars(t('audit_cancel_edit'), ENT_QUOTES, 'UTF-8') ?></button></div>
            </form>
        </div>

        <div class="feature-card">
            <h2 class="h5 mb-3"><?= htmlspecialchars(t('audit_list_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <div id="auditsStatus" class="alert alert-info mb-0" role="status"><?= htmlspecialchars($isGlobalAdmin ? t('audit_select_company_first') : t('audit_loading'), ENT_QUOTES, 'UTF-8') ?></div>
            <div id="auditsTableWrapper" class="table-responsive mt-3 d-none"><table class="table table-hover align-middle mb-0"><thead><tr><th><?= htmlspecialchars(t('audit_name'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('audit_threshold'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('audit_attempts'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('audit_questions_title'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_state'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars(t('companies_col_actions'), ENT_QUOTES, 'UTF-8') ?></th></tr></thead><tbody id="auditsTableBody"></tbody></table></div>
        </div>

        <div id="auditDetail" class="feature-card mt-4 audit-detail">
            <div class="d-flex justify-content-between align-items-start gap-3"><div><span class="section-label"><?= htmlspecialchars(t('audit_detail_title'), ENT_QUOTES, 'UTF-8') ?></span><h2 class="h5 mb-0" id="auditDetailName">-</h2></div><button type="button" id="auditDetailCloseBtn" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('my_audits_close'), ENT_QUOTES, 'UTF-8') ?></button></div>
            <div id="detailAlert" class="alert d-none mt-3" role="alert" aria-live="polite"></div>

            <div class="subsection">
                <h3 class="h6"><?= htmlspecialchars(t('audit_questions_title'), ENT_QUOTES, 'UTF-8') ?> <span id="auditMaxScore" class="text-muted"></span></h3>
                <div id="auditQuestionsList" class="mb-3"></div>
                <div class="input-group mb-2"><input type="text" id="questionSearchInput" class="form-control" placeholder="<?= htmlspecialchars(t('audit_question_search'), ENT_QUOTES, 'UTF-8') ?>"><button type="button" id="questionSearchBtn" class="btn btn-outline-custom"><?= htmlspecialchars(t('audit_search'), ENT_QUOTES, 'UTF-8') ?></button></div>
                <div id="questionSearchResults" class="mb-3"></div>
                <?php if ($puedeBanco): ?>
                <details class="mb-2"><summary style="cursor:pointer"><?= htmlspecialchars(t('audit_new_question'), ENT_QUOTES, 'UTF-8') ?></summary>
                    <form id="newQuestionForm" class="mt-3">
                        <div class="mb-2"><label class="form-label"><?= htmlspecialchars(t('audit_question_text'), ENT_QUOTES, 'UTF-8') ?></label><textarea id="newQuestionText" class="form-control" rows="2" required></textarea></div>
                        <div id="newQuestionOptions">
                            <div class="row g-2 mb-2 option-row"><div class="col-8"><input type="text" class="form-control option-text" required></div><div class="col-4 form-check mt-2"><input type="radio" name="auditCorrectOption" class="form-check-input option-correct" value="0" checked><label class="form-check-label"><?= htmlspecialchars(t('audit_correct'), ENT_QUOTES, 'UTF-8') ?></label></div></div>
                            <div class="row g-2 mb-2 option-row"><div class="col-8"><input type="text" class="form-control option-text" required></div><div class="col-4 form-check mt-2"><input type="radio" name="auditCorrectOption" class="form-check-input option-correct" value="1"><label class="form-check-label"><?= htmlspecialchars(t('audit_correct'), ENT_QUOTES, 'UTF-8') ?></label></div></div>
                        </div>
                        <button type="button" id="addOptionRowBtn" class="btn btn-outline-custom btn-sm mb-2">+ <?= htmlspecialchars(t('audit_question_text'), ENT_QUOTES, 'UTF-8') ?></button>
                        <div class="row g-2"><div class="col-6"><label class="form-label"><?= htmlspecialchars(t('audit_difficulty'), ENT_QUOTES, 'UTF-8') ?></label><input type="number" id="newQuestionDifficulty" class="form-control" min="1" max="5" value="1"></div><div class="col-6"><label class="form-label"><?= htmlspecialchars(t('audit_points'), ENT_QUOTES, 'UTF-8') ?></label><input type="number" id="newQuestionPoints" class="form-control" min="1" value="10"></div></div>
                        <button type="submit" class="btn btn-primary-custom btn-sm mt-2"><?= htmlspecialchars(t('audit_create_question'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                </details>
                <?php endif; ?>
            </div>

            <div class="subsection">
                <h3 class="h6"><?= htmlspecialchars(t('audit_assignments_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                <div id="auditAssignmentsList" class="mb-3"></div>
                <form id="assignForm" class="row g-2 align-items-end">
                    <div class="col-12 col-md-7"><label for="assignAuditorSelect" class="form-label"><?= htmlspecialchars(t('audit_auditor'), ENT_QUOTES, 'UTF-8') ?></label><select id="assignAuditorSelect" class="form-select" required><option value=""><?= htmlspecialchars(t('audit_auditor_placeholder'), ENT_QUOTES, 'UTF-8') ?></option></select></div>
                    <div class="col-8 col-md-3"><label for="assignDeadline" class="form-label"><?= htmlspecialchars(t('audit_deadline'), ENT_QUOTES, 'UTF-8') ?></label><input type="date" id="assignDeadline" class="form-control" required></div>
                    <div class="col-4 col-md-2"><button type="submit" class="btn btn-outline-custom btn-sm w-100"><?= htmlspecialchars(t('audit_assign'), ENT_QUOTES, 'UTF-8') ?></button></div>
                </form>
            </div>
        </div>
    </section>
</div>
<script id="auditI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>window.SCT_LANG_SWITCHER_I18N = <?= json_encode($langSwitcherStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/auditorias-admin.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
