
// Botón Scroll Up
(() => {
  const btn = document.getElementById('backToTop');
  if (!btn) return;

  const root = document.documentElement;

  const scrollPercent = () => {
    const st = root.scrollTop || document.body.scrollTop;
    const sh = root.scrollHeight || document.body.scrollHeight;
    const ch = root.clientHeight;
    const max = Math.max(1, sh - ch);
    return st / max; // 0..1
  };

  // Helpers para interpolar color
  const lerp = (a, b, t) => a + (b - a) * t;
  const lerpColor = (c1, c2, t) => {
    const r = Math.round(lerp(c1[0], c2[0], t));
    const g = Math.round(lerp(c1[1], c2[1], t));
    const b = Math.round(lerp(c1[2], c2[2], t));
    return `rgb(${r}, ${g}, ${b})`;
  };

  const setBtnColor = (p) => {
    const t = Math.min(1, Math.max(0, p));
    const green = [25, 135, 84];  // #198754
    const red   = [220, 53, 69];  // #dc3545
    const color = lerpColor(green, red, t);
    btn.style.setProperty('--to-top-bg', color);
  };

  const onScroll = () => {
    const p = scrollPercent();
    if (p >= 0.05) btn.classList.add('is-visible');
    else btn.classList.remove('is-visible');
    setBtnColor(p);
  };

  // Eventos
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);

  // Acción de subir
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  btn.addEventListener('click', () => {
    if (prefersReduced) window.scrollTo(0, 0);
    else window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();