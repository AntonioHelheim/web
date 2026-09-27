/** Safety Control Tower - Ejecución de Protocolos MINSAL */
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
    const form = document.getElementById('protocolExecutionForm');
    const alert = document.getElementById('executionAlert');
    const submit = document.getElementById('executionSubmit');
    const save = document.getElementById('executionSave');
    const exitLink = document.getElementById('executionExit');
    let I18N = {};
    try { I18N = JSON.parse((document.getElementById('protocolExecutionI18n') || {}).textContent || '{}'); } catch (_) {}
    const errorMessage = I18N.error || 'Respuesta inválida del servidor.';
    const assignmentId = root.dataset.assignmentId || '0';
    const activitySession = window.SCTActivitySession ? window.SCTActivitySession.create({root:root,type:'protocol',id:assignmentId}) : null;
    const embeddedActivity = new URLSearchParams(window.location.search).get('embedded') === '1';

    function show(message,variant) {
        if (!alert) return;
        alert.textContent = message || '';
        alert.classList.remove('d-none','alert-success','alert-danger','alert-warning','alert-info');
        alert.classList.add('alert-' + (variant || 'danger'));
        alert.scrollIntoView({behavior:'smooth',block:'center'});
    }
    function updateOptional(section,toggle) {
        const on = toggle.checked;
        section.classList.toggle('optional-off',!on);
        section.querySelectorAll('.protocol-answer,.protocol-checkbox,.protocol-file').forEach(function (control) { control.disabled = !on; });
    }
    document.querySelectorAll('.protocol-form-toggle').forEach(function (toggle) {
        const section = toggle.closest('[data-protocol-form]');
        toggle.addEventListener('change',function () { updateOptional(section,toggle); });
        updateOptional(section,toggle);
    });
    if (!form) return;

    function collectDraft() {
        const values = {};
        const checks = {};
        const included = {};
        let hasFile = false;
        document.querySelectorAll('[data-protocol-form]').forEach(function (section) {
            const sectionId = section.dataset.protocolForm;
            const toggle = section.querySelector('.protocol-form-toggle');
            included[sectionId] = !toggle || toggle.checked;
            section.querySelectorAll('.protocol-answer').forEach(function (input) { values[input.dataset.fieldId] = input.value; });
            section.querySelectorAll('.protocol-checkbox').forEach(function (input) {
                const id = input.dataset.fieldId;
                if (!checks[id]) checks[id] = [];
                if (input.checked) checks[id].push(input.value);
            });
            section.querySelectorAll('.protocol-file').forEach(function (input) { hasFile = hasFile || !!(input.files && input.files.length); });
        });
        return {values:values,checks:checks,included:included,has_file:hasFile};
    }

    function saveDraft(notify) {
        const draft = collectDraft();
        const saved = activitySession && activitySession.save(draft);
        if (notify) show((saved ? (I18N.save || 'Tus respuestas quedaron guardadas en este dispositivo para continuar después.') : errorMessage) + (draft.has_file ? ' ' + (I18N.file || 'Los archivos adjuntos deben seleccionarse nuevamente al continuar.') : ''),saved ? 'success' : 'danger');
    }

    function restoreDraft() {
        const draft = activitySession ? activitySession.load() : null;
        if (!draft) return;
        Object.keys(draft.included || {}).forEach(function (sectionId) {
            const section = document.querySelector('[data-protocol-form="' + CSS.escape(sectionId) + '"]');
            const toggle = section ? section.querySelector('.protocol-form-toggle') : null;
            if (toggle) { toggle.checked = !!draft.included[sectionId]; updateOptional(section,toggle); }
        });
        Object.keys(draft.values || {}).forEach(function (fieldId) {
            const input = document.querySelector('.protocol-answer[data-field-id="' + CSS.escape(fieldId) + '"]');
            if (input) input.value = draft.values[fieldId] == null ? '' : draft.values[fieldId];
        });
        Object.keys(draft.checks || {}).forEach(function (fieldId) {
            const values = Array.isArray(draft.checks[fieldId]) ? draft.checks[fieldId] : [];
            document.querySelectorAll('.protocol-checkbox[data-field-id="' + CSS.escape(fieldId) + '"]').forEach(function (input) { input.checked = values.includes(input.value); });
        });
        show((I18N.restore || 'Recuperamos las respuestas que habías guardado.') + (draft.has_file ? ' ' + (I18N.file || 'Los archivos adjuntos deben seleccionarse nuevamente al continuar.') : ''),'info');
    }

    if (save) save.addEventListener('click',function () { saveDraft(true); });
    if (exitLink) exitLink.addEventListener('click',function (event) {
        saveDraft(false);
        if (!embeddedActivity) return;
        event.preventDefault();
        if (activitySession) activitySession.exit();
        else if (window.parent && window.parent !== window) window.parent.postMessage({type:'sct-activity-exit'}, window.location.origin);
    });

    form.addEventListener('submit',async function (event) {
        event.preventDefault();
        if (alert) alert.classList.add('d-none');
        const answers = {};
        const included = [];
        let invalid = false;
        let firstInvalid = null;
        document.querySelectorAll('[data-protocol-form]').forEach(function (section) {
            const required = section.dataset.required === '1';
            const toggle = section.querySelector('.protocol-form-toggle');
            const on = required || !toggle || toggle.checked;
            if (!on) return;
            included.push(parseInt(section.dataset.protocolForm,10));
            section.querySelectorAll('.protocol-answer:not(:disabled)').forEach(function (input) {
                const id = input.dataset.fieldId;
                answers[id] = input.value;
                if (input.required && !input.value) { invalid = true; firstInvalid = firstInvalid || input; }
            });
            const checkboxMap = {};
            section.querySelectorAll('.protocol-checkbox:not(:disabled)').forEach(function (input) {
                const id = input.dataset.fieldId;
                if (!checkboxMap[id]) checkboxMap[id] = [];
                if (input.checked) checkboxMap[id].push(input.value);
            });
            Object.keys(checkboxMap).forEach(function (id) {
                answers[id] = checkboxMap[id];
                const block = section.querySelector('[data-field-id="' + id + '"][data-field-type="checkbox"]');
                if (block && block.querySelector('.required-mark') && checkboxMap[id].length === 0) { invalid = true; firstInvalid = firstInvalid || block; }
            });
            section.querySelectorAll('.protocol-file:not(:disabled)').forEach(function (input) {
                if (input.required && (!input.files || !input.files.length)) { invalid = true; firstInvalid = firstInvalid || input; }
            });
        });
        if (invalid) { show(I18N.required || 'Completa todos los campos obligatorios antes de enviar los resultados.','warning'); if (firstInvalid) firstInvalid.scrollIntoView({behavior:'smooth',block:'center'}); return; }
        if (!await window.sctConfirmAction(I18N.confirm || '¿Confirmas que deseas enviar? Revisa que todas tus respuestas estén completas.')) return;
        const formData = new FormData(form);
        formData.set('csrf_token',root.dataset.csrfToken || '');
        formData.set('id_protocol_assignment',assignmentId);
        formData.set('answers',JSON.stringify(answers));
        formData.set('included_forms',JSON.stringify(included));
        submit.disabled = true;
        try {
            const response = await fetch('./ejecucion-enviar.php',{method:'POST',body:formData});
            const result = await response.json();
            submit.disabled = false;
            if (!result.success) { show(result.message || errorMessage,'danger'); return; }
            if (activitySession) activitySession.clear();
            show(result.message || I18N.success || 'Ejecución registrada y enviada a revisión.','success');
            window.setTimeout(function () {
                if (embeddedActivity) {
                    if (activitySession) activitySession.exit();
                    else if (window.parent && window.parent !== window) window.parent.postMessage({type:'sct-activity-exit'}, window.location.origin);
                } else {
                    location.href = './mis-protocolos.php';
                }
            },1500);
        } catch (_) {
            submit.disabled = false;
            show(errorMessage,'danger');
        }
    });

    restoreDraft();
});
