(() => {
  "use strict";

  /* SCROLL SUAVE: delegación para enlaces cargados después mediante component-loader.js */
  document.addEventListener("click", (event) => {
    const anchor = event.target.closest('a[href^="#"]');
    if (!anchor) return;

    const selector = anchor.getAttribute("href");
    if (!selector || selector === "#") return;

    let target;
    try {
      target = document.querySelector(selector);
    } catch (error) {
      return;
    }

    if (!target) return;

    event.preventDefault();
    window.scrollTo({
      top: target.offsetTop - 70,
      behavior: "smooth"
    });
  });

  function setContactStatus(form, type, message) {
    const status = document.getElementById("helheimContactStatus");
    if (!status) return;

    const alertClass = type === "success" ? "alert-success" : "alert-danger";
    status.innerHTML = "";

    const alert = document.createElement("div");
    alert.className = `alert ${alertClass}`;
    alert.setAttribute("role", type === "success" ? "status" : "alert");
    alert.setAttribute("data-i18n-source", message);
    alert.textContent = window.HelheimI18n ? window.HelheimI18n.t(message) : message;
    status.appendChild(alert);

    if (form) {
      status.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  }

  function setContactSubmitting(form, isSubmitting) {
    const button = form.querySelector("#helheimContactSubmit");
    if (!button) return;

    const label = button.querySelector(".contact-submit-label");
    const loading = button.querySelector(".contact-submit-loading");

    button.disabled = isSubmitting;
    button.setAttribute("aria-busy", isSubmitting ? "true" : "false");
    if (label) label.classList.toggle("d-none", isSubmitting);
    if (loading) loading.classList.toggle("d-none", !isSubmitting);
  }

  async function submitContactForm(form) {
    setContactSubmitting(form, true);

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: {
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        }
      });

      let payload = null;
      try {
        payload = await response.json();
      } catch (error) {
        payload = null;
      }

      if (!response.ok || !payload || payload.ok !== true) {
        const message = payload && payload.message
          ? payload.message
          : "No fue posible enviar el mensaje en este momento. Intenta nuevamente.";
        setContactStatus(form, "error", message);
        return;
      }

      setContactStatus(form, "success", payload.message || "Tu mensaje fue enviado correctamente.");
      form.reset();
      form.classList.remove("was-validated");
    } catch (error) {
      setContactStatus(
        form,
        "error",
        "No fue posible conectar con el servidor. Revisa tu conexión e intenta nuevamente."
      );
    } finally {
      setContactSubmitting(form, false);
    }
  }

  /* VALIDACIÓN BOOTSTRAP + ENVÍO AJAX DEL FORMULARIO DE CONTACTO */
  document.addEventListener("submit", (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.classList.contains("needs-validation")) return;

    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
      form.classList.add("was-validated");
      return;
    }

    form.classList.add("was-validated");

    if (form.id === "helheimContactForm") {
      event.preventDefault();
      submitContactForm(form);
    }
  }, true);

  /* LIGHTBOX DE DISEÑO INDUSTRIAL: funciona con contenido inyectado dinámicamente */
  document.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-din-lightbox]");
    if (!trigger) return;

    const sourceImage = trigger.querySelector("img");
    const modalElement = document.getElementById("dinLightboxModal");
    const modalImage = document.getElementById("dinLightboxImage");

    if (!sourceImage || !modalElement || !modalImage || !window.bootstrap || !window.bootstrap.Modal) {
      return;
    }

    const imageSource = sourceImage.currentSrc || sourceImage.src;
    if (!imageSource) return;

    modalImage.src = imageSource;
    const fallbackAlt = "Imagen ampliada de Diseño Industrial";
    modalImage.alt = sourceImage.alt || (window.HelheimI18n ? window.HelheimI18n.t(fallbackAlt) : fallbackAlt);

    const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
  });

  document.addEventListener("hidden.bs.modal", (event) => {
    if (!(event.target instanceof Element) || event.target.id !== "dinLightboxModal") return;
    const modalImage = document.getElementById("dinLightboxImage");
    if (modalImage) {
      modalImage.removeAttribute("src");
      modalImage.alt = "";
    }
  });

  /* NAVBAR GLOBAL: preferencias flotantes sin Popper/scroll interno. */
  const PREF_MARGIN = 8;

  function preferencePanels() {
    return Array.from(document.querySelectorAll("[data-pref-panel]"));
  }

  function preferenceToggles() {
    return Array.from(document.querySelectorAll("[data-pref-toggle]"));
  }

  function closePreferencePanels(exceptName = null) {
    preferencePanels().forEach((panel) => {
      const name = panel.dataset.prefPanel || "";
      if (exceptName && name === exceptName) return;
      panel.hidden = true;
      panel.style.removeProperty("left");
      panel.style.removeProperty("top");
      panel.style.removeProperty("visibility");
    });

    preferenceToggles().forEach((toggle) => {
      const name = toggle.dataset.prefToggle || "";
      if (exceptName && name === exceptName) return;
      toggle.setAttribute("aria-expanded", "false");
    });
  }

  function positionPreferencePanel(toggle, panel) {
    /* Sacamos el panel del navbar: backdrop-filter puede crear un containing block
       para position:fixed en algunos navegadores. En body queda realmente flotante. */
    if (panel.parentElement !== document.body) {
      document.body.appendChild(panel);
    }

    panel.hidden = false;
    panel.style.visibility = "hidden";
    panel.style.left = "0px";
    panel.style.top = "0px";

    const toggleRect = toggle.getBoundingClientRect();
    const panelRect = panel.getBoundingClientRect();
    const viewportWidth = document.documentElement.clientWidth;
    const viewportHeight = document.documentElement.clientHeight;

    let left = toggleRect.right - panelRect.width;
    left = Math.max(PREF_MARGIN, Math.min(left, viewportWidth - panelRect.width - PREF_MARGIN));

    let top = toggleRect.bottom + PREF_MARGIN;
    if (top + panelRect.height > viewportHeight - PREF_MARGIN) {
      const above = toggleRect.top - panelRect.height - PREF_MARGIN;
      top = above >= PREF_MARGIN ? above : Math.max(PREF_MARGIN, viewportHeight - panelRect.height - PREF_MARGIN);
    }

    panel.style.left = `${Math.round(left)}px`;
    panel.style.top = `${Math.round(top)}px`;
    panel.style.visibility = "visible";
  }

  function openPreferencePanel(toggle) {
    const name = toggle.dataset.prefToggle || "";
    const panel = document.querySelector(`[data-pref-panel="${name}"]`);
    if (!name || !panel) return;

    const isOpen = !panel.hidden;
    closePreferencePanels();
    hideNavbarMenu();

    if (isOpen) return;

    positionPreferencePanel(toggle, panel);
    toggle.setAttribute("aria-expanded", "true");
  }

  function hideNavbarMenu() {
    if (!window.bootstrap || !window.bootstrap.Collapse) return;
    const menu = document.getElementById("helheimNavbarNav");
    if (!menu || !menu.classList.contains("show")) return;
    window.bootstrap.Collapse.getOrCreateInstance(menu, { toggle: false }).hide();
  }

  document.addEventListener("click", (event) => {
    const prefToggle = event.target.closest("[data-pref-toggle]");
    if (prefToggle) {
      event.preventDefault();
      event.stopPropagation();
      openPreferencePanel(prefToggle);
      return;
    }

    const prefOption = event.target.closest("[data-theme-select], [data-language-select]");
    if (prefOption) {
      const ownerPanel = prefOption.closest("[data-pref-panel]");
      const ownerName = ownerPanel?.dataset.prefPanel || "";
      const ownerToggle = ownerName
        ? document.querySelector(`[data-pref-toggle="${ownerName}"]`)
        : null;
      closePreferencePanels();
      if (ownerToggle instanceof HTMLElement) ownerToggle.focus({ preventScroll: true });
      return;
    }

    if (!event.target.closest("[data-pref-panel]")) {
      closePreferencePanels();
    }

    const navLink = event.target.closest("#helheimNavbarNav .nav-link");
    if (navLink) hideNavbarMenu();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;
    closePreferencePanels();
    hideNavbarMenu();
  });

  document.addEventListener("show.bs.collapse", (event) => {
    if (!(event.target instanceof Element) || event.target.id !== "helheimNavbarNav") return;
    closePreferencePanels();
  });

  document.addEventListener("shown.bs.collapse", (event) => {
    if (!(event.target instanceof Element) || event.target.id !== "helheimNavbarNav") return;
    const toggle = document.querySelector(".helheim-menu-toggle");
    if (toggle) toggle.setAttribute("aria-label", window.HelheimI18n ? window.HelheimI18n.t("Cerrar") : "Cerrar");
  });

  document.addEventListener("hidden.bs.collapse", (event) => {
    if (!(event.target instanceof Element) || event.target.id !== "helheimNavbarNav") return;
    const toggle = document.querySelector(".helheim-menu-toggle");
    if (toggle) toggle.setAttribute("aria-label", window.HelheimI18n ? window.HelheimI18n.t("Abrir navegación") : "Abrir navegación");
  });

  window.addEventListener("resize", () => closePreferencePanels(), { passive: true });
  window.addEventListener("scroll", () => closePreferencePanels(), { passive: true });

  function initBackToTop() {
    const button = document.getElementById("backToTop");
    if (!button) return;

    const syncVisibility = () => {
      button.classList.toggle("visible", window.scrollY > 300);
    };

    window.addEventListener("scroll", syncVisibility, { passive: true });
    syncVisibility();

    button.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initBackToTop, { once: true });
  } else {
    initBackToTop();
  }
})();
