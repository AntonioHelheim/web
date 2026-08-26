/* =========================================================
   KUDO CHILE

   MAIN.JS

   Este archivo:

   1. Carga los partials HTML
   2. Normaliza recursos internos
   3. Inicializa Bootstrap
   4. Inicializa navegación
   5. Inicializa animaciones
   6. Configura videos
   7. Configura imágenes
   8. Inicializa Virtudes del Bushido
   9. Ejecuta funciones auxiliares

   ========================================================= */


/* =========================================================
   1. RUTA RAÍZ DEL PROYECTO
   ========================================================= */

function getProjectRoot() {

    const scripts = document.querySelectorAll(
        'script[src]'
    );

    for (const script of scripts) {

        const src =
            script.getAttribute('src');

        if (!src) {
            continue;
        }

        if (!src.includes('js-main.js')) {
            continue;
        }

        const scriptURL =
            new URL(
                src,
                document.baseURI
            );

        return new URL(
            '../',
            scriptURL
        ).href;

    }

    console.warn(
        'No se encontró la ubicación de js-main.js. ' +
        'Se utilizará document.baseURI.'
    );

    return new URL(
        './',
        document.baseURI
    ).href;

}


/* =========================================================
   2. CONVERTIR RUTA DE RECURSO A RUTA ABSOLUTA
   ========================================================= */

function resolveProjectResource(resourcePath) {

    if (!resourcePath) {
        return resourcePath;
    }

    const trimmedPath =
        resourcePath.trim();

    /*
     * No modificar URLs externas o especiales.
     */

    if (

        trimmedPath.startsWith('http://') ||
        trimmedPath.startsWith('https://') ||
        trimmedPath.startsWith('//') ||
        trimmedPath.startsWith('data:') ||
        trimmedPath.startsWith('blob:') ||
        trimmedPath.startsWith('#')

    ) {

        return trimmedPath;

    }

    const projectRoot =
        getProjectRoot();

    /*
     * Eliminar ./ o ../ solamente
     * al comienzo de la ruta.
     */

    const cleanPath =
        trimmedPath.replace(
            /^(?:\.\.\/|\.\/)+/,
            ''
        );

    return new URL(
        cleanPath,
        projectRoot
    ).href;

}


/* =========================================================
   3. NORMALIZAR RECURSOS DE LOS PARTIALS
   ========================================================= */

function normalizePartialResources(container) {

    if (!container) {
        return;
    }

    const images =
        container.querySelectorAll(
            'img[src]'
        );

    images.forEach(img => {

        const originalSrc =
            img.getAttribute('src');

        if (!originalSrc) {
            return;
        }

        const absolutePath =
            resolveProjectResource(
                originalSrc
            );

        img.setAttribute(
            'src',
            absolutePath
        );

        img.addEventListener(
            'error',
            function () {

                console.error(
                    '❌ ERROR CARGANDO IMAGEN:',
                    absolutePath
                );

                console.error(
                    'Ruta original:',
                    originalSrc
                );

            },
            {
                once: true
            }
        );

    });


    /* =====================================================
       SRCSET
       ===================================================== */

    const srcsetElements =
        container.querySelectorAll(
            '[srcset]'
        );

    srcsetElements.forEach(element => {

        const srcset =
            element.getAttribute(
                'srcset'
            );

        if (!srcset) {
            return;
        }

        const normalized =
            srcset
                .split(',')
                .map(item => {

                    const parts =
                        item
                            .trim()
                            .split(/\s+/);

                    const originalPath =
                        parts[0];

                    if (!originalPath) {
                        return item.trim();
                    }

                    const absolutePath =
                        resolveProjectResource(
                            originalPath
                        );

                    return [
                        absolutePath,
                        ...parts.slice(1)
                    ].join(' ');

                })
                .join(', ');

        element.setAttribute(
            'srcset',
            normalized
        );

    });


    /* =====================================================
       POSTERS
       ===================================================== */

    const posters =
        container.querySelectorAll(
            '[poster]'
        );

    posters.forEach(element => {

        const originalPath =
            element.getAttribute(
                'poster'
            );

        if (!originalPath) {
            return;
        }

        const absolutePath =
            resolveProjectResource(
                originalPath
            );

        element.setAttribute(
            'poster',
            absolutePath
        );

    });

}


/* =========================================================
   4. CARGAR PARTIAL
   ========================================================= */

