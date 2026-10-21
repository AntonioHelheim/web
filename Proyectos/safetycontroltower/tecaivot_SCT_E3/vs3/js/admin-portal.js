document.addEventListener("DOMContentLoaded", () => {
    const root = document.querySelector("[data-admin-portal]");
    if (!root) return;

    const modeButtons = Array.from(
        root.querySelectorAll("[data-admin-panel-target]")
    );
    const panels = Array.from(
        root.querySelectorAll("[data-admin-panel]")
    );
    const sectionButtons = Array.from(
        root.querySelectorAll("[data-admin-section-target]")
    );
    const adminSections = Array.from(
        document.querySelectorAll("[data-admin-section]")
    );

    const gbuButtons = Array.from(
        root.querySelectorAll("[data-gbu-target]")
    );
    const gbuPanels = Array.from(
        root.querySelectorAll("[data-gbu-panel]")
    );

    const siteButtons = Array.from(
        root.querySelectorAll("[data-site-select]")
    );
    const selectedSite = root.querySelector(
        "[data-selected-site]"
    );
    const siteModules = root.querySelector(
        "[data-site-modules]"
    );
    const siteModuleLinks = Array.from(
        root.querySelectorAll("[data-site-module]")
    );

    function closeMainMenu() {
        const menu = document.querySelector(".sct-main-menu");
        const toggle = document.querySelector(".sct-menu-toggle");
        const backdrop = document.querySelector(".sct-menu-backdrop");

        menu?.classList.remove("is-open");
        toggle?.setAttribute("aria-expanded", "false");
        backdrop?.classList.remove("is-open");
        document.body.classList.remove("sct-menu-open");
    }


    function closeAdminSections() {
        adminSections.forEach(section => {
            section.hidden = true;
        });

        sectionButtons.forEach(button => {
            button.classList.remove("is-active");
            button.setAttribute("aria-expanded", "false");
        });
    }

    function setAdminSection(sectionId, options = {}) {
        const target = adminSections.find(
            section => section.dataset.adminSection === sectionId
        );
        if (!target) return;

        const forceOpen = options.forceOpen === true;
        const alreadyOpen = !target.hidden;

        panels.forEach(panel => {
            panel.hidden = true;
        });

        modeButtons.forEach(button => {
            button.classList.remove("is-active");
            button.setAttribute("aria-expanded", "false");
        });

        closeAdminSections();

        if (alreadyOpen && !forceOpen) return;

        target.hidden = false;

        const activeButton = sectionButtons.find(
            button => button.dataset.adminSectionTarget === sectionId
        );
        activeButton?.classList.add("is-active");
        activeButton?.setAttribute("aria-expanded", "true");

        if (options.scroll !== false) {
            target.scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
        }
    }

    function setPortalPanel(panelId, options = {}) {
        closeAdminSections();

        const target = panels.find(
            panel => panel.dataset.adminPanel === panelId
        );
        if (!target) return;

        const forceOpen = options.forceOpen === true;
        const alreadyOpen = !target.hidden;

        panels.forEach(panel => {
            panel.hidden = true;
        });

        modeButtons.forEach(button => {
            button.classList.remove("is-active");
            button.setAttribute("aria-expanded", "false");
        });

        if (alreadyOpen && !forceOpen) return;

        target.hidden = false;
        const activeButton = modeButtons.find(
            button => button.dataset.adminPanelTarget === panelId
        );
        activeButton?.classList.add("is-active");
        activeButton?.setAttribute("aria-expanded", "true");

        if (options.scroll !== false) {
            target.scrollIntoView({
                behavior: "smooth",
                block: "nearest",
            });
        }
    }

    function setGbu(gbu) {
        gbuPanels.forEach(panel => {
            panel.hidden = panel.dataset.gbuPanel !== gbu;
        });

        gbuButtons.forEach(button => {
            const active = button.dataset.gbuTarget === gbu;
            button.classList.toggle("is-active", active);
            button.setAttribute(
                "aria-pressed",
                active ? "true" : "false"
            );
        });
    }

    function configureAdministratorNavbar() {
        const configNode = document.getElementById(
            "adminPortalNavConfig"
        );
        const scroll = document.querySelector(
            ".sct-main-menu__scroll"
        );

        if (!configNode || !scroll) return;

        let items = [];
        try {
            items = JSON.parse(configNode.textContent || "[]");
        } catch (_) {
            return;
        }

        if (!Array.isArray(items) || items.length === 0) {
            return;
        }

        // Para Administrador, el menú principal debe reflejar sólo las
        // entradas definidas para el portal Administrador. El footer existente no se
        // toca: conserva tema, idiomas y cierre de sesión.
        scroll.replaceChildren();

        const nav = document.createElement("nav");
        nav.className = "sct-main-menu__nav";
        nav.setAttribute(
            "aria-label",
            "Safety Control Tower"
        );

        items.forEach(item => {
            const link = document.createElement("a");
            link.href = item.href || "#";
            link.className = "sct-main-menu__direct-item";
            link.dataset.adminNavId = item.id || "";

            const icon = document.createElement("i");
            icon.className = `bi ${item.icon || "bi-circle"}`;

            const label = document.createElement("span");
            label.textContent = item.label || "";

            const arrow = document.createElement("i");
            arrow.className =
                "bi bi-chevron-right sct-main-menu__direct-arrow";

            link.append(icon, label, arrow);

            if (item.panel) {
                link.dataset.adminOpenPanel = item.panel;
            }
            if (item.section) {
                link.dataset.adminOpenSection = item.section;
            }

            nav.appendChild(link);
        });

        scroll.appendChild(nav);

        nav.addEventListener("click", event => {
            const link = event.target.closest("a");
            if (!link) return;

            const panel = link.dataset.adminOpenPanel || "";
            const section = link.dataset.adminOpenSection || "";

            if (panel) {
                event.preventDefault();
                setPortalPanel(panel, {
                    forceOpen: true,
                    scroll: false,
                });

                root.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            } else if (section) {
                event.preventDefault();
                setAdminSection(section, {
                    forceOpen: true,
                    scroll: true,
                });
            }

            closeMainMenu();
        });
    }

    modeButtons.forEach(button => {
        button.addEventListener("click", () => {
            setPortalPanel(
                button.dataset.adminPanelTarget || ""
            );
        });
    });

    sectionButtons.forEach(button => {
        button.addEventListener("click", () => {
            setAdminSection(
                button.dataset.adminSectionTarget || ""
            );
        });
    });

    document.addEventListener("click", event => {
        const link = event.target.closest("[data-admin-section-link]");
        if (!link) return;

        event.preventDefault();
        setAdminSection(
            link.dataset.adminSectionLink || "",
            {
                forceOpen: true,
                scroll: true,
            }
        );
    });

    gbuButtons.forEach(button => {
        button.addEventListener("click", () => {
            setGbu(button.dataset.gbuTarget || "");
        });
    });

    siteButtons.forEach(button => {
        button.addEventListener("click", () => {
            if (button.disabled) return;

            siteButtons.forEach(item => {
                item.classList.remove("is-selected");
                item.setAttribute("aria-pressed", "false");
            });

            button.classList.add("is-selected");
            button.setAttribute("aria-pressed", "true");

            const gbu = button.dataset.siteGbu || "";
            const site = button.dataset.siteName || "";
            const projectId = button.dataset.projectId || "";

            if (selectedSite) {
                selectedSite.textContent = `${gbu} · ${site}`;
                selectedSite.closest(
                    "[data-selected-site-wrap]"
                )?.classList.remove("d-none");
            }

            if (siteModules && projectId) {
                siteModuleLinks.forEach(link => {
                    const moduleId = link.dataset.moduleId || "";
                    const url = new URL(
                        "api/contexto/sitio.php",
                        window.location.href
                    );

                    url.searchParams.set("module", moduleId);
                    url.searchParams.set("id_project", projectId);
                    link.href = url.toString();
                });

                siteModules.classList.remove("d-none");
                siteModules.scrollIntoView({
                    behavior: "smooth",
                    block: "nearest",
                });
            }
        });
    });

    if (gbuButtons[0]) {
        setGbu(gbuButtons[0].dataset.gbuTarget || "");
    }

    configureAdministratorNavbar();
});
