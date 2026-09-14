/**
 * ==========================================================
 * LANG-SWITCHER.JS
 * SAFETY CONTROL TOWER — Selector de idioma de las pantallas autenticadas
 *
 * Wiring único y compartido para cualquier <select id="pageLanguageSelect">
 * presente en una pantalla de navegación (dashboard, gestiones de módulo,
 * "mis-*", etc.). Antes, cada módulo repetía su propio listener que solo
 * hacía `location.href = ?lang=xx`, sin confirmación ni persistencia.
 *
 * Este archivo agrega, de forma centralizada para TODAS las pantallas que
 * lo incluyan:
 *   1) Confirmación antes de aplicar el cambio.
 *   2) Guardado del idioma elegido en el perfil del usuario (BD), vía
 *      POST a api/usuarios/idioma.php, ANTES de recargar la página.
 *   3) La recarga con ?lang= de siempre, para que el resto de la
 *      navegación (todas las páginas leen $_SESSION['site_lang'] en
 *      i18n.php) quede en el idioma nuevo de inmediato.
 *
 * Si el guardado en BD falla (red, servidor caído, etc.) el cambio de
 * idioma de la sesión actual se aplica igual: journal degradado, nunca
 * bloqueante para el usuario. El error queda en consola para soporte.
 *
 * NO toca el selector de la pantalla de login (#loginLanguage) ni el de
 * recuperación de contraseña: antes de iniciar sesión no hay un perfil de
 * usuario al que asociar el idioma, por lo que ese selector sigue
 * funcionando solo como preferencia de sesión (comportamiento sin cambios).
 * ==========================================================
 */