async function loadPartial(
    containerId,
    filePath
) {

    const container =
        document.getElementById(
            containerId
        );

    if (!container) {

        console.error(
            `No existe el contenedor #${containerId}`
        );

        return;

    }

    try {

        const response =
            await fetch(filePath);

        if (!response.ok) {

            throw new Error(
                `HTTP ${response.status} - ${response.statusText}`
            );

        }

        const html =
            await response.text();

        container.innerHTML =
            html;

        /*
         * Normalizar rutas de recursos
         * después de insertar el partial.
         */

        normalizePartialResources(
            container
        );

        console.log(
            `✓ Partial cargado: ${filePath}`
        );

    } catch (error) {

        console.error(
            `✕ Error cargando ${filePath}:`,
            error
        );

        container.innerHTML = `
            <div class="container py-5">
                <div class="alert alert-danger">
                    <strong>Error:</strong>
                    No se pudo cargar esta sección.
                </div>
            </div>
        `;

    }

}


/* =========================================================
   5. CARGAR TODAS LAS SECCIONES
   ========================================================= */

async function loadAllPartials() {

    await Promise.all([

        loadPartial(
            'navbar-container',
            './partials/navbar.html'
        ),

        loadPartial(
            'home-container',
            './partials/header.html'
        ),

        loadPartial(
            'about-container',
            './partials/about.html'
        ),

        loadPartial(
            'events-container',
            './partials/events.html'
        ),

        loadPartial(
            'dojos-chile-container',
            './partials/dojos-chile.html'
        ),

        loadPartial(
            'bushido-container',
            './partials/bushido.html'
        ),

        loadPartial(
            'grados-container',
            './partials/grados.html'
        ),

        loadPartial(
            'kihon-container',
            './partials/kihon.html'
        ),

        loadPartial(
            'hokutoki-container',
            './partials/hokutoki.html'
        ),

        loadPartial(
            'dojos-international-container',
            './partials/dojos-international.html'
        ),

        loadPartial(
            'federation-container',
            './partials/federation.html'
        ),

        loadPartial(
            'footer-container',
            './partials/footer.html'
        )

    ]);

    console.log(
        '✓ Todos los partials fueron cargados.'
    );

}


/* =========================================================
   6. VIRTUDES DEL BUSHIDO
   ========================================================= */

