/** Safety Control Tower - Mis Auditorías */
document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) return;
    const csrfToken = container.dataset.csrfToken || "";
    const i18nEl = document.getElementById("myAuditsI18n");
    let I18N = {};
    try { I18N = i18nEl ? JSON.parse(i18nEl.textContent || "{}") : {}; } catch (_) {}
    const S = (key, fallback) => I18N[key] || fallback || key;
    const pageLanguageSelect = document.getElementById("pageLanguageSelect");
    const myAuditsAlert = document.getElementById("myAuditsAlert");
    const myAuditsStatus = document.getElementById("myAuditsStatus");
    const myAuditsList = document.getElementById("myAuditsList");
    const executePanel = document.getElementById("executePanel");
    const executeTitle = document.getElementById("executeTitle");
    const executeAlert = document.getElementById("executeAlert");
    const executeQuestions = document.getElementById("executeQuestions");
    const executeObservations = document.getElementById("executeObservations");
    const executeSubmitBtn = document.getElementById("executeSubmitBtn");
    const executeCloseBtn = document.getElementById("executeCloseBtn");
    let currentAssignmentId = null;

    function escapeHtml(value) { const el = document.createElement("div"); el.textContent = value == null ? "" : String(value); return el.innerHTML; }
    function showAlert(el, message, variant) { if (!el) return; el.textContent = message || ""; el.classList.remove("d-none", "alert-success", "alert-danger", "alert-info", "alert-warning"); el.classList.add("alert-" + (variant || "danger")); }
    function hideAlert(el) { if (el) el.classList.add("d-none"); }
    async function api(url, options) { try { const response = await fetch(url, options || {}); return await response.json(); } catch (_) { return { success:false, message:S("audit_error_response", "Respuesta inválida del servidor.") }; } }
    function postJson(url, data) { return api(url, { method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify(Object.assign({csrf_token:csrfToken}, data || {})) }); }
    function fmt(template, values) { let out = template || ""; Object.keys(values || {}).forEach(key => { out = out.replaceAll("{" + key + "}", String(values[key])); }); return out; }



    async function loadAssignments() {
        myAuditsStatus.classList.remove("d-none", "alert-danger");
        myAuditsStatus.classList.add("alert-info");
        myAuditsStatus.textContent = S("my_audits_loading", "Cargando tus auditorías...");
        myAuditsList.classList.add("d-none");
        const r = await api("./mis-asignaciones.php");
        if (!r.success) { myAuditsStatus.classList.remove("alert-info"); myAuditsStatus.classList.add("alert-danger"); myAuditsStatus.textContent = r.message || S("audit_error_response"); return; }
        const items = r.data || [];
        if (!items.length) { myAuditsStatus.textContent = S("my_audits_empty", "No tienes auditorías asignadas por el momento."); return; }
        myAuditsStatus.classList.add("d-none"); myAuditsList.classList.remove("d-none"); renderAssignments(items);
    }

    function renderAssignments(items) {
        myAuditsList.innerHTML = "";
        items.forEach(function (a) {
            const state = parseInt(a.state, 10);
            const remaining = Math.max(0, parseInt(a.attempts_allowed || 0, 10) - parseInt(a.intentos_usados || 0, 10));
            const card = document.createElement("div");
            card.className = "audit-card " + (state === 2 ? "completed" : state === 3 ? "failed" : "pending");
            let status = S("my_audits_pending", "Pendiente");
            if (state === 2) status = S("my_audits_compliant", "Cumple");
            else if (state === 3) status = S("my_audits_non_compliant", "No cumple");
            let action = "";
            if (state === 1) {
                action = remaining > 0 ? '<button type="button" class="btn btn-primary-custom btn-sm" data-action="execute">' + escapeHtml(S("my_audits_execute", "Ejecutar auditoría")) + '</button>' : '<span class="text-muted">' + escapeHtml(S("my_audits_no_attempts", "Sin intentos disponibles")) + '</span>';
            }
            const score = a.score ? '<div class="result-score mt-2">' + escapeHtml(S("audit_result", "Resultado")) + ': ' + escapeHtml(a.score) + '</div>' : "";
            const obs = a.obs ? '<div class="text-muted mt-2" style="font-size:.88rem"><strong>' + escapeHtml(S("audit_observations", "Observaciones")) + ':</strong> ' + escapeHtml(a.obs) + '</div>' : "";
            card.innerHTML = '<div class="d-flex justify-content-between gap-3 flex-wrap"><div class="flex-grow-1"><h3 class="h6 mb-1">' + escapeHtml(a.test_name) + '</h3><p class="text-muted mb-1" style="font-size:.9rem">' + escapeHtml(a.test_description) + '</p><strong>' + escapeHtml(status) + '</strong> · <span class="text-muted" style="font-size:.84rem">' + escapeHtml(S("my_audits_due", "Vence")) + ' ' + escapeHtml((a.deadline || "").substring(0,10)) + '</span> · <span class="text-muted" style="font-size:.84rem">' + escapeHtml(S("audit_attempts_used", "Intentos usados")) + ': ' + escapeHtml(a.intentos_usados || 0) + '/' + escapeHtml(a.attempts_allowed || 0) + '</span>' + score + obs + '</div><div>' + action + '</div></div>';
            const button = card.querySelector('[data-action="execute"]');
            if (button) button.addEventListener("click", function () { openExecution(a.id_user_test_assigned); });
            myAuditsList.appendChild(card);
        });
    }

    async function openExecution(idAssignment) {
        currentAssignmentId = parseInt(idAssignment, 10);
        hideAlert(executeAlert); executeQuestions.innerHTML = '<p class="text-muted">...</p>'; executeObservations.value = ""; executePanel.classList.remove("d-none"); executePanel.scrollIntoView({behavior:"smooth"});
        const r = await api("./rendir-detalle.php?id_asignacion=" + encodeURIComponent(currentAssignmentId));
        if (!r.success) { showAlert(executeAlert, r.message, "danger"); executeQuestions.innerHTML = ""; return; }
        executeTitle.textContent = r.data.name;
        executeObservations.value = r.data.observaciones_previas || "";
        renderQuestions(r.data.preguntas || []);
    }

    function renderQuestions(questions) {
        executeQuestions.innerHTML = "";
        questions.forEach(function (q, index) {
            const block = document.createElement("div");
            block.className = "question-block"; block.dataset.idRel = q.id_rel; block.dataset.idQuestion = q.id_question;
            let options = "";
            (q.opciones || []).forEach(function (o, optionIndex) {
                const id = "audit_opt_" + q.id_rel + "_" + optionIndex;
                options += '<div class="form-check"><input class="form-check-input" type="radio" name="audit_question_' + q.id_rel + '" id="' + id + '" value="' + o.id_questions_options + '"><label class="form-check-label" for="' + id + '">' + escapeHtml(o.text_option) + '</label></div>';
            });
            block.innerHTML = '<p><strong>' + (index + 1) + '.</strong> ' + escapeHtml(q.question) + '</p>' + options;
            executeQuestions.appendChild(block);
        });
    }

    if (executeSubmitBtn) executeSubmitBtn.addEventListener("click", async function () {
        hideAlert(executeAlert);
        const blocks = Array.from(executeQuestions.querySelectorAll(".question-block"));
        const answers = [];
        for (const block of blocks) {
            const selected = block.querySelector('input[type="radio"]:checked');
            if (!selected) { showAlert(executeAlert, S("my_audits_must_answer", "Debes responder todas las preguntas antes de enviar."), "warning"); return; }
            answers.push({ id_rel:parseInt(block.dataset.idRel,10), id_question:parseInt(block.dataset.idQuestion,10), id_questions_options:parseInt(selected.value,10) });
        }
        executeSubmitBtn.disabled = true;
        const r = await postJson("./rendir-responder.php", { id_asignacion:currentAssignmentId, respuestas:answers, observaciones:executeObservations.value.trim() });
        executeSubmitBtn.disabled = false;
        if (!r.success) { showAlert(executeAlert, r.message, "danger"); return; }
        if (r.data.finalizada) {
            const template = r.data.cumple ? S("my_audits_completed_ok", "Auditoría finalizada con {score}% de cumplimiento.") : S("my_audits_completed_fail", "Auditoría finalizada con {score}% de cumplimiento, bajo el mínimo requerido.");
            showAlert(executeAlert, fmt(template, {score:r.data.porcentaje}), r.data.cumple ? "success" : "warning");
        } else {
            const remaining = r.data.attempts_allowed - r.data.intentos_usados;
            showAlert(executeAlert, fmt(S("my_audits_retry", "Resultado actual: {score}%. Te quedan {remaining} intento(s)."), {score:r.data.porcentaje, remaining:remaining}), "warning");
        }
        setTimeout(function () { executePanel.classList.add("d-none"); loadAssignments(); }, 2200);
    });
    if (executeCloseBtn) executeCloseBtn.addEventListener("click", function () { executePanel.classList.add("d-none"); });

    loadAssignments();
});
