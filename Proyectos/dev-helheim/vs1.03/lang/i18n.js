(() => {
  "use strict";

  const STORAGE_KEY = "helheim-language";
  const SUPPORTED = ["es", "en", "pt"];
  const textSources = new WeakMap();
  const attrSources = new WeakMap();

  function normalize(value) {
    return String(value || "").replace(/\s+/g, " ").trim();
  }

  function localeFor(code) {
    const locales = window.HelheimLocales || {};
    return locales[code] || locales.es || { strings: {}, meta: {} };
  }

  function savedLanguage() {
    const fromStorage = localStorage.getItem(STORAGE_KEY);
    if (SUPPORTED.includes(fromStorage)) return fromStorage;

    const declared = (document.documentElement.lang || "es").slice(0, 2).toLowerCase();
    if (SUPPORTED.includes(declared)) return declared;

    const browser = (navigator.language || "es").slice(0, 2).toLowerCase();
    return SUPPORTED.includes(browser) ? browser : "es";
  }

  let currentLanguage = savedLanguage();

  function translateSource(source, lang = currentLanguage) {
    const clean = normalize(source);
    if (!clean) return source;
    if (lang === "es") return clean;

    const locale = localeFor(lang);
    return Object.prototype.hasOwnProperty.call(locale.strings || {}, clean)
      ? locale.strings[clean]
      : clean;
  }

  function translateTextNode(node, lang) {
    if (!textSources.has(node)) {
      const source = normalize(node.nodeValue);
      if (!source) return;
      textSources.set(node, source);
    }

    const source = textSources.get(node);
    const translated = translateSource(source, lang);
    const leading = /^\s*/.exec(node.nodeValue || "")?.[0] || "";
    const trailing = /\s*$/.exec(node.nodeValue || "")?.[0] || "";
    node.nodeValue = `${leading}${translated}${trailing}`;
  }

  function translateAttributes(element, lang) {
    const attrs = ["placeholder", "aria-label", "title", "alt"];
    let sources = attrSources.get(element);
    if (!sources) {
      sources = {};
      attrSources.set(element, sources);
    }

    attrs.forEach((attr) => {
      if (!element.hasAttribute(attr)) return;
      if (!Object.prototype.hasOwnProperty.call(sources, attr)) {
        sources[attr] = normalize(element.getAttribute(attr));
      }
      const source = sources[attr];
      element.setAttribute(attr, translateSource(source, lang));
    });
  }

  function translateScope(scope = document, lang = currentLanguage) {
    const root = scope instanceof Element || scope instanceof Document ? scope : document;

    const dynamic = root instanceof Element
      ? [root, ...root.querySelectorAll("[data-i18n-source]")].filter((el) => el.matches && el.matches("[data-i18n-source]"))
      : [...root.querySelectorAll("[data-i18n-source]")];

    dynamic.forEach((element) => {
      element.textContent = translateSource(element.getAttribute("data-i18n-source") || "", lang);
    });

    const walker = document.createTreeWalker(
      root,
      NodeFilter.SHOW_TEXT,
      {
        acceptNode(node) {
          const parent = node.parentElement;
          if (!parent) return NodeFilter.FILTER_REJECT;
          if (["SCRIPT", "STYLE", "NOSCRIPT", "CODE", "PRE"].includes(parent.tagName)) {
            return NodeFilter.FILTER_REJECT;
          }
          if (parent.closest("[data-i18n-ignore], [data-i18n-source]")) return NodeFilter.FILTER_REJECT;
          return normalize(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
        }
      }
    );

    const nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    nodes.forEach((node) => translateTextNode(node, lang));

    const elements = root instanceof Element ? [root, ...root.querySelectorAll("*")] : [...root.querySelectorAll("*")];
    elements.forEach((element) => translateAttributes(element, lang));
  }

  function updateMetadata(lang) {
    const locale = localeFor(lang);
    const meta = locale.meta || {};
    document.documentElement.lang = meta.htmlLang || lang;

    if (meta.title) document.title = meta.title;

    const description = document.querySelector('meta[name="description"]');
    if (description && meta.description) description.setAttribute("content", meta.description);

    const ogDescription = document.querySelector('meta[property="og:description"]');
    if (ogDescription && meta.description) ogDescription.setAttribute("content", meta.description);

    const twitterDescription = document.querySelector('meta[name="twitter:description"]');
    if (twitterDescription && meta.description) twitterDescription.setAttribute("content", meta.description);
  }


  function updateWhatsAppLinks(lang) {
    const locale = localeFor(lang);
    const message = locale.whatsappMessage;
    if (!message) return;

    document.querySelectorAll('a[href*="wa.me/56935444514?text="]').forEach((link) => {
      try {
        const url = new URL(link.href, document.baseURI);
        url.searchParams.set("text", message);
        link.href = url.href;
      } catch (error) {
        // Mantiene el href existente si no fuera una URL válida.
      }
    });
  }

  function updateControls(lang) {
    document.querySelectorAll("[data-language-select]").forEach((button) => {
      const active = button.dataset.languageSelect === lang;
      button.classList.toggle("active", active);
      button.setAttribute("aria-pressed", active ? "true" : "false");
    });

    document.querySelectorAll("[data-current-language]").forEach((element) => {
      element.textContent = lang.toUpperCase();
    });
  }

  function applyLanguage(lang, options = {}) {
    const next = SUPPORTED.includes(lang) ? lang : "es";
    currentLanguage = next;
    if (options.persist !== false) localStorage.setItem(STORAGE_KEY, next);

    translateScope(document, next);
    updateMetadata(next);
    updateControls(next);
    updateWhatsAppLinks(next);

    document.dispatchEvent(new CustomEvent("helheim:language-changed", {
      detail: { language: next }
    }));
  }

  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-language-select]");
    if (!button) return;
    applyLanguage(button.dataset.languageSelect || "es");
  });

  document.addEventListener("helheim:component-loaded", (event) => {
    if (event.target instanceof Element) {
      translateScope(event.target, currentLanguage);
      updateControls(currentLanguage);
      updateWhatsAppLinks(currentLanguage);
    }
  });

  document.addEventListener("helheim:components-ready", () => {
    translateScope(document, currentLanguage);
    updateControls(currentLanguage);
  });

  window.HelheimI18n = Object.freeze({
    get language() { return currentLanguage; },
    setLanguage: applyLanguage,
    translateScope,
    t: (source) => translateSource(source, currentLanguage)
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => applyLanguage(currentLanguage, { persist: false }), { once: true });
  } else {
    applyLanguage(currentLanguage, { persist: false });
  }
})();
