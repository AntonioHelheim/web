/** SCT — sesión local de actividad, aislamiento del flujo y borradores sin cambios de base de datos. P24 */
(function (window, document) {
    'use strict';

    function safeStorage() {
        try {
            const probe = '__sct_activity_probe__';
            window.localStorage.setItem(probe, '1');
            window.localStorage.removeItem(probe);
            return window.localStorage;
        } catch (_) {
            return null;
        }
    }

    function cleanToken(value) {
        return String(value == null ? '' : value).replace(/[^a-zA-Z0-9_.@-]/g, '_').slice(0, 160);
    }

    function create(options) {
        const opts = options || {};
        const root = opts.root || document.querySelector('.container[data-csrf-token]');
        const type = cleanToken(opts.type || 'activity');
        const user = cleanToken((root && root.dataset.activityUser) || 'session');
        let id = cleanToken(opts.id || '0');
        const storage = safeStorage();
        let returnScrollY = null;
        const defaultShell = opts.shell || null;
        const defaultPanel = opts.panel || null;
        let isolatedNodes = [];

        function resolveElement(candidate) {
            if (!candidate) return null;
            if (candidate.nodeType === 1) return candidate;
            if (typeof candidate === 'string') return document.querySelector(candidate);
            return null;
        }

        function restoreIsolation() {
            isolatedNodes.forEach(function (entry) {
                var node = entry.node;
                if (!node || !node.isConnected) return;
                if (entry.hadHidden) node.setAttribute('hidden', '');
                else node.removeAttribute('hidden');
                if (entry.ariaHidden === null) node.removeAttribute('aria-hidden');
                else node.setAttribute('aria-hidden', entry.ariaHidden);
                try { node.inert = !!entry.inert; } catch (_) {}
                node.classList.remove('sct-activity-isolated-hidden');
            });
            isolatedNodes = [];
        }

        function isolate(shellCandidate, panelCandidate) {
            var shell = resolveElement(shellCandidate) || resolveElement(defaultShell);
            var panel = resolveElement(panelCandidate) || resolveElement(defaultPanel);
            restoreIsolation();
            if (!shell || !panel || !shell.contains(panel)) return;

            Array.prototype.forEach.call(shell.children, function (child) {
                if (child === panel || child.contains(panel)) return;
                isolatedNodes.push({
                    node: child,
                    hadHidden: child.hasAttribute('hidden'),
                    ariaHidden: child.getAttribute('aria-hidden'),
                    inert: !!child.inert
                });
                child.setAttribute('hidden', '');
                child.setAttribute('aria-hidden', 'true');
                try { child.inert = true; } catch (_) {}
                child.classList.add('sct-activity-isolated-hidden');
            });
            shell.classList.add('sct-activity-shell--isolated');
            panel.classList.add('sct-activity-panel--active');
        }

        function key() {
            return 'sct:draft:v1:' + user + ':' + type + ':' + id;
        }

        function setId(nextId) {
            id = cleanToken(nextId || '0');
        }

        function save(payload) {
            if (!storage || id === '0') return false;
            try {
                storage.setItem(key(), JSON.stringify({ saved_at: new Date().toISOString(), data: payload || {} }));
                return true;
            } catch (_) {
                return false;
            }
        }

        function load() {
            if (!storage || id === '0') return null;
            try {
                const parsed = JSON.parse(storage.getItem(key()) || 'null');
                return parsed && parsed.data ? parsed.data : null;
            } catch (_) {
                return null;
            }
        }

        function clear() {
            if (!storage || id === '0') return;
            try { storage.removeItem(key()); } catch (_) {}
        }

        function enter(shellCandidate, panelCandidate) {
            var shell = resolveElement(shellCandidate) || resolveElement(defaultShell);
            var panel = resolveElement(panelCandidate) || resolveElement(defaultPanel);
            if (returnScrollY === null) returnScrollY = window.scrollY || window.pageYOffset || 0;
            document.body.classList.add('sct-activity-mode');
            if (shell) shell.classList.add('is-direct-activity');
            isolate(shell, panel);
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('start', id);
                window.history.replaceState({}, '', url.toString());
            } catch (_) {}
            if (panel) {
                window.requestAnimationFrame(function () {
                    panel.scrollIntoView({ behavior: 'auto', block: 'start' });
                    var focusTarget = panel.querySelector('button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
                    if (focusTarget && typeof focusTarget.focus === 'function') {
                        try { focusTarget.focus({ preventScroll: true }); } catch (_) { focusTarget.focus(); }
                    }
                });
            }
        }

        function exit(shellCandidate, panelCandidate) {
            try {
                var query = new URLSearchParams(window.location.search);
                if (query.get('embedded') === '1' && window.parent && window.parent !== window) {
                    window.parent.postMessage({ type: 'sct-activity-exit' }, window.location.origin);
                    return;
                }
            } catch (_) {}
            var shell = resolveElement(shellCandidate) || resolveElement(defaultShell);
            var panel = resolveElement(panelCandidate) || resolveElement(defaultPanel);
            document.body.classList.remove('sct-activity-mode');
            if (shell) {
                shell.classList.remove('is-direct-activity');
                shell.classList.remove('sct-activity-shell--isolated');
            }
            if (panel) panel.classList.remove('sct-activity-panel--active');
            restoreIsolation();
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete('start');
                window.history.replaceState({}, '', url.toString());
            } catch (_) {}
            if (returnScrollY !== null) {
                var targetY = returnScrollY;
                returnScrollY = null;
                window.requestAnimationFrame(function () { window.scrollTo({ top: targetY, behavior: 'auto' }); });
            }
        }

        if (document.body.classList.contains('sct-activity-mode') && defaultShell && defaultPanel) {
            isolate(defaultShell, defaultPanel);
        }

        return { setId: setId, save: save, load: load, clear: clear, enter: enter, exit: exit, isolate: isolate };
    }

    window.SCTActivitySession = { create: create };
}(window, document));
