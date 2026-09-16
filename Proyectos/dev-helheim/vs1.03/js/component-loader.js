(() => {
  "use strict";

  const VERSION = "1.03.0-20260915.1";
  const loaderScript = document.currentScript;

  if (!loaderScript || !loaderScript.src) {
    console.error("Helheim Components: no fue posible determinar la ruta del loader.");
    return;
  }

  // La raíz se calcula desde /js/component-loader.js. Esto permite usar el mismo
  // componente desde index.html, /section/*.html o niveles internos futuros.
  const loaderUrl = new URL(loaderScript.src, document.baseURI);
  const siteRoot = new URL("../", loaderUrl);
  const componentsRoot = new URL("components/", siteRoot);

  function versionedUrl(url) {
    const parsed = new URL(url);
    parsed.searchParams.set("v", VERSION);
    return parsed.href;
  }

  function ensureStylesheet(relativePath, marker) {
    if (document.querySelector(`link[data-helheim-resource="${marker}"]`)) return;
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = versionedUrl(new URL(relativePath, siteRoot));
    link.dataset.helheimResource = marker;
    document.head.appendChild(link);
  }

  function loadScript(relativePath, marker) {
    return new Promise((resolve, reject) => {
      const existing = document.querySelector(`script[data-helheim-resource="${marker}"]`);
      if (existing) {
        if (existing.dataset.loaded === "true") resolve();
        else {
          existing.addEventListener("load", resolve, { once: true });
          existing.addEventListener("error", reject, { once: true });
        }
        return;
      }

      const script = document.createElement("script");
      script.src = versionedUrl(new URL(relativePath, siteRoot));
      script.dataset.helheimResource = marker;
      script.addEventListener("load", () => { script.dataset.loaded = "true"; resolve(); }, { once: true });
      script.addEventListener("error", reject, { once: true });
      document.head.appendChild(script);
    });
  }

  async function ensureGlobalEnhancements() {
    ensureStylesheet("css/theme.css", "theme-css");

    window.HelheimLocales = window.HelheimLocales || {};
    if (!window.HelheimLocales.es) await loadScript("lang/es.js", "lang-es");
    if (!window.HelheimLocales.en) await loadScript("lang/en.js", "lang-en");
    if (!window.HelheimLocales.pt) await loadScript("lang/pt.js", "lang-pt");
    if (!window.HelheimI18n) await loadScript("lang/i18n.js", "i18n");
    if (!window.HelheimTheme) await loadScript("js/theme.js", "theme-js");
  }

  function componentUrl(componentPath) {
    const normalized = componentPath.replace(/^\/+/, "");
    return new URL(normalized, componentsRoot);
  }

  function resolveSitePaths(scope) {
    scope.querySelectorAll("[data-site-href]").forEach((element) => {
      const relativePath = element.getAttribute("data-site-href") || "";
      element.setAttribute("href", new URL(relativePath, siteRoot).href);
    });

    scope.querySelectorAll("[data-site-src]").forEach((element) => {
      const relativePath = element.getAttribute("data-site-src") || "";
      element.setAttribute("src", new URL(relativePath, siteRoot).href);
    });

    scope.querySelectorAll("[data-site-action]").forEach((element) => {
      const relativePath = element.getAttribute("data-site-action") || "";
      element.setAttribute("action", new URL(relativePath, siteRoot).href);
    });
  }

  function setCurrentYear(scope) {
    scope.querySelectorAll("[data-current-year]").forEach((element) => {
      element.textContent = String(new Date().getFullYear());
    });
  }

  function setActiveNavigation(scope) {
    const activeKey = document.body.dataset.navActive;
    if (!activeKey) return;

    scope.querySelectorAll("[data-nav-key]").forEach((link) => {
      const isActive = link.dataset.navKey === activeKey;
      if (isActive) {
        link.setAttribute("aria-current", "page");
      } else {
        link.removeAttribute("aria-current");
      }
    });
  }

  function initBootstrap(scope) {
    if (!window.bootstrap) return;

    if (window.bootstrap.Carousel) {
      scope.querySelectorAll('.carousel[data-bs-ride="carousel"]').forEach((carouselElement) => {
        const instance = window.bootstrap.Carousel.getOrCreateInstance(carouselElement);
        instance.cycle();
      });
    }
  }

  async function loadComponent(element) {
    if (element.dataset.componentState === "loading" || element.dataset.componentState === "loaded") {
      return;
    }

    const path = element.dataset.component;
    if (!path) return;

    element.dataset.componentState = "loading";
    const url = componentUrl(path);

    try {
      const response = await fetch(versionedUrl(url));
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      element.innerHTML = await response.text();
      element.dataset.componentState = "loaded";

      resolveSitePaths(element);
      setCurrentYear(element);
      setActiveNavigation(element);
      initBootstrap(element);

      // Permite que un componente contenga otros componentes en el futuro.
      await loadAll(element);

      element.dispatchEvent(new CustomEvent("helheim:component-loaded", {
        bubbles: true,
        detail: {
          component: path,
          siteRoot: siteRoot.href
        }
      }));
    } catch (error) {
      element.dataset.componentState = "error";
      console.error(`Helheim Components: error cargando ${url.href}`, error);
    }
  }

  async function loadAll(scope = document) {
    const components = Array.from(scope.querySelectorAll("[data-component]"))
      .filter((element) => !element.dataset.componentState);

    await Promise.all(components.map(loadComponent));
  }

  function scrollToCurrentHash() {
    const hash = window.location.hash;
    if (!hash || hash === "#") return;

    let target = null;
    try {
      target = document.querySelector(hash);
    } catch (error) {
      return;
    }

    if (!target) return;

    // Espera un frame para que el contenido inyectado haya calculado su layout.
    window.requestAnimationFrame(() => {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  }

  async function init() {
    try {
      await ensureGlobalEnhancements();
    } catch (error) {
      console.error("Helheim Components: no fue posible cargar preferencias globales.", error);
    }

    await loadAll(document);
    document.dispatchEvent(new CustomEvent("helheim:components-ready", {
      detail: { siteRoot: siteRoot.href }
    }));
    scrollToCurrentHash();
  }

  window.HelheimComponents = Object.freeze({
    siteRoot: siteRoot.href,
    loadAll
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
