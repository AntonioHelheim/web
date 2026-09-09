/**
 * Safety Control Tower - Protocolos MINSAL / Gestión
 * Etapa 2
 */
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('.container[data-csrf-token]');
    if (!root) return;

    const csrfToken = root.dataset.csrfToken || '';
    const isGlobalAdmin = root.dataset.isGlobalAdmin === '1';
    const $ = (id) => document.getElementById(id);

    let I18N = {};
    try {
        I18N = JSON.parse(($('protocolsI18n') || {}).textContent || '{}');
    } catch (_) {}
    const S = (key, fallback) => I18N[key] || fallback || key;

    const els = {
        language: $('pageLanguageSelect'), company: $('companySelect'),
        alert: $('protocolAlert'), status: $('protocolStatus'), tableWrap: $('protocolTableWrap'), tableBody: $('protocolTableBody'),
        form: $('protocolForm'), mode: $('protocolMode'), target: $('protocolTarget'), formTitle: $('protocolFormTitle'),
        code: $('protocolCode'), name: $('protocolName'), version: $('protocolVersion'), authority: $('protocolAuthority'),
        reference: $('protocolReference'), source: $('protocolSource'), from: $('protocolFrom'), until: $('protocolUntil'),
        description: $('protocolDescription'), parameters: $('protocolParameters'), submit: $('protocolSubmitBtn'), cancelEdit: $('protocolCancelEdit'),
        detail: $('protocolDetail'), detailClose: $('detailClose'), detailAlert: $('detailAlert'), detailLocked: $('detailLocked'),
        detailName: $('detailName'), detailScope: $('detailScope'), detailAuthority: $('detailAuthority'), detailReference: $('detailReference'),
        detailVersion: $('detailVersion'), detailSource: $('detailSource'), detailParameters: $('detailParameters'),
        formsList: $('protocolFormsList'), linkForm: $('protocolFormLink'), linkSelect: $('linkFormSelect'), linkOrder: $('linkOrder'), linkRequired: $('linkRequired'),
        assignmentSection: $('assignmentSection'), assignmentForm: $('assignmentForm'), assignCenter: $('assignCenter'), assignProject: $('assignProject'),
        assignWorker: $('assignWorker'), assignResponsible: $('assignResponsible'), assignStart: $('assignStart'), assignDue: $('assignDue'),
        assignRecurrence: $('assignRecurrence'), assignInterval: $('assignInterval'), assignOverrides: $('assignOverrides'), assignNotes: $('assignNotes'),
        assignmentsList: $('assignmentsList'), assignmentDetail: $('assignmentDetail'), assignmentDetailClose: $('assignmentDetailClose'),
        assignmentDetailTitle: $('assignmentDetailTitle'), executionsList: $('executionsList'), executionReview: $('executionReview'),
        executionAnswers: $('executionAnswers'), reviewForm: $('reviewForm'), reviewExecutionId: $('reviewExecutionId'), reviewResult: $('reviewResult'), reviewNotes: $('reviewNotes'),
        trackingList: $('trackingList'), trackingForm: $('trackingForm'), trackingExecutionId: $('trackingExecutionId'), trackingDescription: $('trackingDescription'),
        trackingResponsible: $('trackingResponsible'), trackingCommitment: $('trackingCommitment'), trackingDeadline: $('trackingDeadline')
    };

    let scopeCompany = null;
    let currentProtocol = null;
    let currentAssignment = null;
    let assignmentRows = new Map();
    let companyCatalogs = null;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function showAlert(el, message, variant) {
        if (!el) return;
        el.textContent = message || '';
        el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info', 'alert-warning');
        el.classList.add('alert-' + (variant || 'danger'));
    }

    function hideAlert(el) {
        if (el) el.classList.add('d-none');
    }

    async function api(url, options) {
        try {
            const response = await fetch(url, options || {});
            const data = await response.json();
            if (typeof data.success === 'undefined') throw new Error('invalid');
            return data;
        } catch (_) {
            return { success: false, message: S('error_response', 'Respuesta inválida del servidor.') };
        }
    }

    function postJson(url, data) {
        return api(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.assign({ csrf_token: csrfToken }, data || {}))
        });
    }

    function scopeQuery() {
        return scopeCompany ? ('&id_company=' + encodeURIComponent(scopeCompany)) : '';
    }

    function stateLabel(state) {
        return S('state_' + state, state || '—');
    }

    function resultLabel(result) {
        const key = result === 'pendiente_revision' ? 'pending_review' : result;
        return S(key || 'pending_review', result || S('pending_review', 'Pendiente de revisión'));
    }

    function resultClass(result) {
        if (result === 'conforme' || result === 'no_aplica') return 'ok';
        if (result === 'observado' || result === 'pending_review' || result === 'pendiente_revision') return 'warn';
        if (result === 'no_conforme') return 'danger';
        return '';
    }

    function protocolScopeBadge(protocol) {
        if (protocol.id_company == null) {
            return '<span class="scope-badge global"><i class="bi bi-globe2"></i>' + escapeHtml(S('global', 'Global')) + '</span>';
        }
        return '<span class="scope-badge"><i class="bi bi-building"></i>' + escapeHtml(protocol.company_name || S('company', 'Empresa')) + '</span>';
    }

    function formatDateTime(value) {
        if (!value) return '—';
        return String(value).replace('T', ' ').substring(0, 16);
    }

    function toInputDateTime(date) {
        const pad = n => String(n).padStart(2, '0');
        const d = date instanceof Date ? date : new Date(date);
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function setDefaultDates() {
        const now = new Date();
        const due = new Date(now.getTime());
        due.setDate(due.getDate() + 30);
        if (els.assignStart) els.assignStart.value = toInputDateTime(now);
        if (els.assignDue) els.assignDue.value = toInputDateTime(due);
        const commitment = new Date(now.getTime());
        commitment.setDate(commitment.getDate() + 1);
        const trackingDue = new Date(now.getTime());
        trackingDue.setDate(trackingDue.getDate() + 7);
        if (els.trackingCommitment) els.trackingCommitment.value = toInputDateTime(commitment);
        if (els.trackingDeadline) els.trackingDeadline.value = toInputDateTime(trackingDue);
    }

    function resetDefinitionForm() {
        els.form.reset();
        els.mode.value = 'create';
        els.target.value = '';
        els.version.value = '1';
        els.authority.value = 'Ministerio de Salud de Chile';
        els.parameters.value = '{\n  "rules_mode": "manual",\n  "requires_professional_validation": true\n}';
        els.cancelEdit.classList.add('d-none');
        // Mantiene el encabezado corto y neutral en todos los idiomas.
        els.formTitle.textContent = els.formTitle.dataset.defaultTitle || els.formTitle.textContent;
    }

    function currentCreateOwner() {
        return isGlobalAdmin ? scopeCompany : null;
    }

    async function loadCompanies() {
        if (!isGlobalAdmin || !els.company) return;
        const r = await api('./empresas-disponibles.php');
        if (!r.success) {
            showAlert(els.alert, r.message, 'danger');
            return;
        }
        (r.data || []).forEach(c => {
            const option = document.createElement('option');
            option.value = String(c.id_company);
            option.textContent = c.razon_social;
            els.company.appendChild(option);
        });
    }

    function updateScopeFromSelector() {
        if (!isGlobalAdmin || !els.company) {
            scopeCompany = null;
            return;
        }
        scopeCompany = els.company.value === 'global' ? null : parseInt(els.company.value, 10) || null;
    }

    async function loadProtocols() {
        hideAlert(els.alert);
        els.status.classList.remove('d-none', 'alert-danger');
        els.status.classList.add('alert-info');
        els.status.textContent = S('loading', 'Cargando protocolos...');
        els.tableWrap.classList.add('d-none');
        closeDetail();
        resetDefinitionForm();

        let url = './protocolos-listar.php';
        if (scopeCompany) url += '?id_company=' + encodeURIComponent(scopeCompany);
        const r = await api(url);
        if (!r.success) {
            els.status.classList.remove('alert-info');
            els.status.classList.add('alert-danger');
            els.status.textContent = r.message || S('error_response');
            return;
        }
        const rows = r.data || [];
        if (!rows.length) {
            els.status.textContent = S('empty', 'No hay protocolos configurados en este alcance.');
            return;
        }
        els.status.classList.add('d-none');
        els.tableWrap.classList.remove('d-none');
        renderProtocols(rows);
    }

    function renderProtocols(rows) {
        els.tableBody.innerHTML = '';
        rows.forEach(protocol => {
            const canEditDefinition = isGlobalAdmin || protocol.id_company != null;
            const tr = document.createElement('tr');
            const state = parseInt(protocol.state, 10) === 1;
            const actions = [
                '<button type="button" class="btn btn-outline-custom btn-sm" data-action="manage">' + escapeHtml(S('manage', 'Gestionar')) + '</button>'
            ];
            if (canEditDefinition) {
                actions.push('<button type="button" class="btn btn-outline-custom btn-sm" data-action="edit">' + escapeHtml(S('edit', 'Editar')) + '</button>');
                actions.push('<button type="button" class="btn btn-outline-custom btn-sm" data-action="toggle" data-state="' + (state ? '0' : '1') + '">' + escapeHtml(state ? S('deactivate', 'Desactivar') : S('reactivate', 'Reactivar')) + '</button>');
            }
            tr.innerHTML =
                '<td><code>' + escapeHtml(protocol.code) + '</code></td>' +
                '<td><strong>' + escapeHtml(protocol.name) + '</strong><div class="text-muted small">v' + escapeHtml(protocol.version || 1) + '</div></td>' +
                '<td>' + protocolScopeBadge(protocol) + '</td>' +
                '<td>' + escapeHtml(protocol.form_count || 0) + '</td>' +
                '<td>' + escapeHtml(protocol.assignment_count || 0) + '</td>' +
                '<td><span class="status-pill ' + (state ? 'ok' : 'danger') + '">' + escapeHtml(state ? S('active', 'Activo') : S('inactive', 'Inactivo')) + '</span></td>' +
                '<td><div class="d-flex gap-1 flex-wrap">' + actions.join('') + '</div></td>';

            tr.querySelector('[data-action="manage"]').addEventListener('click', () => openDetail(protocol.id_protocol));
            const edit = tr.querySelector('[data-action="edit"]');
            if (edit) edit.addEventListener('click', () => editProtocol(protocol));
            const toggle = tr.querySelector('[data-action="toggle"]');
            if (toggle) toggle.addEventListener('click', () => toggleProtocol(protocol.id_protocol, parseInt(toggle.dataset.state, 10)));
            els.tableBody.appendChild(tr);
        });
    }

    function editProtocol(protocol) {
        if (!isGlobalAdmin && protocol.id_company == null) {
            showAlert(els.alert, S('global_readonly', 'La definición global es de solo lectura.'), 'warning');
            return;
        }
        els.mode.value = 'edit';
        els.target.value = protocol.id_protocol;
        els.code.value = protocol.code || '';
        els.name.value = protocol.name || '';
        els.version.value = protocol.version || 1;
        els.authority.value = protocol.authority || '';
        els.reference.value = protocol.normative_reference || '';
        els.source.value = protocol.source_url || '';
        els.from.value = protocol.effective_date_from || '';
        els.until.value = protocol.effective_date_until || '';
        els.description.value = protocol.description || '';
        try {
            els.parameters.value = protocol.parameters ? JSON.stringify(JSON.parse(protocol.parameters), null, 2) : '{}';
        } catch (_) {
            els.parameters.value = protocol.parameters || '{}';
        }
        els.cancelEdit.classList.remove('d-none');
        els.form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    async function toggleProtocol(id, state) {
        if (!confirm(S('confirm_state', '¿Confirmas este cambio de estado?'))) return;
        const r = await postJson('./protocolos-cambiar-estado.php', { id_protocol: id, state: state });
        if (!r.success) {
            showAlert(els.alert, r.message, 'danger');
            return;
        }
        showAlert(els.alert, r.message || S('created'), 'success');
        loadProtocols();
    }

    async function openDetail(id) {
        hideAlert(els.detailAlert);
        let url = './protocolos-detalle.php?id_protocol=' + encodeURIComponent(id);
        if (scopeCompany) url += '&id_company=' + encodeURIComponent(scopeCompany);
        const r = await api(url);
        if (!r.success) {
            showAlert(els.alert, r.message, 'danger');
            return;
        }
        currentProtocol = r.data;
        els.detail.classList.add('active');
        els.detailName.textContent = currentProtocol.name || '-';
        els.detailScope.innerHTML = protocolScopeBadge(currentProtocol);
        els.detailAuthority.textContent = currentProtocol.authority || '—';
        els.detailReference.textContent = currentProtocol.normative_reference || '—';
        els.detailVersion.textContent = 'v' + (currentProtocol.version || 1);
        els.detailSource.innerHTML = currentProtocol.source_url
            ? '<a href="' + escapeHtml(currentProtocol.source_url) + '" target="_blank" rel="noopener noreferrer">' + escapeHtml(currentProtocol.source_url) + '</a>'
            : '—';
        try {
            els.detailParameters.textContent = currentProtocol.parameters ? JSON.stringify(JSON.parse(currentProtocol.parameters), null, 2) : '{}';
        } catch (_) {
            els.detailParameters.textContent = currentProtocol.parameters || '{}';
        }

        const locked = !!currentProtocol.definition_locked;
        els.detailLocked.classList.toggle('d-none', !locked);

        renderProtocolForms(currentProtocol.formularios || []);
        renderAssignments(currentProtocol.asignaciones || []);

        const implementationCompany = parseInt(currentProtocol.implementation_company || 0, 10) || null;
        els.assignmentSection.classList.toggle('d-none', !implementationCompany);
        if (implementationCompany) {
            await loadCompanyCatalogs(implementationCompany);
        } else {
            companyCatalogs = null;
        }
        await loadAvailableForms(implementationCompany);
        updateCompositionEditing(locked, implementationCompany);
        setDefaultDates();
        els.detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function updateCompositionEditing(locked, implementationCompany) {
        const scopeAssignments = (currentProtocol && currentProtocol.asignaciones || []).length > 0;
        const compositionLocked = scopeAssignments || (locked && currentProtocol && (currentProtocol.id_company != null || !implementationCompany));
        const controls = els.linkForm ? Array.from(els.linkForm.querySelectorAll('input,select,button')) : [];
        controls.forEach(c => c.disabled = compositionLocked);
        if (!implementationCompany && !isGlobalAdmin) controls.forEach(c => c.disabled = true);
        // Global protocol definition may be implemented by company managers, but not edited.
        if (!isGlobalAdmin && currentProtocol && currentProtocol.id_company == null) {
            showAlert(els.detailAlert, S('global_readonly', 'La definición global es de solo lectura; puedes implementarla para tu empresa.'), 'info');
        }
    }

    async function loadAvailableForms(implementationCompany) {
        let url = './formularios-disponibles.php';
        if (implementationCompany) url += '?id_company=' + encodeURIComponent(implementationCompany);
        const r = await api(url);
        els.linkSelect.innerHTML = '';
        if (!r.success) {
            const o = document.createElement('option'); o.value = ''; o.textContent = r.message || '—'; els.linkSelect.appendChild(o); return;
        }
        const already = new Set((currentProtocol && currentProtocol.formularios || []).map(x => String(x.id_form)));
        const items = (r.data || []).filter(f => !already.has(String(f.id_form)) && parseInt(f.field_count || 0, 10) > 0);
        if (!items.length) {
            const o = document.createElement('option'); o.value = ''; o.textContent = S('no_forms', 'No hay formularios disponibles.'); els.linkSelect.appendChild(o); return;
        }
        items.forEach(f => {
            const o = document.createElement('option');
            o.value = f.id_form;
            o.textContent = (f.id_company == null ? '[' + S('global', 'Global') + '] ' : '') + f.name + ' (' + (f.field_count || 0) + ')';
            els.linkSelect.appendChild(o);
        });
    }

    function renderProtocolForms(forms) {
        if (!forms.length) {
            els.formsList.innerHTML = '<p class="text-muted small">' + escapeHtml(S('no_forms', 'No hay formularios vinculados.')) + '</p>';
            return;
        }
        els.formsList.innerHTML = '';
        forms.forEach(link => {
            const row = document.createElement('div');
            row.className = 'protocol-form-row';
            const scope = link.id_company == null ? S('global', 'Global') : (link.form_company_name || S('company', 'Empresa'));
            const canRemove = !((currentProtocol && currentProtocol.asignaciones || []).length > 0);
            row.innerHTML = '<div class="d-flex justify-content-between gap-3 flex-wrap"><div><strong>' + escapeHtml(link.form_name) + '</strong>' +
                '<div class="text-muted small">' + escapeHtml(scope) + ' · ' + escapeHtml(link.field_count || 0) + ' · ' + escapeHtml(parseInt(link.is_required, 10) === 1 ? S('required', 'Obligatorio') : S('optional', 'Opcional')) + '</div></div>' +
                (canRemove ? '<button type="button" class="btn btn-outline-danger btn-sm" data-action="remove">' + escapeHtml(S('remove', 'Quitar')) + '</button>' : '') + '</div>';
            const b = row.querySelector('[data-action="remove"]');
            if (b) b.addEventListener('click', () => removeProtocolForm(link.id_protocol_form));
            els.formsList.appendChild(row);
        });
    }

    async function removeProtocolForm(idProtocolForm) {
        if (!confirm(S('confirm_remove_form', '¿Deseas desvincular este formulario?'))) return;
        const r = await postJson('./protocolo-formularios-quitar.php', { id_protocol_form: idProtocolForm });
        if (!r.success) { showAlert(els.detailAlert, r.message, 'danger'); return; }
        showAlert(els.detailAlert, r.message || S('linked'), 'success');
        await openDetail(currentProtocol.id_protocol);
    }

    async function loadCompanyCatalogs(company) {
        let url = './catalogos-empresa.php';
        if (isGlobalAdmin) url += '?id_company=' + encodeURIComponent(company);
        const r = await api(url);
        if (!r.success) {
            showAlert(els.detailAlert, r.message, 'danger');
            return;
        }
        companyCatalogs = r.data || {};
        fillSelect(els.assignCenter, companyCatalogs.centers || [], true);
        fillSelect(els.assignProject, companyCatalogs.projects || [], true);
        fillSelect(els.assignWorker, companyCatalogs.workers || [], true, item => item.name + (item.rut ? ' · ' + item.rut : ''));
        fillSelect(els.assignResponsible, companyCatalogs.users || [], false, item => item.name + ' · ' + item.id);
        fillSelect(els.trackingResponsible, companyCatalogs.users || [], true, item => item.name + ' · ' + item.id);
    }

    function fillSelect(select, items, blank, formatter) {
        if (!select) return;
        select.innerHTML = '';
        if (blank) {
            const o = document.createElement('option'); o.value = ''; o.textContent = '—'; select.appendChild(o);
        }
        items.forEach(item => {
            const o = document.createElement('option'); o.value = item.id; o.textContent = formatter ? formatter(item) : item.name; select.appendChild(o);
        });
    }

    function renderAssignments(rows) {
        assignmentRows = new Map();
        rows.forEach(r => assignmentRows.set(String(r.id_protocol_assignment), r));
        if (!rows.length) {
            els.assignmentsList.innerHTML = '<p class="text-muted small">' + escapeHtml(S('no_assignments', 'No hay asignaciones para esta empresa.')) + '</p>';
            return;
        }
        els.assignmentsList.innerHTML = '';
        rows.forEach(a => {
            const row = document.createElement('div');
            row.className = 'assignment-row';
            const target = [a.center_name, a.project_name, a.worker_name ? (a.worker_name + ' ' + (a.worker_lastname || '')) : ''].filter(Boolean).join(' · ') || S('company_wide', 'Empresa completa');
            const latest = a.latest_result ? '<span class="status-pill ' + resultClass(a.latest_result) + '">' + escapeHtml(resultLabel(a.latest_result)) + '</span>' : '';
            const overdue = parseInt(a.is_overdue, 10) === 1 ? '<span class="status-pill danger">' + escapeHtml(S('overdue', 'Vencido')) + '</span>' : '';
            const state = String(a.state || 'activa');
            let actions = '<button type="button" class="btn btn-outline-custom btn-sm" data-action="view">' + escapeHtml(S('view', 'Ver')) + '</button>';
            if (state === 'activa') {
                actions += '<button type="button" class="btn btn-outline-custom btn-sm" data-state="suspendida">' + escapeHtml(S('suspend', 'Suspender')) + '</button>';
                actions += '<button type="button" class="btn btn-outline-custom btn-sm" data-state="cerrada">' + escapeHtml(S('close_assignment', 'Cerrar')) + '</button>';
                actions += '<button type="button" class="btn btn-outline-danger btn-sm" data-state="cancelada">' + escapeHtml(S('cancel_assignment', 'Cancelar')) + '</button>';
            } else if (state === 'suspendida') {
                actions += '<button type="button" class="btn btn-outline-custom btn-sm" data-state="activa">' + escapeHtml(S('activate', 'Activar')) + '</button>';
                actions += '<button type="button" class="btn btn-outline-danger btn-sm" data-state="cancelada">' + escapeHtml(S('cancel_assignment', 'Cancelar')) + '</button>';
            }
            row.innerHTML = '<div class="d-flex justify-content-between gap-3 flex-wrap"><div><strong>' + escapeHtml(a.responsible_name + ' ' + (a.responsible_lastname || '')) + '</strong>' +
                '<div class="text-muted small">' + escapeHtml(target) + '</div><div class="small mt-1">' + escapeHtml(formatDateTime(a.next_due_at)) + ' · ' + escapeHtml(stateLabel(state)) + ' ' + overdue + ' ' + latest + '</div></div>' +
                '<div class="d-flex gap-1 flex-wrap">' + actions + '</div></div>';
            row.querySelector('[data-action="view"]').addEventListener('click', () => openAssignment(a));
            row.querySelectorAll('[data-state]').forEach(b => b.addEventListener('click', () => changeAssignmentState(a.id_protocol_assignment, b.dataset.state)));
            els.assignmentsList.appendChild(row);
        });
    }

    async function changeAssignmentState(id, state) {
        if (!confirm(S('confirm_state', '¿Confirmas este cambio de estado?'))) return;
        const r = await postJson('./asignacion-cambiar-estado.php', { id_protocol_assignment: id, state: state });
        if (!r.success) { showAlert(els.detailAlert, r.message, 'danger'); return; }
        showAlert(els.detailAlert, r.message, 'success');
        openDetail(currentProtocol.id_protocol);
    }

    async function openAssignment(assignment) {
        currentAssignment = assignment;
        els.assignmentDetail.classList.add('active');
        els.executionReview.classList.remove('active');
        els.assignmentDetailTitle.textContent = assignment.responsible_name + ' ' + (assignment.responsible_lastname || '') + ' · ' + formatDateTime(assignment.next_due_at);
        if (els.trackingExecutionId) els.trackingExecutionId.value = '';
        await Promise.all([loadExecutions(assignment.id_protocol_assignment), loadTracking(assignment.id_protocol_assignment)]);
        els.assignmentDetail.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    async function loadExecutions(idAssignment) {
        const r = await api('./ejecuciones-listar.php?id_protocol_assignment=' + encodeURIComponent(idAssignment));
        if (!r.success) { els.executionsList.innerHTML = '<div class="alert alert-danger">' + escapeHtml(r.message) + '</div>'; return; }
        const rows = r.data || [];
        if (!rows.length) { els.executionsList.innerHTML = '<p class="text-muted small">' + escapeHtml(S('no_executions', 'Todavía no existen ejecuciones.')) + '</p>'; return; }
        els.executionsList.innerHTML = '';
        rows.forEach(e => {
            const row = document.createElement('div'); row.className = 'execution-row';
            row.innerHTML = '<div class="d-flex justify-content-between gap-3 flex-wrap"><div><strong>' + escapeHtml(S('cycle', 'Ciclo')) + ' ' + escapeHtml(e.cycle_number) + '</strong>' +
                '<div class="text-muted small">' + escapeHtml(S('submitted', 'Enviado')) + ': ' + escapeHtml(formatDateTime(e.submitted_at)) + '</div><span class="status-pill ' + resultClass(e.result) + '">' + escapeHtml(resultLabel(e.result)) + '</span></div>' +
                '<button type="button" class="btn btn-outline-custom btn-sm">' + escapeHtml(S('view', 'Ver')) + '</button></div>';
            row.querySelector('button').addEventListener('click', () => openExecution(e.id_protocol_execution));
            els.executionsList.appendChild(row);
        });
    }

    async function openExecution(idExecution) {
        const r = await api('./ejecucion-detalle.php?id_protocol_execution=' + encodeURIComponent(idExecution));
        if (!r.success) { showAlert(els.detailAlert, r.message, 'danger'); return; }
        const e = r.data;
        els.executionAnswers.innerHTML = '';
        (e.submissions || []).forEach(s => {
            const wrapper = document.createElement('div');
            wrapper.className = 'mb-3';
            let html = '<h5 class="h6">' + escapeHtml(s.form_name) + '</h5>';
            const answers = s.detalle && s.detalle.respuestas ? s.detalle.respuestas : [];
            answers.forEach(a => {
                let display = a.display_value;
                if (Array.isArray(display)) display = display.join(', ');
                if (a.file_path) {
                    display = '<a href="../formularios/archivo-descargar.php?id_answer=' + encodeURIComponent(a.id_answer) + '"><i class="bi bi-download"></i> ' + escapeHtml(a.original_name || 'Archivo') + '</a>';
                } else {
                    display = escapeHtml(display == null || display === '' ? '—' : display);
                }
                html += '<div class="answer-line"><div class="answer-label">' + escapeHtml(a.label) + '</div><div class="answer-value">' + display + '</div></div>';
            });
            wrapper.innerHTML = html;
            els.executionAnswers.appendChild(wrapper);
        });
        els.reviewExecutionId.value = e.id_protocol_execution;
        els.reviewResult.value = e.result === 'pendiente_revision' ? 'conforme' : e.result;
        els.reviewNotes.value = e.review_notes || '';
        els.executionReview.classList.add('active');
        Array.from(els.reviewForm.querySelectorAll('select,textarea,button')).forEach(c => c.disabled = e.result !== 'pendiente_revision');
        if (els.trackingExecutionId) els.trackingExecutionId.value = e.id_protocol_execution;
        els.executionReview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    async function loadTracking(idAssignment) {
        const r = await api('./tracking-listar.php?id_protocol_assignment=' + encodeURIComponent(idAssignment));
        if (!r.success) { els.trackingList.innerHTML = '<div class="alert alert-danger">' + escapeHtml(r.message) + '</div>'; return; }
        const rows = r.data || [];
        if (!rows.length) { els.trackingList.innerHTML = '<p class="text-muted small">' + escapeHtml(S('no_tracking', 'No hay acciones de seguimiento registradas.')) + '</p>'; return; }
        els.trackingList.innerHTML = '';
        rows.forEach(t => {
            const row = document.createElement('div'); row.className = 'tracking-row';
            const statusKey = 'tracking_' + t.status;
            let actions = '';
            if (t.status === 'pendiente') actions = '<button type="button" class="btn btn-outline-custom btn-sm" data-status="en_curso">' + escapeHtml(S('tracking_en_curso', 'En curso')) + '</button>';
            if (t.status === 'en_curso') actions = '<button type="button" class="btn btn-outline-custom btn-sm" data-status="completado">' + escapeHtml(S('tracking_completado', 'Completado')) + '</button>';
            if (t.status !== 'completado' && t.status !== 'cancelado') actions += '<button type="button" class="btn btn-outline-danger btn-sm" data-status="cancelado">' + escapeHtml(S('tracking_cancelado', 'Cancelado')) + '</button>';
            row.innerHTML = '<div class="d-flex justify-content-between gap-3 flex-wrap"><div><strong>' + escapeHtml(t.description) + '</strong><div class="text-muted small">' + escapeHtml((t.responsible_name ? t.responsible_name + ' ' + (t.responsible_lastname || '') : '—')) + ' · ' + escapeHtml(formatDateTime(t.deadline)) + '</div><span class="status-pill">' + escapeHtml(S(statusKey, t.status)) + '</span></div><div class="d-flex gap-1 flex-wrap">' + actions + '</div></div>';
            row.querySelectorAll('[data-status]').forEach(b => b.addEventListener('click', () => changeTrackingState(t.id_protocol_tracking, b.dataset.status)));
            els.trackingList.appendChild(row);
        });
    }

    async function changeTrackingState(id, status) {
        const r = await postJson('./tracking-cambiar-estado.php', { id_protocol_tracking: id, status: status });
        if (!r.success) { showAlert(els.detailAlert, r.message, 'danger'); return; }
        await loadTracking(currentAssignment.id_protocol_assignment);
    }

    // --- Eventos de UI ---------------------------------------------------
    if (els.company) els.company.addEventListener('change', () => {
        updateScopeFromSelector();
        loadProtocols();
    });

    els.form.addEventListener('submit', async (event) => {
        event.preventDefault(); hideAlert(els.alert);
        let parameters = els.parameters.value.trim();
        if (parameters) {
            try { const parsed = JSON.parse(parameters); if (!parsed || Array.isArray(parsed) || typeof parsed !== 'object') throw new Error(); }
            catch (_) { showAlert(els.alert, 'JSON inválido.', 'warning'); return; }
        }
        const payload = {
            code: els.code.value, name: els.name.value, version: els.version.value,
            authority: els.authority.value, normative_reference: els.reference.value, source_url: els.source.value,
            effective_date_from: els.from.value, effective_date_until: els.until.value,
            description: els.description.value, parameters: parameters
        };
        if (isGlobalAdmin && scopeCompany) payload.id_company = scopeCompany;
        let endpoint = './protocolos-crear.php';
        if (els.mode.value === 'edit') { endpoint = './protocolos-editar.php'; payload.id_protocol = parseInt(els.target.value, 10); }
        els.submit.disabled = true;
        const r = await postJson(endpoint, payload);
        els.submit.disabled = false;
        if (!r.success) { showAlert(els.alert, r.message, 'danger'); return; }
        showAlert(els.alert, r.message || S('created', 'Protocolo guardado correctamente.'), 'success');
        resetDefinitionForm();
        loadProtocols();
    });

    els.cancelEdit.addEventListener('click', resetDefinitionForm);
    els.detailClose.addEventListener('click', closeDetail);
    els.assignmentDetailClose.addEventListener('click', () => { els.assignmentDetail.classList.remove('active'); currentAssignment = null; });

    els.linkForm.addEventListener('submit', async (event) => {
        event.preventDefault(); hideAlert(els.detailAlert);
        if (!currentProtocol || !els.linkSelect.value) return;
        const payload = {
            id_protocol: currentProtocol.id_protocol,
            id_form: parseInt(els.linkSelect.value, 10),
            is_required: els.linkRequired.checked ? 1 : 0,
            sort_order: parseInt(els.linkOrder.value || '0', 10)
        };
        if (scopeCompany) payload.id_company = scopeCompany;
        const r = await postJson('./protocolo-formularios-agregar.php', payload);
        if (!r.success) { showAlert(els.detailAlert, r.message, 'danger'); return; }
        showAlert(els.detailAlert, r.message || S('linked'), 'success');
        await openDetail(currentProtocol.id_protocol);
    });

    els.assignRecurrence.addEventListener('change', () => {
        els.assignInterval.disabled = els.assignRecurrence.value === 'none';
    });

    els.assignmentForm.addEventListener('submit', async (event) => {
        event.preventDefault(); hideAlert(els.detailAlert);
        if (!currentProtocol) return;
        const implementationCompany = parseInt(currentProtocol.implementation_company || 0, 10);
        if (!implementationCompany) { showAlert(els.detailAlert, S('select_company'), 'warning'); return; }
        const payload = {
            id_protocol: currentProtocol.id_protocol,
            id_company: implementationCompany,
            id_company_center: els.assignCenter.value || null,
            id_project: els.assignProject.value || null,
            id_worker: els.assignWorker.value || null,
            responsible_user: els.assignResponsible.value,
            start_at: els.assignStart.value,
            next_due_at: els.assignDue.value,
            recurrence_unit: els.assignRecurrence.value,
            recurrence_interval: els.assignRecurrence.value === 'none' ? null : parseInt(els.assignInterval.value || '1', 10),
            parameter_overrides: els.assignOverrides.value.trim(),
            notes: els.assignNotes.value
        };
        const r = await postJson('./asignaciones-crear.php', payload);
        if (!r.success) { showAlert(els.detailAlert, r.message, 'danger'); return; }
        showAlert(els.detailAlert, r.message || S('assigned'), 'success');
        els.assignmentForm.reset(); setDefaultDates(); els.assignInterval.disabled = true;
        await openDetail(currentProtocol.id_protocol);
    });

    els.reviewForm.addEventListener('submit', async (event) => {
        event.preventDefault(); hideAlert(els.detailAlert);
        const id = parseInt(els.reviewExecutionId.value || '0', 10); if (!id) return;
        const r = await postJson('./ejecucion-revisar.php', { id_protocol_execution: id, result: els.reviewResult.value, review_notes: els.reviewNotes.value });
        if (!r.success) { showAlert(els.detailAlert, r.message, 'danger'); return; }
        showAlert(els.detailAlert, r.message || S('reviewed'), 'success');
        els.executionReview.classList.remove('active');
        if (currentAssignment) {
            await Promise.all([loadExecutions(currentAssignment.id_protocol_assignment), loadTracking(currentAssignment.id_protocol_assignment)]);
        }
        await openDetail(currentProtocol.id_protocol);
    });

    els.trackingForm.addEventListener('submit', async (event) => {
        event.preventDefault(); hideAlert(els.detailAlert);
        if (!currentAssignment) return;
        const r = await postJson('./tracking-crear.php', {
            id_protocol_assignment: currentAssignment.id_protocol_assignment,
            id_protocol_execution: els.trackingExecutionId.value || null,
            description: els.trackingDescription.value,
            responsible_user: els.trackingResponsible.value || null,
            commitment_date: els.trackingCommitment.value,
            deadline: els.trackingDeadline.value
        });
        if (!r.success) { showAlert(els.detailAlert, r.message, 'danger'); return; }
        showAlert(els.detailAlert, r.message || S('tracking_created'), 'success');
        els.trackingDescription.value = ''; els.trackingExecutionId.value = ''; setDefaultDates();
        await loadTracking(currentAssignment.id_protocol_assignment);
    });

    function closeDetail() {
        els.detail.classList.remove('active');
        els.assignmentDetail.classList.remove('active');
        els.executionReview.classList.remove('active');
        currentProtocol = null; currentAssignment = null; companyCatalogs = null;
    }

    // Inicio
    els.formTitle.dataset.defaultTitle = els.formTitle.textContent;
    resetDefinitionForm();
    setDefaultDates();
    (async () => {
        await loadCompanies();
        updateScopeFromSelector();
        await loadProtocols();
    })();
});
