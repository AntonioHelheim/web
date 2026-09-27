/** Safety Control Tower - Dashboard por rol / P77 */
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('.dashboard-shell[data-is-global-admin]');
    if (!root) return;

    const $ = function (id) { return document.getElementById(id); };
    const isGlobalAdmin = root.dataset.isGlobalAdmin === '1';
    const pageRole = root.dataset.role || 'trabajador';
    const isWorkerRole = pageRole === 'trabajador';
    let I18N = {};
    try {
        I18N = JSON.parse(($('dashboardI18n') || {}).textContent || '{}');
    } catch (_) {}
    const S = function (key, fallback) { return I18N[key] || fallback || key; };

    const companySelect = $('companySelect');
    const periodFilter = $('periodFilter');
    const projectFilter = $('projectFilter');
    const centerFilter = $('centerFilter');
    const projectFilterWrap = $('projectFilterWrap');
    const centerFilterWrap = $('centerFilterWrap');
    const resetFilters = $('resetFilters');
    const dashAlert = $('dashAlert');
    const dashStatus = $('dashStatus');
    const dashContent = $('dashContent');
    const filterScopeNote = $('filterScopeNote');
    const dashboardLiveStatus = $('dashboardLiveStatus');
    const rankingSection = $('rankingSection');
    const programsSection = $('programsSection');
    const personalProgressSection = $('personalProgressSection');
    const summaryActivity = $('summaryActivity');
    const summarySteps = $('summarySteps');
    const summaryCalendar = $('summaryCalendar');
    const summaryNotifications = $('summaryNotifications');
    const operationsState = $('operationsState');
    const keyProgressChart = $('keyProgressChart');
    const keyProgressMeta = $('keyProgressMeta');
    const filterPanel = $('dashboardFilterPanel');
    const perModuleSections = document.querySelectorAll('.dashboard-section.per-module');
    const dashboardTabButtons = Array.prototype.slice.call(document.querySelectorAll('[data-dashboard-tab]'));
    const dashboardTabPanels = Array.prototype.slice.call(document.querySelectorAll('[data-dashboard-tab-panel]'));

    function activateDashboardTab(name, focusButton) {
        const available = dashboardTabButtons.some(function (button) {
            return button.getAttribute('data-dashboard-tab') === name;
        });
        const target = available ? name : 'summary';
        dashboardTabButtons.forEach(function (button) {
            const selected = button.getAttribute('data-dashboard-tab') === target;
            button.classList.toggle('is-active', selected);
            button.setAttribute('aria-selected', selected ? 'true' : 'false');
            button.setAttribute('tabindex', selected ? '0' : '-1');
            if (selected && focusButton) button.focus();
        });
        dashboardTabPanels.forEach(function (panel) {
            const selected = panel.getAttribute('data-dashboard-tab-panel') === target;
            panel.hidden = !selected;
            panel.classList.toggle('is-active', selected);
        });
    }

    dashboardTabButtons.forEach(function (button, index) {
        button.addEventListener('click', function () {
            activateDashboardTab(button.getAttribute('data-dashboard-tab') || 'summary', false);
        });
        button.addEventListener('keydown', function (event) {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            let next = index;
            if (event.key === 'ArrowLeft') next = (index - 1 + dashboardTabButtons.length) % dashboardTabButtons.length;
            if (event.key === 'ArrowRight') next = (index + 1) % dashboardTabButtons.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = dashboardTabButtons.length - 1;
            activateDashboardTab(dashboardTabButtons[next].getAttribute('data-dashboard-tab') || 'summary', true);
        });
    });
    activateDashboardTab('summary', false);
    // P69: el abatible lo gestiona exclusivamente sct-collapsible.js para evitar flechas duplicadas.
    installChartToggleButtons();

    let currentCompanyId = null;
    let requestSerial = 0;
    let lastDashboardData = null;
    let lastDashboardRole = pageRole;
    let lastDashboardConsolidated = false;

    const dashboardModuleRoutes = {
        events: '../eventos/gestion-eventos.php',
        induction: '../induccion/gestion-induccion.php',
        audits: '../auditorias/gestion-auditorias.php',
        self: '../autoevaluaciones/gestion-autoevaluaciones.php',
        protocols: '../protocolos/gestion-protocolos.php',
        forms: '../formularios/gestion-formularios.php',
        programs: '../programas/gestion-programas.php',
        workers: '../trabajadores/gestion-trabajadores.php',
        users: '../usuarios/gestion-usuarios.php',
        projects: '../proyectos/gestion-proyectos.php',
        companies: '../empresas/gestion-empresas.php'
    };

    if (filterPanel && window.matchMedia('(max-width: 767.98px)').matches) {
        filterPanel.open = false;
    }

    function isConsolidated() {
        return currentCompanyId === '__all__';
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function clampPct(value) {
        const number = Number(value || 0);
        return Math.max(0, Math.min(100, Number.isFinite(number) ? number : 0));
    }

    function formatPct(value) {
        const number = Number(value);
        if (!Number.isFinite(number)) return '—';
        return (Math.round(number * 10) / 10).toString().replace('.0', '') + '%';
    }


    function chartColorFromToken(token, index) {
        const explicit = String(token || '');
        if (explicit && !explicit.startsWith('fill-')) return explicit;
        const map = {
            'fill-good': 'var(--success-dark)',
            'fill-warn': 'var(--accent)',
            'fill-bad': '#aa2424',
            'fill-neutral': '#9eadb8',
            'fill-info': 'var(--primary)',
            'fill-dark': 'var(--primary-darker)'
        };
        if (map[explicit]) return map[explicit];
        const palette = ['var(--primary)', 'var(--success-dark)', 'var(--accent)', '#44549B', '#AA2424', '#60798B', '#2A7C89', '#8C6A2E'];
        return palette[index % palette.length];
    }

    function currentChartMode(container, fallback) {
        if (!container) return fallback || 'bars';
        return container.dataset.dashboardChartMode || fallback || 'bars';
    }

    function chartToggleButton(container) {
        if (!container || !container.id) return null;
        return document.querySelector('[data-dashboard-chart-toggle="' + container.id + '"]');
    }

    function syncChartToggleButton(container, mode) {
        const button = chartToggleButton(container);
        if (!button) return;
        const model = container.__dashboardChartModel || {};
        let primary = 'bars';
        let alternative = 'donut';
        if (model.kind === 'operations') alternative = 'radial';
        if (model.kind === 'program-progress') alternative = 'columns';
        const nextMode = mode === alternative ? primary : alternative;
        const labels = {
            bars: S('chart_switch_to_bars', 'Cambiar a gráfico de barras'),
            donut: S('chart_switch_to_donut', 'Cambiar a gráfico circular'),
            radial: S('chart_switch_to_radial', 'Cambiar a gráfico radial'),
            columns: S('chart_switch_to_columns', 'Cambiar a gráfico de columnas')
        };
        const icons = {
            bars: 'bi-bar-chart',
            donut: 'bi-pie-chart',
            radial: 'bi-bullseye',
            columns: 'bi-bar-chart-line'
        };
        button.setAttribute('aria-label', labels[nextMode] || labels.bars);
        button.setAttribute('title', labels[nextMode] || labels.bars);
        button.setAttribute('aria-pressed', mode === alternative ? 'true' : 'false');
        const icon = button.querySelector('i');
        if (icon) icon.className = 'bi ' + (icons[nextMode] || icons.bars);
    }

    function categoricalDonutMarkup(rows, total, centerLabel) {
        const safeRows = (rows || []).map(function (row, index) {
            return {
                label: String(row[0] || ''),
                value: Math.max(0, Number(row[1] || 0)),
                color: chartColorFromToken(row[2], index)
            };
        });
        const safeTotal = Math.max(0, Number(total || 0));
        let cursor = 0;
        const segments = [];
        safeRows.forEach(function (row) {
            const pct = safeTotal > 0 ? (row.value / safeTotal) * 100 : 0;
            const end = Math.min(100, cursor + pct);
            segments.push(row.color + ' ' + cursor + '% ' + end + '%');
            cursor = end;
        });
        if (cursor < 100) segments.push('rgba(158,173,184,.20) ' + cursor + '% 100%');
        const bg = 'conic-gradient(' + segments.join(',') + ')';
        const aria = safeRows.map(function (row) { return row.label + ': ' + row.value; }).join(', ');
        return '<div class="dashboard-switch-donut">'
            + '<div class="dashboard-switch-donut__chart" tabindex="0" style="background:' + bg + '" role="img" aria-label="' + escapeHtml(aria) + '">'
            + '<div class="dashboard-switch-donut__center"><strong>' + safeTotal + '</strong><span>' + escapeHtml(centerLabel || S('summary_title', 'Total')) + '</span></div></div>'
            + '<div class="dashboard-switch-donut__legend">'
            + safeRows.map(function (row) {
                const pct = safeTotal > 0 ? Math.round((row.value / safeTotal) * 100) : 0;
                return '<div class="dashboard-switch-donut__legend-row" tabindex="0" role="group" aria-label="' + escapeHtml(row.label + ': ' + row.value + ' (' + pct + '%)') + '">'
                    + '<i style="background:' + row.color + '"></i><span>' + escapeHtml(row.label) + '</span><strong>' + row.value + '</strong><small>' + pct + '%</small></div>';
            }).join('')
            + '</div></div>';
    }

    function renderCategoricalBars(container, model) {
        container.innerHTML = '';
        const total = Math.max(0, Number(model.total || 0));
        if (!total) {
            container.innerHTML = '<p class="text-muted small mb-0">' + escapeHtml(S('no_data', 'Sin datos para el período seleccionado.')) + '</p>';
            return;
        }
        const cap = Math.max(1, Number(model.limit || 6));
        const visibleRows = model.rows.slice(0, cap);
        const extraRows = model.rows.slice(cap);

        function createBarRow(row, index) {
            const label = row[0];
            const value = Number(row[1] || 0);
            const token = String(row[2] || '');
            const cls = token.startsWith('fill-') ? token : '';
            const color = chartColorFromToken(token, index || 0);
            const pct = total > 0 ? Math.round((value / total) * 100) : 0;
            const el = document.createElement('div');
            el.className = 'dashboard-bar-row';
            el.tabIndex = 0;
            el.setAttribute('role', 'group');
            el.setAttribute('aria-label', label + ': ' + value + ' (' + pct + '%)');
            el.innerHTML = '<span class="dashboard-bar-label" title="' + escapeHtml(label) + '">' + escapeHtml(label) + '</span>'
                + '<span class="dashboard-bar-track" role="progressbar" aria-label="' + escapeHtml(label) + '" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + pct + '"><span class="dashboard-bar-fill ' + cls + '" style="width:' + pct + '%;background:' + escapeHtml(color) + '"></span></span>'
                + '<span class="dashboard-bar-value">' + value + '</span>';
            return el;
        }

        visibleRows.forEach(function (row, index) { container.appendChild(createBarRow(row, index)); });
        if (extraRows.length) {
            const extra = document.createElement('div');
            extra.className = 'dashboard-expandable-extra dashboard-bar-extra';
            extra.dataset.dashboardExtra = '';
            extra.hidden = true;
            extraRows.forEach(function (row, index) { extra.appendChild(createBarRow(row, cap + index)); });
            container.appendChild(extra);
            const holder = document.createElement('div');
            holder.innerHTML = listToggleMarkup(extraRows.length, 'data-dashboard-toggle="collection"');
            const button = holder.firstElementChild;
            button.dataset.extraCount = String(extraRows.length);
            container.appendChild(button);
        }
    }

    function renderCategoricalPoints(container, model) {
        container.innerHTML = '';
        const rows = model.rows || [];
        const total = Math.max(0, Number(model.total || 0));
        if (!rows.length || !total) {
            container.innerHTML = '<p class="text-muted small mb-0">' + escapeHtml(S('no_data', 'Sin datos para el período seleccionado.')) + '</p>';
            return;
        }
        const cap = Math.max(1, Number(model.limit || 6));
        const visibleRows = rows.slice(0, cap);
        const extraRows = rows.slice(cap);
        const maxValue = Math.max.apply(null, rows.map(function (row) { return Math.max(0, Number(row[1] || 0)); }).concat([1]));

        function createPointRow(row, index) {
            const label = String(row[0] || '');
            const value = Math.max(0, Number(row[1] || 0));
            const color = chartColorFromToken(row[2], index || 0);
            const axisPct = Math.max(0, Math.min(100, (value / maxValue) * 100));
            const sharePct = total > 0 ? Math.round((value / total) * 100) : 0;
            const el = document.createElement('div');
            el.className = 'dashboard-point-row';
            el.tabIndex = 0;
            el.setAttribute('role', 'group');
            el.setAttribute('aria-label', label + ': ' + value + ' (' + sharePct + '%)');
            el.innerHTML = '<span class="dashboard-point-row__label" title="' + escapeHtml(label) + '">' + escapeHtml(label) + '</span>'
                + '<span class="dashboard-point-axis" aria-hidden="true"><i class="dashboard-point-axis__dot" style="left:' + axisPct + '%;background:' + escapeHtml(color) + '"></i></span>'
                + '<span class="dashboard-point-row__value"><strong>' + value + '</strong><small>' + sharePct + '%</small></span>';
            return el;
        }

        visibleRows.forEach(function (row, index) { container.appendChild(createPointRow(row, index)); });
        if (extraRows.length) {
            const extra = document.createElement('div');
            extra.className = 'dashboard-expandable-extra dashboard-point-extra';
            extra.dataset.dashboardExtra = '';
            extra.hidden = true;
            extraRows.forEach(function (row, index) { extra.appendChild(createPointRow(row, cap + index)); });
            container.appendChild(extra);
            const holder = document.createElement('div');
            holder.innerHTML = listToggleMarkup(extraRows.length, 'data-dashboard-toggle="collection"');
            const button = holder.firstElementChild;
            button.dataset.extraCount = String(extraRows.length);
            container.appendChild(button);
        }
    }

    function renderCategoricalChart(container) {
        if (!container || !container.__dashboardChartModel) return;
        const model = container.__dashboardChartModel;
        const mode = currentChartMode(container, model.defaultMode || 'bars');
        container.dataset.dashboardChartMode = mode;
        if (!Number(model.total || 0)) {
            container.innerHTML = '<p class="text-muted small mb-0">' + escapeHtml(S('no_data', 'Sin datos para el período seleccionado.')) + '</p>';
            syncChartToggleButton(container, mode);
            return;
        }
        if (mode === 'donut') {
            container.innerHTML = categoricalDonutMarkup(model.rows, model.total, model.centerLabel);
        } else {
            renderCategoricalBars(container, model);
        }
        syncChartToggleButton(container, mode);
    }

    function registerCategoricalChart(container, rows, total, options) {
        if (!container) return;
        const opts = options || {};
        container.__dashboardChartModel = {
            kind: 'categorical',
            rows: rows || [],
            total: Math.max(0, Number(total || 0)),
            limit: opts.limit || 6,
            defaultMode: opts.defaultMode || 'bars',
            centerLabel: opts.centerLabel || S('summary_title', 'Total')
        };
        if (!container.dataset.dashboardChartMode || container.dataset.dashboardChartMode === 'points') {
            container.dataset.dashboardChartMode = container.__dashboardChartModel.defaultMode;
        }
        renderCategoricalChart(container);
    }

    function renderStatusChartModel(container) {
        if (!container || !container.__dashboardChartModel) return;
        const model = container.__dashboardChartModel;
        const mode = currentChartMode(container, model.defaultMode || 'bars');
        container.dataset.dashboardChartMode = mode;
        const pct = clampPct(model.progress);
        const tagHtml = (model.tags || []).map(function (tag) {
            return '<span class="dashboard-status-tag"><span>' + escapeHtml(tag[0]) + '</span><strong>' + escapeHtml(tag[1]) + '</strong></span>';
        }).join('');
        const copy = '<div class="dashboard-status-copy"><h3>' + escapeHtml(model.title) + '</h3><p>' + escapeHtml(S('status_intro', 'Una lectura rápida del indicador que más importa para tu perfil.')) + '</p>'
            + '<div class="dashboard-status-tags">' + tagHtml + '</div></div>';
        let chart = '';
        if (mode === 'donut') {
            const ringColor = pct >= 80 ? 'var(--success-dark)' : (pct > 0 ? 'var(--primary)' : '#9EADB8');
            chart = '<div class="dashboard-donut dashboard-donut--status" style="--progress:' + pct + ';--ring-color:' + ringColor + '" role="img" aria-label="' + escapeHtml(model.label + ': ' + formatPct(pct)) + '">'
                + '<div><div class="dashboard-donut__value">' + escapeHtml(formatPct(pct)) + '</div><span class="dashboard-donut__label">' + escapeHtml(model.label) + '</span></div></div>';
        } else {
            chart = '<div class="dashboard-status-bar-chart" role="img" aria-label="' + escapeHtml(model.label + ': ' + formatPct(pct)) + '">'
                + '<div class="dashboard-status-bar-chart__head"><strong>' + escapeHtml(formatPct(pct)) + '</strong><span>' + escapeHtml(model.label) + '</span></div>'
                + '<div class="dashboard-status-bar-chart__scale"><span>0%</span><span>50%</span><span>100%</span></div>'
                + '<div class="dashboard-status-bar-chart__track"><span style="width:' + pct + '%"></span></div></div>';
        }
        container.innerHTML = '<div class="dashboard-status-layout dashboard-status-layout--stacked">' + chart + copy + '</div>';
        syncChartToggleButton(container, mode);
    }

    function renderActivitySnapshotChart(container) {
        if (!container || !container.__dashboardChartModel) return;
        const model = container.__dashboardChartModel;
        const mode = currentChartMode(container, model.defaultMode || 'bars');
        container.dataset.dashboardChartMode = mode;
        const stats = '<div class="dashboard-activity-snapshot__stats">'
            + '<div class="dashboard-activity-stat"><small>' + escapeHtml(S('activity_snapshot_total', 'Actividad total')) + '</small><strong>' + model.totalActivity + '</strong><span>' + escapeHtml(model.introLabel) + '</span></div>'
            + '<div class="dashboard-activity-stat"><small>' + escapeHtml(S('activity_snapshot_peak', 'Mes con mayor actividad')) + '</small><strong>' + escapeHtml(model.peak.label) + '</strong><span>' + model.peak.total + '</span></div>'
            + '</div>';
        if (mode === 'donut') {
            const rows = model.sample.map(function (row, index) {
                return [row.label, row.total, chartColorFromToken('', index)];
            });
            container.innerHTML = '<div class="dashboard-activity-snapshot">'
                + categoricalDonutMarkup(rows, model.totalActivity, S('activity_snapshot_total', 'Actividad total')) + stats + '</div>';
        } else {
            const max = Math.max(1, Number(model.max || 0));
            container.innerHTML = '<div class="dashboard-activity-snapshot">'
                + '<div class="dashboard-mini-bars" role="img" aria-label="' + escapeHtml(model.introLabel) + '">'
                + model.sample.map(function (row, index) {
                    const pct = Math.max(0, Math.min(100, (Number(row.total || 0) / max) * 100));
                    return '<div class="dashboard-mini-bar" tabindex="0" role="group" aria-label="' + escapeHtml(row.label + ': ' + row.total) + '">'
                        + '<span class="dashboard-mini-bar__value">' + row.total + '</span>'
                        + '<span class="dashboard-mini-bar__track"><i style="height:' + pct + '%;background:' + chartColorFromToken('', index) + '"></i></span>'
                        + '<small title="' + escapeHtml(row.label) + '">' + escapeHtml(row.label) + '</small></div>';
                }).join('')
                + '</div>' + stats + '</div>';
        }
        syncChartToggleButton(container, mode);
    }

    function renderOperationsChart(container) {
        if (!container || !container.__dashboardChartModel) return;
        const model = container.__dashboardChartModel;
        const mode = currentChartMode(container, model.defaultMode || 'bars');
        container.dataset.dashboardChartMode = mode;
        const countRows = model.rows.filter(function (row) { return !Object.prototype.hasOwnProperty.call(row, 'pct'); });
        const maxCount = Math.max.apply(null, countRows.map(function (row) { return Number(row.value || 0); }).concat([1]));

        if (mode === 'radial') {
            container.innerHTML = '<div class="dashboard-operation-radial-list">' + model.rows.map(function (row, index) {
                const hasPct = Object.prototype.hasOwnProperty.call(row, 'pct');
                const rawValue = hasPct ? clampPct(row.pct) : Number(row.value || 0);
                const pct = hasPct ? rawValue : Math.max(0, Math.min(100, (rawValue / maxCount) * 100));
                const valueLabel = hasPct ? formatPct(rawValue) : String(rawValue);
                const color = chartColorFromToken(hasPct ? 'fill-info' : '', index);
                return '<div class="dashboard-operation-radial-row" tabindex="0" role="group" aria-label="' + escapeHtml(row.label + ': ' + valueLabel) + '">'
                    + '<div class="dashboard-operation-radial-row__chart" style="--operation-progress:' + pct + ';--operation-color:' + escapeHtml(color) + '"><span>' + escapeHtml(valueLabel) + '</span></div>'
                    + '<div class="dashboard-operation-radial-row__copy"><strong>' + escapeHtml(row.label) + '</strong><small>' + escapeHtml(row.meta || '') + '</small></div>'
                    + '</div>';
            }).join('') + '</div>';
        } else {
            container.innerHTML = '<div class="dashboard-operation-bars">' + model.rows.map(function (row, index) {
                const hasPct = Object.prototype.hasOwnProperty.call(row, 'pct');
                const rawValue = hasPct ? clampPct(row.pct) : Number(row.value || 0);
                const pct = hasPct ? rawValue : Math.max(0, Math.min(100, (rawValue / maxCount) * 100));
                const valueLabel = hasPct ? formatPct(rawValue) : String(rawValue);
                const color = chartColorFromToken(hasPct ? 'fill-info' : '', index);
                return '<div class="dashboard-operation-bar-row" tabindex="0" role="group" aria-label="' + escapeHtml(row.label + ': ' + valueLabel) + '">'
                    + '<div class="dashboard-operation-bar-row__head"><span class="dashboard-operation-bar-row__icon"><i class="bi ' + escapeHtml(row.icon || 'bi-circle') + '"></i></span><div><strong>' + escapeHtml(row.label) + '</strong><small>' + escapeHtml(row.meta || '') + '</small></div><em>' + escapeHtml(valueLabel) + '</em></div>'
                    + '<div class="dashboard-operation-bar-row__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + Math.round(pct) + '"><span style="width:' + pct + '%;background:' + escapeHtml(color) + '"></span></div>'
                    + '</div>';
            }).join('') + '</div>';
        }
        syncChartToggleButton(container, mode);
    }

    function renderProgramProgressChart(container) {
        if (!container || !container.__dashboardChartModel) return;
        const model = container.__dashboardChartModel;
        const mode = currentChartMode(container, model.defaultMode || 'bars');
        container.dataset.dashboardChartMode = mode;
        if (!model.rows.length) {
            container.innerHTML = '<div class="dashboard-trend-empty">' + escapeHtml(S('programs_no_data', 'No hay detalle mensual disponible para esta vista.')) + '</div>';
            syncChartToggleButton(container, mode);
            return;
        }
        if (mode === 'columns') {
            const rows = model.rows.slice(0, 6);
            container.innerHTML = '<div class="dashboard-program-columns" role="img" aria-label="' + escapeHtml(S('program_progress_chart_title', 'Avance por programa')) + '">'
                + rows.map(function (row) {
                    const progress = clampPct(row.progress_percentage || 0);
                    const target = row.target_percentage == null ? null : clampPct(row.target_percentage);
                    return '<div class="dashboard-program-column" tabindex="0" role="group" aria-label="' + escapeHtml(row.name + ': ' + formatPct(progress)) + '">'
                        + '<strong>' + escapeHtml(formatPct(progress)) + '</strong>'
                        + '<div class="dashboard-program-column__plot"><span style="height:' + progress + '%"></span>' + (target == null ? '' : '<i style="bottom:' + target + '%" title="' + escapeHtml(S('programs_target', 'Meta') + ': ' + formatPct(target)) + '"></i>') + '</div>'
                        + '<small title="' + escapeHtml(row.name) + '">' + escapeHtml(row.name) + '</small></div>';
                }).join('') + '</div>';
        } else {
            const items = model.rows.map(function (row) {
                const progress = clampPct(row.progress_percentage || 0);
                const target = row.target_percentage == null ? null : clampPct(row.target_percentage);
                const meetsTarget = target == null ? progress >= 80 : progress >= target;
                return '<div class="dashboard-program-alt-card" tabindex="0" role="group" aria-label="' + escapeHtml(row.name + ': ' + formatPct(progress)) + '">'
                    + '<div class="dashboard-program-alt-card__head"><div class="dashboard-program-alt-card__copy"><strong title="' + escapeHtml(row.name) + '">' + escapeHtml(row.name) + '</strong><span>' + escapeHtml(row.status || '') + '</span></div><div class="dashboard-program-alt-card__metrics"><strong>' + escapeHtml(formatPct(progress)) + '</strong>' + (target == null ? '' : '<small>' + escapeHtml(S('programs_target', 'Meta') + ' ' + formatPct(target)) + '</small>') + '</div></div>'
                    + '<div class="dashboard-program-alt-card__track"><span class="dashboard-program-alt-card__fill' + (meetsTarget ? ' is-good' : '') + '" style="width:' + progress + '%"></span>' + (target == null ? '' : '<b class="dashboard-program-alt-card__target" style="left:' + target + '%"></b>') + '</div>'
                    + '<div class="dashboard-program-alt-card__foot"><span>' + escapeHtml(S('activity_progress', 'Avance')) + '</span>' + (target == null ? '' : '<em>' + escapeHtml(progress >= target ? 'OK' : S('programs_below_target', 'Bajo meta')) + '</em>') + '</div>'
                    + '</div>';
            });
            renderExpandableCollection(container, items, 4);
            const toggle = container.querySelector('.dashboard-list-toggle');
            if (toggle) toggle.dataset.extraCount = String(Math.max(0, items.length - 3));
        }
        syncChartToggleButton(container, mode);
    }

    function rerenderSwitchableChart(container) {
        if (!container || !container.__dashboardChartModel) return;
        const kind = container.__dashboardChartModel.kind;
        if (kind === 'status') return renderStatusChartModel(container);
        if (kind === 'activity-snapshot') return renderActivitySnapshotChart(container);
        if (kind === 'operations') return renderOperationsChart(container);
        if (kind === 'program-progress') return renderProgramProgressChart(container);
        renderCategoricalChart(container);
    }

    function installChartToggleButtons() {
        if (isWorkerRole) return;
        const targets = [
            'roleStatusContent',
            'operationsState',
            'programProgressList',
            'eventsState',
            'eventsCriticality',
            'eventsTypes',
            'evalInduction',
            'evalAudits',
            'evalSelf',
            'protocolAssignments',
            'protocolResults',
            'formsTop'
        ];
        targets.forEach(function (id) {
            const container = $(id);
            if (!container) return;
            let header = null;
            if (id === 'programProgressList') {
                const panel = container.closest('.dashboard-panel');
                if (panel) {
                    header = panel.querySelector(':scope > .dashboard-panel__head');
                    if (!header) {
                        header = document.createElement('header');
                        header.className = 'dashboard-panel__head dashboard-panel__head--generated';
                        const title = document.createElement('h3');
                        title.textContent = S('program_progress_chart_title', 'Avance por programa');
                        header.appendChild(title);
                        panel.insertBefore(header, panel.firstChild);
                    }
                }
            } else {
                const panel = container.closest('.dashboard-panel');
                header = panel ? panel.querySelector(':scope > .dashboard-panel__head') : null;
            }
            if (!header || header.querySelector('[data-dashboard-chart-toggle="' + id + '"]')) return;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'dashboard-chart-toggle-btn';
            button.dataset.dashboardChartToggle = id;
            button.innerHTML = '<i class="bi bi-pie-chart" aria-hidden="true"></i>';
            button.setAttribute('aria-label', S('chart_switch_to_donut', 'Cambiar a gráfico circular'));
            button.setAttribute('title', S('chart_switch_to_donut', 'Cambiar a gráfico circular'));
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                if (!container.__dashboardChartModel) return;
                const model = container.__dashboardChartModel || {};
                const current = currentChartMode(container, model.defaultMode || 'bars');
                let alternative = 'donut';
                if (model.kind === 'operations') alternative = 'radial';
                if (model.kind === 'program-progress') alternative = 'columns';
                container.dataset.dashboardChartMode = current === alternative ? 'bars' : alternative;
                rerenderSwitchableChart(container);
            });
            const access = header.querySelector(':scope > .dashboard-panel-access-btn');
            if (access) header.insertBefore(button, access);
            else header.appendChild(button);
        });
    }

    function installSectionCollapsibles() {
        if (isWorkerRole) return;

        function prepareOwner(owner, head, splitChild) {
            if (!owner || !head || head.querySelector(':scope > .dashboard-section-collapse-btn')) return;
            owner.classList.add(splitChild ? 'dashboard-subsection-shell' : 'dashboard-section--collapsible');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'dashboard-section-collapse-btn';
            button.setAttribute('aria-expanded', 'true');
            button.setAttribute('aria-label', S('section_collapse', 'Contraer sección'));
            button.setAttribute('title', S('section_collapse', 'Contraer sección'));
            button.innerHTML = '<i class="bi bi-chevron-up" aria-hidden="true"></i>';
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const collapsed = !owner.classList.contains('is-dashboard-section-collapsed');
                owner.classList.toggle('is-dashboard-section-collapsed', collapsed);
                button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                button.setAttribute('aria-label', collapsed ? S('section_expand', 'Expandir sección') : S('section_collapse', 'Contraer sección'));
                button.setAttribute('title', collapsed ? S('section_expand', 'Expandir sección') : S('section_collapse', 'Contraer sección'));
                const icon = button.querySelector('i');
                if (icon) icon.className = 'bi ' + (collapsed ? 'bi-chevron-down' : 'bi-chevron-up');
                if (!collapsed) {
                    window.setTimeout(function () { window.dispatchEvent(new Event('resize')); }, 0);
                }
            });
            head.appendChild(button);
        }

        document.querySelectorAll('.dashboard-tab-panel > .dashboard-section').forEach(function (section) {
            section.classList.add('dashboard-section--shell');
            const head = Array.prototype.find.call(section.children, function (child) {
                return child.classList && child.classList.contains('dashboard-section-head');
            });
            if (head) {
                prepareOwner(section, head, false);
                return;
            }

            const splitGrid = Array.prototype.find.call(section.children, function (child) {
                return child.classList && child.classList.contains('dashboard-panel-grid');
            });
            if (!splitGrid) return;
            section.classList.add('dashboard-section--split-shell');
            Array.prototype.forEach.call(splitGrid.children, function (child) {
                const compactHead = Array.prototype.find.call(child.children, function (node) {
                    return node.classList && node.classList.contains('dashboard-section-head');
                });
                if (compactHead) prepareOwner(child, compactHead, true);
            });
        });
    }

    function listToggleMarkup(extraCount, extraAttrs) {
        if (!extraCount) return '';
        return '<button type="button" class="dashboard-list-toggle" aria-expanded="false" ' + (extraAttrs || '') + '>'
            + '<span>' + escapeHtml(S('view_more', 'Ver más')) + ' (' + extraCount + ')</span>'
            + '<i class="bi bi-chevron-down" aria-hidden="true"></i></button>';
    }

    function renderExpandableCollection(container, itemsHtml, limit) {
        if (!container) return;
        const items = itemsHtml || [];
        const cap = Math.max(1, Number(limit || 4));
        const visible = items.slice(0, cap).join('');
        const hiddenItems = items.slice(cap);
        container.innerHTML = visible;
        if (!hiddenItems.length) return;
        container.innerHTML += '<div class="dashboard-expandable-extra" data-dashboard-extra hidden>' + hiddenItems.join('') + '</div>'
            + listToggleMarkup(hiddenItems.length, 'data-dashboard-toggle="collection"');
    }

    function dashboardModuleRoute(key) {
        return dashboardModuleRoutes[key] || '';
    }

    function statusSmartTarget() {
        if (lastDashboardConsolidated) return '';
        if (lastDashboardRole === 'jefatura') return 'induction';
        if (lastDashboardRole === 'cliente') return 'programs';
        if (lastDashboardRole === 'administrador' || lastDashboardRole === 'administrador_completo') return 'events';
        return '';
    }

    function navigateDashboardModule(key) {
        const href = dashboardModuleRoute(key);
        if (!href) return false;
        window.location.href = href;
        return true;
    }

    function dashboardDetailRowsFromElement(panel) {
        if (!panel) return [];
        const rows = [];
        panel.querySelectorAll('.dashboard-operation-row, .dashboard-status-tag, .dashboard-bar-row, .dashboard-program-stat').forEach(function (node) {
            const text = (node.innerText || node.textContent || '').replace(/\s+/g, ' ').trim();
            if (text) rows.push(text);
        });
        return rows;
    }

    function dashboardDetailMarkup(key, panel) {
        const data = lastDashboardData || {};
        const timeline = progressTimelineRows(data.tendencia || []);
        const list = function (rows) {
            if (!rows.length) return '<p class="dashboard-panel-detail__empty">' + escapeHtml(S('no_data', 'Sin datos para el período seleccionado.')) + '</p>';
            return '<ul class="dashboard-panel-detail__list">' + rows.map(function (row) {
                return '<li>' + escapeHtml(row) + '</li>';
            }).join('') + '</ul>';
        };

        if (key === 'progress') {
            if (!timeline.length) return list([]);
            return '<div class="dashboard-panel-detail__table-wrap"><table class="dashboard-panel-detail__table"><thead><tr><th>'
                + escapeHtml(S('progress_timeline_period', 'Período')) + '</th><th>'
                + escapeHtml(S('progress_timeline_period_total', 'Registros del período')) + '</th><th>'
                + escapeHtml(S('progress_timeline_series', 'Registros acumulados')) + '</th></tr></thead><tbody>'
                + timeline.map(function (row) {
                    const breakdown = S('trend_events', 'Eventos') + ' ' + row.eventos
                        + ' · ' + S('trend_forms', 'Formularios') + ' ' + row.formularios
                        + ' · ' + S('trend_protocols', 'Protocolos') + ' ' + row.protocolos
                        + ' · ' + S('trend_evaluations', 'Evaluaciones finalizadas') + ' ' + row.evaluaciones;
                    return '<tr><td><strong>' + escapeHtml(row.periodo) + '</strong></td><td><strong>' + escapeHtml(row.periodTotal) + '</strong><small>' + escapeHtml(breakdown) + '</small></td><td>' + escapeHtml(row.cumulative) + '</td></tr>';
                }).join('') + '</tbody></table></div>';
        }

        if (key === 'trend' || key === 'activity-snapshot') {
            const totals = [
                S('trend_events', 'Eventos') + ': ' + (data.tendencia || []).reduce(function (sum, row) { return sum + Number(row.eventos || 0); }, 0),
                S('trend_forms', 'Formularios') + ': ' + (data.tendencia || []).reduce(function (sum, row) { return sum + Number(row.formularios || 0); }, 0),
                S('trend_protocols', 'Protocolos') + ': ' + (data.tendencia || []).reduce(function (sum, row) { return sum + Number(row.protocolos || 0); }, 0),
                S('trend_evaluations', 'Evaluaciones finalizadas') + ': ' + (data.tendencia || []).reduce(function (sum, row) { return sum + Number(row.evaluaciones || 0); }, 0)
            ];
            return list(totals);
        }

        if (key === 'calendar') {
            const rows = (data.actividad_reciente || []).slice(0, 8).map(function (row) {
                const meta = recentMeta(row.tipo);
                return meta[1] + ' · ' + formatDate(row.fecha) + ' · ' + (row.titulo || '') + (row.detalle ? ' — ' + row.detalle : '');
            });
            return list(rows);
        }

        if (key === 'notifications') {
            const rows = (data.actividad_reciente || []).slice(0, 8).map(function (row) {
                const meta = recentMeta(row.tipo);
                return meta[1] + ': ' + (row.titulo || '') + ' · ' + formatDate(row.fecha);
            });
            return list(rows);
        }

        if (key === 'operations' || key === 'status') {
            return list(dashboardDetailRowsFromElement(panel));
        }

        return list(dashboardDetailRowsFromElement(panel));
    }

    function closeDashboardPanelDetail(panel, restoreFocus) {
        if (!panel) return;
        const detail = panel.querySelector(':scope > .dashboard-panel-detail');
        if (detail) detail.remove();
        panel.classList.remove('is-detail-open');
        if (restoreFocus && typeof panel.focus === 'function') panel.focus();
    }

    function openDashboardPanelDetail(panel, key) {
        if (!panel) return;
        const existing = panel.querySelector(':scope > .dashboard-panel-detail');
        if (existing) {
            closeDashboardPanelDetail(panel, false);
            return;
        }
        document.querySelectorAll('.dashboard-panel.is-detail-open').forEach(function (openPanel) {
            if (openPanel !== panel) closeDashboardPanelDetail(openPanel, false);
        });
        const detail = document.createElement('div');
        detail.className = 'dashboard-panel-detail';
        detail.setAttribute('role', 'region');
        detail.setAttribute('aria-live', 'polite');
        detail.innerHTML = '<div class="dashboard-panel-detail__head"><strong>' + escapeHtml(S('graph_detail_title', 'Detalle del gráfico')) + '</strong>'
            + '<button type="button" class="dashboard-panel-detail__close" aria-label="' + escapeHtml(S('graph_close_detail', 'Cerrar detalle')) + '"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>'
            + '<div class="dashboard-panel-detail__body">' + dashboardDetailMarkup(key, panel) + '</div>';
        panel.appendChild(detail);
        panel.classList.add('is-detail-open');
        const close = detail.querySelector('.dashboard-panel-detail__close');
        if (close) close.focus();
    }

    function configureStatusPanelAccess() {
        const panel = document.querySelector('[data-dashboard-smart-nav="status"]');
        const trigger = document.querySelector('[data-dashboard-smart-nav-trigger="status"]');
        if (!panel || !trigger) return;
        const target = statusSmartTarget();
        panel.setAttribute('role', target ? 'link' : 'button');
        const icon = trigger.querySelector('i');
        if (icon) icon.className = 'bi ' + (target ? 'bi-arrow-up-right' : 'bi-info-lg');
        trigger.setAttribute('aria-label', target ? S('graph_open_module', 'Abrir módulo') : S('graph_more_info', 'Ver más información'));
    }

    function isDashboardNestedInteractive(target) {
        return !!(target && target.closest && target.closest('a,button,input,select,textarea,summary,[role="tab"],[data-sct-info-tip],.dashboard-panel-detail'));
    }

    document.addEventListener('click', function (event) {
        const closeDetail = event.target.closest ? event.target.closest('.dashboard-panel-detail__close') : null;
        if (closeDetail) {
            event.preventDefault();
            event.stopPropagation();
            closeDashboardPanelDetail(closeDetail.closest('.dashboard-panel'), true);
            return;
        }

        const detailTrigger = event.target.closest ? event.target.closest('[data-dashboard-detail-trigger]') : null;
        if (detailTrigger) {
            event.preventDefault();
            event.stopPropagation();
            const key = detailTrigger.getAttribute('data-dashboard-detail-trigger') || '';
            const panel = detailTrigger.closest('.dashboard-panel') || document.querySelector('[data-dashboard-detail="' + key + '"]');
            openDashboardPanelDetail(panel, key);
            return;
        }

        const smartTrigger = event.target.closest ? event.target.closest('[data-dashboard-smart-nav-trigger]') : null;
        if (smartTrigger) {
            event.preventDefault();
            event.stopPropagation();
            const target = statusSmartTarget();
            if (!navigateDashboardModule(target)) {
                const panel = smartTrigger.closest('.dashboard-panel') || document.querySelector('[data-dashboard-smart-nav="status"]');
                openDashboardPanelDetail(panel, 'status');
            }
            return;
        }

        const seriesNode = event.target.closest ? event.target.closest('[data-dashboard-series-nav]') : null;
        if (seriesNode) {
            const target = seriesNode.getAttribute('data-dashboard-series-nav') || '';
            if (navigateDashboardModule(target)) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }
        }

        const panel = event.target.closest ? event.target.closest('[data-dashboard-nav], [data-dashboard-detail], [data-dashboard-smart-nav]') : null;
        if (!panel || isDashboardNestedInteractive(event.target)) return;

        if (panel.hasAttribute('data-dashboard-nav')) {
            navigateDashboardModule(panel.getAttribute('data-dashboard-nav') || '');
            return;
        }
        if (panel.hasAttribute('data-dashboard-smart-nav')) {
            const target = statusSmartTarget();
            if (!navigateDashboardModule(target)) openDashboardPanelDetail(panel, 'status');
            return;
        }
        if (panel.hasAttribute('data-dashboard-detail')) {
            openDashboardPanelDetail(panel, panel.getAttribute('data-dashboard-detail') || '');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const target = event.target;
        if (!target || isDashboardNestedInteractive(target)) return;

        const seriesNode = target.closest ? target.closest('[data-dashboard-series-nav]') : null;
        if (seriesNode) {
            const seriesTarget = seriesNode.getAttribute('data-dashboard-series-nav') || '';
            if (navigateDashboardModule(seriesTarget)) {
                event.preventDefault();
                return;
            }
        }

        const panel = target.closest ? target.closest('[data-dashboard-nav], [data-dashboard-detail], [data-dashboard-smart-nav]') : null;
        if (!panel) return;
        event.preventDefault();
        if (panel.hasAttribute('data-dashboard-nav')) {
            navigateDashboardModule(panel.getAttribute('data-dashboard-nav') || '');
        } else if (panel.hasAttribute('data-dashboard-smart-nav')) {
            const smartTarget = statusSmartTarget();
            if (!navigateDashboardModule(smartTarget)) openDashboardPanelDetail(panel, 'status');
        } else {
            openDashboardPanelDetail(panel, panel.getAttribute('data-dashboard-detail') || '');
        }
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.dashboard-list-toggle');
        if (!button) return;

        if (button.dataset.dashboardRows === 'ranking') {
            const rows = document.querySelectorAll('#rankingBody .dashboard-ranking-extra');
            const expanded = button.getAttribute('aria-expanded') === 'true';
            rows.forEach(function (row) { row.hidden = expanded; });
            button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            const label = button.querySelector('span');
            const icon = button.querySelector('i');
            if (label) label.textContent = expanded ? S('view_more', 'Ver más') + ' (' + rows.length + ')' : S('view_less', 'Ver menos');
            if (icon) icon.className = 'bi ' + (expanded ? 'bi-chevron-down' : 'bi-chevron-up');
            return;
        }

        const container = button.parentElement;
        const extra = container ? container.querySelector(':scope > [data-dashboard-extra]') : null;
        if (!extra) return;
        const expanded = button.getAttribute('aria-expanded') === 'true';
        extra.hidden = expanded;
        button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        const label = button.querySelector('span');
        const icon = button.querySelector('i');
        const extraCount = Number(button.dataset.extraCount || extra.children.length || 0);
        if (label) label.textContent = expanded ? S('view_more', 'Ver más') + (extraCount ? ' (' + extraCount + ')' : '') : S('view_less', 'Ver menos');
        if (icon) icon.className = 'bi ' + (expanded ? 'bi-chevron-down' : 'bi-chevron-up');
    });

    function showAlert(message, variant) {
        if (!dashAlert) return;
        dashAlert.textContent = message || '';
        dashAlert.classList.remove('d-none', 'alert-success', 'alert-info', 'alert-warning', 'alert-danger');
        dashAlert.classList.add('alert-' + (variant || 'danger'));
    }

    function hideAlert() {
        if (dashAlert) dashAlert.classList.add('d-none');
    }

    async function api(url) {
        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' }, cache: 'no-store', credentials: 'same-origin' });
            const data = await response.json();
            if (typeof data.success === 'undefined') throw new Error('invalid');
            return data;
        } catch (_) {
            return { success: false, message: S('error_response', 'Respuesta inválida del servidor.') };
        }
    }

    function companyQuery() {
        return isGlobalAdmin && currentCompanyId && !isConsolidated()
            ? '?id_company=' + encodeURIComponent(currentCompanyId)
            : '';
    }

    async function loadCompanies() {
        if (!companySelect) return;
        const response = await api('./empresas-disponibles.php');
        if (!response.success) {
            showAlert(response.message, 'danger');
            return;
        }
        (response.data || []).forEach(function (row) {
            const option = document.createElement('option');
            option.value = row.id_company;
            option.textContent = row.razon_social;
            companySelect.appendChild(option);
        });
    }

    async function loadScopeCatalogs() {
        if (!projectFilter || !centerFilter) return;
        if (isGlobalAdmin && !currentCompanyId) return;
        if (isConsolidated()) {
            projectFilter.innerHTML = '<option value="">' + escapeHtml(S('all_projects', 'Todos los proyectos')) + '</option>';
            centerFilter.innerHTML = '<option value="">' + escapeHtml(S('all_centers', 'Todos los centros')) + '</option>';
            return;
        }

        projectFilter.innerHTML = '<option value="">' + escapeHtml(S('all_projects', 'Todos los proyectos')) + '</option>';
        centerFilter.innerHTML = '<option value="">' + escapeHtml(S('all_centers', 'Todos los centros')) + '</option>';
        const q = companyQuery();
        const results = await Promise.all([
            api('./proyectos-disponibles.php' + q),
            api('./centros-disponibles.php' + q)
        ]);
        const projects = results[0];
        const centers = results[1];

        if (projects.success) {
            (projects.data || []).forEach(function (row) {
                const option = document.createElement('option');
                option.value = row.id_project;
                option.textContent = row.name;
                projectFilter.appendChild(option);
            });
        }
        if (centers.success) {
            (centers.data || []).forEach(function (row) {
                const option = document.createElement('option');
                option.value = row.id_company_center;
                option.textContent = row.name;
                centerFilter.appendChild(option);
            });
        }
    }

    function buildParams() {
        const params = new URLSearchParams();
        if (isGlobalAdmin && currentCompanyId && !isConsolidated()) {
            params.set('id_company', String(currentCompanyId));
        }
        if (projectFilter && projectFilter.value) params.set('id_project', projectFilter.value);
        if (centerFilter && centerFilter.value) params.set('id_center', centerFilter.value);
        params.set('period', periodFilter ? (periodFilter.value || '90') : '90');
        return params;
    }

    function toggleConsolidatedUi(consolidated) {
        perModuleSections.forEach(function (el) { el.classList.toggle('d-none', consolidated); });
        if (rankingSection) rankingSection.classList.toggle('d-none', !consolidated);
        if (projectFilterWrap) projectFilterWrap.classList.toggle('d-none', consolidated);
        if (centerFilterWrap) centerFilterWrap.classList.toggle('d-none', consolidated);
        if (filterScopeNote) {
            const key = consolidated ? 'consolidated' : 'default';
            filterScopeNote.textContent = filterScopeNote.dataset[key] || filterScopeNote.textContent;
        }
    }

    function dashboardFilterContextText() {
        const parts = [];
        if (isGlobalAdmin && companySelect) {
            const option = companySelect.options[companySelect.selectedIndex];
            if (option && companySelect.value) parts.push(option.textContent.trim());
        }
        if (periodFilter) {
            const option = periodFilter.options[periodFilter.selectedIndex];
            if (option) parts.push(option.textContent.trim());
        }
        if (projectFilter && projectFilter.value) {
            const option = projectFilter.options[projectFilter.selectedIndex];
            if (option) parts.push(option.textContent.trim());
        }
        if (centerFilter && centerFilter.value) {
            const option = centerFilter.options[centerFilter.selectedIndex];
            if (option) parts.push(option.textContent.trim());
        }
        return parts.join(' · ');
    }

    function updateFilterScopeNote() {
        if (!filterScopeNote) return;
        const consolidated = isConsolidated();
        const base = filterScopeNote.dataset[consolidated ? 'consolidated' : 'default'] || '';
        const context = dashboardFilterContextText();
        const applies = S('filter_applies', 'Los filtros activos actualizan Resumen, Seguimiento y Cumplimiento; Proyecto y Centro sólo afectan los módulos que almacenan ese alcance.');
        filterScopeNote.textContent = (context ? S('filter_current', 'Vista actual') + ': ' + context + '. ' : '') + base + (applies ? ' ' + applies : '');
    }

    function setDashboardLiveStatus(kind, dateValue) {
        if (!dashboardLiveStatus) return;
        const text = dashboardLiveStatus.querySelector('span:last-child');
        dashboardLiveStatus.classList.toggle('is-error', kind === 'error');
        if (!text) return;
        if (kind === 'updated') {
            const date = dateValue ? new Date(dateValue) : new Date();
            const time = Number.isNaN(date.getTime()) ? '' : new Intl.DateTimeFormat(document.documentElement.lang || 'es', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(date);
            text.textContent = S('live_status_updated', 'Datos en vivo') + (time ? ' · ' + time : '');
        } else if (kind === 'error') {
            text.textContent = S('live_status_error', 'No se pudo actualizar en vivo. Se mantienen los últimos datos disponibles.');
        } else {
            text.textContent = S('live_status_waiting', 'Datos conectados a la base de datos.');
        }
    }

    function captureLiveUiState() {
        const ids = ['summaryActivity', 'summaryNotifications', 'summarySteps', 'recentList'];
        const state = {};
        ids.forEach(function (id) {
            const container = $(id);
            const toggle = container ? container.querySelector('.dashboard-list-toggle') : null;
            state[id] = !!(toggle && toggle.getAttribute('aria-expanded') === 'true');
        });
        return state;
    }

    function restoreLiveUiState(state) {
        if (!state) return;
        Object.keys(state).forEach(function (id) {
            if (!state[id]) return;
            const container = $(id);
            const toggle = container ? container.querySelector('.dashboard-list-toggle') : null;
            if (toggle && toggle.getAttribute('aria-expanded') !== 'true') toggle.click();
        });
    }

    async function loadDashboard(options) {
        options = options || {};
        const silent = options.silent === true;
        const liveUiState = silent ? captureLiveUiState() : null;
        if (isGlobalAdmin && !currentCompanyId) return;
        const serial = ++requestSerial;
        hideAlert();
        if (!silent) {
            dashStatus.classList.remove('d-none');
            dashContent.classList.add('d-none');
        }

        const consolidated = isConsolidated();
        toggleConsolidatedUi(consolidated);
        const period = periodFilter ? (periodFilter.value || '90') : '90';
        const url = consolidated
            ? './indicadores-consolidados.php?period=' + encodeURIComponent(period)
            : './indicadores.php?' + buildParams().toString();

        const response = await api(url);
        if (serial !== requestSerial) return;
        if (!response.success) {
            if (!silent) {
                dashStatus.classList.add('d-none');
                showAlert(response.message || S('error_response', 'No se pudieron cargar los indicadores.'), 'danger');
            }
            setDashboardLiveStatus('error');
            return;
        }

        dashStatus.classList.add('d-none');
        dashContent.classList.remove('d-none');
        lastDashboardData = response.data || {};
        lastDashboardConsolidated = consolidated;
        lastDashboardRole = consolidated ? 'administrador_completo' : (lastDashboardData.role || pageRole);
        if (consolidated) renderConsolidated(lastDashboardData);
        else renderAll(lastDashboardData);
        updateFilterScopeNote();
        restoreLiveUiState(liveUiState);
        setDashboardLiveStatus('updated', new Date().toISOString());
    }

    if (companySelect) {
        companySelect.addEventListener('change', async function () {
            const raw = companySelect.value;
            currentCompanyId = raw === '__all__' ? '__all__' : (raw ? parseInt(raw, 10) : null);
            if (projectFilter) projectFilter.value = '';
            if (centerFilter) centerFilter.value = '';
            if (!currentCompanyId) {
                toggleConsolidatedUi(false);
                dashContent.classList.add('d-none');
                dashStatus.classList.remove('d-none');
                dashStatus.innerHTML = '<i class="bi bi-buildings" aria-hidden="true"></i><span>' + escapeHtml(S('select_company', 'Selecciona una empresa para ver sus indicadores.')) + '</span>';
                return;
            }
            await loadScopeCatalogs();
            updateFilterScopeNote();
            await loadDashboard();
        });
        loadCompanies();
    } else {
        loadScopeCatalogs().then(loadDashboard);
    }

    [periodFilter, projectFilter, centerFilter].forEach(function (el) {
        if (el) el.addEventListener('change', function () { updateFilterScopeNote(); loadDashboard(); });
    });

    if (resetFilters) {
        resetFilters.addEventListener('click', function () {
            if (periodFilter) periodFilter.value = '90';
            if (projectFilter) projectFilter.value = '';
            if (centerFilter) centerFilter.value = '';
            loadDashboard();
        });
    }

    setDashboardLiveStatus('waiting');
    updateFilterScopeNote();
    window.setInterval(function () {
        if (document.visibilityState !== 'visible') return;
        if (document.querySelector('.modal.show')) return;
        const active = document.activeElement;
        if (active && active.closest && active.closest('form, [data-dashboard-extra], .dashboard-panel-detail')) return;
        loadDashboard({ silent: true });
    }, 30000);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') loadDashboard({ silent: true });
    });
    window.addEventListener('focus', function () {
        if (document.visibilityState === 'visible') loadDashboard({ silent: true });
    });

    function renderConsolidated(data) {
        renderMetrics(data, true);
        renderRoleFocus(data, true);
        renderOperationsState(data, true, 'administrador_completo');
        renderSummaryBoard(data, true, 'administrador_completo');
        renderPersonalProgress(null);
        renderPrograms(data.programas || {}, true, 'administrador_completo');
        renderTrend(data.tendencia || []);
        renderRanking(data.ranking_empresas || []);
    }

    function renderAll(data) {
        const role = data.role || pageRole;
        renderMetrics(data, false, role);
        renderRoleFocus(data, false, role);
        renderOperationsState(data, false, role);
        renderSummaryBoard(data, false, role);
        renderPersonalProgress(null);
        renderPrograms(role === 'trabajador' ? {} : (data.programas || {}), false, role);
        renderTrend(data.tendencia || []);

        if (role === 'trabajador') return;

        renderEventState(data.eventos || {});
        renderBars($('eventsCriticality'), [
            [S('crit_low', 'Baja'), data.eventos?.por_criticidad?.baja || 0, 'fill-good'],
            [S('crit_medium', 'Media'), data.eventos?.por_criticidad?.media || 0, 'fill-warn'],
            [S('crit_high', 'Alta'), data.eventos?.por_criticidad?.alta || 0, 'fill-bad'],
            [S('crit_critical', 'Crítica'), data.eventos?.por_criticidad?.critica || 0, 'fill-bad']
        ], data.eventos?.total || 0);
        renderLabelBars($('eventsTypes'), data.eventos?.tipos_principales || []);

        renderEvaluation($('evalInduction'), $('rateInduction'), data.induccion || {});
        renderEvaluation($('evalAudits'), $('rateAudits'), data.auditorias || {});
        renderEvaluation($('evalSelf'), $('rateSelf'), data.autoevaluaciones || {});
        renderProtocols(data.protocolos || {});
        renderForms(data.formularios || {});
        renderRecent(data.actividad_reciente || []);
    }


    function formatMonthLabel(value) {
        if (!value) return '';
        const clean = String(value).slice(0, 7);
        const date = new Date(clean + '-01T00:00:00');
        if (Number.isNaN(date.getTime())) return clean;
        return new Intl.DateTimeFormat(document.documentElement.lang || 'es', {
            month: 'short', year: '2-digit'
        }).format(date);
    }

    function renderSummaryBoard(data, consolidated, role) {
        renderSummaryActivity(data.actividad_reciente || [], role);
        renderSummarySteps(data, consolidated, role);
        renderSummaryCalendar(data.calendario || []);
        renderSummaryNotifications(data.notificaciones || []);
    }

    function renderSummaryActivity(rows, role) {
        if (!summaryActivity) return;
        if (!rows.length) {
            summaryActivity.__dashboardChartModel = null;
            summaryActivity.innerHTML = '<div class="dashboard-summary-empty"><i class="bi bi-activity" aria-hidden="true"></i><span>' + escapeHtml(S('activity_snapshot_empty', 'Aún no hay actividad en el período seleccionado.')) + '</span></div>';
            return;
        }
        const recentItems = rows.slice().sort(function (a, b) {
            return String(b.fecha || '').localeCompare(String(a.fecha || ''));
        }).map(function (row) {
            const meta = recentMeta(row.tipo);
            const routeKey = ({ evento: 'events', formulario: 'forms', protocolo: 'protocols', auditoria: 'audits', evaluacion: 'induction' })[String(row.tipo || '')] || '';
            const href = routeKey ? dashboardModuleRoute(routeKey) : '';
            const content = '<span class="dashboard-recent-icon"><i class="bi ' + meta[0] + '"></i></span>'
                + '<span class="dashboard-recent-copy"><strong>' + escapeHtml(row.titulo || meta[1]) + '</strong><small>' + escapeHtml(row.detalle || meta[1]) + '</small></span>'
                + '<span class="dashboard-recent-date">' + escapeHtml(formatDate(row.fecha)) + '</span>';
            return href ? '<a class="dashboard-recent-item dashboard-recent-item--link" href="' + escapeHtml(href) + '">' + content + '</a>' : '<div class="dashboard-recent-item">' + content + '</div>';
        });
        summaryActivity.__dashboardChartModel = null;
        renderExpandableCollection(summaryActivity, recentItems, 4);
        const toggle = summaryActivity.querySelector('.dashboard-list-toggle');
        if (toggle) toggle.dataset.extraCount = String(Math.max(0, recentItems.length - 4));
    }

    function buildSummarySteps(data, consolidated, role) {
        const personal = data.personal || {};
        const induction = personal.induccion || {};
        const auditStates = data.auditorias?.por_estado_operativo || {};
        const programs = data.programas || {};
        const steps = [];
        if (role === 'trabajador' && !consolidated) {
            steps.push([S('dashboard_worker_overdue', 'Inducciones vencidas'), induction.vencidas || 0, S('dashboard_worker_overdue_help', 'Empieza por estas si tienes alguna vencida.'), null, 'bi-clock-history']);
            steps.push([S('dashboard_worker_in_progress', 'En curso'), induction.en_curso || 0, S('dashboard_worker_in_progress_help', 'Continúa las inducciones que ya comenzaste.'), null, 'bi-play-circle']);
            steps.push([S('dashboard_worker_to_start', 'Por iniciar'), induction.por_iniciar || 0, S('dashboard_worker_to_start_help', 'Inducciones asignadas que aún no comienzas.'), null, 'bi-journal-plus']);
            steps.push([S('dashboard_worker_certificates', 'Certificados'), induction.certificados || 0, S('dashboard_worker_certificates_help', 'Certificados obtenidos por inducciones aprobadas.'), null, 'bi-award']);
        } else if (role === 'jefatura' && !consolidated) {
            const auditOpen = Number(auditStates.pendiente || 0) + Number(auditStates.en_curso || 0);
            steps.push([S('metric_team_courses', 'Inducciones pendientes'), data.induccion?.pendientes || 0, S('eval_pending', 'Pendientes'), data.induccion?.tasa_aprobacion || 0, 'bi-journal-check']);
            steps.push([S('metric_audits_open', 'Auditorías abiertas'), auditOpen, S('events_in_progress', 'En proceso'), null, 'bi-shield-check']);
            steps.push([S('kpi_open_critical', 'Eventos altos/críticos abiertos'), data.eventos?.abiertos_criticos || 0, S('attention_title', 'Requiere atención'), null, 'bi-shield-exclamation']);
            steps.push([S('metric_programs_below', 'Programas bajo meta'), programs.bajo_meta || 0, S('programs_average', 'Avance promedio'), clampPct(programs.promedio_avance || 0), 'bi-graph-up-arrow']);
        } else if (role === 'cliente' && !consolidated) {
            steps.push([S('kpi_open_critical', 'Eventos altos/críticos abiertos'), data.eventos?.abiertos_criticos || 0, S('attention_title', 'Requiere atención'), null, 'bi-shield-exclamation']);
            steps.push([S('kpi_protocol_overdue', 'Protocolos vencidos'), data.protocolos?.asignaciones_vencidas || 0, S('protocol_overdue', 'Vencidos'), null, 'bi-calendar-x']);
            steps.push([S('metric_program_progress', 'Avance de programas'), formatPct(programs.promedio_avance || 0), S('programs_average', 'Avance promedio'), clampPct(programs.promedio_avance || 0), 'bi-graph-up-arrow']);
            steps.push([S('kpi_workers', 'Trabajadores activos'), data.totales?.trabajadores || 0, S('summary_title', 'Resumen principal'), null, 'bi-people']);
        } else {
            const auditOpen = Number(auditStates.pendiente || 0) + Number(auditStates.en_curso || 0);
            steps.push([S('kpi_open_critical', 'Eventos altos/críticos abiertos'), data.eventos?.abiertos_criticos || 0, S('attention_title', 'Requiere atención'), null, 'bi-shield-exclamation']);
            steps.push([S('metric_team_courses', 'Inducciones pendientes'), data.induccion?.pendientes || 0, S('eval_pending', 'Pendientes'), data.induccion?.tasa_aprobacion || 0, 'bi-journal-check']);
            steps.push([S('metric_audits_open', 'Auditorías abiertas'), auditOpen, S('events_in_progress', 'En proceso'), null, 'bi-clipboard2-check']);
            steps.push([S('kpi_protocol_overdue', 'Protocolos vencidos'), data.protocolos?.asignaciones_vencidas || 0, S('protocol_overdue', 'Vencidos'), null, 'bi-calendar-x']);
        }
        return steps;
    }

    function renderSummarySteps(data, consolidated, role) {
        if (!summarySteps) return;
        const steps = buildSummarySteps(data, consolidated, role);
        if (!steps.length) {
            summarySteps.innerHTML = '<div class="dashboard-summary-empty"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>' + escapeHtml(S('steps_empty', 'No hay pasos prioritarios para esta vista.')) + '</span></div>';
            return;
        }
        const items = steps.map(function (row) {
            const progress = row[3] == null ? '' : '<div class="dashboard-step__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + clampPct(row[3]) + '"><span style="width:' + clampPct(row[3]) + '%"></span></div>';
            return '<div class="dashboard-step"><span class="dashboard-step__icon"><i class="bi ' + escapeHtml(row[4]) + '"></i></span><div class="dashboard-step__body"><strong>' + escapeHtml(row[0]) + '</strong><small>' + escapeHtml(row[2]) + '</small>' + progress + '</div><span class="dashboard-step__value">' + escapeHtml(row[1]) + '</span></div>';
        });
        renderExpandableCollection(summarySteps, items, 4);
        const toggle = summarySteps.querySelector('.dashboard-list-toggle');
        if (toggle) toggle.dataset.extraCount = String(Math.max(0, items.length - 3));
    }

    function reminderStorageScope() {
        const company = currentCompanyId && currentCompanyId !== '__all__' ? String(currentCompanyId) : String(root.dataset.companyId || '0');
        return 'sct:dashboard:calendar:' + String(root.dataset.userKey || 'user') + ':' + company;
    }

    function loadPersonalCalendarItems() {
        try {
            const raw = window.localStorage.getItem(reminderStorageScope());
            const rows = raw ? JSON.parse(raw) : [];
            return Array.isArray(rows) ? rows : [];
        } catch (_) {
            return [];
        }
    }

    function savePersonalCalendarItems(rows) {
        try { window.localStorage.setItem(reminderStorageScope(), JSON.stringify(rows || [])); } catch (_) {}
    }

    function calendarItemMeta(item) {
        const type = String(item && item.type || '');
        const map = {
            induccion: ['bi-journal-check', S('personal_induction', 'Inducción')],
            auditoria: ['bi-clipboard2-check', S('personal_audits', 'Auditoría')],
            autoevaluacion: ['bi-person-check', S('personal_self', 'Autoevaluación')],
            protocolo: ['bi-clipboard2-pulse', S('recent_protocol', 'Protocolo')],
            programa_inicio: ['bi-play-circle', S('programs_in_progress', 'Inicio de programa')],
            programa_fin: ['bi-flag', S('programs_target', 'Cierre de programa')],
            evento_seguimiento: ['bi-shield-exclamation', S('recent_event', 'Seguimiento de evento')],
            note: ['bi-sticky', S('calendar_note_type_note', 'Nota')],
            reminder: ['bi-alarm', S('calendar_note_type_reminder', 'Recordatorio')]
        };
        return map[type] || ['bi-calendar-event', S('calendar_day_schedule', 'Programado')];
    }

    function calendarToneClass(item) {
        const type = String(item && item.type || '');
        if (item && item.origin === 'personal') {
            return 'is-priority-' + String(item.priority || 'medium');
        }
        const map = {
            induccion: 'is-type-induction',
            auditoria: 'is-type-audit',
            autoevaluacion: 'is-type-self',
            protocolo: 'is-type-protocol',
            programa_inicio: 'is-type-program',
            programa_fin: 'is-type-program',
            evento_seguimiento: 'is-type-event'
        };
        return map[type] || 'is-type-default';
    }

    function calendarSemanticState(item) {
        if (!item) return 'scheduled';
        if (item.origin === 'personal') {
            const priority = String(item.priority || 'medium');
            if (priority === 'critical') return 'critical';
            if (priority === 'high') return 'warning';
            return 'scheduled';
        }
        const explicit = String(item.semantic_state || '').toLowerCase();
        if (['critical', 'warning', 'scheduled', 'complete'].includes(explicit)) return explicit;
        const status = String(item.status || item.meta || '').toLowerCase();
        if (/venc|critic|atras|overdue/.test(status)) return 'critical';
        if (/reprob|suspend|advert|warning/.test(status)) return 'warning';
        if (/complet|cerrad|aprobad|finaliz/.test(status)) return 'complete';
        return 'scheduled';
    }

    function calendarPriorityLabel(priority) {
        const map = {
            low: S('calendar_priority_low', 'Baja'),
            medium: S('calendar_priority_medium', 'Media'),
            high: S('calendar_priority_high', 'Alta'),
            critical: S('calendar_priority_critical', 'Crítica')
        };
        return map[String(priority || 'medium')] || map.medium;
    }

    function calendarDateKey(value) {
        const raw = String(value || '');
        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
        const date = new Date(raw.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return '';
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function openReminderEditor(dateKey) {
        const modalEl = $('dashboardReminderModal');
        const form = $('dashboardReminderForm');
        if (!modalEl || !form) return;
        form.reset();
        const dateInput = $('dashboardReminderDate');
        if (dateInput) dateInput.value = dateKey || new Date().toISOString().slice(0, 10);
        const priority = $('dashboardReminderPriority');
        if (priority) priority.value = 'medium';
        updateReminderPriorityPreview();
        const feedback = $('dashboardReminderFeedback');
        if (feedback) { feedback.classList.add('d-none'); feedback.textContent = ''; }
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            window.setTimeout(function () { const title = $('dashboardReminderTitle'); if (title) title.focus(); }, 180);
        }
    }

    function renderCalendarDayDetail(dateKey, allItems) {
        if (!summaryCalendar) return;
        const panel = summaryCalendar.querySelector('[data-calendar-day-panel]');
        if (!panel) return;
        const dayItems = (allItems || []).filter(function (item) { return calendarDateKey(item.date || item.datetime) === dateKey; });
        const systemItems = dayItems.filter(function (item) { return item.origin !== 'personal'; });
        const personalItems = dayItems.filter(function (item) { return item.origin === 'personal'; });
        const date = new Date(dateKey + 'T12:00:00');
        const heading = Number.isNaN(date.getTime()) ? dateKey : new Intl.DateTimeFormat(document.documentElement.lang || 'es', { weekday: 'long', day: 'numeric', month: 'long' }).format(date);
        function itemMarkup(item, personal) {
            const meta = calendarItemMeta(item);
            const time = String(item.time || (item.datetime ? String(item.datetime).slice(11, 16) : '') || '');
            const assignmentMeta = Number(item.count || 0) > 1 ? Number(item.count) + ' ' + S('calendar_assignments', 'asignaciones') : '';
            const tone = calendarToneClass(item);
            const priorityText = personal ? calendarPriorityLabel(item.priority) : '';
            const detailText = assignmentMeta || item.meta || item.details || meta[1];
            const copy = '<span class="dashboard-calendar-day-item__icon"><i class="bi ' + meta[0] + '"></i></span><span class="dashboard-calendar-day-item__copy"><strong>' + escapeHtml(item.title || '') + '</strong><small>' + escapeHtml((time ? time + ' · ' : '') + detailText) + '</small>' + (priorityText ? '<em>' + escapeHtml(S('calendar_note_priority', 'Prioridad')) + ': ' + escapeHtml(priorityText) + '</em>' : '') + '</span>';
            if (!personal && item.href) return '<a class="dashboard-calendar-day-item ' + tone + '" href="' + escapeHtml(item.href) + '">' + copy + '<i class="bi bi-arrow-right" aria-hidden="true"></i></a>';
            return '<div class="dashboard-calendar-day-item dashboard-calendar-day-item--personal ' + tone + '">' + copy + '</div>';
        }
        panel.innerHTML = '<div class="dashboard-calendar-day-panel__head"><div><small>' + escapeHtml(S('calendar_day_schedule', 'Programado para este día')) + '</small><strong>' + escapeHtml(heading) + '</strong></div><button type="button" class="dashboard-calendar-add-btn" data-calendar-add="' + escapeHtml(dateKey) + '"><i class="bi bi-calendar-plus" aria-hidden="true"></i><span>' + escapeHtml(S('calendar_add_note', 'Agregar nota o recordatorio')) + '</span></button></div>'
            + (systemItems.length ? '<div class="dashboard-calendar-day-group"><span>' + escapeHtml(S('calendar_system_items', 'Actividades programadas')) + '</span>' + systemItems.map(function (item) { return itemMarkup(item, false); }).join('') + '</div>' : '')
            + (personalItems.length ? '<div class="dashboard-calendar-day-group"><span>' + escapeHtml(S('calendar_personal_items', 'Notas y recordatorios personales')) + '</span>' + personalItems.map(function (item) { return itemMarkup(item, true); }).join('') + '</div>' : '')
            + (!dayItems.length ? '<p class="dashboard-calendar-day-empty">' + escapeHtml(S('calendar_day_empty', 'No hay actividades programadas para este día.')) + '</p>' : '');
        const add = panel.querySelector('[data-calendar-add]');
        if (add) add.addEventListener('click', function () { openReminderEditor(add.dataset.calendarAdd || dateKey); });
    }

    function renderSummaryCalendar(rows) {
        if (!summaryCalendar) return;
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startWeekday = (firstDay.getDay() + 6) % 7;
        const totalDays = lastDay.getDate();
        const systemItems = (rows || []).map(function (row) { return Object.assign({ origin: 'system' }, row); });
        const personalItems = loadPersonalCalendarItems().map(function (row) { return Object.assign({ origin: 'personal' }, row); });
        const allItems = systemItems.concat(personalItems);
        const markers = {};
        const markerTones = {};
        const markerStates = {};
        const stateRank = { complete: 1, scheduled: 2, warning: 3, critical: 4 };
        allItems.forEach(function (row) {
            const key = calendarDateKey(row.date || row.datetime);
            if (!key || key.slice(0, 7) !== year + '-' + String(month + 1).padStart(2, '0')) return;
            markers[key] = (markers[key] || 0) + 1;
            if (!markerTones[key]) markerTones[key] = [];
            const tone = calendarToneClass(row);
            if (!markerTones[key].includes(tone)) markerTones[key].push(tone);
            const semanticState = calendarSemanticState(row);
            if (!markerStates[key] || stateRank[semanticState] > stateRank[markerStates[key]]) markerStates[key] = semanticState;
        });
        const monthLabel = new Intl.DateTimeFormat(document.documentElement.lang || 'es', { month: 'long', year: 'numeric' }).format(now);
        const weekDays = ['L','M','M','J','V','S','D'];
        let cells = '';
        for (let i = 0; i < startWeekday; i++) cells += '<span class="dashboard-mini-calendar__cell is-empty" aria-hidden="true"></span>';
        for (let day = 1; day <= totalDays; day++) {
            const dateKey = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            const isToday = day === now.getDate();
            const count = markers[dateKey] || 0;
            const label = day + ' · ' + (count ? count + ' ' + S('calendar_day_schedule', 'programado') : S('calendar_day_empty', 'sin actividades programadas'));
            const dots = (markerTones[dateKey] || []).slice(0, 4).map(function (tone) { return '<i class="dashboard-mini-calendar__tone ' + tone + '" aria-hidden="true"></i>'; }).join('');
            const dayState = markerStates[dateKey] || '';
            cells += '<button type="button" class="dashboard-mini-calendar__cell dashboard-mini-calendar__day' + (isToday ? ' is-today' : '') + (count ? ' has-events' : '') + (dayState ? ' is-state-' + dayState : '') + '" data-calendar-date="' + dateKey + '" aria-label="' + escapeHtml(label) + '"><strong>' + day + '</strong>' + (dots ? '<span class="dashboard-mini-calendar__tones">' + dots + '</span>' : '') + (count ? '<em>' + count + '</em>' : '') + '</button>';
        }
        const upcoming = allItems.filter(function (item) {
            const key = calendarDateKey(item.date || item.datetime);
            return key && key >= calendarDateKey(now) && key.slice(0, 7) === year + '-' + String(month + 1).padStart(2, '0');
        }).sort(function (a, b) { return String(a.datetime || a.date).localeCompare(String(b.datetime || b.date)); }).slice(0, 4);
        const upcomingRows = upcoming.map(function (item) {
            const meta = calendarItemMeta(item);
            const tone = calendarToneClass(item);
            const content = '<span class="dashboard-calendar-feed__icon"><i class="bi ' + meta[0] + '"></i></span><div><strong>' + escapeHtml(item.title || '') + '</strong><small>' + escapeHtml(formatDate(item.datetime || item.date)) + (item.origin === 'personal' ? ' · ' + escapeHtml(calendarPriorityLabel(item.priority)) : '') + '</small></div>';
            return item.href && item.origin !== 'personal' ? '<a class="dashboard-calendar-feed__item ' + tone + '" href="' + escapeHtml(item.href) + '">' + content + '</a>' : '<div class="dashboard-calendar-feed__item ' + tone + '">' + content + '</div>';
        }).join('');
        summaryCalendar.innerHTML = '<div class="dashboard-mini-calendar"><div class="dashboard-mini-calendar__head"><strong>' + escapeHtml(monthLabel) + '</strong><small>' + escapeHtml(S('calendar_hover_hint', 'Pasa el mouse o selecciona un día para ver lo programado.')) + '</small></div><div class="dashboard-mini-calendar__week">' + weekDays.map(function (day) { return '<span>' + day + '</span>'; }).join('') + '</div><div class="dashboard-mini-calendar__grid">' + cells + '</div></div>'
            + '<div class="dashboard-calendar-day-panel" data-calendar-day-panel><p class="dashboard-calendar-day-empty">' + escapeHtml(S('calendar_hover_hint', 'Pasa el mouse o selecciona un día para ver lo programado.')) + '</p></div>'
            + '<div class="dashboard-calendar-feed"><span class="dashboard-calendar-feed__title">' + escapeHtml(S('calendar_day_schedule', 'Próximas actividades')) + '</span>' + (upcomingRows || '<div class="dashboard-summary-empty dashboard-summary-empty--compact"><span>' + escapeHtml(S('calendar_no_events', 'Sin registros este mes.')) + '</span></div>') + '</div>';
        const dayButtons = summaryCalendar.querySelectorAll('[data-calendar-date]');
        dayButtons.forEach(function (button) {
            const show = function () { renderCalendarDayDetail(button.dataset.calendarDate || '', allItems); };
            button.addEventListener('mouseenter', show);
            button.addEventListener('focus', show);
            button.addEventListener('click', show);
        });
    }

    function renderSummaryNotifications(rows) {
        if (!summaryNotifications) return;
        if (!rows.length) {
            summaryNotifications.innerHTML = '<div class="dashboard-summary-empty"><i class="bi bi-bell" aria-hidden="true"></i><span>' + escapeHtml(S('recent_empty', 'Sin notificaciones pendientes.')) + '</span></div>';
            return;
        }
        const items = rows.map(function (row) {
            const title = String(row.title || '');
            const count = row.show_count ? Number(row.count || 0) : 0;
            const visibleTitle = (count > 0 ? count + ' · ' : '') + title;
            const href = String(row.href || '../usuarios/gestiones.php');
            return '<a class="dashboard-notice dashboard-notice--link" href="' + escapeHtml(href) + '"><span class="dashboard-notice__icon"><i class="bi ' + escapeHtml(row.icon || 'bi-bell') + '"></i></span><div class="dashboard-notice__body"><strong>' + escapeHtml(visibleTitle) + '</strong>' + (row.meta ? '<small>' + escapeHtml(row.meta) + '</small>' : '') + '</div><i class="bi bi-arrow-right dashboard-notice__open" aria-hidden="true"></i></a>';
        });
        summaryNotifications.innerHTML = items.join('');
    }


    function updateReminderPriorityPreview() {
        const select = $('dashboardReminderPriority');
        const preview = $('dashboardReminderPriorityPreview');
        if (!select || !preview) return;
        preview.className = 'dashboard-reminder-priority-preview is-' + String(select.value || 'medium');
        preview.setAttribute('title', calendarPriorityLabel(select.value));
    }
    const dashboardReminderPriority = $('dashboardReminderPriority');
    if (dashboardReminderPriority) {
        dashboardReminderPriority.addEventListener('change', updateReminderPriorityPreview);
        updateReminderPriorityPreview();
    }

    const dashboardReminderForm = $('dashboardReminderForm');
    if (dashboardReminderForm) {
        dashboardReminderForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const title = String(($('dashboardReminderTitle') || {}).value || '').trim();
            const date = String(($('dashboardReminderDate') || {}).value || '').trim();
            if (!title || !date) return;
            const rows = loadPersonalCalendarItems();
            rows.push({
                id: 'local-' + Date.now(),
                origin: 'personal',
                type: String(($('dashboardReminderType') || {}).value || 'note'),
                priority: String(($('dashboardReminderPriority') || {}).value || 'medium'),
                title: title,
                details: String(($('dashboardReminderDetails') || {}).value || '').trim(),
                date: date,
                time: String(($('dashboardReminderTime') || {}).value || '').trim(),
                datetime: date + ' ' + (String(($('dashboardReminderTime') || {}).value || '').trim() || '09:00') + ':00'
            });
            savePersonalCalendarItems(rows);
            const feedback = $('dashboardReminderFeedback');
            if (feedback) { feedback.textContent = S('calendar_note_saved', 'Guardado en este dispositivo.'); feedback.classList.remove('d-none'); }
            if (lastDashboardData) renderSummaryCalendar(lastDashboardData.calendario || []);
            const modalEl = $('dashboardReminderModal');
            if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                window.setTimeout(function () { window.bootstrap.Modal.getOrCreateInstance(modalEl).hide(); }, 250);
            }
        });
    }

    function progressTimelineRows(rows) {
        let cumulative = 0;
        return (rows || []).map(function (row) {
            const periodTotal = ['eventos', 'formularios', 'protocolos', 'evaluaciones'].reduce(function (sum, key) {
                return sum + Math.max(0, Number(row[key] || 0));
            }, 0);
            cumulative += periodTotal;
            return {
                periodo: String(row.periodo || ''),
                periodTotal: periodTotal,
                cumulative: cumulative,
                eventos: Number(row.eventos || 0),
                formularios: Number(row.formularios || 0),
                protocolos: Number(row.protocolos || 0),
                evaluaciones: Number(row.evaluaciones || 0)
            };
        });
    }

    function renderProgressTimeline(rows) {
        if (!keyProgressChart || !keyProgressMeta) return;
        const points = progressTimelineRows(rows);
        if (!points.length) {
            keyProgressChart.innerHTML = '<div class="dashboard-trend-empty">' + escapeHtml(S('no_data', 'Sin datos para el período seleccionado.')) + '</div>';
            keyProgressMeta.innerHTML = '';
            return;
        }

        const compact = window.matchMedia('(max-width: 767.98px)').matches;
        const width = 920;
        const height = compact ? 286 : 300;
        const left = compact ? 44 : 54;
        const right = 22;
        const top = 22;
        const bottom = compact ? 58 : 50;
        const plotW = width - left - right;
        const plotH = height - top - bottom;
        const max = Math.max(1, points[points.length - 1].cumulative);
        const x = function (i) { return left + (points.length === 1 ? plotW / 2 : (i / (points.length - 1)) * plotW); };
        const y = function (value) { return top + plotH - (Number(value || 0) / max) * plotH; };
        const linePoints = points.map(function (row, index) { return x(index) + ',' + y(row.cumulative); }).join(' ');
        const areaPoints = left + ',' + (top + plotH) + ' ' + linePoints + ' ' + (width - right) + ',' + (top + plotH);
        let svg = '<svg class="dashboard-progress-timeline-svg" viewBox="0 0 ' + width + ' ' + height + '" role="img" aria-label="' + escapeHtml(S('progress_timeline_intro', 'Evolución de los registros operacionales acumulados dentro del período seleccionado.')) + '">';

        [0, .25, .5, .75, 1].forEach(function (ratio) {
            const yy = top + plotH - ratio * plotH;
            svg += '<line x1="' + left + '" y1="' + yy + '" x2="' + (width - right) + '" y2="' + yy + '" class="dashboard-progress-timeline-grid"/>';
            svg += '<text x="' + (left - 8) + '" y="' + (yy + 4) + '" text-anchor="end" class="dashboard-progress-timeline-axis">' + Math.round(max * ratio) + '</text>';
        });

        const maxLabels = compact ? 4 : 7;
        const labelStep = Math.max(1, Math.ceil(points.length / maxLabels));
        points.forEach(function (row, index) {
            if (points.length <= maxLabels || index % labelStep === 0 || index === points.length - 1) {
                const full = row.periodo;
                const limit = compact ? 9 : 13;
                const label = full.length > limit ? full.slice(0, limit - 1) + '…' : full;
                svg += '<text x="' + x(index) + '" y="' + (height - 16) + '" text-anchor="middle" class="dashboard-progress-timeline-axis"><title>' + escapeHtml(full) + '</title>' + escapeHtml(label) + '</text>';
            }
        });

        svg += '<polygon class="dashboard-progress-timeline-area" points="' + areaPoints + '"></polygon>';
        svg += '<polyline class="dashboard-progress-timeline-line" points="' + linePoints + '"></polyline>';
        points.forEach(function (row, index) {
            const breakdown = S('trend_events', 'Eventos') + ' ' + row.eventos
                + ' · ' + S('trend_forms', 'Formularios') + ' ' + row.formularios
                + ' · ' + S('trend_protocols', 'Protocolos') + ' ' + row.protocolos
                + ' · ' + S('trend_evaluations', 'Evaluaciones finalizadas') + ' ' + row.evaluaciones;
            const label = S('progress_timeline_series', 'Registros acumulados') + ' · ' + row.periodo + ': ' + row.cumulative
                + '. ' + S('progress_timeline_period_total', 'Registros del período') + ': ' + row.periodTotal
                + '. ' + breakdown;
            svg += '<circle class="dashboard-progress-timeline-point" cx="' + x(index) + '" cy="' + y(row.cumulative) + '" r="5" tabindex="0" focusable="true" role="img" aria-label="' + escapeHtml(label) + '"><title>' + escapeHtml(label) + '</title></circle>';
        });
        svg += '</svg>';
        keyProgressChart.innerHTML = svg;

        const peak = points.reduce(function (best, row) {
            return row.periodTotal > best.periodTotal ? row : best;
        }, points[0]);
        const last = points[points.length - 1];
        keyProgressMeta.innerHTML = '<div class="dashboard-progress-timeline-explainer"><i class="bi bi-info-circle" aria-hidden="true"></i><span>'
            + escapeHtml(S('progress_timeline_explainer', 'La línea suma, período a período, los eventos, formularios enviados, protocolos y evaluaciones finalizadas según los filtros activos. Cada punto muestra el total acumulado hasta ese momento; no representa un porcentaje de cumplimiento.'))
            + '</span></div>'
            + '<div class="dashboard-progress-timeline-meta__stats">'
            + '<span class="dashboard-progress-timeline-meta__item"><small>' + escapeHtml(S('progress_timeline_series', 'Registros acumulados')) + '</small><strong>' + escapeHtml(last.cumulative) + '</strong></span>'
            + '<span class="dashboard-progress-timeline-meta__item"><small>' + escapeHtml(S('activity_snapshot_peak', 'Mayor actividad')) + '</small><strong>' + escapeHtml(peak.periodo) + ' · ' + escapeHtml(peak.periodTotal) + '</strong></span>'
            + '</div>';
    }

    function renderMetrics(data) {
        renderProgressTimeline(data.tendencia || []);
    }

    function renderRoleFocus(data, consolidated, forcedRole) {
        const role = forcedRole || (consolidated ? 'administrador_completo' : (data.role || pageRole));
        const personal = data.personal || {};
        let progress = 0;
        let ringLabel = S('approval_company', 'Aprobación de inducciones');
        let ringTitle = S('status_title', 'Estado general');
        const tags = [];
        let priorities = [];

        if (consolidated) {
            const rates = (data.ranking_empresas || []).map(function (row) { return Number(row.tasa_aprobacion_induccion); }).filter(Number.isFinite);
            progress = rates.length ? rates.reduce(function (a, b) { return a + b; }, 0) / rates.length : 0;
            ringLabel = S('approval_global', 'Aprobación promedio');
            tags.push([S('kpi_companies', 'Empresas activas'), data.totales?.empresas || 0]);
            tags.push([S('programs_in_progress', 'Programas en curso'), data.programas?.en_curso || 0]);
            priorities = [
                [S('priority_critical_events', 'Eventos críticos abiertos'), data.eventos?.abiertos_criticos || 0, 'bi-shield-exclamation', 'danger'],
                [S('priority_protocol_overdue', 'Protocolos vencidos'), data.protocolos?.asignaciones_vencidas || 0, 'bi-calendar-x', 'warning'],
                [S('priority_protocol_review', 'Protocolos por revisar'), data.protocolos?.pendientes_revision || 0, 'bi-clipboard2-pulse', 'warning']
            ];
        } else if (role === 'trabajador') {
            const induction = personal.induccion || {};
            progress = induction.avance || 0;
            ringLabel = S('dashboard_worker_completion', 'Inducciones completadas');
            ringTitle = S('dashboard_worker_progress_title', 'Mi avance de inducción');
            tags.push([S('dashboard_worker_approved', 'Aprobadas'), induction.aprobadas || 0]);
            tags.push([S('dashboard_worker_failed', 'Reprobadas'), induction.reprobadas || 0]);
            const inductionHref = '../induccion/mis-induccion.php';
            priorities = [
                [S('dashboard_worker_overdue', 'Inducciones vencidas'), induction.vencidas || 0, 'bi-clock-history', 'danger', inductionHref],
                [S('dashboard_worker_in_progress', 'En curso'), induction.en_curso || 0, 'bi-play-circle', 'warning', inductionHref],
                [S('dashboard_worker_to_start', 'Por iniciar'), induction.por_iniciar || 0, 'bi-journal-plus', 'warning', inductionHref]
            ];
        } else if (role === 'jefatura') {
            progress = data.induccion?.tasa_aprobacion == null ? 0 : data.induccion.tasa_aprobacion;
            ringLabel = S('approval_company', 'Aprobación de inducciones');
            ringTitle = S('status_title', 'Cumplimiento del equipo');
            tags.push([S('kpi_workers', 'Trabajadores activos'), data.totales?.trabajadores || 0]);
            tags.push([S('metric_audits_open', 'Auditorías abiertas'), Number(data.auditorias?.por_estado_operativo?.pendiente || 0) + Number(data.auditorias?.por_estado_operativo?.en_curso || 0)]);
            const auditStates = data.auditorias?.por_estado_operativo || {};
            priorities = [
                [S('priority_critical_events', 'Eventos críticos abiertos'), data.eventos?.abiertos_criticos || 0, 'bi-shield-exclamation', 'danger'],
                [S('priority_induction_overdue', 'Inducciones vencidas pendientes'), data.induccion?.vencidas_pendientes || 0, 'bi-journal-x', 'warning'],
                [S('metric_audits_open', 'Auditorías abiertas'), Number(auditStates.pendiente || 0) + Number(auditStates.en_curso || 0), 'bi-shield-check', 'warning'],
                [S('priority_protocol_overdue', 'Protocolos vencidos'), data.protocolos?.asignaciones_vencidas || 0, 'bi-calendar-x', 'warning'],
                [S('metric_programs_below', 'Programas bajo meta'), data.programas?.bajo_meta || 0, 'bi-graph-down-arrow', 'warning']
            ];
        } else if (role === 'cliente') {
            progress = data.programas?.promedio_avance || 0;
            ringLabel = S('metric_program_progress', 'Avance de programas');
            ringTitle = S('status_title', 'Estado de supervisión');
            tags.push([S('kpi_projects', 'Proyectos activos'), data.totales?.proyectos || 0]);
            tags.push([S('approval_company', 'Aprobación de inducciones'), formatPct(data.induccion?.tasa_aprobacion || 0)]);
            const auditStates = data.auditorias?.por_estado_operativo || {};
            priorities = [
                [S('priority_critical_events', 'Eventos críticos abiertos'), data.eventos?.abiertos_criticos || 0, 'bi-shield-exclamation', 'danger'],
                [S('priority_protocol_overdue', 'Protocolos vencidos'), data.protocolos?.asignaciones_vencidas || 0, 'bi-calendar-x', 'warning'],
                [S('priority_protocol_review', 'Protocolos por revisar'), data.protocolos?.pendientes_revision || 0, 'bi-clipboard2-pulse', 'warning'],
                [S('metric_programs_below', 'Programas bajo meta'), data.programas?.bajo_meta || 0, 'bi-graph-down-arrow', 'warning'],
                [S('metric_audits_open', 'Auditorías abiertas'), Number(auditStates.pendiente || 0) + Number(auditStates.en_curso || 0), 'bi-shield-check', 'warning']
            ];
        } else {
            const eventStates = data.eventos?.por_estado || {};
            const eventTotal = Number(data.eventos?.total || 0);
            const eventClosed = Number(eventStates.cerrado || 0);
            progress = eventTotal > 0 ? (eventClosed / eventTotal) * 100 : 0;
            ringLabel = S('events_closed', 'Eventos cerrados');
            ringTitle = S('status_title', 'Estado operacional');
            tags.push([S('kpi_users', 'Usuarios activos'), data.totales?.usuarios || 0]);
            tags.push([S('kpi_projects', 'Proyectos activos'), data.totales?.proyectos || 0]);
            const auditStates = data.auditorias?.por_estado_operativo || {};
            priorities = [
                [S('priority_critical_events', 'Eventos críticos abiertos'), data.eventos?.abiertos_criticos || 0, 'bi-shield-exclamation', 'danger'],
                [S('priority_protocol_overdue', 'Protocolos vencidos'), data.protocolos?.asignaciones_vencidas || 0, 'bi-calendar-x', 'warning'],
                [S('priority_induction_overdue', 'Inducciones vencidas pendientes'), data.induccion?.vencidas_pendientes || 0, 'bi-journal-x', 'warning'],
                [S('metric_audits_open', 'Auditorías abiertas'), Number(auditStates.pendiente || 0) + Number(auditStates.en_curso || 0), 'bi-shield-check', 'warning'],
                [S('priority_protocol_review', 'Protocolos por revisar'), data.protocolos?.pendientes_revision || 0, 'bi-clipboard2-pulse', 'warning']
            ];
        }

        renderStatusRing(progress, ringLabel, ringTitle, tags);
        renderPriorityList(priorities);
        configureStatusPanelAccess();
    }

    function renderOperationsState(data, consolidated, role) {
        if (!operationsState) return;
        const personal = data.personal || {};
        const evals = personal.evaluaciones || {};
        const auditStates = data.auditorias?.por_estado_operativo || {};
        const programs = data.programas || {};
        const rows = [];
        const addPct = function (label, pct, meta, icon) { rows.push({ label: label, pct: clampPct(pct), meta: meta, icon: icon || 'bi-graph-up' }); };
        const addCount = function (label, value, meta, icon, kind) { rows.push({ label: label, value: Number(value || 0), meta: meta, icon: icon || 'bi-circle', kind: kind || 'neutral' }); };

        if (role === 'trabajador' && !consolidated) {
            const induction = personal.induccion || {};
            addPct(S('dashboard_worker_completion', 'Inducciones completadas'), induction.avance || 0, S('dashboard_worker_completion_help', 'Porcentaje de inducciones asignadas que ya finalizaste.'), 'bi-journal-check');
            addPct(S('dashboard_worker_approval_rate', 'Aprobación de completadas'), induction.tasa_aprobacion == null ? 0 : induction.tasa_aprobacion, S('dashboard_worker_approval_help', 'Porcentaje de inducciones finalizadas que aprobaste.'), 'bi-award');
            addCount(S('dashboard_worker_overdue', 'Vencidas'), induction.vencidas || 0, S('dashboard_worker_overdue_help', 'Empieza por estas si tienes alguna vencida.'), 'bi-clock-history', Number(induction.vencidas || 0) > 0 ? 'warning' : 'neutral');
            addCount(S('dashboard_worker_certificates', 'Certificados'), induction.certificados || 0, S('dashboard_worker_certificates_help', 'Certificados obtenidos por inducciones aprobadas.'), 'bi-patch-check', 'neutral');
        } else if (consolidated) {
            addCount(S('kpi_companies', 'Empresas activas'), data.totales?.empresas || 0, S('scope_all_companies', 'Todas las empresas'), 'bi-buildings');
            addCount(S('kpi_open_critical', 'Eventos altos/críticos abiertos'), data.eventos?.abiertos_criticos || 0, S('attention_title', 'Requiere atención'), 'bi-shield-exclamation', Number(data.eventos?.abiertos_criticos || 0) > 0 ? 'danger' : 'neutral');
            addCount(S('kpi_protocol_overdue', 'Protocolos vencidos'), data.protocolos?.asignaciones_vencidas || 0, S('protocol_overdue', 'Vencidos'), 'bi-calendar-x', Number(data.protocolos?.asignaciones_vencidas || 0) > 0 ? 'warning' : 'neutral');
            addPct(S('metric_program_progress', 'Avance de programas'), programs.promedio_avance || 0, S('programs_average', 'Avance promedio'), 'bi-bar-chart-steps');
        } else {
            const eventStates = data.eventos?.por_estado || {};
            const eventTotal = Number(data.eventos?.total || 0);
            const eventClosed = Number(eventStates.cerrado || 0);
            const eventClosePct = eventTotal > 0 ? (eventClosed / eventTotal) * 100 : 0;
            const auditTotal = Object.keys(auditStates).reduce(function (sum, key) { return sum + Number(auditStates[key] || 0); }, 0);
            const auditPct = auditTotal > 0 ? (Number(auditStates.completada || 0) / auditTotal) * 100 : 0;

            if (role === 'jefatura') {
                addPct(S('personal_induction', 'Inducciones'), data.induccion?.tasa_aprobacion || 0, S('rate_label', 'Tasa de aprobación'), 'bi-journal-check');
                addPct(S('personal_audits', 'Auditorías'), auditPct, S('progress_complete', 'Completadas'), 'bi-clipboard2-check');
                addPct(S('kpi_events', 'Eventos del período'), eventClosePct, S('events_closed', 'Cerrados'), 'bi-shield-check');
                addPct(S('metric_program_progress', 'Avance de programas'), programs.promedio_avance || 0, S('programs_average', 'Avance promedio'), 'bi-graph-up-arrow');
            } else if (role === 'cliente') {
                addPct(S('metric_program_progress', 'Avance de programas'), programs.promedio_avance || 0, S('programs_average', 'Avance promedio'), 'bi-graph-up-arrow');
                addPct(S('kpi_events', 'Eventos del período'), eventClosePct, S('events_closed', 'Cerrados'), 'bi-shield-check');
                addPct(S('personal_induction', 'Inducciones'), data.induccion?.tasa_aprobacion || 0, S('rate_label', 'Tasa de aprobación'), 'bi-journal-check');
                addPct(S('personal_audits', 'Auditorías'), auditPct, S('progress_complete', 'Completadas'), 'bi-clipboard2-check');
            } else {
                addPct(S('kpi_events', 'Eventos del período'), eventClosePct, S('events_closed', 'Cerrados'), 'bi-shield-check');
                addPct(S('personal_induction', 'Inducciones'), data.induccion?.tasa_aprobacion || 0, S('rate_label', 'Tasa de aprobación'), 'bi-journal-check');
                addPct(S('personal_audits', 'Auditorías'), auditPct, S('progress_complete', 'Completadas'), 'bi-clipboard2-check');
                addPct(S('metric_program_progress', 'Avance de programas'), programs.promedio_avance || 0, S('programs_average', 'Avance promedio'), 'bi-graph-up-arrow');
            }
        }

        if (isWorkerRole) {
            operationsState.innerHTML = rows.map(function (row) {
                if (Object.prototype.hasOwnProperty.call(row, 'pct')) {
                    return '<div class="dashboard-operation-row"><span class="dashboard-operation-row__icon"><i class="bi ' + escapeHtml(row.icon) + '"></i></span><div class="dashboard-operation-row__body"><div><strong>' + escapeHtml(row.label) + '</strong><span>' + escapeHtml(formatPct(row.pct)) + '</span></div><small>' + escapeHtml(row.meta || '') + '</small><div class="dashboard-operation-row__track"><span style="width:' + clampPct(row.pct) + '%"></span></div></div></div>';
                }
                return '<div class="dashboard-operation-row dashboard-operation-row--' + escapeHtml(row.kind || 'neutral') + '"><span class="dashboard-operation-row__icon"><i class="bi ' + escapeHtml(row.icon) + '"></i></span><div class="dashboard-operation-row__body"><div><strong>' + escapeHtml(row.label) + '</strong><span>' + escapeHtml(row.value) + '</span></div><small>' + escapeHtml(row.meta || '') + '</small></div></div>';
            }).join('');
            return;
        }
        operationsState.__dashboardChartModel = {
            kind: 'operations',
            rows: rows,
            defaultMode: 'bars'
        };
        if (!operationsState.dataset.dashboardChartMode) operationsState.dataset.dashboardChartMode = 'bars';
        renderOperationsChart(operationsState);
    }

    function renderStatusRing(progress, label, title, tags) {
        const container = $('roleStatusContent');
        if (!container) return;
        if (isWorkerRole) {
            const pct = clampPct(progress);
            const ringColor = pct >= 80 ? 'var(--success-dark)' : (pct > 0 ? 'var(--accent)' : '#9EADB8');
            const tagHtml = (tags || []).map(function (tag) {
                return '<span class="dashboard-status-tag"><span>' + escapeHtml(tag[0]) + '</span><strong>' + escapeHtml(tag[1]) + '</strong></span>';
            }).join('');
            container.innerHTML = '<div class="dashboard-status-layout">'
                + '<div class="dashboard-donut" style="--progress:' + pct + ';--ring-color:' + ringColor + '" role="img" aria-label="' + escapeHtml(label + ': ' + formatPct(pct)) + '">'
                + '<div><div class="dashboard-donut__value">' + escapeHtml(formatPct(pct)) + '</div><span class="dashboard-donut__label">' + escapeHtml(label) + '</span></div></div>'
                + '<div class="dashboard-status-copy"><h3>' + escapeHtml(title) + '</h3><p>' + escapeHtml(S('status_intro', 'Una lectura rápida del indicador que más importa para tu perfil.')) + '</p>'
                + '<div class="dashboard-status-tags">' + tagHtml + '</div></div></div>';
            return;
        }
        container.__dashboardChartModel = {
            kind: 'status',
            progress: clampPct(progress),
            label: label,
            title: title,
            tags: tags || [],
            defaultMode: 'bars'
        };
        if (!container.dataset.dashboardChartMode) container.dataset.dashboardChartMode = 'bars';
        renderStatusChartModel(container);
    }

    function renderPriorityList(rows) {
        const container = $('priorityList');
        if (!container) return;
        const active = (rows || []).filter(function (row) { return Number(row[1] || 0) > 0; });
        if (!active.length) {
            container.innerHTML = '<div class="dashboard-priority-empty"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>' + escapeHtml(S('attention_empty', 'No hay pendientes críticos en esta vista.')) + '</span></div>';
            return;
        }
        const items = active.map(function (row) {
            const label = row[0];
            const value = Number(row[1] || 0);
            const icon = row[2] || 'bi-circle';
            const kind = row[3] || 'warning';
            const href = row[4] || '';
            const tag = href ? 'a' : 'div';
            const hrefAttr = href ? ' href="' + escapeHtml(href) + '"' : '';
            return '<' + tag + hrefAttr + ' class="dashboard-priority-item dashboard-priority-item--' + escapeHtml(kind) + (href ? ' dashboard-priority-item--link' : '') + '">'
                + '<span class="dashboard-priority-item__icon"><i class="bi ' + escapeHtml(icon) + '"></i></span>'
                + '<div class="dashboard-priority-item__copy"><strong>' + escapeHtml(label) + '</strong></div>'
                + '<span class="dashboard-priority-item__value">' + value + '</span>'
                + (href ? '<i class="bi bi-arrow-right dashboard-priority-item__arrow" aria-hidden="true"></i>' : '')
                + '</' + tag + '>';
        });
        renderExpandableCollection(container, items, 4);
        const priorityToggle = container.querySelector('.dashboard-list-toggle');
        if (priorityToggle) priorityToggle.dataset.extraCount = String(Math.max(0, items.length - 4));
    }

    function renderPersonalProgress(personal) {
        if (!personalProgressSection) return;
        if (!personal || !personal.evaluaciones) {
            personalProgressSection.classList.add('d-none');
            return;
        }
        const evaluations = personal.evaluaciones || {};
        const items = [
            ['induccion', S('personal_induction', 'Inducciones'), 'bi-journal-text', 'induction'],
            ['auditoria', S('personal_audits', 'Auditorías'), 'bi-shield-check', 'audits'],
            ['autoevaluacion', S('personal_self', 'Autoevaluaciones'), 'bi-clipboard-check', 'self']
        ];
        const cards = items.map(function (item) {
            const data = evaluations[item[0]] || {};
            const pct = clampPct(data.avance || 0);
            const state = pct >= 100 ? 'complete' : (pct > 0 ? 'progress' : 'pending');
            const status = pct >= 100 ? S('progress_complete', 'Completa') : (pct > 0 ? S('progress_in_progress', 'En curso') : S('progress_pending', 'Por completar'));
            const openLabel = S('graph_open_module', 'Abrir módulo') + ': ' + item[1];
            return '<article class="dashboard-progress-card dashboard-progress-card--' + state + '" data-dashboard-nav="' + escapeHtml(item[3]) + '" tabindex="0" role="link" aria-label="' + escapeHtml(openLabel) + '">'
                + '<div class="dashboard-progress-card__head"><div style="display:flex;gap:.7rem;min-width:0"><span class="dashboard-progress-card__icon"><i class="bi ' + item[2] + '"></i></span><div><strong>' + escapeHtml(item[1]) + '</strong><small>' + escapeHtml(status) + '</small></div></div><span class="dashboard-progress-card__head-actions"><span class="dashboard-progress-card__percent">' + escapeHtml(formatPct(pct)) + '</span><i class="bi bi-arrow-up-right dashboard-progress-card__open" aria-hidden="true"></i></span></div>'
                + '<div class="dashboard-progress-meta"><span>' + escapeHtml(S('progress_label', 'Progreso')) + '</span><strong>' + escapeHtml((data.finalizadas || 0) + ' / ' + (data.total || 0)) + '</strong></div>'
                + '<div class="dashboard-progress-bar-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + pct + '"><span style="width:' + pct + '%"></span></div>'
                + '</article>';
        }).join('');
        $('personalProgressGrid').innerHTML = cards;
        personalProgressSection.classList.remove('d-none');
    }

    function renderPrograms(programs, consolidated, role) {
        if (!programsSection) return;
        const total = Number(programs.total || 0);
        if (total <= 0 || (!consolidated && role === 'trabajador')) {
            programsSection.classList.add('d-none');
            return;
        }
        programsSection.classList.remove('d-none');
        const avg = clampPct(programs.promedio_avance || 0);
        const inProgress = consolidated ? Number(programs.en_curso || 0) : Number(programs.por_estado?.en_curso || 0);
        const below = consolidated ? 0 : Number(programs.bajo_meta || 0);
        $('programSummary').innerHTML = [
            ['bi-bar-chart-steps', S('programs_average', 'Avance promedio'), formatPct(avg)],
            ['bi-play-circle', S('programs_in_progress', 'Programas en curso'), inProgress],
            ['bi-bullseye', S('programs_below_target', 'Bajo meta'), below]
        ].map(function (row) {
            return '<div class="dashboard-program-stat"><i class="bi ' + row[0] + '"></i><span>' + escapeHtml(row[1]) + '</span><strong>' + escapeHtml(row[2]) + '</strong></div>';
        }).join('');

        const detail = programs.detalle || [];
        const list = $('programProgressList');
        if (!list) return;
        list.__dashboardChartModel = {
            kind: 'program-progress',
            rows: detail,
            defaultMode: 'bars'
        };
        if (!list.dataset.dashboardChartMode) list.dataset.dashboardChartMode = 'bars';
        renderProgramProgressChart(list);
    }

    function renderEventState(events) {
        const container = $('eventsState');
        if (!container) return;
        const states = events.por_estado || {};
        const open = Number(states.abierto || 0);
        const progress = Number(states.en_proceso || 0);
        const closed = Number(states.cerrado || 0);
        const total = Number(events.total || 0);
        registerCategoricalChart(container, [
            [S('events_open', 'Abierto'), open, '#AA2424'],
            [S('events_in_progress', 'En proceso'), progress, 'var(--accent)'],
            [S('events_closed', 'Cerrado'), closed, 'var(--success-dark)']
        ], total, {
            defaultMode: 'bars',
            centerLabel: S('kpi_events', 'Eventos')
        });
    }

    function renderBars(container, rows, total, limit) {
        registerCategoricalChart(container, rows, total, {
            defaultMode: 'bars',
            limit: limit || 6,
            centerLabel: S('summary_title', 'Total')
        });
    }

    function renderLabelBars(container, rows) {
        const total = rows.reduce(function (sum, row) { return sum + Number(row.cantidad || 0); }, 0);
        renderBars(container, rows.map(function (row, idx) {
            return [row.label, row.cantidad, idx % 2 ? 'fill-info' : 'fill-dark'];
        }), total);
    }

    function renderEvaluation(container, rateEl, data) {
        const total = Number(data.total || 0);
        renderBars(container, [
            [S('eval_approved', 'Aprobadas'), data.aprobados || 0, 'fill-good'],
            [S('eval_pending', 'Pendientes'), data.pendientes || 0, 'fill-warn'],
            [S('eval_failed', 'Reprobadas'), data.reprobados || 0, 'fill-bad']
        ], total);
        if (!rateEl) return;
        const rate = data.tasa_aprobacion;
        rateEl.innerHTML = '<span>' + escapeHtml(S('rate_label', 'Tasa de aprobación')) + '</span><strong>' + (rate == null ? escapeHtml(S('rate_empty', 'Sin resultados')) : escapeHtml(formatPct(rate))) + '</strong>';
        if (Number(data.vencidas_pendientes || 0) > 0) {
            rateEl.innerHTML += '<span title="' + escapeHtml(S('overdue_pending', 'Pendientes vencidas')) + '"><i class="bi bi-clock-history"></i> ' + escapeHtml(data.vencidas_pendientes) + '</span>';
        }
    }

    function renderProtocols(data) {
        $('protocolVisible').textContent = data.protocolos_visibles || 0;
        $('protocolOverdue').textContent = data.asignaciones_vencidas || 0;
        $('trackingOverdue').textContent = data.seguimientos_vencidos || 0;
        const assignments = data.asignaciones || {};
        renderBars($('protocolAssignments'), [
            [S('protocol_active', 'Activas'), assignments.activa || 0, 'fill-info'],
            [S('protocol_suspended', 'Suspendidas'), assignments.suspendida || 0, 'fill-warn'],
            [S('protocol_closed', 'Cerradas'), assignments.cerrada || 0, 'fill-good'],
            [S('protocol_cancelled', 'Canceladas'), assignments.cancelada || 0, 'fill-neutral']
        ], assignments.total || 0);
        const results = data.ejecuciones || {};
        renderBars($('protocolResults'), [
            [S('protocol_pending_review', 'Pendiente revisión'), results.pendiente_revision || 0, 'fill-warn'],
            [S('protocol_conforme', 'Conforme'), results.conforme || 0, 'fill-good'],
            [S('protocol_observado', 'Observado'), results.observado || 0, 'fill-info'],
            [S('protocol_no_conforme', 'No conforme'), results.no_conforme || 0, 'fill-bad'],
            [S('protocol_no_aplica', 'No aplica'), results.no_aplica || 0, 'fill-neutral']
        ], results.total || 0);
    }

    function renderForms(data) {
        $('formsActive').textContent = data.formularios_activos || 0;
        $('formsSent').textContent = data.envios || 0;
        $('formsUsers').textContent = data.usuarios_participantes || 0;
        renderLabelBars($('formsTop'), data.formularios_mas_usados || []);
    }

    function recentMeta(type) {
        const map = {
            evento: ['bi-exclamation-triangle', S('recent_event', 'Evento')],
            formulario: ['bi-card-checklist', S('recent_form', 'Formulario')],
            protocolo: ['bi-clipboard2-pulse', S('recent_protocol', 'Protocolo')],
            auditoria: ['bi-clipboard2-check', S('recent_audit', 'Auditoría')],
            evaluacion: ['bi-journal-check', S('personal_progress_title', 'Evaluación')]
        };
        return map[type] || ['bi-activity', type];
    }

    function formatDate(value) {
        if (!value) return '';
        const d = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return String(value);
        return new Intl.DateTimeFormat(document.documentElement.lang || 'es', {
            day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
        }).format(d);
    }

    function renderRecent(rows) {
        const container = $('recentList');
        if (!container) return;
        if (!rows.length) {
            container.innerHTML = '<p class="text-muted small mb-0">' + escapeHtml(S('recent_empty', 'Sin actividad reciente para el período.')) + '</p>';
            return;
        }
        const recentItems = rows.map(function (row) {
            const meta = recentMeta(row.tipo);
            return '<div class="dashboard-recent-item"><span class="dashboard-recent-icon" title="' + escapeHtml(meta[1]) + '"><i class="bi ' + meta[0] + '"></i></span>'
                + '<div><div class="dashboard-recent-title">' + escapeHtml(row.titulo) + '</div><div class="dashboard-recent-detail">' + escapeHtml(row.detalle || meta[1]) + '</div></div>'
                + '<span class="dashboard-recent-date">' + escapeHtml(formatDate(row.fecha)) + '</span></div>';
        });
        renderExpandableCollection(container, recentItems, 4);
        const recentToggle = container.querySelector('.dashboard-list-toggle');
        if (recentToggle) recentToggle.dataset.extraCount = String(Math.max(0, recentItems.length - 4));
    }

    function renderRanking(rows) {
        const body = $('rankingBody');
        if (!body) return;
        const panel = body.closest('.dashboard-panel');
        if (panel) {
            const oldToggle = panel.querySelector('.dashboard-list-toggle[data-dashboard-rows="ranking"]');
            if (oldToggle) oldToggle.remove();
        }
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="5" class="text-muted small text-center">' + escapeHtml(S('ranking_empty', 'No hay empresas activas para comparar.')) + '</td></tr>';
            return;
        }
        body.innerHTML = rows.map(function (row, index) {
            const critical = Number(row.eventos_criticos_abiertos || 0);
            const overdue = Number(row.protocolos_vencidos || 0);
            const review = Number(row.protocolos_pendientes_revision || 0);
            const rate = row.tasa_aprobacion_induccion;
            const rateText = rate == null ? '—' : formatPct(rate);
            const extraClass = index >= 6 ? ' class="dashboard-ranking-extra" hidden' : '';
            return '<tr' + extraClass + '><td>' + escapeHtml(row.razon_social) + '</td>'
                + '<td class="text-end' + (critical > 0 ? ' ranking-cell-bad' : '') + '">' + critical + '</td>'
                + '<td class="text-end' + (overdue > 0 ? ' ranking-cell-bad' : '') + '">' + overdue + '</td>'
                + '<td class="text-end' + (review > 0 ? ' ranking-cell-warn' : '') + '">' + review + '</td>'
                + '<td class="text-end">' + escapeHtml(rateText) + '</td></tr>';
        }).join('');
        if (rows.length > 6 && panel) {
            const holder = document.createElement('div');
            holder.innerHTML = listToggleMarkup(rows.length - 6, 'data-dashboard-rows="ranking"');
            panel.appendChild(holder.firstElementChild);
        }
    }

    function renderTrend(rows) {
        const wrap = $('trendChart');
        const legend = $('trendLegend');
        if (!wrap || !legend) return;
        if (!rows.length) {
            wrap.innerHTML = '<div class="dashboard-trend-empty">' + escapeHtml(S('no_data', 'Sin datos para el período seleccionado.')) + '</div>';
            legend.innerHTML = '';
            return;
        }

        const isWorkerInductionTrend = rows.some(function (row) { return Object.prototype.hasOwnProperty.call(row, 'asignadas'); });
        const series = isWorkerInductionTrend
            ? [
                ['asignadas', S('dashboard_worker_assigned_short', 'Asignadas'), '#005C9A'],
                ['aprobadas', S('dashboard_worker_approved_short', 'Aprobadas'), '#006725'],
                ['reprobadas', S('dashboard_worker_failed_short', 'Reprobadas'), '#BC5921']
              ]
            : [
                ['eventos', S('trend_events', 'Eventos'), '#BC5921'],
                ['formularios', S('trend_forms', 'Formularios'), '#005C9A'],
                ['protocolos', S('trend_protocols', 'Protocolos'), '#44549B'],
                ['evaluaciones', S('trend_evaluations', 'Evaluaciones finalizadas'), '#006725']
              ];
        const compactChart = window.matchMedia('(max-width: 767.98px)').matches;
        const width = 920;
        const height = compactChart ? 286 : 300;
        const left = compactChart ? 44 : 52;
        const right = 20;
        const top = 20;
        const bottom = compactChart ? 58 : 54;
        const plotW = width - left - right;
        const plotH = height - top - bottom;
        let max = 0;
        rows.forEach(function (row) {
            series.forEach(function (s) { max = Math.max(max, Number(row[s[0]] || 0)); });
        });
        max = Math.max(1, max);
        const x = function (i) { return left + (rows.length === 1 ? plotW / 2 : (i / (rows.length - 1)) * plotW); };
        const y = function (v) { return top + plotH - (Number(v || 0) / max) * plotH; };

        let svg = '<svg class="dashboard-trend-svg" viewBox="0 0 ' + width + ' ' + height + '" role="img">';
        [0, 0.25, 0.5, 0.75, 1].forEach(function (ratio) {
            const yy = top + plotH - ratio * plotH;
            svg += '<line x1="' + left + '" y1="' + yy + '" x2="' + (width - right) + '" y2="' + yy + '" stroke="rgba(96,121,139,.18)" stroke-width="1"/>';
            svg += '<text x="' + (left - 8) + '" y="' + (yy + 4) + '" text-anchor="end" font-size="10" fill="#60798B">' + Math.round(max * ratio) + '</text>';
        });
        const maxAxisLabels = compactChart ? 4 : 6;
        const labelStep = Math.max(1, Math.ceil(rows.length / maxAxisLabels));
        rows.forEach(function (row, i) {
            if (rows.length <= maxAxisLabels || i % labelStep === 0 || i === rows.length - 1) {
                const fullLabel = String(row.periodo || '');
                const maxChars = compactChart ? 9 : 12;
                const shortLabel = fullLabel.length > maxChars ? fullLabel.slice(0, maxChars - 1) + '…' : fullLabel;
                svg += '<text x="' + x(i) + '" y="' + (height - 16) + '" text-anchor="middle" font-size="' + (compactChart ? 9 : 10) + '" fill="#60798B"><title>' + escapeHtml(fullLabel) + '</title>' + escapeHtml(shortLabel) + '</text>';
            }
        });
        series.forEach(function (s) {
            const points = rows.map(function (row, i) { return x(i) + ',' + y(row[s[0]]); }).join(' ');
            svg += '<polyline class="dashboard-trend-series" data-trend-series="' + escapeHtml(s[0]) + '" fill="none" stroke="' + s[2] + '" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="' + points + '"/>';
            rows.forEach(function (row, i) {
                const value = Number(row[s[0]] || 0);
                const pointLabel = s[1] + ' · ' + row.periodo + ': ' + value;
                const navMap = { eventos: 'events', formularios: 'forms', protocolos: 'protocols' };
                const navAttr = navMap[s[0]] ? ' data-dashboard-series-nav="' + navMap[s[0]] + '"' : '';
                svg += '<circle class="dashboard-trend-point" data-trend-series="' + escapeHtml(s[0]) + '"' + navAttr + ' cx="' + x(i) + '" cy="' + y(value) + '" r="4" fill="' + s[2] + '" tabindex="0" focusable="true" role="img" aria-label="' + escapeHtml(pointLabel) + '"><title>' + escapeHtml(pointLabel) + '</title></circle>';
            });
        });
        svg += '</svg>';
        wrap.innerHTML = svg;
        legend.innerHTML = series.map(function (s) {
            return '<button type="button" class="dashboard-trend-legend__button" data-trend-series="' + escapeHtml(s[0]) + '" aria-pressed="false"><i class="dashboard-trend-dot" style="background:' + s[2] + '"></i>' + escapeHtml(s[1]) + '</button>';
        }).join('');

        legend.querySelectorAll('.dashboard-trend-legend__button').forEach(function (button) {
            button.addEventListener('click', function () {
                const key = button.dataset.trendSeries;
                const wasActive = button.getAttribute('aria-pressed') === 'true';
                legend.querySelectorAll('.dashboard-trend-legend__button').forEach(function (other) {
                    other.setAttribute('aria-pressed', 'false');
                    other.classList.remove('is-active');
                });
                wrap.querySelectorAll('[data-trend-series]').forEach(function (node) {
                    node.classList.remove('is-dimmed', 'is-emphasized');
                });
                if (wasActive) return;
                button.setAttribute('aria-pressed', 'true');
                button.classList.add('is-active');
                wrap.querySelectorAll('[data-trend-series]').forEach(function (node) {
                    const same = node.getAttribute('data-trend-series') === key;
                    node.classList.toggle('is-emphasized', same);
                    node.classList.toggle('is-dimmed', !same);
                });
            });
        });
    }
});