function initBushidoVirtues() {

    const virtueCards =
        document.querySelectorAll(
            '.virtue-card'
        );

    if (!virtueCards.length) {

        console.warn(
            'No se encontraron cards de virtudes.'
        );

        return;

    }

    virtueCards.forEach(card => {

        const trigger =
            card.querySelector(
                '.bushido-virtue-trigger'
            );

        if (!trigger) {
            return;
        }

        /*
         * Evita duplicar listeners.
         */

        if (
            trigger.dataset.virtueInitialized ===
            'true'
        ) {
            return;
        }

        trigger.dataset.virtueInitialized =
            'true';

        /*
         * Buscar el contenido asociado.
         */

        const targetSelector =
            trigger.getAttribute(
                'data-bs-target'
            ) ||
            trigger.getAttribute(
                'data-target'
            ) ||
            trigger.getAttribute(
                'href'
            );

        let target =
            null;

        if (
            targetSelector &&
            targetSelector.startsWith('#')
        ) {

            target =
                document.querySelector(
                    targetSelector
                );

        }

        /*
         * Fallback:
         * buscar contenido dentro de la card.
         */

        if (!target) {

            target =
                card.querySelector(
                    '.virtue-description, ' +
                    '.virtue-content, ' +
                    '.collapse'
                );

        }

        if (!target) {

            console.warn(
                'No se encontró contenido para la virtud:',
                card
            );

            return;

        }

        /*
         * Estado inicial.
         */

        const initiallyOpen =
            target.classList.contains('show');

        trigger.setAttribute(
            'aria-expanded',
            initiallyOpen
                ? 'true'
                : 'false'
        );

        if (initiallyOpen) {

            card.classList.add(
                'is-open'
            );

        }

        /*
         * Evento click.
         */

        trigger.addEventListener(
            'click',
            function (event) {

                /*
                 * Si es un enlace interno,
                 * evitar navegación.
                 */

                if (
                    trigger.tagName === 'A'
                ) {

                    event.preventDefault();

                }

                /*
                 * Detectar si Bootstrap Collapse
                 * está disponible.
                 */

                const hasBootstrapCollapse =

                    typeof bootstrap !==
                    'undefined' &&

                    bootstrap.Collapse;


                /*
                 * Cerrar otras cards.
                 */

                virtueCards.forEach(otherCard => {

                    if (
                        otherCard === card
                    ) {
                        return;
                    }

                    const otherTrigger =
                        otherCard.querySelector(
                            '.bushido-virtue-trigger'
                        );

                    const otherTargetSelector =
                        otherTrigger
                            ? (
                                otherTrigger.getAttribute(
                                    'data-bs-target'
                                ) ||
                                otherTrigger.getAttribute(
                                    'data-target'
                                )
                            )
                            : null;

                    let otherTarget =
                        null;

                    if (
                        otherTargetSelector
                    ) {

                        otherTarget =
                            document.querySelector(
                                otherTargetSelector
                            );

                    }

                    if (!otherTarget) {

                        otherTarget =
                            otherCard.querySelector(
                                '.virtue-description, ' +
                                '.virtue-content, ' +
                                '.collapse'
                            );

                    }

                    if (!otherTarget) {
                        return;
                    }

                    /*
                     * Cerrar usando Bootstrap
                     * si está disponible.
                     */

                    if (
                        hasBootstrapCollapse
                    ) {

                        const instance =
                            bootstrap.Collapse
                                .getOrCreateInstance(
                                    otherTarget,
                                    {
                                        toggle: false
                                    }
                                );

                        instance.hide();

                    } else {

                        otherTarget.classList.remove(
                            'show'
                        );

                    }

                    otherCard.classList.remove(
                        'is-open'
                    );

                    if (otherTrigger) {

                        otherTrigger.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                    }

                });


                /*
                 * Abrir/cerrar card seleccionada.
                 */

                if (
                    hasBootstrapCollapse
                ) {

                    const instance =
                        bootstrap.Collapse
                            .getOrCreateInstance(
                                target,
                                {
                                    toggle: false
                                }
                            );

                    instance.toggle();

                } else {

                    target.classList.toggle(
                        'show'
                    );

                }

            }
        );


        /*
         * Eventos de Bootstrap.
         *
         * Mantienen sincronizado el estado visual.
         */

        target.addEventListener(
            'show.bs.collapse',
            function () {

                card.classList.add(
                    'is-open'
                );

                trigger.setAttribute(
                    'aria-expanded',
                    'true'
                );

            }
        );


        target.addEventListener(
            'hide.bs.collapse',
            function () {

                card.classList.remove(
                    'is-open'
                );

                trigger.setAttribute(
                    'aria-expanded',
                    'false'
                );

            }
        );


        /*
         * Fallback cuando Bootstrap
         * no está disponible.
         */

        target.addEventListener(
            'transitionend',
            function () {

                const isOpen =
                    target.classList.contains(
                        'show'
                    );

                card.classList.toggle(
                    'is-open',
                    isOpen
                );

                trigger.setAttribute(
                    'aria-expanded',
                    isOpen
                        ? 'true'
                        : 'false'
                );

            }
        );

    });

    console.log(
        '✓ Virtudes del Bushido inicializadas.'
    );

}


/* =========================================================
   7. SCROLL Y BOTÓN VOLVER ARRIBA
   ========================================================= */

function initBackToTop() {

    const backToTopBtn =
        document.getElementById(
            'backToTop'
        );

    if (!backToTopBtn) {
        return;
    }

    function updateBackToTop() {

        backToTopBtn.classList.toggle(
            'show',
            window.scrollY > 300
        );

    }

    window.addEventListener(
        'scroll',
        updateBackToTop,
        {
            passive: true
        }
    );

    updateBackToTop();

    backToTopBtn.addEventListener(
        'click',
        function (event) {

            event.preventDefault();

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

        }
    );

}


/* =========================================================
   8. NAVEGACIÓN INTERNA
   ========================================================= */

