/**
 * ==========================================================
 * CENTROS.JS
 * SAFETY CONTROL TOWER — Gestión de Centros/Sedes
 *
 * Misma estructura que proyectos.js, sin el panel de trabajadores
 * (los centros/sedes no tienen asociación directa con trabajadores).
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) {
        return;
    }

    const csrfToken = container.dataset.csrfToken;
    const isGlobalAdmin = container.dataset.isGlobalAdmin === "1";

    const i18nNode = document.getElementById("centersI18n");
    const I18N = i18nNode ? JSON.parse(i18nNode.textContent) : {};
    const tt = function (key, fallback) { return I18N[key] || fallback; };

    const companySelect     = document.getElementById("companySelect");

    const centerForm         = document.getElementById("centerForm");
    const centerFormMode     = document.getElementById("centerFormMode");
    const centerFormTarget   = document.getElementById("centerFormTarget");
    const centerNameInput    = document.getElementById("centerName");
    const centerDescInput    = document.getElementById("centerDescription");
    const centerSubmitBtn    = document.getElementById("centerSubmitBtn");
    const centerCancelBtn    = document.getElementById("centerCancelEditBtn");
    const centersActionAlert = document.getElementById("centersActionAlert");

    const centersStatus    = document.getElementById("centersStatus");
    const centersTableWrap = document.getElementById("centersTableWrapper");
    const centersTableBody = document.getElementById("centersTableBody");

    let currentCompanyId = null;

    function mostrarAlerta(elemento, mensaje, variante) {
        if (!elemento) return;
        elemento.textContent = mensaje;
        elemento.classList.remove("d-none", "alert-success", "alert-danger", "alert-info", "alert-warning");
        elemento.classList.add("alert-" + (variante || "danger"));
    }

    function escapeHtml(texto) {
        const div = document.createElement("div");
        div.textContent = texto == null ? "" : String(texto);
        return div.innerHTML;
    }

    async function llamarApi(url, opciones) {
        opciones = opciones || {};
        const respuesta = await fetch(url, opciones);
        try {
            return await respuesta.json();
        } catch (e) {
            return { success: false, message: "Respuesta inválida del servidor." };
        }
    }

    function postJson(url, datos) {
        return llamarApi(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(Object.assign({ csrf_token: csrfToken }, datos)),
        });
    }

    async function cargarEmpresasDisponibles() {
        if (!companySelect) return;

        const resultado = await llamarApi("./empresas-disponibles.php");
        if (!resultado.success) {
            mostrarAlerta(centersActionAlert, resultado.message || tt("centers_error_load_companies", "No se pudieron cargar las empresas."), "danger");
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
                cargarCentros();
            } else {
                centersTableWrap.classList.add("d-none");
                centersStatus.classList.remove("d-none");
                centersStatus.textContent = tt("centers_select_company_first", "Selecciona una empresa para ver sus centros/sedes.");
            }
        });

        cargarEmpresasDisponibles();
    } else {
        cargarCentros();
    }

    async function cargarCentros() {
        centersStatus.classList.remove("d-none", "alert-danger");
        centersStatus.classList.add("alert-info");
        centersStatus.textContent = tt("centers_loading", "Cargando centros/sedes...");
        centersTableWrap.classList.add("d-none");

        const url = isGlobalAdmin && currentCompanyId
            ? "./listar.php?id_company=" + encodeURIComponent(currentCompanyId)
            : "./listar.php";

        const resultado = await llamarApi(url);

        if (!resultado.success) {
            centersStatus.classList.remove("alert-info");
            centersStatus.classList.add("alert-danger");
            centersStatus.textContent = resultado.message || tt("centers_error_load", "No se pudieron cargar los centros/sedes.");
            return;
        }

        const centros = resultado.data || [];

        if (centros.length === 0) {
            centersStatus.classList.remove("alert-danger");
            centersStatus.classList.add("alert-info");
            centersStatus.textContent = tt("centers_empty", "Todavía no hay centros/sedes registrados.");
            centersTableWrap.classList.add("d-none");
            return;
        }

        centersStatus.classList.add("d-none");
        centersTableWrap.classList.remove("d-none");
        renderizarTablaCentros(centros);
    }

    function renderizarTablaCentros(centros) {
        centersTableBody.innerHTML = "";

        centros.forEach(function (centro) {
            const fila = document.createElement("tr");

            const estadoTexto = String(centro.state) === "1" ? tt("common_active", "Activo") : tt("common_inactive", "Inactivo");
            const estadoClase = String(centro.state) === "1" ? "text-success" : "text-muted";
            const botonEstadoTexto = String(centro.state) === "1" ? tt("common_deactivate", "Dar de baja") : tt("common_reactivate", "Reactivar");

            fila.innerHTML =
                '<td>' + escapeHtml(centro.id_company_center) + '</td>' +
                '<td>' + escapeHtml(centro.name) + '</td>' +
                '<td>' + escapeHtml(centro.description) + '</td>' +
                '<td class="' + estadoClase + '">' + estadoTexto + '</td>' +
                '<td class="form-actions">' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="edit">' + tt("common_edit", "Editar") + '</button>' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="toggle-state">' + botonEstadoTexto + '</button>' +
                '</td>';

            fila.querySelector('[data-action="edit"]').addEventListener("click", function () {
                iniciarEdicion(centro);
            });

            fila.querySelector('[data-action="toggle-state"]').addEventListener("click", function () {
                cambiarEstadoCentro(centro);
            });

            centersTableBody.appendChild(fila);
        });
    }

    function iniciarEdicion(centro) {
        if (!centerForm) return;

        centerFormMode.value = "edit";
        centerFormTarget.value = centro.id_company_center;
        centerNameInput.value = centro.name;
        centerDescInput.value = centro.description;

        centerSubmitBtn.textContent = tt("centers_save_changes", "Guardar cambios");
        if (centerCancelBtn) centerCancelBtn.classList.remove("d-none");

        centerForm.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    function cancelarEdicion() {
        if (!centerForm) return;

        centerForm.reset();
        centerFormMode.value = "create";
        centerFormTarget.value = "";
        centerSubmitBtn.textContent = tt("centers_save", "Guardar centro/sede");
        if (centerCancelBtn) centerCancelBtn.classList.add("d-none");
    }

    if (centerCancelBtn) {
        centerCancelBtn.addEventListener("click", cancelarEdicion);
    }

    if (centerForm) {
        centerForm.addEventListener("submit", async function (event) {
            event.preventDefault();

            const modo = centerFormMode.value;
            const datos = {
                name: centerNameInput.value.trim(),
                description: centerDescInput.value.trim(),
            };

            if (!datos.name || !datos.description) {
                mostrarAlerta(centersActionAlert, tt("centers_required_fields", "Nombre y descripción son obligatorios."), "danger");
                return;
            }

            let url = "./crear.php";
            if (modo === "edit") {
                url = "./editar.php";
                datos.id_company_center = centerFormTarget.value;
            } else if (isGlobalAdmin) {
                if (!currentCompanyId) {
                    mostrarAlerta(centersActionAlert, tt("centers_select_company_before_create", "Selecciona una empresa antes de crear un centro/sede."), "danger");
                    return;
                }
                datos.id_company = currentCompanyId;
            }

            centerSubmitBtn.disabled = true;
            const resultado = await postJson(url, datos);
            centerSubmitBtn.disabled = false;

            if (!resultado.success) {
                mostrarAlerta(centersActionAlert, resultado.message || tt("centers_error_save", "No se pudo guardar el centro/sede."), "danger");
                return;
            }

            mostrarAlerta(centersActionAlert, resultado.message || tt("centers_saved", "Centro/sede guardado correctamente."), "success");
            cancelarEdicion();
            cargarCentros();
        });
    }

    async function cambiarEstadoCentro(centro) {
        const nuevoEstado = String(centro.state) === "1" ? 0 : 1;
        const confirmacion = nuevoEstado === 1
            ? tt("centers_confirm_reactivate", "¿Reactivar este centro/sede?")
            : tt("centers_confirm_deactivate", "¿Dar de baja este centro/sede? Podrás reactivarlo más adelante.");

        if (!window.confirm(confirmacion)) {
            return;
        }

        const resultado = await postJson("./cambiar-estado.php", {
            id_company_center: centro.id_company_center,
            state: nuevoEstado,
        });

        if (!resultado.success) {
            mostrarAlerta(centersActionAlert, resultado.message || tt("centers_error_state", "No se pudo cambiar el estado."), "danger");
            return;
        }

        mostrarAlerta(centersActionAlert, resultado.message || tt("centers_state_updated", "Estado actualizado."), "success");
        cargarCentros();
    }

});