(function () {
    "use strict";

    // Debe leerse de forma síncrona, en el momento en que el navegador
    // ejecuta este <script>, para poder resolver la URL del endpoint sin
    // importar si la página que lo incluye vive en la raíz del sitio
    // (bienvenida.php) o dos carpetas más abajo (api/<modulo>/archivo.php).
    var THIS_SCRIPT_URL = document.currentScript ? document.currentScript.src : "";

    // Textos de respaldo si la página no inyectó window.SCT_LANG_SWITCHER_I18N
    // (ej. una pantalla todavía no actualizada). Cubren los 5 idiomas del
    // sistema para que el diálogo de confirmación nunca se vea en un idioma
    // distinto al que el usuario tiene activo.
    var FALLBACK_STRINGS = {
        es: { title: "Cambiar idioma", text: "¿Deseas cambiar el idioma del sistema a {language}? Se actualizará tu perfil.", confirm: "Confirmar", cancel: "Cancelar", updated: "Idioma actualizado correctamente.", error: "No fue posible guardar el idioma en tu perfil, pero se aplicará en esta sesión." },
        en: { title: "Change language", text: "Do you want to switch the system language to {language}? Your profile will be updated.", confirm: "Confirm", cancel: "Cancel", updated: "Language updated successfully.", error: "We couldn't save the language to your profile, but it will still apply to this session." },
        pt: { title: "Alterar idioma", text: "Deseja alterar o idioma do sistema para {language}? Seu perfil será atualizado.", confirm: "Confirmar", cancel: "Cancelar", updated: "Idioma atualizado com sucesso.", error: "Não foi possível salvar o idioma no seu perfil, mas ele será aplicado nesta sessão." },
        fr: { title: "Changer de langue", text: "Voulez-vous changer la langue du système en {language} ? Votre profil sera mis à jour.", confirm: "Confirmer", cancel: "Annuler", updated: "Langue mise à jour.", error: "Impossible d'enregistrer la langue dans votre profil, mais elle sera appliquée à cette session." },
        zh: { title: "更改语言", text: "是否将系统语言切换为 {language}？您的个人资料将随之更新。", confirm: "确认", cancel: "取消", updated: "语言已成功更新。", error: "无法将语言保存到您的个人资料，但本次会话仍会应用该语言。" }
    };

    function idiomaActivo() {
        var lang = (document.documentElement.getAttribute("lang") || "es").toLowerCase();
        return FALLBACK_STRINGS[lang] ? lang : "es";
    }

    function obtenerTextos() {
        var inyectados = window.SCT_LANG_SWITCHER_I18N;
        var base = FALLBACK_STRINGS[idiomaActual()] || FALLBACK_STRINGS.es;
        if (inyectados && typeof inyectados === "object") {
            // La página puede inyectar solo los textos de su idioma activo;
            // se completan los que falten con el respaldo de ese mismo idioma.
            return Object.assign({}, base, inyectados);
        }
        return base;
    }

    function idiomaActual() {
        return idiomaActiva_cache || (idiomaActiva_cache = idiomaActivo());
    }
    var idiomaActiva_cache = null;

    function obtenerCsrfToken() {
        var conAtributo = document.querySelector("[data-csrf-token]");
        if (conAtributo && conAtributo.getAttribute("data-csrf-token")) {
            return conAtributo.getAttribute("data-csrf-token");
        }
        var inputOculto = document.getElementById("csrfToken");
        if (inputOculto && inputOculto.value) {
            return inputOculto.value;
        }
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute("content")) {
            return meta.getAttribute("content");
        }
        return "";
    }

    function resolverUrlEndpoint() {
        if (!THIS_SCRIPT_URL) {
            // Respaldo poco probable (script cargado sin src detectable):
            // se asume la ruta relativa clásica desde la raíz del sitio.
            return "api/usuarios/idioma.php";
        }
        // js/lang-switcher.js y api/usuarios/idioma.php son hermanos bajo la
        // raíz de la app (misma carpeta padre que "js" y "api"), sin importar
        // si el sitio vive en el dominio raíz o bajo una subcarpeta de
        // ambiente de desarrollo (ver Plan de Trabajo — control de versiones).
        return new URL("../api/usuarios/idioma.php", THIS_SCRIPT_URL).toString();
    }

    /**
     * Modal de confirmación mínimo, sin dependencias (no asume que Bootstrap
     * JS esté cargado en la página). Devuelve una Promise<boolean>.
     */
    function confirmarCambioIdioma(mensaje, tituloTexto, textoConfirmar, textoCancelar) {
        return new Promise(function (resolve) {
            var overlay = document.createElement("div");
            overlay.setAttribute("role", "presentation");
            overlay.style.cssText =
                "position:fixed;inset:0;z-index:2000;background:rgba(15,23,42,.45);" +
                "display:flex;align-items:center;justify-content:center;padding:16px;" +
                "font-family:inherit;";

            var dialogo = document.createElement("div");
            dialogo.setAttribute("role", "alertdialog");
            dialogo.setAttribute("aria-modal", "true");
            dialogo.setAttribute("aria-labelledby", "sctLangSwitcherTitle");
            dialogo.style.cssText =
                "background:#fff;border-radius:14px;max-width:380px;width:100%;" +
                "padding:22px 22px 18px;box-shadow:0 24px 60px rgba(15,23,42,.28);" +
                "color:#1f2937;";

            var titulo = document.createElement("h2");
            titulo.id = "sctLangSwitcherTitle";
            titulo.textContent = tituloTexto;
            titulo.style.cssText = "margin:0 0 10px;font-size:17px;font-weight:800;color:#0b2149;";

            var texto = document.createElement("p");
            texto.textContent = mensaje;
            texto.style.cssText = "margin:0 0 20px;font-size:13.5px;line-height:1.5;color:#374151;";

            var acciones = document.createElement("div");
            acciones.style.cssText = "display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap;";

            var btnCancelar = document.createElement("button");
            btnCancelar.type = "button";
            btnCancelar.textContent = textoCancelar;
            btnCancelar.style.cssText =
                "border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:8px;" +
                "padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;";

            var btnConfirmar = document.createElement("button");
            btnConfirmar.type = "button";
            btnConfirmar.textContent = textoConfirmar;
            btnConfirmar.style.cssText =
                "border:none;background:#0a3d91;color:#fff;border-radius:8px;" +
                "padding:8px 16px;font-size:13px;font-weight:700;cursor:pointer;";

            function cerrar(resultado) {
                document.removeEventListener("keydown", alEscape, true);
                if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                resolve(resultado);
            }

            function alEscape(evento) {
                if (evento.key === "Escape") cerrar(false);
            }

            btnCancelar.addEventListener("click", function () { cerrar(false); });
            btnConfirmar.addEventListener("click", function () { cerrar(true); });
            overlay.addEventListener("mousedown", function (evento) {
                if (evento.target === overlay) cerrar(false);
            });
            document.addEventListener("keydown", alEscape, true);

            acciones.appendChild(btnCancelar);
            acciones.appendChild(btnConfirmar);
            dialogo.appendChild(titulo);
            dialogo.appendChild(texto);
            dialogo.appendChild(acciones);
            overlay.appendChild(dialogo);
            document.body.appendChild(overlay);
            btnConfirmar.focus();
        });
    }

    async function guardarIdiomaEnPerfil(codigoIdioma) {
        var csrfToken = obtenerCsrfToken();
        var respuesta = await fetch(resolverUrlEndpoint(), {
            method: "POST",
            credentials: "same-origin",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ language: codigoIdioma, csrf_token: csrfToken })
        });
        var datos = null;
        try { datos = await respuesta.json(); } catch (e) { /* respuesta no-JSON: se ignora, ver catch superior */ }
        if (!respuesta.ok || !datos || !datos.success) {
            throw new Error((datos && datos.message) || "No fue posible guardar el idioma.");
        }
        return datos;
    }

    function inicializar() {
        var select = document.getElementById("pageLanguageSelect");
        if (!select) return;

        var valorPrevio = select.value;

        select.addEventListener("change", function () {
            var nuevoValor = select.value;
            var opcionElegida = select.options[select.selectedIndex];
            var nombreIdiomaElegido = opcionElegida ? opcionElegida.textContent.trim() : nuevoValor.toUpperCase();

            var textos = obtenerTextos();
            var mensaje = (textos.text || FALLBACK_STRINGS.es.text).replace("{language}", nombreIdiomaElegido);

            confirmarCambioIdioma(mensaje, textos.title, textos.confirm, textos.cancel).then(function (confirmado) {
                if (!confirmado) {
                    select.value = valorPrevio;
                    return;
                }

                select.disabled = true;

                guardarIdiomaEnPerfil(nuevoValor)
                    .catch(function (error) {
                        // No bloquea la navegación: el cambio de idioma de la
                        // sesión ocurre igual vía ?lang=, solo avisamos por
                        // consola (soporte) de que la persistencia en BD falló.
                        console.warn("lang-switcher: no se pudo guardar el idioma en el perfil.", error);
                    })
                    .finally(function () {
                        valorPrevio = nuevoValor;
                        var url = new URL(window.location.href);
                        url.searchParams.set("lang", nuevoValor);
                        window.location.href = url.toString();
                    });
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", inicializar);
    } else {
        inicializar();
    }
})();
