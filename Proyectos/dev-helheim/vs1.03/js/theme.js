(() => {
  "use strict";

  const STORAGE_KEY = "helheim-theme";
  const SUPPORTED = ["dark", "light"];

  function savedTheme() {
    const saved = localStorage.getItem(STORAGE_KEY);
    return SUPPORTED.includes(saved) ? saved : "dark";
  }

  let currentTheme = savedTheme();

  function updateControls(theme) {
    document.querySelectorAll("[data-theme-select]").forEach((button) => {
      const active = button.dataset.themeSelect === theme;
      button.classList.toggle("active", active);
      button.setAttribute("aria-pressed", active ? "true" : "false");
    });

    document.querySelectorAll("[data-current-theme-icon]").forEach((icon) => {
      icon.className = theme === "light"
        ? "bi bi-sun-fill me-1"
        : "bi bi-moon-stars-fill me-1";
    });
  }

  function applyTheme(theme, options = {}) {
    const next = SUPPORTED.includes(theme) ? theme : "dark";
    currentTheme = next;
    document.documentElement.dataset.theme = next;
    document.documentElement.style.colorScheme = next;

    if (options.persist !== false) {
      localStorage.setItem(STORAGE_KEY, next);
    }

    const themeColor = document.querySelector('meta[name="theme-color"]');
    if (themeColor) {
      themeColor.setAttribute("content", next === "light" ? "#f4efe9" : "#080a08");
    }

    updateControls(next);
    document.dispatchEvent(new CustomEvent("helheim:theme-changed", {
      detail: { theme: next }
    }));
  }

  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-theme-select]");
    if (!button) return;
    applyTheme(button.dataset.themeSelect || "dark");
  });

  document.addEventListener("helheim:component-loaded", () => updateControls(currentTheme));
  document.addEventListener("helheim:components-ready", () => updateControls(currentTheme));

  window.HelheimTheme = Object.freeze({
    get theme() { return currentTheme; },
    setTheme: applyTheme
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => applyTheme(currentTheme, { persist: false }), { once: true });
  } else {
    applyTheme(currentTheme, { persist: false });
  }
})();
