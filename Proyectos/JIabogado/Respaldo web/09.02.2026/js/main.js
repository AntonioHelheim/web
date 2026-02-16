document.addEventListener("DOMContentLoaded", () => {

  /* ====================== HERO FADE + SCROLL BUTTON ====================== */
  const hero = document.querySelector(".hero");
  const scrollBtn = document.getElementById("scrollTopBtn");

  window.addEventListener("scroll", () => {
    const scrollY = window.scrollY;

    if (hero) {
      hero.style.opacity = Math.max(0, 1 - scrollY / 700);
    }

    if (scrollBtn) {
      if (scrollY > 400) {
        scrollBtn.classList.add("show");
      } else {
        scrollBtn.classList.remove("show");
      }
    }
  });

  if (scrollBtn) {
    scrollBtn.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  }

});
