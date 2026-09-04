/**
 * ==========================================================
 * INDUCCION-MIS.JS
 * SAFETY CONTROL TOWER — Mis Inducciones
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) return;

    const csrfToken = container.dataset.csrfToken;

    const misAlert = document.getElementById("misAlert");
    const misStatus = document.getElementById("misStatus");
    const misLista = document.getElementById("misLista");

    const rendirPanel = document.getElementById("rendirPanel");
    const rendirTitulo = document.getElementById("rendirTitulo");
    const rendirAlert = document.getElementById("rendirAlert");
    const rendirPreguntas = document.getElementById("rendirPreguntas");
    const rendirEnviarBtn = document.getElementById("rendirEnviarBtn");
    const rendirCerrarBtn = document.getElementById("rendirCerrarBtn");

    let currentAsignacionId = null;

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

    /* ========================= LISTADO ========================= */

    async function cargarMisAsignaciones() {
        misStatus.classList.remove("d-none", "alert-danger");
        misStatus.classList.add("alert-info");
        misStatus.textContent = "Cargando tus cursos asignados...";
        misLista.classList.add("d-none");

        const r = await llamarApi("./mis-asignaciones.php");
        if (!r.success) {
            misStatus.classList.remove("alert-info");
            misStatus.classList.add("alert-danger");
            misStatus.textContent = r.message || "No se pudieron cargar tus cursos.";
            return;
        }

        const asignaciones = r.data || [];
        if (asignaciones.length === 0) {
            misStatus.textContent = "No tienes cursos de inducción asignados por el momento.";
            return;
        }

        misStatus.classList.add("d-none");
        misLista.classList.remove("d-none");
        renderizarLista(asignaciones);
    }

    function renderizarLista(asignaciones) {
        misLista.innerHTML = "";

        const estados = {
            1: ["Pendiente", "text-warning"],
            2: ["Aprobado", "text-success"],
            3: ["Reprobado", "text-danger"],
        };

        asignaciones.forEach(function (a) {
            const [texto, clase] = estados[a.state] || ["-", ""];
            const card = document.createElement("div");
            card.className = "curso-card";

            let accionesHtml = "";
            if (a.state === 1 || a.state === "1") {
                if (a.intentos_usados >= a.attempts_allowed) {
                    accionesHtml = '<span class="text-muted">Sin intentos disponibles</span>';
                } else {
                    accionesHtml = '<button type="button" class="btn btn-primary-custom btn-sm" data-action="rendir">Rendir curso</button>';
                }
            } else if ((a.state === 2 || a.state === "2") && a.certificado_disponible) {
                accionesHtml = '<a class="btn btn-outline-custom btn-sm" href="./certificado-descargar.php?id_asignacion=' + encodeURIComponent(a.id_user_test_assigned) + '" target="_blank">Descargar certificado</a>';
            }

            card.innerHTML =
                '<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">' +
                    '<div>' +
                        '<h3 class="h6 mb-1">' + escapeHtml(a.test_name) + '</h3>' +
                        '<p class="text-muted mb-1" style="font-size:0.9rem;">' + escapeHtml(a.test_description) + '</p>' +
                        '<span class="' + clase + '">' + texto + '</span>' +
                        ' · <span class="text-muted" style="font-size:0.85rem;">Vence ' + escapeHtml((a.deadline || "").substring(0, 10)) + '</span>' +
                        ' · <span class="text-muted" style="font-size:0.85rem;">Intentos usados: ' + escapeHtml(a.intentos_usados) + '</span>' +
                    '</div>' +
                    '<div>' + accionesHtml + '</div>' +
                '</div>';

            const btnRendir = card.querySelector('[data-action="rendir"]');
            if (btnRendir) {
                btnRendir.addEventListener("click", function () { abrirRendir(a.id_user_test_assigned); });
            }

            misLista.appendChild(card);
        });
    }

    /* ========================= RENDIR ========================= */

    async function abrirRendir(idAsignacion) {
        currentAsignacionId = idAsignacion;
        ocultarAlerta(rendirAlert);
        rendirPreguntas.innerHTML = '<p class="text-muted">Cargando...</p>';
        rendirPanel.classList.remove("d-none");
        rendirPanel.scrollIntoView({ behavior: "smooth" });

        const r = await llamarApi("./rendir-detalle.php?id_asignacion=" + encodeURIComponent(idAsignacion));
        if (!r.success) {
            mostrarAlerta(rendirAlert, r.message || "No se pudo cargar el curso.", "danger");
            rendirPreguntas.innerHTML = "";
            return;
        }

        rendirTitulo.textContent = r.data.name;
        renderizarPreguntasRendir(r.data.preguntas);
    }

    function renderizarPreguntasRendir(preguntas) {
        rendirPreguntas.innerHTML = "";
        preguntas.forEach(function (p, idx) {
            const bloque = document.createElement("div");
            bloque.className = "rendir-pregunta";
            bloque.dataset.idRel = p.id_rel;
            bloque.dataset.idQuestion = p.id_question;

            let opcionesHtml = "";
            p.opciones.forEach(function (o, oIdx) {
                const inputId = "opt_" + p.id_rel + "_" + oIdx;
                opcionesHtml +=
                    '<div class="form-check">' +
                        '<input class="form-check-input" type="radio" name="pregunta_' + p.id_rel + '" id="' + inputId + '" value="' + o.id_questions_options + '">' +
                        '<label class="form-check-label" for="' + inputId + '">' + escapeHtml(o.text_option) + '</label>' +
                    '</div>';
            });

            bloque.innerHTML =
                '<p><strong>' + (idx + 1) + '.</strong> ' + escapeHtml(p.question) + '</p>' +
                opcionesHtml;

            rendirPreguntas.appendChild(bloque);
        });
    }

    if (rendirEnviarBtn) {
        rendirEnviarBtn.addEventListener("click", async function () {
            ocultarAlerta(rendirAlert);

            const bloques = Array.from(rendirPreguntas.querySelectorAll(".rendir-pregunta"));
            const respuestas = [];

            for (const bloque of bloques) {
                const idRel = bloque.dataset.idRel;
                const seleccionado = bloque.querySelector('input[type="radio"]:checked');
                if (!seleccionado) {
                    mostrarAlerta(rendirAlert, "Debes responder todas las preguntas antes de enviar.", "warning");
                    return;
                }
                respuestas.push({
                    id_rel: parseInt(idRel, 10),
                    id_question: parseInt(bloque.dataset.idQuestion, 10),
                    id_questions_options: parseInt(seleccionado.value, 10),
                });
            }

            rendirEnviarBtn.disabled = true;
            const r = await postJson("./rendir-responder.php", {
                id_asignacion: currentAsignacionId,
                respuestas: respuestas,
            });
            rendirEnviarBtn.disabled = false;

            if (!r.success) {
                mostrarAlerta(rendirAlert, r.message || "No se pudo enviar tu respuesta.", "danger");
                return;
            }

            if (r.data.aprobado) {
                mostrarAlerta(rendirAlert, "¡Aprobaste con " + r.data.porcentaje + "%! Ya puedes descargar tu certificado desde la lista.", "success");
            } else {
                const restantes = r.data.attempts_allowed - r.data.intentos_usados;
                const mensaje = restantes > 0
                    ? "No alcanzaste el puntaje mínimo (" + r.data.porcentaje + "%). Te quedan " + restantes + " intento(s)."
                    : "No alcanzaste el puntaje mínimo (" + r.data.porcentaje + "%) y ya no te quedan intentos disponibles.";
                mostrarAlerta(rendirAlert, mensaje, "warning");
            }

            setTimeout(function () {
                rendirPanel.classList.add("d-none");
                cargarMisAsignaciones();
            }, 2500);
        });
    }

    if (rendirCerrarBtn) {
        rendirCerrarBtn.addEventListener("click", function () {
            rendirPanel.classList.add("d-none");
        });
    }

    cargarMisAsignaciones();

});
