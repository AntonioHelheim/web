document.addEventListener("DOMContentLoaded", () => {
    const root=document.querySelector("[data-admin-user-management]");
    if (!root) return;

    const i18nNode=document.getElementById("adminUserI18n");
    let I={};
    try { I=JSON.parse(i18nNode?.textContent || "{}"); } catch (_) {}
    const tx=(key,fallback="")=>I[key] || fallback;

    const scopeSeedNode=document.getElementById("adminUserScopeSeed");
    const orgSeedNode=document.getElementById("adminCreateOrgSeed");

    let scopeSeed={gbus:[],sites:[],projects:[]};
    let orgSeed={
        jefaturas:[],
        gerencias:[],
        roles:[],
        default_jefatura:""
    };

    try {
        const parsed=JSON.parse(scopeSeedNode?.textContent || "{}");
        scopeSeed={
            gbus:Array.isArray(parsed.gbus) ? parsed.gbus : [],
            sites:Array.isArray(parsed.sites) ? parsed.sites : [],
            projects:Array.isArray(parsed.projects) ? parsed.projects : []
        };
    } catch (_) {}

    try {
        const parsed=JSON.parse(orgSeedNode?.textContent || "{}");
        orgSeed={
            jefaturas:Array.isArray(parsed.jefaturas)
                ? parsed.jefaturas
                : [],
            gerencias:Array.isArray(parsed.gerencias)
                ? parsed.gerencias
                : [],
            roles:Array.isArray(parsed.roles)
                ? parsed.roles
                : [],
            default_jefatura:String(
                parsed.default_jefatura || ""
            )
        };
    } catch (_) {}

    const gbu=document.getElementById("adminUserGbu");
    const site=document.getElementById("adminUserSite");
    const project=document.getElementById("adminUserProject");
    const search=document.getElementById("adminUserSearch");
    const searchBtn=document.getElementById("adminUserSearchBtn");
    const resetBtn=document.getElementById("adminUserResetBtn");
    const status=document.getElementById("adminUserStatus");
    const results=document.getElementById("adminUserResults");
    const userModeButtons=Array.from(
        root.querySelectorAll("[data-admin-user-mode]")
    );
    const searchPanel=root.querySelector("[data-admin-user-search-panel]");
    const newButton=root.querySelector("[data-admin-new-person]");
    const createPanel=root.querySelector("[data-admin-create-person]");
    const closeCreate=document.querySelector("[data-admin-new-person-close]");
    const createForm=document.querySelector("[data-admin-new-person-form]");

    let filters={
        gbus:[...scopeSeed.gbus],
        sites:[...scopeSeed.sites],
        projects:[...scopeSeed.projects]
    };
    let filtersLoaded=false;
    let creationOptions={
        // E3 VS2: creación reutiliza inmediatamente el mismo catálogo
        // canónico que Buscar/Editar, sin depender de admin-nuevo-opciones.
        filters:{
            gbus:[...scopeSeed.gbus],
            sites:[...scopeSeed.sites],
            projects:[...scopeSeed.projects]
        },
        defaults:{gbu:"",site:"",id_project:null},
        default_jefatura:orgSeed.default_jefatura,
        jefaturas:[...orgSeed.jefaturas],
        gerencias:[...orgSeed.gerencias],
        positions:[],
        contractors:[],
        roles:[...orgSeed.roles]
    };
    let creationDefaultsApplied=false;

    const esc=value => String(value ?? "")
        .replaceAll("&","&amp;").replaceAll("<","&lt;")
        .replaceAll(">","&gt;").replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");

    const norm=value=>String(value ?? "")
        .trim()
        .toLocaleLowerCase();

    function normalizeScopeCatalog(value){
        const source=value || {};
        return {
            gbus:Array.isArray(source.gbus) ? source.gbus : [],
            sites:Array.isArray(source.sites) ? source.sites : [],
            projects:Array.isArray(source.projects) ? source.projects : []
        };
    }

    function scopeHasData(value){
        const catalog=normalizeScopeCatalog(value);
        return Boolean(
            catalog.gbus.length
            || catalog.sites.length
            || catalog.projects.length
        );
    }

    function effectiveScopeCatalog(primary){
        const catalog=normalizeScopeCatalog(primary);
        return scopeHasData(catalog)
            ? catalog
            : normalizeScopeCatalog(scopeSeed);
    }

    function effectiveOrgList(primary,fallback){
        return Array.isArray(primary) && primary.length
            ? primary
            : (Array.isArray(fallback) ? [...fallback] : []);
    }

    function mergeOrgCatalog(incoming={}){
        return {
            jefaturas:effectiveOrgList(
                incoming.jefaturas,
                orgSeed.jefaturas
            ),
            gerencias:effectiveOrgList(
                incoming.gerencias,
                orgSeed.gerencias
            ),
            roles:effectiveOrgList(
                incoming.roles,
                orgSeed.roles
            ),
            default_jefatura:String(
                incoming.default_jefatura
                || orgSeed.default_jefatura
                || ""
            )
        };
    }

    function fillSelect(select,items,valueKey,labelFn){
        const current=select.value;
        select.querySelectorAll("option:not(:first-child)").forEach(option=>option.remove());
        items.forEach(item=>{
            const option=document.createElement("option");
            option.value=typeof item==="string" ? item : item[valueKey];
            option.textContent=typeof item==="string" ? item : labelFn(item);
            select.appendChild(option);
        });
        if ([...select.options].some(o=>o.value===current)) select.value=current;
    }

    function syncFilters(){
        fillSelect(gbu,filters.gbus,"",x=>x);
        const sites=filters.sites.filter(
            item=>!gbu.value || norm(item.gbu)===norm(gbu.value)
        );
        fillSelect(site,sites,"site",item=>item.site);
        const projects=filters.projects.filter(item=>{
            if (
                gbu.value
                && norm(item.gbu)!==norm(gbu.value)
            ) return false;
            if (
                site.value
                && norm(item.site)!==norm(site.value)
            ) return false;
            return true;
        });
        fillSelect(project,projects,"id_project",item=>{
            const prefix=[item.gbu,item.site].filter(Boolean).join(" · ");
            return prefix ? `${prefix} · ${item.name}` : item.name;
        });
    }

    function hasScopeFilter(){ return Boolean(gbu.value || site.value || project.value); }
    function setIdleMessage(){
        results.replaceChildren();
        status.textContent=tx("idle","Selecciona GBU, Sitio o Proyecto y luego presiona Buscar.");
    }

    function roleTone(level){
        switch (Number(level)) {
            case 2:return "manager"; case 3:return "admin"; case 4:return "supervisor";
            case 5:return "user"; case 6:return "medical"; case 7:return "contractor";
            default:return "unknown";
        }
    }

    function render(users){
        results.replaceChildren();
        if (!users.length){
            status.textContent=tx("empty","No se encontraron usuarios con los filtros seleccionados.");
            return;
        }
        status.textContent=tx("found","{count} usuario(s) encontrado(s).").replace("{count}",users.length);
        users.forEach(user=>{
            const card=document.createElement("article");
            card.className="admin-user-result-card";
            card.dataset.role=roleTone(user.access_level);
            const active=Number(user.state)===1;
            card.innerHTML=`
              <div class="admin-user-result-card__icon"><i class="bi bi-person-badge"></i></div>
              <div class="admin-user-result-card__main">
                <div class="admin-user-result-card__title">
                  <strong>${esc(user.name)} ${esc(user.lastname)}</strong>
                  <span>${esc(user.id_users)}</span>
                  <span class="admin-user-role-badge" data-role="${roleTone(user.access_level)}">${esc(user.role_label || user.role_names || tx("no_role","Sin perfil"))}</span>
                  <span class="admin-user-account-badge" data-state="${active ? "active" : "inactive"}">${active ? esc(tx("active","Activo")) : esc(tx("inactive","Inactivo"))}</span>
                </div>
                <div class="admin-user-result-card__meta">
                  <span><i class="bi bi-credit-card-2-front"></i>${esc(user.rut)}</span>
                  <span><i class="bi bi-building-gear"></i>${esc(user.gbu || tx("no_gbu","Sin GBU"))}</span>
                  <span><i class="bi bi-geo-alt"></i>${esc(user.site || tx("no_site","Sin Sitio"))}</span>
                  <span><i class="bi bi-diagram-3"></i>${esc(user.projects || tx("no_project","Sin proyecto"))}</span>
                  <span><i class="bi bi-person-workspace"></i>${esc(user.jefaturas || tx("no_supervisor","Sin Jefatura"))}</span>
                  <span><i class="bi bi-key"></i>${esc(user.credential_status || "pending_activation")}</span>
                </div>
              </div>
              <div class="admin-user-result-card__actions">
                <a class="btn btn-outline-custom" href="api/usuarios/perfil-completo.php?id_users=${encodeURIComponent(user.id_users)}"><i class="bi bi-pencil-square"></i><span>${esc(tx("edit","Editar"))}</span></a>
                <button class="btn btn-outline-custom" type="button" data-account-state data-user-id="${esc(user.id_users)}" data-next-state="${active ? 0 : 1}"><i class="bi ${active ? "bi-person-x" : "bi-person-check"}"></i><span>${active ? esc(tx("deactivate","Desactivar")) : esc(tx("activate","Activar"))}</span></button>
                <button class="btn btn-outline-custom" type="button" data-send-access data-user-id="${esc(user.id_users)}"><i class="bi bi-envelope-check"></i><span>${esc(tx("send_access","Enviar acceso"))}</span></button>
                <button class="btn btn-outline-danger-custom" type="button" data-delete-access data-user-id="${esc(user.id_users)}"><i class="bi bi-trash3"></i><span>${esc(tx("delete_access","Eliminar acceso"))}</span></button>
              </div>`;
            results.appendChild(card);
        });
    }

    function setUserMode(mode){
        const isSearch=mode==="search";
        const isCreate=mode==="create";

        if(searchPanel) searchPanel.hidden=!isSearch;
        if(createPanel) createPanel.hidden=!isCreate;

        userModeButtons.forEach(button=>{
            const active=button.dataset.adminUserMode===mode;
            button.classList.toggle("is-active",active);
            button.setAttribute(
                "aria-expanded",
                active ? "true" : "false"
            );
        });
    }

    async function loadFilters(){
        status.textContent=tx("loading_filters","Cargando filtros...");
        const url=new URL("api/usuarios/admin-directorio.php",window.location.href);
        url.searchParams.set("mode","filters");
        try {
            const response=await fetch(url,{headers:{"Accept":"application/json"},credentials:"same-origin"});
            const data=await response.json();
            if (!response.ok || !data.success) {
                throw new Error(
                    data.message
                    || tx(
                        "filter_error",
                        "No fue posible cargar los filtros de usuarios."
                    )
                );
            }
            filters=effectiveScopeCatalog(
                data.data?.filters
            );
            filtersLoaded=true;
            syncFilters();

            if(!filters.gbus?.length){
                status.textContent=tx(
                    "scope_catalog_empty",
                    "La empresa no tiene GBU, Sitios y Proyectos activos."
                );
                results.replaceChildren();
                return;
            }

            setIdleMessage();
        } catch (error) {
            if(scopeHasData(scopeSeed)){
                filters=effectiveScopeCatalog(scopeSeed);
                filtersLoaded=true;
                syncFilters();
                setIdleMessage();
                return;
            }

            status.textContent=
                error?.message
                || tx(
                    "filter_error",
                    "No fue posible cargar los filtros de usuarios."
                );
            results.replaceChildren();
        }
    }

    async function runSearch(){
        status.textContent=tx("searching","Buscando usuarios...");
        results.replaceChildren();
        const url=new URL("api/usuarios/admin-directorio.php",window.location.href);
        if (gbu.value) url.searchParams.set("gbu",gbu.value);
        if (site.value) url.searchParams.set("site",site.value);
        if (project.value) url.searchParams.set("id_project",project.value);
        if (search.value.trim()) url.searchParams.set("q",search.value.trim());
        try {
            const response=await fetch(url,{headers:{"Accept":"application/json"},credentials:"same-origin"});
            const data=await response.json();
            if (!response.ok || !data.success){ status.textContent=data.message || tx("search_error","No fue posible realizar la búsqueda."); return; }
            render(data.data?.users || []);
        } catch (_) { status.textContent=tx("search_error","No fue posible realizar la búsqueda."); }
    }

    gbu.addEventListener("change",()=>{site.value="";project.value="";syncFilters();setIdleMessage();});
    site.addEventListener("change",()=>{project.value="";syncFilters();setIdleMessage();});
    project.addEventListener("change",setIdleMessage);
    searchBtn.addEventListener("click",runSearch);
    search.addEventListener("keydown",event=>{if(event.key==="Enter"){event.preventDefault();runSearch();}});
    resetBtn.addEventListener("click",()=>{gbu.value="";site.value="";project.value="";search.value="";syncFilters();setIdleMessage();});

    function option(select,value,label,dataset={}){
        const o=document.createElement("option");
        o.value=value;
        o.textContent=label;
        Object.entries(dataset).forEach(([key,val])=>{
            o.dataset[key]=String(val);
        });
        select.appendChild(o);
    }

    function selectedRoleLevel(){
        if (!createForm) return null;
        const role=createForm.querySelector("[data-create-role]");
        const selected=role?.selectedOptions?.[0];
        if (!selected || !selected.value) return null;
        const level=Number(selected.dataset.level || 0);
        return level || null;
    }

    function createMode(){
        return createForm?.querySelector(
            'input[name="record_type"]:checked'
        )?.value || "worker";
    }

    function jefaturaRequired(){
        return true;
    }

    function gerenciaRequired(){
        return false;
    }

    function setStepState(name,state){
        const card=createForm?.querySelector(
            `[data-create-step="${name}"]`
        );
        if (!card) return;

        card.classList.toggle("is-locked",state==="locked");
        card.classList.toggle("is-current",state==="current");
        card.classList.toggle("is-complete",state==="complete");
        card.classList.toggle("is-optional",state==="optional");
    }

    function syncCreateScope(){
        if (!createForm) return;

        const g=createForm.querySelector("[data-create-gbu]");
        const s=createForm.querySelector("[data-create-site]");
        const p=createForm.querySelector("[data-create-project]");

        const cg=g.value;
        const cs=s.value;
        const cp=p.value;

        g.replaceChildren();
        option(g,"",tx("select","—"));
        creationOptions.filters.gbus.forEach(
            value=>option(g,value,value)
        );
        if ([...g.options].some(o=>o.value===cg)) g.value=cg;

        const sites=creationOptions.filters.sites.filter(
            row=>!g.value || norm(row.gbu)===norm(g.value)
        );
        s.replaceChildren();
        option(s,"",tx("select","—"));
        sites.forEach(row=>option(s,row.site,row.site));
        if ([...s.options].some(o=>o.value===cs)) s.value=cs;

        const projects=creationOptions.filters.projects.filter(row=>{
            if (
                g.value
                && norm(row.gbu)!==norm(g.value)
            ) return false;
            if (
                s.value
                && norm(row.site)!==norm(s.value)
            ) return false;
            return true;
        });
        p.replaceChildren();
        option(p,"",tx("select","—"));
        projects.forEach(row=>option(
            p,
            row.id_project,
            [row.gbu,row.site,row.name].filter(Boolean).join(" · ")
        ));
        if ([...p.options].some(o=>o.value===cp)) p.value=cp;

        s.disabled=!g.value;
        p.disabled=!g.value || !s.value;

        setStepState(
            "gbu",
            g.value ? "complete" : "current"
        );
        setStepState(
            "site",
            !g.value
                ? "locked"
                : (s.value ? "complete" : "current")
        );
        setStepState(
            "project",
            !g.value || !s.value
                ? "locked"
                : (p.value ? "complete" : "current")
        );
    }

    function syncCreateHierarchy(){
        if (!createForm) return;

        const j=createForm.querySelector("[data-create-jefatura]");
        const g=createForm.querySelector("[data-create-gerencia]");
        const jHelp=createForm.querySelector(
            '[data-create-hierarchy-help="jefatura"]'
        );
        const gHelp=createForm.querySelector(
            '[data-create-hierarchy-help="gerencia"]'
        );

        const requireJ=jefaturaRequired();
        const requireG=gerenciaRequired();

        if (j) {
            j.required=requireJ;
            j.disabled=false;
        }

        if (g) {
            const allowGerencia=createMode()==="user";
            g.required=requireG;
            g.disabled=!allowGerencia;

            if (!allowGerencia) {
                g.value="";
            }
        }

        if (jHelp) {
            jHelp.textContent=requireJ
                ? tx("step_jefatura_required","La Jefatura es obligatoria para crear la persona.")
                : tx("step_jefatura_optional","No requerida.");
        }

        if (gHelp) {
            gHelp.textContent=requireG
                ? tx("step_gerencia_required","Gerencia obligatoria.")
                : tx("step_gerencia_optional","Gerencia opcional.");
        }
    }

    function renderCreateJefaturas(query=""){
        if (!createForm) return;

        const select=createForm.querySelector("[data-create-jefatura]");
        if (!select) return;

        const current=select.value;
        const q=norm(query);

        select.replaceChildren();
        option(select,"",tx("select","—"));

        creationOptions.jefaturas
            .filter(row=>{
                if(!q) return true;
                const label=[
                    row.name,
                    row.lastname,
                    row.id_users
                ].filter(Boolean).join(" ");
                return norm(label).includes(q);
            })
            .forEach(row=>option(
                select,
                row.id_users,
                `${row.name} ${row.lastname} · ${row.id_users}`
            ));

        if(
            current
            && [...select.options].some(
                o=>String(o.value)===String(current)
            )
        ){
            select.value=current;
        }
    }

    function fillCreateStatic(){
        if (!createForm) return;

        const jefatura=createForm.querySelector("[data-create-jefatura]");
        const managers=createForm.querySelector("[data-create-gerencia]");
        const positions=createForm.querySelector("[data-create-position]");
        const roles=createForm.querySelector("[data-create-role]");
        const contractors=createForm.querySelector("[data-create-contractor]");

        [managers,roles,contractors].forEach(select=>{
            if (!select) return;
            select.replaceChildren();
            option(select,"",tx("select","—"));
        });

        renderCreateJefaturas(
            createForm.querySelector(
                "[data-create-jefatura-search]"
            )?.value || ""
        );

        if(positions?.tagName==="SELECT"){
            positions.replaceChildren();
            option(positions,"",tx("select","—"));

            creationOptions.positions.forEach(value=>option(
                positions,
                value,
                value
            ));
        }

        creationOptions.gerencias.forEach(row=>option(
            managers,
            row.id_users,
            `${row.name} ${row.lastname} · ${row.id_users}`
        ));

        creationOptions.roles.forEach(row=>option(
            roles,
            row.id_role_group,
            row.label,
            {level:row.access_level}
        ));

        creationOptions.contractors.forEach(row=>option(
            contractors,
            row.id_contractor_company,
            row.business_name
        ));
    }

    function reportCreationOrgCatalogs(){
        if (!status) return;

        const isUser=createMode()==="user";
        const missing=[];

        if(!creationOptions.jefaturas?.length){
            missing.push(
                tx(
                    "missing_jefaturas",
                    "Jefaturas"
                )
            );
        }

        if(isUser && !creationOptions.roles?.length){
            missing.push(
                tx(
                    "missing_roles",
                    "Grupos de perfil"
                )
            );
        }

        if(isUser && !creationOptions.gerencias?.length){
            missing.push(
                tx(
                    "missing_gerencias",
                    "Gerencias"
                )
            );
        }

        if(missing.length){
            status.textContent=
                tx(
                    "org_catalog_missing",
                    "No fue posible cargar: "
                )
                + missing.join(", ");
        }
    }

    function applyCreationDefaults(){
        if (
            !createForm
            || creationDefaultsApplied
        ) return;

        const defaults=creationOptions.defaults || {};
        const g=createForm.querySelector("[data-create-gbu]");
        const s=createForm.querySelector("[data-create-site]");
        const p=createForm.querySelector("[data-create-project]");
        const j=createForm.querySelector("[data-create-jefatura]");

        if(
            defaults.gbu
            && [...g.options].some(
                option=>norm(option.value)===norm(defaults.gbu)
            )
        ){
            const match=[...g.options].find(
                option=>norm(option.value)===norm(defaults.gbu)
            );
            g.value=match.value;
            syncCreateScope();
        }

        if(
            defaults.site
            && [...s.options].some(
                option=>norm(option.value)===norm(defaults.site)
            )
        ){
            const match=[...s.options].find(
                option=>norm(option.value)===norm(defaults.site)
            );
            s.value=match.value;
            syncCreateScope();
        }

        if(defaults.id_project){
            const projectOption=[...p.options].find(
                option=>String(option.value)
                    ===String(defaults.id_project)
            );
            if(projectOption){
                p.value=projectOption.value;
            }
        }

        // Para Jefatura se precarga al actor, pero puede escoger otra
        // Jefatura activa de la misma empresa.
        if(j){
            const preferred=String(
                creationOptions.default_jefatura || ""
            );
            const match=[...j.options].find(
                option=>String(option.value)===preferred
            );

            if(match){
                j.value=match.value;
            }else if(
                creationOptions.jefaturas.length===1
            ){
                j.value=String(
                    creationOptions.jefaturas[0].id_users || ""
                );
            }
        }

        creationDefaultsApplied=true;
        syncCreateScope();
        syncCreateHierarchy();
    }

    function primeCreationScope(){
        if (!createForm) return;

        creationOptions.filters=effectiveScopeCatalog(
            creationOptions.filters
        );

        if (!scopeHasData(creationOptions.filters)) {
            creationOptions.filters=effectiveScopeCatalog(scopeSeed);
        }

        // Scope fields become operational immediately for both modes:
        // worker-only and worker + SCT user.
        syncCreateScope();
    }

    async function loadCreationOptions(){
        const response=await fetch(
            "api/usuarios/admin-nuevo-opciones.php",
            {
                headers:{"Accept":"application/json"},
                credentials:"same-origin"
            }
        );

        const data=await response.json();

        if (!response.ok || !data.success) {
            throw new Error(
                data.message
                || tx(
                    "create_options_error",
                    "No fue posible cargar las opciones auxiliares."
                )
            );
        }

        const incoming=data.data || {};

        // Nunca reemplazar un catálogo operativo por uno vacío.
        creationOptions={
            ...creationOptions,
            ...incoming,
            ...mergeOrgCatalog(incoming),
            filters:effectiveScopeCatalog(
                incoming.filters
            )
        };

        if (!scopeHasData(creationOptions.filters)) {
            creationOptions.filters=effectiveScopeCatalog(scopeSeed);
        }

        creationDefaultsApplied=false;

        fillCreateStatic();
        primeCreationScope();
        applyCreationDefaults();
        syncCreateHierarchy();
        reportCreationOrgCatalogs();

        if(
            !creationOptions.filters?.gbus?.length
            && status
        ){
            status.textContent=tx(
                "scope_catalog_empty",
                "La empresa no tiene GBU, Sitios y Proyectos activos."
            );
        }
    }

    function syncCreateMode(){
        if (!createForm) return;

        const isUser=createMode()==="user";

        createForm.querySelectorAll("[data-create-user-only]")
            .forEach(node=>node.classList.toggle("d-none",!isUser));

        const emailWrap=createForm.querySelector(
            "[data-create-email-wrap]"
        );
        const email=createForm.querySelector("[data-create-email]");

        emailWrap?.classList.toggle("d-none",!isUser);

        if (email) {
            email.required=isUser;
            email.disabled=!isUser;

            if (!isUser) {
                // Trabajador sin cuenta: este dato no existe.
                email.value="";
            }
        }

        const roleField=createForm.elements.id_role_group;
        if (roleField) {
            roleField.required=isUser;
            roleField.disabled=!isUser;
        }

        const positionField=createForm.elements.position;
        if (positionField) {
            positionField.required=false;
        }

        [
            "mutual_code",
            "years_experience_current_role",
            "emergency_contact_name",
            "emergency_contact_phone",
            "emergency_contact_relation"
        ].forEach(name=>{
            const field=createForm.elements[name];
            if (field) {
                field.required=false;
                field.disabled=!isUser;
            }
        });

        const role=createForm.querySelector("[data-create-role]");
        if (!isUser && role) {
            role.value="";
        }

        fillCreateStatic();
        primeCreationScope();
        applyCreationDefaults();
        syncCreateHierarchy();

        if(
            creationOptions.jefaturas?.length
            || creationOptions.roles?.length
            || creationOptions.gerencias?.length
        ){
            reportCreationOrgCatalogs();
        }
    }

    root.querySelector('[data-admin-user-mode="search"]')
        ?.addEventListener("click",async()=>{
            setUserMode("search");
            if(!filtersLoaded){
                await loadFilters();
            }
            searchPanel?.scrollIntoView({
                behavior:"smooth",
                block:"nearest"
            });
        });

    newButton?.addEventListener("click",async()=>{
        setUserMode("create");

        // GBU/Sitio/Proyecto deben estar disponibles de inmediato.
        creationOptions.filters=effectiveScopeCatalog(scopeSeed);
        creationOptions={
            ...creationOptions,
            ...mergeOrgCatalog({})
        };
        creationDefaultsApplied=false;

        fillCreateStatic();
        primeCreationScope();
        applyCreationDefaults();
        syncCreateMode();

        createPanel?.scrollIntoView({
            behavior:"smooth",
            block:"nearest"
        });

        try {
            await loadCreationOptions();
            syncCreateMode();
        } catch (error) {
            // El error de opciones auxiliares no debe inutilizar el
            // catálogo de alcance ya disponible en bienvenida.php.
            primeCreationScope();

            status.textContent=
                error?.message
                || tx(
                    "create_options_error",
                    "No fue posible cargar algunas opciones auxiliares."
                );
        }
    });

    closeCreate?.addEventListener("click",()=>setUserMode(""));
    createForm?.querySelector("[data-create-gbu]")
        ?.addEventListener("change",()=>{
            createForm.querySelector("[data-create-site]").value="";
            createForm.querySelector("[data-create-project]").value="";
            syncCreateScope();
        });

    createForm?.querySelector("[data-create-site]")
        ?.addEventListener("change",()=>{
            createForm.querySelector("[data-create-project]").value="";
            syncCreateScope();
        });

    createForm?.querySelector("[data-create-project]")
        ?.addEventListener("change",()=>{
            syncCreateScope();
        });

    createForm?.querySelector("[data-create-jefatura-search]")
        ?.addEventListener("input",event=>{
            const selected=createForm.querySelector(
                "[data-create-jefatura]"
            )?.value || "";

            renderCreateJefaturas(event.target.value || "");

            const select=createForm.querySelector(
                "[data-create-jefatura]"
            );
            if(
                selected
                && [...select.options].some(
                    option=>String(option.value)===String(selected)
                )
            ){
                select.value=selected;
            }

            syncCreateHierarchy();
        });

    createForm?.querySelector("[data-create-jefatura]")
        ?.addEventListener("change",syncCreateHierarchy);

    createForm?.querySelector("[data-create-role]")
        ?.addEventListener("change",()=>{
            const g=createForm.querySelector("[data-create-gerencia]");
            if (g) g.value="";
            syncCreateHierarchy();
        });

    createForm?.querySelectorAll('input[name="record_type"]')
        .forEach(r=>r.addEventListener("change",syncCreateMode));
    const contractorFlag=createForm?.querySelector("[data-create-contractor-flag]");
    contractorFlag?.addEventListener("change",()=>{const wrap=createForm.querySelector("[data-create-contractor-wrap]");const field=createForm.elements.id_contractor_company;const active=contractorFlag.value==="yes";wrap?.classList.toggle("d-none",!active);if(field)field.required=active;});

    function showCreateMessage(text,type="success"){
        const message=createForm?.querySelector(
            "[data-create-message]"
        );

        if (!message) return;

        message.textContent=String(text || "");
        message.className=
            "admin-create-person__message "
            +(type==="success" ? "is-success" : "is-error");
        message.hidden=false;
        message.setAttribute("role","status");
        message.setAttribute("aria-live","polite");

        message.scrollIntoView({
            behavior:"smooth",
            block:"nearest"
        });
    }

    createForm?.addEventListener("submit",async event=>{
        event.preventDefault();
        const message=createForm.querySelector("[data-create-message]");
        const submit=createForm.querySelector('button[type="submit"]');
        const payload=Object.fromEntries(new FormData(createForm).entries());
        payload.csrf_token=root.dataset.csrf || "";
        if(submit)submit.disabled=true;
        if(message){
            message.className=
                "admin-create-person__message d-none";
            message.textContent="";
            message.hidden=true;
        }
        try {
            const response=await fetch(
                "api/usuarios/admin-nuevo-guardar.php",
                {
                    method:"POST",
                    headers:{
                        "Content-Type":"application/json",
                        "Accept":"application/json"
                    },
                    credentials:"same-origin",
                    body:JSON.stringify(payload)
                }
            );

            const raw=await response.text();
            let data={};

            try {
                data=JSON.parse(raw || "{}");
            } catch (_) {
                data={
                    success:false,
                    message:tx(
                        "create_http_error",
                        "No fue posible completar la solicitud."
                    ) + ` (HTTP ${response.status})`
                };
            }

            if(response.ok&&data.success){
                const successMessage=
                    data.message
                    || (
                        createMode()==="user"
                            ? tx(
                                "created_user_confirm",
                                "Usuario creado correctamente."
                            )
                            : tx(
                                "created_worker_confirm",
                                "Trabajador creado correctamente."
                            )
                    );

                createForm.reset();
                creationDefaultsApplied=false;
                syncCreateMode();
                syncCreateScope();
                applyCreationDefaults();
                syncCreateHierarchy();

                showCreateMessage(
                    successMessage,
                    "success"
                );
            }else{
                showCreateMessage(
                    data.message
                    || tx(
                        "create_error",
                        "No fue posible crear el registro."
                    ),
                    "error"
                );
            }
        } catch (_) {
            showCreateMessage(
                tx(
                    "create_error",
                    "No fue posible crear el registro."
                ),
                "error"
            );
        } finally {
            if(submit)submit.disabled=false;
        }
    });

    results.addEventListener("click",async event=>{
        const stateButton=event.target.closest("[data-account-state]");
        const accessButton=event.target.closest("[data-send-access]");
        const deleteButton=event.target.closest("[data-delete-access]");
        if(stateButton){
            const response=await fetch("api/usuarios/admin-cuenta-estado.php",{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json"},credentials:"same-origin",body:JSON.stringify({id_users:stateButton.dataset.userId || "",state:Number(stateButton.dataset.nextState),csrf_token:root.dataset.csrf || ""})});
            const data=await response.json(); status.textContent=data.message || ""; if(response.ok&&data.success)runSearch(); return;
        }
        if(accessButton){
            const response=await fetch("api/usuarios/enviar-acceso.php",{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json"},credentials:"same-origin",body:JSON.stringify({id_users:accessButton.dataset.userId || "",csrf_token:root.dataset.csrf || ""})});
            const data=await response.json();
            status.textContent=data.message || "";
            return;
        }

        if(deleteButton){
            const userId=deleteButton.dataset.userId || "";
            if(!window.confirm(tx(
                "delete_confirm",
                "Esta acción elimina el acceso SCT y conserva la ficha del trabajador y la trazabilidad histórica. ¿Deseas continuar?"
            ))) return;

            deleteButton.disabled=true;

            try {
                const response=await fetch(
                    "api/usuarios/admin-cuenta-eliminar.php",
                    {
                        method:"POST",
                        headers:{
                            "Content-Type":"application/json",
                            "Accept":"application/json"
                        },
                        credentials:"same-origin",
                        body:JSON.stringify({
                            id_users:userId,
                            csrf_token:root.dataset.csrf || ""
                        })
                    }
                );
                const data=await response.json();
                status.textContent=data.message || "";

                if(response.ok && data.success){
                    runSearch();
                }
            } catch (_) {
                status.textContent=tx(
                    "delete_error",
                    "No fue posible eliminar el acceso SCT."
                );
            } finally {
                deleteButton.disabled=false;
            }
        }
    });

    // Sin carga de filtros ni usuarios hasta seleccionar una operación.
    setUserMode("");
});
