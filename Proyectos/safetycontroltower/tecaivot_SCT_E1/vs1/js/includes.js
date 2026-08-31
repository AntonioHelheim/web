//  Actualiza este número cada vez que subas cambios
const CACHE_VERSION = "2026-07-29-2030";

function loadSection(id, file, callback) {
  fetch(`${file}?v=${CACHE_VERSION}`)
    .then(res => res.text())
    .then(data => {
      const el = document.getElementById(id);
      if (el) el.innerHTML = data;

      if (callback) callback();
    })
    .catch(err => console.error(`Error cargando ${file}`, err));
}

document.addEventListener("DOMContentLoaded", () => {

  loadSection("header", "./partials/header.html");
  loadSection("nav", "./partials/nav.html");
  loadSection("hero", "./partials/hero.html");
  loadSection("areas", "./partials/areas.html");
  loadSection("footer", "./partials/footer.html");

  // MOBILE MENU
  loadSection("mobileMenu", "./partials/menu.html", () => {
    if (typeof initMobileMenu === "function") {
      initMobileMenu();
    }
  });

});