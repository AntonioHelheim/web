/**
 * ==========================================================
 * TRABAJADORES.JS
 * SAFETY CONTROL TOWER — Gestión de Trabajadores
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) {
        return;
    }

    const csrfToken = container.dataset.csrfToken;
    const isGlobalAdmin = container.dataset.isGlobalAdmin === "1";

    const companySelect = document.getElementById("companySelect");

    const workerForm         = document.getElementById("workerForm");
    const workerFormMode     = document.getElementById("workerFormMode");
    const workerFormTarget   = document.getElementById("workerFormTarget");
    const workerFormTitle    = document.getElementById("workerFormTitle");
    const workerRutInput     = document.getElementById("workerRut");
    const workerRutHelp      = document.getElementById("workerRutHelp");
    const workerNameInput    = document.getElementById("workerName");
    const workerLastnameInput= document.getElementById("workerLastname");
    const workerEmailInput   = document.getElementById("workerEmail");
    const workerPhoneInput   = document.getElementById("workerPhone");
    const workerPositionInput= document.getElementById("workerPosition");
    const workerSubmitBtn    = document.getElementById("workerSubmitBtn");
    const workerCancelBtn    = document.getElementById("workerCancelEditBtn");
    const workersActionAlert = document.getElementById("workersActionAlert");

    const workerPhotoSection = document.getElementById("workerPhotoSection");
    const workerPhotoPreview = document.getElementById("workerPhotoPreview");
    const workerPhotoInput   = document.getElementById("workerPhotoInput");
    const workerPhotoUploadBtn = document.getElementById("workerPhotoUploadBtn");

    const searchInput   = document.getElementById("workerSearchInput");
    const stateFilter    = document.getElementById("workerStateFilter");

    const workersStatus      = document.getElementById("workersStatus");
    const workersTableWrap   = document.getElementById("workersTableWrapper");
    const workersTableBody   = document.getElementById("workersTableBody");

    let currentCompanyId = null;
    let searchDebounceTimer = null;

    /* ==========================================================
       HELPERS
    ========================================================== */

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

    function construirQuery(idCompany) {
        const params = new URLSearchParams();
        if (isGlobalAdmin && idCompany) {
            params.set("id_company", idCompany);
        }
        const termino = (searchInput.value || "").trim();
        if (termino) {
            params.set("q", termino);
        }
        if (stateFilter.value !== "") {
            params.set("state", stateFilter.value);
        }
        return params.toString();
    }

    /* ==========================================================
       SELECTOR DE EMPRESA
    ========================================================== */

    async function cargarEmpresasDisponibles() {
        if (!companySelect) return;

        const resultado = await llamarApi("./empresas-disponibles.php");
        if (!resultado.success) {
            mostrarAlerta(workersActionAlert, resultado.message || "No se pudieron cargar las empresas.", "danger");
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
                cargarTrabajadores();
            } else {
                workersTableWrap.classList.add("d-none");
                workersStatus.classList.remove("d-none");
                workersStatus.textContent = "Selecciona una empresa para ver sus trabajadores.";
            }
        });

        cargarEmpresasDisponibles();
    } else {
        cargarTrabajadores();
    }

    /* ==========================================================
       BÚSQUEDA / FILTROS
    ========================================================== */

    searchInput.addEventListener("input", function () {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(cargarTrabajadores, 350);
    });

    stateFilter.addEventListener("change", cargarTrabajadores);

    /* ==========================================================
       LISTADO
    ========================================================== */

    async function cargarTrabajadores() {
        if (isGlobalAdmin && !currentCompanyId) {
            return;
        }

        workersStatus.classList.remove("d-none", "alert-danger");
        workersStatus.classList.add("alert-info");
        workersStatus.textContent = "Cargando trabajadores...";
        workersTableWrap.classList.add("d-none");

        const query = construirQuery(currentCompanyId);
        const resultado = await llamarApi("./listar.php" + (query ? "?" + query : ""));

        if (!resultado.success) {
            workersStatus.classList.remove("alert-info");
            workersStatus.classList.add("alert-danger");
            workersStatus.textContent = resultado.message || "No se pudieron cargar los trabajadores.";
            return;
        }

        const trabajadores = resultado.data || [];

        if (trabajadores.length === 0) {
            workersStatus.classList.remove("alert-danger");
            workersStatus.classList.add("alert-info");
            workersStatus.textContent = "No hay trabajadores que coincidan con la búsqueda.";
            workersTableWrap.classList.add("d-none");
            return;
        }

        workersStatus.classList.add("d-none");
        workersTableWrap.classList.remove("d-none");
        renderizarTabla(trabajadores);
    }

    function renderizarTabla(trabajadores) {
        workersTableBody.innerHTML = "";

        trabajadores.forEach(function (trabajador) {
            const fila = document.createElement("tr");

            const estadoTexto = String(trabajador.state) === "1" ? "Activo" : "Inactivo";
            const estadoClase = String(trabajador.state) === "1" ? "text-success" : "text-muted";
            const botonEstadoTexto = String(trabajador.state) === "1" ? "Dar de baja" : "Reactivar";

            const fotoHtml = trabajador.photo_path
                ? '<img class="worker-photo-thumb" src="../../' + escapeHtml(trabajador.photo_path) + '" alt="">'
                : '<span class="worker-photo-placeholder"><i class="bi bi-person"></i></span>';

            const contacto = [trabajador.email, trabajador.phone].filter(Boolean).join(" · ") || "-";

            fila.innerHTML =
                '<td>' + fotoHtml + '</td>' +
                '<td>' + escapeHtml(trabajador.rut) + '</td>' +
                '<td>' + escapeHtml(trabajador.name) + ' ' + escapeHtml(trabajador.lastname) + '</td>' +
                '<td>' + escapeHtml(trabajador.position || '-') + '</td>' +
                '<td>' + escapeHtml(contacto) + '</td>' +
                '<td class="' + estadoClase + '">' + estadoTexto + '</td>' +
                '<td class="form-actions">' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="edit">Editar</button>' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="toggle-state">' + botonEstadoTexto + '</button>' +
                '</td>';

            fila.querySelector('[data-action="edit"]').addEventListener("click", function () {
                iniciarEdicion(trabajador);
            });

            fila.querySelector('[data-action="toggle-state"]').addEventListener("click", function () {
                cambiarEstado(trabajador);
            });

            workersTableBody.appendChild(fila);
        });
    }

    /* ==========================================================
       ALTA / EDICIÓN
    ========================================================== */

    function iniciarEdicion(trabajador) {
        if (!workerForm) return;

        workerFormMode.value = "edit";
        workerFormTarget.value = trabajador.id_worker;

        workerRutInput.value = trabajador.rut;
        workerRutInput.disabled = true;
        workerRutHelp.textContent = "El RUT no se puede editar; si está mal, da de baja este registro y crea uno nuevo.";

        workerNameInput.value = trabajador.name;
        workerLastnameInput.value = trabajador.lastname;
        workerEmailInput.value = trabajador.email || "";
        workerPhoneInput.value = trabajador.phone || "";
        workerPositionInput.value = trabajador.position || "";

        if (workerFormTitle) workerFormTitle.textContent = "Editar trabajador";
        workerSubmitBtn.textContent = "Guardar cambios";
        if (workerCancelBtn) workerCancelBtn.classList.remove("d-none");

        if (workerPhotoSection) {
            workerPhotoSection.classList.remove("d-none");
            workerPhotoPreview.src = trabajador.photo_path
                ? "../../" + trabajador.photo_path
                : "";
            workerPhotoPreview.style.display = trabajador.photo_path ? "" : "none";
        }

        workerForm.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    function cancelarEdicion() {
        if (!workerForm) return;

        workerForm.reset();
        workerFormMode.value = "create";
        workerFormTarget.value = "";
        workerRutInput.disabled = false;
        workerRutHelp.textContent = "No se puede editar después de creado.";

        if (workerFormTitle) workerFormTitle.textContent = "Nuevo trabajador";
        workerSubmitBtn.textContent = "Guardar trabajador";
        if (workerCancelBtn) workerCancelBtn.classList.add("d-none");
        if (workerPhotoSection) workerPhotoSection.classList.add("d-none");
    }

    if (workerCancelBtn) {
        workerCancelBtn.addEventListener("click", cancelarEdicion);
    }

    if (workerForm) {
        workerForm.addEventListener("submit", async function (event) {
            event.preventDefault();

            const modo = workerFormMode.value;
            const datos = {
                name: workerNameInput.value.trim(),
                lastname: workerLastnameInput.value.trim(),
                email: workerEmailInput.value.trim(),
                phone: workerPhoneInput.value.trim(),
                position: workerPositionInput.value.trim(),
            };

            if (!datos.name || !datos.lastname) {
                mostrarAlerta(workersActionAlert, "Nombre y apellido son obligatorios.", "danger");
                return;
            }

            let url = "./crear.php";
            if (modo === "edit") {
                url = "./editar.php";
                datos.id_worker = workerFormTarget.value;
            } else {
                const rut = workerRutInput.value.trim();
                if (!rut) {
                    mostrarAlerta(workersActionAlert, "El RUT es obligatorio.", "danger");
                    return;
                }
                datos.rut = rut;

                if (isGlobalAdmin) {
                    if (!currentCompanyId) {
                        mostrarAlerta(workersActionAlert, "Selecciona una empresa antes de crear un trabajador.", "danger");
                        return;
                    }
                    datos.id_company = currentCompanyId;
                }
            }

            workerSubmitBtn.disabled = true;
            const resultado = await postJson(url, datos);
            workerSubmitBtn.disabled = false;

            if (!resultado.success) {
                mostrarAlerta(workersActionAlert, resultado.message || "No se pudo guardar el trabajador.", "danger");
                return;
            }

            mostrarAlerta(workersActionAlert, resultado.message || "Trabajador guardado correctamente.", "success");

            if (modo === "create" && resultado.data && resultado.data.id_worker) {
                // Deja el formulario en modo edición sobre el trabajador
                // recién creado, para poder subirle la foto de inmediato
                // sin tener que volver a buscarlo en la tabla.
                iniciarEdicion({
                    id_worker: resultado.data.id_worker,
                    rut: datos.rut,
                    name: datos.name,
                    lastname: datos.lastname,
                    email: datos.email,
                    phone: datos.phone,
                    position: datos.position,
                    photo_path: null,
                });
            } else {
                cancelarEdicion();
            }

            cargarTrabajadores();
        });
    }

    async function cambiarEstado(trabajador) {
        const nuevoEstado = String(trabajador.state) === "1" ? 0 : 1;
        const confirmacion = nuevoEstado === 1
            ? "¿Reactivar a este trabajador?"
            : "¿Dar de baja a este trabajador? Podrás reactivarlo más adelante.";

        if (!window.confirm(confirmacion)) {
            return;
        }

        const resultado = await postJson("./cambiar-estado.php", {
            id_worker: trabajador.id_worker,
            state: nuevoEstado,
        });

        if (!resultado.success) {
            mostrarAlerta(workersActionAlert, resultado.message || "No se pudo cambiar el estado.", "danger");
            return;
        }

        mostrarAlerta(workersActionAlert, resultado.message || "Estado actualizado.", "success");
        cargarTrabajadores();
    }

    /* ==========================================================
       FOTO
    ========================================================== */

    if (workerPhotoUploadBtn) {
        workerPhotoUploadBtn.addEventListener("click", async function () {
            const idWorker = workerFormTarget.value;
            const archivo = workerPhotoInput.files[0];

            if (!idWorker) {
                mostrarAlerta(workersActionAlert, "Guarda el trabajador antes de subir una foto.", "warning");
                return;
            }

            if (!archivo) {
                mostrarAlerta(workersActionAlert, "Selecciona una imagen primero.", "warning");
                return;
            }

            const formData = new FormData();
            formData.append("csrf_token", csrfToken);
            formData.append("id_worker", idWorker);
            formData.append("foto", archivo);

            workerPhotoUploadBtn.disabled = true;
            const resultado = await llamarApi("./subir-foto.php", {
                method: "POST",
                body: formData,
            });
            workerPhotoUploadBtn.disabled = false;

            if (!resultado.success) {
                mostrarAlerta(workersActionAlert, resultado.message || "No se pudo subir la foto.", "danger");
                return;
            }

            mostrarAlerta(workersActionAlert, resultado.message || "Foto actualizada.", "success");
            workerPhotoPreview.src = "../../" + resultado.data.photo_path;
            workerPhotoPreview.style.display = "";
            workerPhotoInput.value = "";
            cargarTrabajadores();
        });
    }

});
