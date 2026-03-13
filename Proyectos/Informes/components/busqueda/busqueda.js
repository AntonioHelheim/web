/**
 * ============================================================
 *  COMPONENTE: busqueda
 *  Archivo:    components/busqueda/busqueda.js
 *  Funciones:  auto-uppercase + sanitización del input patente
 *              reset Ken Burns al cambiar slide del carousel
 *  Reutilizable: sí — sin dependencias externas
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Auto-uppercase + solo A-Z 0-9 en el input ── */
    document.querySelectorAll('input[name="patente"]').forEach(el => {
        el.addEventListener('input', () => {
            const pos = el.selectionStart;
            el.value = el.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            el.setSelectionRange(pos, pos);
        });
    });

    /* ── Reset Ken Burns al cambiar de slide ─────── */
    document.querySelectorAll('.carousel').forEach(carousel => {
        carousel.addEventListener('slide.bs.carousel', () => {
            carousel.querySelectorAll('.carousel-item img').forEach(img => {
                img.style.transform = '';
            });
        });
    });

});