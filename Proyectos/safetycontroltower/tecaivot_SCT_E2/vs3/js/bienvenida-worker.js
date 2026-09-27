document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var greeting = document.querySelector('[data-worker-greeting]');
    if (greeting) {
        var hour = new Date().getHours();
        var text = hour < 12 ? greeting.dataset.morning : (hour < 19 ? greeting.dataset.afternoon : greeting.dataset.evening);
        var greetingLabel = greeting.querySelector('[data-worker-greeting-label]');
        if (greetingLabel) greetingLabel.textContent = text + ',';
    }

    var buttons = Array.prototype.slice.call(document.querySelectorAll('[data-worker-activity-filter]'));
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-worker-activity-item]'));
    var empty = document.querySelector('[data-worker-activity-empty]');
    var activityGrid = document.querySelector('[data-worker-activity-grid]');
    var mobileActivityLimit = activityGrid ? Math.max(1, Number(activityGrid.getAttribute('data-worker-mobile-limit') || 3)) : 3;
    var mobileActivitiesQuery = window.matchMedia ? window.matchMedia('(max-width: 599.98px)') : null;

    var statusDataNode = document.getElementById('workerActivityStatusData');
    var statusSummary = { counts: {}, labels: {} };
    try { statusSummary = statusDataNode ? JSON.parse(statusDataNode.textContent || '{}') : statusSummary; } catch (_) {}
    var statusSummaryCards = Array.prototype.slice.call(document.querySelectorAll('[data-worker-status-summary]'));

    function refreshStatusSummary(filter) {
        if (!statusSummaryCards.length) return;
        var counts = (statusSummary.counts || {})[filter] || (statusSummary.counts || {}).all || {};
        var labels = (statusSummary.labels || {})[filter] || (statusSummary.labels || {}).all || {};
        var visibleCount = 0;
        statusSummaryCards.forEach(function (card) {
            var key = card.getAttribute('data-worker-status-summary') || '';
            var countNode = card.querySelector('[data-worker-status-count]');
            var labelNode = card.querySelector('[data-worker-status-label]');
            if (countNode) countNode.textContent = String(Number(counts[key] || 0));
            if (labelNode && labels[key]) labelNode.textContent = labels[key];
            // Formularios y protocolos no tienen un estado de reprobación propio.
            card.hidden = key === 'failed' && (filter === 'forms' || filter === 'protocols');
            if (!card.hidden) visibleCount += 1;
        });
        var guides = Array.prototype.slice.call(document.querySelectorAll('.welcome-status-guide--activity-summary'));
        guides.forEach(function (guide) {
            var visibleInGuide = Array.prototype.slice.call(guide.querySelectorAll('[data-worker-status-summary]')).filter(function (card) { return !card.hidden; }).length;
            guide.setAttribute('data-status-card-count', String(visibleInGuide || visibleCount));
        });
    }

    function saveWelcomeReturnState() {
        if (!window.history || !window.history.replaceState) return;
        var currentState = window.history.state && typeof window.history.state === 'object' ? window.history.state : {};
        var nextState = {};
        Object.keys(currentState).forEach(function (key) { nextState[key] = currentState[key]; });
        var active = buttons.find(function (button) { return button.classList.contains('is-active'); });
        nextState.sctWelcome = {
            scrollY: window.scrollY || window.pageYOffset || 0,
            activityType: active ? (active.getAttribute('data-worker-activity-filter') || 'all') : 'all'
        };
        window.history.replaceState(nextState, '', window.location.href);
    }

    function restoreWelcomeReturnState() {
        var state = window.history.state && window.history.state.sctWelcome ? window.history.state.sctWelcome : null;
        if (!state) return;
        var filter = state.activityType || 'all';
        if (buttons.some(function (button) { return button.getAttribute('data-worker-activity-filter') === filter; })) {
            applyFilter(filter, false);
        }
        if (typeof state.scrollY === 'number') {
            window.requestAnimationFrame(function () {
                window.scrollTo({ top: state.scrollY, behavior: 'auto' });
            });
        }
    }

    function applyFilter(filter, updateUrl) {
        var matched = 0;
        var shown = 0;
        var limitForMobile = mobileActivitiesQuery ? mobileActivitiesQuery.matches : window.innerWidth < 600;
        buttons.forEach(function (button) {
            var active = button.getAttribute('data-worker-activity-filter') === filter;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        var visibleItems = [];
        items.forEach(function (item) {
            var matchesFilter = filter === 'all' || item.getAttribute('data-category') === filter;
            if (matchesFilter) matched += 1;
            var show = matchesFilter && (!limitForMobile || shown < mobileActivityLimit);
            item.hidden = !show;
            item.classList.toggle('is-mobile-preview-hidden', matchesFilter && !show);
            item.classList.remove('is-desktop-tail-wide');
            if (show) {
                shown += 1;
                visibleItems.push(item);
            }
        });
        if (!limitForMobile && visibleItems.length > 1 && visibleItems.length % 3 === 1) {
            visibleItems[visibleItems.length - 1].classList.add('is-desktop-tail-wide');
        }
        if (empty) empty.hidden = matched !== 0;
        refreshStatusSummary(filter);

        if (updateUrl && window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            if (filter === 'all') url.searchParams.delete('activity_type');
            else url.searchParams.set('activity_type', filter);
            url.hash = 'worker-activities';
            var currentState = window.history.state && typeof window.history.state === 'object' ? window.history.state : {};
            window.history.replaceState(currentState, '', url.toString());
        }
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            applyFilter(button.getAttribute('data-worker-activity-filter') || 'all', true);
        });
    });

    function reapplyActiveFilter() {
        var active = buttons.find(function (button) { return button.classList.contains('is-active'); });
        applyFilter(active ? (active.getAttribute('data-worker-activity-filter') || 'all') : 'all', false);
    }
    if (mobileActivitiesQuery) {
        if (typeof mobileActivitiesQuery.addEventListener === 'function') mobileActivitiesQuery.addEventListener('change', reapplyActiveFilter);
        else if (typeof mobileActivitiesQuery.addListener === 'function') mobileActivitiesQuery.addListener(reapplyActiveFilter);
    }

    if (buttons.length) {
        var requested = new URLSearchParams(window.location.search).get('activity_type') || 'all';
        if (!buttons.some(function (button) { return button.getAttribute('data-worker-activity-filter') === requested; })) requested = 'all';
        applyFilter(requested, false);
        restoreWelcomeReturnState();
    }

    // Conserva el punto exacto de Bienvenida también en accesos directos a Mi espacio.
    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('a[href*="api/usuarios/mis-actividades.php"]') : null;
        if (!link || link.hasAttribute('data-start-activity')) return;
        saveWelcomeReturnState();
    });

    // Confirmación única antes de abrir una actividad pendiente o vencida.
    var dialog = document.getElementById('welcomeActivityStartDialog');
    if (!dialog) return;
    var dialogType = dialog.querySelector('[data-start-dialog-type]');
    var dialogName = dialog.querySelector('[data-start-dialog-name]');
    var dialogOverdue = dialog.querySelector('[data-start-dialog-overdue]');
    var dialogCancel = dialog.querySelector('[data-start-dialog-cancel]');
    var dialogConfirm = dialog.querySelector('[data-start-dialog-confirm]');
    var pendingHref = '';
    var lastTrigger = null;

    function closeDialog() {
        if (typeof dialog.close === 'function' && dialog.open) dialog.close();
        else dialog.removeAttribute('open');
        pendingHref = '';
        if (lastTrigger && typeof lastTrigger.focus === 'function') lastTrigger.focus();
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('[data-start-activity][data-requires-confirmation="1"]') : null;
        if (!link) return;
        event.preventDefault();
        pendingHref = link.href || link.getAttribute('href') || '';
        lastTrigger = link;
        if (dialogType) dialogType.textContent = link.getAttribute('data-activity-type-label') || '';
        if (dialogName) {
            var due = link.getAttribute('data-activity-deadline') || '';
            dialogName.textContent = (link.getAttribute('data-activity-name') || '') + (due ? ' · ' + due : '');
        }
        if (dialogOverdue) dialogOverdue.hidden = link.getAttribute('data-activity-overdue') !== '1';
        if (dialogConfirm) dialogConfirm.disabled = false;
        if (typeof dialog.showModal === 'function') dialog.showModal();
        else dialog.setAttribute('open', '');
        if (dialogCancel) dialogCancel.focus();
    });

    if (dialogCancel) dialogCancel.addEventListener('click', closeDialog);
    if (dialogConfirm) {
        dialogConfirm.addEventListener('click', function () {
            if (!pendingHref) return;
            dialogConfirm.disabled = true;
            saveWelcomeReturnState();
            window.location.assign(pendingHref);
        });
    }
    dialog.addEventListener('cancel', function (event) {
        event.preventDefault();
        closeDialog();
    });
    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) closeDialog();
    });
});
