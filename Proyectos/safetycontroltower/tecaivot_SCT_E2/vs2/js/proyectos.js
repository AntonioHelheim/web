/**
 * ==========================================================
 * PROYECTOS.JS
 * SAFETY CONTROL TOWER — Gestión de Proyectos
 *
 * Consume los endpoints de /api/proyectos/. Todos responden en el
 * formato { success, message, data } de lib/response.php.
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) {
        return;
    }

    const csrfToken = container.dataset.csrfToken;
    const isGlobalAdmin = container.dataset.isGlobalAdmin === "1";

    const i18nNode = document.getElementById("projectsI18n");
    const I18N = i18nNode ? JSON.parse(i18nNode.textContent) : {};
    const tt = function (key, fallback) { return I18N[key] || fallback; };

    /* ==========================================================
       ELEMENTOS
    ========================================================== */

    const companySelect   = document.getElementById("companySelect");

    const projectForm         = document.getElementById("projectForm");
    const projectFormMode     = document.getElementById("projectFormMode");
    const projectFormTarget   = document.getElementById("projectFormTarget");
    const projectNameInput    = document.getElementById("projectName");
    const projectDescInput    = document.getElementById("projectDescription");
    const projectSubmitBtn    = document.getElementById("projectSubmitBtn");
    const projectCancelBtn    = document.getElementById("projectCancelEditBtn");
    const projectsActionAlert = document.getElementById("projectsActionAlert");

    const projectsStatus      = document.getElementById("projectsStatus");
    const projectsTableWrap   = document.getElementById("projectsTableWrapper");
    const projectsTableBody   = document.getElementById("projectsTableBody");

    const workersModalEl      = document.getElementById("projectWorkersModal");
    const workersModalTitle   = document.getElementById("projectWorkersModalLabel");
    const workersModalSubtitle= document.getElementById("projectWorkersModalSubtitle");
    const workersAlert        = document.getElementById("projectWorkersAlert");
    const workersList         = document.getElementById("projectWorkersList");
    const workerSearchInput   = document.getElementById("workerSearchInput");
    const workerSearchBtn     = document.getElementById("workerSearchBtn");
    const workerSearchResults = document.getElementById("workerSearchResults");

    const workersModal = workersModalEl && window.bootstrap
        ? new bootstrap.Modal(workersModalEl)
        : null;

    let currentCompanyId = null;
    let currentProjectIdForWorkers = null;

    /* ==========================================================
       HELPERS
    ========================================================== */

    function mostrarAlerta(elemento, mensaje, variante) {
        if (!elemento) return;
        elemento.textContent = mensaje;
        elemento.classList.remove("d-none", "alert-success", "alert-danger", "alert-info", "alert-warning");
        elemento.classList.add("alert-" + (variante || "danger"));
    }

    function ocultarAlerta(elemento) {
        if (!elemento) return;
        elemento.classList.add("d-none");
    }

    function escapeHtml(texto) {
        const div = document.createElement("div");
        div.textContent = texto == null ? "" : String(texto);
        return div.innerHTML;
    }

    async function llamarApi(url, opciones) {
        opciones = opciones || {};
        const respuesta = await fetch(url, opciones);
        let cuerpo;
        try {
            cuerpo = await respuesta.json();
        } catch (e) {
            cuerpo = { success: false, message: tt("common_invalid_server_response", "Respuesta inválida del servidor.") };
        }
        return cuerpo;
    }

    function postJson(url, datos) {
        return llamarApi(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(Object.assign({ csrf_token: csrfToken }, datos)),
        });
    }

    /* ==========================================================
       SELECTOR DE EMPRESA (solo administrador / administrador_completo)
    ========================================================== */

    async function cargarEmpresasDisponibles() {
        if (!companySelect) return;

        const resultado = await llamarApi("./empresas-disponibles.php");
        if (!resultado.success) {
            mostrarAlerta(projectsActionAlert, resultado.message || tt("projects_error_load_companies", "No se pudieron cargar las empresas."), "danger");
            return;
        }

        (resultado.data || []).forEach(function (empresa) {
            const option = document.createElement("option");
            option.value = empresa.id_company;
            option.textContent = empresa.razon_social;
            companySelect.appendChild(option);
        });
    }

    if (companySelect) {
        companySelect.addEventListener("change", function () {
            const valor = companySelect.value;
            currentCompanyId = valor ? parseInt(valor, 10) : null;
            if (currentCompanyId) {
                cargarProyectos();
            } else {
                projectsTableWrap.classList.add("d-none");
                projectsStatus.classList.remove("d-none");
                projectsStatus.textContent = tt("projects_select_company_first", "Selecciona una empresa para ver sus proyectos.");
            }
        });

        cargarEmpresasDisponibles();
    } else {
        // No es admin global: el backend resuelve la empresa desde la sesión,
        // no hace falta que el frontend sepa el id_company.
        cargarProyectos();
    }

    /* ==========================================================
       LISTADO DE PROYECTOS
    ========================================================== */

    async function cargarProyectos() {
        projectsStatus.classList.remove("d-none");
        projectsStatus.classList.remove("alert-danger");
        projectsStatus.classList.add("alert-info");
        projectsStatus.textContent = tt("projects_loading", "Cargando proyectos...");
        projectsTableWrap.classList.add("d-none");

        const url = isGlobalAdmin && currentCompanyId
            ? "./listar.php?id_company=" + encodeURIComponent(currentCompanyId)
            : "./listar.php";

        const resultado = await llamarApi(url);

        if (!resultado.success) {
            projectsStatus.classList.remove("alert-info");
            projectsStatus.classList.add("alert-danger");
            projectsStatus.textContent = resultado.message || tt("projects_error_load", "No se pudieron cargar los proyectos.");
            return;
        }

        const proyectos = resultado.data || [];

        if (proyectos.length === 0) {
            projectsStatus.classList.remove("alert-danger");
            projectsStatus.classList.add("alert-info");
            projectsStatus.textContent = tt("projects_empty", "Todavía no hay proyectos registrados.");
            projectsTableWrap.classList.add("d-none");
            return;
        }

        projectsStatus.classList.add("d-none");
        projectsTableWrap.classList.remove("d-none");
        renderizarTablaProyectos(proyectos);
    }

    function renderizarTablaProyectos(proyectos) {
        projectsTableBody.innerHTML = "";

        proyectos.forEach(function (proyecto) {
            const fila = document.createElement("tr");

            const estadoTexto = String(proyecto.state) === "1" ? tt("common_active", "Activo") : tt("common_inactive", "Inactivo");
            const estadoClase = String(proyecto.state) === "1" ? "text-success" : "text-muted";
            const botonEstadoTexto = String(proyecto.state) === "1" ? tt("common_deactivate", "Dar de baja") : tt("common_reactivate", "Reactivar");

            fila.innerHTML =
                '<td>' + escapeHtml(proyecto.id_project) + '</td>' +
                '<td>' + escapeHtml(proyecto.name) + '</td>' +
                '<td>' + escapeHtml(proyecto.description || '-') + '</td>' +
                '<td class="' + estadoClase + '">' + escapeHtml(estadoTexto) + '</td>' +
                '<td class="form-actions">' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="workers">' + escapeHtml(tt("projects_workers_btn", "Trabajadores")) + '</button>' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="edit">' + escapeHtml(tt("common_edit", "Editar")) + '</button>' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="toggle-state">' + escapeHtml(botonEstadoTexto) + '</button>' +
                '</td>';

            fila.querySelector('[data-action="edit"]').addEventListener("click", function () {
                iniciarEdicion(proyecto);
            });

            fila.querySelector('[data-action="toggle-state"]').addEventListener("click", function () {
                cambiarEstadoProyecto(proyecto);
            });

            fila.querySelector('[data-action="workers"]').addEventListener("click", function () {
                abrirModalTrabajadores(proyecto);
            });

            projectsTableBody.appendChild(fila);
        });
    }

    /* ==========================================================
       ALTA / EDICIÓN DE PROYECTO
    ========================================================== */

    function iniciarEdicion(proyecto) {
        if (!projectForm) return;

        projectFormMode.value = "edit";
        projectFormTarget.value = proyecto.id_project;
        projectNameInput.value = proyecto.name;
        projectDescInput.value = proyecto.description || "";

        projectSubmitBtn.textContent = tt("projects_save_changes", "Guardar cambios");
        if (projectCancelBtn) projectCancelBtn.classList.remove("d-none");

        projectForm.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    function cancelarEdicion() {
        if (!projectForm) return;

        projectForm.reset();
        projectFormMode.value = "create";
        projectFormTarget.value = "";
        projectSubmitBtn.textContent = tt("projects_save", "Guardar proyecto");
        if (projectCancelBtn) projectCancelBtn.classList.add("d-none");
    }

    if (projectCancelBtn) {
        projectCancelBtn.addEventListener("click", cancelarEdicion);
    }

    if (projectForm) {
        projectForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            ocultarAlerta(projectsActionAlert);

            const modo = projectFormMode.value;
            const datos = {
                name: projectNameInput.value.trim(),
                description: projectDescInput.value.trim(),
            };

            if (!datos.name) {
                mostrarAlerta(projectsActionAlert, tt("projects_required_name", "El nombre del proyecto es obligatorio."), "danger");
                return;
            }

            let url = "./crear.php";
            if (modo === "edit") {
                url = "./editar.php";
                datos.id_project = projectFormTarget.value;
            } else if (isGlobalAdmin) {
                if (!currentCompanyId) {
                    mostrarAlerta(projectsActionAlert, tt("projects_select_company_before_create", "Selecciona una empresa antes de crear un proyecto."), "danger");
                    return;
                }
                datos.id_company = currentCompanyId;
            }

            projectSubmitBtn.disabled = true;
            const resultado = await postJson(url, datos);
            projectSubmitBtn.disabled = false;

            if (!resultado.success) {
                mostrarAlerta(projectsActionAlert, resultado.message || tt("projects_error_save", "No se pudo guardar el proyecto."), "danger");
                return;
            }

            mostrarAlerta(projectsActionAlert, resultado.message || tt("projects_saved", "Proyecto guardado correctamente."), "success");
            cancelarEdicion();
            cargarProyectos();
        });
    }

    async function cambiarEstadoProyecto(proyecto) {
        const nuevoEstado = String(proyecto.state) === "1" ? 0 : 1;
        const confirmacion = nuevoEstado === 1
            ? tt("projects_confirm_reactivate", "¿Reactivar este proyecto?")
            : tt("projects_confirm_deactivate", "¿Dar de baja este proyecto? Podrás reactivarlo más adelante.");

        if (!window.confirm(confirmacion)) {
            return;
        }

        const resultado = await postJson("./cambiar-estado.php", {
            id_project: proyecto.id_project,
            state: nuevoEstado,
        });

        if (!resultado.success) {
            mostrarAlerta(projectsActionAlert, resultado.message || tt("projects_error_state", "No se pudo cambiar el estado."), "danger");
            return;
        }

        mostrarAlerta(projectsActionAlert, resultado.message || tt("projects_state_updated", "Estado actualizado."), "success");
        cargarProyectos();
    }

    /* ==========================================================
       MODAL DE TRABAJADORES ASOCIADOS
    ========================================================== */

    function abrirModalTrabajadores(proyecto) {
        currentProjectIdForWorkers = proyecto.id_project;
        workersModalTitle.textContent = tt("projects_workers_modal_title", "Trabajadores del proyecto");
        workersModalSubtitle.textContent = proyecto.name;
        ocultarAlerta(workersAlert);

        if (workerSearchInput) workerSearchInput.value = "";
        if (workerSearchResults) workerSearchResults.innerHTML = "";

        if (workersModal) {
            workersModal.show();
        }

        cargarTrabajadoresAsociados();
    }

    async function cargarTrabajadoresAsociados() {
        workersList.innerHTML = '<p class="text-muted mb-0">' + escapeHtml(tt("common_loading", "Cargando...")) + '</p>';

        const resultado = await llamarApi(
            "./trabajadores-listar.php?id_project=" + encodeURIComponent(currentProjectIdForWorkers)
        );

        if (!resultado.success) {
            workersList.innerHTML = "";
            mostrarAlerta(workersAlert, resultado.message || tt("projects_workers_error_load", "No se pudieron cargar los trabajadores."), "danger");
            return;
        }

        const trabajadores = resultado.data || [];

        if (trabajadores.length === 0) {
            workersList.innerHTML = '<p class="text-muted mb-0">' + escapeHtml(tt("projects_workers_empty", "Todavía no hay trabajadores asociados a este proyecto.")) + '</p>';
            return;
        }

        workersList.innerHTML = "";
        const wrapper = document.createElement("div");
        wrapper.className = "d-flex flex-wrap gap-2";

        trabajadores.forEach(function (trabajador) {
            const chip = document.createElement("span");
            chip.className = "worker-chip";
            chip.innerHTML =
                '<span>' + escapeHtml(trabajador.name) + ' ' + escapeHtml(trabajador.lastname) +
                ' <small class="text-muted">(' + escapeHtml(trabajador.rut) + ')</small></span>' +
                '<button type="button" title="' + escapeHtml(tt("projects_worker_remove_title", "Quitar del proyecto")) + '">&times;</button>';

            chip.querySelector("button").addEventListener("click", function () {
                desasociarTrabajador(trabajador.id_worker);
            });

            wrapper.appendChild(chip);
        });

        workersList.appendChild(wrapper);
    }

    async function desasociarTrabajador(idWorker) {
        if (!window.confirm(tt("projects_worker_remove_confirm", "¿Quitar a este trabajador del proyecto?"))) {
            return;
        }

        const resultado = await postJson("./trabajadores-desasociar.php", {
            id_project: currentProjectIdForWorkers,
            id_worker: idWorker,
        });

        if (!resultado.success) {
            mostrarAlerta(workersAlert, resultado.message || tt("projects_worker_remove_error", "No se pudo quitar al trabajador."), "danger");
            return;
        }

        cargarTrabajadoresAsociados();
    }

    if (workerSearchBtn) {
        workerSearchBtn.addEventListener("click", async function () {
            const termino = (workerSearchInput.value || "").trim();
            ocultarAlerta(workersAlert);
            workerSearchResults.innerHTML = "";

            if (termino.length < 2) {
                mostrarAlerta(workersAlert, tt("projects_worker_search_min_length", "Ingresa al menos 2 caracteres para buscar."), "warning");
                return;
            }

            const resultado = await llamarApi(
                "./trabajadores-buscar.php?id_project=" + encodeURIComponent(currentProjectIdForWorkers) +
                "&q=" + encodeURIComponent(termino)
            );

            if (!resultado.success) {
                mostrarAlerta(workersAlert, resultado.message || tt("projects_worker_search_error", "No se pudo realizar la búsqueda."), "danger");
                return;
            }

            const encontrados = resultado.data || [];

            if (encontrados.length === 0) {
                workerSearchResults.innerHTML = '<p class="text-muted mb-0 mt-2">' + escapeHtml(tt("projects_worker_search_no_results", "Sin resultados. Verifica que el trabajador ya esté registrado en el módulo de Trabajadores.")) + '</p>';
                return;
            }

            encontrados.forEach(function (trabajador) {
                const item = document.createElement("button");
                item.type = "button";
                item.className = "list-group-item list-group-item-action";
                item.textContent = trabajador.name + " " + trabajador.lastname + " (" + trabajador.rut + ")";

                item.addEventListener("click", async function () {
                    const resultadoAsociar = await postJson("./trabajadores-asociar.php", {
                        id_project: currentProjectIdForWorkers,
                        id_worker: trabajador.id_worker,
                    });

                    if (!resultadoAsociar.success) {
                        mostrarAlerta(workersAlert, resultadoAsociar.message || tt("projects_worker_associate_error", "No se pudo asociar al trabajador."), "danger");
                        return;
                    }

                    workerSearchInput.value = "";
                    workerSearchResults.innerHTML = "";
                    cargarTrabajadoresAsociados();
                });

                workerSearchResults.appendChild(item);
            });
        });
    }

});
