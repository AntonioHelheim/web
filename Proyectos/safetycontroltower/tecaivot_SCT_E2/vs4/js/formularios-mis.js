/** Safety Control Tower - Mis Formularios Dinámicos */
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('.container[data-csrf-token]');
    if (!root) return;

    function notifyActivityUpdated() {
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({type:'sct-activity-updated'}, window.location.origin);
            } else {
                window.dispatchEvent(new CustomEvent('sct:activity-updated'));
            }
        } catch (_) {}
    }
    const csrf = root.dataset.csrfToken || '';
    const $ = function (id) { return document.getElementById(id); };
    let I18N = {};
    try { I18N = JSON.parse(($('myFormsI18n') || {}).textContent || '{}'); } catch (_) {}
    const S = function (key, fallback) { return I18N[key] || fallback || key; };
    let requestedStartId = parseInt(new URLSearchParams(window.location.search).get('start') || '0',10);
    const myFormsAlert = $('myFormsAlert');
    const availableStatus = $('availableStatus');
    const availableList = $('availableList');
    const historyList = $('historyList');
    const scopeFilter = $('myFormsScopeFilter');
    const fillPanel = $('fillPanel');
    const fillTitle = $('fillTitle');
    const fillDescription = $('fillDescription');
    const fillAlert = $('fillAlert');
    const fillForm = $('fillForm');
    const fillFields = $('fillFields');
    const fillSubmit = $('fillSubmit');
    const fillSave = $('fillSave');
    const fillClose = $('fillClose');
    const historyDetail = $('historyDetail');
    const historyAnswers = $('historyAnswers');
    const historyDetailClose = $('historyDetailClose');
    const personalShell = document.querySelector('.quick-links');
    const activitySession = window.SCTActivitySession ? window.SCTActivitySession.create({root:root,type:'form',shell:personalShell,panel:fillPanel}) : null;
    let currentForm = null;
    let currentScope = 'all';
    let availableItems = [];
    let historyItems = [];
    let companyLabel = S('forms_filter_company','Empresa');

    function esc(value) { const el = document.createElement('div'); el.textContent = value == null ? '' : String(value); return el.innerHTML; }
    function show(el,message,variant) { if (!el) return; el.textContent = message || ''; el.classList.remove('d-none','alert-success','alert-danger','alert-warning','alert-info'); el.classList.add('alert-' + (variant || 'danger')); }
    function hide(el) { if (el) el.classList.add('d-none'); }
    async function api(url,options) { try { const response = await fetch(url,options || {}); return await response.json(); } catch (_) { return {success:false,message:S('forms_error_response','Respuesta inválida del servidor.')}; } }

    async function load() {
        availableStatus.className = 'alert alert-info mb-0';
        availableStatus.textContent = S('my_forms_loading','Cargando formularios...');
        availableList.classList.add('d-none');
        const response = await api('./mis-formularios-listar.php');
        if (!response.success) { availableStatus.className = 'alert alert-danger mb-0'; availableStatus.textContent = response.message; historyList.innerHTML = ''; return; }
        availableItems = response.data.disponibles || [];
        historyItems = response.data.historial || [];
        const companyCandidate = availableItems.find(function (item) { return item.id_company != null && item.company_name; })
            || historyItems.find(function (item) { return item.company_name; });
        if (companyCandidate && companyCandidate.company_name) companyLabel = String(companyCandidate.company_name);
        renderAvailable(availableItems);
        renderHistory(historyItems);
        renderScopeFilter();
        applyScopeFilter();
        if (requestedStartId > 0) { const id = requestedStartId; requestedStartId = 0; openForm(id); }
    }

    function renderAvailable(items) {
        availableList.innerHTML = '';
        if (!items.length) { availableStatus.classList.remove('d-none'); availableStatus.textContent = S('my_forms_empty','No hay formularios disponibles por el momento.'); return; }
        availableStatus.classList.add('d-none');
        availableList.classList.remove('d-none');
        items.forEach(function (form) {
            const card = document.createElement('article');
            card.className = 'form-card';
            const global = form.id_company == null;
            card.dataset.formScope = global ? 'global' : 'company';
            const scope = global ? S('forms_global_badge','Global') : (form.company_name || S('forms_company_badge','Empresa'));
            card.innerHTML = '<div class="sct-personal-card-shell"><div class="sct-personal-card-head"><span class="sct-personal-card-head__icon"><i class="bi bi-card-text"></i></span><div class="sct-personal-card-head__copy"><h3>' + esc(form.name) + '</h3><p>' + esc(form.description || '') + '</p></div><span class="sct-personal-status-badge is-info">' + esc(scope) + '</span></div><div class="sct-personal-card-facts"><div class="sct-personal-card-fact"><small>' + esc(S('forms_company_badge','Empresa')) + '</small><strong>' + esc(scope) + '</strong></div><div class="sct-personal-card-fact"><small>' + esc(S('activities_fields_label','Campos')) + '</small><strong>' + esc(form.field_count || 0) + '</strong></div></div><div class="sct-personal-card-actions"><button class="btn btn-primary-custom"><i class="bi bi-pencil-square"></i> ' + esc(S('my_forms_complete','Completar formulario')) + '</button></div></div>';
            card.querySelector('button').addEventListener('click',function () { openForm(form.id_form); });
            availableList.appendChild(card);
        });
    }

    function renderHistory(items) {
        historyList.innerHTML = '';
        if (!items.length) { historyList.innerHTML = '<div class="sct-personal-empty"><i class="bi bi-clock-history"></i><div><strong>' + esc(S('my_forms_history_empty','Todavía no has enviado formularios.')) + '</strong></div></div>'; return; }
        items.forEach(function (submission) {
            const row = document.createElement('article');
            row.className = 'history-row';
            row.dataset.formScope = submission.form_company_id == null ? 'global' : 'company';
            row.innerHTML = '<div class="sct-personal-card-shell"><div class="sct-personal-card-head"><span class="sct-personal-card-head__icon"><i class="bi bi-check2-square"></i></span><div class="sct-personal-card-head__copy"><h3>' + esc(submission.form_name) + '</h3><p>' + esc(S('forms_submission_date','Enviado')) + ': ' + esc(submission.submitted_at || '') + '</p></div><span class="sct-personal-status-badge is-success">' + esc(S('my_forms_submitted','Enviado')) + '</span></div><div class="sct-personal-card-actions"><button class="btn btn-outline-custom"><i class="bi bi-eye"></i> ' + esc(S('my_forms_open','Ver respuesta')) + '</button></div></div>';
            row.querySelector('button').addEventListener('click',function () { openHistory(submission.id_submission); });
            historyList.appendChild(row);
        });
    }

    function scopeCounts() {
        const allCards = Array.from(document.querySelectorAll('#availableList [data-form-scope], #historyList [data-form-scope]'));
        return {
            all: allCards.length,
            global: allCards.filter(function (card) { return card.dataset.formScope === 'global'; }).length,
            company: allCards.filter(function (card) { return card.dataset.formScope === 'company'; }).length
        };
    }

    function renderScopeFilter() {
        if (!scopeFilter) return;
        const counts = scopeCounts();
        const buttons = [
            ['all', S('forms_filter_all','Todos'), counts.all, 'bi-grid'],
            ['global', S('forms_filter_global','Global'), counts.global, 'bi-globe2'],
            ['company', companyLabel || S('forms_filter_company','Empresa'), counts.company, 'bi-building']
        ];
        scopeFilter.innerHTML = buttons.map(function (item) {
            const active = currentScope === item[0];
            return '<button type="button" class="sct-personal-filter-pill' + (active ? ' is-active' : '') + '" data-form-scope-filter="' + item[0] + '" aria-pressed="' + (active ? 'true' : 'false') + '"><i class="bi ' + item[3] + '" aria-hidden="true"></i>' + esc(item[1]) + '<span>' + item[2] + '</span></button>';
        }).join('');
        Array.from(scopeFilter.querySelectorAll('[data-form-scope-filter]')).forEach(function (button) {
            button.addEventListener('click', function () {
                currentScope = button.dataset.formScopeFilter || 'all';
                renderScopeFilter();
                applyScopeFilter();
            });
        });
    }

    function ensureFilterEmpty(section, key) {
        if (!section) return null;
        let empty = section.querySelector('[data-form-filter-empty="' + key + '"]');
        if (!empty) {
            empty = document.createElement('div');
            empty.className = 'sct-personal-empty sct-personal-filter-empty';
            empty.dataset.formFilterEmpty = key;
            empty.innerHTML = '<i class="bi bi-inbox" aria-hidden="true"></i><div><strong>' + esc(S('forms_filter_empty','No hay formularios para este filtro.')) + '</strong></div>';
            section.appendChild(empty);
        }
        return empty;
    }

    function applyScopeFilter() {
        const availableCards = Array.from(availableList.querySelectorAll('[data-form-scope]'));
        const historyCards = Array.from(historyList.querySelectorAll('[data-form-scope]'));
        const matches = function (card) { return currentScope === 'all' || card.dataset.formScope === currentScope; };
        let availableVisible = 0;
        let historyVisible = 0;
        availableCards.forEach(function (card) { const show = matches(card); card.hidden = !show; if (show) availableVisible++; });
        historyCards.forEach(function (card) { const show = matches(card); card.hidden = !show; if (show) historyVisible++; });

        const availableSection = availableList.closest('[data-forms-section="available"]');
        const historySection = historyList.closest('[data-forms-section="history"]');
        const availableEmpty = ensureFilterEmpty(availableSection, 'available');
        const historyEmpty = ensureFilterEmpty(historySection, 'history');
        if (availableEmpty) availableEmpty.hidden = availableVisible !== 0 || availableCards.length === 0;
        if (historyEmpty) historyEmpty.hidden = historyVisible !== 0 || historyCards.length === 0;
    }

    async function openForm(id) {
        hide(fillAlert);
        if (activitySession) { activitySession.setId(id); activitySession.enter(personalShell, fillPanel); }
        else { document.body.classList.add('sct-activity-mode'); if (personalShell) personalShell.classList.add('is-direct-activity'); }
        const response = await api('./formulario-publico-detalle.php?id=' + encodeURIComponent(id));
        if (!response.success) { leaveForm(false); show(myFormsAlert,response.message,'danger'); return; }
        currentForm = response.data;
        fillTitle.textContent = currentForm.name;
        fillDescription.textContent = currentForm.description || '';
        renderFields(currentForm.campos || []);
        restoreDraft();
        fillPanel.classList.remove('d-none');
        fillPanel.scrollIntoView({behavior:'smooth',block:'start'});
    }

    function renderFields(fields) {
        fillFields.innerHTML = '';
        fields.forEach(function (field) {
            const wrap = document.createElement('div');
            wrap.className = 'dynamic-field';
            wrap.dataset.idField = field.id_field;
            wrap.dataset.fieldType = field.field_type;
            const required = String(field.is_required) === '1';
            const req = required ? ' <span class="text-danger">*</span>' : '';
            const id = 'dyn_' + field.id_field;
            const options = (field.options || '').split('|').map(function (value) { return value.trim(); }).filter(Boolean);
            let input = '';
            if (field.field_type === 'text') input = '<textarea class="form-control dyn-value" id="' + id + '" rows="3" ' + (required ? 'required' : '') + '></textarea>';
            else if (field.field_type === 'number') input = '<input type="number" step="any" class="form-control dyn-value" id="' + id + '" ' + (required ? 'required' : '') + '>';
            else if (field.field_type === 'date') input = '<input type="date" class="form-control dyn-value" id="' + id + '" ' + (required ? 'required' : '') + '>';
            else if (field.field_type === 'select') input = '<select class="form-select dyn-value" id="' + id + '" ' + (required ? 'required' : '') + '><option value="">—</option>' + options.map(function (option) { return '<option value="' + esc(option) + '">' + esc(option) + '</option>'; }).join('') + '</select>';
            else if (field.field_type === 'checkbox') input = '<div class="dyn-checkboxes">' + options.map(function (option,index) { return '<div class="form-check"><input class="form-check-input dyn-check" type="checkbox" value="' + esc(option) + '" id="' + id + '_' + index + '"><label class="form-check-label" for="' + id + '_' + index + '">' + esc(option) + '</label></div>'; }).join('') + '</div>';
            else if (field.field_type === 'file') input = '<input type="file" class="form-control dyn-file" id="' + id + '" accept=".jpg,.jpeg,.png,.webp,.pdf,.txt,.doc,.docx,.xls,.xlsx" ' + (required ? 'required' : '') + '><div class="form-text">JPG, PNG, WebP, PDF, TXT, DOC/DOCX, XLS/XLSX · máx. 5 MB</div>';
            wrap.innerHTML = '<label class="form-label" for="' + id + '"><strong>' + esc(field.label) + '</strong>' + req + '</label>' + input;
            fillFields.appendChild(wrap);
        });
    }

    function collectDraft() {
        const answers = {};
        let hasFile = false;
        fillFields.querySelectorAll('.dynamic-field').forEach(function (wrap) {
            const id = wrap.dataset.idField;
            const type = wrap.dataset.fieldType;
            if (type === 'checkbox') answers[id] = Array.from(wrap.querySelectorAll('.dyn-check:checked')).map(function (input) { return input.value; });
            else if (type === 'file') { const input = wrap.querySelector('.dyn-file'); hasFile = hasFile || !!(input && input.files && input.files.length); }
            else { const input = wrap.querySelector('.dyn-value'); answers[id] = input ? input.value : ''; }
        });
        return {answers:answers,has_file:hasFile};
    }

    function saveDraft(notify) {
        const draft = collectDraft();
        const saved = activitySession && activitySession.save(draft);
        if (notify) show(fillAlert,(saved ? S('activity_draft_saved','Tus respuestas quedaron guardadas en este dispositivo para continuar después.') : S('forms_error_response','No se pudo guardar el avance.')) + (draft.has_file ? ' ' + S('activity_file_not_saved','Los archivos adjuntos deben seleccionarse nuevamente al continuar.') : ''),saved ? 'success' : 'danger');
    }

    function restoreDraft() {
        const draft = activitySession ? activitySession.load() : null;
        if (!draft || !draft.answers) return;
        Object.keys(draft.answers).forEach(function (id) {
            const wrap = fillFields.querySelector('[data-id-field="' + CSS.escape(id) + '"]');
            if (!wrap) return;
            if (wrap.dataset.fieldType === 'checkbox') {
                const values = Array.isArray(draft.answers[id]) ? draft.answers[id] : [];
                wrap.querySelectorAll('.dyn-check').forEach(function (input) { input.checked = values.includes(input.value); });
            } else {
                const input = wrap.querySelector('.dyn-value');
                if (input) input.value = draft.answers[id] == null ? '' : draft.answers[id];
            }
        });
        show(fillAlert,S('activity_draft_restored','Recuperamos las respuestas que habías guardado.') + (draft.has_file ? ' ' + S('activity_file_not_saved','Los archivos adjuntos deben seleccionarse nuevamente al continuar.') : ''),'info');
    }

    function leaveForm(saveFirst) {
        if (saveFirst && currentForm) saveDraft(false);
        fillPanel.classList.add('d-none');
        hide(fillAlert);
        if (activitySession) activitySession.exit(personalShell, fillPanel);
        else { document.body.classList.remove('sct-activity-mode'); if (personalShell) personalShell.classList.remove('is-direct-activity'); }
        currentForm = null;
    }

    if (fillSave) fillSave.addEventListener('click',function () { saveDraft(true); });
    if (fillClose) fillClose.addEventListener('click',function () { leaveForm(true); });
    if (fillForm) fillForm.addEventListener('submit',async function (event) {
        event.preventDefault();
        if (!currentForm) return;
        hide(fillAlert);
        const answers = {};
        let missing = false;
        let firstMissing = null;
        const formData = new FormData();
        formData.append('csrf_token',csrf);
        formData.append('id_form',String(currentForm.id_form));
        Array.from(fillFields.querySelectorAll('.dynamic-field')).forEach(function (wrap) {
            const id = wrap.dataset.idField;
            const type = wrap.dataset.fieldType;
            const field = (currentForm.campos || []).find(function (candidate) { return String(candidate.id_field) === String(id); });
            const required = field && String(field.is_required) === '1';
            if (type === 'checkbox') { const values = Array.from(wrap.querySelectorAll('.dyn-check:checked')).map(function (input) { return input.value; }); answers[id] = values; if (required && !values.length) { missing = true; firstMissing = firstMissing || wrap; } }
            else if (type === 'file') { const input = wrap.querySelector('.dyn-file'); if (input && input.files && input.files[0]) formData.append('file_' + id,input.files[0]); else if (required) { missing = true; firstMissing = firstMissing || wrap; } }
            else { const input = wrap.querySelector('.dyn-value'); const value = input ? input.value : ''; answers[id] = value; if (required && !String(value).trim()) { missing = true; firstMissing = firstMissing || wrap; } }
        });
        if (missing) { show(fillAlert,S('activity_complete_before_submit',S('my_forms_required','Completa todos los campos obligatorios.')),'warning'); if (firstMissing) firstMissing.scrollIntoView({behavior:'smooth',block:'center'}); return; }
        if (!await window.sctConfirmAction(S('activity_confirm_submit','¿Confirmas que deseas enviar? Revisa que todas tus respuestas estén completas.'))) return;
        formData.append('answers',JSON.stringify(answers));
        fillSubmit.disabled = true;
        const response = await api('./formulario-enviar.php',{method:'POST',body:formData});
        fillSubmit.disabled = false;
        if (!response.success) { show(fillAlert,response.message,'danger'); return; }
        if (activitySession) activitySession.clear();
        show(fillAlert,response.message || S('my_forms_submitted','Formulario enviado correctamente.'),'success');
        window.setTimeout(function () { fillForm.reset(); leaveForm(false); load(); },1200);
    });

    async function openHistory(id) {
        const response = await api('./envio-detalle.php?id_submission=' + encodeURIComponent(id));
        if (!response.success) { show(myFormsAlert,response.message,'danger'); return; }
        historyAnswers.innerHTML = '';
        (response.data.respuestas || []).forEach(function (answer) {
            const line = document.createElement('div');
            line.className = 'answer-line';
            let value = '';
            if (answer.file_path) value = '<a class="btn btn-outline-custom btn-sm" href="./archivo-descargar.php?id_answer=' + encodeURIComponent(answer.id_answer) + '">' + esc(S('forms_file_download','Descargar archivo')) + ' · ' + esc(answer.original_name || 'archivo') + '</a>';
            else if (Array.isArray(answer.display_value)) value = '<div class="answer-value">' + esc(answer.display_value.join(', ')) + '</div>';
            else value = '<div class="answer-value">' + esc(answer.display_value == null || answer.display_value === '' ? '—' : answer.display_value) + '</div>';
            line.innerHTML = '<div class="answer-label">' + esc(answer.label) + '</div>' + value;
            historyAnswers.appendChild(line);
        });
        historyDetail.classList.remove('d-none');
        historyDetail.scrollIntoView({behavior:'smooth'});
    }
    if (historyDetailClose) historyDetailClose.addEventListener('click',function () { historyDetail.classList.add('d-none'); });
    load();
});
