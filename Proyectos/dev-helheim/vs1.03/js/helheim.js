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
