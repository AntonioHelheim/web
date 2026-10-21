document.addEventListener("DOMContentLoaded",()=>{
    const modal=document.querySelector("[data-health-modal]");
    const form=document.getElementById("workerHealthForm");
    if(!modal || !form) return;

    const step=form.querySelector('[data-health-step="0"]');
    const submit=modal.querySelector("[data-health-submit]");
    const alertBox=modal.querySelector("[data-health-alert]");
    const i18n=window.SCT_SELF_ORG_I18N || {};
    const csrf=modal.dataset.csrf || "";

    if(!step || !submit) return;

    const card=document.createElement("div");
    card.className="health-subcard self-org-profile-card";
    card.innerHTML=`
      <h4>${escapeHtml(i18n.title || "Ubicación y asignación")}</h4>
      <p class="self-org-profile-card__intro">${escapeHtml(i18n.intro || "")}</p>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label" for="selfPhysicalLocation">${escapeHtml(i18n.location || "Ubicación física")}</label>
          <select class="form-select" id="selfPhysicalLocation" name="self_physical_location">
            <option value="">${escapeHtml(i18n.optional || "Opcional")}</option>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label" for="selfGbu">${escapeHtml(i18n.gbu || "GBU")}</label>
          <select class="form-select" id="selfGbu" name="self_gbu" required>
            <option value=""></option>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label" for="selfSite">${escapeHtml(i18n.site || "Sitio")}</label>
          <select class="form-select" id="selfSite" name="self_site" required disabled>
            <option value=""></option>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label" for="selfProject">${escapeHtml(i18n.project || "Proyecto")}</label>
          <select class="form-select" id="selfProject" name="self_project" disabled>
            <option value="">${escapeHtml(i18n.optional || "Opcional")}</option>
          </select>
        </div>
      </div>
    `;

    step.appendChild(card);

    const location=card.querySelector("#selfPhysicalLocation");
    const gbu=card.querySelector("#selfGbu");
    const site=card.querySelector("#selfSite");
    const project=card.querySelector("#selfProject");

    let dataset=null;
    let loaded=false;
    let bypass=false;

    function escapeHtml(value){
        return String(value ?? "")
            .replaceAll("&","&amp;")
            .replaceAll("<","&lt;")
            .replaceAll(">","&gt;")
            .replaceAll('"',"&quot;")
            .replaceAll("'","&#039;");
    }

    function option(select,value,label){
        const o=document.createElement("option");
        o.value=value;
        o.textContent=label;
        select.appendChild(o);
    }

    function showError(message){
        if(!alertBox) return;
        alertBox.textContent=message || i18n.saveError || "";
        alertBox.className="alert alert-danger mt-3";
    }

    async function post(url,payload){
        const response=await fetch(url,{
            method:"POST",
            credentials:"same-origin",
            headers:{
                "Content-Type":"application/json",
                "Accept":"application/json"
            },
            body:JSON.stringify(payload)
        });
        const json=await response.json();

        if(!response.ok || !json.success){
            throw new Error(json.message || i18n.saveError || "");
        }

        return json;
    }

    function projects(){
        return dataset?.projects || [];
    }

    function syncSites(reset=true){
        const selected=reset ? "" : site.value;
        site.replaceChildren();
        option(site,"","");

        const seen=new Set();
        projects()
            .filter(row=>row.gbu_code===gbu.value)
            .forEach(row=>{
                if(!seen.has(row.site_name)){
                    seen.add(row.site_name);
                    option(site,row.site_name,row.site_name);
                }
            });

        site.disabled=!gbu.value;

        if(
            selected
            && [...site.options].some(o=>o.value===selected)
        ){
            site.value=selected;
        }

        syncProjects(reset);
    }

    function syncProjects(reset=true){
        const selected=reset ? "" : project.value;

        project.replaceChildren();
        option(
            project,
            "",
            i18n.optional || "Opcional"
        );

        projects()
            .filter(row=>
                row.gbu_code===gbu.value
                && row.site_name===site.value
            )
            .forEach(row=>{
                option(project,row.id_project,row.name);
            });

        project.disabled=!gbu.value || !site.value;

        if(
            selected
            && [...project.options].some(
                o=>o.value===String(selected)
            )
        ){
            project.value=String(selected);
        }
    }

    function render(data){
        dataset=data;

        location.replaceChildren();
        option(
            location,
            "",
            i18n.optional || "Opcional"
        );

        const matrix=data.matrix || {};
        option(
            location,
            "matrix",
            `${i18n.matrix || "Matriz"}${
                matrix.address ? " · " + matrix.address : ""
            }`
        );

        (data.centers || []).forEach(center=>{
            option(
                location,
                `center:${center.id_company_center}`,
                `${i18n.center || "Sede"} · ${center.name}${
                    center.address ? " · " + center.address : ""
                }`
            );
        });

        const gbus=[...new Set(
            projects()
                .map(row=>row.gbu_code)
                .filter(Boolean)
        )];

        gbu.replaceChildren();
        option(gbu,"","");
        gbus.forEach(value=>option(gbu,value,value));

        const current=data.current || {};

        if(current.physical_location_type==="matrix"){
            location.value="matrix";
        }else if(
            current.physical_location_type==="center"
            && current.id_company_center
        ){
            location.value=`center:${current.id_company_center}`;
        }

        if(current.gbu_code){
            gbu.value=current.gbu_code;
        }

        syncSites(false);

        if(current.site_name){
            site.value=current.site_name;
        }

        syncProjects(false);

        if(current.confirmed_project_id){
            project.value=String(current.confirmed_project_id);
        }
    }

    async function load(){
        try{
            const json=await post(
                "api/usuarios/self-org-obtener.php",
                {csrf_token:csrf}
            );
            render(json.data || {});
            loaded=true;
        }catch(error){
            showError(error.message);
        }
    }

    function valid(){
        let ok=true;

        [gbu,site].forEach(field=>{
            field.classList.remove("is-invalid");
            if(!field.value){
                field.classList.add("is-invalid");
                ok=false;
            }
        });

        if(!ok){
            showError(
                i18n.required
                || "Selecciona GBU y Sitio."
            );
        }

        return ok;
    }

    gbu.addEventListener("change",()=>{
        syncSites(true);
    });

    site.addEventListener("change",()=>{
        syncProjects(true);
    });

    modal.addEventListener("show.bs.modal",()=>{
        if(!loaded) load();
    });

    submit.addEventListener("click",async event=>{
        if(bypass) return;

        if(!valid()){
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        submit.disabled=true;

        try{
            await post(
                "api/usuarios/self-org-guardar.php",
                {
                    csrf_token:csrf,
                    physical_location:location.value,
                    gbu_code:gbu.value,
                    site_name:site.value,
                    id_project:project.value || null
                }
            );

            bypass=true;
            submit.disabled=false;
            submit.click();
            bypass=false;
        }catch(error){
            submit.disabled=false;
            showError(error.message);
        }
    },true);
});
