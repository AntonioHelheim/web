document.addEventListener("DOMContentLoaded", () => {
    const root = document.querySelector(".container[data-csrf-token]");
    if (!root) return;

    const csrfToken = root.dataset.csrfToken || "";
    const i18nEl = document.getElementById("companiesI18n");
    let strings = {};
    try { strings = JSON.parse(i18nEl?.textContent || "{}"); } catch (_) { strings = {}; }

    const tr = (key, vars = {}) => {
        let value = strings[key] || key;
        Object.entries(vars).forEach(([k, v]) => {
            value = value.split(`{${k}}`).join(String(v));
        });
        return value;
    };

    const escapeHtml = value => String(value ?? "")
        .split("&").join("&amp;")
        .split("<").join("&lt;")
        .split(">").join("&gt;")
        .split('"').join("&quot;")
        .split("'").join("&#039;");

    const logoUrl = path => path
        ? `../../${String(path).replace(/^\/+/, "")}?v=${Date.now()}`
        : "";

    // El cambio de idioma (confirmación + guardado en perfil) lo maneja js/lang-switcher.js.

    const form = document.getElementById("companyForm");
    const mode = document.getElementById("companyFormMode");
    const idInput = document.getElementById("companyId");
    const rutInput = document.getElementById("companyRut");
    const nameInput = document.getElementById("companyName");
    const addressInput = document.getElementById("companyAddress");
    const emailInput = document.getElementById("companyEmail");
    const formTitle = document.getElementById("companyFormTitle");
    const submitBtn = document.getElementById("companySubmitBtn");
    const cancelEditBtn = document.getElementById("companyCancelEditBtn");
    const alertBox = document.getElementById("companiesActionAlert");
    const statusBox = document.getElementById("companiesStatus");
    const tableWrapper = document.getElementById("companiesTableWrapper");
    const tableBody = document.getElementById("companiesTableBody");
    const stateFilter = document.getElementById("companiesStateFilter");

    const logoEditor = document.getElementById("companyLogoEditor");
    const logoPlaceholder = document.getElementById("editCompanyLogoPlaceholder");
    const logoImage = document.getElementById("editCompanyLogo");
    const logoFile = document.getElementById("companyLogoFile");
    const logoUploadBtn = document.getElementById("companyLogoUploadBtn");
    const logoRemoveBtn = document.getElementById("companyLogoRemoveBtn");

    let companies = [];
    let currentEditingCompany = null;

    function showAlert(message, type = "info") {
        alertBox.textContent = message || "";
        alertBox.className = `alert mb-3 alert-${type}`;
        alertBox.classList.toggle("d-none", !message);
    }

    function setStatus(message, type = "info") {
        statusBox.textContent = message;
        statusBox.className = `alert mb-0 alert-${type}`;
        statusBox.classList.remove("d-none");
    }

    async function api(url, options = {}) {
        const response = await fetch(url, { credentials: "same-origin", ...options });
        let data;
        try {
            data = await response.json();
        } catch (_) {
            throw new Error(tr("companies_error_load"));
        }
        return { response, data };
    }

    async function postJson(url, payload) {
        return api(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ ...payload, csrf_token: csrfToken }),
        });
    }

    function setLogoPreview(path) {
        const hasLogo = Boolean(path);
        if (hasLogo) {
            logoImage.src = logoUrl(path);
        } else {
            logoImage.removeAttribute("src");
        }
        logoImage.classList.toggle("d-none", !hasLogo);
        logoPlaceholder.classList.toggle("d-none", hasLogo);
        logoRemoveBtn.disabled = !hasLogo;
    }

    function resetForm() {
        form.reset();
        mode.value = "create";
        idInput.value = "";
        rutInput.disabled = false;
        formTitle.textContent = tr("companies_form_new");
        submitBtn.textContent = tr("companies_save");
        cancelEditBtn.classList.add("d-none");
        logoEditor.classList.add("d-none");
        logoFile.value = "";
        setLogoPreview(null);
        currentEditingCompany = null;
    }

    function render() {
        const filter = stateFilter.value;
        const visible = companies.filter(company => filter === "all" || String(company.state) === filter);
        tableBody.innerHTML = "";

        if (!visible.length) {
            tableWrapper.classList.add("d-none");
            setStatus(tr("companies_empty"), "info");
            return;
        }

        visible.forEach(company => {
            const active = Number(company.state) === 1;
            const badge = active
                ? `<span class="badge text-bg-success">${escapeHtml(tr("common_active"))}</span>`
                : `<span class="badge text-bg-secondary">${escapeHtml(tr("common_inactive"))}</span>`;
            const stateAction = active
                ? `<button class="btn btn-outline-danger btn-sm" data-action="deactivate" data-id="${company.id_company}" title="${escapeHtml(tr("companies_action_deactivate"))}"><i class="bi bi-building-x"></i></button>`
                : `<button class="btn btn-outline-success btn-sm" data-action="reactivate" data-id="${company.id_company}" title="${escapeHtml(tr("companies_action_reactivate"))}"><i class="bi bi-building-check"></i></button>`;
            const logo = company.logo_path
                ? `<img src="${escapeHtml(logoUrl(company.logo_path))}" class="company-logo table-logo" alt="">`
                : `<span class="company-logo table-logo placeholder"><i class="bi bi-building"></i></span>`;

            const row = document.createElement("tr");
            row.innerHTML = `<td>${logo}</td><td>${company.id_company}</td><td>${escapeHtml(company.rut)}</td><td>${escapeHtml(company.razon_social)}</td><td>${escapeHtml(company.address)}</td><td>${escapeHtml(company.email)}</td><td>${badge}</td><td><div class="company-actions"><button class="btn btn-outline-secondary btn-sm" data-action="edit" data-id="${company.id_company}" title="${escapeHtml(tr("companies_action_edit"))}"><i class="bi bi-pencil"></i></button>${stateAction}</div></td>`;
            tableBody.appendChild(row);
        });

        statusBox.classList.add("d-none");
        tableWrapper.classList.remove("d-none");
    }

    async function loadCompanies() {
        try {
            setStatus(tr("companies_loading"), "info");
            tableWrapper.classList.add("d-none");
            const { response, data } = await api("listar.php");
            if (!response.ok || !data.success) {
                throw new Error(data.message || tr("companies_error_load"));
            }
            companies = Array.isArray(data.data) ? data.data : [];
            if (currentEditingCompany) {
                const refreshed = companies.find(item => Number(item.id_company) === Number(currentEditingCompany.id_company));
                if (refreshed) currentEditingCompany = refreshed;
            }
            render();
        } catch (error) {
            setStatus(error.message || tr("companies_error_load"), "danger");
        }
    }

    function editCompany(id) {
        const company = companies.find(item => Number(item.id_company) === Number(id));
        if (!company) return;

        currentEditingCompany = company;
        mode.value = "edit";
        idInput.value = String(company.id_company);
        rutInput.value = company.rut || "";
        rutInput.disabled = true;
        nameInput.value = company.razon_social || "";
        addressInput.value = company.address || "";
        emailInput.value = company.email || "";
        formTitle.textContent = tr("companies_form_edit");
        submitBtn.textContent = tr("companies_update");
        cancelEditBtn.classList.remove("d-none");
        logoEditor.classList.remove("d-none");
        logoFile.value = "";
        setLogoPreview(company.logo_path || null);
        form.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    async function submitCompany(event) {
        event.preventDefault();
        showAlert("");
        submitBtn.disabled = true;

        const isEdit = mode.value === "edit";
        const payload = {
            razon_social: nameInput.value.trim(),
            address: addressInput.value.trim(),
            email: emailInput.value.trim(),
        };
        if (isEdit) payload.id_company = Number(idInput.value);
        else payload.rut = rutInput.value.trim();

        try {
            const { response, data } = await postJson(isEdit ? "editar.php" : "crear.php", payload);
            if (!response.ok || !data.success) {
                throw new Error(data.message || tr("companies_error_save"));
            }
            showAlert(data.message || tr("companies_saved"), "success");
            resetForm();
            await loadCompanies();
        } catch (error) {
            showAlert(error.message || tr("companies_error_save"), "danger");
        } finally {
            submitBtn.disabled = false;
        }
    }

    async function uploadLogo() {
        if (!currentEditingCompany || !logoFile.files?.[0]) {
            showAlert(tr("companies_logo_invalid"), "warning");
            return;
        }

        const formData = new FormData();
        formData.append("id_company", String(currentEditingCompany.id_company));
        formData.append("csrf_token", csrfToken);
        formData.append("logo", logoFile.files[0]);
        logoUploadBtn.disabled = true;

        try {
            const { response, data } = await api("subir-logo.php", { method: "POST", body: formData });
            if (!response.ok || !data.success) {
                throw new Error(data.message || tr("companies_logo_invalid"));
            }
            const path = data.data?.logo_path || null;
            currentEditingCompany.logo_path = path;
            const matching = companies.find(item => Number(item.id_company) === Number(currentEditingCompany.id_company));
            if (matching) matching.logo_path = path;
            setLogoPreview(path);
            logoFile.value = "";
            render();
            showAlert(data.message || tr("companies_logo_updated"), "success");
        } catch (error) {
            showAlert(error.message || tr("companies_logo_invalid"), "danger");
        } finally {
            logoUploadBtn.disabled = false;
        }
    }

    async function removeLogo() {
        if (!currentEditingCompany?.logo_path) return;
        logoRemoveBtn.disabled = true;

        try {
            const { response, data } = await postJson("eliminar-logo.php", { id_company: Number(currentEditingCompany.id_company) });
            if (!response.ok || !data.success) {
                throw new Error(data.message || tr("companies_logo_invalid"));
            }
            currentEditingCompany.logo_path = null;
            const matching = companies.find(item => Number(item.id_company) === Number(currentEditingCompany.id_company));
            if (matching) matching.logo_path = null;
            setLogoPreview(null);
            render();
            showAlert(data.message || tr("companies_logo_removed"), "success");
        } catch (error) {
            showAlert(error.message || tr("companies_logo_invalid"), "danger");
        } finally {
            logoRemoveBtn.disabled = !currentEditingCompany?.logo_path;
        }
    }

    async function changeState(id, action) {
        const company = companies.find(item => Number(item.id_company) === Number(id));
        if (!company) return;
        const question = action === "deactivate"
            ? tr("companies_confirm_deactivate", { company: company.razon_social })
            : tr("companies_confirm_reactivate", { company: company.razon_social });
        if (!window.confirm(question)) return;

        try {
            const { response, data } = await postJson(action === "deactivate" ? "baja.php" : "reactivar.php", { id_company: Number(id) });
            if (!response.ok || !data.success) {
                throw new Error(data.message || tr("companies_error_state"));
            }
            showAlert(data.message || tr("companies_state_updated"), "success");
            await loadCompanies();
        } catch (error) {
            showAlert(error.message || tr("companies_error_state"), "danger");
        }
    }

    tableBody.addEventListener("click", event => {
        const button = event.target.closest("button[data-action]");
        if (!button) return;
        if (button.dataset.action === "edit") editCompany(button.dataset.id);
        if (button.dataset.action === "deactivate" || button.dataset.action === "reactivate") {
            changeState(button.dataset.id, button.dataset.action);
        }
    });

    form.addEventListener("submit", submitCompany);
    cancelEditBtn.addEventListener("click", resetForm);
    stateFilter.addEventListener("change", render);
    logoUploadBtn.addEventListener("click", uploadLogo);
    logoRemoveBtn.addEventListener("click", removeLogo);
    loadCompanies();
});
