<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'induction.view', '../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = currentUserHasCapability($pdo, 'induction.manage');
$puedeGestionarBanco = currentUserHasCapability($pdo, 'questions.manage');
$isGlobalAdmin = induccionIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');

$jsKeys = [
    'common_invalid_server_response', 'common_active', 'common_inactive', 'common_edit',
    'common_deactivate', 'common_reactivate', 'common_delete',
    'induction_select_company_first', 'induction_loading_courses', 'induction_error_load_courses',
    'induction_empty_courses', 'induction_manage_btn', 'induction_course_form_edit', 'induction_save_changes',
    'induction_course_form_new', 'induction_save_course', 'induction_select_company_before_create',
    'induction_error_save_course', 'induction_course_saved', 'induction_confirm_reactivate_course',
    'induction_confirm_deactivate_course', 'induction_error_load_course', 'induction_max_score_prefix',
    'induction_no_questions_yet', 'induction_remove_from_course_title', 'induction_question_removed_confirm',
    'induction_search_no_new_results', 'induction_add_btn', 'induction_score_required',
    'induction_option_placeholder', 'induction_option_all_text_required', 'induction_error_create_question',
    'induction_question_created', 'induction_no_materials_yet', 'induction_confirm_delete_material',
    'induction_error_add_material', 'induction_select_user_placeholder', 'induction_no_assignments_yet',
    'induction_status_pending', 'induction_status_approved', 'induction_status_failed',
    'induction_assignment_due_prefix', 'induction_select_user_required', 'induction_error_assign',
    'induction_assigned_success',
];
$jsStrings = [];
foreach ($jsKeys as $key) {
    $jsStrings[$key] = t($key);
}

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
    <title><?= htmlspecialchars(t('induction_page_title'), ENT_QUOTES, 'UTF-8') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= $assetVersionEscaped ?> -->
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>">

    <style>
        .welcome-hero { padding: 4rem 0 2rem; }
        .welcome-topbar {
            display: flex; justify-content: space-between; align-items: center;
            gap: 1rem; flex-wrap: wrap;
            padding: 1.25rem 0; border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img { height: 32px; }
        .topbar-actions { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .language-select { min-width: 120px; }
        .welcome-greeting-icon { font-size: 2.5rem; color: #16a34a; margin-bottom: 0.75rem; }
        .quick-links { margin-top: 2rem; margin-bottom: 3rem; }
        .form-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .curso-detalle { display: none; }
        .curso-detalle.activo { display: block; }
        .subseccion { border-top: 1px solid var(--border); padding-top: 1.25rem; margin-top: 1.25rem; }
        .chip-pregunta {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.5rem 0.75rem; border-radius: 10px; background: rgba(0,0,0,0.03);
            margin-bottom: 0.5rem; font-size: 0.9rem;
        }
        .chip-pregunta button { border: none; background: none; color: #dc2626; }
        .badge-estado-1 { color: #d97706; }
        .badge-estado-2 { color: #16a34a; }
        .badge-estado-3 { color: #dc2626; }
    </style>
</head>
<body>

    <div class="container" data-csrf-token="<?php echo $csrfTokenEscaped; ?>"
         data-is-global-admin="<?php echo $isGlobalAdmin ? '1' : '0'; ?>"
         data-puede-banco="<?php echo $puedeGestionarBanco ? '1' : '0'; ?>">

        <div class="welcome-topbar">
            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                </div>
            </div>
            <div class="topbar-actions">
                <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= htmlspecialchars(t('common_language'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-arrow-left"></i></a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'), ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon"><i class="bi bi-mortarboard"></i></div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title"><?= htmlspecialchars(t('induction_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="section-description intro-description-centered">
                <?= htmlspecialchars(t('induction_intro'), ENT_QUOTES, 'UTF-8') ?>
                <?= htmlspecialchars(t('companies_session_as'), ENT_QUOTES, 'UTF-8') ?> <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3"><?= htmlspecialchars(t('common_company_section_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label"><?= htmlspecialchars(t('common_company_section_title'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="companySelect" class="form-select">
                            <option value=""><?= htmlspecialchars(t('common_select_company_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div id="courseActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

            <?php if ($puedeGestionar): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3" id="courseFormTitle"><?= htmlspecialchars(t('induction_course_form_new'), ENT_QUOTES, 'UTF-8') ?></h2>
                <form id="courseForm" novalidate>
                    <input type="hidden" id="courseFormMode" value="create">
                    <input type="hidden" id="courseFormTarget" value="">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="courseName" class="form-label"><?= htmlspecialchars(t('common_name'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="courseName" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-12 col-md-8">
                            <label for="courseDescription" class="form-label"><?= htmlspecialchars(t('common_description'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="courseDescription" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseAttempts" class="form-label"><?= htmlspecialchars(t('induction_attempts_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="number" id="courseAttempts" class="form-control" min="1" max="20" value="3" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseApproval" class="form-label"><?= htmlspecialchars(t('induction_approval_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="number" id="courseApproval" class="form-control" min="1" max="100" value="70" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseFrom" class="form-label"><?= htmlspecialchars(t('induction_valid_from'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="date" id="courseFrom" class="form-control" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseUntil" class="form-label"><?= htmlspecialchars(t('induction_valid_until'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="date" id="courseUntil" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-actions mt-3">
                        <button type="submit" id="courseSubmitBtn" class="btn btn-primary-custom btn-sm"><?= htmlspecialchars(t('induction_save_course'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="button" id="courseCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= htmlspecialchars(t('common_cancel_edit'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3"><?= htmlspecialchars(t('induction_courses_list_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div id="coursesStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?= htmlspecialchars($isGlobalAdmin ? t('induction_select_company_first') : t('induction_loading_courses'), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div id="coursesTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars(t('common_name'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars(t('induction_col_approval'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars(t('induction_col_attempts'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars(t('induction_col_questions'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars(t('common_state'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars(t('common_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>
                        </thead>
                        <tbody id="coursesTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Detalle de curso: preguntas, materiales, asignaciones -->
            <div id="courseDetail" class="feature-card mt-4 curso-detalle">
                <div class="d-flex justify-content-between align-items-start">
                    <h2 class="h5 mb-0"><?= htmlspecialchars(t('induction_detail_prefix'), ENT_QUOTES, 'UTF-8') ?> <span id="courseDetailName">-</span></h2>
                    <button type="button" id="courseDetailCloseBtn" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_close'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>

                <div id="detailAlert" class="alert d-none mt-3" role="alert" aria-live="polite"></div>

                <!-- Preguntas -->
                <div class="subseccion">
                    <h3 class="h6"><?= htmlspecialchars(t('induction_questions_of_course'), ENT_QUOTES, 'UTF-8') ?> <span id="courseDetailScore" class="text-muted"></span></h3>
                    <div id="courseQuestionsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <div class="input-group mb-2">
                        <input type="text" id="questionSearchInput" class="form-control" placeholder="<?= htmlspecialchars(t('induction_question_search_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="button" id="questionSearchBtn" class="btn btn-outline-custom"><?= htmlspecialchars(t('induction_search_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                    <div id="questionSearchResults" class="mb-3"></div>

                    <?php if ($puedeGestionarBanco): ?>
                    <details class="mb-2">
                        <summary class="mb-2" style="cursor:pointer;"><?= htmlspecialchars(t('induction_new_question_toggle'), ENT_QUOTES, 'UTF-8') ?></summary>
                        <form id="newQuestionForm" class="mt-2">
                            <div class="mb-2">
                                <label class="form-label"><?= htmlspecialchars(t('induction_question_text_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea id="newQuestionText" class="form-control" rows="2" required></textarea>
                            </div>
                            <div id="newQuestionOptions">
                                <div class="row g-2 mb-2 option-row">
                                    <div class="col-8"><input type="text" class="form-control option-text" placeholder="<?= htmlspecialchars(t('induction_option_placeholder'), ENT_QUOTES, 'UTF-8') ?> 1"></div>
                                    <div class="col-4 form-check mt-2"><input type="radio" name="correctOption" class="form-check-input option-correct" value="0" checked> <label class="form-check-label"><?= htmlspecialchars(t('induction_option_correct_label'), ENT_QUOTES, 'UTF-8') ?></label></div>
                                </div>
                                <div class="row g-2 mb-2 option-row">
                                    <div class="col-8"><input type="text" class="form-control option-text" placeholder="<?= htmlspecialchars(t('induction_option_placeholder'), ENT_QUOTES, 'UTF-8') ?> 2"></div>
                                    <div class="col-4 form-check mt-2"><input type="radio" name="correctOption" class="form-check-input option-correct" value="1"> <label class="form-check-label"><?= htmlspecialchars(t('induction_option_correct_label'), ENT_QUOTES, 'UTF-8') ?></label></div>
                                </div>
                            </div>
                            <button type="button" id="addOptionRowBtn" class="btn btn-outline-custom btn-sm mb-2"><?= htmlspecialchars(t('induction_add_option_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                            <div class="row g-2">
                                <div class="col-6"><label class="form-label"><?= htmlspecialchars(t('induction_difficulty_label'), ENT_QUOTES, 'UTF-8') ?></label><input type="number" id="newQuestionDifficulty" class="form-control" min="1" max="5" value="1"></div>
                                <div class="col-6"><label class="form-label"><?= htmlspecialchars(t('induction_points_label'), ENT_QUOTES, 'UTF-8') ?></label><input type="number" id="newQuestionPoints" class="form-control" min="1" value="10"></div>
                            </div>
                            <button type="submit" class="btn btn-primary-custom btn-sm mt-2"><?= htmlspecialchars(t('induction_create_question_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        </form>
                    </details>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Materiales -->
                <div class="subseccion">
                    <h3 class="h6"><?= htmlspecialchars(t('induction_materials_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <div id="courseMaterialsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <form id="materialForm" class="row g-2">
                        <div class="col-12 col-md-4"><input type="text" id="materialTitle" class="form-control" placeholder="<?= htmlspecialchars(t('induction_material_title_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-6 col-md-3">
                            <select id="materialType" class="form-select">
                                <option value="texto"><?= htmlspecialchars(t('induction_material_type_text'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="documento"><?= htmlspecialchars(t('induction_material_type_document'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="video"><?= htmlspecialchars(t('induction_material_type_video'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="otro"><?= htmlspecialchars(t('induction_material_type_other'), ENT_QUOTES, 'UTF-8') ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-5"><input type="text" id="materialContent" class="form-control" placeholder="<?= htmlspecialchars(t('induction_material_content_placeholder'), ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-12"><button type="submit" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('induction_add_material_btn'), ENT_QUOTES, 'UTF-8') ?></button></div>
                    </form>
                    <?php endif; ?>
                </div>

                <!-- Asignaciones -->
                <div class="subseccion">
                    <h3 class="h6"><?= htmlspecialchars(t('induction_assignments_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <div id="courseAssignmentsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <form id="assignForm" class="row g-2">
                        <div class="col-12 col-md-6">
                            <select id="assignUserSelect" class="form-select" required>
                                <option value=""><?= htmlspecialchars(t('induction_select_user_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                            </select>
                        </div>
                        <div class="col-8 col-md-4"><input type="date" id="assignDeadline" class="form-control" required></div>
                        <div class="col-4 col-md-2"><button type="submit" class="btn btn-outline-custom btn-sm w-100"><?= htmlspecialchars(t('induction_assign_btn'), ENT_QUOTES, 'UTF-8') ?></button></div>
                    </form>
                    <?php endif; ?>
                </div>

            </div>

        </section>

    </div>

    <script id="inductionAdminI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script>window.SCT_LANG_SWITCHER_I18N = <?= json_encode($langSwitcherStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
    <script src="../../js/induccion-admin.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
