/** SCT P75 — Formularios dinámicos / constructor guiado para roles de gestión. */
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('.container[data-csrf-token]');
    if (!root) return;
    const csrf = root.dataset.csrfToken || '';
    const isGlobal = root.dataset.isGlobalAdmin === '1';
    const $ = id => document.getElementById(id);
    let I = {};
    try { I = JSON.parse(($('formsI18n') || {}).textContent || '{}'); } catch (_) {}
    const S = (k, f) => I[k] || f || k;

    const els = {
        create: $('formsCreateBtn'), stateFilter: $('formsStateFilter'), modalEl: $('formsWizardModal'), prev: $('formsWizardPrev'), draft: $('formsSaveDraft'), publish: $('formsPublish'),
        company: $('companySelect'), alert: $('formsAlert'), status: $('formsStatus'), tableWrap: $('formsTableWrap'), tableBody: $('formsTableBody'), cards: $('formsMobileCards'),
        editor: $('formEditor'), editorTitle: $('formEditorTitle'), mode: $('formMode'), target: $('formTarget'), name: $('formName'), description: $('formDescription'), submit: $('formSubmitBtn'), cancelEdit: $('formCancelEditBtn'),
        detail: $('formsDetail'), detailName: $('formsDetailName'), detailClose: $('formsDetailClose'), detailAlert: $('detailAlert'), locked: $('lockedNote'),
        fields: $('fieldsList'), fieldForm: $('fieldForm'), fieldMode: $('fieldMode'), fieldTarget: $('fieldTarget'), fieldLabel: $('fieldLabel'), fieldType: $('fieldType'), fieldOrder: $('fieldOrder'), fieldRequired: $('fieldRequired'), fieldOptions: $('fieldOptions'), fieldSubmit: $('fieldSubmitBtn'), fieldCancel: $('fieldCancelEdit'),
        submissions: $('submissionsList'), submissionDetail: $('submissionDetail'), submissionClose: $('submissionDetailClose'), submissionAnswers: $('submissionAnswers'),
        materialDrop: $('formsMaterialDropZone'), materialFiles: $('formsMaterialFiles'), materialChoose: $('formsMaterialChoose'), materialPending: $('formsMaterialPending'), materialUpload: $('formsMaterialUpload'), materials: $('formsMaterialsList'),
        users: $('formsAssignUsers'), groups: $('formsGroupsList'), assignments: $('formsAssignmentsList'), assignBtn: $('formsAssignBtn'), accessStart: $('formsAccessStart'), deadline: $('formsDeadline'),
        manageModalEl: $('formsManageModal'), manageTitle: $('formsManageTitle'), manageAlert: $('formsManageAlert'), manageBasic: $('formsManageBasic'), manageMaterials: $('formsManageMaterials'), manageFields: $('formsManageFields'), manageGroups: $('formsManageGroups'), manageAssignments: $('formsManageAssignments'), manageStats: $('formsManageStats'), manageEdit: $('formsManageEdit')
    };
    const modal = els.modalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(els.modalEl, { backdrop: 'static', keyboard: false }) : null;
    const manageModal = els.manageModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(els.manageModalEl) : null;
    let currentForm = null;
    let managedForm = null;
    let currentScope = isGlobal ? 'global' : null;
    let step = 1;
    let pendingFiles = [];
    let groups = [];
    let catalogRows = [];

    const esc = value => { const d = document.createElement('div'); d.textContent = value == null ? '' : String(value); return d.innerHTML; };
    const fieldTypeLabel = t => S('activity_builder_field_type_' + String(t || 'text'), String(t || 'text'));
    const api = async (url, opt) => { try { const r = await fetch(url, opt || {}); const j = await r.json(); return j; } catch (_) { return { success: false, message: S('forms_error_response', 'Respuesta inválida del servidor.') }; } };
    const post = (url, data) => api(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.assign({ csrf_token: csrf }, data || {})) });
    function showAlert(el, msg, variant) { if (!el) return; el.textContent = msg || ''; el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info'); el.classList.add('alert-' + (variant || 'danger')); }
    function hideAlert(el) { if (el) el.classList.add('d-none'); }

    function companyForAssignment() {
        if (!currentForm) return 0;
        if (currentForm.id_company != null && Number(currentForm.id_company) > 0) return Number(currentForm.id_company);
        if (isGlobal && currentScope !== 'global') return Number(currentScope) || 0;
        return 0;
    }

    function updateFooter() {
        if (els.prev) els.prev.classList.toggle('d-none', step === 1);
        if (els.draft) els.draft.classList.toggle('d-none', !currentForm);
        if (els.publish) els.publish.classList.toggle('d-none', step !== 4 || !currentForm);
    }
    async function showStep(next) {
        step = Math.max(1, Math.min(4, Number(next) || 1));
        document.querySelectorAll('[data-forms-step]').forEach(el => el.classList.toggle('d-none', Number(el.dataset.formsStep) !== step));
        document.querySelectorAll('[data-forms-step-go]').forEach(btn => {
            const n = Number(btn.dataset.formsStepGo);
            btn.classList.toggle('is-active', n === step);
            btn.classList.toggle('is-complete', n < step);
            btn.disabled = n > 1 && !currentForm;
        });
        updateFooter();
        if (!currentForm) return;
        if (step === 2) await loadMaterials();
        if (step === 3) await loadDetail();
        if (step === 4) await Promise.all([loadUsersAndGroups(), loadAssignments(), loadSubmissions()]);
    }

    function resetForm() {
        if (!els.editor) return;
        els.mode.value = 'create'; els.target.value = ''; els.name.value = ''; els.description.value = '';
        els.editorTitle.textContent = S('forms_form_new', 'Nuevo formulario');
        els.submit.textContent = S('activity_builder_next', 'Siguiente');
        els.cancelEdit.classList.add('d-none');
    }
    function resetField() {
        if (!els.fieldForm) return;
        els.fieldMode.value = 'create'; els.fieldTarget.value = ''; els.fieldLabel.value = ''; els.fieldType.value = 'text'; els.fieldOrder.value = '0'; els.fieldRequired.checked = false; els.fieldOptions.value = '';
        els.fieldSubmit.textContent = S('forms_field_new', 'Agregar campo'); els.fieldCancel.classList.add('d-none');
    }
    function closeWizard() { if (modal) modal.hide(); }
    function openCreate() {
        currentForm = null; pendingFiles = []; groups = []; resetForm(); resetField(); hideAlert(els.alert); hideAlert(els.detailAlert); showStep(1); if (modal) modal.show(); setTimeout(() => els.name && els.name.focus(), 200);
    }

    async function loadCompanies() {
        if (!isGlobal || !els.company) return;
        const r = await api('./empresas-disponibles.php');
        if (!r.success) return showAlert(els.alert, r.message, 'danger');
        els.company.innerHTML = '<option value="global">' + esc(S('forms_scope_global', 'Plantillas globales')) + '</option>';
        (r.data || []).forEach(c => { const o = document.createElement('option'); o.value = c.id_company; o.textContent = c.razon_social; els.company.appendChild(o); });
        els.company.value = 'global'; currentScope = 'global';
    }

    async function loadForms() {
        hideAlert(els.alert);
        els.status.className = 'alert alert-info mb-0'; els.status.textContent = S('forms_loading', 'Cargando formularios...');
        els.tableWrap.classList.add('d-none'); if (els.cards) els.cards.innerHTML = '';
        let url = './formularios-listar.php'; if (isGlobal && currentScope !== 'global') url += '?id_company=' + encodeURIComponent(currentScope);
        const r = await api(url);
        if (!r.success) { els.status.className = 'alert alert-danger mb-0'; els.status.textContent = r.message || S('forms_error_load', 'No se pudieron cargar los formularios.'); return; }
        const items = r.data || []; catalogRows = items;
        if (!items.length) { els.status.textContent = S('forms_empty', 'No hay formularios configurados para este ámbito.'); return; }
        els.status.classList.add('d-none'); els.tableWrap.classList.remove('d-none'); renderFormsFiltered();
    }

    function renderFormsFiltered(){
        const filter=els.stateFilter?els.stateFilter.value:'active';
        const rows=catalogRows.filter(f=>filter==='all'||(filter==='active'?String(f.state)==='1':String(f.state)!=='1'));
        if(!rows.length){els.tableWrap.classList.add('d-none');if(els.cards)els.cards.innerHTML='<p class="text-muted mb-0">'+esc(S('forms_empty','No hay registros para este filtro.'))+'</p>';return;}
        els.tableWrap.classList.remove('d-none');renderForms(rows);
    }

    function actionButtons(f) {
        const editable = !!f.editable, locked = !!f.locked, active = String(f.state) === '1';
        let h = '<button class="btn btn-outline-custom btn-sm" data-a="manage">' + esc(S('forms_manage', 'Gestionar')) + '</button>';
        if (editable && !locked) h += '<button class="btn btn-outline-custom btn-sm" data-a="edit">' + esc(S('forms_edit', 'Editar')) + '</button>';
        if (editable) h += '<button class="btn ' + (active ? 'btn-outline-danger' : 'btn-outline-success') + ' btn-sm" data-a="toggle">' + esc(active ? S('forms_deactivate', 'Dar de baja') : S('forms_reactivate', 'Reactivar')) + '</button>';
        return h;
    }
    function bindActions(host, f) {
        const manage = host.querySelector('[data-a="manage"]'); if (manage) manage.addEventListener('click', () => openManage(f));
        const edit = host.querySelector('[data-a="edit"]'); if (edit) edit.addEventListener('click', () => startEdit(f));
        const toggleBtn = host.querySelector('[data-a="toggle"]'); if (toggleBtn) toggleBtn.addEventListener('click', () => toggle(f));
    }
    function renderForms(items) {
        els.tableBody.innerHTML = ''; if (els.cards) els.cards.innerHTML = '';
        items.forEach(f => {
            const active = String(f.state) === '1';
            const scope = f.id_company == null ? S('forms_global_badge', 'Global') : (f.company_name || S('forms_company_badge', 'Empresa'));
            const tr = document.createElement('tr');
            tr.innerHTML = '<td><strong>' + esc(f.name) + '</strong></td><td>' + esc(f.description || '—') + '</td><td>' + esc(f.field_count || 0) + '</td><td>' + esc(scope) + '</td><td>' + esc(active ? S('common_active', 'Activo') : S('common_inactive', 'Inactivo')) + '</td><td><div class="d-flex gap-1 flex-wrap">' + actionButtons(f) + '</div></td>';
            bindActions(tr, f); els.tableBody.appendChild(tr);
            if (els.cards) {
                const card = document.createElement('article'); card.className = 'sct-builder-mobile-card';
                card.innerHTML = '<div class="sct-builder-mobile-card__head"><strong>' + esc(f.name) + '</strong><span class="status-pill ' + (active ? 'ok' : 'danger') + '">' + esc(active ? S('common_active', 'Activo') : S('common_inactive', 'Inactivo')) + '</span></div><p>' + esc(f.description || '—') + '</p><div class="sct-builder-mobile-card__meta"><span>' + esc(fieldTypeLabel('text')) + ': ' + esc(f.field_count || 0) + '</span><span>' + esc(scope) + '</span></div><div class="sct-builder-mobile-card__actions">' + actionButtons(f) + '</div>';
                bindActions(card, f); els.cards.appendChild(card);
            }
        });
    }

    function startEdit(f) {
        currentForm = f; els.mode.value = 'edit'; els.target.value = f.id_form; els.name.value = f.name || ''; els.description.value = f.description || '';
        els.editorTitle.textContent = S('forms_form_edit', 'Editar formulario'); els.submit.textContent = S('activity_builder_next', 'Siguiente'); els.cancelEdit.classList.remove('d-none');
        showStep(1); if (modal) modal.show();
    }
    function renderManageBasic(f) {
        if (!els.manageBasic) return;
        const scope = f.id_company == null ? S('forms_global_badge', 'Global') : (f.company_name || S('forms_company_badge', 'Empresa'));
        const facts = [
            [S('forms_name','Nombre'), f.name || '—'],
            [S('forms_description','Descripción'), f.description || '—'],
            [S('forms_scope','Ámbito'), scope],
            [S('companies_col_state','Estado'), String(f.state) === '1' ? S('common_active','Activo') : S('common_inactive','Inactivo')]
        ];
        els.manageBasic.innerHTML = facts.map(x => '<div class="sct-builder-readonly-fact"><small>'+esc(x[0])+'</small><strong>'+esc(x[1])+'</strong></div>').join('');
    }
    function renderManageFields(fields) {
        if (!els.manageFields) return;
        if (!(fields || []).length) { els.manageFields.innerHTML='<p class="sct-builder-empty">'+esc(S('forms_fields_empty','Todavía no hay campos configurados.'))+'</p>'; return; }
        els.manageFields.innerHTML=(fields||[]).map((f,i)=>'<div class="sct-builder-readonly-question"><span class="sct-builder-readonly-question__number">'+(i+1)+'</span><div><strong>'+esc(f.label)+'</strong><small>'+esc(fieldTypeLabel(f.field_type))+' · '+esc(String(f.is_required)==='1'?S('forms_required_yes','Obligatorio'):S('forms_required_no','Opcional'))+'</small></div></div>').join('');
    }
    function renderManageGroups(readGroups, assignedRows) {
        if (!els.manageGroups) return;
        const assigned = new Set((assignedRows||[]).map(a=>String(a.id_users||'')));
        const matches=(readGroups||[]).map(g=>{ const users=Array.from(new Set((g.users||[]).map(String))); const covered=users.filter(id=>assigned.has(id)).length; return covered?{label:g.label||g.name,covered,total:users.length}:null; }).filter(Boolean);
        els.manageGroups.innerHTML=matches.length?'<div class="sct-builder-readonly-group-title">'+esc(S('activity_builder_assigned_groups','Grupos asignados'))+'</div>'+matches.map(g=>'<span class="sct-builder-readonly-group"><strong>'+esc(g.label)+'</strong><small>'+g.covered+'/'+g.total+' '+esc(S('activity_builder_people','personas'))+'</small></span>').join(''):'';
    }
    async function openManage(f) {
        managedForm=f;
        if (els.manageTitle) els.manageTitle.textContent=f.name||S('forms_manage','Gestionar');
        renderManageBasic(f);
        if (els.manageMaterials) els.manageMaterials.innerHTML='<p class="sct-builder-empty">'+esc(S('activity_builder_loading','Cargando...'))+'</p>';
        if (els.manageFields) els.manageFields.innerHTML='<p class="sct-builder-empty">'+esc(S('activity_builder_loading','Cargando...'))+'</p>';
        if (els.manageAssignments) els.manageAssignments.innerHTML='<p class="sct-builder-empty">'+esc(S('activity_builder_loading','Cargando...'))+'</p>';
        hideAlert(els.manageAlert);
        if (manageModal) manageModal.show();
        const detail=await api('./formularios-detalle.php?id='+encodeURIComponent(f.id_form));
        if(!detail.success){showAlert(els.manageAlert,detail.message||S('forms_error_detail','No se pudo cargar el detalle.'),'danger');return;}
        const full=Object.assign({},f,detail.data||{}); managedForm=full; renderManageBasic(full); renderManageFields(full.campos||[]);
        const company=(full.id_company!=null&&Number(full.id_company)>0)?Number(full.id_company):(isGlobal&&currentScope!=='global'?Number(currentScope)||0:0);
        const reqs=[api('../actividades/support-materials.php?entity=dynamic_form&id='+encodeURIComponent(full.id_form)),api('./asignaciones-listar.php?id_form='+encodeURIComponent(full.id_form))];
        if(company)reqs.push(api('./grupos-disponibles.php?id_company='+encodeURIComponent(company))); else reqs.push(Promise.resolve({success:true,data:[]}));
        const [materials,assignments,groupResult]=await Promise.all(reqs);
        if(els.manageMaterials){ const rows=materials.success?(materials.data||[]):[]; els.manageMaterials.innerHTML=rows.length?rows.map(m=>{const href=m.file_path?'../../'+String(m.file_path).replace(/^\/+/, ''):'';return '<div class="sct-builder-readonly-row"><div><i class="bi bi-paperclip"></i><strong>'+esc(m.original_name||m.title||'Material')+'</strong><small>'+esc(m.mime_type||'')+'</small></div>'+(href?'<a class="btn btn-outline-custom btn-sm" href="'+esc(href)+'" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i><span>'+esc(S('common_view','Ver'))+'</span></a>':'')+'</div>';}).join(''):'<p class="sct-builder-empty">'+esc(S('activity_builder_no_materials','Aún no hay material cargado.'))+'</p>'; }
        const assignedRows=assignments.success?(assignments.data||[]):[];
        if(els.manageStats){ const done=assignedRows.filter(a=>a.status==='submitted'||Number(a.submission_count||0)>0).length; els.manageStats.textContent=assignedRows.length+' asignados · '+done+' completados · '+Math.max(0,assignedRows.length-done)+' pendientes'; }
        renderManageGroups(groupResult.success?(groupResult.data||[]):[],assignedRows);
        if(els.manageAssignments){ els.manageAssignments.innerHTML=assignedRows.length?assignedRows.map(a=>{const name=((a.name||'')+' '+(a.lastname||'')).trim()||a.id_users;const done=a.status==='submitted'||Number(a.submission_count||0)>0;return '<div class="sct-builder-readonly-assignment"><div class="sct-builder-readonly-assignment__main"><strong>'+esc(name)+'</strong><small>'+esc(a.id_users||'')+'</small></div><div class="sct-builder-readonly-assignment__progress"><span><b>'+esc(done?S('activity_builder_completed','Completado'):S('activity_builder_pending','Pendiente'))+'</b><small>'+esc(Number(a.submission_count||0))+' respuesta(s)</small></span><div class="sct-builder-mini-progress"><i style="width:'+(done?100:0)+'%"></i></div></div></div>';}).join(''):'<p class="sct-builder-empty">'+esc(S('activity_builder_no_assignments','Aún no hay asignaciones.'))+'</p>'; }
        if(els.manageEdit) els.manageEdit.classList.toggle('d-none', !full.editable || !!full.locked);
    }

    if (els.editor) els.editor.addEventListener('submit', async e => {
        e.preventDefault();
        if (!els.editor.checkValidity()) { els.editor.reportValidity(); return; }
        const editing = els.mode.value === 'edit';
        const payload = { name: els.name.value.trim(), description: els.description.value.trim() };
        let url = './formularios-crear.php';
        if (editing) { url = './formularios-editar.php'; payload.id_form = Number(els.target.value); }
        else if (isGlobal) { if (currentScope === 'global') payload.scope = 'global'; else { payload.scope = 'company'; payload.id_company = Number(currentScope); } }
        els.submit.disabled = true; const r = await post(url, payload); els.submit.disabled = false;
        if (!r.success) return showAlert(els.alert, r.message, 'danger');
        const id = editing ? Number(els.target.value) : Number(r.data && r.data.id_form || 0);
        if (!id) return showAlert(els.alert, S('forms_error_response', 'No se pudo obtener el formulario guardado.'), 'danger');
        if (!editing) await post('./formularios-cambiar-estado.php', { id_form: id, state: 0 });
        currentForm = Object.assign({}, currentForm || {}, { id_form: id, name: payload.name, description: payload.description, id_company: currentScope !== 'global' ? Number(currentScope) : null, state: 0 });
        await loadForms(); await showStep(2);
    });

    async function toggle(f) {
        if (!await window.sctConfirmAction(String(f.state) === '1' ? S('forms_confirm_deactivate', '¿Dar de baja este formulario?') : S('forms_confirm_reactivate', '¿Reactivar este formulario?'))) return;
        const r = await post('./formularios-cambiar-estado.php', { id_form: Number(f.id_form), state: String(f.state) === '1' ? 0 : 1 });
        if (!r.success) return showAlert(els.alert, r.message, 'danger');
        showAlert(els.alert, r.message, 'success'); await loadForms();
    }

    async function loadDetail() {
        if (!currentForm) return;
        const r = await api('./formularios-detalle.php?id=' + encodeURIComponent(currentForm.id_form));
        if (!r.success) return showAlert(els.detailAlert, r.message, 'danger');
        currentForm = Object.assign({}, currentForm, r.data || {});
        els.detailName.textContent = currentForm.name || '-'; els.locked.classList.toggle('d-none', !currentForm.locked);
        els.fieldForm.classList.toggle('d-none', !currentForm.editable || currentForm.locked); renderFields(currentForm.campos || []);
    }
    function renderFields(fields) {
        els.fields.innerHTML = '';
        if (!fields.length) { els.fields.innerHTML = '<p class="text-muted">' + esc(S('forms_fields_empty', 'Todavía no hay campos configurados.')) + '</p>'; return; }
        fields.forEach(f => {
            const row = document.createElement('div'); row.className = 'field-row'; const req = String(f.is_required) === '1' ? S('forms_required_yes', 'Obligatorio') : S('forms_required_no', 'Opcional');
            let actions = ''; if (currentForm.editable && !currentForm.locked) actions = '<div class="d-flex gap-1"><button class="btn btn-outline-custom btn-sm" data-a="edit">' + esc(S('forms_edit', 'Editar')) + '</button><button class="btn btn-outline-danger btn-sm" data-a="delete"><i class="bi bi-trash"></i></button></div>';
            row.innerHTML = '<div><strong>' + esc(f.label) + '</strong><div class="meta">' + esc(fieldTypeLabel(f.field_type)) + ' · ' + esc(req) + ' · #' + esc(f.sort_order) + (f.options ? ' · ' + esc(f.options) : '') + '</div></div>' + actions;
            const eb = row.querySelector('[data-a="edit"]'); if (eb) eb.addEventListener('click', () => startFieldEdit(f));
            const db = row.querySelector('[data-a="delete"]'); if (db) db.addEventListener('click', () => deleteField(f)); els.fields.appendChild(row);
        });
    }
    function startFieldEdit(f) { els.fieldMode.value = 'edit'; els.fieldTarget.value = f.id_field; els.fieldLabel.value = f.label || ''; els.fieldType.value = f.field_type || 'text'; els.fieldOrder.value = f.sort_order || 0; els.fieldRequired.checked = String(f.is_required) === '1'; els.fieldOptions.value = f.options || ''; els.fieldSubmit.textContent = S('forms_field_edit', 'Guardar campo'); els.fieldCancel.classList.remove('d-none'); }
    if (els.fieldCancel) els.fieldCancel.addEventListener('click', resetField);
    if (els.fieldForm) els.fieldForm.addEventListener('submit', async e => {
        e.preventDefault(); if (!currentForm || !els.fieldForm.checkValidity()) { els.fieldForm.reportValidity(); return; }
        const payload = { label: els.fieldLabel.value.trim(), field_type: els.fieldType.value, sort_order: Number(els.fieldOrder.value || 0), is_required: els.fieldRequired.checked ? 1 : 0, options: els.fieldOptions.value.trim() };
        let url = './campos-agregar.php'; if (els.fieldMode.value === 'edit') { url = './campos-editar.php'; payload.id_field = Number(els.fieldTarget.value); } else payload.id_form = Number(currentForm.id_form);
        const r = await post(url, payload); if (!r.success) return showAlert(els.detailAlert, r.message, 'danger'); resetField(); await loadDetail(); await loadForms();
    });
    async function deleteField(f) { if (!await window.sctConfirmAction(S('forms_confirm_field_delete', '¿Eliminar este campo?'))) return; const r = await post('./campos-eliminar.php', { id_field: Number(f.id_field) }); if (!r.success) return showAlert(els.detailAlert, r.message, 'danger'); await loadDetail(); await loadForms(); }

    function renderPendingFiles() {
        if (!els.materialPending) return; els.materialPending.innerHTML = '';
        els.materialPending.classList.toggle('d-none', !pendingFiles.length); els.materialUpload.classList.toggle('d-none', !pendingFiles.length);
        pendingFiles.forEach((f, index) => { const row = document.createElement('div'); row.className = 'sct-builder-file-row'; row.innerHTML = '<div><i class="bi bi-file-earmark"></i><strong>' + esc(f.name) + '</strong><span>' + esc(Math.max(1, Math.round(f.size / 1024))) + ' KB</span></div><button type="button" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>'; row.querySelector('button').addEventListener('click', () => { pendingFiles.splice(index, 1); renderPendingFiles(); }); els.materialPending.appendChild(row); });
    }
    function addPending(files) { Array.from(files || []).forEach(f => { if (!pendingFiles.some(x => x.name === f.name && x.size === f.size)) pendingFiles.push(f); }); renderPendingFiles(); }
    async function loadMaterials() {
        if (!currentForm || !els.materials) return;
        els.materials.innerHTML = '<p class="text-muted small">' + esc(S('activity_builder_loading', 'Cargando...')) + '</p>';
        const r = await api('../actividades/support-materials.php?entity=dynamic_form&id=' + encodeURIComponent(currentForm.id_form));
        if (!r.success) { els.materials.innerHTML = '<div class="alert alert-warning">' + esc(r.message) + '</div>'; return; }
        els.materials.innerHTML = '';
        if (!(r.data || []).length) els.materials.innerHTML = '<p class="text-muted small">' + esc(S('activity_builder_no_materials', 'Aún no hay material cargado.')) + '</p>';
        (r.data || []).forEach(m => { const row = document.createElement('div'); row.className = 'sct-builder-file-row'; row.innerHTML = '<div><i class="bi bi-paperclip"></i><strong>' + esc(m.original_name || m.title) + '</strong><span>' + esc(m.mime_type || '') + '</span></div><button type="button" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>'; row.querySelector('button').addEventListener('click', async () => { if (!await window.sctConfirmAction(S('activity_builder_confirm_delete_material', '¿Eliminar este archivo?'))) return; const fd = new FormData(); fd.append('entity', 'dynamic_form'); fd.append('id', currentForm.id_form); fd.append('action', 'delete'); fd.append('id_material', m.id_activity_material); fd.append('csrf_token', csrf); const d = await api('../actividades/support-materials.php', { method: 'POST', body: fd }); if (!d.success) return showAlert(els.alert, d.message, 'danger'); loadMaterials(); }); els.materials.appendChild(row); });
    }
    async function uploadMaterials() {
        if (!currentForm || !pendingFiles.length) return;
        const fd = new FormData(); fd.append('entity', 'dynamic_form'); fd.append('id', currentForm.id_form); fd.append('action', 'upload'); fd.append('csrf_token', csrf); pendingFiles.forEach(f => fd.append('files[]', f));
        els.materialUpload.disabled = true; const r = await api('../actividades/support-materials.php', { method: 'POST', body: fd }); els.materialUpload.disabled = false;
        if (!r.success) return showAlert(els.alert, r.message, 'danger'); pendingFiles = []; renderPendingFiles(); await loadMaterials();
    }

    async function loadUsersAndGroups() {
        if (!currentForm || !els.users) return;
        const company = companyForAssignment();
        els.users.innerHTML = ''; els.groups.innerHTML = '';
        if (!company) { els.groups.innerHTML = '<div class="alert alert-info mb-0">' + esc(S('activity_builder_select_company_for_assignment', 'Selecciona una empresa para asignar esta actividad.')) + '</div>'; return; }
        const [u, g] = await Promise.all([api('./usuarios-disponibles.php?id_company=' + company), api('./grupos-disponibles.php?id_company=' + company)]);
        if (u.success) (u.data || []).forEach(user => { const o = document.createElement('option'); o.value = user.id_users; o.textContent = ((user.name || '') + ' ' + (user.lastname || '')).trim() + ' · ' + (user.email || user.id_users); els.users.appendChild(o); });
        if (window.sctBulkAssignmentRefresh) window.sctBulkAssignmentRefresh('formsAssignUsers');
        groups = g.success ? (g.data || []) : []; renderGroups();
    }
    function renderGroups() {
        els.groups.innerHTML = ''; if (!groups.length) { els.groups.innerHTML = '<p class="text-muted small">' + esc(S('activity_builder_no_groups', 'No hay grupos disponibles.')) + '</p>'; return; }
        groups.forEach(group => { const row = document.createElement('label'); row.className = 'sct-builder-group-option'; row.innerHTML = '<input type="checkbox" class="form-check-input"><span><strong>' + esc(group.label || group.name) + '</strong><small>' + esc(group.count || 0) + ' ' + esc(S('activity_builder_people', 'personas')) + '</small></span>'; const cb = row.querySelector('input'); cb.addEventListener('change', () => { const values = new Set(Array.from(els.users.selectedOptions).map(o => o.value)); (group.users || []).forEach(id => cb.checked ? values.add(String(id)) : values.delete(String(id))); Array.from(els.users.options).forEach(o => { o.selected = values.has(o.value); }); els.users.dispatchEvent(new Event('change', { bubbles: true })); if (window.sctBulkAssignmentRefresh) window.sctBulkAssignmentRefresh('formsAssignUsers'); }); els.groups.appendChild(row); });
    }
    async function assignSelected() {
        const ids = Array.from(els.users.selectedOptions || []).map(o => o.value); if (!ids.length) return showAlert(els.alert, S('activity_builder_select_people', 'Selecciona al menos una persona.'), 'warning');
        const r = await post('./asignaciones-crear.php', { id_form: Number(currentForm.id_form), id_company: companyForAssignment(), id_users: ids, access_start: els.accessStart.value || '', deadline: els.deadline.value || '' });
        if (!r.success) return showAlert(els.alert, r.message, 'danger'); showAlert(els.alert, r.message, 'success'); await loadAssignments();
    }
    async function loadAssignments() {
        if (!currentForm || !els.assignments) return;
        const r = await api('./asignaciones-listar.php?id_form=' + encodeURIComponent(currentForm.id_form)); els.assignments.innerHTML = '';
        if (!r.success) { els.assignments.innerHTML = '<div class="alert alert-warning">' + esc(r.message) + '</div>'; return; }
        if (!(r.data || []).length) { els.assignments.innerHTML = '<p class="text-muted small">' + esc(S('activity_builder_no_assignments', 'Aún no hay asignaciones.')) + '</p>'; return; }
        (r.data || []).forEach(a => { const row = document.createElement('div'); row.className = 'sct-builder-assignment-row'; const name = ((a.name || '') + ' ' + (a.lastname || '')).trim() || a.id_users; row.innerHTML = '<div><strong>' + esc(name) + '</strong><small>' + esc(a.id_users) + '</small></div><div><span class="status-pill ' + (a.status === 'submitted' ? 'ok' : 'warn') + '">' + esc(a.status === 'submitted' ? S('activity_builder_completed', 'Completado') : S('activity_builder_pending', 'Pendiente')) + '</span><small>' + esc(a.deadline ? String(a.deadline).slice(0, 10) : '—') + '</small></div>'; els.assignments.appendChild(row); });
    }

    async function loadSubmissions() {
        if (!currentForm || !els.submissions) return;
        const r = await api('./envios-listar.php?id_form=' + encodeURIComponent(currentForm.id_form)); els.submissions.innerHTML = '';
        if (!r.success) { els.submissions.innerHTML = '<div class="alert alert-warning">' + esc(r.message || '') + '</div>'; return; }
        if (!(r.data || []).length) { els.submissions.innerHTML = '<p class="text-muted">' + esc(S('forms_submissions_empty', 'Todavía no hay respuestas registradas.')) + '</p>'; return; }
        (r.data || []).forEach(s => { const row = document.createElement('div'); row.className = 'submission-row'; const full = ((s.name || '') + ' ' + (s.lastname || '')).trim() || s.id_users; row.innerHTML = '<div><strong>' + esc(full) + '</strong><div class="text-muted small">' + esc(s.id_users) + '</div></div><button class="btn btn-outline-custom btn-sm">' + esc(S('forms_submission_view', 'Ver respuesta')) + '</button>'; row.querySelector('button').addEventListener('click', () => openSubmission(s.id_submission)); els.submissions.appendChild(row); });
    }
    async function openSubmission(id) {
        const r = await api('./envio-detalle.php?id_submission=' + encodeURIComponent(id)); if (!r.success) return showAlert(els.detailAlert, r.message, 'danger'); els.submissionAnswers.innerHTML = '';
        (r.data.respuestas || []).forEach(a => { const line = document.createElement('div'); line.className = 'answer-line'; line.innerHTML = '<div class="answer-label">' + esc(a.label) + '</div><div class="answer-value">' + esc(Array.isArray(a.display_value) ? a.display_value.join(', ') : (a.display_value || '—')) + '</div>'; els.submissionAnswers.appendChild(line); }); els.submissionDetail.classList.remove('d-none');
    }

    async function saveState(state) {
        if (!currentForm) return;
        if (state === 1) { await loadDetail(); if (!(currentForm.campos || []).length) return showAlert(els.alert, S('activity_builder_form_needs_field', 'Agrega al menos un campo antes de publicar.'), 'warning'); }
        const r = await post('./formularios-cambiar-estado.php', { id_form: Number(currentForm.id_form), state });
        if (!r.success) return showAlert(els.alert, r.message, 'danger'); showAlert(els.alert, state ? S('activity_builder_published', 'Publicado correctamente.') : S('activity_builder_draft_saved', 'Borrador guardado.'), 'success'); await loadForms(); if (state === 1) closeWizard();
    }

    function syncFieldOptionsVisibility(){ if(!els.fieldOptions)return; const row=els.fieldOptions.closest('.wide'); const show=['select','checkbox'].includes(els.fieldType.value); if(row)row.classList.toggle('d-none',!show); if(!show)els.fieldOptions.value=''; }
    if(els.fieldType)els.fieldType.addEventListener('change',syncFieldOptionsVisibility);
    syncFieldOptionsVisibility();
    if (els.stateFilter) els.stateFilter.addEventListener('change',renderFormsFiltered);
    if (els.create) els.create.addEventListener('click', openCreate);
    if (els.manageEdit) els.manageEdit.addEventListener('click', () => { if (!managedForm) return; const f=managedForm; if (manageModal) manageModal.hide(); setTimeout(()=>startEdit(f),180); });
    if (els.cancelEdit) els.cancelEdit.addEventListener('click', resetForm);
    if (els.prev) els.prev.addEventListener('click', () => showStep(step - 1));
    if (els.draft) els.draft.addEventListener('click', () => saveState(0));
    if (els.publish) els.publish.addEventListener('click', async () => { if (els.users && els.users.selectedOptions.length) await assignSelected(); await saveState(1); });
    document.querySelectorAll('[data-forms-step-go]').forEach(btn => btn.addEventListener('click', () => { const n = Number(btn.dataset.formsStepGo); if (n === 1 || currentForm) showStep(n); }));
    document.querySelectorAll('[data-forms-next]').forEach(btn => btn.addEventListener('click', () => { if (currentForm) showStep(step + 1); }));
    if (els.company) els.company.addEventListener('change', () => { currentScope = els.company.value || 'global'; currentForm = null; resetForm(); loadForms(); });
    if (els.materialChoose) els.materialChoose.addEventListener('click', () => els.materialFiles.click());
    if (els.materialFiles) els.materialFiles.addEventListener('change', () => { addPending(els.materialFiles.files); els.materialFiles.value = ''; });
    if (els.materialDrop) { ['dragenter', 'dragover'].forEach(ev => els.materialDrop.addEventListener(ev, e => { e.preventDefault(); els.materialDrop.classList.add('is-dragover'); })); ['dragleave', 'drop'].forEach(ev => els.materialDrop.addEventListener(ev, e => { e.preventDefault(); els.materialDrop.classList.remove('is-dragover'); if (ev === 'drop') addPending(e.dataTransfer.files); })); }
    if (els.materialUpload) els.materialUpload.addEventListener('click', uploadMaterials);
    if (els.assignBtn) els.assignBtn.addEventListener('click', assignSelected);
    if (els.submissionClose) els.submissionClose.addEventListener('click', () => els.submissionDetail.classList.add('d-none'));
    if (els.detailClose) els.detailClose.addEventListener('click', closeWizard);
    if (els.modalEl) els.modalEl.addEventListener('hidden.bs.modal', () => { pendingFiles = []; renderPendingFiles(); hideAlert(els.alert); });

    showStep(1);
    (async () => { if (isGlobal) await loadCompanies(); await loadForms(); })();
});
