/**
 * ==========================================================
 * DASHBOARD.JS
 * SAFETY CONTROL TOWER — Panel General
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const container = document.querySelector(".container[data-is-global-admin]");
    if (!container) return;

    const isGlobalAdmin = container.dataset.isGlobalAdmin === "1";

    const companySelect = document.getElementById("companySelect");
    const projectFilter = document.getElementById("projectFilter");

    const dashAlert = document.getElementById("dashAlert");
    const dashStatus = document.getElementById("dashStatus");
    const dashContent = document.getElementById("dashContent");
    const statCardsRow = document.getElementById("statCardsRow");
    const barsEstado = document.getElementById("barsEstado");
    const barsCriticidad = document.getElementById("barsCriticidad");
    const barsInduccion = document.getElementById("barsInduccion");
    const tasaCumplimiento = document.getElementById("tasaCumplimiento");

    let currentCompanyId = null;

    function mostrarAlerta(el, msg, variante) {
        if (!el) return;
        el.textContent = msg;
        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-info", "alert-warning");
        el.classList.add("alert-" + (variante || "danger"));
    }
    function escapeHtml(t) {
        const d = document.createElement("div");
        d.textContent = t == null ? "" : String(t);
        return d.innerHTML;
    }
    async function llamarApi(url) {
        const r = await fetch(url);
        try { return await r.json(); }
        catch (e) { return { success: false, message: "Respuesta inválida del servidor." }; }
    }

    /* ========================= EMPRESA Y PROYECTOS ========================= */

    async function cargarEmpresasDisponibles() {
        if (!companySelect) return;
        const r = await llamarApi("./empresas-disponibles.php");
        if (!r.success) { mostrarAlerta(dashAlert, r.message, "danger"); return; }
        (r.data || []).forEach(function (e) {
            const opt = document.createElement("option");
            opt.value = e.id_company;
            opt.textContent = e.razon_social;
            companySelect.appendChild(opt);
        });
    }

    async function cargarProyectosFiltro() {
        projectFilter.innerHTML = '<option value="">Todos los proyectos</option>';
        const params = isGlobalAdmin && currentCompanyId ? "?id_company=" + encodeURIComponent(currentCompanyId) : "";
        const r = await llamarApi("./proyectos-disponibles.php" + params);
        if (!r.success) return;
        (r.data || []).forEach(function (p) {
            const opt = document.createElement("option");
            opt.value = p.id_project;
            opt.textContent = p.name;
            projectFilter.appendChild(opt);
        });
    }

    if (companySelect) {
        companySelect.addEventListener("change", function () {
            currentCompanyId = companySelect.value ? parseInt(companySelect.value, 10) : null;
            if (currentCompanyId) {
                cargarProyectosFiltro();
                cargarIndicadores();
            } else {
                dashContent.classList.add("d-none");
                dashStatus.classList.remove("d-none");
                dashStatus.textContent = "Selecciona una empresa para ver sus indicadores.";
            }
        });
        cargarEmpresasDisponibles();
    } else {
        cargarProyectosFiltro();
        cargarIndicadores();
    }

    projectFilter.addEventListener("change", cargarIndicadores);

    /* ========================= INDICADORES ========================= */

    async function cargarIndicadores() {
        if (isGlobalAdmin && !currentCompanyId) return;

        dashStatus.classList.remove("d-none", "alert-danger");
        dashStatus.classList.add("alert-info");
        dashStatus.textContent = "Cargando indicadores...";
        dashContent.classList.add("d-none");

        const params = new URLSearchParams();
        if (isGlobalAdmin && currentCompanyId) params.set("id_company", currentCompanyId);
        if (projectFilter.value) params.set("id_project", projectFilter.value);

        const r = await llamarApi("./indicadores.php?" + params.toString());

        if (!r.success) {
            dashStatus.classList.remove("alert-info");
            dashStatus.classList.add("alert-danger");
            dashStatus.textContent = r.message || "No se pudieron cargar los indicadores.";
            return;
        }

        dashStatus.classList.add("d-none");
        dashContent.classList.remove("d-none");
        renderizarTodo(r.data);
    }

    function renderizarTodo(datos) {
        renderizarTarjetas(datos);
        renderizarBarras(barsEstado, [
            ["Abierto", datos.eventos.por_estado.abierto, "fill-abierto"],
            ["En proceso", datos.eventos.por_estado.en_proceso, "fill-en_proceso"],
            ["Cerrado", datos.eventos.por_estado.cerrado, "fill-cerrado"],
        ], datos.eventos.total);

        renderizarBarras(barsCriticidad, [
            ["Baja", datos.eventos.por_criticidad.baja, "fill-baja"],
            ["Media", datos.eventos.por_criticidad.media, "fill-media"],
            ["Alta", datos.eventos.por_criticidad.alta, "fill-alta"],
            ["Crítica", datos.eventos.por_criticidad.critica, "fill-critica"],
        ], datos.eventos.total);

        renderizarBarras(barsInduccion, [
            ["Aprobados", datos.induccion.aprobados, "fill-aprobados"],
            ["Pendientes", datos.induccion.pendientes, "fill-pendientes"],
            ["Reprobados", datos.induccion.reprobados, "fill-reprobados"],
        ], datos.induccion.total_asignadas);

        tasaCumplimiento.textContent = datos.induccion.tasa_cumplimiento !== null
            ? "Tasa de aprobación (sobre asignaciones ya finalizadas): " + datos.induccion.tasa_cumplimiento + "%"
            : "Todavía no hay asignaciones finalizadas para calcular una tasa de aprobación.";
    }

    function renderizarTarjetas(datos) {
        const tarjetas = [];

        if (datos.totales.empresas != null) {
            tarjetas.push(["Empresas registradas", datos.totales.empresas]);
        }
        tarjetas.push(["Proyectos activos", datos.totales.proyectos]);
        tarjetas.push(["Centros/Sedes", datos.totales.centros]);
        tarjetas.push(["Trabajadores", datos.totales.trabajadores]);
        tarjetas.push(["Inducciones aprobadas", datos.induccion.aprobados]);
        tarjetas.push(["Eventos registrados", datos.eventos.total]);

        statCardsRow.innerHTML = "";
        tarjetas.forEach(function ([label, valor]) {
            const col = document.createElement("div");
            col.className = "col-6 col-md-4 col-lg-2";
            col.innerHTML =
                '<div class="stat-card">' +
                    '<div class="stat-number">' + escapeHtml(valor) + '</div>' +
                    '<div class="stat-label">' + escapeHtml(label) + '</div>' +
                '</div>';
            statCardsRow.appendChild(col);
        });
    }

    function renderizarBarras(contenedor, filas, total) {
        contenedor.innerHTML = "";
        if (!total) {
            contenedor.innerHTML = '<p class="text-muted mb-0" style="font-size:0.85rem;">Sin datos todavía.</p>';
            return;
        }
        filas.forEach(function ([label, valor, claseFill]) {
            const porcentaje = total > 0 ? Math.round((valor / total) * 100) : 0;
            const fila = document.createElement("div");
            fila.className = "bar-row";
            fila.innerHTML =
                '<span class="bar-label">' + escapeHtml(label) + '</span>' +
                '<span class="bar-track"><span class="bar-fill ' + claseFill + '" style="width:' + porcentaje + '%"></span></span>' +
                '<span class="bar-value">' + escapeHtml(valor) + '</span>';
            contenedor.appendChild(fila);
        });
    }

});
