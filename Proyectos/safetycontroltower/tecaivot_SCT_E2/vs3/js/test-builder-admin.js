/**
 * Safety Control Tower — P75 constructor común de actividades evaluables.
 * Inducción, Auditoría y Autoevaluación.
 */
(function () {
    'use strict';

    const root = document.getElementById('sctTestBuilder');
    if (!root) return;

    const csrfHost = document.querySelector('[data-csrf-token]');
    const csrf = csrfHost ? (csrfHost.dataset.csrfToken || '') : '';
    const moduleName = root.dataset.builderModule || 'induction';
    const testType = root.dataset.builderTestType || 'induccion';
    const isInduction = moduleName === 'induction' || testType === 'induccion';
    const isGlobal = root.dataset.builderGlobal === '1';
    const canBank = root.dataset.builderCanBank === '1';
    const defaultAttempts = Number(root.dataset.builderDefaultAttempts || 1);
    const defaultApproval = Number(root.dataset.builderDefaultApproval || 70);
    let endpoints = {};
    try { endpoints = JSON.parse(root.dataset.builderEndpoints || '{}'); } catch (e) { endpoints = {}; }

    const modalEl = document.getElementById('builderWizardModal');
    const modal = modalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: false }) : null;
    const pageAlert = document.getElementById('builderPageAlert');
    const wizardAlert = document.getElementById('builderWizardAlert');
    const companySelect = document.getElementById('builderCompanySelect');
    const createBtn = document.getElementById('builderCreateBtn');
    const stateFilter = document.getElementById('builderStateFilter');
    const catalogStatus = document.getElementById('builderCatalogStatus');
    const tableWrap = document.getElementById('builderCatalogTableWrap');
    const tableBody = document.getElementById('builderCatalogBody');
    const cards = document.getElementById('builderCatalogCards');
    const wizardTitle = document.getElementById('builderWizardTitle');
    const wizardSubtitle = document.getElementById('builderWizardSubtitle');
    const progressButtons = Array.from(document.querySelectorAll('[data-builder-goto]'));
    const steps = Array.from(document.querySelectorAll('[data-builder-step]'));
    const prevBtn = document.getElementById('builderPrevBtn');
    const nextBtn = document.getElementById('builderNextBtn');
    const saveDraftBtn = document.getElementById('builderSaveDraftBtn');
    const publishBtn = document.getElementById('builderPublishBtn');

    const basicForm = document.getElementById('builderBasicForm');
    const itemId = document.getElementById('builderItemId');
    const formMode = document.getElementById('builderFormMode');
    const nameInput = document.getElementById('builderName');
    const descriptionInput = document.getElementById('builderDescription');
    const attemptsInput = document.getElementById('builderAttempts');
    const approvalInput = document.getElementById('builderApproval');
    const fromInput = document.getElementById('builderFrom');
    const untilInput = document.getElementById('builderUntil');

    const materialFiles = document.getElementById('builderMaterialFiles');
    const materialChoose = document.getElementById('builderMaterialChooseBtn');
    const materialUpload = document.getElementById('builderMaterialUploadBtn');
    const materialPending = document.getElementById('builderMaterialPending');
    const materialsList = document.getElementById('builderMaterialsList');
    const materialDrop = document.getElementById('builderMaterialDropZone');

    const questionForm = document.getElementById('builderQuestionForm');
    const questionText = document.getElementById('builderQuestionText');
    const questionType = document.getElementById('builderQuestionType');
    const questionDifficulty = document.getElementById('builderQuestionDifficulty');
    const questionScore = document.getElementById('builderQuestionScore');
    const questionOptions = document.getElementById('builderQuestionOptions');
    const addOptionBtn = document.getElementById('builderAddOptionBtn');
    const questionMedia = document.getElementById('builderQuestionMedia');
    const questionMediaChoose = document.getElementById('builderQuestionMediaChooseBtn');
    const questionMediaPreview = document.getElementById('builderQuestionMediaPreview');
    const questionSubmitBtn = document.getElementById('builderQuestionSubmitBtn');
    const questionCancelEditBtn = document.getElementById('builderQuestionCancelEditBtn');
    const questionSearch = document.getElementById('builderQuestionSearch');
    const questionSearchBtn = document.getElementById('builderQuestionSearchBtn');
    const bankResults = document.getElementById('builderQuestionBankResults');
    const addSelectedBtn = document.getElementById('builderAddSelectedQuestionsBtn');
    const questionsList = document.getElementById('builderQuestionsList');
    const scoreTotal = document.getElementById('builderQuestionScoreTotal');
    const autoWeightsBtn = document.getElementById('builderAutoWeightsBtn');
    const editWeightsBtn = document.getElementById('builderEditWeightsBtn');
    const weightMessage = document.getElementById('builderWeightMessage');

    const manageModalEl = document.getElementById('builderManageModal');
    const manageModal = manageModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(manageModalEl) : null;
    const manageTitle = document.getElementById('builderManageTitle');
    const manageAlert = document.getElementById('builderManageAlert');
    const manageBasic = document.getElementById('builderManageBasic');
    const manageMaterials = document.getElementById('builderManageMaterials');
    const manageQuestions = document.getElementById('builderManageQuestions');
    const manageGroups = document.getElementById('builderManageGroups');
    const manageAssignments = document.getElementById('builderManageAssignments');
    const manageStats = document.getElementById('builderManageStats');
    const manageEditBtn = document.getElementById('builderManageEditBtn');

    const userSelect = document.getElementById('builderUserSelect');
    const groupsList = document.getElementById('builderGroupsList');
    const startAt = document.getElementById('builderStartAt');
    const deadline = document.getElementById('builderDeadline');
    const assignmentsList = document.getElementById('builderAssignmentsList');
    const selectedPeopleCount = document.getElementById('builderSelectedPeopleCount');

    let currentStep = 1;
    let currentCompany = '';
    let currentItem = null;
    let catalog = [];
    let currentDetail = null;
    let bankSelection = new Map();
    let pendingMaterialFiles = [];
    let pendingQuestionMedia = [];
    let createdFromWizard = false;
    let editingQuestionRel = null;
    let editingQuestionId = null;
    let weightEditMode = false;
    let managedItem = null;
    let peopleContextKey = '';

    const labels = {
        manage: root.dataset.labelManage || 'Gestionar',
        edit: root.dataset.labelEdit || 'Editar',
        deactivate: root.dataset.labelDeactivate || 'Dar de baja',
        reactivate: root.dataset.labelReactivate || 'Reactivar',
    };

    function html(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function showAlert(el, message, type) {
        if (!el) return;
        el.className = 'alert alert-' + (type || 'info');
        el.textContent = message || '';
        el.classList.remove('d-none');
    }
    function hideAlert(el) { if (el) el.classList.add('d-none'); }

    async function api(url, options) {
        try {
            const response = await fetch(url, options || {});
            const data = await response.json();
            return data;
        } catch (e) {
            return { success: false, message: 'No se pudo completar la operación.' };
        }
    }
    async function postJson(url, payload) {
        const body = Object.assign({}, payload || {}, { csrf_token: csrf });
        return api(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
    }

    function dateOnly(value) { return value ? String(value).substring(0, 10) : ''; }
    function localDateValue(date) {
        const d = date instanceof Date ? date : new Date();
        const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000);
        return local.toISOString().slice(0, 10);
    }
    function ensureInductionDraftValidity() {
        if (!isInduction || !fromInput || !untilInput) return;
        if (!fromInput.value) fromInput.value = localDateValue(new Date());
        if (!untilInput.value) {
            const until = new Date();
            until.setFullYear(until.getFullYear() + 1);
            untilInput.value = localDateValue(until);
        }
    }
    function formatDate(value) {
        const v = dateOnly(value);
        if (!v) return '—';
        const p = v.split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : v;
    }

    function endpoint(name, fallback) { return endpoints[name] || fallback || ''; }

    async function loadCompanies() {
        if (!isGlobal || !companySelect) return;
        const r = await api(endpoint('companies'));
        companySelect.innerHTML = '<option value="">Selecciona una empresa</option>';
        if (!r.success) { showAlert(pageAlert, r.message || 'No se pudieron cargar las empresas.', 'danger'); return; }
        (r.data || []).forEach(function (c) {
            const opt = document.createElement('option');
            opt.value = c.id_company;
            opt.textContent = c.name;
            companySelect.appendChild(opt);
        });
    }

    async function loadCatalog() {
        hideAlert(pageAlert);
        if (isGlobal && !currentCompany) {
            catalog = [];
            catalogStatus.classList.remove('d-none');
            catalogStatus.textContent = 'Selecciona una empresa para ver sus actividades.';
            tableWrap.classList.add('d-none');
            cards.classList.add('d-none');
            return;
        }
        catalogStatus.classList.remove('d-none');
        catalogStatus.textContent = 'Cargando...';
        const url = endpoint('list') + (isGlobal ? '?id_company=' + encodeURIComponent(currentCompany) : '');
        const r = await api(url);
        if (!r.success) { catalogStatus.textContent = r.message || 'No se pudo cargar el listado.'; return; }
        catalog = r.data || [];
        renderCatalog();
    }

    function filteredCatalog() {
        const filter = stateFilter ? stateFilter.value : 'active';
        return catalog.filter(function (item) {
            const active = String(item.state) === '1';
            if (filter === 'active') return active;
            if (filter === 'inactive') return !active;
            return true;
        });
    }

    function renderCatalog() {
        const data = filteredCatalog();
        tableBody.innerHTML = '';
        cards.innerHTML = '';
        catalogStatus.classList.add('d-none');
        tableWrap.classList.remove('d-none');
        cards.classList.remove('d-none');

        if (!data.length) {
            catalogStatus.classList.remove('d-none');
            catalogStatus.textContent = 'No hay registros para este filtro.';
            tableWrap.classList.add('d-none');
            cards.classList.add('d-none');
            return;
        }

        data.forEach(function (item) {
            const active = String(item.state) === '1';
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td><strong>' + html(item.name) + '</strong></td>' +
                '<td class="sct-builder-table__description">' + html(item.description || '—') + '</td>' +
                '<td><strong>' + html(item.approval_percentage) + '%</strong></td>' +
                '<td><span class="text-nowrap">' + html(formatDate(item.effective_date_from)) + '</span><br><small class="text-muted">' + html(formatDate(item.effective_date_until)) + '</small></td>' +
                '<td class="text-end"><div class="sct-builder-row-actions">' +
                    '<button class="btn btn-outline-custom btn-sm" type="button" data-action="manage"><i class="bi bi-people"></i><span>Gestionar</span></button>' +
                    '<button class="btn btn-outline-custom btn-sm" type="button" data-action="edit"><i class="bi bi-pencil"></i><span>Editar</span></button>' +
                    '<button class="btn btn-outline-custom btn-sm ' + (active ? 'is-danger' : '') + '" type="button" data-action="toggle"><i class="bi ' + (active ? 'bi-pause-circle' : 'bi-arrow-counterclockwise') + '"></i><span>' + (active ? 'Dar de baja' : 'Reactivar') + '</span></button>' +
                '</div></td>';
            bindCatalogActions(tr, item);
            tableBody.appendChild(tr);

            const card = document.createElement('article');
            card.className = 'sct-builder-mobile-card';
            card.innerHTML =
                '<div class="sct-builder-mobile-card__top"><div><strong>' + html(item.name) + '</strong><p>' + html(item.description || '') + '</p></div><span class="sct-builder-state ' + (active ? 'is-active' : 'is-inactive') + '">' + (active ? 'Activo' : 'Inactivo') + '</span></div>' +
                '<div class="sct-builder-mobile-card__facts"><span><small>Aprobación</small><strong>' + html(item.approval_percentage) + '%</strong></span><span><small>Vigencia</small><strong>' + html(formatDate(item.effective_date_from)) + ' — ' + html(formatDate(item.effective_date_until)) + '</strong></span></div>' +
                '<div class="sct-builder-row-actions"><button class="btn btn-outline-custom btn-sm" data-action="manage">Gestionar</button><button class="btn btn-outline-custom btn-sm" data-action="edit">Editar</button><button class="btn btn-outline-custom btn-sm" data-action="toggle">' + (active ? 'Dar de baja' : 'Reactivar') + '</button></div>';
            bindCatalogActions(card, item);
            cards.appendChild(card);
        });
    }

    function bindCatalogActions(container, item) {
        const manage = container.querySelector('[data-action="manage"]');
        const edit = container.querySelector('[data-action="edit"]');
        const toggle = container.querySelector('[data-action="toggle"]');
        if (manage) manage.addEventListener('click', function () { openManage(item); });
        if (edit) edit.addEventListener('click', function () { openExisting(item, 1); });
        if (toggle) toggle.addEventListener('click', function () { toggleState(item); });
    }

    async function toggleState(item) {
        const active = String(item.state) === '1';
        const ok = window.sctConfirmAction ? await window.sctConfirmAction(active ? '¿Dar de baja esta actividad?' : '¿Reactivar esta actividad?') : window.confirm(active ? '¿Dar de baja esta actividad?' : '¿Reactivar esta actividad?');
        if (!ok) return;
        const r = await postJson(endpoint('state'), { id_test: item.id_test, state: active ? 0 : 1 });
        if (!r.success) { showAlert(pageAlert, r.message || 'No se pudo cambiar el estado.', 'danger'); return; }
        showAlert(pageAlert, r.message || 'Estado actualizado.', 'success');
        await loadCatalog();
    }

    function resetWizard() {
        currentItem = null;
        currentDetail = null;
        createdFromWizard = false;
        itemId.value = '';
        peopleContextKey = '';
        formMode.value = 'create';
        basicForm.reset();
        attemptsInput.value = String(defaultAttempts);
        approvalInput.value = String(defaultApproval);
        if (startAt) startAt.value = '';
        if (deadline) deadline.value = '';
        pendingMaterialFiles = [];
        pendingQuestionMedia = [];
        materialFiles.value = '';
        if (questionMedia) questionMedia.value = '';
        renderPendingMaterials();
        renderMediaPreview();
        materialsList.innerHTML = '';
        questionsList.innerHTML = '';
        assignmentsList.innerHTML = '';
        groupsList.innerHTML = '';
        bankResults.innerHTML = '';
        bankSelection.clear();
        weightEditMode = false;
        hideAlert(wizardAlert);
        if (weightMessage) weightMessage.textContent = '';
        resetQuestionEditor();
        gotoStep(1);
    }

    function openCreate() {
        if (isGlobal && !currentCompany) {
            showAlert(pageAlert, 'Selecciona una empresa antes de crear una actividad.', 'warning');
            return;
        }
        resetWizard();
        wizardTitle.textContent = createBtn ? createBtn.textContent.trim() : 'Crear actividad';
        modal.show();
        window.setTimeout(function () { nameInput.focus(); }, 250);
    }

    function normalizeAssignment(a) {
        const isAudit = testType === 'auditoria';
        const name = isAudit
            ? (a.name_auditor || a.email || 'Usuario')
            : (((a.name || '') + ' ' + (a.lastname || '')).trim() || a.id_users || a.email || 'Usuario');
        const id = isAudit ? (a.id_users_auditor || a.email || '') : (a.id_users || a.email || '');
        const attempts = Number(a.intentos_usados || 0);
        let score = a.porcentaje_ultimo;
        if ((score === null || score === undefined || score === '') && a.score !== undefined && a.score !== null) {
            score = parseFloat(String(a.score).replace('%', '').replace(',', '.'));
        }
        const rawState = isAudit ? (a.status || '') : String(a.state || a.assignment_state || '1');
        const completed = isAudit ? rawState === 'completada' : rawState === '2';
        const failed = !isAudit && rawState === '3';
        const status = completed ? 'Completado' : (failed ? 'Reprobado' : (rawState === 'en_curso' ? 'En curso' : 'Pendiente'));
        return { name: name, id: String(id), attempts: attempts, score: Number.isFinite(Number(score)) ? Number(score) : null, status: status, completed: completed, failed: failed, deadline: a.deadline || '', source: a };
    }

    function renderManageBasic(item) {
        if (!manageBasic) return;
        const facts = [
            ['Nombre', item.name || '—'],
            ['Descripción', item.description || '—'],
            ['Aprobación requerida', String(item.approval_percentage || 0) + '%'],
            ['Intentos permitidos', item.attempts_allowed || '—'],
            ['Vigencia', formatDate(item.effective_date_from) + ' — ' + formatDate(item.effective_date_until)],
            ['Estado', String(item.state) === '1' ? 'Activo' : 'Borrador / inactivo']
        ];
        manageBasic.innerHTML = facts.map(function (fact) {
            return '<div class="sct-builder-readonly-fact"><small>' + html(fact[0]) + '</small><strong>' + html(fact[1]) + '</strong></div>';
        }).join('');
    }

    function renderManageMaterials(list) {
        if (!manageMaterials) return;
        if (!list.length) { manageMaterials.innerHTML = '<p class="sct-builder-empty">No hay material de apoyo cargado.</p>'; return; }
        manageMaterials.innerHTML = list.map(function (m) {
            const href = m.file_path ? '../../' + String(m.file_path).replace(/^\/+/, '') : '';
            const label = m.title || 'Material';
            return '<div class="sct-builder-readonly-row"><div><i class="bi bi-paperclip"></i><strong>' + html(label) + '</strong><small>' + html(m.material_type || '') + '</small></div>' + (href ? '<a class="btn btn-outline-custom btn-sm" href="' + html(href) + '" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i><span>Ver</span></a>' : '') + '</div>';
        }).join('');
    }

    function renderManageQuestions(list) {
        if (!manageQuestions) return;
        if (!list.length) { manageQuestions.innerHTML = '<p class="sct-builder-empty">No hay preguntas agregadas.</p>'; return; }
        manageQuestions.innerHTML = list.map(function (q, idx) {
            const media = Array.isArray(q.media) ? q.media : [];
            const mediaHtml = media.length ? '<div class="sct-builder-readonly-media">' + media.map(function (m) {
                const src = '../../' + String(m.file_path || '').replace(/^\/+/, '');
                if (m.media_type === 'image') return '<a href="' + html(src) + '" target="_blank" rel="noopener"><img src="' + html(src) + '" alt=""></a>';
                return '<a href="' + html(src) + '" target="_blank" rel="noopener"><i class="bi bi-camera-video"></i><span>' + html(m.original_name || 'Video') + '</span></a>';
            }).join('') + '</div>' : '';
            return '<div class="sct-builder-readonly-question"><span class="sct-builder-readonly-question__number">' + (idx + 1) + '</span><div><strong>' + html(q.question) + '</strong><small>' + html(q.question_type === 'true_false' ? 'Verdadero / falso' : 'Opción múltiple') + ' · ' + html(q.assigned_score || 0) + '%</small>' + mediaHtml + '</div></div>';
        }).join('');
    }

    function renderManageGroups(groups, assignedIds) {
        if (!manageGroups) return;
        const assigned = new Set(assignedIds.map(String));
        const matches = (groups || []).map(function (g) {
            const users = Array.from(new Set((g.users || []).map(String)));
            const covered = users.filter(function (id) { return assigned.has(id); }).length;
            if (!covered) return null;
            return { label: g.label || g.name, covered: covered, total: users.length };
        }).filter(Boolean);
        if (!matches.length) { manageGroups.innerHTML = ''; return; }
        manageGroups.innerHTML = '<div class="sct-builder-readonly-group-title">Grupos con usuarios asignados</div>' + matches.map(function (g) {
            return '<span class="sct-builder-readonly-group"><strong>' + html(g.label) + '</strong><small>' + html(g.covered) + '/' + html(g.total) + ' usuarios</small></span>';
        }).join('');
    }

    function renderManageAssignments(rows, groups) {
        if (!manageAssignments) return;
        const normalized = (rows || []).map(normalizeAssignment);
        const completed = normalized.filter(function (a) { return a.completed; }).length;
        const failed = normalized.filter(function (a) { return a.failed; }).length;
        const pending = Math.max(0, normalized.length - completed - failed);
        if (manageStats) manageStats.textContent = normalized.length + ' asignados · ' + completed + ' completados · ' + pending + ' pendientes' + (failed ? ' · ' + failed + ' reprobados' : '');
        renderManageGroups(groups || [], normalized.map(function (a) { return a.id; }).filter(Boolean));
        if (!normalized.length) { manageAssignments.innerHTML = '<p class="sct-builder-empty">No hay usuarios asignados.</p>'; return; }
        manageAssignments.innerHTML = normalized.map(function (a) {
            const progress = a.score === null ? (a.completed ? 100 : 0) : Math.max(0, Math.min(100, a.score));
            return '<div class="sct-builder-readonly-assignment"><div class="sct-builder-readonly-assignment__main"><strong>' + html(a.name) + '</strong><small>' + html(a.id) + '</small></div><div class="sct-builder-readonly-assignment__progress"><span><b>' + html(a.status) + '</b><small>' + html(a.attempts) + ' intento(s) · ' + (a.score === null ? 'sin calificación' : html(a.score) + '%') + '</small></span><div class="sct-builder-mini-progress" aria-label="Progreso ' + html(progress) + '%"><i style="width:' + progress + '%"></i></div></div></div>';
        }).join('');
    }

    async function openManage(item) {
        managedItem = item;
        if (manageTitle) manageTitle.textContent = item.name || 'Gestionar actividad';
        renderManageBasic(item);
        if (manageMaterials) manageMaterials.innerHTML = '<p class="sct-builder-empty">Cargando…</p>';
        if (manageQuestions) manageQuestions.innerHTML = '<p class="sct-builder-empty">Cargando…</p>';
        if (manageAssignments) manageAssignments.innerHTML = '<p class="sct-builder-empty">Cargando…</p>';
        if (manageGroups) manageGroups.innerHTML = '';
        if (manageStats) manageStats.textContent = '';
        hideAlert(manageAlert);
        if (manageModal) manageModal.show();
        const company = isGlobal ? currentCompany : '';
        const groupUrl = '../../api/actividades/test-builder.php?action=groups&type=' + encodeURIComponent(testType) + (company ? '&id_company=' + encodeURIComponent(company) : '');
        const detailUrl = '../../api/actividades/test-builder.php?action=detail&type=' + encodeURIComponent(testType) + '&id_test=' + encodeURIComponent(item.id_test);
        const assignmentUrl = endpoint('assignments_list') + '?id_test=' + encodeURIComponent(item.id_test);
        const results = await Promise.all([api(detailUrl), api(assignmentUrl), api(groupUrl)]);
        if (!results[0].success) { showAlert(manageAlert, results[0].message || 'No se pudo cargar el detalle.', 'danger'); return; }
        const detail = results[0].data || {};
        renderManageBasic(detail.test || item);
        renderManageMaterials(detail.materials || []);
        renderManageQuestions(detail.questions || []);
        renderManageAssignments(results[1].success ? (results[1].data || []) : [], results[2].success ? (results[2].data || []) : []);
    }

    async function openExisting(item, step) {
        resetWizard();
        currentItem = item;
        peopleContextKey = '';
        itemId.value = item.id_test;
        formMode.value = 'edit';
        nameInput.value = item.name || '';
        descriptionInput.value = item.description || '';
        attemptsInput.value = item.attempts_allowed || 1;
        approvalInput.value = item.approval_percentage || 70;
        fromInput.value = dateOnly(item.effective_date_from);
        untilInput.value = dateOnly(item.effective_date_until);
        if (isInduction) {
            const from = dateOnly(item.effective_date_from);
            if (startAt) startAt.value = from ? from + 'T00:00' : '';
            if (deadline) deadline.value = dateOnly(item.effective_date_until);
        }
        wizardTitle.textContent = item.name || 'Editar actividad';
        modal.show();
        await loadDetail();
        gotoStep(step || 1);
    }

    function gotoStep(step) {
        currentStep = Math.min(4, Math.max(1, Number(step) || 1));
        steps.forEach(function (section) { section.classList.toggle('d-none', Number(section.dataset.builderStep) !== currentStep); });
        progressButtons.forEach(function (button) {
            const n = Number(button.dataset.builderGoto);
            button.classList.toggle('is-active', n === currentStep);
            button.classList.toggle('is-complete', n < currentStep);
            button.setAttribute('aria-current', n === currentStep ? 'step' : 'false');
            button.disabled = n > currentStep && !itemId.value;
        });
        prevBtn.classList.toggle('d-none', currentStep === 1);
        nextBtn.classList.toggle('d-none', currentStep === 4);
        publishBtn.classList.toggle('d-none', currentStep !== 4);
        saveDraftBtn.classList.toggle('d-none', !itemId.value);
        if (currentStep === 4 && itemId.value) void loadPeopleAndAssignments();
        const current = steps.find(function (section) { return Number(section.dataset.builderStep) === currentStep; });
        if (current) current.scrollIntoView({ block: 'start' });
    }

    async function saveBasic() {
        hideAlert(wizardAlert);
        if (!basicForm.reportValidity()) return false;
        // Inducción define su vigencia de cara al usuario únicamente en el paso final.
        // Mientras el borrador necesita existir para adjuntar material/preguntas, se mantienen
        // fechas técnicas provisionales compatibles con las APIs actuales.
        ensureInductionDraftValidity();
        if (fromInput.value && untilInput.value && fromInput.value > untilInput.value) {
            showAlert(wizardAlert, 'La fecha de término debe ser posterior a la fecha de inicio.', 'warning');
            return false;
        }
        const payload = {
            name: nameInput.value.trim(),
            description: descriptionInput.value.trim(),
            attempts_allowed: attemptsInput.value,
            approval_percentage: approvalInput.value,
            effective_date_from: fromInput.value,
            effective_date_until: untilInput.value,
        };
        let url = endpoint('create');
        if (formMode.value === 'edit' && itemId.value) {
            url = endpoint('edit');
            payload.id_test = itemId.value;
        } else if (isGlobal) {
            payload.id_company = currentCompany;
        }
        const r = await postJson(url, payload);
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudo guardar la información básica.', 'danger'); return false; }
        if (!itemId.value) {
            const id = r.data && (r.data.id_test || r.data.id) ? (r.data.id_test || r.data.id) : null;
            if (!id) { showAlert(wizardAlert, 'El servidor guardó la actividad, pero no devolvió su identificador.', 'danger'); return false; }
            itemId.value = id;
            formMode.value = 'edit';
            createdFromWizard = true;
            // Se mantiene como borrador/inactiva hasta publicar.
            await postJson(endpoint('state'), { id_test: id, state: 0 });
        }
        await loadDetail();
        saveDraftBtn.classList.remove('d-none');
        return true;
    }

    async function loadDetail() {
        if (!itemId.value) return;
        const url = '../../api/actividades/test-builder.php?action=detail&type=' + encodeURIComponent(testType) + '&id_test=' + encodeURIComponent(itemId.value);
        const r = await api(url);
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudo cargar el detalle.', 'danger'); return; }
        currentDetail = r.data || {};
        renderMaterials(currentDetail.materials || []);
        renderQuestions(currentDetail.questions || []);
    }

    function renderMaterials(list) {
        materialsList.innerHTML = '';
        if (!list.length) {
            materialsList.innerHTML = '<p class="sct-builder-empty">No hay material de apoyo cargado.</p>';
            return;
        }
        list.forEach(function (m) {
            const row = document.createElement('div');
            row.className = 'sct-builder-file-row';
            const icon = m.material_type === 'video' ? 'bi-camera-video' : (m.material_type === 'documento' ? 'bi-file-earmark-text' : 'bi-image');
            row.innerHTML = '<span class="sct-builder-file-row__icon"><i class="bi ' + icon + '"></i></span><div><strong>' + html(m.title || 'Archivo') + '</strong><small>' + html(m.material_type || '') + '</small></div><button type="button" class="btn btn-outline-custom btn-sm" aria-label="Eliminar"><i class="bi bi-trash"></i></button>';
            row.querySelector('button').addEventListener('click', async function () {
                const ok = window.sctConfirmAction ? await window.sctConfirmAction('¿Eliminar este material?') : window.confirm('¿Eliminar este material?');
                if (!ok) return;
                const r = await postJson('../../api/actividades/test-builder.php?action=material_delete&type=' + encodeURIComponent(testType), { id_material: m.id_material, id_test: itemId.value });
                if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudo eliminar el material.', 'danger'); return; }
                await loadDetail();
            });
            materialsList.appendChild(row);
        });
    }

    function classifyMaterial(file) {
        const type = String(file.type || '').toLowerCase();
        if (type.startsWith('video/')) return 'video';
        if (type.startsWith('image/')) return 'otro';
        return 'documento';
    }

    function setPendingMaterialFiles(files) {
        pendingMaterialFiles = Array.from(files || []);
        renderPendingMaterials();
    }
    function renderPendingMaterials() {
        materialPending.innerHTML = '';
        const has = pendingMaterialFiles.length > 0;
        materialPending.classList.toggle('d-none', !has);
        materialUpload.classList.toggle('d-none', !has);
        pendingMaterialFiles.forEach(function (file, idx) {
            const row = document.createElement('div');
            row.className = 'sct-builder-pending-file';
            row.innerHTML = '<i class="bi bi-paperclip"></i><span>' + html(file.name) + '</span><small>' + Math.max(1, Math.round(file.size / 1024)) + ' KB</small><button type="button" aria-label="Quitar">×</button>';
            row.querySelector('button').addEventListener('click', function () { pendingMaterialFiles.splice(idx, 1); renderPendingMaterials(); });
            materialPending.appendChild(row);
        });
    }

    async function uploadMaterials() {
        if (!itemId.value) { showAlert(wizardAlert, 'Guarda primero los datos básicos.', 'warning'); return false; }
        if (!pendingMaterialFiles.length) return true;
        const form = new FormData();
        form.append('csrf_token', csrf);
        form.append('id_test', itemId.value);
        form.append('type', testType);
        pendingMaterialFiles.forEach(function (file) { form.append('files[]', file, file.name); });
        materialUpload.disabled = true;
        const r = await api('../../api/actividades/test-builder.php?action=material_upload', { method: 'POST', body: form });
        materialUpload.disabled = false;
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudieron subir los archivos.', 'danger'); return false; }
        pendingMaterialFiles = [];
        materialFiles.value = '';
        renderPendingMaterials();
        await loadDetail();
        return true;
    }

    function setupQuestionOptions() {
        if (!questionOptions) return;
        questionOptions.innerHTML = '';
        if (questionType && questionType.value === 'true_false') {
            addOption('Verdadero', true, true);
            addOption('Falso', false, true);
            if (addOptionBtn) addOptionBtn.classList.add('d-none');
        } else {
            addOption('', true, false);
            addOption('', false, false);
            if (addOptionBtn) addOptionBtn.classList.remove('d-none');
        }
    }

    function addOption(value, checked, locked) {
        const idx = questionOptions.querySelectorAll('.sct-builder-option-row').length;
        const row = document.createElement('div');
        row.className = 'sct-builder-option-row';
        row.innerHTML = '<span class="sct-builder-option-row__handle"><i class="bi bi-grip-vertical"></i></span><input type="text" class="form-control sct-builder-option-text" placeholder="Alternativa ' + (idx + 1) + '" value="' + html(value || '') + '" ' + (locked ? 'readonly' : '') + '><label class="sct-builder-correct"><input type="radio" name="builderCorrectOption" class="form-check-input" ' + (checked ? 'checked' : '') + '><span>Correcta</span></label>' + (locked ? '' : '<button type="button" class="btn btn-sm btn-link text-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>');
        const remove = row.querySelector('button');
        if (remove) remove.addEventListener('click', function () { if (questionOptions.querySelectorAll('.sct-builder-option-row').length > 2) row.remove(); });
        questionOptions.appendChild(row);
    }

    function renderMediaPreview() {
        questionMediaPreview.innerHTML = '';
        pendingQuestionMedia.forEach(function (file) {
            const item = document.createElement('div');
            item.className = 'sct-builder-media-preview__item';
            if (String(file.type).startsWith('image/')) {
                const img = document.createElement('img');
                img.alt = file.name;
                img.src = URL.createObjectURL(file);
                item.appendChild(img);
            } else {
                item.innerHTML = '<i class="bi bi-camera-video"></i><span>' + html(file.name) + '</span>';
            }
            questionMediaPreview.appendChild(item);
        });
    }

    function balancedWeights(count) {
        if (count <= 0 || count > 100) return [];
        const base = Math.floor(100 / count);
        let remainder = 100 - (base * count);
        return Array.from({ length: count }, function () {
            const value = base + (remainder > 0 ? 1 : 0);
            if (remainder > 0) remainder--;
            return value;
        });
    }

    function setWeightMessage(total) {
        if (!weightMessage) return;
        weightMessage.className = 'sct-builder-weight-message ' + (total === 100 ? 'is-valid' : 'is-warning');
        weightMessage.textContent = total === 100
            ? 'Distribución válida: las preguntas suman 100%.'
            : 'La suma actual es ' + total + '%. Debe ser exactamente 100% antes de publicar.';
    }

    async function saveQuestionWeights(weights) {
        if (!itemId.value || !weights.length) return false;
        const r = await postJson('../../api/actividades/test-builder.php?action=question_weights_update&type=' + encodeURIComponent(testType), { id_test: itemId.value, weights: weights });
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudieron guardar los porcentajes.', 'warning'); return false; }
        await loadDetail();
        return true;
    }

    async function autoRebalanceQuestions() {
        const list = currentDetail && Array.isArray(currentDetail.questions) ? currentDetail.questions : [];
        if (!list.length) return true;
        const values = balancedWeights(list.length);
        if (!values.length) { showAlert(wizardAlert, 'La distribución automática admite hasta 100 preguntas.', 'warning'); return false; }
        weightEditMode = false;
        return saveQuestionWeights(list.map(function (q, idx) { return { id_rel: Number(q.id_rel), score: values[idx] }; }));
    }

    async function toggleManualWeights() {
        if (!weightEditMode) {
            weightEditMode = true;
            renderQuestions((currentDetail && currentDetail.questions) || []);
            return;
        }
        const inputs = Array.from(questionsList.querySelectorAll('[data-weight-rel]'));
        const weights = inputs.map(function (input) { return { id_rel: Number(input.dataset.weightRel), score: Number(input.value) }; });
        const total = weights.reduce(function (sum, item) { return sum + (Number.isFinite(item.score) ? item.score : 0); }, 0);
        if (weights.some(function (item) { return !Number.isInteger(item.score) || item.score < 1 || item.score > 100; }) || total !== 100) {
            setWeightMessage(total);
            showAlert(wizardAlert, 'Los porcentajes manuales deben ser enteros entre 1 y 100 y sumar exactamente 100%.', 'warning');
            return;
        }
        const ok = await saveQuestionWeights(weights);
        if (ok) weightEditMode = false;
    }

    function renderQuestions(list) {
        questionsList.innerHTML = '';
        let total = 0;
        if (!list.length) {
            questionsList.innerHTML = '<p class="sct-builder-empty">Todavía no hay preguntas agregadas.</p>';
            scoreTotal.textContent = '0%';
            setWeightMessage(0);
            if (editWeightsBtn) editWeightsBtn.disabled = true;
            if (autoWeightsBtn) autoWeightsBtn.disabled = true;
            return;
        }
        if (editWeightsBtn) editWeightsBtn.disabled = false;
        if (autoWeightsBtn) autoWeightsBtn.disabled = false;
        list.forEach(function (q, idx) {
            total += Number(q.assigned_score || 0);
            const card = document.createElement('article');
            card.className = 'sct-builder-question-card';
            card.draggable = !weightEditMode;
            card.dataset.relId = q.id_rel;
            const media = (q.media || []);
            const mediaHtml = media.length ? '<div class="sct-builder-question-card__media">' + media.slice(0, 3).map(function (m) { return m.media_type === 'image' ? '<img src="../../' + html(m.file_path) + '" alt="">' : '<span><i class="bi bi-camera-video"></i></span>'; }).join('') + '</div>' : '';
            const weightHtml = weightEditMode
                ? '<label class="sct-builder-weight-input"><span class="visually-hidden">Porcentaje pregunta ' + (idx + 1) + '</span><input type="number" min="1" max="100" step="1" value="' + html(q.assigned_score || 1) + '" data-weight-rel="' + html(q.id_rel) + '"><b>%</b></label>'
                : '<strong>' + html(q.assigned_score) + '%</strong>';
            card.innerHTML = '<div class="sct-builder-question-card__handle" title="Arrastrar para reordenar"><i class="bi bi-grip-vertical"></i></div><div class="sct-builder-question-card__body"><div class="sct-builder-question-card__meta"><span>' + (idx + 1) + '</span><span>' + html(q.question_type === 'true_false' ? 'Verdadero / falso' : 'Opción múltiple') + '</span>' + weightHtml + '</div><p>' + html(q.question) + '</p>' + mediaHtml + '</div><div class="sct-builder-question-card__actions"><button type="button" class="btn btn-link btn-sm" data-action="edit" aria-label="Editar"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-link btn-sm text-danger" data-action="remove" aria-label="Eliminar"><i class="bi bi-trash"></i></button></div>';
            const editButton = card.querySelector('[data-action="edit"]');
            const removeButton = card.querySelector('[data-action="remove"]');
            if (editButton) editButton.addEventListener('click', function () { editQuestion(q); });
            if (removeButton) removeButton.addEventListener('click', async function () {
                const ok = window.sctConfirmAction ? await window.sctConfirmAction('¿Eliminar esta pregunta de la actividad?') : window.confirm('¿Eliminar esta pregunta de la actividad?');
                if (!ok) return;
                const r = await postJson('../../api/actividades/test-builder.php?action=question_remove&type=' + encodeURIComponent(testType), { id_test: itemId.value, id_rel: q.id_rel });
                if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudo quitar la pregunta.', 'danger'); return; }
                resetQuestionEditor();
                await loadDetail();
                await autoRebalanceQuestions();
            });
            if (!weightEditMode) bindDrag(card);
            questionsList.appendChild(card);
        });
        scoreTotal.textContent = total + '%';
        scoreTotal.classList.toggle('text-bg-warning', total !== 100);
        scoreTotal.classList.toggle('text-bg-success', total === 100);
        setWeightMessage(total);
        if (editWeightsBtn) {
            const span = editWeightsBtn.querySelector('span');
            if (span) span.textContent = weightEditMode ? 'Guardar pesos' : 'Editar pesos';
        }
    }

    let dragging = null;
    function bindDrag(card) {
        card.addEventListener('dragstart', function () { dragging = card; card.classList.add('is-dragging'); });
        card.addEventListener('dragend', function () { card.classList.remove('is-dragging'); dragging = null; persistQuestionOrder(); });
        card.addEventListener('dragover', function (event) {
            event.preventDefault();
            if (!dragging || dragging === card) return;
            const rect = card.getBoundingClientRect();
            const before = event.clientY < rect.top + rect.height / 2;
            questionsList.insertBefore(dragging, before ? card : card.nextSibling);
        });
    }
    async function persistQuestionOrder() {
        const rels = Array.from(questionsList.querySelectorAll('[data-rel-id]')).map(function (el) { return Number(el.dataset.relId); });
        if (!rels.length) return;
        const r = await postJson('../../api/actividades/test-builder.php?action=question_reorder&type=' + encodeURIComponent(testType), { id_test: itemId.value, rel_ids: rels });
        if (!r.success) showAlert(wizardAlert, r.message || 'No se pudo guardar el orden.', 'warning');
        else await loadDetail();
    }

    function resetQuestionEditor() {
        editingQuestionRel = null;
        editingQuestionId = null;
        if (questionForm) questionForm.reset();
        if (questionType) questionType.value = 'multiple_choice';
        if (questionDifficulty) questionDifficulty.value = '1';
        if (questionScore) questionScore.value = '10';
        pendingQuestionMedia = [];
        if (questionMedia) questionMedia.value = '';
        renderMediaPreview();
        setupQuestionOptions();
        if (questionSubmitBtn) {
            const span = questionSubmitBtn.querySelector('span');
            if (span) span.textContent = 'Agregar pregunta';
        }
        if (questionCancelEditBtn) questionCancelEditBtn.classList.add('d-none');
    }

    function editQuestion(q) {
        editingQuestionRel = Number(q.id_rel);
        editingQuestionId = Number(q.id_question);
        questionText.value = q.question || '';
        questionType.value = q.question_type || 'multiple_choice';
        questionDifficulty.value = q.difficulty || 1;
        questionScore.value = q.assigned_score || q.points || 10;
        setupQuestionOptions();
        questionOptions.innerHTML = '';
        (q.options || []).forEach(function (o) { addOption(o.text_option || '', String(o.is_it_co) === '1', questionType.value === 'true_false'); });
        if (questionSubmitBtn) {
            const span = questionSubmitBtn.querySelector('span');
            if (span) span.textContent = 'Guardar cambios';
        }
        if (questionCancelEditBtn) questionCancelEditBtn.classList.remove('d-none');
        questionText.focus();
        questionText.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    async function createQuestion(event) {
        event.preventDefault();
        hideAlert(wizardAlert);
        if (!itemId.value) { showAlert(wizardAlert, 'Guarda primero los datos básicos.', 'warning'); return; }
        if (!questionForm.reportValidity()) return;
        const rows = Array.from(questionOptions.querySelectorAll('.sct-builder-option-row'));
        const options = rows.map(function (row) { return { text_option: row.querySelector('.sct-builder-option-text').value.trim(), is_it_co: row.querySelector('input[type="radio"]').checked ? 1 : 0 }; });
        if (options.some(function (o) { return !o.text_option; }) || !options.some(function (o) { return o.is_it_co; })) {
            showAlert(wizardAlert, 'Completa las alternativas y marca la respuesta correcta.', 'warning');
            return;
        }
        const wasEditing = !!editingQuestionRel;
        const questionAction = wasEditing ? 'question_update' : 'question_create';
        const payload = {
            id_test: itemId.value,
            question: questionText.value.trim(),
            question_type: questionType.value,
            difficulty: questionDifficulty.value,
            points: questionScore.value,
            assigned_score: questionScore.value,
            options: options,
        };
        if (editingQuestionRel) { payload.id_rel = editingQuestionRel; payload.id_question = editingQuestionId; }
        const r = await postJson('../../api/actividades/test-builder.php?action=' + questionAction + '&type=' + encodeURIComponent(testType), payload);
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudo crear la pregunta.', 'danger'); return; }
        const questionId = r.data && r.data.id_question ? r.data.id_question : null;
        if (questionId && pendingQuestionMedia.length) await uploadQuestionMedia(questionId, pendingQuestionMedia);
        resetQuestionEditor();
        await loadDetail();
        if (!wasEditing) await autoRebalanceQuestions();
    }

    async function uploadQuestionMedia(questionId, files) {
        const form = new FormData();
        form.append('csrf_token', csrf);
        form.append('id_test', itemId.value);
        form.append('id_question', questionId);
        form.append('type', testType);
        files.forEach(function (file) { form.append('files[]', file, file.name); });
        const r = await api('../../api/actividades/test-builder.php?action=question_media_upload', { method: 'POST', body: form });
        if (!r.success) showAlert(wizardAlert, r.message || 'La pregunta se creó, pero no se pudo adjuntar todo el multimedia.', 'warning');
    }

    async function searchBank() {
        if (!itemId.value) return;
        bankSelection.clear();
        const q = questionSearch.value.trim();
        const r = await api('../../api/actividades/test-builder.php?action=question_bank&type=' + encodeURIComponent(testType) + '&id_test=' + encodeURIComponent(itemId.value) + '&q=' + encodeURIComponent(q));
        bankResults.innerHTML = '';
        addSelectedBtn.classList.add('d-none');
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudo consultar el banco.', 'danger'); return; }
        const rows = r.data || [];
        if (!rows.length) { bankResults.innerHTML = '<p class="sct-builder-empty">No hay preguntas disponibles con ese criterio.</p>'; return; }
        rows.forEach(function (p) {
            const row = document.createElement('label');
            row.className = 'sct-builder-bank-row';
            row.innerHTML = '<input type="checkbox" class="form-check-input"><div class="sct-builder-bank-row__copy"><strong>' + html(p.question) + '</strong><small>' + html(p.question_type === 'true_false' ? 'Verdadero / falso' : 'Opción múltiple') + ' · ' + html(p.points) + '%</small></div><input type="number" class="form-control form-control-sm sct-builder-bank-score" min="1" max="100" value="' + html(p.points || 10) + '" aria-label="Puntaje">';
            const checkbox = row.querySelector('input[type="checkbox"]');
            const score = row.querySelector('.sct-builder-bank-score');
            checkbox.addEventListener('change', function () {
                if (checkbox.checked) bankSelection.set(String(p.id_questions), { id_question: p.id_questions, score: Number(score.value) || 10 });
                else bankSelection.delete(String(p.id_questions));
                addSelectedBtn.classList.toggle('d-none', bankSelection.size === 0);
            });
            score.addEventListener('change', function () { if (checkbox.checked) bankSelection.set(String(p.id_questions), { id_question: p.id_questions, score: Number(score.value) || 10 }); });
            bankResults.appendChild(row);
        });
    }

    async function addSelectedQuestions() {
        if (!bankSelection.size) return;
        const items = Array.from(bankSelection.values());
        const r = await postJson('../../api/actividades/test-builder.php?action=question_add_many&type=' + encodeURIComponent(testType), { id_test: itemId.value, questions: items });
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudieron agregar las preguntas.', 'danger'); return; }
        bankSelection.clear();
        bankResults.innerHTML = '';
        addSelectedBtn.classList.add('d-none');
        await loadDetail();
        await autoRebalanceQuestions();
    }

    async function loadPeopleAndAssignments(force) {
        if (!itemId.value) return;
        const contextKey = String(itemId.value) + '|' + String(isGlobal ? currentCompany : 'own');
        if (!force && peopleContextKey === contextKey) return;
        peopleContextKey = contextKey;
        const usersOk = await loadUsers();
        await Promise.all([loadGroups(), loadAssignments()]);
        if (!usersOk) peopleContextKey = '';
    }

    async function loadUsers() {
        if (!userSelect) return false;
        const base = endpoint('users');
        if (!base) {
            showAlert(wizardAlert, 'No se pudo resolver el origen de usuarios para la asignación.', 'danger');
            return false;
        }
        const sep = base.indexOf('?') >= 0 ? '&' : '?';
        const url = isGlobal ? base + sep + 'id_company=' + encodeURIComponent(currentCompany) : base;
        userSelect.innerHTML = '';
        if (window.sctBulkAssignmentRefresh) window.sctBulkAssignmentRefresh('builderUserSelect');
        const r = await api(url);
        if (!r.success) {
            showAlert(wizardAlert, r.message || 'No se pudieron cargar los usuarios disponibles.', 'danger');
            updateSelectedPeopleCount();
            return false;
        }
        (r.data || []).forEach(function (u) {
            const opt = document.createElement('option');
            opt.value = u.id_users || u.email || '';
            const displayName = ((u.name || '') + ' ' + (u.lastname || '')).trim();
            opt.textContent = (displayName || u.id_users || u.email || 'Usuario') + (u.id_users && displayName ? ' (' + u.id_users + ')' : '');
            opt.dataset.search = opt.textContent;
            userSelect.appendChild(opt);
        });
        if (window.sctBulkAssignmentRefresh) window.sctBulkAssignmentRefresh('builderUserSelect');
        updateSelectedPeopleCount();
        return true;
    }

    function updateSelectedPeopleCount() {
        if (!selectedPeopleCount || !userSelect) return;
        selectedPeopleCount.textContent = String(Array.from(userSelect.selectedOptions || []).filter(function (o) { return !!o.value; }).length);
    }

    async function loadGroups() {
        const company = isGlobal ? currentCompany : '';
        const r = await api('../../api/actividades/test-builder.php?action=groups&type=' + encodeURIComponent(testType) + (company ? '&id_company=' + encodeURIComponent(company) : ''));
        groupsList.innerHTML = '';
        if (!r.success || !(r.data || []).length) {
            groupsList.innerHTML = '<p class="sct-builder-empty">No hay grupos disponibles.</p>';
            return;
        }
        (r.data || []).forEach(function (g) {
            const label = document.createElement('label');
            label.className = 'sct-builder-group-row';
            label.innerHTML = '<input type="checkbox" class="form-check-input"><span><strong>' + html(g.label || g.name) + '</strong><small>' + html(g.count) + ' usuarios</small></span>';
            label.querySelector('input').addEventListener('change', function (event) {
                const set = new Set((g.users || []).map(String));
                Array.from(userSelect.options).forEach(function (opt) { if (set.has(String(opt.value))) opt.selected = event.target.checked; });
                if (window.sctBulkAssignmentRefresh) window.sctBulkAssignmentRefresh('builderUserSelect');
                updateSelectedPeopleCount();
            });
            groupsList.appendChild(label);
        });
    }

    async function loadAssignments() {
        const r = await api(endpoint('assignments_list') + '?id_test=' + encodeURIComponent(itemId.value));
        assignmentsList.innerHTML = '';
        if (!r.success || !(r.data || []).length) {
            assignmentsList.innerHTML = '<p class="sct-builder-empty">No hay usuarios asignados todavía.</p>';
            return;
        }
        (r.data || []).forEach(function (a) {
            const row = document.createElement('div');
            row.className = 'sct-builder-assignment-row';
            const name = ((a.name || '') + ' ' + (a.lastname || '')).trim() || a.id_users || a.email || 'Usuario';
            const state = String(a.state) === '2' ? 'Completado' : (String(a.state) === '3' ? 'Reprobado' : 'Pendiente');
            row.innerHTML = '<div><strong>' + html(name) + '</strong><small>' + html(a.id_users || a.email || '') + '</small></div><span>' + html(formatDate(a.deadline)) + '</span><span class="sct-builder-state ' + (String(a.state) === '1' ? 'is-pending' : 'is-active') + '">' + state + '</span>';
            assignmentsList.appendChild(row);
        });
    }

    async function saveInductionFinalValidity() {
        if (!isInduction) return true;
        const from = startAt && startAt.value ? dateOnly(startAt.value) : '';
        const until = deadline && deadline.value ? dateOnly(deadline.value) : '';
        if (!from || !until) {
            showAlert(wizardAlert, 'Define la vigencia desde y hasta antes de publicar el curso.', 'warning');
            return false;
        }
        if (from > until) {
            showAlert(wizardAlert, 'La vigencia hasta debe ser posterior o igual a la vigencia desde.', 'warning');
            return false;
        }
        fromInput.value = from;
        untilInput.value = until;
        return saveBasic();
    }

    async function assignSelected() {
        const users = window.sctBulkAssignmentValues ? window.sctBulkAssignmentValues('builderUserSelect') : Array.from(userSelect.selectedOptions || []).map(function (o) { return o.value; }).filter(Boolean);
        if (!users.length) return true;
        const payload = {
            id_test: itemId.value,
            id_users: users,
            deadline: isInduction ? deadline.value : (deadline.value || untilInput.value)
        };
        if (startAt.value) payload.start_at = startAt.value;
        if (!payload.deadline) { showAlert(wizardAlert, isInduction ? 'Define la vigencia del curso.' : 'Define una fecha límite o la vigencia de la actividad.', 'warning'); return false; }
        const r = await postJson(endpoint('assignments_create'), payload);
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudo completar la asignación.', 'danger'); return false; }
        Array.from(userSelect.options || []).forEach(function (o) { o.selected = false; });
        if (window.sctBulkAssignmentRefresh) window.sctBulkAssignmentRefresh('builderUserSelect');
        updateSelectedPeopleCount();
        await loadAssignments();
        return true;
    }

    async function publish() {
        hideAlert(wizardAlert);
        if (!itemId.value) return;
        if (currentDetail && (currentDetail.questions || []).length === 0) {
            showAlert(wizardAlert, 'Agrega al menos una pregunta antes de publicar.', 'warning');
            gotoStep(3);
            return;
        }
        const totalWeight = currentDetail ? (currentDetail.questions || []).reduce(function (sum, q) { return sum + Number(q.assigned_score || 0); }, 0) : 0;
        if (totalWeight !== 100) {
            showAlert(wizardAlert, 'El peso total de las preguntas debe sumar exactamente 100% antes de publicar.', 'warning');
            gotoStep(3);
            return;
        }
        const validityOk = await saveInductionFinalValidity();
        if (!validityOk) return;
        const okAssign = await assignSelected();
        if (!okAssign) return;
        const r = await postJson(endpoint('state'), { id_test: itemId.value, state: 1 });
        if (!r.success) { showAlert(wizardAlert, r.message || 'No se pudo publicar.', 'danger'); return; }
        showAlert(wizardAlert, r.message || 'Actividad publicada correctamente.', 'success');
        await loadCatalog();
        window.setTimeout(function () { modal.hide(); }, 600);
    }

    async function saveDraft() {
        hideAlert(wizardAlert);
        if (currentStep === 1 && !itemId.value) {
            const ok = await saveBasic();
            if (!ok) return;
        }
        if (isInduction && currentStep === 4 && ((startAt && startAt.value) || (deadline && deadline.value))) {
            const validityOk = await saveInductionFinalValidity();
            if (!validityOk) return;
        }
        if (itemId.value) await postJson(endpoint('state'), { id_test: itemId.value, state: 0 });
        showAlert(wizardAlert, 'Borrador guardado. Puedes continuar más tarde.', 'success');
        await loadCatalog();
    }

    async function next() {
        if (currentStep === 1) {
            const ok = await saveBasic();
            if (!ok) return;
            gotoStep(2);
            return;
        }
        if (currentStep === 2) {
            const ok = await uploadMaterials();
            if (!ok) return;
            gotoStep(3);
            return;
        }
        if (currentStep === 3) {
            gotoStep(4);
        }
    }

    if (createBtn) createBtn.addEventListener('click', openCreate);
    if (stateFilter) stateFilter.addEventListener('change', renderCatalog);
    if (companySelect) companySelect.addEventListener('change', function () { currentCompany = companySelect.value; peopleContextKey = ''; loadCatalog(); });
    progressButtons.forEach(function (button) { button.addEventListener('click', function () { const n = Number(button.dataset.builderGoto); if (n <= currentStep || itemId.value) gotoStep(n); }); });
    if (prevBtn) prevBtn.addEventListener('click', function () { gotoStep(currentStep - 1); });
    if (nextBtn) nextBtn.addEventListener('click', next);
    if (saveDraftBtn) saveDraftBtn.addEventListener('click', saveDraft);
    if (publishBtn) publishBtn.addEventListener('click', publish);
    if (materialChoose) materialChoose.addEventListener('click', function () { materialFiles.click(); });
    if (materialFiles) materialFiles.addEventListener('change', function () { setPendingMaterialFiles(materialFiles.files); });
    if (materialUpload) materialUpload.addEventListener('click', uploadMaterials);
    if (materialDrop) {
        ['dragenter', 'dragover'].forEach(function (name) { materialDrop.addEventListener(name, function (event) { event.preventDefault(); materialDrop.classList.add('is-dragover'); }); });
        ['dragleave', 'drop'].forEach(function (name) { materialDrop.addEventListener(name, function (event) { event.preventDefault(); materialDrop.classList.remove('is-dragover'); }); });
        materialDrop.addEventListener('drop', function (event) { setPendingMaterialFiles(event.dataTransfer.files); });
    }
    if (questionType) questionType.addEventListener('change', setupQuestionOptions);
    if (addOptionBtn) addOptionBtn.addEventListener('click', function () { addOption('', false, false); });
    if (questionMediaChoose && questionMedia) questionMediaChoose.addEventListener('click', function () { questionMedia.click(); });
    if (questionMedia) questionMedia.addEventListener('change', function () { pendingQuestionMedia = Array.from(questionMedia.files || []); renderMediaPreview(); });
    if (questionForm) questionForm.addEventListener('submit', createQuestion);
    if (questionCancelEditBtn) questionCancelEditBtn.addEventListener('click', resetQuestionEditor);
    if (userSelect) userSelect.addEventListener('change', updateSelectedPeopleCount);
    if (questionSearchBtn) questionSearchBtn.addEventListener('click', searchBank);
    if (questionSearch) questionSearch.addEventListener('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); searchBank(); } });
    if (addSelectedBtn) addSelectedBtn.addEventListener('click', addSelectedQuestions);
    if (autoWeightsBtn) autoWeightsBtn.addEventListener('click', autoRebalanceQuestions);
    if (editWeightsBtn) editWeightsBtn.addEventListener('click', toggleManualWeights);
    if (manageEditBtn) manageEditBtn.addEventListener('click', function () {
        if (!managedItem) return;
        const target = managedItem;
        if (manageModal) manageModal.hide();
        window.setTimeout(function () { openExisting(target, 1); }, 180);
    });

    if (modalEl) modalEl.addEventListener('hidden.bs.modal', function () {
        hideAlert(wizardAlert);
        if (createdFromWizard) loadCatalog();
    });

    setupQuestionOptions();
    if (isGlobal) loadCompanies();
    else loadCatalog();
})();
