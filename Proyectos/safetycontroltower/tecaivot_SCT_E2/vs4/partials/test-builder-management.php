<?php
require_once __DIR__ . '/info-tip.php';

/**
 * SCT P75 — Constructor común para actividades evaluables.
 * Reutilizado por Inducción, Auditorías y Autoevaluaciones.
 * El motor de ejecución existente se conserva; este partial sólo normaliza
 * la experiencia de gestión/creación para roles con permisos.
 */
if (!function_exists('sctTestBuilderManagement')) {
    function sctTestBuilderManagement(array $cfg)
    {
        $esc = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
        $json = function ($value) { return htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); };

        $module = isset($cfg['module']) ? (string) $cfg['module'] : 'induction';
        $testType = isset($cfg['test_type']) ? (string) $cfg['test_type'] : 'induccion';
        $title = isset($cfg['title']) ? (string) $cfg['title'] : t('activity_builder_tests_title');
        $catalogTitle = isset($cfg['catalog_title']) ? (string) $cfg['catalog_title'] : $title;
        $createLabel = isset($cfg['create_label']) ? (string) $cfg['create_label'] : t('activity_builder_create_new');
        $createIcon = array_key_exists('create_icon', $cfg) ? trim((string) $cfg['create_icon']) : 'bi-plus-lg';
        $isGlobalAdmin = !empty($cfg['is_global_admin']);
        $canManage = !array_key_exists('can_manage', $cfg) || !empty($cfg['can_manage']);
        $canBank = !empty($cfg['can_bank']);
        $endpoints = isset($cfg['endpoints']) && is_array($cfg['endpoints']) ? $cfg['endpoints'] : [];
        $defaultAttempts = isset($cfg['default_attempts']) ? (int) $cfg['default_attempts'] : 1;
        $defaultApproval = isset($cfg['default_approval']) ? (int) $cfg['default_approval'] : 70;
        $assignmentLabel = isset($cfg['assignment_label']) ? (string) $cfg['assignment_label'] : t('activity_builder_people');
        ?>
        <section class="sct-builder-management"
                 id="sctTestBuilder"
                 data-builder-module="<?= $esc($module) ?>"
                 data-builder-test-type="<?= $esc($testType) ?>"
                 data-builder-global="<?= $isGlobalAdmin ? '1' : '0' ?>"
                 data-builder-can-bank="<?= $canBank ? '1' : '0' ?>"
                 data-builder-default-attempts="<?= (int) $defaultAttempts ?>"
                 data-builder-default-approval="<?= (int) $defaultApproval ?>"
                 data-label-manage="<?= $esc(t('activity_builder_manage')) ?>"
                 data-label-edit="<?= $esc(t('common_edit')) ?>"
                 data-label-deactivate="<?= $esc(t('activity_builder_deactivate')) ?>"
                 data-label-reactivate="<?= $esc(t('activity_builder_reactivate')) ?>"
                 data-builder-endpoints="<?= $json($endpoints) ?>">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card sct-builder-company-card mb-3">
                <div class="sct-builder-section-heading">
                    <div>
                        <span class="section-label"><?= $esc(t('activity_builder_scope')) ?></span>
                        <h2 class="h5 mb-0"><?= $esc(t('activity_builder_company')) ?></h2>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-xl-4">
                        <label for="builderCompanySelect" class="form-label"><?= $esc(t('activity_builder_company')) ?></label>
                        <select id="builderCompanySelect" class="form-select">
                            <option value=""><?= $esc(t('activity_builder_select_company')) ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div id="builderPageAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

            <section class="feature-card sct-builder-catalog" id="builderCatalog">
                <div class="sct-builder-catalog__head">
                    <div>
                        <span class="section-label"><?= $esc(t('activity_builder_current_items')) ?></span>
                        <h2 class="h5 mb-1"><?= $esc($catalogTitle) ?></h2>
                        <p class="sct-builder-muted mb-0"><?= $esc(t('activity_builder_catalog_help')) ?></p>
                    </div>
                    <div class="sct-builder-catalog__actions">
                        <label class="visually-hidden" for="builderStateFilter"><?= $esc(t('activity_builder_filter_state')) ?></label>
                        <select id="builderStateFilter" class="form-select form-select-sm">
                            <option value="active"><?= $esc(t('activity_builder_filter_active')) ?></option>
                            <option value="inactive"><?= $esc(t('activity_builder_filter_inactive')) ?></option>
                            <option value="all"><?= $esc(t('activity_builder_filter_all')) ?></option>
                        </select>
                        <?php if ($canManage): ?>
                        <button type="button" id="builderCreateBtn" class="btn btn-primary-custom">
                            <?php if ($createIcon !== ''): ?><i class="bi <?= $esc($createIcon) ?>" aria-hidden="true"></i><?php endif; ?>
                            <span><?= $esc($createLabel) ?></span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="builderCatalogStatus" class="alert alert-info mb-0 mt-3" role="status" aria-live="polite">
                    <?= $esc($isGlobalAdmin ? t('activity_builder_select_company_first') : t('activity_builder_loading')) ?>
                </div>
                <div id="builderCatalogTableWrap" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0 sct-builder-table">
                        <thead>
                            <tr>
                                <th><?= $esc(t('activity_builder_name')) ?></th>
                                <th><?= $esc(t('activity_builder_description')) ?></th>
                                <th><?= $esc(t('activity_builder_approval_short')) ?></th>
                                <th><?= $esc(t('activity_builder_validity')) ?></th>
                                <th class="text-end"><?= $esc(t('common_actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody id="builderCatalogBody"></tbody>
                    </table>
                </div>
                <div id="builderCatalogCards" class="sct-builder-mobile-list d-none"></div>
            </section>

            <?php if ($canManage): ?>
            <div class="modal fade sct-builder-modal" id="builderWizardModal" tabindex="-1" aria-labelledby="builderWizardTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header sct-builder-modal__header">
                            <div class="min-w-0">
                                <span class="section-label"><?= $esc(t('activity_builder_title')) ?></span>
                                <h2 class="modal-title h4 mb-1" id="builderWizardTitle"><?= $esc($createLabel) ?></h2>
                                <p class="sct-builder-muted mb-0" id="builderWizardSubtitle"><?= $esc(t('activity_builder_wizard_help')) ?></p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $esc(t('common_close')) ?>"></button>
                        </div>

                        <div class="sct-builder-progress-wrap">
                            <div class="sct-builder-progress" id="builderProgress" role="list" aria-label="<?= $esc(t('activity_builder_steps')) ?>">
                                <?php
                                $steps = [
                                    [1, 'activity_builder_step_basic'],
                                    [2, 'activity_builder_step_material'],
                                    [3, 'activity_builder_step_questions'],
                                    [4, 'activity_builder_step_assignment'],
                                ];
                                foreach ($steps as $step): ?>
                                <button type="button" class="sct-builder-progress__step" data-builder-goto="<?= (int) $step[0] ?>" role="listitem" aria-label="<?= $esc(t($step[1])) ?>">
                                    <span class="sct-builder-progress__title"><?= $esc(t($step[1])) ?></span>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="modal-body sct-builder-modal__body">
                            <div id="builderWizardAlert" class="alert d-none" role="alert" aria-live="polite"></div>

                            <!-- PASO 1 -->
                            <section class="sct-builder-step" data-builder-step="1">
                                <div class="sct-builder-step__heading">
                                    <div><h3><?= $esc(t('activity_builder_step_basic')) ?></h3><p><?= $esc(t($module === 'induction' ? 'activity_builder_induction_basic_help' : 'activity_builder_basic_help')) ?></p></div>
                                </div>
                                <form id="builderBasicForm" novalidate>
                                    <input type="hidden" id="builderItemId" value="">
                                    <input type="hidden" id="builderFormMode" value="create">
                                    <?php if ($module === 'induction'): ?>
                                    <!-- P79: la vigencia de Inducción se define una sola vez, en el paso final.
                                         Estos campos permanecen como soporte técnico del contrato API existente. -->
                                    <input type="hidden" id="builderFrom" value="">
                                    <input type="hidden" id="builderUntil" value="">
                                    <?php endif; ?>
                                    <div class="row g-3 sct-builder-basic-grid">
                                        <div class="col-12 col-lg-5 sct-builder-basic-field sct-builder-basic-field--name">
                                            <label for="builderName" class="form-label"><?= $esc(t('activity_builder_name')) ?> *</label>
                                            <input type="text" id="builderName" class="form-control" maxlength="50" required>
                                        </div>
                                        <div class="col-12 col-lg-7 sct-builder-basic-field sct-builder-basic-field--description">
                                            <label for="builderDescription" class="form-label"><?= $esc(t('activity_builder_description')) ?> *</label>
                                            <textarea id="builderDescription" class="form-control" rows="2" maxlength="255" required></textarea>
                                        </div>
                                        <div class="col-6 col-lg-3 sct-builder-basic-field sct-builder-basic-field--attempts">
                                            <label for="builderAttempts" class="form-label"><?= $esc(t('activity_builder_attempts')) ?> *</label>
                                            <input type="number" id="builderAttempts" class="form-control" min="1" max="20" value="<?= $defaultAttempts ?>" required>
                                        </div>
                                        <div class="col-6 col-lg-3 sct-builder-basic-field sct-builder-basic-field--approval">
                                            <label for="builderApproval" class="form-label"><?= $esc(t('activity_builder_approval')) ?> *</label>
                                            <div class="input-group"><input type="number" id="builderApproval" class="form-control" min="0" max="100" value="<?= $defaultApproval ?>" required><span class="input-group-text">%</span></div>
                                        </div>
                                        <?php if ($module !== 'induction'): ?>
                                        <div class="col-6 col-lg-3 sct-builder-basic-field sct-builder-basic-field--from">
                                            <label for="builderFrom" class="form-label"><?= $esc(t('activity_builder_valid_from')) ?> *</label>
                                            <input type="date" id="builderFrom" class="form-control" required>
                                        </div>
                                        <div class="col-6 col-lg-3 sct-builder-basic-field sct-builder-basic-field--until">
                                            <label for="builderUntil" class="form-label"><?= $esc(t('activity_builder_valid_until')) ?> *</label>
                                            <input type="date" id="builderUntil" class="form-control" required>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </section>

                            <!-- PASO 2 -->
                            <section class="sct-builder-step d-none" data-builder-step="2">
                                <div class="sct-builder-step__heading">
                                    <div><h3><?= $esc(t('activity_builder_step_material')) ?></h3><p><?= $esc(t('activity_builder_material_help')) ?></p></div>
                                </div>
                                <div class="sct-builder-upload-zone" id="builderMaterialDropZone">
                                    <input id="builderMaterialFiles" type="file" class="visually-hidden" multiple accept="application/pdf,image/*,video/*,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                    <div class="sct-builder-upload-zone__icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                    <div><strong><?= $esc(t('activity_builder_upload_files')) ?></strong><p><?= $esc(t('activity_builder_upload_files_help')) ?></p></div>
                                    <button type="button" id="builderMaterialChooseBtn" class="btn btn-outline-custom btn-sm"><?= $esc(t('activity_builder_choose_files')) ?></button>
                                </div>
                                <div id="builderMaterialPending" class="sct-builder-file-pending d-none"></div>
                                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mt-3">
                                    <h4 class="h6 mb-0"><?= $esc(t('activity_builder_uploaded_files')) ?></h4>
                                    <button type="button" id="builderMaterialUploadBtn" class="btn btn-primary-custom btn-sm d-none"><i class="bi bi-upload"></i> <?= $esc(t('activity_builder_upload_selected')) ?></button>
                                </div>
                                <div id="builderMaterialsList" class="sct-builder-file-list mt-2"></div>
                            </section>

                            <!-- PASO 3 -->
                            <section class="sct-builder-step d-none" data-builder-step="3">
                                <div class="sct-builder-step__heading">
                                    <div><h3><?= $esc(t('activity_builder_step_questions')) ?></h3><p><?= $esc(t('activity_builder_questions_help')) ?></p></div>
                                </div>
                                <div class="sct-builder-question-layout">
                                    <div class="sct-builder-question-editor">
                                        <ul class="nav nav-pills sct-builder-tabs" role="tablist">
                                            <?php if ($canBank): ?><li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#builderQuestionCreatePane" type="button"><?= $esc(t('activity_builder_create_question')) ?></button></li><?php endif; ?>
                                            <li class="nav-item"><button class="nav-link<?= $canBank ? '' : ' active' ?>" data-bs-toggle="pill" data-bs-target="#builderQuestionBankPane" type="button"><?= $esc(t('activity_builder_question_bank')) ?></button></li>
                                        </ul>
                                        <div class="tab-content mt-3">
                                            <?php if ($canBank): ?>
                                            <div class="tab-pane fade show active" id="builderQuestionCreatePane">
                                                <form id="builderQuestionForm" novalidate>
                                                    <div class="mb-3">
                                                        <label for="builderQuestionText" class="form-label"><?= $esc(t('activity_builder_question')) ?> *</label>
                                                        <textarea id="builderQuestionText" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                    <div class="row g-3 sct-builder-question-fields">
                                                        <div class="col-12 col-md-6">
                                                            <label for="builderQuestionType" class="form-label"><?= $esc(t('activity_builder_answer_type')) ?></label>
                                                            <select id="builderQuestionType" class="form-select">
                                                                <option value="multiple_choice"><?= $esc(t('activity_builder_type_multiple')) ?></option>
                                                                <option value="true_false"><?= $esc(t('activity_builder_type_true_false')) ?></option>
                                                            </select>
                                                            <div class="form-text"><?= $esc(t('activity_builder_eval_type_help')) ?></div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <label for="builderQuestionDifficulty" class="form-label"><?= $esc(t('activity_builder_difficulty')) ?></label>
                                                            <input id="builderQuestionDifficulty" type="number" class="form-control" min="1" max="5" value="1">
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="sct-builder-label-with-help">
                                                                <label for="builderQuestionScore" class="form-label"><?= $esc(t('activity_builder_weight')) ?></label>
                                                                <?= sctInfoTip(t('activity_builder_weights_help'), t('info_more_label')) ?>
                                                            </div>
                                                            <div class="input-group"><input id="builderQuestionScore" type="number" class="form-control" min="1" max="100" value="10" readonly><span class="input-group-text">%</span></div>
                                                        </div>
                                                    </div>
                                                    <div id="builderQuestionOptions" class="sct-builder-options mt-3"></div>
                                                    <button type="button" id="builderAddOptionBtn" class="btn btn-outline-custom btn-sm mt-2"><i class="bi bi-plus"></i> <?= $esc(t('activity_builder_add_option')) ?></button>

                                                    <div class="sct-builder-question-media mt-3">
                                                        <span class="form-label d-block"><?= $esc(t('activity_builder_question_media')) ?></span>
                                                        <div class="sct-builder-media-picker">
                                                            <input id="builderQuestionMedia" type="file" class="visually-hidden" multiple accept="image/*,video/*" aria-describedby="builderQuestionMediaHelp">
                                                            <button type="button" id="builderQuestionMediaChooseBtn" class="btn btn-outline-custom btn-sm sct-builder-media-choose">
                                                                <i class="bi bi-image" aria-hidden="true"></i>
                                                                <span><?= $esc(t('activity_builder_choose_files')) ?></span>
                                                            </button>
                                                        </div>
                                                        <div id="builderQuestionMediaHelp" class="form-text"><?= $esc(t('activity_builder_question_media_help')) ?></div>
                                                        <div id="builderQuestionMediaPreview" class="sct-builder-media-preview mt-2"></div>
                                                    </div>
                                                    <div class="d-flex gap-2 flex-wrap mt-3">
                                                        <button type="submit" id="builderQuestionSubmitBtn" class="btn btn-primary-custom"><i class="bi bi-plus-circle"></i> <span><?= $esc(t('activity_builder_add_question')) ?></span></button>
                                                        <button type="button" id="builderQuestionCancelEditBtn" class="btn btn-outline-custom d-none"><?= $esc(t('common_cancel')) ?></button>
                                                    </div>
                                                </form>
                                            </div>
                                            <?php endif; ?>
                                            <div class="tab-pane fade<?= $canBank ? '' : ' show active' ?>" id="builderQuestionBankPane">
                                                <div class="sct-builder-bank-search">
                                                    <label for="builderQuestionSearch" class="visually-hidden"><?= $esc(t('activity_builder_search_questions')) ?></label>
                                                    <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input id="builderQuestionSearch" type="search" class="form-control" placeholder="<?= $esc(t('activity_builder_search_questions')) ?>"><button id="builderQuestionSearchBtn" type="button" class="btn btn-outline-custom"><?= $esc(t('activity_builder_search')) ?></button></div>
                                                </div>
                                                <div id="builderQuestionBankResults" class="sct-builder-bank-results mt-3"></div>
                                                <button id="builderAddSelectedQuestionsBtn" type="button" class="btn btn-primary-custom mt-3 d-none"><?= $esc(t('activity_builder_add_selected')) ?></button>
                                            </div>
                                        </div>
                                    </div>
                                    <aside class="sct-builder-question-list-panel">
                                        <div class="d-flex justify-content-between gap-2 align-items-center"><div><span class="section-label"><?= $esc(t('activity_builder_course_questions')) ?></span><h4 class="h6 mb-0"><?= $esc(t('activity_builder_added_questions')) ?></h4></div><span id="builderQuestionScoreTotal" class="badge text-bg-light">0%</span></div>
                                        <p class="sct-builder-muted small mt-2 mb-2"><?= $esc(t('activity_builder_reorder_help')) ?></p>
                                        <div class="sct-builder-weight-toolbar" aria-label="<?= $esc(t('activity_builder_weight_distribution')) ?>">
                                            <button type="button" id="builderAutoWeightsBtn" class="btn btn-outline-custom btn-sm"><i class="bi bi-calculator"></i> <?= $esc(t('activity_builder_auto_weights')) ?></button>
                                            <button type="button" id="builderEditWeightsBtn" class="btn btn-outline-custom btn-sm"><i class="bi bi-sliders"></i> <span><?= $esc(t('activity_builder_edit_weights')) ?></span></button>
                                        </div>
                                        <div id="builderWeightMessage" class="sct-builder-weight-message" role="status" aria-live="polite"></div>
                                        <div id="builderQuestionsList" class="sct-builder-question-list"></div>
                                    </aside>
                                </div>
                            </section>

                            <!-- PASO 4 -->
                            <section class="sct-builder-step d-none" data-builder-step="4">
                                <div class="sct-builder-step__heading">
                                    <div><h3><?= $esc(t('activity_builder_step_assignment')) ?></h3><p><?= $esc(t($module === 'induction' ? 'activity_builder_induction_assignment_help' : 'activity_builder_assignment_help')) ?></p></div>
                                </div>
                                <div class="row g-4">
                                    <div class="col-12 col-xl-7">
                                        <div class="sct-builder-assignment-box">
                                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2"><h4 class="h6 mb-0"><?= $esc($assignmentLabel) ?></h4><span id="builderSelectedPeopleCount" class="badge text-bg-light">0</span></div>
                                            <select id="builderUserSelect" class="sct-bulk-picker__source" multiple aria-hidden="true" tabindex="-1"></select>
                                            <?php sctBulkAssignmentPicker('builderUserSelect', ['label' => $assignmentLabel]); ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-xl-5">
                                        <div class="sct-builder-assignment-box h-100">
                                            <h4 class="h6"><?= $esc(t('activity_builder_groups')) ?></h4>
                                            <p class="sct-builder-muted small"><?= $esc(t('activity_builder_groups_help')) ?></p>
                                            <div id="builderGroupsList" class="sct-builder-groups"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <?php if ($module === 'induction'): ?>
                                        <div class="sct-builder-final-validity">
                                            <div class="sct-builder-final-validity__head">
                                                <h4 class="h6 mb-0"><?= $esc(t('activity_builder_validity')) ?></h4>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <label for="builderStartAt" class="form-label"><?= $esc(t('activity_builder_valid_from')) ?> *</label>
                                                    <input id="builderStartAt" type="datetime-local" class="form-control">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label for="builderDeadline" class="form-label"><?= $esc(t('activity_builder_valid_until')) ?> *</label>
                                                    <input id="builderDeadline" type="date" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label for="builderStartAt" class="form-label"><?= $esc(t('activity_builder_access_start')) ?></label>
                                                <input id="builderStartAt" type="datetime-local" class="form-control">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="builderDeadline" class="form-label"><?= $esc(t('activity_builder_deadline')) ?></label>
                                                <input id="builderDeadline" type="date" class="form-control">
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between gap-2 align-items-center flex-wrap"><h4 class="h6 mb-0"><?= $esc(t('activity_builder_current_assignments')) ?></h4><span class="sct-builder-muted small"><?= $esc(t('activity_builder_assignments_help')) ?></span></div>
                                    <div id="builderAssignmentsList" class="sct-builder-assignment-list mt-2"></div>
                                </div>
                            </section>
                        </div>

                        <div class="modal-footer sct-builder-modal__footer">
                            <div class="sct-builder-modal__footer-left">
                                <button type="button" id="builderCancelBtn" class="btn btn-outline-custom" data-bs-dismiss="modal"><?= $esc(t('common_cancel')) ?></button>
                                <button type="button" id="builderSaveDraftBtn" class="btn btn-outline-custom d-none"><i class="bi bi-save"></i> <?= $esc(t('activity_builder_save_draft')) ?></button>
                            </div>
                            <div class="sct-builder-modal__footer-right">
                                <button type="button" id="builderPrevBtn" class="btn btn-outline-custom d-none"><i class="bi bi-arrow-left"></i> <?= $esc(t('activity_builder_previous')) ?></button>
                                <button type="button" id="builderNextBtn" class="btn btn-primary-custom"><?= $esc(t('activity_builder_next')) ?> <i class="bi bi-arrow-right"></i></button>
                                <button type="button" id="builderPublishBtn" class="btn btn-primary-custom d-none"><i class="bi bi-check2-circle"></i> <?= $esc(t('activity_builder_publish')) ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade sct-builder-modal sct-builder-manage-modal" id="builderManageModal" tabindex="-1" aria-labelledby="builderManageTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header sct-builder-modal__header">
                            <div class="min-w-0">
                                <span class="section-label"><?= $esc(t('activity_builder_manage')) ?></span>
                                <h2 id="builderManageTitle" class="modal-title mb-1"><?= $esc(t('activity_builder_manage_readonly_title')) ?></h2>
                                <p class="sct-builder-muted mb-0"><?= $esc(t('activity_builder_manage_readonly_help')) ?></p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $esc(t('common_close')) ?>"></button>
                        </div>
                        <div class="modal-body sct-builder-modal__body">
                            <div id="builderManageAlert" class="alert d-none" role="alert" aria-live="polite"></div>
                            <section class="sct-builder-readonly-section">
                                <h3><?= $esc(t('activity_builder_basic_information')) ?></h3>
                                <div id="builderManageBasic" class="sct-builder-readonly-grid"></div>
                            </section>
                            <section class="sct-builder-readonly-section">
                                <h3><?= $esc(t('activity_builder_support_materials')) ?></h3>
                                <div id="builderManageMaterials" class="sct-builder-readonly-list"></div>
                            </section>
                            <section class="sct-builder-readonly-section">
                                <h3><?= $esc(t('activity_builder_added_questions')) ?></h3>
                                <div id="builderManageQuestions" class="sct-builder-readonly-list"></div>
                            </section>
                            <section class="sct-builder-readonly-section">
                                <div class="sct-builder-readonly-section__head"><h3><?= $esc(t('activity_builder_assigned_people')) ?></h3><span id="builderManageStats" class="badge text-bg-light"></span></div>
                                <div id="builderManageGroups" class="sct-builder-readonly-groups"></div>
                                <div id="builderManageAssignments" class="sct-builder-readonly-list"></div>
                            </section>
                        </div>
                        <div class="modal-footer sct-builder-modal__footer">
                            <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal"><?= $esc(t('common_close')) ?></button>
                            <button type="button" id="builderManageEditBtn" class="btn btn-primary-custom"><i class="bi bi-pencil"></i> <?= $esc(t('activity_builder_edit_activity')) ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>
        <?php
    }
}
