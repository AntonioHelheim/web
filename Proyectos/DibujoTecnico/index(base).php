<?php
/*
|--------------------------------------------------------------------------
| Configuración de errores (CAMBIAR EN PRODUCCIÓN)
|--------------------------------------------------------------------------
*/
if (getenv('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}

/*
|--------------------------------------------------------------------------
| Conexión
|--------------------------------------------------------------------------
*/
require __DIR__ . '/pages/dblocalhost.php';/*LocalHost*/
/*require __DIR__ . '/pages/db.php';/*Produccion*/
date_default_timezone_set('America/Santiago');

/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
*/
$results = [];
$error = null;
$searched = false;

/*
|--------------------------------------------------------------------------
| Procesamiento de búsqueda
|--------------------------------------------------------------------------
*/
$patente = strtoupper(trim($_GET['patente'] ?? ''));
$patente = preg_replace('/[^A-Z0-9]/', '', $patente);
if ($patente !== '') {
    $searched = true;
    if (!preg_match('/^[A-Z]{4}[0-9]{2}$|^[A-Z]{2}[0-9]{4}$/', $patente)) {
        $error = "Formato de patente inválido.";
    } else {
        $stmt = $conn->prepare("
            SELECT 
                field_279,
                field_280,
                field_281,
                field_282,
                field_327,
                field_331
            FROM app_entity_28
            WHERE field_279 = ?
            ORDER BY date_added DESC
        ");
        if (!$stmt) {
            $error = "Error interno al preparar la consulta.";
        } else {
            $stmt->bind_param("s", $patente);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $results = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            } else {
                $error = "Error al ejecutar la consulta.";
            }
            $stmt->close();
        }
    }
}

function formatearFecha($valor) {
    if (empty($valor) || !is_numeric($valor)) {
        return '-';
    }
    $timestamp = (int)$valor;
    if ($timestamp > 9999999999) {
        $timestamp /= 1000;
    }
    $fecha = new DateTime();
    $fecha->setTimestamp($timestamp);
    static $formatter = null;
    if ($formatter === null) {
        $formatter = new IntlDateFormatter(
            'es_CL',
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT,
            'America/Santiago',
            IntlDateFormatter::GREGORIAN,
            "d 'de' MMMM 'de' yyyy - HH:mm 'hrs'"
        );
    }
    return $formatter->format($fecha);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="author" content="Juan Antonio Concha L. & Maite Lazcano"/>
    <meta name="copyright" content="Helheim Tierra del Fuego"/>
    <meta name="robots" content="index"/>

    <link rel="shortcut icon" href="https://www.helheim.cl//images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="https://www.helheim.cl//images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://www.helheim.cl//images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://www.helheim.cl//images/favicon-16x16.png">

    <title>Helheim.cl</title>
    <meta name="title" content="Helheim Tierra del Fuego"/>
    <meta name="description" content="Gestion de proyectos, Desarrollo Web, Diseño Industrial y Diseño Grafico"/>

    <meta property="og:type" content="website"/>
    <meta property="og:url" content="https://www.helheim.cl"/>
    <meta property="og:title" content="Helheim Tierra del Fuego"/>
    <meta property="og:description" content="Gestion de proyectos, Desarrollo Web, Diseño Industrial y Diseño Grafico"/>
    <meta property="og:image" content="https://www.helheim.cl/images/Logo_Helheim-Metadata_1200x640.png"/>

    <meta property="twitter:card" content="summary_large_image"/>
    <meta property="twitter:url" content="https://www.helheim.cl"/>
    <meta property="twitter:title" content="Helheim Tierra del Fuego"/>
    <meta property="twitter:description" content="Gestion de proyectos, Desarrollo Web, Diseño Industrial y Diseño Grafico"/>
    <meta property="twitter:image" content="https://www.helheim.cl/images/Logo_Helheim-Metadata_1200x640.png"/>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,500;1,600&family=Bebas+Neue&family=Barlow:wght@300;400;500;600&family=Barlow+Condensed:wght@400;700&display=swap" rel="stylesheet">
    <link href="./css/styles_v_1.css?v=2025-10-02-1925" rel="stylesheet"/>

    <style>
        /* ===========================
           VARIABLES & BASE
        =========================== */
        :root {
            --clr-bg:        #080a08;
            --clr-surface:   #0e1210;
            --clr-border:    #1f2b1f;
            --clr-accent:    #22c55e;
            --clr-accent-dk: #15803d;
            --clr-accent-lt: #4ade80;
            --clr-danger:    #ef4444;
            --clr-text:      #e2e8e2;
            --clr-muted:     #6b7c6b;
            --font-display:  'Bebas Neue', sans-serif;
            --font-body:     'Barlow', sans-serif;
            --font-cond:     'Barlow Condensed', sans-serif;
            --font-serif:    'Playfair Display', serif;
            --radius:        4px;
            --transition:    .25s cubic-bezier(.4,0,.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            background-color: var(--clr-bg);
            color: var(--clr-text);
            font-family: var(--font-body);
            font-weight: 300;
            overflow-x: hidden;
        }

        /* Noise overlay para profundidad */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity: .025;
            pointer-events: none;
            z-index: 9999;
        }

        /* ===========================
           NAVBAR
        =========================== */
        .navbar {
            background-color: rgba(8,10,8,.92) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--clr-border);
            padding: .75rem 1.25rem;
        }

        .navbar-brand {
            font-family: var(--font-display);
            font-size: 1.5rem !important;
            letter-spacing: .1em;
            color: var(--clr-accent) !important;
            text-transform: uppercase;
            transition: color var(--transition), letter-spacing var(--transition);
        }
        .navbar-brand:hover {
            color: var(--clr-accent-lt) !important;
            letter-spacing: .16em;
        }

        .navbar-nav .nav-link {
            font-family: var(--font-cond);
            font-weight: 400;
            font-size: .85rem !important;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--clr-muted) !important;
            padding: .4rem .75rem !important;
            border-radius: var(--radius);
            transition: color var(--transition), background var(--transition);
            position: relative;
        }
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0; left: .75rem; right: .75rem;
            height: 1px;
            background: var(--clr-accent);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform var(--transition);
        }
        .navbar-nav .nav-link:hover {
            color: var(--clr-accent-lt) !important;
            background: rgba(34,197,94,.06);
        }
        .navbar-nav .nav-link:hover::after { transform: scaleX(1); }

        /* Social icons en el nav */
        .navbar-nav .nav-social-row a {
            color: var(--clr-muted);
            font-size: 1.3rem;
            transition: color var(--transition), transform var(--transition);
            display: inline-block;
        }
        .navbar-nav .nav-social-row a:hover {
            color: var(--clr-danger);
            transform: scale(1.2);
        }

        .navbar-toggler {
            border-color: var(--clr-border) !important;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(34,197,94,0.9)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        /* Etiqueta de sección en el nav */
        .nav-section-label {
            font-family: var(--font-cond);
            font-size: .7rem;
            letter-spacing: .15em;
            color: var(--clr-accent) !important;
            text-transform: uppercase;
            opacity: .6;
            padding: 1.2rem .75rem .3rem;
        }

        /* ===========================
           CAROUSEL
        =========================== */
        #CarouselDesktopContainer {
            position: relative;
        }
        #CarouselDesktopContainer::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 120px;
            background: linear-gradient(to bottom, transparent, var(--clr-bg));
            pointer-events: none;
            z-index: 2;
        }
        #CarouselDesktop .carousel-item img,
        #CarouselMovil .carousel-item img {
            transition: transform 6s ease;
        }
        #CarouselDesktop .carousel-item.active img,
        #CarouselMovil .carousel-item.active img {
            transform: scale(1.03);
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: invert(1) sepia(1) saturate(5) hue-rotate(90deg);
        }

        /* ===========================
           SECCIÓN PRINCIPAL
        =========================== */
        .main-section {
            padding: 3.5rem 0 5rem;
        }

        /* Título de sección */
        .section-heading {
            font-family: var(--font-display);
            font-size: clamp(2rem, 5vw, 3.5rem);
            letter-spacing: .08em;
            color: var(--clr-text);
            line-height: 1;
        }
        .section-heading span {
            color: var(--clr-accent);
        }

        .section-rule {
            width: 3rem;
            height: 2px;
            background: var(--clr-accent);
            margin: 1rem 0 2rem;
        }


        /* ===========================
           FOOTER
        =========================== */
        .footer-helheim {
            background: var(--clr-surface);
            border-top: 1px solid var(--clr-border);
            padding: 2.5rem 0;
            margin-top: 5rem;
        }

        .footer-brand {
            font-family: var(--font-display);
            font-size: 1.6rem;
            letter-spacing: .1em;
            color: var(--clr-accent);
        }

        .footer-rule {
            border-color: var(--clr-border) !important;
            margin: 1.25rem 0;
        }

        .footer-text {
            font-family: var(--font-cond);
            font-size: .8rem;
            letter-spacing: .08em;
            color: var(--clr-muted);
            line-height: 1.8;
        }
        .footer-text a {
            color: var(--clr-accent);
            text-decoration: none;
            transition: color var(--transition);
        }
        .footer-text a:hover { color: var(--clr-accent-lt); }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.1rem;
            height: 2.1rem;
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            color: var(--clr-muted);
            font-size: 1rem;
            transition: color var(--transition), border-color var(--transition), transform var(--transition);
            text-decoration: none;
        }
        .footer-social a:hover {
            color: var(--clr-danger);
            border-color: var(--clr-danger);
            transform: translateY(-2px);
        }

        /* ===========================
           UTILITIES
        =========================== */
        .text-accent { color: var(--clr-accent) !important; }
        .divider-line {
            border: none;
            border-top: 1px solid var(--clr-border);
            margin: 2rem 0;
        }


        /* ===========================
   BOTÓN SCROLL UP
=========================== */

