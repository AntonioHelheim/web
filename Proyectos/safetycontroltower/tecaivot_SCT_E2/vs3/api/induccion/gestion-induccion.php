<?php
require_once __DIR__ . '/common.php';

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
    'induction_error_response','induction_select_to_view','induction_loading','induction_load_error','induction_empty',
    'common_active','common_inactive','induction_manage_btn','common_edit','induction_confirm_reactivate','induction_confirm_deactivate',
    'induction_deactivate','induction_reactivate','induction_edit_course_title','induction_save_changes',
    'induction_new_course_title','induction_save_course','induction_select_company_first','induction_save_error','induction_saved',
    'induction_detail_load_error','induction_max_score_prefix','induction_no_questions','induction_remove_from_course_title',
    'induction_confirm_remove_question','induction_no_new_results','induction_add_btn','induction_valid_score_required',
    'induction_options_required','induction_create_question_error','induction_question_created','induction_no_materials',
    'induction_delete','induction_confirm_delete_material','induction_add_material_error','induction_no_assignments',
    'induction_status_pending','induction_status_approved','induction_status_failed','induction_due_prefix',
    'induction_select_user_warning','induction_assign_error','induction_assigned_ok','induction_option_placeholder',
    'induction_option_correct','induction_select_user','induction_pts_suffix',
];
$jsStrings = [];
foreach ($jsKeys as $k) $jsStrings[$k] = t($k);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tt('induction_page_title') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= $assetVersionEscaped ?> -->
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>">

    <style>
        .welcome-hero { padding: 4rem 0 2rem; }
        .welcome-topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.25rem 0; border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img { height: 32px; }
        .topbar-actions { display: flex; gap: 0.5rem; align-items: center; }
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
                <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= tt('common_language') ?>">
                    <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= tt('mgmt_back') ?> <i class="bi bi-arrow-left"></i></a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= tt('common_logout') ?> <i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon"><i class="bi bi-mortarboard"></i></div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title"><?= tt('induction_title') ?></h1>
            <p class="section-description intro-description-centered">
                <?= tt('induction_intro') ?> <?= tt('users_session_as') ?> <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3"><?= tt('induction_company_section_title') ?></h2>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label"><?= tt('induction_company_section_title') ?></label>
                        <select id="companySelect" class="form-select">
                            <option value=""><?= tt('induction_select_company') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div id="courseActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

            <?php if ($puedeGestionar): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3" id="courseFormTitle"><?= tt('induction_new_course_title') ?></h2>
                <form id="courseForm" novalidate>
                    <input type="hidden" id="courseFormMode" value="create">
                    <input type="hidden" id="courseFormTarget" value="">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="courseName" class="form-label"><?= tt('induction_course_name') ?></label>
                            <input type="text" id="courseName" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-12 col-md-8">
                            <label for="courseDescription" class="form-label"><?= tt('induction_course_description') ?></label>
                            <input type="text" id="courseDescription" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseAttempts" class="form-label"><?= tt('induction_course_attempts') ?></label>
                            <input type="number" id="courseAttempts" class="form-control" min="1" max="20" value="3" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseApproval" class="form-label"><?= tt('induction_course_approval') ?></label>
                            <input type="number" id="courseApproval" class="form-control" min="1" max="100" value="70" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseFrom" class="form-label"><?= tt('induction_course_from') ?></label>
                            <input type="date" id="courseFrom" class="form-control" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseUntil" class="form-label"><?= tt('induction_course_until') ?></label>
                            <input type="date" id="courseUntil" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-actions mt-3">
                        <button type="submit" id="courseSubmitBtn" class="btn btn-primary-custom btn-sm"><?= tt('induction_save_course') ?></button>
                        <button type="button" id="courseCancelEditBtn" class="btn btn-outline-custom btn-sm d-none"><?= tt('induction_cancel_edit') ?></button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3"><?= tt('induction_courses_title') ?></h2>
                <div id="coursesStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?php echo $isGlobalAdmin ? tt('induction_select_to_view') : tt('induction_loading'); ?>
                </div>
                <div id="coursesTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th><?= tt('induction_course_name') ?></th><th><?= tt('induction_col_approval') ?></th><th><?= tt('induction_col_attempts') ?></th><th><?= tt('induction_col_questions') ?></th><th><?= tt('common_state') ?></th><th><?= tt('common_actions') ?></th>
                            </tr>
                        </thead>
                        <tbody id="coursesTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Detalle de curso: preguntas, materiales, asignaciones -->
            <div id="courseDetail" class="feature-card mt-4 curso-detalle">
                <div class="d-flex justify-content-between align-items-start">
                    <h2 class="h5 mb-0"><?= tt('induction_detail_prefix') ?> <span id="courseDetailName">-</span></h2>
                    <button type="button" id="courseDetailCloseBtn" class="btn btn-outline-custom btn-sm"><?= tt('induction_close') ?></button>
                </div>

                <div id="detailAlert" class="alert d-none mt-3" role="alert" aria-live="polite"></div>

                <!-- Preguntas -->
                <div class="subseccion">
                    <h3 class="h6"><?= tt('induction_questions_title') ?> <span id="courseDetailScore" class="text-muted"></span></h3>
                    <div id="courseQuestionsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <div class="input-group mb-2">
                        <input type="text" id="questionSearchInput" class="form-control" placeholder="<?= tt('induction_search_bank_placeholder') ?>">
                        <button type="button" id="questionSearchBtn" class="btn btn-outline-custom"><?= tt('induction_search') ?></button>
                    </div>
                    <div id="questionSearchResults" class="mb-3"></div>

                    <?php if ($puedeGestionarBanco): ?>
                    <details class="mb-2">
                        <summary class="mb-2" style="cursor:pointer;"><?= tt('induction_new_question_toggle') ?></summary>
                        <form id="newQuestionForm" class="mt-2">
                            <div class="mb-2">
                                <label class="form-label"><?= tt('induction_question_statement') ?></label>
                                <textarea id="newQuestionText" class="form-control" rows="2" required></textarea>
                            </div>
                            <div id="newQuestionOptions">
                                <div class="row g-2 mb-2 option-row">
                                    <div class="col-8"><input type="text" class="form-control option-text" placeholder="<?= tt('induction_option_placeholder') ?> 1"></div>
                                    <div class="col-4 form-check mt-2"><input type="radio" name="correctOption" class="form-check-input option-correct" value="0" checked> <label class="form-check-label"><?= tt('induction_option_correct') ?></label></div>
                                </div>
                                <div class="row g-2 mb-2 option-row">
                                    <div class="col-8"><input type="text" class="form-control option-text" placeholder="<?= tt('induction_option_placeholder') ?> 2"></div>
                                    <div class="col-4 form-check mt-2"><input type="radio" name="correctOption" class="form-check-input option-correct" value="1"> <label class="form-check-label"><?= tt('induction_option_correct') ?></label></div>
                                </div>
                            </div>
                            <button type="button" id="addOptionRowBtn" class="btn btn-outline-custom btn-sm mb-2"><?= tt('induction_add_option') ?></button>
                            <div class="row g-2">
                                <div class="col-6"><label class="form-label"><?= tt('induction_difficulty') ?></label><input type="number" id="newQuestionDifficulty" class="form-control" min="1" max="5" value="1"></div>
                                <div class="col-6"><label class="form-label"><?= tt('induction_points') ?></label><input type="number" id="newQuestionPoints" class="form-control" min="1" value="10"></div>
                            </div>
                            <button type="submit" class="btn btn-primary-custom btn-sm mt-2"><?= tt('induction_create_question') ?></button>
                        </form>
                    </details>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Materiales -->
                <div class="subseccion">
                    <h3 class="h6"><?= tt('induction_materials_title') ?></h3>
                    <div id="courseMaterialsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <form id="materialForm" class="row g-2">
                        <div class="col-12 col-md-4"><input type="text" id="materialTitle" class="form-control" placeholder="<?= tt('induction_material_title_placeholder') ?>" required></div>
                        <div class="col-6 col-md-3">
                            <select id="materialType" class="form-select">
                                <option value="texto"><?= tt('induction_material_type_text') ?></option>
                                <option value="documento"><?= tt('induction_material_type_doc') ?></option>
                                <option value="video"><?= tt('induction_material_type_video') ?></option>
                                <option value="otro"><?= tt('induction_material_type_other') ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-5"><input type="text" id="materialContent" class="form-control" placeholder="<?= tt('induction_material_content_placeholder') ?>"></div>
                        <div class="col-12"><button type="submit" class="btn btn-outline-custom btn-sm"><?= tt('induction_add_material') ?></button></div>
                    </form>
                    <?php endif; ?>
                </div>

                <!-- Asignaciones -->
                <div class="subseccion">
                    <h3 class="h6"><?= tt('induction_assignments_title') ?></h3>
                    <div id="courseAssignmentsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <form id="assignForm" class="row g-2">
                        <div class="col-12 col-md-6">
                            <select id="assignUserSelect" class="form-select" required>
                                <option value=""><?= tt('induction_select_user') ?></option>
                            </select>
                        </div>
                        <div class="col-8 col-md-4"><input type="date" id="assignDeadline" class="form-control" required></div>
                        <div class="col-4 col-md-2"><button type="submit" class="btn btn-outline-custom btn-sm w-100"><?= tt('induction_assign') ?></button></div>
                    </form>
                    <?php endif; ?>
                </div>

            </div>

        </section>

    </div>

    <script id="inductionI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/induccion-admin.js?v=<?= $assetVersionEscaped ?>"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