function initInternalLinks() {

    document
        .querySelectorAll(
            'a[href^="#"]'
        )
        .forEach(anchor => {

            /*
             * No interceptar controles
             * de Bootstrap Collapse.
             */

            if (
                anchor.hasAttribute(
                    'data-bs-toggle'
                )
            ) {
                return;
            }

            anchor.addEventListener(
                'click',
                function (event) {

                    const href =
                        this.getAttribute(
                            'href'
                        );

                    if (
                        !href ||
                        href === '#'
                    ) {
                        return;
                    }

                    let target;

                    try {

                        target =
                            document.querySelector(
                                href
                            );

                    } catch (error) {

                        return;

                    }

                    if (!target) {
                        return;
                    }

                    event.preventDefault();

                    const navbar =
                        document.querySelector(
                            '.navbar'
                        );

                    const navbarHeight =
                        navbar
                            ? navbar.offsetHeight
                            : 0;

                    const offsetTop =
                        target
                            .getBoundingClientRect()
                            .top +
                        window.scrollY -
                        navbarHeight -
                        20;

                    window.scrollTo({

                        top:
                            Math.max(
                                0,
                                offsetTop
                            ),

                        behavior:
                            'smooth'

                    });


                    /*
                     * Cerrar navbar móvil.
                     */

                    const navbarCollapse =
                        document.querySelector(
                            '.navbar-collapse.show'
                        );

                    if (
                        navbarCollapse &&
                        typeof bootstrap !==
                        'undefined' &&
                        bootstrap.Collapse
                    ) {

                        bootstrap.Collapse
                            .getOrCreateInstance(
                                navbarCollapse
                            )
                            .hide();

                    }

                }
            );

        });

}


/* =========================================================
   9. NAVBAR STICKY
   ========================================================= */

function initNavbar() {

    const navbar =
        document.querySelector(
            '.navbar'
        );

    if (!navbar) {
        return;
    }

    function updateNavbar() {

        navbar.classList.toggle(
            'navbar-scrolled',
            window.scrollY > 50
        );

    }

    window.addEventListener(
        'scroll',
        updateNavbar,
        {
            passive: true
        }
    );

    updateNavbar();

}


/* =========================================================
   10. ANIMACIONES AL HACER SCROLL
   ========================================================= */

function initScrollAnimations() {

    const elements =
        document.querySelectorAll(

            '.card-section, ' +
            '.dojo-card, ' +
            '.event-card, ' +
            '.virtue-card, ' +
            '.video-card'

        );

    if (!elements.length) {
        return;
    }

    if (
        !('IntersectionObserver' in window)
    ) {

        elements.forEach(element => {

            element.classList.add(
                'is-visible'
            );

        });

        return;

    }

    const observer =
        new IntersectionObserver(

            function (entries) {

                entries.forEach(
                    entry => {

                        if (
                            !entry.isIntersecting
                        ) {
                            return;
                        }

                        entry.target.classList.add(
                            'is-visible'
                        );

                        observer.unobserve(
                            entry.target
                        );

                    }
                );

            },

            {
                threshold: 0.1,
                rootMargin:
                    '0px 0px -80px 0px'
            }

        );

    elements.forEach(element => {

        element.classList.add(
            'scroll-animation'
        );

        observer.observe(
            element
        );

    });

}


/* =========================================================
   11. TOOLTIPS BOOTSTRAP
   ========================================================= */

function initTooltips() {

    if (

        typeof bootstrap ===
        'undefined' ||

        !bootstrap.Tooltip

    ) {
        return;
    }

    document
        .querySelectorAll(
            '[data-bs-toggle="tooltip"]'
        )
        .forEach(element => {

            bootstrap.Tooltip
                .getOrCreateInstance(
                    element
                );

        });

}


/* =========================================================
   12. VIDEOS RESPONSIVOS
   ========================================================= */

function makeVideosResponsive() {

    const iframes =
        document.querySelectorAll(
            'iframe[src*="youtube"], ' +
            'iframe[src*="youtu.be"]'
        );

    iframes.forEach(iframe => {

        if (

            iframe.parentElement &&
            iframe.parentElement.classList.contains(
                'ratio'
            )

        ) {
            return;
        }

        const wrapper =
            document.createElement(
                'div'
            );

        wrapper.classList.add(
            'ratio',
            'ratio-16x9',
            'video-wrapper'
        );

        iframe.parentNode.insertBefore(
            wrapper,
            iframe
        );

        wrapper.appendChild(
            iframe
        );

    });

}


/* =========================================================
   13. LAZY LOADING DE IMÁGENES
   ========================================================= */

