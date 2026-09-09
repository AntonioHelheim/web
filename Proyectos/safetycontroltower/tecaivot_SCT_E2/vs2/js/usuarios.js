document.addEventListener("DOMContentLoaded", () => {
    const root = document.querySelector(".container[data-current-user]");
    if (!root) return;

    const csrfToken = root.dataset.csrfToken || "";
    const actorLevel = Number(root.dataset.actorLevel || 0);
    const currentLang = root.dataset.currentLang || "es";
    const i18nEl = document.getElementById("usersI18n");
    let strings = {};
    try { strings = JSON.parse(i18nEl?.textContent || "{}"); } catch (_) { strings = {}; }
    const tr = (key, vars = {}) => {
        let value = strings[key] || key;
        Object.entries(vars).forEach(([k, v]) => { value = value.replaceAll(`{${k}}`, String(v)); });
        return value;
    };

    const localeMap = { es: "es-CL", en: "en-US", pt: "pt-BR", fr: "fr-FR", zh: "zh-CN" };
    const locale = localeMap[currentLang] || "es-CL";
    const escapeHtml = value => String(value ?? "").replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;");
    const photoUrl = path => path ? `../../${String(path).replace(/^\/+/, "")}?v=${Date.now()}` : "";
    const roleLabel = role => role ? (strings[`role_${role}`] || role.replaceAll("_", " ")) : tr("role_none");

    const createBtn = document.getElementById("usersCreateBtn");
    const searchInput = document.getElementById("usersSearch");
    const companyFilter = document.getElementById("usersCompanyFilter");
    const companyFilterWrap = document.getElementById("usersCompanyFilterWrap");
    const stateFilter = document.getElementById("usersStateFilter");
    const roleFilter = document.getElementById("usersRoleFilter");
    const statusBox = document.getElementById("usersStatus");
    const tableWrapper = document.getElementById("usersTableWrapper");
    const tableBody = document.getElementById("usersTableBody");
    const actionAlert = document.getElementById("usersActionAlert");

    const viewModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("userViewModal"));
    const formModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("userFormModal"));
    const stateModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("userStateModal"));

    const form = document.getElementById("userForm");
    const formMode = document.getElementById("userFormMode");
    const formTarget = document.getElementById("userFormTarget");
    const formTitle = document.getElementById("userFormModalLabel");
    const formAlert = document.getElementById("userFormAlert");
    const formSubmit = document.getElementById("userFormSubmit");
    const userEmail = document.getElementById("userEmail");
    const userRole = document.getElementById("userRole");
    const userCompany = document.getElementById("userCompany");
    const userCompanyGroup = document.getElementById("userCompanyGroup");
    const userName = document.getElementById("userName");
    const userLastname = document.getElementById("userLastname");
    const userRut = document.getElementById("userRut");
    const userLanguage = document.getElementById("userLanguage");
    const stateConfirmBtn = document.getElementById("userStateConfirmBtn");
    const stateModalText = document.getElementById("userStateModalText");

    const photoEditor = document.getElementById("userPhotoEditor");
    const photoFile = document.getElementById("userPhotoFile");
    const photoUploadBtn = document.getElementById("userPhotoUploadBtn");
    const photoRemoveBtn = document.getElementById("userPhotoRemoveBtn");
    const editPhoto = document.getElementById("editUserPhoto");
    const editPhotoPlaceholder = document.getElementById("editUserPhotoPlaceholder");

    let context = {};
    let companies = [];
    let roleOptions = [];
    let pendingStateChange = null;
    let currentEditingUser = null;
    let searchTimer = null;

    function showActionAlert(message, type = "info") {
        actionAlert.textContent = message || "";
        actionAlert.className = `alert mt-3 mb-0 alert-${type}`;
        actionAlert.classList.toggle("d-none", !message);
    }
    function showFormAlert(message, type = "danger") {
        formAlert.textContent = message || "";
        formAlert.className = `alert mb-3 alert-${type}`;
        formAlert.classList.toggle("d-none", !message);
    }
    function setStatus(message, type = "info") {
        statusBox.textContent = message;
        statusBox.className = `alert mt-4 mb-0 alert-${type}`;
        statusBox.classList.remove("d-none");
    }
    async function api(url, options = {}) {
        const response = await fetch(url, { credentials: "same-origin", ...options });
        let data;
        try { data = await response.json(); } catch (_) { throw new Error(tr("users_error_load")); }
        return { response, data };
    }
    async function postJson(url, payload) {
        return api(url, { method: "POST", headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" }, body: JSON.stringify({ ...payload, csrf_token: csrfToken }) });
    }
    function formatDate(value) {
        if (!value || value === "1970-01-01 00:00:00") return tr("common_no_access");
        const date = new Date(String(value).replace(" ", "T"));
        return Number.isNaN(date.getTime()) ? String(value) : new Intl.DateTimeFormat(locale, { dateStyle: "short", timeStyle: "short" }).format(date);
    }
    function setEditable(element, editable) { if (element) element.disabled = !editable; }
    function setPhotoPreview(path) {
        const hasPhoto = Boolean(path);
        editPhoto.classList.toggle("d-none", !hasPhoto);
        editPhotoPlaceholder.classList.toggle("d-none", hasPhoto);
        if (hasPhoto) editPhoto.src = photoUrl(path);
        photoRemoveBtn.disabled = !hasPhoto;
    }

    function populateCompanies() {
        companyFilter.innerHTML = `<option value="all">${escapeHtml(tr("common_all_f"))}</option>`;
        userCompany.innerHTML = `<option value="">${escapeHtml(tr("users_select_company"))}</option>`;
        companies.forEach(company => {
            const label = company.razon_social || `#${company.id_company}`;
            companyFilter.insertAdjacentHTML("beforeend", `<option value="${company.id_company}">${escapeHtml(label)}</option>`);
            userCompany.insertAdjacentHTML("beforeend", `<option value="${company.id_company}">${escapeHtml(label)}</option>`);
        });
        if (actorLevel !== 1) {
            companyFilterWrap.classList.add("d-none");
            if (companies[0]) userCompany.value = String(companies[0].id_company);
        }
    }
    function populateRoleFilter() {
        roleFilter.innerHTML = `<option value="all">${escapeHtml(tr("common_all"))}</option>`;
        const levels = new Map();
        roleOptions.forEach(role => { if (role.access_level && !levels.has(role.access_level)) levels.set(role.access_level, roleLabel(role.name)); });
        [...levels.entries()].sort((a,b) => a[0]-b[0]).forEach(([level,label]) => roleFilter.insertAdjacentHTML("beforeend", `<option value="${level}">${escapeHtml(label)}</option>`));
    }
    function populateRoleSelect(roles, selectedId, currentRole = null) {
        userRole.innerHTML = `<option value="">${escapeHtml(tr("users_select_role"))}</option>`;
        roles.forEach(role => userRole.insertAdjacentHTML("beforeend", `<option value="${role.id_role_group}">${escapeHtml(roleLabel(role.name))}</option>`));
        if (selectedId && !roles.some(r => Number(r.id_role_group) === Number(selectedId)) && currentRole) {
            userRole.insertAdjacentHTML("beforeend", `<option value="${Number(selectedId)}">${escapeHtml(roleLabel(currentRole))}</option>`);
        }
        if (selectedId) userRole.value = String(selectedId);
    }
    async function loadRoles(companyId) {
        const params = new URLSearchParams();
        if (companyId) params.set("id_company", companyId);
        const { response, data } = await api(`roles.php${params.toString() ? `?${params}` : ""}`);
        if (!response.ok || !data.success) throw new Error(data.message || tr("users_error_load"));
        return data.data || {};
    }
    async function initializeContext() {
        const payload = await loadRoles();
        context = payload.context || {};
        companies = payload.companies || [];
        roleOptions = payload.role_options || [];
        populateCompanies();
        populateRoleFilter();
        createBtn.classList.toggle("d-none", !context.can_create_user);
    }

    function renderUsers(users) {
        tableBody.innerHTML = "";
        if (!users.length) {
            tableWrapper.classList.add("d-none");
            setStatus(tr("users_empty"), "info");
            return;
        }
        users.forEach(user => {
            const credential = user.credential_status || "pending_activation";
            const stateBadge = Number(user.state) === 1 ? `<span class="badge text-bg-success">${escapeHtml(tr("common_active"))}</span>` : `<span class="badge text-bg-secondary">${escapeHtml(tr("common_inactive"))}</span>`;
            const accessBadge = credential === "active" ? `<span class="badge text-bg-primary">${escapeHtml(tr("users_status_configured"))}</span>` : credential === "reset_required" ? `<span class="badge text-bg-danger">${escapeHtml(tr("users_status_reset"))}</span>` : `<span class="badge text-bg-warning">${escapeHtml(tr("users_status_pending"))}</span>`;
            const actions = [`<button class="btn btn-outline-secondary btn-sm" data-action="view" data-user="${escapeHtml(user.id_users)}" title="${escapeHtml(tr("common_view"))}"><i class="bi bi-eye"></i></button>`];
            if (user.can_edit) actions.push(`<button class="btn btn-outline-secondary btn-sm" data-action="edit" data-user="${escapeHtml(user.id_users)}" title="${escapeHtml(tr("common_edit"))}"><i class="bi bi-pencil"></i></button>`);
            if (user.can_send_access) {
                const title = credential === "pending_activation" ? tr("users_action_activation") : tr("users_action_reset");
                actions.push(`<button class="btn btn-outline-secondary btn-sm" data-action="access" data-user="${escapeHtml(user.id_users)}" data-credential-status="${escapeHtml(credential)}" title="${escapeHtml(title)}"><i class="bi ${credential === "pending_activation" ? "bi-envelope-check" : "bi-key"}"></i></button>`);
            }
            if (user.can_change_state) {
                const next = Number(user.state) === 1 ? 0 : 1;
                const title = next ? tr("users_action_activate") : tr("users_action_deactivate");
                actions.push(`<button class="btn btn-outline-secondary btn-sm" data-action="state" data-user="${escapeHtml(user.id_users)}" data-state="${next}" title="${escapeHtml(title)}"><i class="bi ${next ? "bi-person-check" : "bi-person-x"}"></i></button>`);
            }
            const initials = `${user.name?.[0] || ""}${user.lastname?.[0] || ""}`.toUpperCase() || "US";
            const avatar = user.profile_photo_path ? `<img src="${escapeHtml(photoUrl(user.profile_photo_path))}" class="table-avatar" alt="">` : `<span class="table-avatar">${escapeHtml(initials)}</span>`;
            const role = roleLabel(user.primary_role);
            const row = document.createElement("tr");
            row.innerHTML = `<td class="small"><div class="table-user">${avatar}<span>${escapeHtml(user.id_users)}</span></div></td><td>${escapeHtml(`${user.name || ""} ${user.lastname || ""}`.trim())}</td><td>${escapeHtml(user.razon_social || "-")}</td><td>${escapeHtml(role)}</td><td>${stateBadge}</td><td>${accessBadge}</td><td class="small">${escapeHtml(formatDate(user.last_access))}</td><td><div class="users-actions">${actions.join("")}</div></td>`;
            tableBody.appendChild(row);
        });
        statusBox.classList.add("d-none");
        tableWrapper.classList.remove("d-none");
    }

    async function loadUsers() {
        try {
            setStatus(tr("users_loading"), "info");
            tableWrapper.classList.add("d-none");
            const params = new URLSearchParams();
            if (searchInput.value.trim()) params.set("q", searchInput.value.trim());
            if (stateFilter.value !== "all") params.set("state", stateFilter.value);
            if (companyFilter.value !== "all") params.set("id_company", companyFilter.value);
            if (roleFilter.value !== "all") params.set("access_level", roleFilter.value);
            const { response, data } = await api(`listar.php?${params}`);
            if (!response.ok || !data.success) throw new Error(data.message || tr("users_error_load"));
            renderUsers(data.data?.users || []);
        } catch (error) { setStatus(error.message || tr("users_error_load"), "danger"); }
    }
    async function getUser(idUsers) {
        const { response, data } = await api(`obtener.php?id_users=${encodeURIComponent(idUsers)}`);
        if (!response.ok || !data.success) throw new Error(data.message || tr("users_error_get"));
        return data.data;
    }
    function updateViewPhoto(path) {
        const img = document.getElementById("viewUserPhoto");
        const placeholder = document.getElementById("viewUserPhotoPlaceholder");
        img.classList.toggle("d-none", !path); placeholder.classList.toggle("d-none", Boolean(path));
        if (path) img.src = photoUrl(path);
    }
    async function showUser(idUsers) {
        try {
            const user = await getUser(idUsers);
            document.getElementById("viewUserEmail").textContent = user.id_users || "-";
            document.getElementById("viewUserName").textContent = user.name || "-";
            document.getElementById("viewUserLastname").textContent = user.lastname || "-";
            document.getElementById("viewUserRut").textContent = user.rut || "-";
            document.getElementById("viewUserCompany").textContent = user.razon_social || "-";
            document.getElementById("viewUserRoles").textContent = (user.role_names || []).map(roleLabel).join(", ") || tr("role_none");
            document.getElementById("viewUserAccess").textContent = roleLabel(user.primary_role);
            document.getElementById("viewUserState").textContent = Number(user.state) === 1 ? tr("common_active") : tr("common_inactive");
            document.getElementById("viewUserPasswordStatus").textContent = user.credential_status === "active" ? tr("users_status_configured") : user.credential_status === "reset_required" ? tr("users_status_reset") : tr("users_status_pending");
            document.getElementById("viewUserLanguage").textContent = user.language || "-";
            document.getElementById("viewUserLastAccess").textContent = formatDate(user.last_access);
            updateViewPhoto(user.profile_photo_path);
            viewModal.show();
        } catch (error) { showActionAlert(error.message, "danger"); }
    }

    async function openCreate() {
        form.reset(); showFormAlert(""); currentEditingUser = null; formMode.value = "create"; formTarget.value = ""; formTitle.textContent = tr("users_new_title"); userEmail.disabled = false; photoEditor.classList.add("d-none");
        [userName,userLastname,userRut,userLanguage,userRole,userCompany].forEach(el => setEditable(el,true));
        if (actorLevel === 1) { userCompanyGroup.classList.remove("d-none"); populateRoleSelect([], null); }
        else { userCompanyGroup.classList.add("d-none"); const companyId = context.company_id || companies[0]?.id_company; if (companyId) userCompany.value = String(companyId); const payload = await loadRoles(companyId); populateRoleSelect(payload.role_options || [], null); }
        formModal.show();
    }

    async function openEdit(idUsers) {
        try {
            const user = await getUser(idUsers); currentEditingUser = user; const editable = user.permissions?.editable_fields || [];
            form.reset(); showFormAlert(""); formMode.value = "edit"; formTarget.value = user.id_users; formTitle.textContent = tr("users_edit_title");
            userEmail.value = user.id_users || ""; userName.value = user.name || ""; userLastname.value = user.lastname || ""; userRut.value = user.rut || ""; userLanguage.value = String(user.language || "es").toLowerCase() === "esp" ? "es" : String(user.language || "es").toLowerCase(); userCompany.value = String(user.id_company || "");
            userEmail.disabled = true; setEditable(userName, editable.includes("name")); setEditable(userLastname, editable.includes("lastname")); setEditable(userRut, editable.includes("rut")); setEditable(userLanguage, editable.includes("language")); setEditable(userCompany, editable.includes("id_company"));
            userCompanyGroup.classList.toggle("d-none", actorLevel !== 1);
            if (editable.includes("id_role_group")) { const payload = await loadRoles(user.id_company); populateRoleSelect(payload.role_options || [], user.primary_role_id, user.primary_role); setEditable(userRole, true); }
            else { populateRoleSelect([], user.primary_role_id, user.primary_role); setEditable(userRole, false); }
            photoEditor.classList.toggle("d-none", !user.permissions?.can_manage_photo); setPhotoPreview(user.profile_photo_path); photoFile.value = "";
            formModal.show();
        } catch (error) { showActionAlert(error.message, "danger"); }
    }

    async function refreshRoleSelectForCompany() {
        if (!userCompany.value || userCompany.disabled || formMode.value !== "create") return;
        try { const payload = await loadRoles(userCompany.value); populateRoleSelect(payload.role_options || [], null); }
        catch (error) { showFormAlert(error.message); }
    }

    async function submitUser(event) {
        event.preventDefault(); showFormAlert(""); const mode = formMode.value;
        const payload = { id_users: mode === "create" ? userEmail.value.trim() : formTarget.value, name: userName.value.trim(), lastname: userLastname.value.trim(), rut: userRut.disabled ? "" : userRut.value.trim(), language: userLanguage.value, id_role_group: Number(userRole.value || 0), id_company: Number(userCompany.value || 0) };
        if (!payload.id_users || !payload.name || !payload.lastname) { showFormAlert(tr("users_required")); return; }
        formSubmit.disabled = true;
        try {
            const { response, data } = await postJson(mode === "create" ? "crear.php" : "actualizar.php", payload);
            if (!response.ok || !data.success) throw new Error(data.message || tr("users_error_save"));
            showActionAlert(data.message || tr("users_saved"), "success"); formModal.hide(); await loadUsers();
        } catch (error) { showFormAlert(error.message || tr("users_error_save")); }
        finally { formSubmit.disabled = false; }
    }

    function requestStateChange(idUsers, state) {
        pendingStateChange = { id_users: idUsers, state: Number(state) };
        stateModalText.textContent = pendingStateChange.state === 1 ? tr("users_confirm_activate", { user: idUsers }) : tr("users_confirm_deactivate", { user: idUsers }); stateModal.show();
    }
    async function confirmStateChange() {
        if (!pendingStateChange) return; stateConfirmBtn.disabled = true;
        try { const { response, data } = await postJson("cambiar-estado.php", pendingStateChange); if (!response.ok || !data.success) throw new Error(data.message || tr("users_error_state")); showActionAlert(data.message || tr("users_state_updated"), "success"); stateModal.hide(); pendingStateChange = null; await loadUsers(); }
        catch (error) { showActionAlert(error.message || tr("users_error_state"), "danger"); }
        finally { stateConfirmBtn.disabled = false; }
    }
    async function sendAccessLink(idUsers, credentialStatus) {
        const question = credentialStatus === "pending_activation" ? tr("users_confirm_access_activation", { user: idUsers }) : tr("users_confirm_access_reset", { user: idUsers });
        if (!window.confirm(question)) return;
        try { const { response, data } = await postJson("enviar-acceso.php", { id_users: idUsers }); if (!response.ok || !data.success) throw new Error(data.message || tr("users_error_access")); showActionAlert(data.message || tr("users_access_sent"), "success"); await loadUsers(); }
        catch (error) { showActionAlert(error.message || tr("users_error_access"), "danger"); }
    }

    async function uploadPhoto() {
        if (!currentEditingUser || !photoFile.files?.[0]) { showFormAlert(tr("users_photo_invalid")); return; }
        const formData = new FormData(); formData.append("id_users", currentEditingUser.id_users); formData.append("csrf_token", csrfToken); formData.append("foto", photoFile.files[0]); photoUploadBtn.disabled = true;
        try {
            const { response, data } = await api("subir-foto.php", { method: "POST", body: formData });
            if (!response.ok || !data.success) throw new Error(data.message || tr("users_photo_invalid"));
            currentEditingUser.profile_photo_path = data.data?.profile_photo_path || null; setPhotoPreview(currentEditingUser.profile_photo_path); photoFile.value = ""; showFormAlert(data.message || tr("users_photo_updated"), "success"); await loadUsers();
        } catch (error) { showFormAlert(error.message, "danger"); }
        finally { photoUploadBtn.disabled = false; }
    }
    async function removePhoto() {
        if (!currentEditingUser?.profile_photo_path) return;
        photoRemoveBtn.disabled = true;
        try { const { response, data } = await postJson("eliminar-foto.php", { id_users: currentEditingUser.id_users }); if (!response.ok || !data.success) throw new Error(data.message || tr("users_photo_invalid")); currentEditingUser.profile_photo_path = null; setPhotoPreview(null); showFormAlert(data.message || tr("users_photo_removed"), "success"); await loadUsers(); }
        catch (error) { showFormAlert(error.message, "danger"); }
        finally { photoRemoveBtn.disabled = false; }
    }

    tableBody.addEventListener("click", event => { const button = event.target.closest("button[data-action]"); if (!button) return; const id = button.dataset.user; if (button.dataset.action === "view") showUser(id); if (button.dataset.action === "edit") openEdit(id); if (button.dataset.action === "state") requestStateChange(id, button.dataset.state); if (button.dataset.action === "access") sendAccessLink(id, button.dataset.credentialStatus || "pending_activation"); });
    createBtn?.addEventListener("click", openCreate); form?.addEventListener("submit", submitUser); stateConfirmBtn?.addEventListener("click", confirmStateChange); userCompany?.addEventListener("change", refreshRoleSelectForCompany); photoUploadBtn?.addEventListener("click", uploadPhoto); photoRemoveBtn?.addEventListener("click", removePhoto);
    [companyFilter,stateFilter,roleFilter].forEach(el => el?.addEventListener("change", loadUsers));
    searchInput?.addEventListener("input", () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadUsers, 250); });

    (async () => { try { await initializeContext(); await loadUsers(); } catch (error) { setStatus(error.message || tr("users_error_init"), "danger"); } })();
});