.back-to-top {
    position: fixed;
    right: 22px;
    bottom: 22px;

    width: 46px;
    height: 46px;

    border-radius: var(--radius);
    border: 1px solid var(--clr-border);

    background: var(--clr-surface);
    color: var(--clr-accent);

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 1.2rem;

    cursor: pointer;

    opacity: 0;
    visibility: hidden;

    transform: translateY(10px);

    transition:
        opacity .25s ease,
        transform .25s ease,
        background .25s ease,
        border-color .25s ease;
}

/* visible al hacer scroll */
.back-to-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* hover */
.back-to-top:hover {
    background: var(--clr-accent);
    border-color: var(--clr-accent);
    color: #000;
    box-shadow: 0 0 18px rgba(34,197,94,.35);
}

/* ===========================
   BOTÓN WHATSAPP
=========================== */

.whatsapp-float {
    position: fixed;
    left: 22px;
    bottom: 22px;

    width: 46px;
    height: 46px;

    border-radius: var(--radius);
    border: 1px solid var(--clr-border);

    background: var(--clr-surface);
    color: var(--clr-accent);

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 1.25rem;

    text-decoration: none;

    transition:
        background .25s ease,
        border-color .25s ease,
        transform .2s ease,
        box-shadow .25s ease;
}

.whatsapp-float:hover {
    background: var(--clr-accent);
    border-color: var(--clr-accent);
    color: #000;
    transform: translateY(-2px);
    box-shadow: 0 0 18px rgba(34,197,94,.35);
}

    </style>
