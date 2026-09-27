/** Safety Control Tower — comportamiento común de creación para roles de gestión. */
(function () {
    'use strict';
    function init() {
        const params = new URLSearchParams(window.location.search || '');
        if (params.get('action') !== 'create') return;
        const target = document.querySelector('[data-sct-create-panel]');
        if (!target) return;
        window.setTimeout(function () {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            const first = target.querySelector('input:not([type="hidden"]), textarea, select');
            if (first && typeof first.focus === 'function') first.focus({ preventScroll: true });
        }, 180);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
