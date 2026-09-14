/** Safety Control Tower - Auditorías / administración */
document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) return;

    const csrfToken = container.dataset.csrfToken || "";
    const isGlobalAdmin = container.dataset.isGlobalAdmin === "1";
    const puedeBanco = container.dataset.puedeBanco === "1";
    const i18nEl = document.getElementById("auditI18n");
    let I18N = {};
    try { I18N = i18nEl ? JSON.parse(i18nEl.textContent || "{}") : {}; } catch (_) {}
    const S = (key, fallback) => I18N[key] || fallback || key;

    const companySelect = document.getElementById("companySelect");
    const pageLanguageSelect = document.getElementById("pageLanguageSelect");
    const auditForm = document.getElementById("auditForm");
    const auditFormMode = document.getElementById("auditFormMode");
    const auditFormTarget = document.getElementById("auditFormTarget");
    const auditFormTitle = document.getElementById("auditFormTitle");
    const auditName = document.getElementById("auditName");
    const auditDescription = document.getElementById("auditDescription");
    const auditAttempts = document.getElementById("auditAttempts");
    const auditThreshold = document.getElementById("auditThreshold");
    const auditFrom = document.getElementById("auditFrom");
    const auditUntil = document.getElementById("auditUntil");
    const auditSubmitBtn = document.getElementById("auditSubmitBtn");
    const auditCancelEditBtn = document.getElementById("auditCancelEditBtn");
    const auditActionAlert = document.getElementById("auditActionAlert");
    const auditsStatus = document.getElementById("auditsStatus");
    const auditsTableWrapper = document.getElementById("auditsTableWrapper");
    const auditsTableBody = document.getElementById("auditsTableBody");
    const auditDetail = document.getElementById("auditDetail");
    const auditDetailName = document.getElementById("auditDetailName");
    const auditDetailCloseBtn = document.getElementById("auditDetailCloseBtn");
    const auditMaxScore = document.getElementById("auditMaxScore");
    const detailAlert = document.getElementById("detailAlert");
    const auditQuestionsList = document.getElementById("auditQuestionsList");
    const questionSearchInput = document.getElementById("questionSearchInput");
    const questionSearchBtn = document.getElementById("questionSearchBtn");
    const questionSearchResults = document.getElementById("questionSearchResults");
    const newQuestionForm = document.getElementById("newQuestionForm");
    const newQuestionText = document.getElementById("newQuestionText");
    const newQuestionOptions = document.getElementById("newQuestionOptions");
    const addOptionRowBtn = document.getElementById("addOptionRowBtn");
    const newQuestionDifficulty = document.getElementById("newQuestionDifficulty");
    const newQuestionPoints = document.getElementById("newQuestionPoints");
    const auditAssignmentsList = document.getElementById("auditAssignmentsList");
    const assignForm = document.getElementById("assignForm");
    const assignAuditorSelect = document.getElementById("assignAuditorSelect");
    const assignDeadline = document.getElementById("assignDeadline");

    let currentCompanyId = null;
    let currentAuditId = null;
    let currentAuditData = null;

    function escapeHtml(value) {
        const el = document.createElement("div");
        el.textContent = value == null ? "" : String(value);
        return el.innerHTML;
    }
    function showAlert(el, message, variant) {
        if (!el) return;
        el.textContent = message || "";
        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-info", "alert-warning");
        el.classList.add("alert-" + (variant || "danger"));
    }
    function hideAlert(el) { if (el) el.classList.add("d-none"); }
    async function api(url, options) {
        try {
            const response = await fetch(url, options || {});
            const payload = await response.json();
            return payload;
        } catch (_) {
            return { success: false, message: S("audit_error_response", "Respuesta inválida del servidor.") };
        }
    }
    function postJson(url, data) {
        return api(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(Object.assign({ csrf_token: csrfToken }, data || {})),
        });
    }
    function closeDetail() {
        currentAuditId = null;
        currentAuditData = null;
        if (auditDetail) auditDetail.classList.remove("active");
        if (questionSearchResults) questionSearchResults.innerHTML = "";
        hideAlert(detailAlert);
    }
    function resetForm() {
        if (!auditForm) return;
        auditForm.reset();
        auditAttempts.value = "1";
        auditThreshold.value = "80";
        auditFormMode.value = "create";
        auditFormTarget.value = "";
        auditFormTitle.textContent = S("audit_form_new", "Nueva auditoría");
        auditSubmitBtn.textContent = S("audit_save", "Guardar auditoría");
        auditCancelEditBtn.classList.add("d-none");
    }

    // El cambio de idioma (confirmación + guardado en perfil) lo maneja
    // js/lang-switcher.js, incluido en la página junto a este script.

    async function loadCompanies() {
        if (!companySelect) return;
        const r = await api("./empresas-disponibles.php");
        if (!r.success) { showAlert(auditActionAlert, r.message, "danger"); return; }
        (r.data || []).forEach(function (company) {
            const option = document.createElement("option");
            option.value = company.id_company;
            option.textContent = company.razon_social;
            companySelect.appendChild(option);
        });
    }

    if (companySelect) {
        companySelect.addEventListener("change", function () {
            currentCompanyId = companySelect.value ? parseInt(companySelect.value, 10) : null;
            resetForm();
            closeDetail();
            if (currentCompanyId) loadAudits();
            else {
                auditsTableWrapper.classList.add("d-none");
                auditsStatus.classList.remove("d-none");
                auditsStatus.textContent = S("audit_select_company_first", "Selecciona una empresa para ver sus auditorías.");
            }
        });
        loadCompanies();
    } else {
        loadAudits();
    }

    async function loadAudits() {
        auditsStatus.classList.remove("d-none", "alert-danger");
        auditsStatus.classList.add("alert-info");
        auditsStatus.textContent = S("audit_loading", "Cargando auditorías...");
        auditsTableWrapper.classList.add("d-none");
        const url = isGlobalAdmin && currentCompanyId
            ? "./auditorias-listar.php?id_company=" + encodeURIComponent(currentCompanyId)
            : "./auditorias-listar.php";
        const r = await api(url);
        if (!r.success) {
            auditsStatus.classList.remove("alert-info");
            auditsStatus.classList.add("alert-danger");
            auditsStatus.textContent = r.message || S("audit_error_load");
            return;
        }
        const audits = r.data || [];
        if (!audits.length) {
            auditsStatus.textContent = S("audit_empty", "Todavía no hay auditorías configuradas para esta empresa.");
            return;
        }
        auditsStatus.classList.add("d-none");
        auditsTableWrapper.classList.remove("d-none");
        renderAudits(audits);
    }

    function renderAudits(audits) {
        auditsTableBody.innerHTML = "";
        audits.forEach(function (audit) {
            const active = String(audit.state) === "1";
            const row = document.createElement("tr");
            row.innerHTML =
                "<td>" + escapeHtml(audit.name) + "</td>" +
                "<td>" + escapeHtml(audit.approval_percentage) + "%</td>" +
                "<td>" + escapeHtml(audit.attempts_allowed) + "</td>" +
                "<td>" + escapeHtml(audit.preguntas_count == null ? 0 : audit.preguntas_count) + "</td>" +
                '<td class="' + (active ? "text-success" : "text-muted") + '">' + (active ? S("common_active", "Activo") : S("common_inactive", "Inactivo")) + "</td>" +
                '<td class="form-actions">' +
                '<button type="button" class="btn btn-outline-custom btn-sm" data-action="manage">' + escapeHtml(S("audit_action_manage", "Gestionar")) + "</button>" +
                '<button type="button" class="btn btn-outline-custom btn-sm" data-action="edit">' + escapeHtml(S("common_edit", "Editar")) + "</button>" +
                '<button type="button" class="btn btn-outline-custom btn-sm" data-action="toggle">' + escapeHtml(active ? S("audit_action_deactivate", "Dar de baja") : S("audit_action_reactivate", "Reactivar")) + "</button>" +
                "</td>";
            row.querySelector('[data-action="manage"]').addEventListener("click", function () { openDetail(audit); });
            row.querySelector('[data-action="edit"]').addEventListener("click", function () { startEdit(audit); });
            row.querySelector('[data-action="toggle"]').addEventListener("click", function () { toggleAudit(audit); });
            auditsTableBody.appendChild(row);
        });
    }

    function startEdit(audit) {
        auditFormMode.value = "edit";
        auditFormTarget.value = audit.id_test;
        auditName.value = audit.name || "";
        auditDescription.value = audit.description || "";
        auditAttempts.value = audit.attempts_allowed || 1;
        auditThreshold.value = audit.approval_percentage || 80;
        auditFrom.value = (audit.effective_date_from || "").substring(0, 10);
        auditUntil.value = (audit.effective_date_until || "").substring(0, 10);
        auditFormTitle.textContent = S("audit_form_edit", "Editar auditoría");
        auditSubmitBtn.textContent = S("audit_update", "Guardar cambios");
        auditCancelEditBtn.classList.remove("d-none");
        auditForm.scrollIntoView({ behavior: "smooth" });
    }

    if (auditCancelEditBtn) auditCancelEditBtn.addEventListener("click", resetForm);

    if (auditForm) {
        auditForm.addEventListener("submit", async function (event) {
            event.preventDefault(); hideAlert(auditActionAlert);
            if (isGlobalAdmin && !currentCompanyId) { showAlert(auditActionAlert, S("audit_select_company_first"), "warning"); return; }
            const edit = auditFormMode.value === "edit";
            const payload = {
                id_company: currentCompanyId,
                name: auditName.value.trim(),
                description: auditDescription.value.trim(),
                attempts_allowed: parseInt(auditAttempts.value, 10),
                approval_percentage: parseInt(auditThreshold.value, 10),
                effective_date_from: auditFrom.value,
                effective_date_until: auditUntil.value,
            };
            if (edit) payload.id_test = parseInt(auditFormTarget.value, 10);
            auditSubmitBtn.disabled = true;
            const r = await postJson(edit ? "./auditorias-editar.php" : "./auditorias-crear.php", payload);
            auditSubmitBtn.disabled = false;
            if (!r.success) { showAlert(auditActionAlert, r.message || S("audit_error_load"), "danger"); return; }
            showAlert(auditActionAlert, r.message || (edit ? S("audit_updated") : S("audit_created")), "success");
            resetForm(); loadAudits();
        });
    }

    async function toggleAudit(audit) {
        const active = String(audit.state) === "1";
        if (!window.confirm(active ? S("audit_confirm_deactivate") : S("audit_confirm_reactivate"))) return;
        const r = await postJson("./auditorias-cambiar-estado.php", { id_test: audit.id_test, state: active ? 0 : 1 });
        if (!r.success) { showAlert(auditActionAlert, r.message, "danger"); return; }
        showAlert(auditActionAlert, r.message, "success");
        if (currentAuditId === parseInt(audit.id_test, 10)) closeDetail();
        loadAudits();
    }

    async function openDetail(audit) {
        currentAuditId = parseInt(audit.id_test, 10);
        currentAuditData = audit;
        auditDetailName.textContent = audit.name;
        auditDetail.classList.add("active");
        auditDetail.scrollIntoView({ behavior: "smooth" });
        hideAlert(detailAlert);
        await Promise.all([loadAuditDetail(), loadAssignments(), loadAuditors()]);
    }
    if (auditDetailCloseBtn) auditDetailCloseBtn.addEventListener("click", closeDetail);

    async function loadAuditDetail() {
        const r = await api("./auditorias-detalle.php?id=" + encodeURIComponent(currentAuditId));
        if (!r.success) { showAlert(detailAlert, r.message || S("audit_error_detail"), "danger"); return; }
        currentAuditData = r.data;
        auditMaxScore.textContent = "(" + S("audit_max_score", "Puntaje máximo") + ": " + (r.data.puntaje_maximo || 0) + ")";
        renderQuestions(r.data.preguntas || []);
    }

    function renderQuestions(questions) {
        auditQuestionsList.innerHTML = "";
        if (!questions.length) {
            auditQuestionsList.innerHTML = '<p class="text-muted mb-2">—</p>';
            return;
        }
        questions.forEach(function (q) {
            const item = document.createElement("div");
            item.className = "question-chip";
            item.innerHTML = '<div><div>' + escapeHtml(q.question) + '</div><div class="meta">' + escapeHtml(S("audit_points", "Puntaje")) + ': ' + escapeHtml(q.assigned_score) + '</div></div><button type="button" class="btn btn-outline-danger btn-sm">' + escapeHtml(S("audit_remove_question", "Quitar")) + "</button>";
            item.querySelector("button").addEventListener("click", async function () {
                const r = await postJson("./auditoria-preguntas-quitar.php", { id_rel: q.id_rel });
                if (!r.success) { showAlert(detailAlert, r.message, "danger"); return; }
                showAlert(detailAlert, r.message, "success"); loadAuditDetail();
            });
            auditQuestionsList.appendChild(item);
        });
    }

    async function searchQuestions() {
        if (!currentAuditId) return;
        const r = await api("./preguntas-listar.php?q=" + encodeURIComponent(questionSearchInput.value.trim()));
        if (!r.success) { showAlert(detailAlert, r.message, "danger"); return; }
        questionSearchResults.innerHTML = "";
        (r.data || []).forEach(function (q) {
            const row = document.createElement("div");
            row.className = "question-chip";
            row.innerHTML = '<div class="flex-grow-1"><div>' + escapeHtml(q.question) + '</div><div class="meta">' + escapeHtml(S("audit_difficulty")) + ': ' + escapeHtml(q.difficulty) + '</div></div><div class="d-flex gap-2 align-items-center"><input class="form-control form-control-sm score-input" type="number" min="1" max="10000" style="width:90px" value="' + escapeHtml(q.points || 10) + '"><button type="button" class="btn btn-outline-custom btn-sm">+</button></div>';
            row.querySelector("button").addEventListener("click", async function () {
                const score = parseInt(row.querySelector(".score-input").value, 10);
                const rr = await postJson("./auditoria-preguntas-agregar.php", { id_test: currentAuditId, id_question: q.id_questions, assigned_score: score });
                if (!rr.success) { showAlert(detailAlert, rr.message, "danger"); return; }
                showAlert(detailAlert, rr.message || S("audit_question_added"), "success");
                questionSearchResults.innerHTML = ""; loadAuditDetail();
            });
            questionSearchResults.appendChild(row);
        });
    }
    if (questionSearchBtn) questionSearchBtn.addEventListener("click", searchQuestions);
    if (questionSearchInput) questionSearchInput.addEventListener("keydown", function (e) { if (e.key === "Enter") { e.preventDefault(); searchQuestions(); } });

    if (addOptionRowBtn && newQuestionOptions) {
        addOptionRowBtn.addEventListener("click", function () {
            const index = newQuestionOptions.querySelectorAll(".option-row").length;
            const row = document.createElement("div");
            row.className = "row g-2 mb-2 option-row";
            row.innerHTML = '<div class="col-8"><input type="text" class="form-control option-text" required></div><div class="col-4 form-check mt-2"><input type="radio" name="auditCorrectOption" class="form-check-input option-correct" value="' + index + '"><label class="form-check-label">' + escapeHtml(S("audit_correct", "Correcta")) + "</label></div>";
            newQuestionOptions.appendChild(row);
        });
    }

    if (newQuestionForm && puedeBanco) {
        newQuestionForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            const rows = Array.from(newQuestionOptions.querySelectorAll(".option-row"));
            const options = rows.map(function (row) {
                return { text_option: row.querySelector(".option-text").value.trim(), is_it_co: row.querySelector(".option-correct").checked };
            });
            const points = parseInt(newQuestionPoints.value, 10);
            const r = await postJson("./preguntas-crear.php", { question: newQuestionText.value.trim(), difficulty: parseInt(newQuestionDifficulty.value, 10), points: points, opciones: options });
            if (!r.success) { showAlert(detailAlert, r.message, "danger"); return; }
            const add = await postJson("./auditoria-preguntas-agregar.php", { id_test: currentAuditId, id_question: r.data.id_questions, assigned_score: points });
            if (!add.success) { showAlert(detailAlert, add.message, "danger"); return; }
            showAlert(detailAlert, S("audit_question_created", "Pregunta creada y agregada."), "success");
            newQuestionForm.reset();
            const extra = Array.from(newQuestionOptions.querySelectorAll(".option-row")).slice(2); extra.forEach(el => el.remove());
            loadAuditDetail();
        });
    }

    async function loadAuditors() {
        if (!assignAuditorSelect) return;
        const r = await api("./auditores-disponibles.php" + (currentCompanyId ? "?id_company=" + encodeURIComponent(currentCompanyId) : ""));
        if (!r.success) { showAlert(detailAlert, r.message, "danger"); return; }
        assignAuditorSelect.innerHTML = '<option value="">' + escapeHtml(S("audit_auditor_placeholder", "Selecciona un auditor...")) + "</option>";
        (r.data || []).forEach(function (u) {
            const option = document.createElement("option");
            option.value = u.id_users;
            const fullName = ((u.name || "") + " " + (u.lastname || "")).trim();
            option.textContent = (fullName || u.id_users) + " — " + u.id_users + (u.company_name ? " · " + u.company_name : "");
            assignAuditorSelect.appendChild(option);
        });
    }

    async function loadAssignments() {
        if (!currentAuditId) return;
        const r = await api("./asignaciones-listar.php?id_test=" + encodeURIComponent(currentAuditId));
        if (!r.success) { showAlert(detailAlert, r.message || S("audit_error_assignments"), "danger"); return; }
        renderAssignments(r.data || []);
    }

    function renderAssignments(items) {
        auditAssignmentsList.innerHTML = "";
        if (!items.length) { auditAssignmentsList.innerHTML = '<p class="text-muted">' + escapeHtml(S("audit_no_assignments", "Todavía no hay ejecuciones asignadas.")) + "</p>"; return; }
        const statusLabels = {
            pendiente: S("audit_status_pending", "Pendiente"),
            en_curso: S("audit_status_in_progress", "En curso"),
            completada: S("audit_status_completed", "Completada"),
            cancelada: S("audit_status_cancelled", "Cancelada"),
        };
        items.forEach(function (a) {
            const status = a.status || "pendiente";
            const card = document.createElement("div");
            card.className = "assignment-card " + (status === "completada" ? "completed" : status === "en_curso" ? "in-progress" : "pending");
            const score = a.score ? '<span class="score-pill">' + escapeHtml(S("audit_result", "Resultado")) + ': ' + escapeHtml(a.score) + "</span> · " : "";
            const obs = a.obs ? '<div class="text-muted mt-2" style="font-size:.88rem"><strong>' + escapeHtml(S("audit_observations", "Observaciones")) + ':</strong> ' + escapeHtml(a.obs) + "</div>" : "";
            card.innerHTML = '<div class="d-flex justify-content-between gap-3 flex-wrap"><div><strong>' + escapeHtml(a.name_auditor || a.email) + '</strong><div class="text-muted" style="font-size:.84rem">' + escapeHtml(a.email) + '</div></div><div class="text-end"><div>' + score + escapeHtml(statusLabels[status] || status) + '</div><div class="text-muted" style="font-size:.82rem">' + escapeHtml(S("audit_attempts_used", "Intentos usados")) + ': ' + escapeHtml(a.intentos_usados || 0) + ' · ' + escapeHtml(S("audit_deadline", "Plazo")) + ': ' + escapeHtml((a.deadline || "").substring(0, 10)) + "</div></div></div>" + obs;
            auditAssignmentsList.appendChild(card);
        });
    }

    if (assignForm) {
        assignForm.addEventListener("submit", async function (event) {
            event.preventDefault(); hideAlert(detailAlert);
            const auditor = assignAuditorSelect.value;
            if (!auditor || !assignDeadline.value) return;
            const r = await postJson("./asignaciones-crear.php", { id_test: currentAuditId, id_users: auditor, deadline: assignDeadline.value });
            if (!r.success) { showAlert(detailAlert, r.message, "danger"); return; }
            showAlert(detailAlert, r.message || S("audit_assignment_success"), "success");
            assignForm.reset(); loadAssignments();
        });
    }
});
