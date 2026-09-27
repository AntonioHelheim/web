/**
 * SCT — Mi espacio / Mis actividades
 * P51: HUB único de Usuario Demo. Conserva tipo/estado/orden/scroll y ejecuta los
 * motores existentes dentro del HUB, sin duplicar lógica funcional.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var hub = document.querySelector('[data-activity-hub]');
    if (!hub) return;

    var body = document.body;
    var browse = hub.querySelector('[data-activity-browse]');
    var workspace = hub.querySelector('[data-activity-workspace]');
    var backButton = hub.querySelector('[data-activity-back]');
    var frameShell = hub.querySelector('[data-frame-shell]');
    var frame = hub.querySelector('[data-activity-frame]');
    var frameLoading = hub.querySelector('[data-frame-loading]');
    var completeDetail = hub.querySelector('[data-complete-detail]');
    var workspaceType = hub.querySelector('[data-workspace-type]');
    var workspaceTitle = hub.querySelector('[data-workspace-title]');
    var workspaceStatus = hub.querySelector('[data-workspace-status]');
    var completeTitle = hub.querySelector('[data-complete-title]');
    var completeStatus = hub.querySelector('[data-complete-status]');
    var completeDeadline = hub.querySelector('[data-complete-deadline]');
    var completeDeadlineRow = hub.querySelector('[data-complete-deadline-row]');
    var completeDate = hub.querySelector('[data-complete-date]');
    var completeDateRow = hub.querySelector('[data-complete-date-row]');
    var empty = document.getElementById('activityFilterEmpty');
    var categoryPills = Array.prototype.slice.call(hub.querySelectorAll('[data-category-filter]'));
    var statusPills = Array.prototype.slice.call(hub.querySelectorAll('[data-status-filter]'));
    var cards = Array.prototype.slice.call(hub.querySelectorAll('[data-personal-card]'));
    var cardGrid = hub.querySelector('[data-personal-card-grid]');
    var sortSelect = hub.querySelector('[data-activity-sort]');

    var currentCategory = hub.dataset.initialCategory || 'all';
    var currentStatus = hub.dataset.initialStatus || 'all';
    var currentSort = hub.dataset.initialSort || 'priority';
    var initialFrom = hub.dataset.initialFrom || '';
    var initialSource = hub.dataset.initialSource || '';
    var initialId = parseInt(hub.dataset.initialId || '0', 10) || 0;
    var browseScrollY = null;
    var activityOpenedClientSide = false;

    function cardFor(source, id) {
        return cards.find(function (card) {
            return card.dataset.activitySource === source && Number(card.dataset.activityId || 0) === Number(id || 0);
        }) || null;
    }

    function mergeHistoryState(extra) {
        var base = window.history.state && typeof window.history.state === 'object' ? window.history.state : {};
        var next = {};
        Object.keys(base).forEach(function (key) { next[key] = base[key]; });
        Object.keys(extra || {}).forEach(function (key) { next[key] = extra[key]; });
        return next;
    }

    function updateBrowseUrl() {
        if (!window.history || !window.history.replaceState) return;
        var url = new URL(window.location.href);
        url.searchParams.delete('activity_source');
        url.searchParams.delete('activity_id');
        url.searchParams.delete('from');
        url.searchParams.delete('status');
        url.searchParams.delete('detail_status');
        if (currentCategory === 'all') url.searchParams.delete('category');
        else url.searchParams.set('category', currentCategory);
        if (currentStatus === 'all') url.searchParams.delete('activity_status');
        else url.searchParams.set('activity_status', currentStatus);
        if (currentSort === 'priority') url.searchParams.delete('sort');
        else url.searchParams.set('sort', currentSort);
        url.hash = 'activities-list';
        window.history.replaceState(mergeHistoryState({
            sctHubBrowse: {
                scrollY: window.scrollY || window.pageYOffset || 0,
                category: currentCategory,
                status: currentStatus,
                sort: currentSort
            }
        }), '', url.toString());
    }

    function numericData(card, key, fallback) {
        var value = Number(card.dataset[key]);
        return Number.isFinite(value) ? value : fallback;
    }

    function compareCards(a, b) {
        if (currentSort === 'deadline') {
            var deadlineDiff = numericData(a, 'sortDeadline', 9999999999) - numericData(b, 'sortDeadline', 9999999999);
            if (deadlineDiff !== 0) return deadlineDiff;
            return numericData(a, 'sortPriority', 9) - numericData(b, 'sortPriority', 9);
        }
        if (currentSort === 'status') {
            var statusDiff = numericData(a, 'sortStatus', 9) - numericData(b, 'sortStatus', 9);
            if (statusDiff !== 0) return statusDiff;
            return numericData(a, 'sortDeadline', 9999999999) - numericData(b, 'sortDeadline', 9999999999);
        }
        var priorityDiff = numericData(a, 'sortPriority', 9) - numericData(b, 'sortPriority', 9);
        if (priorityDiff !== 0) return priorityDiff;
        return numericData(a, 'sortDeadline', 9999999999) - numericData(b, 'sortDeadline', 9999999999);
    }

    function sortCards() {
        if (!cardGrid || !cards.length) return;
        cards.slice().sort(compareCards).forEach(function (card) {
            cardGrid.appendChild(card);
        });
    }

    function matchesStatus(card, status) {
        if (status === 'all') return true;
        var detail = card.dataset.detailStatus || '';
        if (status === 'completed') return detail === 'approved' || detail === 'complete';
        return detail === status;
    }

    function refreshStatusCounts() {
        var counts = {all:0, overdue:0, in_progress:0, completed:0, failed:0};
        cards.forEach(function (card) {
            var categoryMatch = currentCategory === 'all' || card.dataset.category === currentCategory;
            if (!categoryMatch) return;
            counts.all += 1;
            var detail = card.dataset.detailStatus || '';
            if (detail === 'approved' || detail === 'complete') counts.completed += 1;
            else if (Object.prototype.hasOwnProperty.call(counts, detail)) counts[detail] += 1;
        });
        statusPills.forEach(function (pill) {
            var count = pill.querySelector('[data-status-count]');
            var key = pill.dataset.statusFilter || 'all';
            if (count) count.textContent = String(counts[key] || 0);
        });
    }

    function applyFilters(updateUrl) {
        var visible = 0;
        categoryPills.forEach(function (pill) {
            var active = pill.dataset.categoryFilter === currentCategory;
            pill.classList.toggle('is-active', active);
            pill.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        statusPills.forEach(function (pill) {
            var active = pill.dataset.statusFilter === currentStatus;
            pill.classList.toggle('is-active', active);
            pill.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        refreshStatusCounts();
        cards.forEach(function (card) {
            var categoryMatch = currentCategory === 'all' || card.dataset.category === currentCategory;
            var statusMatch = matchesStatus(card, currentStatus);
            card.hidden = !(categoryMatch && statusMatch);
            if (categoryMatch && statusMatch) visible += 1;
        });
        sortCards();
        if (sortSelect && sortSelect.value !== currentSort) sortSelect.value = currentSort;
        if (empty) empty.hidden = visible !== 0;
        if (updateUrl) updateBrowseUrl();
    }

    categoryPills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            currentCategory = pill.dataset.categoryFilter || 'all';
            applyFilters(true);
        });
    });

    statusPills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            currentStatus = pill.dataset.statusFilter || 'all';
            applyFilters(true);
        });
    });

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            currentSort = ['priority','deadline','status'].indexOf(sortSelect.value) !== -1 ? sortSelect.value : 'priority';
            applyFilters(true);
        });
    }

    function hideFrame() {
        if (frame) {
            try { frame.src = 'about:blank'; } catch (_) {}
        }
        if (frameShell) frameShell.hidden = true;
        if (frameLoading) frameLoading.hidden = false;
    }

    function showComplete(card) {
        hideFrame();
        if (!completeDetail) return;
        completeDetail.hidden = false;
        if (completeTitle) completeTitle.textContent = card.dataset.activityName || '';
        if (completeStatus) completeStatus.textContent = card.dataset.activityStatusLabel || '';
        var deadline = card.dataset.activityDeadline || '';
        var completed = card.dataset.activityCompleted || '';
        if (completeDeadlineRow) completeDeadlineRow.hidden = !deadline;
        if (completeDeadline) completeDeadline.textContent = deadline;
        if (completeDateRow) completeDateRow.hidden = !completed;
        if (completeDate) completeDate.textContent = completed;
    }

    function prepareEmbeddedFrame() {
        if (!frame) return;
        try {
            var doc = frame.contentDocument;
            if (!doc) return;
            doc.documentElement.classList.add('sct-inside-activity-hub');
            var dark = document.documentElement.classList.contains('sct-theme-dark');
            doc.documentElement.classList.toggle('sct-theme-dark', dark);
            doc.documentElement.setAttribute('data-sct-theme', dark ? 'dark' : 'light');
            if (doc.body) doc.body.classList.add('sct-inside-activity-hub');

            doc.addEventListener('click', function (event) {
                var target = event.target && event.target.closest ? event.target.closest('#executionExit') : null;
                if (!target) return;
                event.preventDefault();
                event.stopPropagation();
                if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
                closeActivity();
            }, true);
        } catch (_) {
            // El contenido es same-origin; si el navegador impide acceso, el motor sigue funcionando dentro del iframe.
        }
    }

    function showFrame(card) {
        if (completeDetail) completeDetail.hidden = true;
        if (!frameShell || !frame) return;
        var src = card.dataset.moduleHref || '';
        if (!src) {
            showComplete(card);
            return;
        }
        frameShell.hidden = false;
        if (frameLoading) frameLoading.hidden = false;
        frame.onload = function () {
            if (frameLoading) frameLoading.hidden = true;
            prepareEmbeddedFrame();
        };
        frame.src = src;
    }

    function setWorkspaceHeader(card) {
        if (workspaceType) workspaceType.textContent = card.dataset.activityType || '';
        if (workspaceTitle) workspaceTitle.textContent = card.dataset.activityName || '';
        if (workspaceStatus) {
            workspaceStatus.textContent = card.dataset.activityStatusLabel || '';
            workspaceStatus.dataset.state = card.dataset.detailStatus || '';
        }
    }

    function openActivity(card, pushHistory) {
        if (!card || !workspace) return;
        if (card.dataset.activityScheduled === '1') {
            if (pushHistory && typeof card.focus === 'function') card.focus();
            return;
        }
        var source = card.dataset.activitySource || '';
        var id = parseInt(card.dataset.activityId || '0', 10) || 0;
        if (!source || !id) return;

        if (pushHistory) {
            browseScrollY = window.scrollY || window.pageYOffset || 0;
            window.history.replaceState(mergeHistoryState({
                sctHubBrowse: {
                    scrollY: browseScrollY,
                    category: currentCategory,
                    status: currentStatus,
                    sort: currentSort
                }
            }), '', window.location.href);

            var url = new URL(window.location.href);
            url.searchParams.set('activity_source', source);
            url.searchParams.set('activity_id', String(id));
            url.searchParams.set('from', 'space');
            url.hash = '';
            window.history.pushState({
                sctHubActivity: true,
                source: source,
                id: id
            }, '', url.toString());
            activityOpenedClientSide = true;
        }

        body.classList.add('sct-hub-activity-mode');
        workspace.hidden = false;
        if (browse) browse.setAttribute('aria-hidden', 'true');
        setWorkspaceHeader(card);

        if (card.dataset.activityComplete === '1') showComplete(card);
        else showFrame(card);

        window.requestAnimationFrame(function () {
            window.scrollTo({ top: 0, behavior: 'auto' });
            if (backButton && typeof backButton.focus === 'function') {
                try { backButton.focus({ preventScroll: true }); } catch (_) { backButton.focus(); }
            }
        });
    }

    function restoreBrowseFromState(state) {
        var saved = state && state.sctHubBrowse ? state.sctHubBrowse : null;
        if (saved) {
            currentCategory = saved.category || currentCategory;
            currentStatus = saved.status || currentStatus;
            currentSort = saved.sort || currentSort;
            applyFilters(false);
            browseScrollY = typeof saved.scrollY === 'number' ? saved.scrollY : browseScrollY;
        }
        window.requestAnimationFrame(function () {
            if (browseScrollY !== null) window.scrollTo({ top: browseScrollY, behavior: 'auto' });
        });
    }

    function closeWorkspace(stateToRestore) {
        body.classList.remove('sct-hub-activity-mode');
        if (workspace) workspace.hidden = true;
        if (browse) browse.removeAttribute('aria-hidden');
        if (completeDetail) completeDetail.hidden = true;
        hideFrame();
        restoreBrowseFromState(stateToRestore || window.history.state || {});
    }

    function fallbackToBrowse() {
        var url = new URL(window.location.href);
        url.searchParams.delete('activity_source');
        url.searchParams.delete('activity_id');
        url.searchParams.delete('from');
        window.history.replaceState(mergeHistoryState({ sctHubActivity: false }), '', url.toString());
        closeWorkspace(window.history.state || {});
    }

    function closeActivity() {
        if (activityOpenedClientSide) {
            window.history.back();
            return;
        }
        if (initialFrom && document.referrer) {
            window.history.back();
            return;
        }
        fallbackToBrowse();
    }

    if (backButton) backButton.addEventListener('click', closeActivity);

    hub.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('[data-open-hub-activity]') : null;
        if (!link) return;
        var card = link.closest('[data-personal-card]');
        if (!card) return;
        event.preventDefault();
        openActivity(card, true);
    });

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || !event.data) return;
        if (event.data.type === 'sct-activity-updated') {
            try { window.sessionStorage.setItem('sct:personal-activity-refresh', '1'); } catch (_) {}
            return;
        }
        if (event.data.type !== 'sct-activity-exit') return;
        try { window.sessionStorage.setItem('sct:personal-activity-refresh', '1'); } catch (_) {}
        closeActivity();
    });

    document.addEventListener('sct:theme-change', function () {
        prepareEmbeddedFrame();
    });

    window.addEventListener('popstate', function (event) {
        var url = new URL(window.location.href);
        var source = url.searchParams.get('activity_source') || '';
        var id = parseInt(url.searchParams.get('activity_id') || '0', 10) || 0;
        if (source && id) {
            var card = cardFor(source, id);
            if (card) openActivity(card, false);
            return;
        }
        activityOpenedClientSide = false;
        closeWorkspace(event.state || {});
        try {
            if (window.sessionStorage.getItem('sct:personal-activity-refresh') === '1') {
                window.sessionStorage.removeItem('sct:personal-activity-refresh');
                window.location.reload();
            }
        } catch (_) {}
    });

    applyFilters(false);

    if (initialSource && initialId > 0) {
        var initialCard = cardFor(initialSource, initialId);
        if (initialCard && initialCard.dataset.activityScheduled !== '1') {
            window.history.replaceState(mergeHistoryState({
                sctHubActivity: true,
                source: initialSource,
                id: initialId
            }), '', window.location.href);
            openActivity(initialCard, false);
        } else {
            fallbackToBrowse();
        }
    } else {
        body.classList.remove('sct-hub-activity-mode');
        if (workspace) workspace.hidden = true;
        restoreBrowseFromState(window.history.state || {});
    }
});
