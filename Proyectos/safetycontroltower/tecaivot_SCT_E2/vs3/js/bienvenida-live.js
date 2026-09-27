(function () {
    'use strict';

    var root = document.body;
    if (!root || root.classList.contains('welcome-role-trabajador')) return;

    var status = document.querySelector('[data-welcome-live-status]');
    var statusText = status ? status.querySelector('span:last-child') : null;
    var endpoint = './api/usuarios/bienvenida-vivo.php';
    var timer = null;
    var inFlight = false;
    var lastSuccess = 0;
    var lang = (document.documentElement.lang || 'es').toLowerCase().slice(0, 2);

    var labels = {
        es: { waiting: 'Datos conectados a la base de datos.', updated: 'Actualizado', error: 'No se pudo actualizar ahora.' },
        en: { waiting: 'Data connected to the database.', updated: 'Updated', error: 'Could not refresh right now.' },
        pt: { waiting: 'Dados conectados ao banco de dados.', updated: 'Atualizado', error: 'Não foi possível atualizar agora.' },
        fr: { waiting: 'Données connectées à la base de données.', updated: 'Mis à jour', error: 'Impossible d’actualiser maintenant.' },
        zh: { waiting: '数据已连接到数据库。', updated: '已更新', error: '暂时无法更新。' }
    };
    var L = labels[lang] || labels.es;

    function setStatus(kind, date) {
        if (!status || !statusText) return;
        status.classList.toggle('is-error', kind === 'error');
        if (kind === 'updated' && date) {
            var d = new Date(date);
            var formatted = Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat(document.documentElement.lang || 'es', {
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            }).format(d);
            statusText.textContent = L.updated + (formatted ? ' · ' + formatted : '');
        } else {
            statusText.textContent = kind === 'error' ? L.error : L.waiting;
        }
    }

    function formatDate(value) {
        if (!value) return '—';
        var d = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return '—';
        return new Intl.DateTimeFormat(document.documentElement.lang || 'es', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        }).format(d);
    }

    function updateSummary(data) {
        var values = {
            scope: String(Number(data.scope_value || 0)),
            pending: String(Number(data.pending_total || 0)),
            next_due: formatDate(data.next_due || '')
        };
        Object.keys(values).forEach(function (key) {
            var card = document.querySelector('[data-welcome-live-summary="' + key + '"]');
            if (!card) return;
            var value = card.querySelector('.welcome-role-summary__copy > strong');
            if (value && value.textContent !== values[key]) value.textContent = values[key];
        });
    }

    function updateActivities(data) {
        var values = {};
        (data.activities || []).forEach(function (item) {
            values[String(item.key || '')] = Number(item.value || 0);
        });
        var cards = Array.prototype.slice.call(document.querySelectorAll('[data-welcome-live-activity]'));
        cards.forEach(function (card) {
            var key = card.getAttribute('data-welcome-live-activity') || '';
            if (!Object.prototype.hasOwnProperty.call(values, key)) return;
            var value = card.querySelector('.welcome-role-card__value');
            if (value) value.textContent = String(values[key]);
            card.dataset.liveValue = String(values[key]);
        });
        if (cards.length) {
            var grid = cards[0].parentElement;
            cards.sort(function (a, b) { return Number(b.dataset.liveValue || 0) - Number(a.dataset.liveValue || 0); });
            cards.forEach(function (card) { grid.appendChild(card); });
        }
    }

    async function refresh(force) {
        if (inFlight || document.visibilityState === 'hidden') return;
        if (!force && lastSuccess && Date.now() - lastSuccess < 12000) return;
        inFlight = true;
        try {
            var response = await fetch(endpoint, {
                headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' },
                cache: 'no-store',
                credentials: 'same-origin'
            });
            var json = await response.json();
            if (!response.ok || !json || json.success !== true || !json.data) throw new Error('refresh');
            updateSummary(json.data);
            updateActivities(json.data);
            lastSuccess = Date.now();
            setStatus('updated', json.data.generated_at || new Date().toISOString());
        } catch (_) {
            setStatus('error');
        } finally {
            inFlight = false;
        }
    }

    setStatus('waiting');
    window.setTimeout(function () { refresh(true); }, 600);
    timer = window.setInterval(function () { refresh(false); }, 30000);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') refresh(true);
    });
    window.addEventListener('focus', function () { refresh(false); });
    window.addEventListener('beforeunload', function () { if (timer) window.clearInterval(timer); });
}());
