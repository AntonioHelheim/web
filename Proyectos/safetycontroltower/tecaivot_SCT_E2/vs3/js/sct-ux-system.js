(function () {
    'use strict';

    function ensureUxStylesheet() {
        if (document.querySelector('link[data-sct-ux-runtime]')) return;
        var script = document.currentScript;
        if (!script || !script.src) return;
        var href = script.src.replace(/\/js\/sct-ux-system\.js(?:\?.*)?$/i, '/css/sct-ux-system.css?v=20260921-p43-final');
        if (href === script.src) return;
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.setAttribute('data-sct-ux-runtime', '1');
        (document.head || document.documentElement).appendChild(link);
    }

    ensureUxStylesheet();

    var nav = document.querySelector('.sct-app-navbar');
    var role = nav ? (nav.getAttribute('data-sct-role') || '') : '';
    if (role && document.body) {
        document.body.classList.add('sct-role-' + role.replace(/[^a-z0-9_-]/gi, ''));
    }

    var labels = {
        confirmTitle: nav ? (nav.getAttribute('data-confirm-title') || 'Confirmar acción') : 'Confirmar acción',
        confirmAction: nav ? (nav.getAttribute('data-confirm-action') || 'Confirmar') : 'Confirmar',
        cancel: nav ? (nav.getAttribute('data-cancel-label') || 'Cancelar') : 'Cancelar'
    };

    function buildConfirmDialog() {
        var existing = document.getElementById('sctGlobalConfirmDialog');
        if (existing) return existing;

        var dialog = document.createElement('dialog');
        dialog.id = 'sctGlobalConfirmDialog';
        dialog.className = 'sct-confirm-dialog';
        dialog.setAttribute('aria-labelledby', 'sctGlobalConfirmTitle');
        dialog.setAttribute('aria-describedby', 'sctGlobalConfirmMessage');
        dialog.innerHTML = ''
            + '<div class="sct-confirm-dialog__panel">'
            + '  <div class="sct-confirm-dialog__head">'
            + '    <span class="sct-confirm-dialog__icon" aria-hidden="true"><i class="bi bi-exclamation-circle"></i></span>'
            + '    <div class="sct-confirm-dialog__copy">'
            + '      <h2 id="sctGlobalConfirmTitle"></h2>'
            + '      <p id="sctGlobalConfirmMessage"></p>'
            + '    </div>'
            + '  </div>'
            + '  <div class="sct-confirm-dialog__actions">'
            + '    <button type="button" class="btn btn-outline-custom" data-sct-confirm-cancel></button>'
            + '    <button type="button" class="btn btn-primary-custom" data-sct-confirm-ok></button>'
            + '  </div>'
            + '</div>';
        document.body.appendChild(dialog);
        return dialog;
    }

    window.sctConfirm = function (message, options) {
        options = options || {};
        var dialog = buildConfirmDialog();

        if (!dialog || typeof dialog.showModal !== 'function') {
            return Promise.resolve(window.confirm(String(message || '')));
        }

        var title = dialog.querySelector('#sctGlobalConfirmTitle');
        var messageNode = dialog.querySelector('#sctGlobalConfirmMessage');
        var cancel = dialog.querySelector('[data-sct-confirm-cancel]');
        var ok = dialog.querySelector('[data-sct-confirm-ok]');
        var lastFocus = document.activeElement;

        title.textContent = options.title || labels.confirmTitle;
        messageNode.textContent = String(message || '');
        cancel.textContent = options.cancelLabel || labels.cancel;
        ok.textContent = options.confirmLabel || labels.confirmAction;

        return new Promise(function (resolve) {
            var settled = false;

            function finish(value) {
                if (settled) return;
                settled = true;
                cleanup();
                if (dialog.open) dialog.close(value ? 'confirm' : 'cancel');
                if (lastFocus && typeof lastFocus.focus === 'function') {
                    window.setTimeout(function () { lastFocus.focus(); }, 0);
                }
                resolve(Boolean(value));
            }

            function onCancel(event) {
                event.preventDefault();
                finish(false);
            }

            function onClick(event) {
                if (event.target === dialog) finish(false);
            }

            function cleanup() {
                ok.removeEventListener('click', onOk);
                cancel.removeEventListener('click', onCancelClick);
                dialog.removeEventListener('cancel', onCancel);
                dialog.removeEventListener('click', onClick);
            }

            function onOk() { finish(true); }
            function onCancelClick() { finish(false); }

            ok.addEventListener('click', onOk);
            cancel.addEventListener('click', onCancelClick);
            dialog.addEventListener('cancel', onCancel);
            dialog.addEventListener('click', onClick);

            dialog.showModal();
            window.setTimeout(function () { ok.focus(); }, 0);
        });
    };

    window.sctConfirmAction = function (message, options) {
        if (typeof window.sctConfirm === 'function') {
            return window.sctConfirm(message, options || {});
        }
        return Promise.resolve(window.confirm(String(message || '')));
    };

    function normalizeHeaderText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function enhanceResponsiveTable(table) {
        if (!table || table.dataset.sctResponsiveReady === '1') return;
        if (table.classList.contains('sct-no-mobile-cards')) return;

        var headers = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) {
            return normalizeHeaderText(th.textContent);
        });
        if (!headers.length) return;

        table.dataset.sctResponsiveReady = '1';
        table.classList.add('sct-responsive-cards');
        var wrapper = table.closest('.table-responsive');
        if (wrapper) wrapper.classList.add('sct-table-card-view');

        function labelRows() {
            Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function (row) {
                var cells = row.children;
                for (var i = 0; i < cells.length; i += 1) {
                    var cell = cells[i];
                    if (!cell || cell.tagName !== 'TD') continue;
                    if (cell.hasAttribute('colspan')) continue;
                    var label = headers[i] || '';
                    cell.setAttribute('data-label', label);
                    var isLast = i === cells.length - 1;
                    var normalized = label.toLowerCase();
                    var hasActions = cell.querySelector('button, .btn, [data-action], .dropdown, details');
                    if (hasActions && (isLast || /acci|action|ação|动作|操作/i.test(normalized))) {
                        cell.setAttribute('data-sct-action-cell', '1');
                    }
                }
            });
        }

        labelRows();
        new MutationObserver(labelRows).observe(table.tBodies[0] || table, { childList: true, subtree: true });
    }

    function enhanceTables(root) {
        if (!document.body || !document.body.classList.contains('sct-module-page')) return;
        (root || document).querySelectorAll('.table-responsive > table.table').forEach(enhanceResponsiveTable);
    }

    function init() {
        enhanceTables(document);

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes || [], function (node) {
                    if (!node || node.nodeType !== 1) return;
                    if (node.matches && node.matches('.table-responsive > table.table')) enhanceResponsiveTable(node);
                    enhanceTables(node);
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
