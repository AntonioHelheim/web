/** Safety Control Tower - Mis Protocolos */
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('.container[data-csrf-token]'); if (!root) return;
    const $ = id => document.getElementById(id);
    let I18N={}; try { I18N=JSON.parse(($('myProtocolsI18n')||{}).textContent||'{}'); } catch(_) {}
    const S=(k,f)=>I18N[k]||f||k;
    const status=$('myProtocolsStatus'), list=$('myProtocolsList'), alert=$('myProtocolsAlert'), language=$('pageLanguageSelect');

    function esc(v){const d=document.createElement('div');d.textContent=v==null?'':String(v);return d.innerHTML;}
    function fmtDate(v){return v?String(v).replace('T',' ').substring(0,16):'—';}
    function show(el,msg,variant){if(!el)return;el.textContent=msg||'';el.classList.remove('d-none','alert-success','alert-danger','alert-info','alert-warning');el.classList.add('alert-'+(variant||'danger'));}
    async function api(url){try{const r=await fetch(url);return await r.json();}catch(_){return{success:false,message:S('protocols_error_response','Respuesta inválida del servidor.')}}}
    function resultLabel(r){const map={pendiente_revision:'protocols_result_pending_review',conforme:'protocols_result_conforme',observado:'protocols_result_observado',no_conforme:'protocols_result_no_conforme',no_aplica:'protocols_result_no_aplica'};return S(map[r]||'',r||'—');}
    function stateLabel(s){const map={activa:'protocols_state_active',suspendida:'protocols_state_suspended',cerrada:'protocols_state_closed',cancelada:'protocols_state_cancelled'};return S(map[s]||'',s||'—');}
    function resultClass(r){if(r==='conforme'||r==='no_aplica')return'ok';if(r==='observado'||r==='pendiente_revision')return'warn';if(r==='no_conforme')return'danger';return'';}
    function target(a){const parts=[];if(a.center_name)parts.push(a.center_name);if(a.project_name)parts.push(a.project_name);if(a.worker_name)parts.push((a.worker_name+' '+(a.worker_lastname||'')).trim());return parts.length?parts.join(' · '):S('protocols_company_wide','Empresa completa');}

    async function load(){status.classList.remove('d-none','alert-danger');status.classList.add('alert-info');status.textContent=S('protocols_my_loading','Cargando tus protocolos...');list.classList.add('d-none');const r=await api('./mis-asignaciones.php');if(!r.success){status.classList.remove('alert-info');status.classList.add('alert-danger');status.textContent=r.message;return;}const rows=r.data||[];if(!rows.length){status.textContent=S('protocols_my_empty','No tienes protocolos asignados por el momento.');return;}status.classList.add('d-none');list.classList.remove('d-none');render(rows);}

    function render(rows){
        list.innerHTML='';
        rows.forEach(a=>{
            const pending=a.latest_result==='pendiente_revision', active=a.state==='activa'&&parseInt(a.protocol_state||0,10)===1, scheduled=active&&parseInt(a.is_scheduled||0,10)===1, overdue=!scheduled&&parseInt(a.is_overdue||0,10)===1;
            const card=document.createElement('article');
            card.className='protocol-card '+(pending?'pending-review':(overdue?'overdue':(active?'active':'')));
            card.dataset.personalStatus=overdue?'overdue':(a.state==='cerrada'?'complete':'incomplete');
            let statusLabel=stateLabel(a.state), statusClass='is-info';
            if(scheduled){statusLabel=S('activities_status_scheduled','Programada');statusClass='is-info';}
            else if(overdue){statusLabel=S('protocols_overdue','Vencido');statusClass='is-danger';}
            else if(pending){statusLabel=S('protocols_pending_review','Pendiente de revisión');statusClass='is-warning';}
            else if(!active){statusClass='is-success';}
            let action='';
            if(scheduled)action='<button type="button" class="btn btn-outline-custom" disabled><i class="bi bi-clock"></i> '+esc(S('activities_scheduled_action','Aún no disponible'))+'</button>';
            else if(active&&!pending)action='<a class="btn btn-primary-custom" href="./ejecutar-protocolo.php?id_assignment='+encodeURIComponent(a.id_protocol_assignment)+'"><i class="bi bi-play-circle"></i> '+esc(S('protocols_execute','Ejecutar protocolo'))+'</a>';
            else if(pending)action='<span class="sct-personal-status-badge is-warning">'+esc(S('protocols_pending_review','Existe una ejecución pendiente de revisión.'))+'</span>';
            const source=a.source_url?'<a class="btn btn-outline-custom" target="_blank" rel="noopener noreferrer" href="'+esc(a.source_url)+'"><i class="bi bi-box-arrow-up-right"></i> '+esc(S('protocols_source_official','Ver fuente oficial'))+'</a>':'';
            const result=a.latest_result?resultLabel(a.latest_result):'—';
            card.innerHTML='<div class="sct-personal-card-shell">'
                +'<div class="sct-personal-card-head"><span class="sct-personal-card-head__icon"><i class="bi bi-clipboard2-pulse"></i></span><div class="sct-personal-card-head__copy"><h3>'+esc(a.protocol_name)+'</h3><p>'+esc(a.protocol_description||'')+'</p></div><span class="sct-personal-status-badge '+statusClass+'">'+esc(statusLabel)+'</span></div>'
                +'<div class="sct-personal-card-facts">'
                +'<div class="sct-personal-card-fact"><small>'+esc(S('protocols_target','Alcance'))+'</small><strong>'+esc(target(a))+'</strong></div>'
                +(a.start_at?'<div class="sct-personal-card-fact"><small>'+esc(S('activities_available_from','Disponible desde'))+'</small><strong>'+esc(fmtDate(a.start_at))+'</strong></div>':'')
                +'<div class="sct-personal-card-fact"><small>'+esc(S('activities_next_due_label','Próximo control'))+'</small><strong>'+esc(fmtDate(a.next_due_at))+'</strong></div>'
                +'<div class="sct-personal-card-fact"><small>'+esc(S('protocols_history','Historial'))+'</small><strong>'+esc(a.execution_count||0)+'</strong></div>'
                +'<div class="sct-personal-card-fact"><small>'+esc(S('activities_result_label','Resultado'))+'</small><strong>'+esc(result)+'</strong></div>'
                +'</div><div class="sct-personal-card-actions">'+action+source+'<button type="button" class="btn btn-outline-custom" data-history><i class="bi bi-clock-history"></i> '+esc(S('protocols_history','Historial'))+'</button></div><div class="history-panel" data-history-panel></div></div>';
            const btn=card.querySelector('[data-history]'),panel=card.querySelector('[data-history-panel]');
            btn.addEventListener('click',()=>toggleHistory(a,panel));
            list.appendChild(card);
        });
    }

    async function toggleHistory(a,panel){if(panel.classList.contains('active')){panel.classList.remove('active');return;}panel.classList.add('active');panel.innerHTML='<p class="text-muted small">...</p>';const r=await api('./mis-ejecuciones.php?id_protocol_assignment='+encodeURIComponent(a.id_protocol_assignment));if(!r.success){panel.innerHTML='<div class="alert alert-danger mb-0">'+esc(r.message)+'</div>';return;}const rows=r.data||[];if(!rows.length){panel.innerHTML='<p class="text-muted small mb-0">'+esc(S('protocols_history_empty','Aún no hay ejecuciones registradas.'))+'</p>';return;}panel.innerHTML='';rows.forEach(e=>{const item=document.createElement('div');item.className='execution-item';item.innerHTML='<div class="d-flex justify-content-between gap-2 flex-wrap"><div><strong>'+esc(S('protocols_cycle','Ciclo'))+' '+esc(e.cycle_number)+'</strong><div class="small text-muted">'+esc(S('protocols_submitted','Enviado'))+': '+esc(fmtDate(e.submitted_at))+(e.reviewed_at?' · '+esc(S('protocols_reviewed_at','Revisado'))+': '+esc(fmtDate(e.reviewed_at)):'')+'</div><span class="pill '+resultClass(e.result)+'">'+esc(resultLabel(e.result))+'</span></div><button type="button" class="btn btn-outline-custom btn-sm" data-detail>'+esc(S('protocols_view','Ver'))+'</button></div><div class="mt-2 d-none" data-detail-box></div>';item.querySelector('[data-detail]').addEventListener('click',()=>loadExecutionDetail(e.id_protocol_execution,item.querySelector('[data-detail-box]')));panel.appendChild(item);});}

    async function loadExecutionDetail(id,box){if(!box.classList.contains('d-none')){box.classList.add('d-none');return;}box.classList.remove('d-none');box.innerHTML='<span class="text-muted small">...</span>';const r=await api('./ejecucion-detalle.php?id_protocol_execution='+encodeURIComponent(id));if(!r.success){box.innerHTML='<div class="alert alert-danger mb-0">'+esc(r.message)+'</div>';return;}let html='';(r.data.submissions||[]).forEach(s=>{html+='<div class="mt-2"><strong>'+esc(s.form_name)+'</strong>';const answers=s.detalle&&s.detalle.respuestas?s.detalle.respuestas:[];answers.forEach(a=>{let value=a.display_value;if(Array.isArray(value))value=value.join(', ');if(a.file_path)value='<a href="../formularios/archivo-descargar.php?id_answer='+encodeURIComponent(a.id_answer)+'"><i class="bi bi-download"></i> '+esc(a.original_name||'Archivo')+'</a>';else value=esc(value==null||value===''?'—':value);html+='<div class="small border-bottom py-1"><b>'+esc(a.label)+':</b> '+value+'</div>';});html+='</div>';});if(r.data.review_notes)html+='<div class="small mt-2"><b>Obs.:</b> '+esc(r.data.review_notes)+'</div>';box.innerHTML=html||'—';}

    // El cambio de idioma (confirmación + guardado en perfil) lo maneja js/lang-switcher.js.
    load();
});
