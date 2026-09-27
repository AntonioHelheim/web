/** Safety Control Tower - Mis Auditorías */
document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) return;

    function notifyActivityUpdated() {
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({type:'sct-activity-updated'}, window.location.origin);
            } else {
                window.dispatchEvent(new CustomEvent('sct:activity-updated'));
            }
        } catch (_) {}
    }
    const csrfToken = container.dataset.csrfToken || "";
    const i18nEl = document.getElementById("myAuditsI18n");
    let I18N = {};
    try { I18N = i18nEl ? JSON.parse(i18nEl.textContent || "{}") : {}; } catch (_) {}
    const S = (key, fallback) => I18N[key] || fallback || key;
    const pageLanguageSelect = document.getElementById("pageLanguageSelect");
    const myAuditsAlert = document.getElementById("myAuditsAlert");
    const myAuditsStatus = document.getElementById("myAuditsStatus");
    const myAuditsList = document.getElementById("myAuditsList");
    const myAuditsBrowse = document.getElementById("myAuditsBrowse");
    const executePanel = document.getElementById("executePanel");
    const executeTitle = document.getElementById("executeTitle");
    const executeAlert = document.getElementById("executeAlert");
    const executeQuestions = document.getElementById("executeQuestions");
    const executeObservations = document.getElementById("executeObservations");
    const executeSubmitBtn = document.getElementById("executeSubmitBtn");
    const executeSaveBtn = document.getElementById("executeSaveBtn");
    const executeCloseBtn = document.getElementById("executeCloseBtn");
    const personalShell = document.querySelector(".quick-links");
    const activitySession = window.SCTActivitySession ? window.SCTActivitySession.create({ root:container, type:"audit", shell:personalShell, panel:executePanel }) : null;
    let currentAssignmentId = null;
    let currentOverdue = false;
    let assignmentsById = {};
    let directStartHandled = false;
    const requestedStartId = parseInt(new URLSearchParams(window.location.search).get("start") || "0", 10);

    function setBrowseVisible(visible) {
        if (!myAuditsBrowse) return;
        if (visible) {
            myAuditsBrowse.classList.remove("d-none");
            myAuditsBrowse.removeAttribute("hidden");
            myAuditsBrowse.removeAttribute("aria-hidden");
            try { myAuditsBrowse.inert = false; } catch (_) {}
        } else {
            myAuditsBrowse.classList.add("d-none");
            myAuditsBrowse.setAttribute("hidden", "");
            myAuditsBrowse.setAttribute("aria-hidden", "true");
            try { myAuditsBrowse.inert = true; } catch (_) {}
        }
    }

    if (requestedStartId > 0) setBrowseVisible(false);

    function escapeHtml(value) { const el = document.createElement("div"); el.textContent = value == null ? "" : String(value); return el.innerHTML; }
    function showAlert(el, message, variant) { if (!el) return; el.textContent = message || ""; el.classList.remove("d-none", "alert-success", "alert-danger", "alert-info", "alert-warning"); el.classList.add("alert-" + (variant || "danger")); }
    function hideAlert(el) { if (el) el.classList.add("d-none"); }
    async function api(url, options) { try { const response = await fetch(url, options || {}); return await response.json(); } catch (_) { return { success:false, message:S("audit_error_response", "Respuesta inválida del servidor.") }; } }
    function postJson(url, data) { return api(url, { method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify(Object.assign({csrf_token:csrfToken}, data || {})) }); }
    function fmt(template, values) { let out = template || ""; Object.keys(values || {}).forEach(key => { out = out.replaceAll("{" + key + "}", String(values[key])); }); return out; }
    function isOverdue(value) { const raw=String(value||'').substring(0,10); if(!/^\d{4}-\d{2}-\d{2}$/.test(raw))return false; const end=new Date(raw+'T23:59:59'); return !Number.isNaN(end.getTime())&&end.getTime()<Date.now(); }

    // El cambio de idioma (confirmación + guardado en perfil) lo maneja js/lang-switcher.js.

    async function loadAssignments() {
        myAuditsStatus.classList.remove("d-none", "alert-danger");
        myAuditsStatus.classList.add("alert-info");
        myAuditsStatus.textContent = S("my_audits_loading", "Cargando tus auditorías...");
        myAuditsList.classList.add("d-none");
        const r = await api("./mis-asignaciones.php");
        if (!r.success) {
            if (requestedStartId > 0 && activitySession) activitySession.exit(personalShell, executePanel);
            else if (requestedStartId > 0) document.body.classList.remove("sct-activity-mode");
            setBrowseVisible(true);
            myAuditsStatus.classList.remove("alert-info"); myAuditsStatus.classList.add("alert-danger"); myAuditsStatus.textContent = r.message || S("audit_error_response"); return;
        }
        const items = r.data || [];
        if (!items.length) {
            if (requestedStartId > 0 && activitySession) activitySession.exit(personalShell, executePanel);
            else if (requestedStartId > 0) document.body.classList.remove("sct-activity-mode");
            setBrowseVisible(true);
            myAuditsStatus.textContent = S("my_audits_empty", "No tienes auditorías asignadas por el momento."); return;
        }
        myAuditsStatus.classList.add("d-none"); myAuditsList.classList.remove("d-none");
        if (!(requestedStartId > 0 && !directStartHandled)) setBrowseVisible(true);
        assignmentsById={}; items.forEach(function(item){assignmentsById[String(item.id_user_test_assigned)]=item;}); renderAssignments(items);
        if (!directStartHandled && requestedStartId > 0) {
            directStartHandled = true;
            const requested = items.find(function (item) { return Number(item.id_user_test_assigned) === requestedStartId; });
            const remaining = requested ? Math.max(0, Number(requested.attempts_allowed || 0) - Number(requested.intentos_usados || 0)) : 0;
            if (requested && Number(requested.state) === 1 && remaining > 0) {
                window.setTimeout(function () { openExecution(requestedStartId); }, 80);
            } else {
                if (activitySession) activitySession.exit(personalShell, executePanel);
                else document.body.classList.remove("sct-activity-mode");
                setBrowseVisible(true);
                showAlert(myAuditsAlert, S("audit_error_response", "La actividad solicitada ya no está disponible."), "warning");
            }
        }
    }

    function renderAssignments(items) {
        myAuditsList.innerHTML = "";
        items.forEach(function (a) {
            const state = parseInt(a.state, 10);
            const used = parseInt(a.intentos_usados || 0, 10);
            const allowed = parseInt(a.attempts_allowed || 0, 10);
            const remaining = Math.max(0, allowed - used);
            const card = document.createElement("article");
            let cls = "pending", status = S("my_audits_pending", "Pendiente"), badge = "is-warning";
            if (state === 2) { cls = "completed"; status = S("my_audits_compliant", "Cumple"); badge = "is-success"; }
            else if (state === 3) { cls = "failed"; status = S("my_audits_non_compliant", "No cumple"); badge = "is-danger"; }
            card.className = "audit-card " + cls;
            const overdue = state === 1 && isOverdue(a.deadline);
            card.dataset.personalStatus = state === 1 ? (overdue ? "overdue" : "incomplete") : "complete";
            let action = "";
            if (state === 1) {
                action = remaining > 0
                    ? '<button type="button" class="btn btn-primary-custom" data-action="execute"><i class="bi bi-play-circle"></i> ' + escapeHtml(S("my_audits_execute", "Ejecutar auditoría")) + '</button>'
                    : '<span class="sct-personal-status-badge is-danger">' + escapeHtml(S("my_audits_no_attempts", "Sin intentos disponibles")) + '</span>';
            }
            const score = a.score ? escapeHtml(a.score) : "—";
            const due = (a.deadline || "").substring(0,10) || "—";
            const obs = a.obs || "";
            card.innerHTML = '<div class="sct-personal-card-shell">'
                + '<div class="sct-personal-card-head"><span class="sct-personal-card-head__icon"><i class="bi bi-clipboard2-check"></i></span><div class="sct-personal-card-head__copy"><h3>' + escapeHtml(a.test_name) + '</h3><p>' + escapeHtml(a.test_description || "") + '</p></div><span class="sct-personal-status-badge ' + badge + '">' + escapeHtml(status) + '</span></div>'
                + '<div class="sct-personal-card-facts">'
                + '<div class="sct-personal-card-fact"><small>' + escapeHtml(S("my_audits_due", "Vence")) + '</small><strong>' + escapeHtml(due) + '</strong></div>'
                + '<div class="sct-personal-card-fact"><small>' + escapeHtml(S("audit_attempts_used", "Intentos usados")) + '</small><strong>' + escapeHtml(used) + '/' + escapeHtml(allowed) + '</strong></div>'
                + '<div class="sct-personal-card-fact"><small>' + escapeHtml(S("audit_result", "Resultado")) + '</small><strong>' + score + '</strong></div>'
                + '<div class="sct-personal-card-fact"><small>' + escapeHtml(S("activities_status_label", "Estado")) + '</small><strong>' + escapeHtml(status) + '</strong></div>'
                + '</div>'
                + (overdue ? '<span class="sct-personal-status-badge is-danger"><i class="bi bi-clock-history"></i> ' + escapeHtml(S("activity_overdue","Plazo vencido")) + '</span>' : '')
                + (obs ? '<div class="small text-muted"><strong>' + escapeHtml(S("audit_observations", "Observaciones")) + ':</strong> ' + escapeHtml(obs) + '</div>' : '')
                + '<div class="sct-personal-card-actions">' + action + '</div></div>';
            const button = card.querySelector('[data-action="execute"]');
            if (button) button.addEventListener("click", function () { openExecution(a.id_user_test_assigned); });
            myAuditsList.appendChild(card);
        });
    }

    async function openExecution(idAssignment) {
        currentAssignmentId = parseInt(idAssignment, 10);
        const assignment = assignmentsById[String(currentAssignmentId)] || null;
        setBrowseVisible(false);
        executePanel.classList.remove("d-none");
        executePanel.removeAttribute("hidden");
        executePanel.setAttribute("aria-hidden", "false");
        currentOverdue = !!(assignment && isOverdue(assignment.deadline));
        if (activitySession) { activitySession.setId(currentAssignmentId); activitySession.enter(personalShell, executePanel); }
        else { document.body.classList.add("sct-activity-mode"); if (personalShell) personalShell.classList.add("is-direct-activity"); }
        hideAlert(executeAlert); executeQuestions.innerHTML = '<p class="text-muted">...</p>'; executeObservations.value = ""; executePanel.scrollIntoView({behavior:"smooth",block:"start"});
        if (executeSubmitBtn) executeSubmitBtn.innerHTML = '<i class="bi bi-send"></i> ' + escapeHtml(currentOverdue ? S("activity_submit_late","Enviar fuera de plazo") : S("my_audits_submit","Enviar auditoría"));
        if (currentOverdue) showAlert(executeAlert,S("activity_late_warning","El plazo ya terminó. Puedes completar la actividad, pero quedará registrada con la fecha real de entrega."),"warning");
        const r = await api("./rendir-detalle.php?id_asignacion=" + encodeURIComponent(currentAssignmentId));
        if (!r.success) { showAlert(executeAlert, r.message, "danger"); executeQuestions.innerHTML = ""; return; }
        executeTitle.textContent = r.data.name;
        executeObservations.value = r.data.observaciones_previas || "";
        renderQuestions(r.data.preguntas || []);
        restoreDraft();
    }

    function collectDraft() {
        const answers = {};
        executeQuestions.querySelectorAll(".question-block").forEach(function (block) {
            const selected = block.querySelector('input[type="radio"]:checked');
            if (selected) answers[String(block.dataset.idRel)] = selected.value;
        });
        return { answers:answers, observations:executeObservations.value };
    }

    function saveDraft(notify) {
        const saved = activitySession && activitySession.save(collectDraft());
        if (notify) showAlert(executeAlert, saved ? S("activity_draft_saved", "Tus respuestas quedaron guardadas en este dispositivo para continuar después.") : S("audit_error_response", "No se pudo guardar el avance."), saved ? "success" : "danger");
        return saved;
    }

    function restoreDraft() {
        const draft = activitySession ? activitySession.load() : null;
        if (!draft) return;
        Object.keys(draft.answers || {}).forEach(function (rel) {
            const input = executeQuestions.querySelector('[data-id-rel="' + CSS.escape(rel) + '"] input[value="' + CSS.escape(String(draft.answers[rel])) + '"]');
            if (input) input.checked = true;
        });
        if (typeof draft.observations === "string") executeObservations.value = draft.observations;
        showAlert(executeAlert, S("activity_draft_restored", "Recuperamos las respuestas que habías guardado."), "info");
    }


    function sctQuestionMediaHtml(media) {
        if (!Array.isArray(media) || !media.length) return '';
        let html = '<div class="sct-question-execution-media">';
        media.forEach(function (m) {
            const path = '../../' + String(m.file_path || '').replace(/^\/+/, '');
            if (String(m.media_type || '') === 'video') {
                html += '<video controls preload="metadata" src="' + escapeHtml(path) + '" aria-label="' + escapeHtml(m.original_name || 'Video') + '"></video>';
            } else {
                html += '<img src="' + escapeHtml(path) + '" alt="' + escapeHtml(m.original_name || '') + '" loading="lazy">';
            }
        });
        return html + '</div>';
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
            block.innerHTML = '<div class="sct-question-execution-layout"><div class="sct-question-execution-copy"><p><strong>' + (index + 1) + '.</strong> ' + escapeHtml(q.question) + '</p></div>' + sctQuestionMediaHtml(q.media || []) + '</div>' + options;
            executeQuestions.appendChild(block);
        });
    }

    function clearDirectMode() {
        if (activitySession) activitySession.exit(personalShell, executePanel);
        else { document.body.classList.remove("sct-activity-mode"); if (personalShell) personalShell.classList.remove("is-direct-activity"); }
        setBrowseVisible(true);
    }

    if (executeSubmitBtn) executeSubmitBtn.addEventListener("click", async function () {
        hideAlert(executeAlert);
        const blocks = Array.from(executeQuestions.querySelectorAll(".question-block"));
        const answers = [];
        for (const block of blocks) {
            const selected = block.querySelector('input[type="radio"]:checked');
            if (!selected) { showAlert(executeAlert, S("activity_complete_before_submit", S("my_audits_must_answer", "Debes responder todas las preguntas antes de enviar.")), "warning"); block.scrollIntoView({behavior:"smooth",block:"center"}); return; }
            answers.push({ id_rel:parseInt(block.dataset.idRel,10), id_question:parseInt(block.dataset.idQuestion,10), id_questions_options:parseInt(selected.value,10) });
        }
        if (!await window.sctConfirmAction(currentOverdue ? S("activity_confirm_submit_late","Esta entrega está fuera de plazo. ¿Confirmas que deseas enviarla ahora?") : S("activity_confirm_submit", "¿Confirmas que deseas enviar? Revisa que todas tus respuestas estén completas."))) return;
        executeSubmitBtn.disabled = true;
        const r = await postJson("./rendir-responder.php", { id_asignacion:currentAssignmentId, respuestas:answers, observaciones:executeObservations.value.trim() });
        executeSubmitBtn.disabled = false;
        if (!r.success) { showAlert(executeAlert, r.message, "danger"); return; }
        if (activitySession) activitySession.clear();
        if (r.data.finalizada) {
            const template = r.data.cumple ? S("my_audits_completed_ok", "Auditoría finalizada con {score}% de cumplimiento.") : S("my_audits_completed_fail", "Auditoría finalizada con {score}% de cumplimiento, bajo el mínimo requerido.");
            showAlert(executeAlert, fmt(template, {score:r.data.porcentaje}), r.data.cumple ? "success" : "warning");
        } else {
            const remaining = r.data.attempts_allowed - r.data.intentos_usados;
            showAlert(executeAlert, fmt(S("my_audits_retry", "Resultado actual: {score}%. Te quedan {remaining} intento(s)."), {score:r.data.porcentaje, remaining:remaining}), "warning");
        }
        setTimeout(function () { executePanel.classList.add("d-none"); clearDirectMode(); loadAssignments(); }, 2200);
    });
    if (executeSaveBtn) executeSaveBtn.addEventListener("click", function () { saveDraft(true); });
    if (executeCloseBtn) executeCloseBtn.addEventListener("click", function () { saveDraft(false); executePanel.classList.add("d-none"); currentOverdue=false; clearDirectMode(); });

    loadAssignments();
});
