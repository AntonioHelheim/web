/* =========================================================
   KUDO CHILE
   MAIN.JS

   Este archivo:
   1. Carga los partials HTML
   2. Inicializa Bootstrap
   3. Inicializa navegación
   4. Inicializa animaciones
   5. Configura videos
   6. Configura imágenes
   7. Ejecuta funciones auxiliares
   ========================================================= */


/* =========================================================
   1. CARGAR PARTIALS
   ========================================================= */

async function loadPartial(containerId, filePath) {

    const container = document.getElementById(containerId);

    if (!container) {
        console.error(
            `No existe el contenedor #${containerId}`
        );

        return;
    }

    try {

        const response = await fetch(filePath);

        if (!response.ok) {
            throw new Error(
                `HTTP ${response.status} - ${response.statusText}`
            );
        }

        const html = await response.text();

        container.innerHTML = html;

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
   2. CARGAR TODAS LAS SECCIONES
   ========================================================= */

async function loadAllPartials() {

    await Promise.all([

        loadPartial(
            'navbar-container',
            './partials/navbar.html'
        ),

        loadPartial(
            'home-container',
            './partials/home.html'
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
        )

    ]);

    console.log(
        '✓ Todos los partials fueron cargados.'
    );
}


/* =========================================================
   3. SCROLL Y BOTÓN VOLVER ARRIBA
   ========================================================= */

function initBackToTop() {

    const backToTopBtn =
        document.getElementById('backToTop');

    if (!backToTopBtn) {
        return;
    }

    window.addEventListener('scroll', function () {

        if (window.scrollY > 300) {

            backToTopBtn.classList.add('show');

        } else {

            backToTopBtn.classList.remove('show');

        }

    });


    backToTopBtn.addEventListener('click', function (e) {

        e.preventDefault();

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

    });

}


/* =========================================================
   4. NAVEGACIÓN INTERNA
   ========================================================= */

function initInternalLinks() {

    document
        .querySelectorAll('a[href^="#"]')
        .forEach(anchor => {

            anchor.addEventListener(
                'click',
                function (e) {

                    const href =
                        this.getAttribute('href');

                    if (
                        !href ||
                        href === '#' ||
                        href.startsWith('#modal')
                    ) {
                        return;
                    }


                    const target =
                        document.querySelector(href);

                    if (!target) {
                        return;
                    }


                    e.preventDefault();


                    const navbar =
                        document.querySelector('.navbar');

                    const navbarHeight =
                        navbar
                            ? navbar.offsetHeight
                            : 0;


                    const offsetTop =
                        target.getBoundingClientRect().top +
                        window.scrollY -
                        navbarHeight -
                        20;


                    window.scrollTo({

                        top: offsetTop,

                        behavior: 'smooth'

                    });


                    /* Cerrar menú móvil */

                    const navbarCollapse =
                        document.querySelector(
                            '.navbar-collapse'
                        );


                    if (
                        navbarCollapse &&
                        navbarCollapse.classList.contains('show')
                    ) {

                        const bsCollapse =
                            bootstrap.Collapse.getInstance(
                                navbarCollapse
                            );

                        if (bsCollapse) {

                            bsCollapse.hide();

                        } else {

                            navbarCollapse.classList.remove(
                                'show'
                            );

                        }

                    }

                }
            );

        });

}


/* =========================================================
   5. NAVBAR STICKY
   ========================================================= */

function initNavbar() {

    const navbar =
        document.querySelector('.navbar');

    if (!navbar) {
        return;
    }


    function updateNavbar() {

        if (window.scrollY > 50) {

            navbar.classList.add(
                'navbar-scrolled'
            );

        } else {

            navbar.classList.remove(
                'navbar-scrolled'
            );

        }

    }


    window.addEventListener(
        'scroll',
        updateNavbar
    );


    updateNavbar();

}


/* =========================================================
   6. ANIMACIONES AL HACER SCROLL
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


    const observerOptions = {

        threshold: 0.1,

        rootMargin: '0px 0px -80px 0px'

    };


    const observer =
        new IntersectionObserver(
            function (entries) {

                entries.forEach(entry => {

                    if (entry.isIntersecting) {

                        entry.target.classList.add(
                            'is-visible'
                        );

                        observer.unobserve(
                            entry.target
                        );

                    }

                });

            },
            observerOptions
        );


    elements.forEach(element => {

        element.classList.add(
            'scroll-animation'
        );

        observer.observe(element);

    });

}


/* =========================================================
   7. TOOLTIPS BOOTSTRAP
   ========================================================= */

function initTooltips() {

    if (
        typeof bootstrap === 'undefined'
    ) {
        return;
    }


    const tooltipElements =
        document.querySelectorAll(
            '[data-bs-toggle="tooltip"]'
        );


    tooltipElements.forEach(element => {

        new bootstrap.Tooltip(element);

    });

}


/* =========================================================
   8. VIDEOS RESPONSIVOS
   ========================================================= */

function makeVideosResponsive() {

    const iframes =
        document.querySelectorAll(
            'iframe[src*="youtube"]'
        );


    iframes.forEach(iframe => {

        /* Evitar envolver dos veces */

        if (
            iframe.parentElement &&
            iframe.parentElement.classList.contains('ratio')
        ) {

            return;

        }


        const wrapper =
            document.createElement('div');


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
   9. LAZY LOADING DE IMÁGENES
   ========================================================= */

function initLazyImages() {

    const images =
        document.querySelectorAll(
            'img[data-src]'
        );


    if (!images.length) {
        return;
    }


    if (
        !('IntersectionObserver' in window)
    ) {

        images.forEach(img => {

            img.src =
                img.dataset.src;

        });

        return;

    }


    const imageObserver =
        new IntersectionObserver(
            function (entries, observer) {

                entries.forEach(entry => {

                    if (
                        !entry.isIntersecting
                    ) {
                        return;
                    }


                    const img =
                        entry.target;


                    if (img.dataset.src) {

                        img.src =
                            img.dataset.src;

                        img.removeAttribute(
                            'data-src'
                        );

                    }


                    observer.unobserve(img);

                });

            }
        );


    images.forEach(img => {

        imageObserver.observe(img);

    });

}


/* =========================================================
   10. ESTADO DE EVENTOS
   ========================================================= */

function updateEventStatus() {

    const eventCards =
        document.querySelectorAll(
            '.event-card'
        );


    if (!eventCards.length) {
        return;
    }


    /*
     * Actualmente los eventos del sitio
     * no poseen una fecha técnica mediante
     * data-date.
     *
     * Por lo tanto, no modificamos
     * automáticamente su estado.
     */

    eventCards.forEach(card => {

        card.classList.remove(
            'event-past'
        );

    });

}

/* =========================================================
   11. VALIDACIÓN DE FORMULARIOS
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

                if (!form.checkValidity()) {

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
   12. TEMA
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
   13. ESTADÍSTICAS ANIMADAS
   ========================================================= */

function animateStats() {

    const statNumbers =
        document.querySelectorAll(
            '.stat-number'
        );


    if (!statNumbers.length) {
        return;
    }


    const statsObserver =
        new IntersectionObserver(
            function (entries) {

                entries.forEach(entry => {

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


                    let currentValue = 0;


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

                });

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
   14. FILTRO DE DOJOS POR REGIÓN Y COMUNA
   ========================================================= */

function initSearch() {

    const regionFilter =
        document.getElementById('regionFilter');

    const comunaFilter =
        document.getElementById('comunaFilter');

    const clearButton =
        document.getElementById('clearDojoFilters');

    const dojoCards =
        document.querySelectorAll('.dojo-card');

    const noResults =
        document.getElementById('dojoNoResults');

    const dojoCount =
        document.getElementById('dojoCount');


    if (
        !regionFilter ||
        !comunaFilter ||
        !dojoCards.length
    ) {
        return;
    }


    /* =====================================================
       NORMALIZAR TEXTO
       ===================================================== */

    function normalizeText(text) {

        return text
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();

    }


    /* =====================================================
       GENERAR COMUNAS
       ===================================================== */

    function updateComunaOptions() {

        const selectedRegion =
            regionFilter.value;


        const comunas = new Map();


        dojoCards.forEach(card => {

            const region =
                card.dataset.region || '';

            const comuna =
                card.dataset.comuna || '';

            if (
                !comuna ||
                (
                    selectedRegion !== 'all' &&
                    region !== selectedRegion
                )
            ) {
                return;
            }


            /*
             * Utilizamos el texto visible
             * de la ubicación como nombre.
             */

            const location =
                card.querySelector(
                    '.dojo-detail div span'
                );


            let label =
                comuna
                    .replace(/-/g, ' ')
                    .replace(/\b\w/g, letter =>
                        letter.toUpperCase()
                    );


            if (location) {

                const text =
                    location.textContent.trim();

                /*
                 * Para mantener nombres como
                 * "Maipú" o "Concepción".
                 */

                if (text) {

                    const firstPart =
                        text.split(',')[0].trim();

                    if (
                        normalizeText(firstPart) ===
                        normalizeText(comuna.replace(/-/g, ' '))
                    ) {

                        label = firstPart;

                    }

                }

            }


            comunas.set(comuna, label);

        });


        comunaFilter.innerHTML = '';


        const allOption =
            document.createElement('option');

        allOption.value = 'all';

        allOption.textContent =
            'Todas las comunas';

        comunaFilter.appendChild(allOption);


        Array.from(comunas.entries())
            .sort((a, b) =>
                a[1].localeCompare(
                    b[1],
                    'es'
                )
            )
            .forEach(
                ([value, label]) => {

                    const option =
                        document.createElement('option');

                    option.value = value;

                    option.textContent = label;

                    comunaFilter.appendChild(option);

                }
            );


        comunaFilter.disabled =
            comunas.size === 0;

    }


    /* =====================================================
       FILTRAR
       ===================================================== */

    function filterDojos() {

        const selectedRegion =
            regionFilter.value;

        const selectedComuna =
            comunaFilter.value;


        let visibleCount = 0;


        dojoCards.forEach(card => {

            const cardRegion =
                card.dataset.region || '';

            const cardComuna =
                card.dataset.comuna || '';


            const matchesRegion =
                selectedRegion === 'all' ||
                cardRegion === selectedRegion;


            const matchesComuna =
                selectedComuna === 'all' ||
                cardComuna === selectedComuna;


            const shouldShow =
                matchesRegion &&
                matchesComuna;


            if (shouldShow) {

                card.hidden = false;

                visibleCount++;

            } else {

                card.hidden = true;

            }

        });


        /* =================================================
           ACTUALIZAR CONTADOR
           ================================================= */

        if (dojoCount) {

            dojoCount.textContent =
                visibleCount;

        }


        /* =================================================
           SIN RESULTADOS
           ================================================= */

        if (noResults) {

            noResults.hidden =
                visibleCount !== 0;

        }

    }


    /* =====================================================
       CAMBIO DE REGIÓN
       ===================================================== */

    regionFilter.addEventListener(
        'change',
        function () {

            /*
             * Cada vez que cambia la región,
             * reconstruimos las comunas disponibles.
             */

            updateComunaOptions();

            comunaFilter.value = 'all';

            filterDojos();

        }
    );


    /* =====================================================
       CAMBIO DE COMUNA
       ===================================================== */

    comunaFilter.addEventListener(
        'change',
        function () {

            filterDojos();

        }
    );


    /* =====================================================
       LIMPIAR FILTROS
       ===================================================== */

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


    /* =====================================================
       ESTADO INICIAL
       ===================================================== */

    updateComunaOptions();

    filterDojos();

}

/* =========================================================
   15. DETECCIÓN DE DISPOSITIVO
   ========================================================= */

function isMobile() {

    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i
        .test(navigator.userAgent);

}


/* =========================================================
   16. SMOOTH SCROLL
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


    const start =
        window.scrollY;


    const navbar =
        document.querySelector(
            '.navbar'
        );


    const navbarHeight =
        navbar
            ? navbar.offsetHeight
            : 0;


    const end =
        element.offsetTop -
        navbarHeight -
        20;


    const distance =
        end - start;


    let position =
        start;


    const increment =
        distance /
        (duration / 16);


    function animate() {

        position += increment;


        if (
            (increment > 0 &&
             position < end) ||

            (increment < 0 &&
             position > end)
        ) {

            window.scrollTo(
                0,
                position
            );

            requestAnimationFrame(
                animate
            );

        } else {

            window.scrollTo(
                0,
                end
            );

        }

    }


    animate();

}


/* =========================================================
   17. OBTENER PARÁMETRO URL
   ========================================================= */

function getURLParameter(name) {

    const results =
        new RegExp(
            '[?|&]' +
            name +
            '=' +
            '([^&;]+?)(&|#|;|$)'
        ).exec(
            location.search
        );


    if (!results) {
        return '';
    }


    return decodeURIComponent(
        results[1]
            .replace(/\+/g, '%20')
    );

}


/* =========================================================
   18. VALIDAR EMAIL
   ========================================================= */

function validateEmail(email) {

    const regex =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    return regex.test(email);

}


/* =========================================================
   19. COPIAR AL PORTAPAPELES
   ========================================================= */

function copyToClipboard(text) {

    if (
        !navigator.clipboard
    ) {

        console.error(
            'El navegador no permite copiar al portapapeles.'
        );

        return;

    }


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

}


/* =========================================================
   20. INICIALIZACIÓN GENERAL
   ========================================================= */

async function initKudoChile() {

    console.log(
        '🥋 Iniciando Kudo Chile...'
    );


    /*
     * PRIMERO:
     * Cargar los partials.
     */

    await loadAllPartials();


    /*
     * SEGUNDO:
     * Ahora que los partials existen
     * podemos inicializar JavaScript.
     */

    initBackToTop();

    initInternalLinks();

    initNavbar();

    initScrollAnimations();

    initTooltips();

    makeVideosResponsive();

    initLazyImages();

    updateEventStatus();

    initForms();

    initTheme();

    animateStats();

    initSearch();


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
   21. EJECUTAR CUANDO ESTÉ LISTO EL DOCUMENTO
   ========================================================= */

if (
    document.readyState === 'loading'
) {

    document.addEventListener(
        'DOMContentLoaded',
        initKudoChile
    );

} else {

    initKudoChile();

}