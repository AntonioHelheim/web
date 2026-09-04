/**
 * ==========================================================
 * EVENTOS.JS
 * SAFETY CONTROL TOWER — Eventos e Incidentes
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) return;

    const csrfToken = container.dataset.csrfToken;
    const isGlobalAdmin = container.dataset.isGlobalAdmin === "1";
    const puedeGestionar = container.dataset.puedeGestionar === "1";

    const companySelect = document.getElementById("companySelect");

    const eventForm = document.getElementById("eventForm");
    const eventFormMode = document.getElementById("eventFormMode");
    const eventFormTarget = document.getElementById("eventFormTarget");
    const eventType = document.getElementById("eventType");
    const eventCenter = document.getElementById("eventCenter");
    const eventCriticality = document.getElementById("eventCriticality");
    const eventProject = document.getElementById("eventProject");
    const eventWorker = document.getElementById("eventWorker");
    const eventDate = document.getElementById("eventDate");
    const eventDescription = document.getElementById("eventDescription");
    const eventSubmitBtn = document.getElementById("eventSubmitBtn");
    const eventCancelEditBtn = document.getElementById("eventCancelEditBtn");
    const eventActionAlert = document.getElementById("eventActionAlert");

    const filterCriticality = document.getElementById("filterCriticality");
    const filterState = document.getElementById("filterState");
    const filterSearch = document.getElementById("filterSearch");

    const eventsStatus = document.getElementById("eventsStatus");
    const eventsTableWrapper = document.getElementById("eventsTableWrapper");
    const eventsTableBody = document.getElementById("eventsTableBody");

    const eventDetail = document.getElementById("eventDetail");
    const eventDetailBody = document.getElementById("eventDetailBody");
    const eventDetailCloseBtn = document.getElementById("eventDetailCloseBtn");
    const detailAlert = document.getElementById("detailAlert");

    const trackingList = document.getElementById("trackingList");
    const trackingForm = document.getElementById("trackingForm");
    const trackingDescription = document.getElementById("trackingDescription");
    const trackingPerson = document.getElementById("trackingPerson");
    const trackingCommitment = document.getElementById("trackingCommitment");
    const trackingDeadline = document.getElementById("trackingDeadline");

    const evidenceList = document.getElementById("evidenceList");
    const evidenceForm = document.getElementById("evidenceForm");
    const evidenceFile = document.getElementById("evidenceFile");

    let currentCompanyId = null;
    let currentEventId = null;
    let searchDebounce = null;

    function mostrarAlerta(el, msg, variante) {
        if (!el) return;
        el.textContent = msg;
        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-info", "alert-warning");
        el.classList.add("alert-" + (variante || "danger"));
    }
    function ocultarAlerta(el) { if (el) el.classList.add("d-none"); }
    function escapeHtml(t) {
        const d = document.createElement("div");
        d.textContent = t == null ? "" : String(t);
        return d.innerHTML;
    }
    async function llamarApi(url, opciones) {
        opciones = opciones || {};
        const r = await fetch(url, opciones);
        try { return await r.json(); }
        catch (e) { return { success: false, message: "Respuesta inválida del servidor." }; }
    }
    function postJson(url, datos) {
        return llamarApi(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(Object.assign({ csrf_token: csrfToken }, datos)),
        });
    }

    /* ========================= EMPRESA Y CATALOGOS ========================= */

    async function cargarEmpresasDisponibles() {
        if (!companySelect) return;
        const r = await llamarApi("./empresas-disponibles.php");
        if (!r.success) { mostrarAlerta(eventActionAlert, r.message, "danger"); return; }
        (r.data || []).forEach(function (e) {
            const opt = document.createElement("option");
            opt.value = e.id_company;
            opt.textContent = e.razon_social;
            companySelect.appendChild(opt);
        });
    }

    async function cargarCatalogos() {
        const params = isGlobalAdmin && currentCompanyId ? "?id_company=" + encodeURIComponent(currentCompanyId) : "";

        const [tipos, centros, proyectos, trabajadores] = await Promise.all([
            llamarApi("./tipos-listar.php"),
            llamarApi("./centros-disponibles.php" + params),
            llamarApi("./proyectos-disponibles.php" + params),
            llamarApi("./trabajadores-disponibles.php" + params),
        ]);

        eventType.innerHTML = '<option value="">Selecciona...</option>';
        (tipos.data || []).forEach(function (t) {
            const opt = document.createElement("option");
            opt.value = t.id_event_type;
            opt.textContent = t.name;
            eventType.appendChild(opt);
        });

        eventCenter.innerHTML = '<option value="">Selecciona...</option>';
        (centros.data || []).forEach(function (c) {
            const opt = document.createElement("option");
            opt.value = c.id_company_center;
            opt.textContent = c.name;
            eventCenter.appendChild(opt);
        });
        if ((centros.data || []).length === 0) {
            mostrarAlerta(eventActionAlert, "Esta empresa todavía no tiene centros/sedes activos. Crea uno en Gestión de Centros/Sedes antes de reportar un evento.", "warning");
        }

        eventProject.innerHTML = '<option value="">Sin proyecto asociado</option>';
        (proyectos.data || []).forEach(function (p) {
            const opt = document.createElement("option");
            opt.value = p.id_project;
            opt.textContent = p.name;
            eventProject.appendChild(opt);
        });

        eventWorker.innerHTML = '<option value="">Sin trabajador asociado</option>';
        (trabajadores.data || []).forEach(function (w) {
            const opt = document.createElement("option");
            opt.value = w.id_worker;
            opt.textContent = w.name + " " + w.lastname + " (" + w.rut + ")";
            eventWorker.appendChild(opt);
        });
    }

    if (companySelect) {
        companySelect.addEventListener("change", function () {
            currentCompanyId = companySelect.value ? parseInt(companySelect.value, 10) : null;
            cerrarDetalle();
            if (currentCompanyId) {
                cargarCatalogos();
                cargarEventos();
            } else {
                eventsTableWrapper.classList.add("d-none");
                eventsStatus.classList.remove("d-none");
                eventsStatus.textContent = "Selecciona una empresa para ver sus eventos.";
            }
        });
        cargarEmpresasDisponibles();
    } else {
        cargarCatalogos();
        cargarEventos();
    }

    /* ========================= LISTADO ========================= */

    function construirQuery() {
        const params = new URLSearchParams();
        if (isGlobalAdmin && currentCompanyId) params.set("id_company", currentCompanyId);
        if (filterCriticality.value) params.set("criticality", filterCriticality.value);
        if (filterState.value) params.set("state", filterState.value);
        if (filterSearch.value.trim()) params.set("q", filterSearch.value.trim());
        return params.toString();
    }

    async function cargarEventos() {
        if (isGlobalAdmin && !currentCompanyId) return;

        eventsStatus.classList.remove("d-none", "alert-danger");
        eventsStatus.classList.add("alert-info");
        eventsStatus.textContent = "Cargando eventos...";
        eventsTableWrapper.classList.add("d-none");

        const query = construirQuery();
        const r = await llamarApi("./eventos-listar.php" + (query ? "?" + query : ""));

        if (!r.success) {
            eventsStatus.classList.remove("alert-info");
            eventsStatus.classList.add("alert-danger");
            eventsStatus.textContent = r.message || "No se pudieron cargar los eventos.";
            return;
        }

        const eventos = r.data || [];
        if (eventos.length === 0) {
            eventsStatus.classList.remove("alert-danger");
            eventsStatus.classList.add("alert-info");
            eventsStatus.textContent = "No hay eventos que coincidan con la búsqueda.";
            eventsTableWrapper.classList.add("d-none");
            return;
        }

        eventsStatus.classList.add("d-none");
        eventsTableWrapper.classList.remove("d-none");
        renderizarEventos(eventos);
    }

    [filterCriticality, filterState].forEach(function (el) {
        el.addEventListener("change", cargarEventos);
    });
    filterSearch.addEventListener("input", function () {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(cargarEventos, 350);
    });

    const etiquetasEstado = { 1: ["Abierto", "estado-1"], 2: ["En proceso", "estado-2"], 3: ["Cerrado", "estado-3"] };

    function renderizarEventos(eventos) {
        eventsTableBody.innerHTML = "";
        eventos.forEach(function (ev) {
            const [estadoTexto, estadoClase] = etiquetasEstado[ev.state] || ["-", ""];
            const fila = document.createElement("tr");
            fila.innerHTML =
                '<td>' + escapeHtml((ev.event_date || "").substring(0, 16).replace("T", " ")) + '</td>' +
                '<td>' + escapeHtml(ev.event_type_name) + '</td>' +
                '<td>' + escapeHtml(ev.center_name) + '</td>' +
                '<td class="crit-' + escapeHtml(ev.criticality) + '">' + escapeHtml(ev.criticality) + '</td>' +
                '<td class="' + estadoClase + '">' + estadoTexto + '</td>' +
                '<td class="form-actions"><button type="button" class="btn btn-outline-custom btn-sm" data-action="detail">Ver detalle</button></td>';

            fila.querySelector('[data-action="detail"]').addEventListener("click", function () { abrirDetalle(ev.id_security_events); });
            eventsTableBody.appendChild(fila);
        });
    }

    /* ========================= ALTA / EDICIÓN ========================= */

    if (eventForm) {
        eventForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            ocultarAlerta(eventActionAlert);

            const modo = eventFormMode.value;
            const datos = {
                id_company_center: eventCenter.value,
                id_project: eventProject.value || null,
                id_worker: eventWorker.value || null,
                id_event: eventType.value,
                event_date: eventDate.value,
                description: eventDescription.value.trim(),
                criticality: eventCriticality.value,
            };

            if (!datos.id_company_center || !datos.id_event || !datos.event_date || !datos.description) {
                mostrarAlerta(eventActionAlert, "Completa todos los campos obligatorios.", "danger");
                return;
            }

            let url = "./eventos-crear.php";
            if (modo === "edit") {
                url = "./eventos-editar.php";
                datos.id_security_events = eventFormTarget.value;
            } else if (isGlobalAdmin) {
                if (!currentCompanyId) { mostrarAlerta(eventActionAlert, "Selecciona una empresa primero.", "danger"); return; }
                datos.id_company = currentCompanyId;
            }

            eventSubmitBtn.disabled = true;
            const r = await postJson(url, datos);
            eventSubmitBtn.disabled = false;

            if (!r.success) { mostrarAlerta(eventActionAlert, r.message || "No se pudo guardar el evento.", "danger"); return; }
            mostrarAlerta(eventActionAlert, r.message || "Evento guardado.", "success");
            cancelarEdicionEvento();
            cargarEventos();
        });
    }

    function cancelarEdicionEvento() {
        eventForm.reset();
        eventFormMode.value = "create";
        eventFormTarget.value = "";
        eventSubmitBtn.textContent = "Reportar evento";
        eventCancelEditBtn.classList.add("d-none");
    }
    if (eventCancelEditBtn) eventCancelEditBtn.addEventListener("click", cancelarEdicionEvento);

    /* ========================= DETALLE ========================= */

    function cerrarDetalle() {
        currentEventId = null;
        eventDetail.classList.remove("activo");
    }
    if (eventDetailCloseBtn) eventDetailCloseBtn.addEventListener("click", cerrarDetalle);

    async function abrirDetalle(idEvento) {
        currentEventId = idEvento;
        ocultarAlerta(detailAlert);
        eventDetail.classList.add("activo");
        eventDetailBody.innerHTML = '<p class="text-muted">Cargando...</p>';
        eventDetail.scrollIntoView({ behavior: "smooth" });

        const r = await llamarApi("./eventos-detalle.php?id=" + encodeURIComponent(idEvento));
        if (!r.success) { mostrarAlerta(detailAlert, r.message || "No se pudo cargar el evento.", "danger"); return; }

        renderizarDetalle(r.data);
        renderizarTracking(r.data.seguimiento || []);
        renderizarEvidencias(r.data.evidencias || []);
    }

    function renderizarDetalle(ev) {
        const [estadoTexto] = etiquetasEstado[ev.state] || ["-"];
        eventDetailBody.innerHTML =
            '<p><strong>' + escapeHtml(ev.event_type_name) + '</strong> — ' + escapeHtml(ev.center_name) +
            (ev.project_name ? ' · Proyecto: ' + escapeHtml(ev.project_name) : '') +
            (ev.id_worker_name ? ' · Trabajador: ' + escapeHtml(ev.id_worker_name) : '') + '</p>' +
            '<p class="crit-' + escapeHtml(ev.criticality) + '">Criticidad: ' + escapeHtml(ev.criticality) + ' · Estado: ' + estadoTexto + '</p>' +
            '<p>' + escapeHtml(ev.description) + '</p>';

        if (puedeGestionar) {
            document.querySelectorAll('#eventDetail [data-estado]').forEach(function (btn) {
                btn.onclick = async function () {
                    const r = await postJson("./eventos-cambiar-estado.php", { id_security_events: currentEventId, state: parseInt(btn.dataset.estado, 10) });
                    if (!r.success) { mostrarAlerta(detailAlert, r.message, "danger"); return; }
                    mostrarAlerta(detailAlert, r.message, "success");
                    abrirDetalle(currentEventId);
                    cargarEventos();
                };
            });
        }
    }

    function renderizarTracking(items) {
        trackingList.innerHTML = "";
        if (items.length === 0) {
            trackingList.innerHTML = '<p class="text-muted mb-0">Sin seguimiento todavía.</p>';
            return;
        }
        items.forEach(function (t) {
            const row = document.createElement("div");
            row.className = "chip-row";
            row.innerHTML =
                '<span>' + escapeHtml(t.tracking_description) + ' — <strong>' + escapeHtml(t.person_charge) + '</strong>' +
                ' (plazo: ' + escapeHtml((t.deadline || "").substring(0, 10)) + ')</span>';
            trackingList.appendChild(row);
        });
    }

    if (trackingForm) {
        trackingForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            ocultarAlerta(detailAlert);

            const r = await postJson("./tracking-crear.php", {
                id_security_events: currentEventId,
                tracking_description: trackingDescription.value.trim(),
                person_charge: trackingPerson.value.trim(),
                commitment_date: trackingCommitment.value,
                deadline: trackingDeadline.value,
            });

            if (!r.success) { mostrarAlerta(detailAlert, r.message || "No se pudo agregar el seguimiento.", "danger"); return; }
            trackingForm.reset();
            abrirDetalle(currentEventId);
        });
    }

    function renderizarEvidencias(items) {
        evidenceList.innerHTML = "";
        if (items.length === 0) {
            evidenceList.innerHTML = '<p class="text-muted mb-0">Sin evidencias todavía.</p>';
            return;
        }
        items.forEach(function (ev) {
            const row = document.createElement("div");
            row.className = "chip-row";
            const link = './evidencia-descargar.php?id_evidence=' + encodeURIComponent(ev.id_evidence);
            row.innerHTML =
                '<a href="' + link + '" target="_blank">' + (ev.file_type === 'imagen' ? '🖼️' : '📄') + ' ' + escapeHtml(ev.original_name) + '</a>' +
                (puedeGestionar ? '<button type="button" title="Eliminar">&times;</button>' : '<span></span>');

            const btn = row.querySelector("button");
            if (btn) {
                btn.addEventListener("click", async function () {
                    if (!window.confirm("¿Eliminar esta evidencia?")) return;
                    const r = await postJson("./evidencia-eliminar.php", { id_evidence: ev.id_evidence });
                    if (!r.success) { mostrarAlerta(detailAlert, r.message, "danger"); return; }
                    abrirDetalle(currentEventId);
                });
            }
            evidenceList.appendChild(row);
        });
    }

    if (evidenceForm) {
        evidenceForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            ocultarAlerta(detailAlert);

            const archivo = evidenceFile.files[0];
            if (!archivo) { mostrarAlerta(detailAlert, "Selecciona un archivo.", "warning"); return; }

            const formData = new FormData();
            formData.append("csrf_token", csrfToken);
            formData.append("id_security_events", currentEventId);
            formData.append("archivo", archivo);

            const r = await llamarApi("./evidencia-subir.php", { method: "POST", body: formData });
            if (!r.success) { mostrarAlerta(detailAlert, r.message || "No se pudo subir la evidencia.", "danger"); return; }

            evidenceForm.reset();
            abrirDetalle(currentEventId);
        });
    }

});
