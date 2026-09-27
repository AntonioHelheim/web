/**
 * ==========================================================
 * INDUCCION-MIS.JS
 * SAFETY CONTROL TOWER — Mis Inducciones
 * P9 v35: evaluación guiada paso a paso.
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector(".container[data-csrf-token]");
    if (!container) return;

    function notifyActivityUpdated() {
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({type:'sct-activity-updated'}, window.location.origin);
            } else {
                window.dispatchEvent(new CustomEvent('sct:activity-updated'));
            }
        } catch (_) {}
    }

    let I18N = {};
    try { I18N = JSON.parse((document.getElementById("myInductionI18n") || {}).textContent || "{}"); } catch (_) {}
    const S = (key, fallback) => I18N[key] || fallback || key;

    const csrfToken = container.dataset.csrfToken;
    const misAlert = document.getElementById("misAlert");
    const misStatus = document.getElementById("misStatus");
    const misLista = document.getElementById("misLista");
    const misInductionFilter = document.getElementById("misInductionFilter");
    const myInductionBrowse = document.getElementById("myInductionBrowse");

    const rendirPanel = document.getElementById("rendirPanel");
    const rendirTitulo = document.getElementById("rendirTitulo");
    const rendirAlert = document.getElementById("rendirAlert");
    const rendirPreguntas = document.getElementById("rendirPreguntas");
    const rendirEnviarBtn = document.getElementById("rendirEnviarBtn");
    const rendirGuardarBtn = document.getElementById("rendirGuardarBtn");
    const rendirCerrarBtn = document.getElementById("rendirCerrarBtn");
    const rendirAnteriorBtn = document.getElementById("rendirAnteriorBtn");
    const rendirSiguienteBtn = document.getElementById("rendirSiguienteBtn");
    const rendirPasoTexto = document.getElementById("rendirPasoTexto");
    const rendirRespondidasTexto = document.getElementById("rendirRespondidasTexto");
    const rendirProgresoBar = document.getElementById("rendirProgresoBar");
    const personalShell = document.querySelector(".induction-personal-shell");
    const activitySession = window.SCTActivitySession ? window.SCTActivitySession.create({ root: container, type: "induction", shell: personalShell, panel: rendirPanel }) : null;

    let currentAsignacionId = null;
    let currentQuestionIndex = 0;
    let evaluationLocked = false;
    let currentOverdue = false;
    let assignmentsById = {};
    let directStartHandled = false;
    const requestedStartId = parseInt(new URLSearchParams(window.location.search).get("start") || "0", 10);

    function setBrowseVisible(visible) {
        if (!myInductionBrowse) return;
        if (visible) {
            myInductionBrowse.classList.remove("d-none");
            myInductionBrowse.removeAttribute("hidden");
            myInductionBrowse.removeAttribute("aria-hidden");
            try { myInductionBrowse.inert = false; } catch (_) {}
        } else {
            myInductionBrowse.classList.add("d-none");
            myInductionBrowse.setAttribute("hidden", "");
            myInductionBrowse.setAttribute("aria-hidden", "true");
            try { myInductionBrowse.inert = true; } catch (_) {}
        }
    }

    if (requestedStartId > 0) setBrowseVisible(false);

    function mostrarAlerta(el, msg, variante) {
        if (!el) return;
        el.textContent = msg;
        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-info", "alert-warning");
        el.classList.add("alert-" + (variante || "danger"));
    }

    function ocultarAlerta(el) {
        if (el) el.classList.add("d-none");
    }

    function escapeHtml(value) {
        const d = document.createElement("div");
        d.textContent = value == null ? "" : String(value);
        return d.innerHTML;
    }

    function safeExternalUrl(value) {
        if (!value) return "";
        try {
            const parsed = new URL(String(value), window.location.href);
            return ["http:", "https:"].includes(parsed.protocol) ? parsed.href : "";
        } catch (_) {
            return "";
        }
    }

    function formatDate(value) {
        const raw = String(value || "").substring(0, 10);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw || "-";
        const parts = raw.split("-").map(Number);
        try {
            return new Intl.DateTimeFormat(document.documentElement.lang || "es", {
                day: "2-digit",
                month: "short",
                year: "numeric",
            }).format(new Date(parts[0], parts[1] - 1, parts[2]));
        } catch (_) {
            return raw;
        }
    }

    function isOverdue(value) {
        const raw = String(value || "").substring(0, 10);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(raw)) return false;
        const end = new Date(raw + "T23:59:59");
        return !Number.isNaN(end.getTime()) && end.getTime() < Date.now();
    }

    async function llamarApi(url, opciones) {
        const r = await fetch(url, opciones || {});
        try {
            return await r.json();
        } catch (_) {
            return { success: false, message: S("my_induction_error_response", "Respuesta inválida del servidor.") };
        }
    }

    function postJson(url, datos) {
        return llamarApi(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(Object.assign({ csrf_token: csrfToken }, datos)),
        });
    }

    const requestedStatus = String(new URLSearchParams(window.location.search).get("status") || "all");
    let activeInductionFilter = ["all", "overdue", "in_progress", "approved", "failed"].indexOf(requestedStatus) !== -1
        ? requestedStatus
        : "all";

    function refreshInductionFilter() {
        if (!misInductionFilter || !misLista) return;
        const cards = Array.prototype.slice.call(misLista.querySelectorAll(":scope > [data-induction-status]"));
        const counts = { all: cards.length, overdue: 0, in_progress: 0, approved: 0, failed: 0 };
        cards.forEach(function (card) {
            const status = card.dataset.inductionStatus || "in_progress";
            if (Object.prototype.hasOwnProperty.call(counts, status)) counts[status] += 1;
            card.hidden = activeInductionFilter !== "all" && status !== activeInductionFilter;
        });
        misInductionFilter.querySelectorAll("[data-filter-count]").forEach(function (node) {
            const key = node.dataset.filterCount || "all";
            node.textContent = String(counts[key] || 0);
        });
        misInductionFilter.querySelectorAll("[data-induction-filter]").forEach(function (button) {
            const selected = (button.dataset.inductionFilter || "all") === activeInductionFilter;
            button.classList.toggle("is-active", selected);
            button.setAttribute("aria-pressed", selected ? "true" : "false");
        });
        misInductionFilter.classList.toggle("d-none", cards.length === 0);
    }

    if (misInductionFilter) {
        misInductionFilter.querySelectorAll("[data-induction-filter]").forEach(function (button) {
            button.addEventListener("click", function () {
                activeInductionFilter = button.dataset.inductionFilter || "all";
                refreshInductionFilter();
            });
        });
    }

    async function cargarMisAsignaciones() {
        misStatus.classList.remove("d-none", "alert-danger");
        misStatus.classList.add("alert-info");
        misStatus.textContent = S("my_induction_loading", "Cargando tus cursos asignados...");
        misLista.classList.add("d-none");
        if (misInductionFilter) misInductionFilter.classList.add("d-none");

        const r = await llamarApi("./mis-asignaciones.php");
        if (!r.success) {
            misStatus.classList.remove("alert-info");
            misStatus.classList.add("alert-danger");
            if (!(requestedStartId > 0 && directStartHandled)) {
                if (requestedStartId > 0 && activitySession) activitySession.exit(personalShell, rendirPanel);
                else if (requestedStartId > 0) document.body.classList.remove("sct-activity-mode");
                setBrowseVisible(true);
            }
            misStatus.textContent = r.message || S("my_induction_load_error", "No se pudieron cargar tus cursos.");
            return;
        }

        const asignaciones = r.data || [];
        if (asignaciones.length === 0) {
            if (!(requestedStartId > 0 && directStartHandled)) {
                if (requestedStartId > 0 && activitySession) activitySession.exit(personalShell, rendirPanel);
                else if (requestedStartId > 0) document.body.classList.remove("sct-activity-mode");
                setBrowseVisible(true);
            }
            misStatus.textContent = S("my_induction_empty", "No tienes cursos de inducción asignados por el momento.");
            return;
        }

        misStatus.classList.add("d-none");
        misLista.classList.remove("d-none");
        if (requestedStartId <= 0) setBrowseVisible(true);
        assignmentsById = {};
        asignaciones.forEach(function (row) { assignmentsById[String(row.id_user_test_assigned)] = row; });
        renderizarLista(asignaciones);
        if (requestedStartId > 0 && !directStartHandled) {
            directStartHandled = true;
            if (assignmentsById[String(requestedStartId)]) {
                abrirRendir(requestedStartId);
            } else {
                setBrowseVisible(true);
                mostrarAlerta(misAlert, S('activity_unavailable_text', 'Esta actividad ya no está disponible.'), 'warning');
            }
        }
        return asignaciones;
    }

    function renderizarLista(asignaciones) {
        misLista.innerHTML = "";

        asignaciones.forEach(function (a) {
            const stateKey = Number(a.state);
            const usados = Number(a.intentos_usados || 0);
            const permitidos = Number(a.attempts_allowed || 0);
            const overdue = stateKey === 1 && isOverdue(a.deadline);
            let inductionStatus = "in_progress";
            let estado = [S("my_induction_status_in_progress", "En curso"), "pending", "bi-play-circle"];
            if (overdue) {
                inductionStatus = "overdue";
                estado = [S("my_induction_status_overdue", "Vencida"), "overdue", "bi-clock-history"];
            } else if (stateKey === 2) {
                inductionStatus = "approved";
                estado = [S("my_induction_status_completed_approved", "Completada aprobada"), "approved", "bi-check-circle"];
            } else if (stateKey === 3) {
                inductionStatus = "failed";
                estado = [S("my_induction_status_completed_failed", "Completada reprobada"), "failed", "bi-x-circle"];
            }

            const card = document.createElement("article");
            card.className = "curso-card";
            card.dataset.state = String(stateKey || "");
            card.dataset.inductionStatus = inductionStatus;

            let accionesHtml = "";
            if (stateKey === 1) {
                if (usados >= permitidos) {
                    accionesHtml = '<span class="induction-pill"><i class="bi bi-lock"></i>' +
                        escapeHtml(S("my_induction_no_attempts", "Sin intentos disponibles")) + '</span>';
                } else {
                    accionesHtml = '<button type="button" class="btn btn-primary-custom" data-action="rendir">' +
                        '<i class="bi bi-play-circle me-1"></i>' +
                        escapeHtml(S("my_induction_take_course", "Comenzar evaluación")) + '</button>';
                }
            } else if (stateKey === 2 && a.certificado_disponible) {
                accionesHtml = '<a class="btn btn-outline-custom" href="./certificado-descargar.php?id_asignacion=' +
                    encodeURIComponent(a.id_user_test_assigned) + '" target="_blank" rel="noopener">' +
                    '<i class="bi bi-download me-1"></i>' +
                    escapeHtml(S("my_induction_download_certificate", "Descargar certificado")) + '</a>';
            }

            const attemptsText = escapeHtml(S("my_induction_attempts_used_prefix", "Intentos usados")) + ': ' +
                escapeHtml(usados) + '/' + escapeHtml(permitidos);

            card.innerHTML =
                '<div class="induction-course-card__layout">' +
                    '<div>' +
                        '<h3 class="induction-course-card__title">' + escapeHtml(a.test_name) + '</h3>' +
                        '<p class="induction-course-card__description">' + escapeHtml(a.test_description || "") + '</p>' +
                        '<div class="induction-course-card__meta">' +
                            '<span class="induction-pill induction-pill--' + estado[1] + '"><i class="bi ' + estado[2] + '"></i>' + escapeHtml(estado[0]) + '</span>' +
                            '<span class="induction-pill"><i class="bi bi-calendar-event"></i>' + escapeHtml(S("my_induction_due_prefix", "Vence")) + ' ' + escapeHtml(formatDate(a.deadline)) + '</span>' +
                            '<span class="induction-pill"><i class="bi bi-arrow-repeat"></i>' + attemptsText + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="induction-course-card__actions">' + accionesHtml + '</div>' +
                '</div>';

            const btnRendir = card.querySelector('[data-action="rendir"]');
            if (btnRendir) {
                btnRendir.addEventListener("click", function () {
                    abrirRendir(a.id_user_test_assigned);
                });
            }

            misLista.appendChild(card);
        });
        refreshInductionFilter();
    }

    async function abrirRendir(idAsignacion) {
        currentAsignacionId = parseInt(idAsignacion, 10);
        setBrowseVisible(false);
        rendirPanel.classList.remove("d-none");
        rendirPanel.removeAttribute("hidden");
        rendirPanel.setAttribute("aria-hidden", "false");
        if (activitySession) {
            activitySession.setId(idAsignacion);
            activitySession.enter(personalShell, rendirPanel);
        } else {
            document.body.classList.add("sct-activity-mode");
            if (personalShell) personalShell.classList.add("is-direct-activity");
        }
        currentQuestionIndex = 0;
        evaluationLocked = false;
        ocultarAlerta(rendirAlert);
        const assignment = assignmentsById[String(idAsignacion)] || null;
        currentOverdue = !!(assignment && isOverdue(assignment.deadline));
        if (currentOverdue) {
            mostrarAlerta(rendirAlert, S("my_induction_overdue_warning", "El plazo de este curso ya terminó. Puedes realizarlo igualmente."), "warning");
        }
        if (rendirEnviarBtn) rendirEnviarBtn.textContent = currentOverdue ? S("activity_submit_late", "Enviar fuera de plazo") : S("my_induction_send_answers", "Enviar respuestas");
        rendirPanel.classList.remove("induction-evaluation--result", "induction-evaluation--unavailable");
        rendirPreguntas.innerHTML = '<p class="text-muted">' + escapeHtml(S("my_induction_detail_loading", "Cargando...")) + '</p>';
        rendirPanel.scrollIntoView({ behavior: "smooth", block: "start" });

        const r = await llamarApi("./rendir-detalle.php?id_asignacion=" + encodeURIComponent(idAsignacion));
        if (!r.success) {
            ocultarAlerta(rendirAlert);
            rendirTitulo.textContent = S("activity_unavailable_title", "Actividad no disponible");
            rendirPanel.classList.add("induction-evaluation--unavailable");
            rendirPreguntas.innerHTML =
                '<div class="induction-unavailable" role="status">' +
                    '<i class="bi bi-file-earmark-x" aria-hidden="true"></i>' +
                    '<h3>' + escapeHtml(S("activity_unavailable_title", "Actividad no disponible")) + '</h3>' +
                    '<p>' + escapeHtml(S("activity_unavailable_text", "Esta evaluación todavía no está disponible o no tiene contenido configurado.")) + '</p>' +
                    '<button type="button" class="btn btn-outline-custom" data-unavailable-back>' +
                        '<i class="bi bi-arrow-left me-1" aria-hidden="true"></i>' + escapeHtml(S("activity_back_to_list", "Volver atrás")) +
                    '</button>' +
                '</div>';
            const backButton = rendirPreguntas.querySelector('[data-unavailable-back]');
            if (backButton) backButton.addEventListener("click", cerrarActividad);
            return;
        }

        rendirTitulo.textContent = r.data.name;
        renderizarPreguntasRendir(r.data.preguntas || [], r.data.materiales || [], r.data.description || '');
    }

    function obtenerBorrador() {
        const respuestas = {};
        bloquesPregunta().forEach(function (bloque) {
            const seleccionado = bloque.querySelector('input[type="radio"]:checked');
            if (seleccionado) respuestas[String(bloque.dataset.idRel)] = seleccionado.value;
        });
        return { respuestas: respuestas, question_index: currentQuestionIndex };
    }

    function guardarBorrador(mostrarConfirmacion) {
        const guardado = activitySession && activitySession.save(obtenerBorrador());
        if (mostrarConfirmacion) {
            mostrarAlerta(rendirAlert, guardado
                ? S("activity_draft_saved", "Tus respuestas quedaron guardadas en este dispositivo para continuar después.")
                : S("my_induction_submit_error", "No se pudo guardar el avance."), guardado ? "success" : "danger");
        }
        return guardado;
    }

    function restaurarBorrador() {
        const draft = activitySession ? activitySession.load() : null;
        if (!draft || !draft.respuestas) return;
        Object.keys(draft.respuestas).forEach(function (rel) {
            const input = rendirPreguntas.querySelector('[data-id-rel="' + CSS.escape(rel) + '"] input[value="' + CSS.escape(String(draft.respuestas[rel])) + '"]');
            if (input) input.checked = true;
        });
        const nextIndex = Number.isFinite(Number(draft.question_index)) ? Number(draft.question_index) : 0;
        mostrarPregunta(nextIndex, false);
        actualizarEstadoEvaluacion();
        mostrarAlerta(rendirAlert, S("activity_draft_restored", "Recuperamos las respuestas que habías guardado."), "info");
    }


    function sctQuestionMediaHtml(media) {
        if (!Array.isArray(media) || !media.length) return '';
        let html = '<div class="sct-question-execution-media">';
        media.forEach(function (m) {
            const path = '../../' + String(m.file_path || '').replace(/^\/+/, '');
            if (String(m.media_type || '') === 'video') {
                html += '<video controls preload="metadata" src="' + escapeHtml(path) + '" aria-label="' + escapeHtml(m.original_name || 'Video') + '"></video>';
            } else {
                html += '<img src="' + escapeHtml(path) + '" alt="' + escapeHtml(m.original_name || '') + '" loading="lazy">';
            }
        });
        return html + '</div>';
    }

    function renderizarPreguntasRendir(preguntas, materiales, descripcionCurso) {
        rendirPreguntas.innerHTML = "";

        materiales = Array.isArray(materiales) ? materiales : [];
        if (materiales.length || descripcionCurso) {
            const materialSection = document.createElement('section');
            materialSection.className = 'induction-course-materials';
            let materialHtml = '<div class="induction-course-materials__head"><span class="induction-course-materials__icon" aria-hidden="true"><i class="bi bi-book"></i></span><div><h3>' + escapeHtml(S('my_induction_course_materials_title', 'Material del curso')) + '</h3><p>' + escapeHtml(S('my_induction_course_materials_intro', 'Revisa este contenido antes de responder la evaluación.')) + '</p></div></div>';
            if (descripcionCurso) materialHtml += '<p class="induction-course-materials__description">' + escapeHtml(descripcionCurso) + '</p>';
            materialHtml += '<div class="induction-course-materials__list">';
            materiales.forEach(function (material) {
                const type = String(material.material_type || 'otro');
                const icon = type === 'video' ? 'bi-play-btn' : (type === 'documento' ? 'bi-file-earmark-text' : (type === 'texto' ? 'bi-card-text' : 'bi-link-45deg'));
                const url = safeExternalUrl(material.file_path);
                materialHtml += '<article class="induction-course-material">' +
                    '<span class="induction-course-material__icon" aria-hidden="true"><i class="bi ' + icon + '"></i></span><div class="induction-course-material__copy"><strong>' + escapeHtml(material.title || '') + '</strong>' +
                    (material.content_text ? '<p>' + escapeHtml(material.content_text) + '</p>' : '') +
                    (url ? '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i>' + escapeHtml(S('my_induction_support_material', 'Abrir material de apoyo')) + '</a>' : '') +
                    '</div></article>';
            });
            materialHtml += '</div>';
            materialSection.innerHTML = materialHtml;
            rendirPreguntas.appendChild(materialSection);
        }

        preguntas.forEach(function (p, idx) {
            const bloque = document.createElement("section");
            bloque.className = "rendir-pregunta d-none";
            bloque.dataset.idRel = p.id_rel;
            bloque.dataset.idQuestion = p.id_question;
            bloque.dataset.questionIndex = idx;

            let opcionesHtml = "";
            (p.opciones || []).forEach(function (o, oIdx) {
                const inputId = "opt_" + p.id_rel + "_" + oIdx;
                opcionesHtml +=
                    '<div class="induction-option">' +
                        '<input class="form-check-input" type="radio" name="pregunta_' + escapeHtml(p.id_rel) + '" id="' + inputId + '" value="' + escapeHtml(o.id_questions_options) + '">' +
                        '<label class="form-check-label" for="' + inputId + '">' + escapeHtml(o.text_option) + '</label>' +
                    '</div>';
            });

            const materialUrl = safeExternalUrl(p.url_add_material);
            const materialHtml = materialUrl
                ? '<a class="induction-question-material" href="' + escapeHtml(materialUrl) + '" target="_blank" rel="noopener">' +
                    '<i class="bi bi-box-arrow-up-right"></i>' + escapeHtml(S("my_induction_support_material", "Abrir material de apoyo")) + '</a>'
                : '';

            const mediaHtml = sctQuestionMediaHtml(p.media || []);
            bloque.innerHTML =
                '<div class="sct-question-execution-layout"><div class="sct-question-execution-copy">' +
                    '<span class="induction-question-number">' + (idx + 1) + '</span>' +
                    '<h3 class="induction-question-title" tabindex="-1">' + escapeHtml(p.question) + '</h3>' + materialHtml +
                '</div>' + mediaHtml + '</div>' + opcionesHtml;

            bloque.addEventListener("change", function (event) {
                if (event.target.matches('input[type="radio"]')) {
                    ocultarAlerta(rendirAlert);
                    actualizarEstadoEvaluacion();
                }
            });

            rendirPreguntas.appendChild(bloque);
        });

        currentQuestionIndex = 0;
        mostrarPregunta(0, false);
        actualizarEstadoEvaluacion();
        restaurarBorrador();
    }

    function bloquesPregunta() {
        return Array.from(rendirPreguntas.querySelectorAll(".rendir-pregunta"));
    }

    function mostrarPregunta(index, focusQuestion) {
        const bloques = bloquesPregunta();
        if (!bloques.length) return;

        currentQuestionIndex = Math.min(Math.max(index, 0), bloques.length - 1);
        bloques.forEach(function (bloque, idx) {
            bloque.classList.toggle("d-none", idx !== currentQuestionIndex);
        });

        const current = bloques[currentQuestionIndex];
        const stepTemplate = S("my_induction_question_of", "Pregunta {current} de {total}");
        if (rendirPasoTexto) {
            rendirPasoTexto.textContent = stepTemplate
                .replace("{current}", currentQuestionIndex + 1)
                .replace("{total}", bloques.length);
        }

        if (rendirAnteriorBtn) rendirAnteriorBtn.disabled = currentQuestionIndex === 0 || evaluationLocked;
        if (rendirSiguienteBtn) {
            rendirSiguienteBtn.classList.toggle("d-none", currentQuestionIndex >= bloques.length - 1);
            rendirSiguienteBtn.disabled = evaluationLocked;
        }
        if (rendirEnviarBtn) {
            rendirEnviarBtn.classList.toggle("d-none", currentQuestionIndex < bloques.length - 1);
            rendirEnviarBtn.disabled = evaluationLocked;
        }

        if (focusQuestion) {
            const title = current.querySelector(".induction-question-title");
            if (title) title.focus({ preventScroll: true });
            current.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }
    }

    function actualizarEstadoEvaluacion() {
        const bloques = bloquesPregunta();
        const respondidas = bloques.filter(function (bloque) {
            return !!bloque.querySelector('input[type="radio"]:checked');
        }).length;
        const total = bloques.length;
        const answeredTemplate = S("my_induction_answered_of", "{answered} de {total} respondidas");

        if (rendirRespondidasTexto) {
            rendirRespondidasTexto.textContent = answeredTemplate
                .replace("{answered}", respondidas)
                .replace("{total}", total);
        }
        if (rendirProgresoBar) {
            const pct = total > 0 ? Math.round((respondidas / total) * 100) : 0;
            rendirProgresoBar.style.width = pct + "%";
            const track = rendirProgresoBar.parentElement;
            if (track) {
                track.setAttribute("aria-valuenow", String(pct));
                track.setAttribute("aria-valuemin", "0");
                track.setAttribute("aria-valuemax", "100");
            }
        }
    }

    function preguntaActualRespondida() {
        const bloques = bloquesPregunta();
        const current = bloques[currentQuestionIndex];
        return current ? !!current.querySelector('input[type="radio"]:checked') : false;
    }

    function cerrarActividad() {
        if (!evaluationLocked) guardarBorrador(false);
        rendirPanel.classList.add("d-none");
        rendirPanel.classList.remove("induction-evaluation--result", "induction-evaluation--unavailable");
        if (activitySession) activitySession.exit(personalShell, rendirPanel);
        else {
            document.body.classList.remove("sct-activity-mode");
            if (personalShell) personalShell.classList.remove("is-direct-activity");
        }
        setBrowseVisible(true);
        currentAsignacionId = null;
        currentQuestionIndex = 0;
        evaluationLocked = false;
        currentOverdue = false;
        ocultarAlerta(rendirAlert);
    }

    if (rendirAnteriorBtn) {
        rendirAnteriorBtn.addEventListener("click", function () {
            ocultarAlerta(rendirAlert);
            mostrarPregunta(currentQuestionIndex - 1, true);
        });
    }

    if (rendirSiguienteBtn) {
        rendirSiguienteBtn.addEventListener("click", function () {
            ocultarAlerta(rendirAlert);
            if (!preguntaActualRespondida()) {
                mostrarAlerta(rendirAlert, S("my_induction_choose_to_continue", "Selecciona una respuesta para continuar."), "warning");
                return;
            }
            mostrarPregunta(currentQuestionIndex + 1, true);
        });
    }

    if (rendirEnviarBtn) {
        rendirEnviarBtn.addEventListener("click", async function () {
            ocultarAlerta(rendirAlert);
            if (evaluationLocked) return;

            const bloques = bloquesPregunta();
            const respuestas = [];

            for (let i = 0; i < bloques.length; i += 1) {
                const bloque = bloques[i];
                const seleccionado = bloque.querySelector('input[type="radio"]:checked');
                if (!seleccionado) {
                    mostrarPregunta(i, true);
                    mostrarAlerta(rendirAlert, S("my_induction_answer_all_required", "Debes responder todas las preguntas antes de enviar."), "warning");
                    return;
                }

                respuestas.push({
                    id_rel: parseInt(bloque.dataset.idRel, 10),
                    id_question: parseInt(bloque.dataset.idQuestion, 10),
                    id_questions_options: parseInt(seleccionado.value, 10),
                });
            }

            const confirmMessage = currentOverdue
                ? S("activity_confirm_submit_late", "Esta entrega está fuera de plazo. ¿Confirmas que deseas enviarla ahora?")
                : S("activity_confirm_submit", S("my_induction_confirm_submit", "¿Enviar tus respuestas? Después de enviarlas se registrará este intento."));
            if (!await window.sctConfirmAction(confirmMessage)) {
                return;
            }

            evaluationLocked = true;
            rendirEnviarBtn.disabled = true;
            if (rendirAnteriorBtn) rendirAnteriorBtn.disabled = true;
            if (rendirSiguienteBtn) rendirSiguienteBtn.disabled = true;

            const r = await postJson("./rendir-responder.php", {
                id_asignacion: currentAsignacionId,
                respuestas: respuestas,
            });

            if (!r.success) {
                evaluationLocked = false;
                mostrarPregunta(currentQuestionIndex, false);
                mostrarAlerta(rendirAlert, r.message || S("my_induction_submit_error", "No se pudo enviar tu respuesta."), "danger");
                return;
            }

            if (activitySession) activitySession.clear();

            rendirPanel.classList.add("induction-evaluation--result");
            if (r.data.aprobado) {
                const msgOk = S("my_induction_passed", "¡Aprobaste con {pct}%! Ya puedes descargar tu certificado desde la lista.")
                    .replace("{pct}", r.data.porcentaje);
                mostrarAlerta(rendirAlert, msgOk, "success");
            } else {
                const restantes = r.data.attempts_allowed - r.data.intentos_usados;
                const mensaje = restantes > 0
                    ? S("my_induction_failed_with_attempts", "No alcanzaste el puntaje mínimo ({pct}%). Te quedan {n} intento(s).")
                        .replace("{pct}", r.data.porcentaje)
                        .replace("{n}", restantes)
                    : S("my_induction_failed_no_attempts", "No alcanzaste el puntaje mínimo ({pct}%) y ya no te quedan intentos disponibles.")
                        .replace("{pct}", r.data.porcentaje);
                mostrarAlerta(rendirAlert, mensaje, "warning");
            }

            cargarMisAsignaciones();
        });
    }

    if (rendirGuardarBtn) {
        rendirGuardarBtn.addEventListener("click", function () { guardarBorrador(true); });
    }

    if (rendirCerrarBtn) {
        rendirCerrarBtn.addEventListener("click", cerrarActividad);
    }

    // Entrada directa desde Mi espacio: primero confirma que la asignación de
    // curso pertenece realmente al usuario y luego abre el motor existente.
    // Esto evita carreras entre el listado y la carga directa del curso.
    cargarMisAsignaciones();
});