</head>
<body>

<!-- ==================== HEADER ==================== -->
<header id="header">
    <nav class="navbar navbar-dark fixed-top">
        <div class="container-fluid px-3 px-md-4">
            <a class="navbar-brand" href="#">Helheim.cl</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto py-3 py-md-0">
                    <li class="nav-item">
                        <a href="https://www.helheim.cl" class="nav-link">
                            <i class="bi bi-globe me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="https://www.helheim.cl/section/01_CTI_ISDW_IS.html" class="nav-link">
                            <i class="bi bi-laptop me-1"></i> Consultoría TI
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="https://www.helheim.cl/section/10_DIN_IyC_DG.html" class="nav-link">
                            <i class="bi bi-palette me-1"></i> Diseño Industrial
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#formcontacto" class="nav-link">
                            <i class="bi bi-envelope me-1"></i> Contacto
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="https://helheim.cl/ruko/index.php?module=users/login" target="_blank" class="nav-link">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Intranet
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="https://helheim.cl:2096/" target="_blank" class="nav-link">
                            <i class="bi bi-envelope-open me-1"></i> Correo
                        </a>
                    </li>

                    <li class="nav-section-label mt-2">Redes & Contacto</li>

                    <li class="nav-item px-2 pb-2">
                        <div class="d-flex gap-3 nav-social-row ps-1 pt-1">
                            <a href="https://wa.me/56958453672?text=Hola, quiero saber mas sobre los Helheim y sus servicios"
                               target="_blank" aria-label="WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://www.instagram.com/helheim_tierra_del_fuego/" target="_blank" aria-label="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="https://www.linkedin.com/company/103153651/" target="_blank" aria-label="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <a href="https://web.facebook.com/HelheimTierradelfuego/" target="_blank" aria-label="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<!-- ==================== END HEADER ==================== -->

<!-- ==================== Carousel Desktop ==================== -->
<div id="CarouselDesktopContainer" class="overflow-hidden d-none d-md-block" style="max-height: 650px;">
    <div id="CarouselDesktop" class="carousel slide h-100" data-bs-ride="carousel">
        <div class="carousel-inner h-100">
            <div class="carousel-item active h-100" data-bs-interval="3000">
                <img src="https://www.helheim.cl/images/desk/007-bannerdesk-scanner.png"
                     class="d-block w-100 h-100"
                     style="object-fit: cover; object-position: center;"
                     alt="BannerEscanerVehicular">
            </div>
            <div class="carousel-item h-100" data-bs-interval="3000">
                <img src="https://www.helheim.cl/images/desk/007-bannerdesk-scanner.png"
                     class="d-block w-100 h-100"
                     style="object-fit: cover; object-position: center;"
                     alt="BannerEscanerVehicular">
            </div>
            <div class="carousel-item h-100" data-bs-interval="3000">
                <img src="https://www.helheim.cl/images/desk/007-bannerdesk-scanner.png"
                     class="d-block w-100 h-100"
                     style="object-fit: cover; object-position: center;"
                     alt="BannerEscanerVehicular">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#CarouselDesktop" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#CarouselDesktop" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>
