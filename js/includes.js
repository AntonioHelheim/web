/**
 * includes.js — Helheim component loader
 * Carga fragmentos HTML en los contenedores definidos por [data-include].
 *
 * Uso en HTML:
 *   <div data-include="components/navbar.html"></div>
 *
 * También admite IDs explícitos mapeados en COMPONENT_MAP (compatibilidad
 * con el patrón <div id="navbar"></div>).
 */

(function () {
    'use strict';

    /* ------------------------------------------------------------------ */
    /* Mapa de IDs → rutas de componentes (patrón legacy por id)           */
    /* ------------------------------------------------------------------ */
    const COMPONENT_MAP = {
        'navbar'           : 'components/navbar.html',
        'carousel-desktop' : 'components/carousel-desktop.html',
        'carousel-movil'   : 'components/carousel-movil.html',
        'aboutcup'         : 'components/aboutcup.html',
        'servicios'        : 'components/servicios.html',
        'contacto'         : 'components/contacto.html',
        'footer'           : 'components/footer.html',
    };

    /* ------------------------------------------------------------------ */
    /* Carga un fragmento HTML en un elemento destino                      */
    /* ------------------------------------------------------------------ */
    async function loadComponent(element, url) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                console.warn(`[includes.js] No se pudo cargar: ${url} (${response.status})`);
                return;
            }
            const html = await response.text();
            element.innerHTML = html;

            /* Re-ejecutar scripts incrustados dentro del fragmento, si los hay */
            element.querySelectorAll('script').forEach(oldScript => {
                const newScript = document.createElement('script');
                [...oldScript.attributes].forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.textContent = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });

            /* Disparar evento personalizado para que otros scripts reaccionen */
            element.dispatchEvent(new CustomEvent('component:loaded', {
                bubbles: true,
                detail: { url }
            }));

        } catch (err) {
            console.error(`[includes.js] Error al cargar ${url}:`, err);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Punto de entrada — recolecta todos los contenedores a inyectar      */
    /* ------------------------------------------------------------------ */
    async function init() {
        const tasks = [];

        /* 1. Atributo data-include="ruta/componente.html" (método preferido) */
        document.querySelectorAll('[data-include]').forEach(el => {
            const url = el.dataset.include;
            tasks.push(loadComponent(el, url));
        });

        /* 2. IDs registrados en COMPONENT_MAP (compatibilidad con patrón legacy) */
        Object.entries(COMPONENT_MAP).forEach(([id, url]) => {
            const el = document.getElementById(id);
            /* Solo actuar si el elemento existe Y no viene ya cubierto por data-include */
            if (el && !el.dataset.include) {
                tasks.push(loadComponent(el, url));
            }
        });

        /* Esperar a que todos los componentes estén inyectados */
        await Promise.all(tasks);

        /* Disparar evento global cuando todo esté listo */
        document.dispatchEvent(new CustomEvent('includes:ready'));
    }

    /* Arrancar cuando el DOM esté disponible */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();