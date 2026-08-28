document.addEventListener('DOMContentLoaded', function () {
    var pageContainer = document.querySelector('[data-current-user][data-csrf-token]');
    var searchInput = document.getElementById('usersSearch');
    var stateFilter = document.getElementById('usersStateFilter');
    var companyFilter = document.getElementById('usersCompanyFilter');
    var companyFilterWrap = document.getElementById('usersCompanyFilterWrap');
    var roleFilter = document.getElementById('usersRoleFilter');
    var statusBox = document.getElementById('usersStatus');
    var actionAlert = document.getElementById('usersActionAlert');
    var tableWrapper = document.getElementById('usersTableWrapper');
    var tableBody = document.getElementById('usersTableBody');
    var createBtn = document.getElementById('usersCreateBtn');

    var formModalElement = document.getElementById('userFormModal');
    var viewModalElement = document.getElementById('userViewModal');
    var stateModalElement = document.getElementById('userStateModal');

    var userForm = document.getElementById('userForm');
    var userFormMode = document.getElementById('userFormMode');
    var userFormTarget = document.getElementById('userFormTarget');
    var userFormAlert = document.getElementById('userFormAlert');
    var userFormTitle = document.getElementById('userFormModalLabel');
    var userFormSubmit = document.getElementById('userFormSubmit');
    var userPasswordGroup = document.getElementById('userPasswordGroup');

    var inputEmail = document.getElementById('userEmail');
    var inputName = document.getElementById('userName');
    var inputLastname = document.getElementById('userLastname');
    var inputRut = document.getElementById('userRut');
    var inputLanguage = document.getElementById('userLanguage');
    var inputRole = document.getElementById('userRole');
    var inputCompany = document.getElementById('userCompany');
    var inputCompanyGroup = document.getElementById('userCompanyGroup');
    var inputPassword = document.getElementById('userPassword');

    var stateModalText = document.getElementById('userStateModalText');
    var stateConfirmBtn = document.getElementById('userStateConfirmBtn');

    var viewUserEmail = document.getElementById('viewUserEmail');
    var viewUserName = document.getElementById('viewUserName');
    var viewUserLastname = document.getElementById('viewUserLastname');
    var viewUserRut = document.getElementById('viewUserRut');
    var viewUserCompany = document.getElementById('viewUserCompany');
    var viewUserRoles = document.getElementById('viewUserRoles');
    var viewUserAccess = document.getElementById('viewUserAccess');
    var viewUserState = document.getElementById('viewUserState');
    var viewUserLanguage = document.getElementById('viewUserLanguage');
    var viewUserLastAccess = document.getElementById('viewUserLastAccess');

    if (!pageContainer || !searchInput || !stateFilter || !companyFilter || !companyFilterWrap || !roleFilter || !statusBox || !actionAlert || !tableWrapper || !tableBody || !createBtn || !formModalElement || !viewModalElement || !stateModalElement || !userForm || !userFormMode || !userFormTarget || !userFormAlert || !userFormTitle || !userFormSubmit || !userPasswordGroup || !inputEmail || !inputName || !inputLastname || !inputRut || !inputLanguage || !inputRole || !inputCompany || !inputCompanyGroup || !inputPassword || !stateModalText || !stateConfirmBtn || !viewUserEmail || !viewUserName || !viewUserLastname || !viewUserRut || !viewUserCompany || !viewUserRoles || !viewUserAccess || !viewUserState || !viewUserLanguage || !viewUserLastAccess) {
        return;
    }

    var currentUser = String(pageContainer.dataset.currentUser || '').trim();
    var csrfToken = String(pageContainer.dataset.csrfToken || '').trim();
    var actorLevel = Number(pageContainer.dataset.actorLevel || '0');
    var actorCompany = Number(pageContainer.dataset.actorCompany || '0');

    var formModal = new bootstrap.Modal(formModalElement);
    var viewModal = new bootstrap.Modal(viewModalElement);
    var stateModal = new bootstrap.Modal(stateModalElement);

    var allUsers = [];
    var roleCatalog = [];
    var companyCatalog = [];
    var contextData = null;
    var pendingStateChange = null;
    var isSubmittingForm = false;
    var isChangingState = false;

    function setStatus(message, variant) {
        statusBox.textContent = message;
        statusBox.classList.remove('d-none', 'alert-info', 'alert-warning', 'alert-danger');

        if (variant === 'warning') {
            statusBox.classList.add('alert-warning');
            return;
        }

        if (variant === 'danger') {
            statusBox.classList.add('alert-danger');
            return;
        }

        statusBox.classList.add('alert-info');
    }

    function hideStatus() {
        statusBox.classList.add('d-none');
    }

    function showActionAlert(message, variant) {
        actionAlert.textContent = message;
        actionAlert.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');

        if (variant === 'success') {
            actionAlert.classList.add('alert-success');
            return;
        }

        if (variant === 'warning') {
            actionAlert.classList.add('alert-warning');
            return;
        }

        if (variant === 'danger') {
            actionAlert.classList.add('alert-danger');
            return;
        }

        actionAlert.classList.add('alert-info');
    }

    function hideActionAlert() {
        actionAlert.classList.add('d-none');
    }

    function showFormAlert(message, variant) {
        userFormAlert.textContent = message;
        userFormAlert.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');

        if (variant === 'success') {
            userFormAlert.classList.add('alert-success');
            return;
        }

        if (variant === 'warning') {
            userFormAlert.classList.add('alert-warning');
            return;
        }

        if (variant === 'danger') {
            userFormAlert.classList.add('alert-danger');
            return;
        }

        userFormAlert.classList.add('alert-info');
    }

    function hideFormAlert() {
        userFormAlert.classList.add('d-none');
    }

    function formatLastAccess(value) {
        if (!value) {
            return 'No registra';
        }

        var normalized = String(value).replace(' ', 'T');
        var date = new Date(normalized);

        if (Number.isNaN(date.getTime())) {
            return 'No registra';
        }

        // Valor centinela para cuentas que aun no registran inicio de sesion.
        if (date.getTime() <= Date.UTC(1970, 0, 2, 0, 0, 0)) {
            return 'No registra';
        }

        return new Intl.DateTimeFormat('es-CL', {
            dateStyle: 'short',
            timeStyle: 'short'
        }).format(date);
    }

    function normalizeUser(rawUser) {
        return {
            id_users: String(rawUser.id_users || ''),
            name: String(rawUser.name || ''),
            lastname: String(rawUser.lastname || ''),
            id_company: Number(rawUser.id_company || 0),
            razon_social: String(rawUser.razon_social || ''),
            role_name: String(rawUser.role_name || 'Sin rol'),
            access_level: Number(rawUser.access_level || 0),
            access_label: String(rawUser.access_label || 'Sin nivel'),
            state: Number(rawUser.state) === 1 ? 1 : 0,
            last_access: rawUser.last_access || '',
            can_edit: Boolean(rawUser.can_edit),
            can_change_state: Boolean(rawUser.can_change_state)
        };
    }

    function setButtonLoading(button, loadingText, isLoading) {
        if (!button) {
            return;
        }

        if (isLoading) {
            button.dataset.originalText = button.textContent;
            button.textContent = loadingText;
            button.disabled = true;
            return;
        }

        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
    }

    function buildRoleFilterOptions(users) {
        var levels = new Map();

        users.forEach(function (user) {
            if (user.access_level > 0) {
                levels.set(String(user.access_level), user.access_label);
            }
        });

        var sorted = Array.from(levels.entries()).sort(function (a, b) {
            return Number(a[0]) - Number(b[0]);
        });

        roleFilter.innerHTML = '';

        var allOption = document.createElement('option');
        allOption.value = 'all';
        allOption.textContent = 'Todos';
        roleFilter.appendChild(allOption);

        sorted.forEach(function (entry) {
            var option = document.createElement('option');
            option.value = entry[0];
            option.textContent = entry[1];
            roleFilter.appendChild(option);
        });
    }

    function buildCompanyFilterOptions() {
        companyFilter.innerHTML = '';

        var allOption = document.createElement('option');
        allOption.value = 'all';
        allOption.textContent = 'Todas';
        companyFilter.appendChild(allOption);

        companyCatalog.forEach(function (company) {
            var option = document.createElement('option');
            option.value = String(company.id_company);
            option.textContent = company.razon_social;
            companyFilter.appendChild(option);
        });

        if (actorLevel !== 1 && actorCompany > 0) {
            companyFilter.value = String(actorCompany);
            companyFilter.disabled = true;
            companyFilterWrap.classList.add('d-none');
        } else {
            companyFilter.disabled = false;
            companyFilterWrap.classList.remove('d-none');
        }
    }

    function buildCompanyFormOptions(selectedId) {
        inputCompany.innerHTML = '';

        companyCatalog.forEach(function (company) {
            var option = document.createElement('option');
            option.value = String(company.id_company);
            option.textContent = company.razon_social;

            if (selectedId && Number(selectedId) === Number(company.id_company)) {
                option.selected = true;
            }

            inputCompany.appendChild(option);
        });

        if (actorLevel !== 1 && actorCompany > 0) {
            inputCompany.value = String(actorCompany);
            inputCompany.disabled = true;
            inputCompanyGroup.classList.add('d-none');
        } else {
            inputCompany.disabled = false;
            inputCompanyGroup.classList.remove('d-none');
        }
    }

    function buildRoleFormOptions(selectedId) {
        inputRole.innerHTML = '';

        roleCatalog.forEach(function (role) {
            var option = document.createElement('option');
            option.value = String(role.id_role_group);
            option.textContent = role.access_label + ' (' + role.name + ')';

            if (selectedId && Number(selectedId) === Number(role.id_role_group)) {
                option.selected = true;
            }

            inputRole.appendChild(option);
        });

        if (inputRole.options.length > 0 && !inputRole.value) {
            inputRole.selectedIndex = 0;
        }
    }

    async function loadRoleOptionsForCompany(companyId) {
        var url = 'php/usuarios/roles.php';
        if (companyId && Number(companyId) > 0) {
            url += '?id_company=' + encodeURIComponent(String(companyId));
        }

        var payload = await fetchJson(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        roleCatalog = payload.data && Array.isArray(payload.data.role_options) ? payload.data.role_options : [];
        buildRoleFormOptions(null);
    }

    function buildStateBadge(stateValue) {
        var badge = document.createElement('span');
        badge.classList.add('badge');

        if (stateValue === 1) {
            badge.classList.add('text-bg-success');
            badge.textContent = 'Activo';
            return badge;
        }

        badge.classList.add('text-bg-secondary');
        badge.textContent = 'Inactivo';
        return badge;
    }

    function createActionButton(label, action, userId, extraClasses, disabled) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-custom btn-sm';
        if (extraClasses) {
            button.className += ' ' + extraClasses;
        }
        button.textContent = label;
        button.dataset.action = action;
        button.dataset.userId = userId;
        if (disabled) {
            button.disabled = true;
        }
        return button;
    }

    function renderRows(users) {
        tableBody.innerHTML = '';

        users.forEach(function (user) {
            var row = document.createElement('tr');

            var userCell = document.createElement('td');
            userCell.textContent = user.id_users;
            row.appendChild(userCell);

            var nameCell = document.createElement('td');
            var fullName = (user.name + ' ' + user.lastname).trim();
            nameCell.textContent = fullName || '-';
            row.appendChild(nameCell);

            var companyCell = document.createElement('td');
            companyCell.textContent = user.razon_social || '-';
            row.appendChild(companyCell);

            var roleCell = document.createElement('td');
            roleCell.textContent = user.access_label || user.role_name || 'Sin rol';
            row.appendChild(roleCell);

            var stateCell = document.createElement('td');
            stateCell.appendChild(buildStateBadge(user.state));
            row.appendChild(stateCell);

            var accessCell = document.createElement('td');
            accessCell.textContent = formatLastAccess(user.last_access);
            row.appendChild(accessCell);

            var actionsCell = document.createElement('td');
            var actionsWrapper = document.createElement('div');
            actionsWrapper.className = 'users-actions';

            actionsWrapper.appendChild(createActionButton('Ver', 'view', user.id_users));
            actionsWrapper.appendChild(createActionButton('Editar', 'edit', user.id_users, '', !user.can_edit));

            var isSelf = user.id_users === currentUser;
            var stateLabel = user.state === 1 ? 'Desactivar' : 'Activar';
            actionsWrapper.appendChild(createActionButton(stateLabel, 'toggle-state', user.id_users, '', isSelf || !user.can_change_state));

            actionsCell.appendChild(actionsWrapper);
            row.appendChild(actionsCell);

            tableBody.appendChild(row);
        });
    }

    function applyFilters() {
        var searchTerm = searchInput.value.trim().toLowerCase();
        var selectedState = stateFilter.value;
        var selectedCompany = companyFilter.value;
        var selectedRole = roleFilter.value;

        var filteredUsers = allUsers.filter(function (user) {
            var stateMatch = selectedState === 'all' || String(user.state) === selectedState;

            var companyMatch = selectedCompany === 'all' || String(user.id_company) === selectedCompany;

            var roleMatch = selectedRole === 'all'
                || String(user.access_level) === selectedRole;

            var searchable = [
                user.id_users,
                user.name,
                user.lastname,
                user.razon_social,
                user.role_name
            ].join(' ').toLowerCase();

            var searchMatch = searchTerm === '' || searchable.includes(searchTerm);

            return stateMatch && companyMatch && roleMatch && searchMatch;
        });

        if (filteredUsers.length === 0) {
            tableWrapper.classList.add('d-none');
            setStatus('Sin usuarios para los filtros seleccionados.', 'warning');
            return;
        }

        renderRows(filteredUsers);
        tableWrapper.classList.remove('d-none');
        hideStatus();
    }

    async function fetchJson(url, options) {
        var response = await fetch(url, options);
        var payload;

        try {
            payload = await response.json();
        } catch (error) {
            throw new Error('Respuesta inválida del servidor.');
        }

        if (!response.ok || !payload.ok) {
            throw new Error(payload.message || 'No se pudo completar la operación.');
        }

        return payload;
    }

    async function loadRoles() {
        var payload = await fetchJson('php/usuarios/roles.php', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        contextData = payload.data && payload.data.context ? payload.data.context : null;
        companyCatalog = payload.data && Array.isArray(payload.data.companies) ? payload.data.companies : [];
        roleCatalog = payload.data && Array.isArray(payload.data.role_options) ? payload.data.role_options : [];

        buildCompanyFilterOptions();
        buildCompanyFormOptions(null);
        buildRoleFormOptions(null);

        if (contextData && contextData.can_create_user === false) {
            createBtn.classList.add('d-none');
        }
    }

    async function loadUsers() {
        setStatus('Cargando usuarios...', 'info');
        tableWrapper.classList.add('d-none');

        var query = new URLSearchParams();
        query.set('state', stateFilter.value || 'all');

        var searchValue = searchInput.value.trim();
        if (searchValue !== '') {
            query.set('q', searchValue);
        }

        var selectedCompany = companyFilter.value;
        if (selectedCompany && selectedCompany !== 'all') {
            query.set('id_company', selectedCompany);
        }

        var selectedAccess = roleFilter.value;
        if (selectedAccess && selectedAccess !== 'all') {
            query.set('access_level', selectedAccess);
        }

        var payload = await fetchJson('php/usuarios/listar.php?' + query.toString(), {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        var rows = payload.data && Array.isArray(payload.data.users) ? payload.data.users : [];
        allUsers = rows.map(normalizeUser);
        buildRoleFilterOptions(allUsers);
        applyFilters();
    }

    async function loadUserDetails(userId) {
        var encodedId = encodeURIComponent(userId);
        var payload = await fetchJson('php/usuarios/obtener.php?id_users=' + encodedId, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        return payload.data || null;
    }

    function fillViewModal(userData) {
        viewUserEmail.textContent = userData.id_users || '-';
        viewUserName.textContent = userData.name || '-';
        viewUserLastname.textContent = userData.lastname || '-';
        viewUserRut.textContent = userData.rut || '-';
        viewUserCompany.textContent = userData.razon_social || '-';
        viewUserRoles.textContent = userData.role_name || 'Sin rol';
        viewUserAccess.textContent = userData.access_label || 'Sin nivel';
        viewUserState.textContent = Number(userData.state) === 1 ? 'Activo' : 'Inactivo';
        viewUserLanguage.textContent = userData.language || '-';
        viewUserLastAccess.textContent = formatLastAccess(userData.last_access);
    }

    function resetForm() {
        userForm.reset();
        hideFormAlert();
        inputLanguage.value = 'ESP';
        userFormTarget.value = '';
        userFormSubmit.disabled = false;
        setButtonLoading(userFormSubmit, 'Guardando...', false);
    }

    function openCreateModal() {
        resetForm();
        userFormMode.value = 'create';
        userFormTitle.textContent = 'Nuevo usuario';
        inputEmail.readOnly = false;
        inputRole.disabled = false;
        inputPassword.required = true;
        userPasswordGroup.classList.remove('d-none');
        inputRut.disabled = false;
        buildCompanyFormOptions(null);
        buildRoleFormOptions(null);
        inputCompany.dispatchEvent(new Event('change'));
        formModal.show();
    }

    async function openEditModal(userId) {
        resetForm();
        userFormMode.value = 'edit';
        userFormTitle.textContent = 'Editar usuario';
        inputEmail.readOnly = true;
        inputPassword.required = false;
        userPasswordGroup.classList.add('d-none');

        var userData = await loadUserDetails(userId);

        userFormTarget.value = userData.id_users || '';
        inputEmail.value = userData.id_users || '';
        inputName.value = userData.name || '';
        inputLastname.value = userData.lastname || '';
        inputRut.value = userData.rut || '';
        inputLanguage.value = userData.language || 'ESP';
        buildCompanyFormOptions(userData.id_company || actorCompany);

        if (actorLevel === 1) {
            await loadRoleOptionsForCompany(userData.id_company || 0);
        }

        buildRoleFormOptions(userData.primary_role_id);

        var permissions = userData.permissions || {};
        var editableFields = Array.isArray(permissions.editable_fields) ? permissions.editable_fields : [];
        var canEditField = function (name) {
            return editableFields.includes(name);
        };

        inputName.disabled = !canEditField('name');
        inputLastname.disabled = !canEditField('lastname');
        inputRut.disabled = !canEditField('rut');
        inputLanguage.disabled = !canEditField('language');
        inputRole.disabled = !canEditField('id_role_group');
        inputCompany.disabled = !canEditField('id_company');

        if (permissions.is_self && actorLevel !== 1) {
            showFormAlert('Tu cuenta solo permite cambios según tu nivel de acceso.', 'warning');
        }

        formModal.show();
    }

    function buildPayloadFromForm() {
        return {
            id_users: inputEmail.value.trim(),
            name: inputName.value.trim(),
            lastname: inputLastname.value.trim(),
            rut: inputRut.value.trim(),
            language: inputLanguage.value.trim(),
            id_role_group: Number(inputRole.value || 0),
            id_company: Number(inputCompany.value || 0)
        };
    }

    async function submitCreate(payload) {
        payload.password = inputPassword.value;
        payload.csrf_token = csrfToken;

        return fetchJson('php/usuarios/crear.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
    }

    async function submitUpdate(payload) {
        payload.id_users = userFormTarget.value.trim();
        payload.csrf_token = csrfToken;

        return fetchJson('php/usuarios/actualizar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
    }

    async function handleFormSubmit(event) {
        event.preventDefault();

        if (isSubmittingForm) {
            return;
        }

        hideFormAlert();

        if (!userForm.checkValidity()) {
            userForm.reportValidity();
            return;
        }

        var mode = userFormMode.value;
        var payload = buildPayloadFromForm();

        isSubmittingForm = true;
        setButtonLoading(userFormSubmit, 'Guardando...', true);

        try {
            if (mode === 'create') {
                await submitCreate(payload);
                showActionAlert('Usuario creado correctamente.', 'success');
            } else {
                await submitUpdate(payload);
                showActionAlert('Usuario actualizado correctamente.', 'success');
            }

            formModal.hide();
            await loadUsers();
        } catch (error) {
            showFormAlert(error.message || 'No se pudo guardar el usuario.', 'danger');
        } finally {
            isSubmittingForm = false;
            setButtonLoading(userFormSubmit, 'Guardando...', false);
        }
    }

    async function openViewModal(userId) {
        var userData = await loadUserDetails(userId);
        fillViewModal(userData);
        viewModal.show();
    }

    function askStateChange(userId, currentState) {
        var nextState = currentState === 1 ? 0 : 1;
        pendingStateChange = {
            id_users: userId,
            state: nextState
        };

        stateModalText.textContent = nextState === 1
            ? '¿Deseas activar este usuario?'
            : '¿Deseas desactivar este usuario?';

        stateModal.show();
    }

    async function submitStateChange() {
        if (!pendingStateChange || isChangingState) {
            return;
        }

        isChangingState = true;
        setButtonLoading(stateConfirmBtn, 'Procesando...', true);

        try {
            var payload = {
                id_users: pendingStateChange.id_users,
                state: pendingStateChange.state,
                csrf_token: csrfToken
            };

            await fetchJson('php/usuarios/cambiar-estado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            stateModal.hide();
            showActionAlert(pendingStateChange.state === 1 ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.', 'success');
            await loadUsers();
        } catch (error) {
            showActionAlert(error.message || 'No se pudo cambiar el estado del usuario.', 'danger');
        } finally {
            pendingStateChange = null;
            isChangingState = false;
            setButtonLoading(stateConfirmBtn, 'Procesando...', false);
        }
    }

    async function handleTableAction(event) {
        var target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        var actionButton = target.closest('button[data-action][data-user-id]');

        if (!actionButton) {
            return;
        }

        var action = actionButton.dataset.action || '';
        var userId = actionButton.dataset.userId || '';

        if (!action || !userId) {
            return;
        }

        try {
            hideActionAlert();

            if (action === 'view') {
                await openViewModal(userId);
                return;
            }

            if (action === 'edit') {
                await openEditModal(userId);
                return;
            }

            if (action === 'toggle-state') {
                var currentUser = allUsers.find(function (u) {
                    return u.id_users === userId;
                });

                if (!currentUser) {
                    showActionAlert('No se pudo identificar el usuario seleccionado.', 'danger');
                    return;
                }

                askStateChange(userId, currentUser.state);
            }
        } catch (error) {
            showActionAlert(error.message || 'No se pudo completar la acción.', 'danger');
        }
    }

    searchInput.addEventListener('input', applyFilters);
    stateFilter.addEventListener('change', function () {
        loadUsers().catch(function (error) {
            setStatus(error.message || 'Error al cargar usuarios.', 'danger');
        });
    });
    companyFilter.addEventListener('change', function () {
        loadUsers().catch(function (error) {
            setStatus(error.message || 'Error al cargar usuarios.', 'danger');
        });
    });
    roleFilter.addEventListener('change', function () {
        loadUsers().catch(function (error) {
            setStatus(error.message || 'Error al cargar usuarios.', 'danger');
        });
    });
    createBtn.addEventListener('click', openCreateModal);
    inputCompany.addEventListener('change', function () {
        if (actorLevel !== 1) {
            return;
        }

        loadRoleOptionsForCompany(inputCompany.value).catch(function (error) {
            showFormAlert(error.message || 'No se pudieron cargar los roles de la empresa.', 'danger');
        });
    });
    userForm.addEventListener('submit', handleFormSubmit);
    stateConfirmBtn.addEventListener('click', submitStateChange);
    tableBody.addEventListener('click', handleTableAction);

    loadRoles().then(function () {
        return loadUsers();
    }).catch(function (error) {
        tableWrapper.classList.add('d-none');
        setStatus(error.message || 'Error al cargar usuarios.', 'danger');
    });
});