<!-- ==================== END Carousel Desktop ==================== -->

<!-- ==================== Carousel Móvil ==================== -->
<div id="CarouselMovil" class="carousel slide d-block d-md-none" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active" data-bs-interval="3000">
            <img src="https://www.helheim.cl/images/movil/007-bannerdesk-scanner.png"
                 class="d-block w-100" alt="BannerEscanerVehicular">
        </div>
        <div class="carousel-item" data-bs-interval="3000">
            <img src="https://www.helheim.cl/images/movil/007-bannerdesk-scanner.png"
                 class="d-block w-100" alt="BannerEscanerVehicular">
        </div>
        <div class="carousel-item" data-bs-interval="3000">
            <img src="https://www.helheim.cl/images/movil/007-bannerdesk-scanner.png"
                 class="d-block w-100" alt="BannerEscanerVehicular">
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#CarouselMovil" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#CarouselMovil" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
<!-- ==================== END Carousel Móvil ==================== -->


<!-- ==================== MAIN ==================== -->
<main class="main-section">
    <div class="container">





    </div>
</main>
<!-- ==================== END MAIN ==================== -->



<!-- ==================== FOOTER ==================== -->
<footer class="footer-helheim">
    <div class="container">
        <div class="text-center">
            <div class="footer-brand mb-1">Helheim.cl</div>
            <!-- <p class="footer-text mb-2">Tierra del Fuego &mdash; Porvenir</p> -->
        </div>

        <hr class="footer-rule">

        <div class="text-center">
            <div class="footer-social d-flex justify-content-center gap-2 mb-3">
                <a href="https://wa.me/56958453672?text=Hola, quiero saber mas sobre los Helheim y sus servicios"
                   target="_blank" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                <a href="https://www.instagram.com/helheim_tierra_del_fuego/"
                   target="_blank" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="https://www.linkedin.com/company/103153651/"
                   target="_blank" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                <a href="https://web.facebook.com/HelheimTierradelfuego/"
                   target="_blank" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            </div>

            <div class="footer-text">
                <p class="mb-1">
                    &copy; <a href="https://helheim.cl" target="_blank">Helheim</a>
                    &nbsp;&mdash;&nbsp; Fundada en 2023 en Tierra del Fuego, Porvenir
                </p>
                <p class="mb-1">CONSULTORÍA HELHEIM TIERRA DEL FUEGO LIMITADA &nbsp;&middot;&nbsp; RUT: 77.742.346-0</p>
                <p class="mb-0" style="opacity:.5;">Últ. act. 12-marzo-2026</p>
            </div>
        </div>
    </div>
</footer>
<!-- ==================== END FOOTER ==================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="./js/main.js?v=2025-10-02-1925"></script>

<script>
    /* Auto-uppercase en el input de patente */
    document.querySelectorAll('input[name="patente"]').forEach(el => {
        el.addEventListener('input', () => {
            const pos = el.selectionStart;
            el.value = el.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            el.setSelectionRange(pos, pos);
        });
    });

    /* Ken Burns en carousel: reset al cambiar slide */
    document.querySelectorAll('.carousel').forEach(carousel => {
        carousel.addEventListener('slide.bs.carousel', e => {
            const items = carousel.querySelectorAll('.carousel-item img');
            items.forEach(img => { img.style.transform = ''; });
        });
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

  const backToTopBtn = document.getElementById('backToTop');

  if (backToTopBtn) {

    window.addEventListener('scroll', function () {

      if (window.scrollY > 200) {
        backToTopBtn.classList.add('visible');
      } else {
        backToTopBtn.classList.remove('visible');
      }

    });

    backToTopBtn.addEventListener('click', function () {

      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });

    });

  }

  /* Scroll suave para anclas */

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {

    anchor.addEventListener('click', function(e) {

      const target = document.querySelector(this.getAttribute('href'));

      if (target) {

        e.preventDefault();

        window.scrollTo({
          top: target.getBoundingClientRect().top + window.scrollY - 72,
          behavior: 'smooth'
        });

      }

    });

  });

});
</script>

<!-- Botón volver arriba -->
<button id="backToTop" class="back-to-top" aria-label="Volver arriba">
    <i class="bi bi-arrow-up"></i>
</button>

<a href="https://wa.me/56958453672?text=Hola, quiero agendar un escaneo vehicular"
   class="whatsapp-float"
   target="_blank"
   aria-label="WhatsApp">
   <i class="bi bi-whatsapp"></i>
</a>


</body>
</html>

<?php
$conn->close();
?>