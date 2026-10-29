document.addEventListener("DOMContentLoaded", () => {
    const form=document.getElementById("adminFullProfileForm");
    if (!form) return;

    const flag=form.querySelector("[data-contractor-flag]");
    const wrap=form.querySelector("[data-contractor-wrap]");
    const contractor=form.elements.id_contractor_company;

    function syncContractor(){
        const active=flag?.value==="yes";
        wrap?.classList.toggle("d-none",!active);
        if (contractor) contractor.required=active;
        if (!active && contractor) contractor.value="";
    }
    flag?.addEventListener("change",syncContractor);
    syncContractor();

    form.addEventListener("submit", async event => {
        event.preventDefault();
        const button=form.querySelector('button[type="submit"]');
        const alertBox=document.getElementById("adminProfileAlert");
        if (button) button.disabled=true;

        const payload=Object.fromEntries(new FormData(form).entries());
        payload.id_users=form.dataset.user || "";
        payload.csrf_token=form.dataset.csrf || "";

        try {
            const response=await fetch("admin-perfil-guardar.php",{
                method:"POST",
                headers:{"Content-Type":"application/json","Accept":"application/json"},
                credentials:"same-origin",
                body:JSON.stringify(payload),
            });
            const data=await response.json();
            alertBox.className="alert " + (response.ok && data.success ? "alert-success" : "alert-danger");
            alertBox.textContent=data.message || "";
            alertBox.classList.remove("d-none");
            if (response.ok && data.success) {
                window.scrollTo({top:0,behavior:"smooth"});
            }
        } catch (_) {
            alertBox.className="alert alert-danger";
            alertBox.textContent="No fue posible guardar los cambios.";
            alertBox.classList.remove("d-none");
        } finally {
            if (button) button.disabled=false;
        }
    });
});
