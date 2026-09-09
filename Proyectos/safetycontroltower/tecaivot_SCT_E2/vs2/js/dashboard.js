/** Safety Control Tower - Dashboard avanzado / Etapa 2 */
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('.dashboard-shell[data-is-global-admin]');
    if (!root) return;

    const $ = (id) => document.getElementById(id);
    const isGlobalAdmin = root.dataset.isGlobalAdmin === '1';
    let I18N = {};
    try { I18N = JSON.parse(($('dashboardI18n') || {}).textContent || '{}'); } catch (_) {}
    const S = (key, fallback) => I18N[key] || fallback || key;

    const companySelect = $('companySelect');
    const periodFilter = $('periodFilter');
    const projectFilter = $('projectFilter');
    const centerFilter = $('centerFilter');
    const resetFilters = $('resetFilters');
    const dashAlert = $('dashAlert');
    const dashStatus = $('dashStatus');
    const dashContent = $('dashContent');
    const language = $('pageLanguageSelect');

    let currentCompanyId = null;
    let requestSerial = 0;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function showAlert(message, variant) {
        if (!dashAlert) return;
        dashAlert.textContent = message || '';
        dashAlert.classList.remove('d-none','alert-success','alert-info','alert-warning','alert-danger');
        dashAlert.classList.add('alert-' + (variant || 'danger'));
    }
    function hideAlert() { if (dashAlert) dashAlert.classList.add('d-none'); }

    async function api(url) {
        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (typeof data.success === 'undefined') throw new Error('invalid');
            return data;
        } catch (_) {
            return { success:false, message:S('error_response','Respuesta inválida del servidor.') };
        }
    }

    function companyQuery() {
        return isGlobalAdmin && currentCompanyId ? '?id_company=' + encodeURIComponent(currentCompanyId) : '';
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
        if (isGlobalAdmin && !currentCompanyId) return;
        projectFilter.innerHTML = '<option value="">' + escapeHtml(S('all_projects','Todos los proyectos')) + '</option>';
        centerFilter.innerHTML = '<option value="">' + escapeHtml(S('all_centers','Todos los centros')) + '</option>';
        const q = companyQuery();
        const [projects, centers] = await Promise.all([
            api('./proyectos-disponibles.php' + q),
            api('./centros-disponibles.php' + q)
        ]);
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
        if (isGlobalAdmin && currentCompanyId) params.set('id_company', String(currentCompanyId));
        if (projectFilter.value) params.set('id_project', projectFilter.value);
        if (centerFilter.value) params.set('id_center', centerFilter.value);
        params.set('period', periodFilter.value || '90');
        return params;
    }

    async function loadDashboard() {
        if (isGlobalAdmin && !currentCompanyId) return;
        const serial = ++requestSerial;
        hideAlert();
        dashStatus.classList.remove('d-none','alert-danger');
        dashStatus.classList.add('alert-info');
        dashStatus.textContent = S('loading','Cargando indicadores...');
        dashContent.classList.add('d-none');

        const response = await api('./indicadores.php?' + buildParams().toString());
        if (serial !== requestSerial) return;
        if (!response.success) {
            dashStatus.classList.remove('alert-info');
            dashStatus.classList.add('alert-danger');
            dashStatus.textContent = response.message || S('error_response','No se pudieron cargar los indicadores.');
            return;
        }

        dashStatus.classList.add('d-none');
        dashContent.classList.remove('d-none');
        renderAll(response.data || {});
    }

    if (companySelect) {
        companySelect.addEventListener('change', async function () {
            currentCompanyId = companySelect.value ? parseInt(companySelect.value, 10) : null;
            projectFilter.value = '';
            centerFilter.value = '';
            if (!currentCompanyId) {
                dashContent.classList.add('d-none');
                dashStatus.classList.remove('d-none');
                dashStatus.textContent = S('select_company','Selecciona una empresa para ver sus indicadores.');
                return;
            }
            await loadScopeCatalogs();
            await loadDashboard();
        });
        loadCompanies();
    } else {
        loadScopeCatalogs().then(loadDashboard);
    }

    [periodFilter, projectFilter, centerFilter].forEach(function (el) {
        if (el) el.addEventListener('change', loadDashboard);
    });

    if (resetFilters) {
        resetFilters.addEventListener('click', function () {
            periodFilter.value = '90';
            projectFilter.value = '';
            centerFilter.value = '';
            loadDashboard();
        });
    }

    function renderAll(data) {
        renderMetrics(data);
        renderTrend(data.tendencia || []);
        renderBars($('eventsState'), [
            [S('events_open','Abierto'), data.eventos?.por_estado?.abierto || 0, 'fill-bad'],
            [S('events_in_progress','En proceso'), data.eventos?.por_estado?.en_proceso || 0, 'fill-warn'],
            [S('events_closed','Cerrado'), data.eventos?.por_estado?.cerrado || 0, 'fill-good']
        ], data.eventos?.total || 0);
        renderBars($('eventsCriticality'), [
            [S('crit_low','Baja'), data.eventos?.por_criticidad?.baja || 0, 'fill-good'],
            [S('crit_medium','Media'), data.eventos?.por_criticidad?.media || 0, 'fill-warn'],
            [S('crit_high','Alta'), data.eventos?.por_criticidad?.alta || 0, 'fill-bad'],
            [S('crit_critical','Crítica'), data.eventos?.por_criticidad?.critica || 0, 'fill-bad']
        ], data.eventos?.total || 0);
        renderLabelBars($('eventsTypes'), data.eventos?.tipos_principales || []);

        renderEvaluation($('evalInduction'), $('rateInduction'), data.induccion || {});
        renderEvaluation($('evalAudits'), $('rateAudits'), data.auditorias || {});
        renderEvaluation($('evalSelf'), $('rateSelf'), data.autoevaluaciones || {});

        renderProtocols(data.protocolos || {});
        renderForms(data.formularios || {});
        renderRecent(data.actividad_reciente || []);
    }

    function metric(label, value, icon, kind) {
        return '<div class="metric-card ' + (kind || '') + '"><div class="metric-top"><span class="metric-icon"><i class="bi ' + icon + '"></i></span></div><div class="metric-value">' + escapeHtml(value) + '</div><div class="metric-label">' + escapeHtml(label) + '</div></div>';
    }

    function renderMetrics(data) {
        const cards = [];
        if (data.totales?.empresas != null) cards.push(metric(S('kpi_companies','Empresas activas'), data.totales.empresas, 'bi-buildings', ''));
        cards.push(metric(S('kpi_projects','Proyectos activos'), data.totales?.proyectos || 0, 'bi-diagram-3', ''));
        cards.push(metric(S('kpi_centers','Centros / sedes'), data.totales?.centros || 0, 'bi-geo-alt', ''));
        cards.push(metric(S('kpi_workers','Trabajadores activos'), data.totales?.trabajadores || 0, 'bi-people', ''));
        cards.push(metric(S('kpi_events','Eventos del periodo'), data.eventos?.total || 0, 'bi-exclamation-triangle', ''));
        cards.push(metric(S('kpi_open_critical','Eventos altos/críticos abiertos'), data.eventos?.abiertos_criticos || 0, 'bi-shield-exclamation', (data.eventos?.abiertos_criticos || 0) > 0 ? 'attention' : ''));
        cards.push(metric(S('kpi_forms','Formularios enviados'), data.formularios?.envios || 0, 'bi-card-checklist', ''));
        cards.push(metric(S('kpi_protocol_overdue','Protocolos vencidos'), data.protocolos?.asignaciones_vencidas || 0, 'bi-calendar-x', (data.protocolos?.asignaciones_vencidas || 0) > 0 ? 'warning' : ''));
        cards.push(metric(S('kpi_protocol_review','Protocolos por revisar'), data.protocolos?.pendientes_revision || 0, 'bi-clipboard2-pulse', (data.protocolos?.pendientes_revision || 0) > 0 ? 'warning' : ''));
        $('metricGrid').innerHTML = cards.join('');
    }

    function renderBars(container, rows, total) {
        if (!container) return;
        container.innerHTML = '';
        if (!total) {
            container.innerHTML = '<p class="text-muted small mb-0">' + escapeHtml(S('no_data','Sin datos para el periodo seleccionado.')) + '</p>';
            return;
        }
        rows.forEach(function (row) {
            const label = row[0], value = Number(row[1] || 0), cls = row[2] || '';
            const pct = total > 0 ? Math.round((value / total) * 100) : 0;
            const el = document.createElement('div');
            el.className = 'bar-row';
            el.innerHTML = '<span class="bar-label" title="' + escapeHtml(label) + '">' + escapeHtml(label) + '</span><span class="bar-track"><span class="bar-fill ' + cls + '" style="width:' + pct + '%"></span></span><span class="bar-value">' + value + '</span>';
            container.appendChild(el);
        });
    }

    function renderLabelBars(container, rows) {
        const total = rows.reduce((sum, row) => sum + Number(row.cantidad || 0), 0);
        renderBars(container, rows.map((row, idx) => [row.label, row.cantidad, idx % 2 ? 'fill-info' : 'fill-purple']), total);
    }

    function renderEvaluation(container, rateEl, data) {
        const total = Number(data.total || 0);
        renderBars(container, [
            [S('eval_approved','Aprobadas'), data.aprobados || 0, 'fill-good'],
            [S('eval_pending','Pendientes'), data.pendientes || 0, 'fill-warn'],
            [S('eval_failed','Reprobadas'), data.reprobados || 0, 'fill-bad']
        ], total);
        if (!rateEl) return;
        const rate = data.tasa_aprobacion;
        rateEl.innerHTML = '<span>' + escapeHtml(S('rate_label','Tasa de aprobación')) + '</span><strong>' + (rate == null ? escapeHtml(S('rate_empty','Sin resultados')) : escapeHtml(rate + '%')) + '</strong>';
        if (Number(data.vencidas_pendientes || 0) > 0) {
            rateEl.innerHTML += '<span title="' + escapeHtml(S('overdue_pending','Pendientes vencidas')) + '"><i class="bi bi-clock-history"></i> ' + escapeHtml(data.vencidas_pendientes) + '</span>';
        }
    }

    function renderProtocols(data) {
        $('protocolVisible').textContent = data.protocolos_visibles || 0;
        $('protocolOverdue').textContent = data.asignaciones_vencidas || 0;
        $('trackingOverdue').textContent = data.seguimientos_vencidos || 0;
        const assignments = data.asignaciones || {};
        renderBars($('protocolAssignments'), [
            [S('protocol_active','Activas'), assignments.activa || 0, 'fill-info'],
            [S('protocol_suspended','Suspendidas'), assignments.suspendida || 0, 'fill-warn'],
            [S('protocol_closed','Cerradas'), assignments.cerrada || 0, 'fill-good'],
            [S('protocol_cancelled','Canceladas'), assignments.cancelada || 0, 'fill-neutral']
        ], assignments.total || 0);
        const results = data.ejecuciones || {};
        renderBars($('protocolResults'), [
            [S('protocol_pending_review','Pendiente revisión'), results.pendiente_revision || 0, 'fill-warn'],
            [S('protocol_conforme','Conforme'), results.conforme || 0, 'fill-good'],
            [S('protocol_observado','Observado'), results.observado || 0, 'fill-info'],
            [S('protocol_no_conforme','No conforme'), results.no_conforme || 0, 'fill-bad'],
            [S('protocol_no_aplica','No aplica'), results.no_aplica || 0, 'fill-neutral']
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
            evento: ['bi-exclamation-triangle', S('recent_event','Evento')],
            formulario: ['bi-card-checklist', S('recent_form','Formulario')],
            protocolo: ['bi-clipboard2-pulse', S('recent_protocol','Protocolo')],
            auditoria: ['bi-clipboard2-check', S('recent_audit','Auditoría')]
        };
        return map[type] || ['bi-activity', type];
    }

    function formatDate(value) {
        if (!value) return '';
        const d = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return String(value);
        return new Intl.DateTimeFormat(document.documentElement.lang || 'es', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' }).format(d);
    }

    function renderRecent(rows) {
        const container = $('recentList');
        if (!rows.length) {
            container.innerHTML = '<p class="text-muted small mb-0">' + escapeHtml(S('recent_empty','Sin actividad reciente para el periodo.')) + '</p>';
            return;
        }
        container.innerHTML = rows.map(function (row) {
            const meta = recentMeta(row.tipo);
            return '<div class="recent-item"><span class="recent-icon" title="' + escapeHtml(meta[1]) + '"><i class="bi ' + meta[0] + '"></i></span><div><div class="recent-title">' + escapeHtml(row.titulo) + '</div><div class="recent-detail">' + escapeHtml(row.detalle || meta[1]) + '</div></div><span class="recent-date">' + escapeHtml(formatDate(row.fecha)) + '</span></div>';
        }).join('');
    }

    function renderTrend(rows) {
        const wrap = $('trendChart');
        const legend = $('trendLegend');
        if (!rows.length) {
            wrap.innerHTML = '<div class="trend-empty">' + escapeHtml(S('no_data','Sin datos para el periodo seleccionado.')) + '</div>';
            legend.innerHTML = '';
            return;
        }
        const series = [
            ['eventos', S('trend_events','Eventos'), '#dc2626'],
            ['formularios', S('trend_forms','Formularios'), '#0284c7'],
            ['protocolos', S('trend_protocols','Protocolos'), '#7c3aed'],
            ['evaluaciones', S('trend_evaluations','Evaluaciones finalizadas'), '#16a34a']
        ];
        const width = 900, height = 240, left = 44, right = 18, top = 18, bottom = 40;
        const plotW = width-left-right, plotH = height-top-bottom;
        let max = 0;
        rows.forEach(row => series.forEach(s => { max = Math.max(max, Number(row[s[0]] || 0)); }));
        max = Math.max(1, max);
        const x = (i) => left + (rows.length === 1 ? plotW/2 : (i/(rows.length-1))*plotW);
        const y = (v) => top + plotH - (Number(v || 0)/max)*plotH;
        let svg = '<svg class="trend-svg" viewBox="0 0 '+width+' '+height+'" role="img">';
        [0,.25,.5,.75,1].forEach(function (ratio) {
            const yy = top + plotH - ratio*plotH;
            svg += '<line x1="'+left+'" y1="'+yy+'" x2="'+(width-right)+'" y2="'+yy+'" stroke="rgba(100,116,139,.16)" stroke-width="1"/>';
            svg += '<text x="'+(left-8)+'" y="'+(yy+4)+'" text-anchor="end" font-size="10" fill="#64748b">'+Math.round(max*ratio)+'</text>';
        });
        rows.forEach(function (row, i) {
            if (rows.length <= 7 || i % Math.ceil(rows.length/7) === 0 || i === rows.length-1) {
                svg += '<text x="'+x(i)+'" y="'+(height-13)+'" text-anchor="middle" font-size="10" fill="#64748b">'+escapeHtml(row.periodo)+'</text>';
            }
        });
        series.forEach(function (s) {
            const points = rows.map((row,i) => x(i)+','+y(row[s[0]])).join(' ');
            svg += '<polyline fill="none" stroke="'+s[2]+'" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="'+points+'"/>';
            rows.forEach(function (row,i) {
                const value = Number(row[s[0]] || 0);
                svg += '<circle cx="'+x(i)+'" cy="'+y(value)+'" r="3" fill="'+s[2]+'"><title>'+escapeHtml(s[1]+' · '+row.periodo+': '+value)+'</title></circle>';
            });
        });
        svg += '</svg>';
        wrap.innerHTML = svg;
        legend.innerHTML = series.map(s => '<span><i class="legend-dot" style="background:'+s[2]+'"></i>'+escapeHtml(s[1])+'</span>').join('');
    }
});
