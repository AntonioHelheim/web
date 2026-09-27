document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var i18nNode = document.getElementById('workerDashboardI18n');
    var strings = {};
    try { strings = i18nNode ? JSON.parse(i18nNode.textContent || '{}') : {}; } catch (e) { strings = {}; }
    function S(key, fallback) { return strings[key] || fallback || key; }
    function esc(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
        });
    }
    function setText(id, value) { var el = document.getElementById(id); if (el) el.textContent = String(value == null ? '' : value); }
    function formatDate(value) {
        if (!value) return '';
        var date = new Date(String(value).replace(' ', 'T'));
        if (isNaN(date.getTime())) return '';
        return new Intl.DateTimeFormat(document.documentElement.lang || 'es', {day:'2-digit',month:'2-digit',year:'numeric'}).format(date);
    }
    function formatPeriod(value) {
        var parts = String(value || '').split('-');
        if (parts.length === 3) {
            var dayDate = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            if (isNaN(dayDate.getTime())) return value || '';
            return new Intl.DateTimeFormat(document.documentElement.lang || 'es', {day:'2-digit', month:'short'}).format(dayDate);
        }
        if (parts.length !== 2) return value || '';
        var date = new Date(Number(parts[0]), Number(parts[1]) - 1, 1);
        return new Intl.DateTimeFormat(document.documentElement.lang || 'es', {month:'short', year:'2-digit'}).format(date);
    }

    var categoryDefs = [
        ['induction', S('course','Cursos / inducciones'), 'bi-journal-check'],
        ['audits', S('audit','Auditorías'), 'bi-clipboard2-check'],
        ['self', S('self','Autoevaluaciones'), 'bi-person-check'],
        ['forms', S('form','Formularios'), 'bi-card-text'],
        ['protocols', S('protocol','Protocolos'), 'bi-clipboard2-pulse']
    ];
    var period = document.getElementById('workerPeriod');
    var loading = document.getElementById('workerDashLoading');
    var content = document.getElementById('workerDashContent');
    var alertBox = document.getElementById('workerDashAlert');
    var updated = document.getElementById('workerDashUpdated');
    var hasRendered = false;
    var lastTrendRows = [];
    var lastCategories = {};
    var lastItems = [];
    var expandedCategories = {};

    function statusClass(status) {
        if (status === 'overdue') return 'overdue';
        if (status === 'failed') return 'failed';
        if (status === 'approved' || status === 'complete') return 'approved';
        return 'in-progress';
    }

    function renderCategories(categories, items) {
        lastCategories = categories || {};
        lastItems = Array.isArray(items) ? items.slice() : [];
        var grid = document.getElementById('workerCategoryGrid');
        var progressList = document.getElementById('workerProgressCategoryList');
        if (!grid && !progressList) return;

        var gridHtml = '';
        categoryDefs.forEach(function (def) {
            var row = (categories || {})[def[0]] || {total:0,pending:0,complete:0,overdue:0};
            var total = Number(row.total || 0);
            if (total <= 0) return;
            var pct = Math.max(0, Math.min(100, Math.round((Number(row.complete || 0) / total) * 100)));
            gridHtml += '<article class="dashboard-worker-category-card">' +
                '<span class="dashboard-worker-category-card__icon"><i class="bi ' + esc(def[2]) + '" aria-hidden="true"></i></span>' +
                '<div class="dashboard-worker-category-card__copy"><span>' + esc(def[1]) + '</span>' +
                '<div class="dashboard-worker-category-card__track" aria-hidden="true"><i style="width:' + pct + '%"></i></div></div>' +
                '<b>' + total + '</b></article>';
        });
        if (grid) grid.innerHTML = gridHtml || '<p class="dashboard-empty-state">' + esc(S('no_data','Sin datos')) + '</p>';

        if (!progressList) return;
        var detailHtml = '';
        categoryDefs.forEach(function (def) {
            var row = (categories || {})[def[0]] || {total:0,pending:0,complete:0,overdue:0};
            var total = Number(row.total || 0);
            if (total <= 0) return;
            var pct = Math.max(0, Math.min(100, Math.round((Number(row.complete || 0) / total) * 100)));
            var categoryItems = lastItems.filter(function (item) { return item.category === def[0]; });
            var expanded = expandedCategories[def[0]] === true;
            detailHtml += '<section class="dashboard-worker-progress-group' + (expanded ? ' is-expanded' : '') + '" data-progress-category="' + esc(def[0]) + '">' +
                '<button type="button" class="dashboard-worker-progress-group__toggle" aria-expanded="' + (expanded ? 'true' : 'false') + '">' +
                    '<span class="dashboard-worker-progress-group__name"><i class="bi ' + esc(def[2]) + '" aria-hidden="true"></i><strong>' + esc(def[1]) + '</strong><small>' + total + '</small></span>' +
                    '<span class="dashboard-worker-progress-group__value"><b>' + pct + '%</b><i class="bi ' + (expanded ? 'bi-chevron-up' : 'bi-chevron-down') + '" aria-hidden="true"></i></span>' +
                '</button>' +
                '<div class="dashboard-worker-progress-list__track" aria-hidden="true"><i style="width:' + pct + '%"></i></div>' +
                '<div class="dashboard-worker-progress-group__details"' + (expanded ? '' : ' hidden') + '>';
            if (!categoryItems.length) {
                detailHtml += '<p class="dashboard-empty-state">' + esc(S('no_data','Sin datos')) + '</p>';
            } else {
                categoryItems.forEach(function (item) {
                    var itemPct = Math.max(0, Math.min(100, Number(item.progress || 0)));
                    var deadline = formatDate(item.deadline || '');
                    detailHtml += '<article class="dashboard-worker-progress-activity dashboard-worker-progress-activity--' + statusClass(item.status) + '">' +
                        '<div class="dashboard-worker-progress-activity__head"><span><strong>' + esc(item.name || '') + '</strong><small>' + esc(item.status_label || '') + (deadline ? ' · ' + esc(S('deadline','Vence')) + ' ' + esc(deadline) : '') + '</small></span><b>' + Math.round(itemPct) + '%</b></div>' +
                        '<div class="dashboard-worker-progress-activity__track" aria-hidden="true"><i style="width:' + itemPct + '%"></i></div>' +
                    '</article>';
                });
            }
            detailHtml += '</div></section>';
        });
        progressList.innerHTML = detailHtml || '<p class="dashboard-empty-state">' + esc(S('no_data','Sin datos')) + '</p>';
    }

    function renderTrend(rows) {
        var chart = document.getElementById('workerTrendChart');
        lastTrendRows = Array.isArray(rows) ? rows.slice() : [];
        var legend = document.getElementById('workerTrendLegend');
        if (!chart || !legend) return;
        rows = Array.isArray(rows) ? rows : [];
        if (!rows.length) {
            chart.innerHTML = '<p class="dashboard-empty-state">' + esc(S('no_data','Sin datos')) + '</p>';
            legend.innerHTML = '';
            return;
        }
        var series = [
            ['induction', S('course','Cursos / inducciones'), 'series-induction'],
            ['audits', S('audit','Auditorías'), 'series-audits'],
            ['self', S('self','Autoevaluaciones'), 'series-self'],
            ['protocols', S('protocols','Protocolos ejecutados'), 'series-protocols'],
            ['forms', S('forms','Formularios enviados'), 'series-forms']
        ];
        var width = 900, height = 300, left = 48, right = 20, top = 22, bottom = 52;
        var plotW = width - left - right, plotH = height - top - bottom;
        var max = 1;
        rows.forEach(function (row) { series.forEach(function (s) { max = Math.max(max, Number(row[s[0]] || 0)); }); });
        var x = function (i) { return left + (rows.length === 1 ? plotW / 2 : (i / (rows.length - 1)) * plotW); };
        var y = function (v) { return top + plotH - (Number(v || 0) / max) * plotH; };
        var svg = '<svg viewBox="0 0 ' + width + ' ' + height + '" role="img" aria-label="' + esc(S('activity_progress','Avance de actividades')) + '">';
        [0, .25, .5, .75, 1].forEach(function (ratio) {
            var yy = top + plotH - ratio * plotH;
            svg += '<line class="dashboard-worker-chart-grid" x1="' + left + '" y1="' + yy + '" x2="' + (width-right) + '" y2="' + yy + '"/>';
            svg += '<text class="dashboard-worker-chart-axis" x="' + (left-8) + '" y="' + (yy+4) + '" text-anchor="end">' + Math.round(max*ratio) + '</text>';
        });
        var labelEvery = rows.length > 24 ? Math.ceil(rows.length / 8) : (rows.length > 12 ? 2 : 1);
        rows.forEach(function (row, i) {
            if (i % labelEvery !== 0 && i !== rows.length - 1) return;
            svg += '<text class="dashboard-worker-chart-axis dashboard-worker-chart-axis--x" x="' + x(i) + '" y="' + (height-16) + '" text-anchor="middle">' + esc(formatPeriod(row.periodo || '')) + '</text>';
        });
        series.forEach(function (s) {
            var points = rows.map(function (row, i) { return x(i) + ',' + y(row[s[0]]); }).join(' ');
            svg += '<polyline class="dashboard-worker-chart-line ' + s[2] + '" points="' + points + '"/>';
            rows.forEach(function (row, i) {
                var value = Number(row[s[0]] || 0);
                svg += '<circle class="dashboard-worker-chart-point ' + s[2] + '" cx="' + x(i) + '" cy="' + y(value) + '" r="4.5" tabindex="0"><title>' + esc(s[1] + ' · ' + formatPeriod(row.periodo || '') + ': ' + value) + '</title></circle>';
            });
        });
        svg += '</svg>';
        chart.innerHTML = svg;
        legend.innerHTML = series.map(function (s) { return '<span class="' + s[2] + '"><i></i>' + esc(s[1]) + '</span>'; }).join('');
    }

    function render(data) {
        var personal = ((data || {}).personal || {});
        var activity = personal.activities || {};
        var statuses = personal.statuses || {};
        var total = Math.max(0, Number(activity.total || 0));
        var overdue = Number(statuses.overdue || 0);
        var inProgress = Number(statuses.in_progress || 0);
        var approved = Number(statuses.approved || 0);
        var failed = Number(statuses.failed || 0);
        setText('workerMetricTotal', total);
        setText('workerMetricInProgress', inProgress);
        setText('workerMetricOverdue', overdue);
        setText('workerMetricApproved', approved);
        setText('workerMetricFailed', failed);

        function setStateBar(id, value) {
            var barEl = document.getElementById(id);
            if (!barEl) return;
            var pctValue = total > 0 ? Math.max(0, Math.min(100, (Number(value || 0) / total) * 100)) : 0;
            barEl.style.width = pctValue + '%';
        }
        setStateBar('workerInProgressBar', inProgress);
        setStateBar('workerOverdueBar', overdue);
        setStateBar('workerApprovedBar', approved);
        setStateBar('workerFailedBar', failed);

        var stateDonut = document.getElementById('workerStateDonut');
        if (stateDonut) {
            var overduePct = total > 0 ? (overdue / total) * 100 : 0;
            var progressPct = total > 0 ? (inProgress / total) * 100 : 0;
            var approvedPct = total > 0 ? (approved / total) * 100 : 0;
            var failedPct = total > 0 ? (failed / total) * 100 : 0;
            stateDonut.style.setProperty('--state-overdue-end', overduePct + '%');
            stateDonut.style.setProperty('--state-progress-end', (overduePct + progressPct) + '%');
            stateDonut.style.setProperty('--state-approved-end', (overduePct + progressPct + approvedPct) + '%');
            stateDonut.style.setProperty('--state-failed-end', (overduePct + progressPct + approvedPct + failedPct) + '%');
            stateDonut.setAttribute('aria-label', [S('in_progress','En curso') + ': ' + inProgress,S('overdue','Vencidas') + ': ' + overdue,S('approved','Aprobadas') + ': ' + approved,S('failed','Reprobadas') + ': ' + failed].join('. '));
        }

        var pct = Math.max(0, Math.min(100, Number(activity.progress || 0)));
        var ring = document.getElementById('workerProgressRing');
        if (ring) {
            ring.style.setProperty('--progress', pct);
            ring.setAttribute('aria-valuenow', String(Math.round(pct)));
            var strong = ring.querySelector('strong');
            if (strong) strong.textContent = (Math.round(pct * 10) / 10).toString().replace('.0','') + '%';
        }
        var bar = document.getElementById('workerProgressBar');
        if (bar) bar.style.width = pct + '%';

        var next = document.getElementById('workerNextDeadline');
        if (next) {
            var formattedNext = formatDate(activity.next_due || '');
            var strongDate = next.querySelector('strong');
            var dueMeta = next.querySelector('em');
            if (strongDate) strongDate.textContent = formattedNext || '—';
            if (dueMeta) dueMeta.textContent = formattedNext ? S('due_short', dueMeta.textContent || '') : S('no_due', dueMeta.textContent || '');
        }
        renderCategories(personal.categories || activity.by_category || {}, personal.items || []);
        renderTrend((data || {}).tendencia || []);
    }

    function restoreScrollPosition(scrollY) {
        if (scrollY === null || typeof scrollY === 'undefined') return;
        var restore = function () { window.scrollTo({top: scrollY, behavior: 'auto'}); };
        if (!window.requestAnimationFrame) { restore(); return; }
        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(restore);
        });
    }

    async function load(preservePosition) {
        var previousScrollY = preservePosition ? (window.scrollY || window.pageYOffset || 0) : null;
        if (loading) loading.classList.remove('d-none');
        if (content && !hasRendered) content.classList.add('d-none');
        if (alertBox) alertBox.classList.add('d-none');
        try {
            var value = period ? (period.value || '90') : '90';
            var response = await fetch('./indicadores.php?period=' + encodeURIComponent(value), {headers:{'Accept':'application/json'}});
            var json = await response.json();
            if (!json || json.success !== true) throw new Error((json && json.message) || S('error','No se pudo cargar el panel.'));
            render(json.data || {});
            if (updated) {
                var now = new Date();
                var time = new Intl.DateTimeFormat(document.documentElement.lang || 'es', {hour:'2-digit',minute:'2-digit'}).format(now);
                updated.textContent = S('updated','Actualizado') + ' ' + time;
            }
            if (content) content.classList.remove('d-none');
            hasRendered = true;
            restoreScrollPosition(previousScrollY);
        } catch (error) {
            if (alertBox) {
                alertBox.textContent = error && error.message ? error.message : S('error','No se pudo cargar el panel.');
                alertBox.classList.remove('d-none');
            }
        } finally {
            if (loading) loading.classList.add('d-none');
        }
    }

    var progressList = document.getElementById('workerProgressCategoryList');
    if (progressList) {
        progressList.addEventListener('click', function (event) {
            var toggle = event.target.closest('.dashboard-worker-progress-group__toggle');
            if (!toggle) return;
            var group = toggle.closest('[data-progress-category]');
            if (!group) return;
            var key = group.getAttribute('data-progress-category') || '';
            expandedCategories[key] = !expandedCategories[key];
            renderCategories(lastCategories, lastItems);
        });
    }

    document.addEventListener('sct:theme-change', function () { if (lastTrendRows.length) renderTrend(lastTrendRows); });
    if (period) period.addEventListener('change', function () { load(true); });
    load(false);
    window.setInterval(function () { if (!document.hidden) load(false); }, 60000);
});