function initLazyImages() {

    const images =
        document.querySelectorAll(
            'img[data-src]'
        );

    if (!images.length) {
        return;
    }

    function loadImage(img) {

        const source =
            img.dataset.src;

        if (!source) {
            return;
        }

        img.src =
            resolveProjectResource(
                source
            );

        img.removeAttribute(
            'data-src'
        );

    }

    if (
        !('IntersectionObserver' in window)
    ) {

        images.forEach(loadImage);

        return;

    }

    const imageObserver =
        new IntersectionObserver(

            function (
                entries,
                observer
            ) {

                entries.forEach(
                    entry => {

                        if (
                            !entry.isIntersecting
                        ) {
                            return;
                        }

                        loadImage(
                            entry.target
                        );

                        observer.unobserve(
                            entry.target
                        );

                    }
                );

            }

        );

    images.forEach(img => {

        imageObserver.observe(
            img
        );

    });

}


/* =========================================================
   14. ESTADO DE EVENTOS
   ========================================================= */

function updateEventStatus() {

    const eventCards =
        document.querySelectorAll(
            '.event-card'
        );

    eventCards.forEach(card => {

        card.classList.remove(
            'event-past'
        );

    });

}


/* =========================================================
   15. VALIDACIÓN DE FORMULARIOS
   ========================================================= */

function initForms() {

    const forms =
        document.querySelectorAll(
            'form'
        );

    forms.forEach(form => {

        form.addEventListener(
            'submit',
            function (event) {

                if (
                    !form.checkValidity()
                ) {

                    event.preventDefault();

                    event.stopPropagation();

                }

                form.classList.add(
                    'was-validated'
                );

            }
        );

    });

}


/* =========================================================
   16. TEMA
   ========================================================= */

function initTheme() {

    const html =
        document.documentElement;

    const savedTheme =
        localStorage.getItem(
            'theme'
        ) || 'dark';

    html.setAttribute(
        'data-bs-theme',
        savedTheme
    );

}


/* =========================================================
   17. ESTADÍSTICAS ANIMADAS
   ========================================================= */

function animateStats() {

    const statNumbers =
        document.querySelectorAll(
            '.stat-number'
        );

    if (
        !statNumbers.length ||
        !('IntersectionObserver' in window)
    ) {
        return;
    }

    const statsObserver =
        new IntersectionObserver(

            function (entries) {

                entries.forEach(
                    entry => {

                        if (
                            !entry.isIntersecting
                        ) {
                            return;
                        }

                        const target =
                            entry.target;

                        const finalValue =
                            parseInt(
                                target.textContent,
                                10
                            );

                        if (
                            isNaN(finalValue)
                        ) {
                            return;
                        }

                        let currentValue =
                            0;

                        const increment =
                            Math.max(
                                1,
                                Math.ceil(
                                    finalValue / 30
                                )
                            );

                        const interval =
                            setInterval(
                                function () {

                                    currentValue +=
                                        increment;

                                    if (
                                        currentValue >=
                                        finalValue
                                    ) {

                                        target.textContent =
                                            finalValue;

                                        clearInterval(
                                            interval
                                        );

                                    } else {

                                        target.textContent =
                                            currentValue;

                                    }

                                },
                                30
                            );

                        statsObserver.unobserve(
                            target
                        );

                    }
                );

            },

            {
                threshold: 0.5
            }

        );

    statNumbers.forEach(stat => {

        statsObserver.observe(
            stat
        );

    });

}


/* =========================================================
   18. FILTRO DE DOJOS POR REGIÓN Y COMUNA
   ========================================================= */

