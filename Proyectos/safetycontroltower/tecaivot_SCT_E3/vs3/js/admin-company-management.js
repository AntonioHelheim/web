document.addEventListener("DOMContentLoaded", () => {
    const root=document.querySelector("[data-admin-company-management]");
    if (!root) return;

    const csrf=root.dataset.csrf || "";
    const companyRole=root.dataset.companyRole || "";
    const isJefatura=companyRole==="jefatura";
    const companyReadOnly=isJefatura;
    const centersReadOnly=false;
    const scopeReadOnly=false;
    const status=root.querySelector("[data-company-status]");
    const modes=Array.from(root.querySelectorAll("[data-company-mode]"));
    const panels=Array.from(root.querySelectorAll("[data-company-panel]"));

    const companyForm=root.querySelector("[data-company-form]");
    const centerForm=root.querySelector("[data-center-form]");
    const centersList=root.querySelector("[data-centers-list]");
    const profileSummary=root.querySelector("[data-company-profile-summary]");
    const projectsList=root.querySelector("[data-projects-list]");
    const scopeStats=root.querySelector("[data-company-scope-stats]");

    const projectForm=root.querySelector("[data-project-form]");
    const gbuSelect=root.querySelector("[data-company-gbu]");
    const siteSelect=root.querySelector("[data-company-site]");
    const projectSelect=root.querySelector("[data-company-project]");
    const gbuNew=root.querySelector("[data-company-gbu-new]");
    const siteNew=root.querySelector("[data-company-site-new]");
    const projectNew=root.querySelector("[data-company-project-new]");
    const scopeSummary=root.querySelector("[data-company-scope-summary]");

    const editGbuButton=root.querySelector("[data-edit-gbu]");
    const deleteGbuButton=root.querySelector("[data-delete-gbu]");
    const gbuEditor=root.querySelector("[data-gbu-editor]");
    const gbuRenameInput=root.querySelector("[data-gbu-rename-input]");
    const editSiteButton=root.querySelector("[data-edit-site]");
    const deleteSiteButton=root.querySelector("[data-delete-site]");
    const editProjectButton=root.querySelector("[data-edit-project]");
    const deleteProjectButton=root.querySelector("[data-delete-project]");
    const siteEditor=root.querySelector("[data-site-editor]");
    const siteRenameInput=root.querySelector("[data-site-rename-input]");

    let data=null;
    let loaded=false;

    const NEW_VALUE="__new__";

    const esc=value=>String(value ?? "")
        .replaceAll("&","&amp;").replaceAll("<","&lt;")
        .replaceAll(">","&gt;").replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");

    function setStatus(message){
        if(status) status.textContent=message || "";
    }

    function setMode(mode){
        panels.forEach(panel=>{
            panel.hidden=panel.dataset.companyPanel!==mode;
        });

        modes.forEach(button=>{
            const active=button.dataset.companyMode===mode;
            button.classList.toggle("is-active",active);
            button.setAttribute(
                "aria-expanded",
                active ? "true" : "false"
            );
        });
    }

    function applyPermissions(){
        if(companyReadOnly && companyForm){
            companyForm
                .querySelectorAll("input,select,textarea")
                .forEach(field=>field.disabled=true);

            companyForm
                .querySelectorAll('button[type="submit"]')
                .forEach(button=>button.hidden=true);
        }

        if(centersReadOnly){
            root.querySelectorAll(
                "[data-new-center],[data-edit-center]"
            ).forEach(button=>button.disabled=true);

            centerForm
                ?.querySelectorAll("input,select,textarea")
                .forEach(field=>field.disabled=true);

            centerForm
                ?.querySelectorAll('button[type="submit"]')
                .forEach(button=>button.hidden=true);
        }

        if(scopeReadOnly){
            root.querySelectorAll(
                "[data-new-project],[data-edit-project],"
                + "[data-edit-gbu],[data-delete-gbu],"
                + "[data-edit-site],[data-delete-site],"
                + "[data-delete-project],"
                + "[data-scope-project-edit]"
            ).forEach(button=>button.disabled=true);

            [
                gbuSelect,
                siteSelect,
                projectSelect,
                gbuNew,
                siteNew,
                projectNew,
                gbuRenameInput
            ].forEach(field=>{
                if(field) field.disabled=true;
            });

            projectForm
                ?.querySelectorAll("input,select,textarea")
                .forEach(field=>field.disabled=true);

            projectForm
                ?.querySelectorAll('button[type="submit"]')
                .forEach(button=>button.hidden=true);
        }
    }

    async function request(url,options={}){
        const response=await fetch(url,{
            credentials:"same-origin",
            headers:{
                "Accept":"application/json",
                ...(options.body ? {"Content-Type":"application/json"} : {})
            },
            ...options
        });
        const payload=await response.json();

        if(!response.ok || !payload.success){
            throw new Error(payload.message || "Error");
        }

        return payload;
    }

    function option(select,value,label){
        const node=document.createElement("option");
        node.value=value;
        node.textContent=label;
        select.appendChild(node);
    }

    async function load(){
        setStatus("...");
        const payload=await request("api/empresa/admin-datos.php");
        data=payload.data || {};
        loaded=true;
        render();
        setStatus("");
    }

    function render(){
        if(!data) return;

        const company=data.company || {};
        if(companyForm){
            Object.entries(company).forEach(([key,value])=>{
                const field=companyForm.elements[key];
                if(!field) return;
                if(field.type==="checkbox"){
                    field.checked=Number(value)===1;
                }else{
                    field.value=value ?? "";
                }
            });
        }

        if(profileSummary){
            profileSummary.innerHTML=(data.profiles || []).map(row=>`
                <div class="admin-company-summary-card">
                  <span>${esc(row.label)}</span>
                  <strong>${Number(row.total || 0)}</strong>
                </div>
            `).join("");
        }

        if(centersList){
            const rows=data.centers || [];
            centersList.innerHTML=rows.length ? rows.map(row=>`
                <article class="admin-company-list-card" data-state="${Number(row.state)===1 ? "active":"inactive"}">
                  <div>
                    <strong>${esc(row.name)}</strong>
                    <span><i class="bi bi-geo-alt"></i>${esc(row.address || "")}</span>
                    <small>${esc(row.description || "")}</small>
                  </div>
                  <button type="button" class="btn btn-outline-custom" data-edit-center="${Number(row.id_company_center)}">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                </article>
            `).join("") : `<p class="admin-company-empty">—</p>`;
        }

        renderScopeInventory();
        renderScopeCatalog();
        applyPermissions();
    }

    function renderScopeInventory(){
        const catalog=data?.scope_catalog || {
            gbus:[],
            sites:[],
            projects:[]
        };

        if(scopeStats){
            scopeStats.innerHTML=`
                <div data-tone="cyan"><span>${esc(window.SCT_COMPANY_I18N?.gbus || "GBU")}</span><strong>${catalog.gbus.length}</strong></div>
                <div data-tone="violet"><span>${esc(window.SCT_COMPANY_I18N?.sites || "Sites")}</span><strong>${catalog.sites.length}</strong></div>
                <div data-tone="green"><span>${esc(window.SCT_COMPANY_I18N?.projects || "Projects")}</span><strong>${catalog.projects.length}</strong></div>
            `;
        }

        if(!projectsList) return;

        projectsList.innerHTML=catalog.projects.length
            ? catalog.projects.map(row=>`
                <article class="admin-company-list-card" data-state="${Number(row.state)===1 ? "active":"inactive"}">
                  <div>
                    <strong>${esc(row.gbu_code || "—")} · ${esc(row.site_name || "—")}</strong>
                    <span><i class="bi bi-diagram-3"></i>${esc(row.name || "")}</span>
                    <small>${esc(row.description || "")}</small>
                  </div>
                  <button type="button" class="btn btn-outline-custom" data-scope-project-edit="${Number(row.id_project)}">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                </article>
              `).join("")
            : `<p class="admin-company-empty">—</p>`;
    }

    function selectedGbu(){
        return gbuSelect?.value===NEW_VALUE
            ? (gbuNew?.value || "").trim()
            : (gbuSelect?.value || "").trim();
    }

    function selectedSite(){
        return siteSelect?.value===NEW_VALUE
            ? (siteNew?.value || "").trim()
            : (siteSelect?.value || "").trim();
    }

    function selectedProjectName(){
        return projectSelect?.value===NEW_VALUE
            ? (projectNew?.value || "").trim()
            : (
                (data?.scope_catalog?.projects || []).find(
                    row=>String(row.id_project)===String(projectSelect?.value || "")
                )?.name || ""
            ).trim();
    }

    function renderScopeCatalog(){
        if(!gbuSelect || !siteSelect || !projectSelect) return;

        const catalog=data?.scope_catalog || {
            gbus:[],
            sites:[],
            projects:[]
        };

        const currentGbu=gbuSelect.value;
        const currentSite=siteSelect.value;
        const currentProject=projectSelect.value;

        gbuSelect.replaceChildren();
        option(gbuSelect,"","");
        catalog.gbus.forEach(gbu=>option(gbuSelect,gbu,gbu));
        option(gbuSelect,NEW_VALUE,"＋");

        if([...gbuSelect.options].some(o=>o.value===currentGbu)){
            gbuSelect.value=currentGbu;
        }

        const gbu=selectedGbu();
        siteSelect.replaceChildren();
        option(siteSelect,"","");
        catalog.sites
            .filter(row=>!gbu || row.gbu_code===gbu)
            .forEach(row=>option(siteSelect,row.site_name,row.site_name));
        option(siteSelect,NEW_VALUE,"＋");

        if([...siteSelect.options].some(o=>o.value===currentSite)){
            siteSelect.value=currentSite;
        }

        const site=selectedSite();
        projectSelect.replaceChildren();
        option(projectSelect,"","");
        catalog.projects
            .filter(row=>{
                if(gbu && row.gbu_code!==gbu) return false;
                if(site && row.site_name!==site) return false;
                return true;
            })
            .forEach(row=>option(projectSelect,row.id_project,row.name));
        option(projectSelect,NEW_VALUE,"＋");

        if([...projectSelect.options].some(o=>o.value===currentProject)){
            projectSelect.value=currentProject;
        }

        syncScopeUi();
    }

    function syncScopeUi(){
        if(!gbuSelect || !siteSelect || !projectSelect) return;

        const gbuIsNew=gbuSelect.value===NEW_VALUE;
        const gbu=selectedGbu();

        gbuNew.hidden=!gbuIsNew;
        if(!gbuIsNew) gbuNew.value="";

        siteSelect.disabled=!gbu;
        const siteIsNew=siteSelect.value===NEW_VALUE;
        const site=selectedSite();
        siteNew.hidden=!siteIsNew;
        if(!siteIsNew) siteNew.value="";

        projectSelect.disabled=!gbu || !site;
        const projectIsNew=projectSelect.value===NEW_VALUE;
        projectNew.hidden=!projectIsNew;
        if(!projectIsNew) projectNew.value="";

        const existingGbu=Boolean(
            gbuSelect.value
            && gbuSelect.value!==NEW_VALUE
        );
        editGbuButton.disabled=!existingGbu;
        deleteGbuButton.disabled=!existingGbu;

        const existingSite=Boolean(
            existingGbu
            && siteSelect.value
            && siteSelect.value!==NEW_VALUE
        );
        editSiteButton.disabled=!existingSite;
        deleteSiteButton.disabled=!existingSite;

        const existingProject=Boolean(
            existingSite
            && projectSelect.value
            && projectSelect.value!==NEW_VALUE
        );
        editProjectButton.disabled=!existingProject;
        deleteProjectButton.disabled=!existingProject;

        updateProjectEditor();
    }

    function updateProjectEditor(){
        const gbu=selectedGbu();
        const site=selectedSite();
        const projectValue=projectSelect?.value || "";

        const isNew=projectValue===NEW_VALUE;
        const existing=(data?.scope_catalog?.projects || []).find(
            row=>String(row.id_project)===String(projectValue)
        );

        const name=selectedProjectName();

        if(!gbu || !site || (!existing && !isNew)){
            projectForm.hidden=true;
            return;
        }

        if(isNew && !name){
            projectForm.hidden=true;
            return;
        }

        projectForm.hidden=false;
        projectForm.elements.id_project.value=existing?.id_project || "";
        projectForm.elements.gbu_code.value=gbu;
        projectForm.elements.site_name.value=site;
        projectForm.elements.description.value=existing?.description || "";
        projectForm.elements.state.value=String(existing?.state ?? 1);

        // name no es visible en el editor porque se selecciona arriba.
        let nameHidden=projectForm.elements.name;
        if(!nameHidden){
            nameHidden=document.createElement("input");
            nameHidden.type="hidden";
            nameHidden.name="name";
            projectForm.appendChild(nameHidden);
        }
        nameHidden.value=existing?.name || name;

        if(scopeSummary){
            scopeSummary.innerHTML=`
                <span><strong>${esc(gbu)}</strong></span>
                <i class="bi bi-chevron-right"></i>
                <span><strong>${esc(site)}</strong></span>
                <i class="bi bi-chevron-right"></i>
                <span><strong>${esc(existing?.name || name)}</strong></span>
            `;
        }
    }

    modes.forEach(button=>{
        button.addEventListener("click",async()=>{
            const mode=button.dataset.companyMode || "";
            setMode(mode);
            if(!loaded){
                try{
                    await load();
                }catch(error){
                    setStatus(error.message);
                }
            }
        });
    });

    companyForm?.addEventListener("submit",async event=>{
        if(companyReadOnly){ event.preventDefault(); return; }
        event.preventDefault();
        const body=Object.fromEntries(new FormData(companyForm).entries());
        body.csrf_token=csrf;
        body.is_contractor=companyForm.elements.is_contractor?.checked ? 1 : 0;

        try{
            const payload=await request(
                "api/empresa/admin-empresa-guardar.php",
                {method:"POST",body:JSON.stringify(body)}
            );
            setStatus(payload.message);
            await load();
        }catch(error){
            setStatus(error.message);
        }
    });

    root.querySelector("[data-new-center]")?.addEventListener("click",()=>{
        centerForm?.reset();
        if(centerForm?.elements.id_company_center){
            centerForm.elements.id_company_center.value="";
        }
        centerForm.hidden=false;
        centerForm.scrollIntoView({behavior:"smooth",block:"nearest"});
    });

    root.querySelector("[data-center-cancel]")?.addEventListener("click",()=>{
        centerForm.hidden=true;
    });

    centersList?.addEventListener("click",event=>{
        const button=event.target.closest("[data-edit-center]");
        if(!button || !data) return;

        const id=Number(button.dataset.editCenter);
        const row=(data.centers || []).find(
            item=>Number(item.id_company_center)===id
        );
        if(!row) return;

        ["id_company_center","name","address","description","state"]
            .forEach(key=>{
                if(centerForm.elements[key]){
                    centerForm.elements[key].value=row[key] ?? "";
                }
            });

        centerForm.hidden=false;
        centerForm.scrollIntoView({behavior:"smooth",block:"nearest"});
    });

    centerForm?.addEventListener("submit",async event=>{
        if(centersReadOnly){ event.preventDefault(); return; }
        event.preventDefault();
        const body=Object.fromEntries(new FormData(centerForm).entries());
        body.csrf_token=csrf;

        try{
            const payload=await request(
                "api/empresa/admin-sede-guardar.php",
                {method:"POST",body:JSON.stringify(body)}
            );
            setStatus(payload.message);
            centerForm.hidden=true;
            await load();
        }catch(error){
            setStatus(error.message);
        }
    });

    gbuSelect?.addEventListener("change",()=>{
        siteSelect.value="";
        projectSelect.value="";
        siteNew.value="";
        projectNew.value="";
        renderScopeCatalog();
    });

    gbuNew?.addEventListener("input",()=>{
        siteSelect.value="";
        projectSelect.value="";
        renderScopeCatalog();
    });

    siteSelect?.addEventListener("change",()=>{
        projectSelect.value="";
        projectNew.value="";
        renderScopeCatalog();
    });

    siteNew?.addEventListener("input",()=>{
        projectSelect.value="";
        renderScopeCatalog();
    });

    projectSelect?.addEventListener("change",syncScopeUi);
    projectNew?.addEventListener("input",updateProjectEditor);

    editProjectButton?.addEventListener("click",()=>{
        if(
            !projectSelect.value
            || projectSelect.value===NEW_VALUE
        ) return;

        updateProjectEditor();

        projectForm?.scrollIntoView({
            behavior:"smooth",
            block:"nearest"
        });
    });

    root.querySelector("[data-project-cancel]")?.addEventListener("click",()=>{
        projectForm.hidden=true;
        projectSelect.value="";
        syncScopeUi();
    });

    projectsList?.addEventListener("click",event=>{
        const button=event.target.closest("[data-scope-project-edit]");
        if(!button || !data) return;

        const id=Number(button.dataset.scopeProjectEdit || 0);
        const row=(data.scope_catalog?.projects || []).find(
            item=>Number(item.id_project)===id
        );
        if(!row) return;

        gbuSelect.value=row.gbu_code || "";
        renderScopeCatalog();
        siteSelect.value=row.site_name || "";
        renderScopeCatalog();
        projectSelect.value=String(row.id_project);
        syncScopeUi();

        projectForm?.scrollIntoView({
            behavior:"smooth",
            block:"nearest"
        });
    });

    projectForm?.addEventListener("submit",async event=>{
        if(scopeReadOnly){ event.preventDefault(); return; }
        event.preventDefault();

        const gbu=selectedGbu();
        const site=selectedSite();
        const projectName=selectedProjectName();

        if(!gbu || !site || !projectName){
            setStatus("");
            projectForm.reportValidity();
            return;
        }

        const body=Object.fromEntries(new FormData(projectForm).entries());
        body.gbu_code=gbu;
        body.site_name=site;
        body.name=projectName;
        body.csrf_token=csrf;

        try{
            const payload=await request(
                "api/empresa/admin-proyecto-guardar.php",
                {method:"POST",body:JSON.stringify(body)}
            );
            setStatus(payload.message);
            projectForm.hidden=true;
            await load();
        }catch(error){
            setStatus(error.message);
        }
    });

    editSiteButton?.addEventListener("click",()=>{
        const site=siteSelect.value;

        if(
            !gbuSelect.value
            || gbuSelect.value===NEW_VALUE
            || !site
            || site===NEW_VALUE
        ) return;

        siteRenameInput.value=site;
        siteEditor.hidden=false;
        siteEditor.scrollIntoView({
            behavior:"smooth",
            block:"nearest"
        });
    });

    root.querySelector("[data-site-rename-cancel]")
        ?.addEventListener("click",()=>{
            siteEditor.hidden=true;
        });

    root.querySelector("[data-site-rename-save]")
        ?.addEventListener("click",async()=>{
            const gbu=gbuSelect.value;
            const oldSite=siteSelect.value;
            const newSite=(siteRenameInput.value || "").trim();

            if(!gbu || !oldSite || !newSite) return;

            if(!window.confirm(
                `${oldSite} → ${newSite}`
            )) return;

            try{
                const payload=await request(
                    "api/empresa/admin-sitio-accion.php",
                    {
                        method:"POST",
                        body:JSON.stringify({
                            action:"rename",
                            gbu_code:gbu,
                            site_name:oldSite,
                            new_site_name:newSite,
                            csrf_token:csrf
                        })
                    }
                );

                setStatus(payload.message);
                siteEditor.hidden=true;
                await load();

                gbuSelect.value=gbu;
                renderScopeCatalog();
                siteSelect.value=newSite;
                renderScopeCatalog();
            }catch(error){
                setStatus(error.message);
            }
        });

    deleteSiteButton?.addEventListener("click",async()=>{
        const gbu=gbuSelect.value;
        const site=siteSelect.value;

        if(
            !gbu
            || gbu===NEW_VALUE
            || !site
            || site===NEW_VALUE
        ) return;

        if(!window.confirm(
            `${gbu} · ${site}\n\n`
            + (window.SCT_COMPANY_I18N?.deleteSiteConfirm
                || "Se desactivarán sólo los proyectos de este Sitio.")
        )) return;

        try{
            const payload=await request(
                "api/empresa/admin-sitio-accion.php",
                {
                    method:"POST",
                    body:JSON.stringify({
                        action:"deactivate",
                        gbu_code:gbu,
                        site_name:site,
                        csrf_token:csrf
                    })
                }
            );

            setStatus(payload.message);
            await load();
            gbuSelect.value=gbu;
            renderScopeCatalog();
        }catch(error){
            setStatus(error.message);
        }
    });

    deleteProjectButton?.addEventListener("click",async()=>{
        const id=Number(projectSelect.value || 0);

        if(!id) return;

        const row=(data?.scope_catalog?.projects || []).find(
            item=>Number(item.id_project)===id
        );

        if(!window.confirm(
            `${row?.name || id}\n\n`
            + (window.SCT_COMPANY_I18N?.deleteProjectConfirm
                || "Se desactivará sólo este Proyecto.")
        )) return;

        try{
            const payload=await request(
                "api/empresa/admin-proyecto-eliminar.php",
                {
                    method:"POST",
                    body:JSON.stringify({
                        id_project:id,
                        csrf_token:csrf
                    })
                }
            );

            setStatus(payload.message);
            await load();
            projectSelect.value="";
            renderScopeCatalog();
        }catch(error){
            setStatus(error.message);
        }
    });

    editGbuButton?.addEventListener("click",()=>{
        if(!gbuSelect.value || gbuSelect.value===NEW_VALUE) return;
        gbuRenameInput.value=gbuSelect.value;
        gbuEditor.hidden=false;
        gbuEditor.scrollIntoView({behavior:"smooth",block:"nearest"});
    });

    root.querySelector("[data-gbu-rename-cancel]")?.addEventListener("click",()=>{
        gbuEditor.hidden=true;
    });

    root.querySelector("[data-gbu-rename-save]")?.addEventListener("click",async()=>{
        const oldGbu=gbuSelect.value;
        const newGbu=(gbuRenameInput.value || "").trim();

        if(!oldGbu || !newGbu) return;

        const message=`${oldGbu} → ${newGbu}`;
        if(!window.confirm(message)) return;

        try{
            const payload=await request(
                "api/empresa/admin-gbu-accion.php",
                {
                    method:"POST",
                    body:JSON.stringify({
                        action:"rename",
                        gbu_code:oldGbu,
                        new_gbu_code:newGbu,
                        csrf_token:csrf
                    })
                }
            );
            setStatus(payload.message);
            gbuEditor.hidden=true;
            await load();
            gbuSelect.value=newGbu;
            renderScopeCatalog();
        }catch(error){
            setStatus(error.message);
        }
    });

    deleteGbuButton?.addEventListener("click",async()=>{
        const gbu=gbuSelect.value;
        if(!gbu || gbu===NEW_VALUE) return;

        if(!window.confirm(
            `${gbu}\n\n`
            + "La GBU y sus proyectos quedarán inactivos. "
            + "La trazabilidad histórica se conservará."
        )) return;

        try{
            const payload=await request(
                "api/empresa/admin-gbu-accion.php",
                {
                    method:"POST",
                    body:JSON.stringify({
                        action:"deactivate",
                        gbu_code:gbu,
                        csrf_token:csrf
                    })
                }
            );
            setStatus(payload.message);
            await load();
            gbuSelect.value="";
            renderScopeCatalog();
        }catch(error){
            setStatus(error.message);
        }
    });

    setMode("");
});
