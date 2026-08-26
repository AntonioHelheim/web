document.addEventListener('DOMContentLoaded', function () {
    var pageContainer = document.querySelector('[data-current-user][data-csrf-token]');
    var searchInput = document.getElementById('usersSearch');
    var stateFilter = document.getElementById('usersStateFilter');
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
    var inputPassword = document.getElementById('userPassword');

    var stateModalText = document.getElementById('userStateModalText');
    var stateConfirmBtn = document.getElementById('userStateConfirmBtn');

    var viewUserEmail = document.getElementById('viewUserEmail');
    var viewUserName = document.getElementById('viewUserName');
    var viewUserLastname = document.getElementById('viewUserLastname');
    var viewUserRut = document.getElementById('viewUserRut');
    var viewUserCompany = document.getElementById('viewUserCompany');
    var viewUserRoles = document.getElementById('viewUserRoles');
    var viewUserState = document.getElementById('viewUserState');
    var viewUserLanguage = document.getElementById('viewUserLanguage');
    var viewUserLastAccess = document.getElementById('viewUserLastAccess');

    if (!pageContainer || !searchInput || !stateFilter || !roleFilter || !statusBox || !actionAlert || !tableWrapper || !tableBody || !createBtn || !formModalElement || !viewModalElement || !stateModalElement || !userForm || !userFormMode || !userFormTarget || !userFormAlert || !userFormTitle || !userFormSubmit || !userPasswordGroup || !inputEmail || !inputName || !inputLastname || !inputRut || !inputLanguage || !inputRole || !inputPassword || !stateModalText || !stateConfirmBtn || !viewUserEmail || !viewUserName || !viewUserLastname || !viewUserRut || !viewUserCompany || !viewUserRoles || !viewUserState || !viewUserLanguage || !viewUserLastAccess) {
        return;
    }

    var currentUser = String(pageContainer.dataset.currentUser || '').trim();
    var csrfToken = String(pageContainer.dataset.csrfToken || '').trim();

    var formModal = new bootstrap.Modal(formModalElement);
    var viewModal = new bootstrap.Modal(viewModalElement);
    var stateModal = new bootstrap.Modal(stateModalElement);

    var allUsers = [];
    var roleCatalog = [];
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
            razon_social: String(rawUser.razon_social || ''),
            role_name: String(rawUser.role_name || 'Sin rol'),
            state: Number(rawUser.state) === 1 ? 1 : 0,
            last_access: rawUser.last_access || ''
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
        var roles = new Set();

        users.forEach(function (user) {
            String(user.role_name)
                .split(',')
                .map(function (role) { return role.trim(); })
                .filter(function (role) { return role.length > 0; })
                .forEach(function (role) { roles.add(role); });
        });

        var sortedRoles = Array.from(roles).sort(function (a, b) {
            return a.localeCompare(b, 'es');
        });

        roleFilter.innerHTML = '';

        var allOption = document.createElement('option');
        allOption.value = 'all';
        allOption.textContent = 'Todos';
        roleFilter.appendChild(allOption);

        sortedRoles.forEach(function (role) {
            var option = document.createElement('option');
            option.value = role;
            option.textContent = role;
            roleFilter.appendChild(option);
        });
    }

    function buildRoleFormOptions(selectedId) {
        inputRole.innerHTML = '';

        roleCatalog.forEach(function (role) {
            var option = document.createElement('option');
            option.value = String(role.id_role_group);
            option.textContent = role.name;

            if (selectedId && Number(selectedId) === Number(role.id_role_group)) {
                option.selected = true;
            }

            inputRole.appendChild(option);
        });
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
            roleCell.textContent = user.role_name || 'Sin rol';
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
            actionsWrapper.appendChild(createActionButton('Editar', 'edit', user.id_users));

            var isSelf = user.id_users === currentUser;
            var stateLabel = user.state === 1 ? 'Desactivar' : 'Activar';
            actionsWrapper.appendChild(createActionButton(stateLabel, 'toggle-state', user.id_users, '', isSelf));

            actionsCell.appendChild(actionsWrapper);
            row.appendChild(actionsCell);

            tableBody.appendChild(row);
        });
    }

    function applyFilters() {
        var searchTerm = searchInput.value.trim().toLowerCase();
        var selectedState = stateFilter.value;
        var selectedRole = roleFilter.value;

        var filteredUsers = allUsers.filter(function (user) {
            var stateMatch = selectedState === 'all' || String(user.state) === selectedState;

            var roleMatch = selectedRole === 'all'
                || String(user.role_name)
                    .split(',')
                    .map(function (role) { return role.trim(); })
                    .includes(selectedRole);

            var searchable = [
                user.id_users,
                user.name,
                user.lastname,
                user.razon_social,
                user.role_name
            ].join(' ').toLowerCase();

            var searchMatch = searchTerm === '' || searchable.includes(searchTerm);

            return stateMatch && roleMatch && searchMatch;
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

        roleCatalog = Array.isArray(payload.data) ? payload.data : [];
        buildRoleFormOptions(null);
    }

    async function loadUsers() {
        setStatus('Cargando usuarios...', 'info');
        tableWrapper.classList.add('d-none');

        var payload = await fetchJson('php/usuarios/listar.php', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        allUsers = Array.isArray(payload.data) ? payload.data.map(normalizeUser) : [];
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
        buildRoleFormOptions(null);
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
        buildRoleFormOptions(userData.primary_role_id);

        var isSelf = userData.id_users === currentUser;
        if (isSelf) {
            inputRole.disabled = true;
            showFormAlert('Por seguridad, no puedes quitarte tu propio rol administrador desde esta interfaz.', 'warning');
        } else {
            inputRole.disabled = false;
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
            id_role_group: Number(inputRole.value || 0)
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
    stateFilter.addEventListener('change', applyFilters);
    roleFilter.addEventListener('change', applyFilters);
    createBtn.addEventListener('click', openCreateModal);
    userForm.addEventListener('submit', handleFormSubmit);
    stateConfirmBtn.addEventListener('click', submitStateChange);
    tableBody.addEventListener('click', handleTableAction);

    Promise.all([loadRoles(), loadUsers()]).catch(function (error) {
        tableWrapper.classList.add('d-none');
        setStatus(error.message || 'Error al cargar usuarios.', 'danger');
    });
});
