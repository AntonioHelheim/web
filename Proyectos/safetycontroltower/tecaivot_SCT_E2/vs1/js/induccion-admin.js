/**
 * ==========================================================
 * INDUCCION-ADMIN.JS
 * SAFETY CONTROL TOWER — Gestión de Inducción
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) return;

    const csrfToken = container.dataset.csrfToken;
    const isGlobalAdmin = container.dataset.isGlobalAdmin === "1";
    const puedeBanco = container.dataset.puedeBanco === "1";

    const companySelect = document.getElementById("companySelect");

    const courseForm = document.getElementById("courseForm");
    const courseFormMode = document.getElementById("courseFormMode");
    const courseFormTarget = document.getElementById("courseFormTarget");
    const courseFormTitle = document.getElementById("courseFormTitle");
    const courseName = document.getElementById("courseName");
    const courseDescription = document.getElementById("courseDescription");
    const courseAttempts = document.getElementById("courseAttempts");
    const courseApproval = document.getElementById("courseApproval");
    const courseFrom = document.getElementById("courseFrom");
    const courseUntil = document.getElementById("courseUntil");
    const courseSubmitBtn = document.getElementById("courseSubmitBtn");
    const courseCancelEditBtn = document.getElementById("courseCancelEditBtn");
    const courseActionAlert = document.getElementById("courseActionAlert");

    const coursesStatus = document.getElementById("coursesStatus");
    const coursesTableWrapper = document.getElementById("coursesTableWrapper");
    const coursesTableBody = document.getElementById("coursesTableBody");

    const courseDetail = document.getElementById("courseDetail");
    const courseDetailName = document.getElementById("courseDetailName");
    const courseDetailScore = document.getElementById("courseDetailScore");
    const courseDetailCloseBtn = document.getElementById("courseDetailCloseBtn");
    const detailAlert = document.getElementById("detailAlert");

    const courseQuestionsList = document.getElementById("courseQuestionsList");
    const questionSearchInput = document.getElementById("questionSearchInput");
    const questionSearchBtn = document.getElementById("questionSearchBtn");
    const questionSearchResults = document.getElementById("questionSearchResults");
    const newQuestionForm = document.getElementById("newQuestionForm");
    const newQuestionText = document.getElementById("newQuestionText");
    const newQuestionOptions = document.getElementById("newQuestionOptions");
    const addOptionRowBtn = document.getElementById("addOptionRowBtn");
    const newQuestionDifficulty = document.getElementById("newQuestionDifficulty");
    const newQuestionPoints = document.getElementById("newQuestionPoints");

    const courseMaterialsList = document.getElementById("courseMaterialsList");
    const materialForm = document.getElementById("materialForm");
    const materialTitle = document.getElementById("materialTitle");
    const materialType = document.getElementById("materialType");
    const materialContent = document.getElementById("materialContent");

    const courseAssignmentsList = document.getElementById("courseAssignmentsList");
    const assignForm = document.getElementById("assignForm");
    const assignUserSelect = document.getElementById("assignUserSelect");
    const assignDeadline = document.getElementById("assignDeadline");

    let currentCompanyId = null;
    let currentCourseId = null;
    let currentCourseData = null;

    /* ========================= HELPERS ========================= */

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

    /* ========================= EMPRESA ========================= */

    async function cargarEmpresasDisponibles() {
        if (!companySelect) return;
        const r = await llamarApi("./empresas-disponibles.php");
        if (!r.success) { mostrarAlerta(courseActionAlert, r.message, "danger"); return; }
        (r.data || []).forEach(function (e) {
            const opt = document.createElement("option");
            opt.value = e.id_company;
            opt.textContent = e.razon_social;
            companySelect.appendChild(opt);
        });
    }

    if (companySelect) {
        companySelect.addEventListener("change", function () {
            currentCompanyId = companySelect.value ? parseInt(companySelect.value, 10) : null;
            cerrarDetalle();
            if (currentCompanyId) {
                cargarCursos();
            } else {
                coursesTableWrapper.classList.add("d-none");
                coursesStatus.classList.remove("d-none");
                coursesStatus.textContent = "Selecciona una empresa para ver sus cursos.";
            }
        });
        cargarEmpresasDisponibles();
    } else {
        cargarCursos();
    }

    /* ========================= CURSOS ========================= */

    async function cargarCursos() {
        coursesStatus.classList.remove("d-none", "alert-danger");
        coursesStatus.classList.add("alert-info");
        coursesStatus.textContent = "Cargando cursos...";
        coursesTableWrapper.classList.add("d-none");

        const url = isGlobalAdmin && currentCompanyId
            ? "./cursos-listar.php?id_company=" + encodeURIComponent(currentCompanyId)
            : "./cursos-listar.php";
        const r = await llamarApi(url);

        if (!r.success) {
            coursesStatus.classList.remove("alert-info");
            coursesStatus.classList.add("alert-danger");
            coursesStatus.textContent = r.message || "No se pudieron cargar los cursos.";
            return;
        }
        const cursos = r.data || [];
        if (cursos.length === 0) {
            coursesStatus.classList.remove("alert-danger");
            coursesStatus.classList.add("alert-info");
            coursesStatus.textContent = "Todavía no hay cursos registrados.";
            coursesTableWrapper.classList.add("d-none");
            return;
        }
        coursesStatus.classList.add("d-none");
        coursesTableWrapper.classList.remove("d-none");
        renderizarCursos(cursos);
    }

    function renderizarCursos(cursos) {
        coursesTableBody.innerHTML = "";
        cursos.forEach(function (curso) {
            const fila = document.createElement("tr");
            const activo = String(curso.state) === "1";
            fila.innerHTML =
                '<td>' + escapeHtml(curso.name) + '</td>' +
                '<td>' + escapeHtml(curso.approval_percentage) + '%</td>' +
                '<td>' + escapeHtml(curso.attempts_allowed) + '</td>' +
                '<td>' + (curso.preguntas_count != null ? escapeHtml(curso.preguntas_count) : '-') + '</td>' +
                '<td class="' + (activo ? 'text-success' : 'text-muted') + '">' + (activo ? 'Activo' : 'Inactivo') + '</td>' +
                '<td class="form-actions">' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="detail">Gestionar</button>' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="edit">Editar</button>' +
                    '<button type="button" class="btn btn-outline-custom btn-sm" data-action="toggle">' + (activo ? 'Dar de baja' : 'Reactivar') + '</button>' +
                '</td>';

            fila.querySelector('[data-action="detail"]').addEventListener("click", function () { abrirDetalle(curso); });
            fila.querySelector('[data-action="edit"]').addEventListener("click", function () { iniciarEdicionCurso(curso); });
            fila.querySelector('[data-action="toggle"]').addEventListener("click", function () { cambiarEstadoCurso(curso); });

            coursesTableBody.appendChild(fila);
        });
    }

    function iniciarEdicionCurso(curso) {
        if (!courseForm) return;
        courseFormMode.value = "edit";
        courseFormTarget.value = curso.id_test;
        courseName.value = curso.name;
        courseDescription.value = curso.description;
        courseAttempts.value = curso.attempts_allowed;
        courseApproval.value = curso.approval_percentage;
        courseFrom.value = (curso.effective_date_from || "").substring(0, 10);
        courseUntil.value = (curso.effective_date_until || "").substring(0, 10);
        courseFormTitle.textContent = "Editar curso";
        courseSubmitBtn.textContent = "Guardar cambios";
        courseCancelEditBtn.classList.remove("d-none");
        courseForm.scrollIntoView({ behavior: "smooth" });
    }

    function cancelarEdicionCurso() {
        if (!courseForm) return;
        courseForm.reset();
        courseFormMode.value = "create";
        courseFormTarget.value = "";
        courseFormTitle.textContent = "Nuevo curso de inducción";
        courseSubmitBtn.textContent = "Guardar curso";
        courseCancelEditBtn.classList.add("d-none");
    }
    if (courseCancelEditBtn) courseCancelEditBtn.addEventListener("click", cancelarEdicionCurso);

    if (courseForm) {
        courseForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            ocultarAlerta(courseActionAlert);

            const modo = courseFormMode.value;
            const datos = {
                name: courseName.value.trim(),
                description: courseDescription.value.trim(),
                attempts_allowed: courseAttempts.value,
                approval_percentage: courseApproval.value,
                effective_date_from: courseFrom.value,
                effective_date_until: courseUntil.value,
            };

            let url = "./cursos-crear.php";
            if (modo === "edit") {
                url = "./cursos-editar.php";
                datos.id_test = courseFormTarget.value;
            } else if (isGlobalAdmin) {
                if (!currentCompanyId) {
                    mostrarAlerta(courseActionAlert, "Selecciona una empresa antes de crear un curso.", "danger");
                    return;
                }
                datos.id_company = currentCompanyId;
            }

            courseSubmitBtn.disabled = true;
            const r = await postJson(url, datos);
            courseSubmitBtn.disabled = false;

            if (!r.success) { mostrarAlerta(courseActionAlert, r.message || "No se pudo guardar el curso.", "danger"); return; }
            mostrarAlerta(courseActionAlert, r.message || "Curso guardado.", "success");
            cancelarEdicionCurso();
            cargarCursos();
        });
    }

    async function cambiarEstadoCurso(curso) {
        const nuevoEstado = String(curso.state) === "1" ? 0 : 1;
        if (!window.confirm(nuevoEstado === 1 ? "¿Reactivar este curso?" : "¿Dar de baja este curso?")) return;
        const r = await postJson("./cursos-cambiar-estado.php", { id_test: curso.id_test, state: nuevoEstado });
        if (!r.success) { mostrarAlerta(courseActionAlert, r.message, "danger"); return; }
        mostrarAlerta(courseActionAlert, r.message, "success");
        cargarCursos();
    }

    /* ========================= DETALLE DE CURSO ========================= */

    function cerrarDetalle() {
        currentCourseId = null;
        currentCourseData = null;
        courseDetail.classList.remove("activo");
    }
    if (courseDetailCloseBtn) courseDetailCloseBtn.addEventListener("click", cerrarDetalle);

    async function abrirDetalle(curso) {
        currentCourseId = curso.id_test;
        courseDetailName.textContent = curso.name;
        courseDetail.classList.add("activo");
        ocultarAlerta(detailAlert);
        questionSearchResults.innerHTML = "";
        if (questionSearchInput) questionSearchInput.value = "";

        await cargarDetalleCurso();
        if (assignUserSelect) cargarUsuariosDisponibles(curso.id_company || currentCompanyId);

        courseDetail.scrollIntoView({ behavior: "smooth" });
    }

    async function cargarDetalleCurso() {
        const r = await llamarApi("./cursos-detalle.php?id=" + encodeURIComponent(currentCourseId));
        if (!r.success) { mostrarAlerta(detailAlert, r.message || "No se pudo cargar el curso.", "danger"); return; }

        currentCourseData = r.data;
        renderizarPreguntasCurso(r.data.preguntas || [], r.data.puntaje_maximo || 0);
        renderizarMateriales(r.data.materiales || []);
        cargarAsignaciones();
    }

    function renderizarPreguntasCurso(preguntas, puntajeMaximo) {
        courseDetailScore.textContent = "(puntaje máximo: " + puntajeMaximo + ")";
        courseQuestionsList.innerHTML = "";

        if (preguntas.length === 0) {
            courseQuestionsList.innerHTML = '<p class="text-muted mb-0">Este curso todavía no tiene preguntas.</p>';
            return;
        }

        preguntas.forEach(function (p) {
            const chip = document.createElement("div");
            chip.className = "chip-pregunta";
            chip.innerHTML =
                '<span>' + escapeHtml(p.question) + ' <strong>(' + escapeHtml(p.assigned_score) + ' pts)</strong></span>' +
                '<button type="button" title="Quitar del curso">&times;</button>';
            chip.querySelector("button").addEventListener("click", function () { quitarPreguntaCurso(p.id_rel); });
            courseQuestionsList.appendChild(chip);
        });
    }

    async function quitarPreguntaCurso(idRel) {
        if (!window.confirm("¿Quitar esta pregunta del curso?")) return;
        const r = await postJson("./curso-preguntas-quitar.php", { id_rel: idRel });
        if (!r.success) { mostrarAlerta(detailAlert, r.message, "danger"); return; }
        cargarDetalleCurso();
    }

    if (questionSearchBtn) {
        questionSearchBtn.addEventListener("click", async function () {
            const termino = (questionSearchInput.value || "").trim();
            const r = await llamarApi("./preguntas-listar.php?q=" + encodeURIComponent(termino));
            questionSearchResults.innerHTML = "";
            if (!r.success) { mostrarAlerta(detailAlert, r.message, "danger"); return; }

            const encontradas = (r.data || []).filter(function (p) {
                return !(currentCourseData && currentCourseData.preguntas || []).some(function (cp) { return cp.id_question == p.id_questions; });
            });

            if (encontradas.length === 0) {
                questionSearchResults.innerHTML = '<p class="text-muted mb-0 mt-2">Sin resultados nuevos.</p>';
                return;
            }

            encontradas.forEach(function (p) {
                const row = document.createElement("div");
                row.className = "d-flex align-items-center gap-2 mb-2";
                row.innerHTML =
                    '<span class="flex-grow-1">' + escapeHtml(p.question) + '</span>' +
                    '<input type="number" class="form-control form-control-sm" style="width:90px" placeholder="Puntaje" value="' + escapeHtml(p.points) + '">' +
                    '<button type="button" class="btn btn-outline-custom btn-sm">Agregar</button>';

                const input = row.querySelector("input");
                row.querySelector("button").addEventListener("click", async function () {
                    const puntaje = parseInt(input.value, 10);
                    if (!puntaje || puntaje < 1) { mostrarAlerta(detailAlert, "Ingresa un puntaje válido.", "warning"); return; }
                    const rr = await postJson("./curso-preguntas-agregar.php", {
                        id_test: currentCourseId, id_question: p.id_questions, assigned_score: puntaje,
                    });
                    if (!rr.success) { mostrarAlerta(detailAlert, rr.message, "danger"); return; }
                    questionSearchResults.innerHTML = "";
                    cargarDetalleCurso();
                });

                questionSearchResults.appendChild(row);
            });
        });
    }

    if (addOptionRowBtn) {
        addOptionRowBtn.addEventListener("click", function () {
            const count = newQuestionOptions.querySelectorAll(".option-row").length;
            const row = document.createElement("div");
            row.className = "row g-2 mb-2 option-row";
            row.innerHTML =
                '<div class="col-8"><input type="text" class="form-control option-text" placeholder="Alternativa ' + (count + 1) + '"></div>' +
                '<div class="col-4 form-check mt-2"><input type="radio" name="correctOption" class="form-check-input option-correct" value="' + count + '"> <label class="form-check-label">Correcta</label></div>';
            newQuestionOptions.appendChild(row);
        });
    }

    if (newQuestionForm) {
        newQuestionForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            ocultarAlerta(detailAlert);

            const filas = Array.from(newQuestionOptions.querySelectorAll(".option-row"));
            const opciones = filas.map(function (fila, idx) {
                return {
                    text_option: fila.querySelector(".option-text").value.trim(),
                    is_it_co: fila.querySelector(".option-correct").checked,
                };
            });

            if (opciones.some(function (o) { return !o.text_option; })) {
                mostrarAlerta(detailAlert, "Todas las alternativas deben tener texto.", "danger");
                return;
            }

            const r = await postJson("./preguntas-crear.php", {
                question: newQuestionText.value.trim(),
                difficulty: newQuestionDifficulty.value,
                points: newQuestionPoints.value,
                opciones: opciones,
            });

            if (!r.success) { mostrarAlerta(detailAlert, r.message || "No se pudo crear la pregunta.", "danger"); return; }

            mostrarAlerta(detailAlert, "Pregunta creada. Búscala arriba para agregarla al curso.", "success");
            newQuestionForm.reset();
        });
    }

    /* ========================= MATERIALES ========================= */

    function renderizarMateriales(materiales) {
        courseMaterialsList.innerHTML = "";
        if (materiales.length === 0) {
            courseMaterialsList.innerHTML = '<p class="text-muted mb-0">Sin materiales todavía.</p>';
            return;
        }
        materiales.forEach(function (m) {
            const row = document.createElement("div");
            row.className = "chip-pregunta";
            const detalle = m.material_type === "texto" ? m.content_text : m.file_path;
            row.innerHTML =
                '<span><strong>' + escapeHtml(m.title) + '</strong> (' + escapeHtml(m.material_type) + ') — ' + escapeHtml((detalle || "").substring(0, 60)) + '</span>' +
                '<button type="button" title="Eliminar">&times;</button>';
            row.querySelector("button").addEventListener("click", async function () {
                if (!window.confirm("¿Eliminar este material?")) return;
                const r = await postJson("./materiales-eliminar.php", { id_material: m.id_material });
                if (!r.success) { mostrarAlerta(detailAlert, r.message, "danger"); return; }
                cargarDetalleCurso();
            });
            courseMaterialsList.appendChild(row);
        });
    }

    if (materialForm) {
        materialForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            ocultarAlerta(detailAlert);

            const tipo = materialType.value;
            const contenido = materialContent.value.trim();
            const datos = {
                id_test: currentCourseId,
                title: materialTitle.value.trim(),
                material_type: tipo,
                content_text: tipo === "texto" ? contenido : "",
                file_path: tipo !== "texto" ? contenido : "",
            };

            const r = await postJson("./materiales-crear.php", datos);
            if (!r.success) { mostrarAlerta(detailAlert, r.message || "No se pudo agregar el material.", "danger"); return; }
            materialForm.reset();
            cargarDetalleCurso();
        });
    }

    /* ========================= ASIGNACIONES ========================= */

    async function cargarUsuariosDisponibles(idCompany) {
        assignUserSelect.innerHTML = '<option value="">Selecciona un usuario...</option>';
        const url = isGlobalAdmin ? "./usuarios-disponibles.php?id_company=" + encodeURIComponent(idCompany) : "./usuarios-disponibles.php";
        const r = await llamarApi(url);
        if (!r.success) return;
        (r.data || []).forEach(function (u) {
            const opt = document.createElement("option");
            opt.value = u.id_users;
            opt.textContent = u.name + " " + u.lastname + " (" + u.id_users + ")";
            assignUserSelect.appendChild(opt);
        });
    }

    async function cargarAsignaciones() {
        const r = await llamarApi("./asignaciones-listar.php?id_test=" + encodeURIComponent(currentCourseId));
        courseAssignmentsList.innerHTML = "";
        if (!r.success) { mostrarAlerta(detailAlert, r.message, "danger"); return; }

        const asignaciones = r.data || [];
        if (asignaciones.length === 0) {
            courseAssignmentsList.innerHTML = '<p class="text-muted mb-0">Sin asignaciones todavía.</p>';
            return;
        }

        const estados = { 1: ["Pendiente", "badge-estado-1"], 2: ["Aprobado", "badge-estado-2"], 3: ["Reprobado", "badge-estado-3"] };
        asignaciones.forEach(function (a) {
            const [texto, clase] = estados[a.state] || ["-", ""];
            const row = document.createElement("div");
            row.className = "chip-pregunta";
            row.innerHTML =
                '<span>' + escapeHtml(a.name) + ' ' + escapeHtml(a.lastname) + ' — vence ' + escapeHtml((a.deadline || "").substring(0, 10)) + '</span>' +
                '<span class="' + clase + '">' + texto + '</span>';
            courseAssignmentsList.appendChild(row);
        });
    }

    if (assignForm) {
        assignForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            ocultarAlerta(detailAlert);

            if (!assignUserSelect.value) { mostrarAlerta(detailAlert, "Selecciona un usuario.", "warning"); return; }

            const r = await postJson("./asignaciones-crear.php", {
                id_test: currentCourseId,
                id_users: assignUserSelect.value,
                deadline: assignDeadline.value,
            });

            if (!r.success) { mostrarAlerta(detailAlert, r.message || "No se pudo asignar el curso.", "danger"); return; }
            mostrarAlerta(detailAlert, "Curso asignado correctamente.", "success");
            assignForm.reset();
            cargarAsignaciones();
        });
    }

});
