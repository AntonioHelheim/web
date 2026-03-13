<!-- ============================================================
     COMPONENTE: hero
     Archivo:    components/hero/hero.php
     Depende de: hero.css
     Nota:       Para cambiar imágenes editar sólo este archivo.
     ============================================================ -->

<!-- Carousel Desktop -->
<div id="CarouselDesktopContainer" class="overflow-hidden d-none d-md-block" style="max-height:650px;">
    <div id="CarouselDesktop" class="carousel slide h-100" data-bs-ride="carousel">
        <div class="carousel-inner h-100">
            <div class="carousel-item active h-100" data-bs-interval="3000">
                <img src="https://www.helheim.cl/images/desk/007-bannerdesk-scanner.png"
                     class="d-block w-100 h-100"
                     style="object-fit:cover; object-position:center;"
                     alt="Escáner Vehicular Helheim">
            </div>
            <div class="carousel-item h-100" data-bs-interval="3000">
                <img src="https://www.helheim.cl/images/desk/007-bannerdesk-scanner.png"
                     class="d-block w-100 h-100"
                     style="object-fit:cover; object-position:center;"
                     alt="Escáner Vehicular Helheim">
            </div>
            <div class="carousel-item h-100" data-bs-interval="3000">
                <img src="https://www.helheim.cl/images/desk/007-bannerdesk-scanner.png"
                     class="d-block w-100 h-100"
                     style="object-fit:cover; object-position:center;"
                     alt="Escáner Vehicular Helheim">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#CarouselDesktop" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#CarouselDesktop" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>
</div>

<!-- Carousel Móvil -->
<div id="CarouselMovil" class="carousel slide d-block d-md-none" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active" data-bs-interval="3000">
            <img src="https://www.helheim.cl/images/movil/007-bannerdesk-scanner.png"
                 class="d-block w-100" alt="Escáner Vehicular Helheim">
        </div>
        <div class="carousel-item" data-bs-interval="3000">
            <img src="https://www.helheim.cl/images/movil/007-bannerdesk-scanner.png"
                 class="d-block w-100" alt="Escáner Vehicular Helheim">
        </div>
        <div class="carousel-item" data-bs-interval="3000">
            <img src="https://www.helheim.cl/images/movil/007-bannerdesk-scanner.png"
                 class="d-block w-100" alt="Escáner Vehicular Helheim">
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#CarouselMovil" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#CarouselMovil" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
</div>