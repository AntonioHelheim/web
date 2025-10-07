document.addEventListener('DOMContentLoaded', () => {
  const panels = document.querySelectorAll('.expanding-cards .panel');

  function resetPanels() {
    panels.forEach(p => {
      p.classList.remove('active');
      if (window.innerWidth <= 768) {
        p.style.height = '8vh';
      } else {
        p.style.flex = '0.5';
      }
    });
  }

  panels.forEach(panel => {
    panel.addEventListener('click', () => {
      const isActive = panel.classList.contains('active');
      if (!isActive) {
        resetPanels();
        panel.classList.add('active');
        if (window.innerWidth <= 768) {
          panel.style.height = '60vh';
        } else {
          panel.style.flex = '5';
        }
      } // Si ya estaba activa, no se vuelve a cerrar
    });
  });
});
