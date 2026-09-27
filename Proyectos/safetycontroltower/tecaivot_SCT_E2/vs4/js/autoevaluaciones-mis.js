/** Safety Control Tower - Mis Autoevaluaciones */
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
    const $ = function (id) { return document.getElementById(id); };
    let I18N = {};
    try { I18N = JSON.parse(($('mySelfI18n') || {}).textContent || '{}'); } catch (_) {}
    const S = function (key, fallback) { return I18N[key] || fallback || key; };
    const csrfToken = container.dataset.csrfToken || '';
    const mySelfStatus = $('mySelfStatus');
    const mySelfList = $('mySelfList');
    const mySelfBrowse = $('mySelfBrowse');
    const executePanel = $('executePanel');
    const executeTitle = $('executeTitle');
    const executeDescription = $('executeDescription');
    const executeAlert = $('executeAlert');
    const executeQuestions = $('executeQuestions');
    const executeSubmitBtn = $('executeSubmitBtn');
    const executeSaveBtn = $('executeSaveBtn');
    const executeCloseBtn = $('executeCloseBtn');
    const personalShell = document.querySelector('.quick-links');
    const activitySession = window.SCTActivitySession ? window.SCTActivitySession.create({ root:container, type:'self-assessment', shell:personalShell, panel:executePanel }) : null;
    let currentAssignmentId = null;
    let currentOverdue = false;
    let assignmentsById = {};
    let directStartHandled = false;
    const requestedStartId = parseInt(new URLSearchParams(window.location.search).get('start') || '0', 10);

    function setBrowseVisible(visible) {
        if (!mySelfBrowse) return;
        if (visible) {
            mySelfBrowse.classList.remove('d-none');
            mySelfBrowse.removeAttribute('hidden');
            mySelfBrowse.removeAttribute('aria-hidden');
            try { mySelfBrowse.inert = false; } catch (_) {}
        } else {
            mySelfBrowse.classList.add('d-none');
            mySelfBrowse.setAttribute('hidden', '');
            mySelfBrowse.setAttribute('aria-hidden', 'true');
            try { mySelfBrowse.inert = true; } catch (_) {}
        }
    }

    if (requestedStartId > 0) setBrowseVisible(false);

    function escapeHtml(value) { const el = document.createElement('div'); el.textContent = value == null ? '' : String(value); return el.innerHTML; }
    function showAlert(el, message, variant) { if (!el) return; el.textContent = message || ''; el.classList.remove('d-none','alert-success','alert-danger','alert-info','alert-warning'); el.classList.add('alert-' + (variant || 'danger')); }
    function hideAlert(el) { if (el) el.classList.add('d-none'); }
    async function api(url, options) { try { const response = await fetch(url, options || {}); return await response.json(); } catch (_) { return {success:false,message:S('self_error_response','Respuesta inválida del servidor.')}; } }
    function postJson(url, data) { return api(url, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(Object.assign({csrf_token:csrfToken}, data || {}))}); }
    function fmt(template, values) { let out = template || ''; Object.keys(values || {}).forEach(function (key) { out = out.replaceAll('{' + key + '}', String(values[key])); }); return out; }
    function isOverdue(value) { const raw=String(value||'').substring(0,10); if(!/^\d{4}-\d{2}-\d{2}$/.test(raw))return false; const end=new Date(raw+'T23:59:59'); return !Number.isNaN(end.getTime())&&end.getTime()<Date.now(); }

    async function loadAssignments() {
        mySelfStatus.classList.remove('d-none','alert-danger');
        mySelfStatus.classList.add('alert-info');
        mySelfStatus.textContent = S('my_self_loading','Cargando tus autoevaluaciones...');
        mySelfList.classList.add('d-none');
        const response = await api('./mis-asignaciones.php');
        if (!response.success) {
            if (requestedStartId > 0 && activitySession) activitySession.exit(personalShell, executePanel);
            else if (requestedStartId > 0) document.body.classList.remove('sct-activity-mode');
            setBrowseVisible(true);
            mySelfStatus.classList.remove('alert-info');
            mySelfStatus.classList.add('alert-danger');
            mySelfStatus.textContent = response.message || S('self_error_response');
            return;
        }
        const items = response.data || [];
        if (!items.length) {
            if (requestedStartId > 0 && activitySession) activitySession.exit(personalShell, executePanel);
            else if (requestedStartId > 0) document.body.classList.remove('sct-activity-mode');
            setBrowseVisible(true);
            mySelfStatus.textContent = S('my_self_empty','No tienes autoevaluaciones asignadas por el momento.');
            return;
        }
        mySelfStatus.classList.add('d-none');
        mySelfList.classList.remove('d-none');
        if (!(requestedStartId > 0 && !directStartHandled)) setBrowseVisible(true);
        assignmentsById = {};
        items.forEach(function (item) { assignmentsById[String(item.id_user_test_assigned)] = item; });
        renderAssignments(items);
        if (!directStartHandled && requestedStartId > 0) {
            directStartHandled = true;
            const requested = items.find(function (item) { return Number(item.id_user_test_assigned) === requestedStartId; });
            const remaining = requested ? Math.max(0, Number(requested.attempts_allowed || 0) - Number(requested.intentos_usados || 0)) : 0;
            if (requested && Number(requested.state) === 1 && remaining > 0) window.setTimeout(function () { openExecution(requestedStartId); }, 80);
            else { if (activitySession) activitySession.exit(personalShell, executePanel); else document.body.classList.remove('sct-activity-mode'); setBrowseVisible(true); showAlert(mySelfStatus,S('self_error_response','La actividad solicitada ya no está disponible.'),'warning'); }
        }
    }

    function renderAssignments(items) {
        mySelfList.innerHTML = '';
        items.forEach(function (item) {
            const state = parseInt(item.state,10);
            const used = parseInt(item.intentos_usados || 0,10);
            const allowed = parseInt(item.attempts_allowed || 0,10);
            const remaining = Math.max(0, allowed - used);
            const card = document.createElement('article');
            let cls = 'pending', status = S('my_self_pending','Pendiente'), badge = 'is-warning';
            if (state === 2) { cls = 'approved'; status = S('my_self_approved','Aprobada'); badge = 'is-success'; }
            else if (state === 3) { cls = 'failed'; status = S('my_self_failed','Reprobada'); badge = 'is-danger'; }
            else if (used > 0) { cls = 'in-progress'; status = S('my_self_in_progress','En curso'); badge = 'is-info'; }
            card.className = 'self-card ' + cls;
            const due = (item.deadline || '').substring(0,10) || '—';
            const score = item.porcentaje_ultimo != null ? escapeHtml(item.porcentaje_ultimo) + '%' : '—';
            const overdue = state === 1 && isOverdue(item.deadline);
            card.dataset.personalStatus = state === 1 ? (overdue ? 'overdue' : 'incomplete') : 'complete';
            let action = '';
            if (state === 1) action = remaining > 0
                ? '<button type="button" class="btn btn-primary-custom" data-action="execute"><i class="bi bi-play-circle"></i> ' + escapeHtml(S('my_self_execute','Realizar autoevaluación')) + '</button>'
                : '<span class="sct-personal-status-badge is-danger">' + escapeHtml(S('my_self_no_attempts','Sin intentos disponibles')) + '</span>';
            else action = '<span class="sct-personal-status-badge ' + badge + '"><i class="bi bi-check2-circle"></i> ' + escapeHtml(status) + '</span>';
            card.innerHTML = '<div class="sct-personal-card-shell"><div class="sct-personal-card-head"><span class="sct-personal-card-head__icon"><i class="bi bi-person-check"></i></span><div class="sct-personal-card-head__copy"><h3>' + escapeHtml(item.test_name) + '</h3><p>' + escapeHtml(item.test_description || '') + '</p></div><span class="sct-personal-status-badge ' + badge + '">' + escapeHtml(status) + '</span></div><div class="sct-personal-card-facts"><div class="sct-personal-card-fact"><small>' + escapeHtml(S('my_self_due','Vence')) + '</small><strong>' + escapeHtml(due) + '</strong></div><div class="sct-personal-card-fact"><small>' + escapeHtml(S('self_attempts_used','Intentos usados')) + '</small><strong>' + used + '/' + allowed + '</strong></div><div class="sct-personal-card-fact"><small>' + escapeHtml(S('self_result','Resultado')) + '</small><strong>' + score + '</strong></div><div class="sct-personal-card-fact"><small>' + escapeHtml(S('activities_status_label','Estado')) + '</small><strong>' + escapeHtml(status) + '</strong></div></div>' + (overdue ? '<span class="sct-personal-status-badge is-danger"><i class="bi bi-clock-history"></i> ' + escapeHtml(S('activity_overdue','Plazo vencido')) + '</span>' : '') + '<div class="sct-personal-card-actions">' + action + '</div></div>';
            const button = card.querySelector('[data-action="execute"]');
            if (button) button.addEventListener('click', function () { openExecution(item.id_user_test_assigned); });
            mySelfList.appendChild(card);
        });
    }

    async function openExecution(id) {
        currentAssignmentId = parseInt(id,10);
        const assignment = assignmentsById[String(currentAssignmentId)] || null;
        currentOverdue = !!(assignment && isOverdue(assignment.deadline));
        setBrowseVisible(false);
        executePanel.classList.remove('d-none');
        executePanel.removeAttribute('hidden');
        executePanel.setAttribute('aria-hidden', 'false');
        if (activitySession) { activitySession.setId(currentAssignmentId); activitySession.enter(personalShell, executePanel); }
        else { document.body.classList.add('sct-activity-mode'); if (personalShell) personalShell.classList.add('is-direct-activity'); }
        hideAlert(executeAlert);
        if (executeSubmitBtn) executeSubmitBtn.innerHTML = '<i class="bi bi-send"></i> ' + escapeHtml(currentOverdue ? S('activity_submit_late','Enviar fuera de plazo') : S('my_self_submit','Enviar autoevaluación'));
        if (currentOverdue) showAlert(executeAlert,S('activity_late_warning','El plazo ya terminó. Puedes completar la actividad, pero quedará registrada con la fecha real de entrega.'),'warning');
        executeQuestions.innerHTML = '<p class="text-muted">...</p>';
        const response = await api('./rendir-detalle.php?id_asignacion=' + encodeURIComponent(currentAssignmentId));
        if (!response.success) { showAlert(executeAlert,response.message,'danger'); executeQuestions.innerHTML = ''; return; }
        executeTitle.textContent = response.data.name;
        executeDescription.textContent = response.data.description || '';
        renderQuestions(response.data.preguntas || []);
        restoreDraft();
        executePanel.scrollIntoView({behavior:'smooth',block:'start'});
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
        executeQuestions.innerHTML = '';
        questions.forEach(function (question,index) {
            const block = document.createElement('div');
            block.className = 'question-block';
            block.dataset.idRel = question.id_rel;
            block.dataset.idQuestion = question.id_question;
            let options = '';
            (question.opciones || []).forEach(function (option,optionIndex) {
                const id = 'self_opt_' + question.id_rel + '_' + optionIndex;
                options += '<div class="form-check"><input class="form-check-input" type="radio" name="self_question_' + question.id_rel + '" id="' + id + '" value="' + option.id_questions_options + '"><label class="form-check-label" for="' + id + '">' + escapeHtml(option.text_option) + '</label></div>';
            });
            block.innerHTML = '<div class="sct-question-execution-layout"><div class="sct-question-execution-copy"><p><strong>' + (index + 1) + '.</strong> ' + escapeHtml(question.question) + '</p></div>' + sctQuestionMediaHtml(question.media || []) + '</div>' + options;
            executeQuestions.appendChild(block);
        });
    }

    function collectDraft() {
        const answers = {};
        executeQuestions.querySelectorAll('.question-block').forEach(function (block) {
            const selected = block.querySelector('input[type="radio"]:checked');
            if (selected) answers[String(block.dataset.idRel)] = selected.value;
        });
        return {answers:answers};
    }
    function saveDraft(notify) {
        const saved = activitySession && activitySession.save(collectDraft());
        if (notify) showAlert(executeAlert,saved ? S('activity_draft_saved','Tus respuestas quedaron guardadas en este dispositivo para continuar después.') : S('self_error_response','No se pudo guardar el avance.'),saved ? 'success' : 'danger');
    }
    function restoreDraft() {
        const draft = activitySession ? activitySession.load() : null;
        if (!draft || !draft.answers) return;
        Object.keys(draft.answers).forEach(function (rel) {
            const input = executeQuestions.querySelector('[data-id-rel="' + CSS.escape(rel) + '"] input[value="' + CSS.escape(String(draft.answers[rel])) + '"]');
            if (input) input.checked = true;
        });
        showAlert(executeAlert,S('activity_draft_restored','Recuperamos las respuestas que habías guardado.'),'info');
    }
    function leaveActivity() {
        executePanel.classList.add('d-none');
        executePanel.setAttribute('aria-hidden', 'true');
        if (activitySession) activitySession.exit(personalShell, executePanel);
        else { document.body.classList.remove('sct-activity-mode'); if (personalShell) personalShell.classList.remove('is-direct-activity'); }
        setBrowseVisible(true);
    }

    if (executeSaveBtn) executeSaveBtn.addEventListener('click',function () { saveDraft(true); });
    if (executeCloseBtn) executeCloseBtn.addEventListener('click',function () { saveDraft(false); currentOverdue=false; leaveActivity(); });
    if (executeSubmitBtn) executeSubmitBtn.addEventListener('click',async function () {
        hideAlert(executeAlert);
        const blocks = Array.from(executeQuestions.querySelectorAll('.question-block'));
        const answers = [];
        for (const block of blocks) {
            const selected = block.querySelector('input[type="radio"]:checked');
            if (!selected) { showAlert(executeAlert,S('activity_complete_before_submit',S('my_self_must_answer','Debes responder todas las preguntas antes de enviar.')),'warning'); block.scrollIntoView({behavior:'smooth',block:'center'}); return; }
            answers.push({id_rel:parseInt(block.dataset.idRel,10),id_question:parseInt(block.dataset.idQuestion,10),id_questions_options:parseInt(selected.value,10)});
        }
        if (!await window.sctConfirmAction(currentOverdue ? S('activity_confirm_submit_late','Esta entrega está fuera de plazo. ¿Confirmas que deseas enviarla ahora?') : S('activity_confirm_submit','¿Confirmas que deseas enviar? Revisa que todas tus respuestas estén completas.'))) return;
        executeSubmitBtn.disabled = true;
        const response = await postJson('./rendir-responder.php',{id_asignacion:currentAssignmentId,respuestas:answers});
        executeSubmitBtn.disabled = false;
        if (!response.success) { showAlert(executeAlert,response.message,'danger'); return; }
        if (activitySession) activitySession.clear();
        if (response.data.finalizada) {
            const template = response.data.cumple ? S('my_self_completed_ok','Autoevaluación aprobada con {score}%.') : S('my_self_completed_fail','Autoevaluación finalizada con {score}%, bajo el mínimo requerido.');
            showAlert(executeAlert,fmt(template,{score:response.data.porcentaje}),response.data.cumple ? 'success' : 'warning');
        } else {
            const remaining = response.data.attempts_allowed - response.data.intentos_usados;
            showAlert(executeAlert,fmt(S('my_self_retry','Resultado actual: {score}%. Te quedan {remaining} intento(s).'),{score:response.data.porcentaje,remaining:remaining}),'warning');
        }
        window.setTimeout(function () { leaveActivity(); loadAssignments(); },2200);
    });

    loadAssignments();
});
