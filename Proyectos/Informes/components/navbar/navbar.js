/**
 * ============================================================
 *  COMPONENTE: navbar
 *  Archivo:    components/navbar/navbar.js
 *  Funciones:  scroll suave para anclas internas (#href)
 *  Reutilizable: sí — sin dependencias externas
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function () {

    /* Scroll suave para todos los links de ancla */
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

});