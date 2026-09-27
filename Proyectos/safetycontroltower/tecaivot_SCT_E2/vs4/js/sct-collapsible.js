(function () {
    'use strict';

    var labels = {
        es: {expand: 'Expandir sección', collapse: 'Contraer sección'},
        en: {expand: 'Expand section', collapse: 'Collapse section'},
        pt: {expand: 'Expandir seção', collapse: 'Recolher seção'},
        fr: {expand: 'Développer la section', collapse: 'Réduire la section'},
        zh: {expand: '展开区域', collapse: '收起区域'}
    };

    function language() {
        var lang = (document.documentElement.lang || 'es').toLowerCase();
        if (lang.indexOf('pt') === 0) return 'pt';
        if (lang.indexOf('en') === 0) return 'en';
        if (lang.indexOf('fr') === 0) return 'fr';
        if (lang.indexOf('zh') === 0) return 'zh';
        return 'es';
    }

    function directChild(root, selectors) {
        var children = Array.prototype.slice.call(root.children || []);
        for (var i = 0; i < children.length; i += 1) {
            if (children[i].matches(selectors)) return children[i];
        }
        return null;
    }

    function directHeading(root) {
        return directChild(root, 'h1, h2, h3, h4, h5, h6');
    }

    function hasParentStandardContainer(section) {
        var parent = section.parentElement;
        if (!parent) return false;
        return !!parent.closest('.sct-standard-collapsible, .feature-card, .form-section, .history-card');
    }

    /**
     * Normaliza sólo contenedores funcionales de primer nivel que ya tienen
     * un título visible. No crea contenido nuevo ni altera formularios/APIs.
     */
    function prepareStandardContainer(section) {
        if (!section || section.hasAttribute('data-sct-no-collapse')) return false;
        if (section.closest('.modal, .offcanvas, .accordion')) return false;
        if (section.matches('.welcome-unified-section, .dashboard-section, .management-module-section')) return true;
        if (hasParentStandardContainer(section)) return false;

        var knownHead = directChild(section,
            '[data-sct-collapse-head], .sct-standard-section-head, .sct-builder-section-heading, .sct-builder-catalog__head, .induction-evaluation__header, .sct-personal-card-head');

        if (!knownHead) {
            var children = Array.prototype.slice.call(section.children || []);
            for (var i = 0; i < children.length; i += 1) {
                var child = children[i];
                if (child.matches('.alert, .table-responsive, form, .row, .meta-grid')) continue;
                if (child.querySelector && child.querySelector('h2, h3, h4, .users-toolbar-title')) {
                    knownHead = child;
                    break;
                }
            }
        }

        if (!knownHead) {
            var heading = directHeading(section);
            if (heading) {
                knownHead = document.createElement('div');
                knownHead.className = 'sct-standard-section-head';
                knownHead.setAttribute('data-sct-collapse-head', '');
                section.insertBefore(knownHead, heading);
                knownHead.appendChild(heading);
            }
        }

        if (!knownHead) return false;

        knownHead.setAttribute('data-sct-collapse-head', '');
        knownHead.classList.add('sct-standard-section-head');
        section.classList.add('sct-standard-collapsible');
        return true;
    }

    function resolveParts(section) {
        var head = directChild(section,
            '.welcome-section-head, .dashboard-section-head, .management-section-head, .sct-standard-section-head, [data-sct-collapse-head]');
        var owner = section;

        if (!head) {
            var panel = directChild(section, '.dashboard-panel');
            if (panel) {
                head = directChild(panel, '.dashboard-panel__head');
                owner = panel;
            }
        }
        if (!head) return null;

        var directChildren = Array.prototype.slice.call(owner.children || []);
        var explicitTargets = directChildren.filter(function (node) {
            return node.hasAttribute && node.hasAttribute('data-sct-collapse-target');
        });
        var targets = explicitTargets.length ? explicitTargets : directChildren.filter(function (node) {
            return node !== head && !node.classList.contains('sct-collapse-toggle');
        });
        if (!targets.length) return null;

        return {head: head, owner: owner, targets: targets};
    }

    function setup(section, index) {
        if (!section || section.dataset.sctCollapsibleReady === '1' || section.hasAttribute('data-sct-no-collapse')) return;

        if (!prepareStandardContainer(section)) return;

        var parts = resolveParts(section);
        if (!parts) return;

        var head = parts.head;
        var targets = parts.targets;
        var title = head.querySelector('h1, h2, h3, h4, h5, h6, .users-toolbar-title');
        var titleText = title ? (title.textContent || '').trim() : '';
        var button = document.createElement('button');
        var labelledBy = section.getAttribute('aria-labelledby') || '';
        var controlId = (labelledBy ? labelledBy + '-body' : 'sct-collapse-region-' + index);

        /* Defensa ante controles residuales: una sección conserva una sola flecha. */
        Array.prototype.slice.call(head.querySelectorAll('.sct-collapse-toggle')).forEach(function (oldButton) {
            oldButton.remove();
        });

        button.type = 'button';
        button.className = 'sct-collapse-toggle';
        button.innerHTML = '<i class="bi bi-chevron-up" aria-hidden="true"></i><span class="visually-hidden"></span>';
        var collapsed = false;
        section.classList.add('is-sct-collapsible');
        section.classList.add(parts.owner === section ? 'sct-collapse-owner-section' : 'sct-collapse-owner-panel');
        head.classList.add('sct-collapsible-head');

        targets.forEach(function (target, targetIndex) {
            if (targetIndex === 0) {
                target.id = target.id || controlId;
                button.setAttribute('aria-controls', target.id);
            }
        });

        function apply(nextCollapsed, focusButton) {
            collapsed = !!nextCollapsed;
            section.classList.toggle('is-sct-collapsed', collapsed);
            targets.forEach(function (target) {
                target.hidden = collapsed;
                target.classList.toggle('sct-collapse-target-hidden', collapsed);
                target.setAttribute('aria-hidden', collapsed ? 'true' : 'false');
            });
            button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

            var text = labels[language()][collapsed ? 'expand' : 'collapse'];
            button.setAttribute('aria-label', text + (titleText ? ': ' + titleText : ''));
            button.setAttribute('title', text);

            var sr = button.querySelector('.visually-hidden');
            if (sr) sr.textContent = text;
            var icon = button.querySelector('i');
            if (icon) icon.className = 'bi ' + (collapsed ? 'bi-chevron-down' : 'bi-chevron-up');

            section.dispatchEvent(new CustomEvent('sct:collapsechange', {
                bubbles: true,
                detail: {collapsed: collapsed}
            }));
            if (!collapsed && window.requestAnimationFrame) {
                window.requestAnimationFrame(function () {
                    window.dispatchEvent(new Event('resize'));
                });
            }
            if (focusButton) {
                try { button.focus({preventScroll: true}); }
                catch (_) { button.focus(); }
            }
        }

        button.addEventListener('click', function () {
            apply(!collapsed, false);
        });

        var buttonHost = head.querySelector('[data-sct-collapse-actions]') || head;
        buttonHost.appendChild(button);
        section.dataset.sctCollapsibleReady = '1';
        apply(false, false);
    }

    function collectSections() {
        var selectors = [
            '.welcome-page .welcome-unified-section',
            '.dashboard-page .dashboard-section',
            '.management-hub-page .management-module-section',
            'body.sct-module-page .feature-card',
            'body.sct-module-page .form-section',
            'body.sct-module-page .history-card'
        ];
        var seen = [];
        selectors.forEach(function (selector) {
            Array.prototype.forEach.call(document.querySelectorAll(selector), function (section) {
                if (seen.indexOf(section) === -1) seen.push(section);
            });
        });
        return seen;
    }

    function boot() {
        collectSections().forEach(function (section, index) {
            setup(section, index);
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