function initSearch() {

    const regionFilter =
        document.getElementById(
            'regionFilter'
        );

    const comunaFilter =
        document.getElementById(
            'comunaFilter'
        );

    const clearButton =
        document.getElementById(
            'clearDojoFilters'
        );

    const dojoCards =
        document.querySelectorAll(
            '.dojo-card'
        );

    const noResults =
        document.getElementById(
            'dojoNoResults'
        );

    const dojoCount =
        document.getElementById(
            'dojoCount'
        );

    if (

        !regionFilter ||
        !comunaFilter ||
        !dojoCards.length

    ) {
        return;
    }

    function normalizeText(text) {

        return text
            .toString()
            .normalize('NFD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .toLowerCase()
            .trim();

    }

    function updateComunaOptions() {

        const selectedRegion =
            regionFilter.value;

        const comunas =
            new Map();

        dojoCards.forEach(card => {

            const region =
                card.dataset.region ||
                '';

            const comuna =
                card.dataset.comuna ||
                '';

            if (

                !comuna ||

                (
                    selectedRegion !==
                    'all' &&

                    region !==
                    selectedRegion
                )

            ) {
                return;
            }

            let label =
                comuna
                    .replace(
                        /-/g,
                        ' '
                    )
                    .replace(
                        /\b\w/g,
                        letter =>
                            letter.toUpperCase()
                    );

            const location =
                card.querySelector(
                    '.dojo-detail div span'
                );

            if (location) {

                const text =
                    location.textContent.trim();

                const firstPart =
                    text.split(',')[0].trim();

                if (

                    normalizeText(
                        firstPart
                    ) ===

                    normalizeText(
                        comuna.replace(
                            /-/g,
                            ' '
                        )
                    )

                ) {

                    label =
                        firstPart;

                }

            }

            comunas.set(
                comuna,
                label
            );

        });

        comunaFilter.innerHTML =
            '';

        const allOption =
            document.createElement(
                'option'
            );

        allOption.value =
            'all';

        allOption.textContent =
            'Todas las comunas';

        comunaFilter.appendChild(
            allOption
        );

        Array.from(
            comunas.entries()
        )
            .sort(
                (a, b) =>
                    a[1].localeCompare(
                        b[1],
                        'es'
                    )
            )
            .forEach(
                ([value, label]) => {

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        value;

                    option.textContent =
                        label;

                    comunaFilter.appendChild(
                        option
                    );

                }
            );

        comunaFilter.disabled =
            comunas.size === 0;

    }

    function filterDojos() {

        const selectedRegion =
            regionFilter.value;

        const selectedComuna =
            comunaFilter.value;

        let visibleCount =
            0;

        dojoCards.forEach(card => {

            const matchesRegion =

                selectedRegion ===
                'all' ||

                card.dataset.region ===
                selectedRegion;


            const matchesComuna =

                selectedComuna ===
                'all' ||

                card.dataset.comuna ===
                selectedComuna;


            const shouldShow =

                matchesRegion &&
                matchesComuna;


            card.hidden =
                !shouldShow;

            if (shouldShow) {
                visibleCount++;
            }

        });

        if (dojoCount) {

            dojoCount.textContent =
                visibleCount;

        }

        if (noResults) {

            noResults.hidden =
                visibleCount !== 0;

        }

    }

    regionFilter.addEventListener(
        'change',
        function () {

            updateComunaOptions();

            comunaFilter.value =
                'all';

            filterDojos();

        }
    );

    comunaFilter.addEventListener(
        'change',
        filterDojos
    );

    if (clearButton) {

        clearButton.addEventListener(
            'click',
            function () {

                regionFilter.value =
                    'all';

                updateComunaOptions();

                comunaFilter.value =
                    'all';

                filterDojos();

            }
        );

    }

    updateComunaOptions();

    filterDojos();

}


/* =========================================================
   19. DETECCIÓN DE DISPOSITIVO
   ========================================================= */

function isMobile() {

    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i
        .test(
            navigator.userAgent
        );

}


/* =========================================================
   20. SMOOTH SCROLL
   ========================================================= */

function smoothScroll(
    target,
    duration = 1000
) {

    const element =
        document.querySelector(
            target
        );

    if (!element) {
        return;
    }

    const navbar =
        document.querySelector(
            '.navbar'
        );

    const navbarHeight =
        navbar
            ? navbar.offsetHeight
            : 0;

    const end =
        element
            .getBoundingClientRect()
            .top +
        window.scrollY -
        navbarHeight -
        20;

    window.scrollTo({

        top:
            Math.max(
                0,
                end
            ),

        behavior:
            'smooth'

    });

}


/* =========================================================
   21. OBTENER PARÁMETRO URL
   ========================================================= */

function getURLParameter(name) {

    const params =
        new URLSearchParams(
            window.location.search
        );

    return params.get(name) || '';

}


/* =========================================================
   22. VALIDAR EMAIL
   ========================================================= */

function validateEmail(email) {

    const regex =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return regex.test(
        email
    );

}


/* =========================================================
   23. COPIAR AL PORTAPAPELES
   ========================================================= */

function copyToClipboard(text) {

    if (

        navigator.clipboard &&
        window.isSecureContext

    ) {

        navigator.clipboard
            .writeText(text)
            .then(() => {

                console.log(
                    '✓ Copiado al portapapeles'
                );

            })
            .catch(error => {

                console.error(
                    'Error al copiar:',
                    error
                );

            });

        return;

    }

    /*
     * Fallback para HTTP/local.
     */

    const textarea =
        document.createElement(
            'textarea'
        );

    textarea.value =
        text;

    textarea.style.position =
        'fixed';

    textarea.style.opacity =
        '0';

    document.body.appendChild(
        textarea
    );

    textarea.select();

    try {

        document.execCommand(
            'copy'
        );

    } catch (error) {

        console.error(
            'Error al copiar:',
            error
        );

    }

    textarea.remove();

}

/* =========================================================
   23. VIRTUDES DEL BUSHIDO
   Bootstrap Collapse
   ========================================================= */

function initBushidoVirtues() {

    const virtuesContainer =
        document.getElementById(
            'bushidoVirtues'
        );


    if (!virtuesContainer) {

        console.warn(
            'No se encontró el contenedor #bushidoVirtues'
        );

        return;

    }


    const triggers =
        virtuesContainer.querySelectorAll(
            '.bushido-virtue-trigger'
        );


    if (!triggers.length) {

        console.warn(
            'No se encontraron cards de virtudes.'
        );

        return;

    }


    /*
     * Verificar que Bootstrap esté disponible.
     */

    if (
        typeof bootstrap === 'undefined' ||
        !bootstrap.Collapse
    ) {

        console.error(
            'Bootstrap Collapse no está disponible.'
        );

        return;

    }


    triggers.forEach(trigger => {

        const targetSelector =
            trigger.getAttribute(
                'data-bs-target'
            );


        if (!targetSelector) {
            return;
        }


        const target =
            document.querySelector(
                targetSelector
            );


        if (!target) {

            console.warn(
                `No se encontró ${targetSelector}`
            );

            return;

        }


        /*
         * Crear la instancia Bootstrap.
         *
         * toggle: false evita que se abra
         * automáticamente al inicializar.
         */

        bootstrap.Collapse.getOrCreateInstance(
            target,
            {
                toggle: false
            }
        );


        /*
         * El listener se agrega explícitamente.
         *
         * Esto es importante porque las cards
         * son cargadas dinámicamente mediante
         * fetch() desde bushido.html.
         */

        trigger.addEventListener(
            'click',
            function (event) {

                event.preventDefault();


                const collapse =
                    bootstrap.Collapse.getOrCreateInstance(
                        target,
                        {
                            toggle: false
                        }
                    );


                collapse.toggle();

            }
        );


        /*
         * Sincronizar atributos ARIA y clases.
         */

        target.addEventListener(
            'show.bs.collapse',
            function () {

                trigger.classList.remove(
                    'collapsed'
                );

                trigger.setAttribute(
                    'aria-expanded',
                    'true'
                );

            }
        );


        target.addEventListener(
            'hide.bs.collapse',
            function () {

                trigger.classList.add(
                    'collapsed'
                );

                trigger.setAttribute(
                    'aria-expanded',
                    'false'
                );

            }
        );

    });


    console.log(
        '✓ Virtudes del Bushido inicializadas.'
    );

}
/* =========================================================
   24. INICIALIZACIÓN GENERAL
   ========================================================= */

async function initKudoChile() {

    console.log(
        '🥋 Iniciando Kudo Chile...'
    );


    /* =====================================================
       1. TEMA
       ===================================================== */

    initTheme();


    /* =====================================================
       2. CARGAR PARTIALS
       ===================================================== */

    await loadAllPartials();


    /* =====================================================
       3. INICIALIZAR COMPONENTES
       ===================================================== */

    initBushidoVirtues();

    initBackToTop();

    initInternalLinks();

    initNavbar();

    initScrollAnimations();

    initTooltips();

    makeVideosResponsive();

    initLazyImages();

    updateEventStatus();

    initForms();

  animateStats();

initSearch();

initBushidoVirtues();


    console.log(
        '%c🥋 KUDO CHILE - Sitio Inicializado',
        'color:#2091F9;font-size:16px;font-weight:bold;'
    );

    console.log(
        '%cFederación Chilena de Kudo',
        'color:#b0b0b0;font-size:12px;'
    );

    console.log(
        '%cMiembro de Kudo International Federation',
        'color:#b0b0b0;font-size:10px;'
    );

}


/* =========================================================
   25. EJECUTAR CUANDO EL DOCUMENTO ESTÉ LISTO
   ========================================================= */

if (
    document.readyState ===
    'loading'
) {

    document.addEventListener(
        'DOMContentLoaded',
        initKudoChile,
        {
            once: true
        }
    );

} else {

    initKudoChile();

}