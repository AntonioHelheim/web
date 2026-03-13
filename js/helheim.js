const CACHE_VERSION = "2025-03-13-0515";

document.addEventListener('DOMContentLoaded', function () {

    /* Scroll suave para anclas */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
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

    /* Reset Ken Burns al cambiar slide */
    document.querySelectorAll('.carousel').forEach(carousel => {
        carousel.addEventListener('slide.bs.carousel', () => {
            carousel.querySelectorAll('.carousel-item img').forEach(img => {
                img.style.transform = '';
            });
        });
    });

    /* Scroll Top — mostrar/ocultar */
    const btn = document.getElementById('backToTop');
    if (btn) {
        window.addEventListener('scroll', () => {
            btn.classList.toggle('visible', window.scrollY > 200);
        });
        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

});