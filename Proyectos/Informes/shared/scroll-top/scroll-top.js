/**
 * ============================================================
 *  COMPONENTE: scroll-top  (utilidad compartida)
 *  Archivo:    shared/scroll-top/scroll-top.js
 *  Funciones:  mostrar/ocultar botón al hacer scroll
 *              scroll suave al top al hacer clic
 *  Reutilizable: sí — sin dependencias externas
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function () {

    const btn = document.getElementById('backToTop');
    if (!btn) return;

    /* Mostrar u ocultar según posición de scroll */
    window.addEventListener('scroll', function () {
        btn.classList.toggle('visible', window.scrollY > 200);
    });

    /* Scroll suave al hacer clic */
    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

});