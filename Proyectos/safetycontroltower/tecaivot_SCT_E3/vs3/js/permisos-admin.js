document.addEventListener('DOMContentLoaded', () => {
  const shell=document.querySelector('.perm-shell[data-csrf-token]'); if(!shell)return;
  const csrf=shell.dataset.csrfToken||'';
  const company=document.getElementById('companySelect'), role=document.getElementById('roleSelect');
  const groups=document.getElementById('permissionsGroups'), status=document.getElementById('permissionsStatus'), alertBox=document.getElementById('permissionsAlert');
  const save=document.getElementById('savePermissionsBtn'), all=document.getElementById('selectAllBtn'), clear=document.getElementById('clearAllBtn'), count=document.getElementById('selectedCount'), roleLabel=document.getElementById('roleLabel');
  const i18n=JSON.parse(document.getElementById('permissionsI18n')?.textContent||'{}');
  let protectedCodes=new Set();
  const api=async(url,options={})=>{const r=await fetch(url,{credentials:'same-origin',...options});let j=null;try{j=await r.json()}catch{}if(!r.ok||!j?.success)throw new Error(j?.message||i18n.permissions_error||'Error');return j.data;};
  const showAlert=(msg,type='danger')=>{alertBox.className=`alert alert-${type}`;alertBox.textContent=msg;alertBox.classList.remove('d-none');};
  const hideAlert=()=>alertBox.classList.add('d-none');
  const updateCount=()=>{const n=groups.querySelectorAll('input[type=checkbox]:checked').length;count.textContent=(i18n.permissions_selected_count||'{count} seleccionados').replace('{count}',String(n));};
  const groupName=code=>code.split('.')[0].replaceAll('_',' ');
  const renderPermissions=data=>{
    protectedCodes=new Set(data.protected_codes||[]); groups.innerHTML='';
    const map=new Map(), assigned=new Set((data.assigned_ids||[]).map(Number));
    for(const p of data.permissions||[]){const key=groupName(p.code);if(!map.has(key))map.set(key,[]);map.get(key).push(p)}
    for(const [key,items] of map){const box=document.createElement('section');box.className='perm-group';const h=document.createElement('h3');h.textContent=key;box.appendChild(h);
      for(const p of items){const row=document.createElement('label');row.className='perm-item';const cb=document.createElement('input');cb.type='checkbox';cb.className='form-check-input mt-1';cb.value=String(p.id_permission);cb.dataset.code=p.code;cb.checked=assigned.has(Number(p.id_permission));if(protectedCodes.has(p.code)){cb.checked=true;cb.disabled=true}
        const txt=document.createElement('div');const code=document.createElement('div');code.className='perm-code';code.textContent=p.code;txt.appendChild(code);const desc=document.createElement('div');desc.className='perm-desc';desc.textContent=p.description||'';txt.appendChild(desc);if(protectedCodes.has(p.code)){const lock=document.createElement('div');lock.className='perm-protected';lock.textContent=i18n.permissions_protected||'Protegido';txt.appendChild(lock)}row.append(cb,txt);box.appendChild(row);}
      groups.appendChild(box);
    }
    roleLabel.textContent=data.role?.display_name||''; groups.classList.remove('d-none'); status.classList.add('d-none'); save.disabled=false;all.disabled=false;clear.disabled=false;updateCount();
  };
  const loadCompanies=async()=>{try{const rows=await api('empresas-listar.php');for(const c of rows){const o=document.createElement('option');o.value=c.id_company;o.textContent=c.razon_social;company.appendChild(o)}status.textContent=i18n.permissions_select_company||'Selecciona una empresa.'}catch(e){status.className='alert alert-danger';status.textContent=e.message}};
  company.addEventListener('change',async()=>{hideAlert();role.innerHTML=`<option value="">${i18n.permissions_select_role||'Selecciona un rol'}</option>`;role.disabled=true;groups.classList.add('d-none');save.disabled=all.disabled=clear.disabled=true;if(!company.value)return;status.className='alert alert-info';status.textContent=i18n.permissions_loading||'Cargando...';status.classList.remove('d-none');try{const rows=await api(`roles-listar.php?id_company=${encodeURIComponent(company.value)}`);for(const r of rows){const o=document.createElement('option');o.value=r.id_role_group;o.textContent=r.display_name;role.appendChild(o)}role.disabled=false;if(!rows.length)status.textContent=i18n.permissions_no_roles||'Sin roles disponibles';else status.textContent=i18n.permissions_select_role||'Selecciona un rol.'}catch(e){status.className='alert alert-danger';status.textContent=e.message}});
  role.addEventListener('change',async()=>{hideAlert();groups.classList.add('d-none');save.disabled=all.disabled=clear.disabled=true;if(!role.value)return;status.className='alert alert-info';status.textContent=i18n.permissions_loading||'Cargando...';status.classList.remove('d-none');try{renderPermissions(await api(`permisos-listar.php?id_role_group=${encodeURIComponent(role.value)}`))}catch(e){status.className='alert alert-danger';status.textContent=e.message}});
  groups.addEventListener('change',updateCount);
  all.addEventListener('click',()=>{groups.querySelectorAll('input[type=checkbox]:not(:disabled)').forEach(x=>x.checked=true);updateCount()});
  clear.addEventListener('click',()=>{groups.querySelectorAll('input[type=checkbox]:not(:disabled)').forEach(x=>x.checked=false);updateCount()});
  save.addEventListener('click',async()=>{hideAlert();save.disabled=true;const ids=[...groups.querySelectorAll('input[type=checkbox]:checked')].map(x=>Number(x.value));try{await api('guardar-matriz.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf_token:csrf,id_role_group:Number(role.value),permission_ids:ids})});showAlert(i18n.permissions_saved||'Permisos guardados.','success');role.dispatchEvent(new Event('change'))}catch(e){showAlert(e.message)}finally{save.disabled=false}});
  // El cambio de idioma (confirmación + guardado en perfil) lo maneja js/lang-switcher.js.
  loadCompanies();
});
