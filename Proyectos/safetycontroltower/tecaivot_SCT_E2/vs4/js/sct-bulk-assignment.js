/** Safety Control Tower — selector múltiple de personas/trabajadores. */
(function () {
    'use strict';

    function normalize(value) {
        return String(value == null ? '' : value).toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function initPicker(root) {
        if (!root || root.dataset.bulkReady === '1') return;
        const sourceId = root.dataset.sourceId || '';
        const source = document.getElementById(sourceId);
        if (!source) return;

        root.dataset.bulkReady = '1';
        const search = root.querySelector('[data-bulk-search]');
        const list = root.querySelector('[data-bulk-list]');
        const count = root.querySelector('[data-bulk-count]');
        const selectAll = root.querySelector('[data-bulk-select-all]');
        const clear = root.querySelector('[data-bulk-clear]');
        const selectedLabel = root.dataset.selectedLabel || 'seleccionados';
        const emptyLabel = root.dataset.emptyLabel || 'No hay personas disponibles.';

        function optionRows() {
            return Array.from(source.options).filter(function (option) {
                return String(option.value || '').trim() !== '';
            });
        }

        function updateCount() {
            const total = optionRows().filter(function (option) { return option.selected; }).length;
            if (count) count.textContent = total + ' ' + selectedLabel;
            root.classList.toggle('has-selection', total > 0);
        }

        function render() {
            if (!list) return;
            const query = normalize(search ? search.value.trim() : '');
            const options = optionRows();
            list.innerHTML = '';
            let visible = 0;

            options.forEach(function (option, index) {
                const haystack = normalize(option.textContent + ' ' + option.value);
                if (query && haystack.indexOf(query) === -1) return;
                visible += 1;

                const item = document.createElement('label');
                item.className = 'sct-bulk-picker__item';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = option.selected;
                checkbox.dataset.optionIndex = String(index);
                checkbox.addEventListener('change', function () {
                    option.selected = checkbox.checked;
                    source.dispatchEvent(new Event('change', { bubbles: true }));
                    item.classList.toggle('is-selected', checkbox.checked);
                    updateCount();
                });

                const copy = document.createElement('span');
                copy.className = 'sct-bulk-picker__item-copy';
                const strong = document.createElement('strong');
                strong.textContent = option.textContent || option.value;
                copy.appendChild(strong);

                item.appendChild(checkbox);
                item.appendChild(copy);
                item.classList.toggle('is-selected', option.selected);
                list.appendChild(item);
            });

            if (!visible) {
                const empty = document.createElement('div');
                empty.className = 'sct-bulk-picker__empty';
                empty.textContent = emptyLabel;
                list.appendChild(empty);
            }
            updateCount();
        }

        if (search) search.addEventListener('input', render);
        if (selectAll) selectAll.addEventListener('click', function () {
            const query = normalize(search ? search.value.trim() : '');
            optionRows().forEach(function (option) {
                const haystack = normalize(option.textContent + ' ' + option.value);
                if (!query || haystack.indexOf(query) !== -1) option.selected = true;
            });
            source.dispatchEvent(new Event('change', { bubbles: true }));
            render();
        });
        if (clear) clear.addEventListener('click', function () {
            optionRows().forEach(function (option) { option.selected = false; });
            source.dispatchEvent(new Event('change', { bubbles: true }));
            render();
        });

        const observer = new MutationObserver(render);
        observer.observe(source, { childList: true, subtree: true, characterData: true });
        const form = source.closest('form');
        if (form) form.addEventListener('reset', function () { window.setTimeout(render, 0); });
        source.addEventListener('sct:bulk-refresh', render);
        render();
    }

    function initAll(scope) {
        (scope || document).querySelectorAll('[data-sct-bulk-picker]').forEach(initPicker);
    }

    window.sctBulkAssignmentValues = function (sourceId) {
        const source = document.getElementById(sourceId);
        if (!source) return [];
        return Array.from(source.selectedOptions || []).map(function (option) {
            return String(option.value || '').trim();
        }).filter(Boolean);
    };

    window.sctBulkAssignmentRefresh = function (sourceId) {
        const source = document.getElementById(sourceId);
        if (source) source.dispatchEvent(new CustomEvent('sct:bulk-refresh'));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initAll(document); });
    } else {
        initAll(document);
    }
})();
