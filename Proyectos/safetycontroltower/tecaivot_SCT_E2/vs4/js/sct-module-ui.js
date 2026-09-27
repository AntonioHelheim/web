(function () {
    'use strict';
    if (!document.body || !document.body.classList.contains('sct-module-page')) return;

    var context = document.querySelector('.sct-module-context');
    if (context) {
        var role = context.getAttribute('data-role') || '';
        var mode = context.getAttribute('data-module-mode') || '';
        if (role) document.body.classList.add('sct-role-' + role.replace(/[^a-z0-9_-]/gi, ''));
        if (mode) document.body.classList.add('sct-mode-' + mode.replace(/[^a-z0-9_-]/gi, ''));
    }

    var lang = (document.documentElement.lang || 'es').toLowerCase().slice(0, 2);
    var hints = {
        es: 'Desliza horizontalmente para ver más columnas.',
        en: 'Swipe horizontally to see more columns.',
        pt: 'Deslize horizontalmente para ver mais colunas.',
        fr: 'Faites glisser horizontalement pour voir plus de colonnes.',
        zh: '横向滑动可查看更多列。'
    };
    var hintText = hints[lang] || hints.es;

    function refreshTables() {
        document.querySelectorAll('.table-responsive').forEach(function (wrapper) {
            var isScrollable = wrapper.scrollWidth > wrapper.clientWidth + 4;
            wrapper.classList.toggle('sct-table-scrollable', isScrollable);
            var existing = wrapper.nextElementSibling;
            var isHint = existing && existing.classList.contains('sct-table-hint');
            if (isScrollable && !isHint) {
                var hint = document.createElement('div');
                hint.className = 'sct-table-hint';
                hint.setAttribute('aria-hidden', 'true');
                hint.innerHTML = '<i class="bi bi-arrow-left-right"></i><span></span>';
                hint.querySelector('span').textContent = hintText;
                wrapper.insertAdjacentElement('afterend', hint);
            } else if (!isScrollable && isHint) {
                existing.remove();
            }
        });
    }

    refreshTables();
    window.addEventListener('resize', function () {
        window.clearTimeout(window.__sctModuleResizeTimer);
        window.__sctModuleResizeTimer = window.setTimeout(refreshTables, 120);
    }, { passive: true });
})();

/* ==========================================================
   P13 / V39 — FILTROS DE ESTADO PARA ACTIVIDADES PERSONALES
   ========================================================== */
(function () {
    'use strict';
    if (!document.body || !document.body.classList.contains('sct-module-page')) return;

    var lang = (document.documentElement.lang || 'es').toLowerCase().slice(0, 2);
    var labels = {
        es: {all:'Todas', overdue:'Atrasadas', incomplete:'Incompletas', complete:'Completas', global:'Global'},
        en: {all:'All', overdue:'Overdue', incomplete:'Incomplete', complete:'Completed', global:'Global'},
        pt: {all:'Todas', overdue:'Em atraso', incomplete:'Incompletas', complete:'Concluídas', global:'Global'},
        fr: {all:'Toutes', overdue:'En retard', incomplete:'Incomplètes', complete:'Terminées', global:'Global'},
        zh: {all:'全部', overdue:'已逾期', incomplete:'未完成', complete:'已完成', global:'全局'}
    };
    var L = labels[lang] || labels.es;

    function enhanceStatusList(list) {
        if (!list || list.dataset.sctStatusFilterReady === '1') return;
        list.dataset.sctStatusFilterReady = '1';
        var active = 'all';
        var bar = document.createElement('div');
        bar.className = 'sct-personal-status-filter';
        bar.setAttribute('role', 'group');
        bar.setAttribute('aria-label', L.all);
        list.insertAdjacentElement('beforebegin', bar);

        function cards() {
            return Array.prototype.slice.call(list.querySelectorAll(':scope > [data-personal-status]'));
        }
        function refresh() {
            var rows = cards();
            if (!rows.length) { bar.hidden = true; return; }
            bar.hidden = false;
            var counts = {all: rows.length, overdue:0, incomplete:0, complete:0};
            rows.forEach(function (card) {
                var status = card.getAttribute('data-personal-status') || 'incomplete';
                if (Object.prototype.hasOwnProperty.call(counts, status)) counts[status] += 1;
                card.hidden = active !== 'all' && status !== active;
            });
            var icons = {all:'bi-grid', overdue:'bi-clock-history', incomplete:'bi-hourglass-split', complete:'bi-check2-circle'};
            bar.innerHTML = ['all','overdue','incomplete','complete'].map(function (key) {
                var selected = key === active;
                return '<button type="button" class="sct-personal-filter-pill' + (selected ? ' is-active' : '') + '" data-personal-filter="' + key + '" aria-pressed="' + (selected ? 'true' : 'false') + '"><i class="bi ' + icons[key] + '" aria-hidden="true"></i>' + L[key] + '<span>' + counts[key] + '</span></button>';
            }).join('');
            Array.prototype.forEach.call(bar.querySelectorAll('[data-personal-filter]'), function (button) {
                button.addEventListener('click', function () {
                    active = button.getAttribute('data-personal-filter') || 'all';
                    refresh();
                });
            });
        }
        new MutationObserver(refresh).observe(list, {childList:true});
        refresh();
    }

    // P43: Mis inducciones no recibe un filtro secundario aquí; el filtro detallado vive en Bienvenida.
    ['myAuditsList','mySelfList','myProtocolsList'].forEach(function (id) {
        enhanceStatusList(document.getElementById(id));
    });

    // Mis Formularios usa un filtro de alcance propio (Todas / Global / Empresa)
    // generado desde formularios-mis.js para conservar el nombre real de la empresa.
}());

/* P43: la ayuda contextual se renderiza exclusivamente con sctInfoTip() desde PHP.
   La interacción permanece delegada en partials/app-navbar.php. */
