/* main.js — navbar fijo + close en móvil + tema outline + scroll up + filtros + i18n + reveal */
(() => {
  /* Utilidades */
  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const cssVar  = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  const getTheme = () => document.documentElement.getAttribute('data-theme') || 'light';

  /* Links del CV por idioma */
  const CV_LINKS = {
    es: "https://docs.google.com/document/d/1YeVvplBCVE8HsPYBtVxVOMdpbd5BI9KI/edit?usp=sharing&ouid=104078916770451841823&rtpof=true&sd=true",
    en: "https://docs.google.com/document/d/1uNCdWp1oxIQPilMhf_ONfRc33RJJL4Zq/edit?usp=sharing&ouid=104078916770451841823&rtpof=true&sd=true",
    da: "https://docs.google.com/document/d/1_YTfGBHKOcXIXV4y4dzwOD1ZX8qE6Z_i/edit?usp=sharing&ouid=104078916770451841823&rtpof=true&sd=true"
  };

  /* ======= NAV HEIGHT dinámico (para fixed-top) ======= */
  function setNavHeightVar(){
    const nav = $('#mainNav');
    if (!nav) return;
    const h = nav.getBoundingClientRect().height || 64;
    document.documentElement.style.setProperty('--nav-height', `${h}px`);
  }

  /* ======= THEME ======= */
  function setTheme(next) {
    const root = document.documentElement;
    root.setAttribute('data-theme', next);
    root.setAttribute('data-bs-theme', next);
    try { localStorage.setItem('theme', next); } catch {}
    updateThemeIcon(next);
  }

  function updateThemeIcon(theme) {
    const icon = $('#themeIcon');
    if (!icon) return;
    if ((theme || getTheme()) === 'dark') {
      icon.classList.remove('bi-moon-stars-fill');
      icon.classList.add('bi-sun-fill');
    } else {
      icon.classList.remove('bi-sun-fill');
      icon.classList.add('bi-moon-stars-fill');
    }
  }

  function setupThemeToggle() {
    const btn = $('#themeToggle');
    if (!btn) return;

    try {
      const saved = localStorage.getItem('theme');
      if (saved) {
        document.documentElement.setAttribute('data-theme', saved);
        document.documentElement.setAttribute('data-bs-theme', saved);
      }
    } catch {}

    updateThemeIcon(getTheme());
    btn.setAttribute('aria-pressed', String(getTheme() === 'dark'));

    btn.addEventListener('click', () => {
      const current = getTheme();
      const next = current === 'light' ? 'dark' : 'light';
      setTheme(next);
      btn.setAttribute('aria-pressed', String(next === 'dark'));
    });
  }

// ===================== Botón Scroll Up =====================
(function backToTop(){
  const btn = document.getElementById('backToTop');
  if(!btn) return;

  const root = document.documentElement;

  function scrollPercent(){
    const st = root.scrollTop || document.body.scrollTop;
    const sh = root.scrollHeight || document.body.scrollHeight;
    const ch = root.clientHeight;
    const max = Math.max(1, sh - ch);
    return st / max; // 0.0 - 1.0
  }

  // Lerp helpers para color
  function lerp(a,b,t){ return a + (b - a) * t; }
  function lerpColor(c1, c2, t){
    const r = Math.round(lerp(c1[0], c2[0], t));
    const g = Math.round(lerp(c1[1], c2[1], t));
    const b = Math.round(lerp(c1[2], c2[2], t));
    return `rgb(${r}, ${g}, ${b})`;
  }
  function setBtnColor(p){
    const start = 0.0, end = 1.00;
    const t = Math.min(1, Math.max(0, (p - start)/(end - start)));
    const green = [25,135,84];  // #192887ff
    const red   = [220,53,69];  // #bd35dcff
    const color = lerpColor(green, red, t);
    btn.style.setProperty('--to-top-bg', color);
  }

  function onScroll(){
    const p = scrollPercent();
    if (p >= 0.05) {
      btn.classList.add('is-visible');
    } else {
      btn.classList.remove('is-visible');
    }
    setBtnColor(p);
  }

  // Mostrar/ocultar y colorear al cargar + eventos
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);

  // Subir al inicio (respeta prefers-reduced-motion)
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  btn.addEventListener('click', () => {
    if (prefersReduced) {
      window.scrollTo(0, 0);
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });
})();

  /* ======= NAVBAR helpers ======= */
  function setupAutoCloseOnNavLinks() {
    const nav = $('#nav');
    if (!nav) return;
    nav.addEventListener('click', (e) => {
      const a = e.target.closest('a.nav-link');
      if (!a) return;
      const collapse = (window.bootstrap && window.bootstrap.Collapse)
        ? window.bootstrap.Collapse.getOrCreateInstance(nav, { toggle:false })
        : null;
      if (collapse) collapse.hide();
    });
  }

  /* ======= REVEAL / PROGRESO ======= */
  let revealObserver = null;
  function setupRevealGroups() {
    $$('.reveal').forEach((el, i) => { if (!el.style.getPropertyValue('--i')) el.style.setProperty('--i', i); });
    if (revealObserver) revealObserver.disconnect();
    revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        const el = entry.target;
        if (entry.isIntersecting) {
          el.classList.add('in-view');
          if (el.classList.contains('progress')) {
            const bar = el.querySelector('.progress-bar');
            if (bar) {
              const to = el.getAttribute('data-to') || el.getAttribute('aria-valuenow') || 0;
              bar.style.setProperty('--to', `${to}%`);
              bar.setAttribute('aria-valuenow', to);
            }
          }
        }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });
    $$('.reveal').forEach((el) => revealObserver.observe(el));
  }

  /* ======= CHIPS (hover) ======= */
  function pickRandomAccentColor() {
    const candidates = ['--chip1','--chip2','--chip3','--chip4','--chip5','--accent'].map(v => cssVar(v)).filter(Boolean);
    if (candidates.length) return candidates[Math.floor(Math.random() * candidates.length)];
    const theme = getTheme(); const h = Math.floor(Math.random() * 360);
    const s = 70 + Math.floor(Math.random() * 20);
    const l = theme === 'dark' ? (45 + Math.floor(Math.random() * 10)) : (50 + Math.floor(Math.random() * 10));
    return `hsl(${h}deg ${s}% ${l}%)`;
  }
  function toRgb(color){ /* (igual que tu versión original) */ 
    if(!color) return null; color=color.trim();
    if(color[0]==='#'){let hex=color.slice(1); if(hex.length===3) hex=hex.split('').map(c=>c+c).join(''); const n=parseInt(hex,16); return {r:(n>>16)&255,g:(n>>8)&255,b:(n)&255};}
    let m=color.match(/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i); if(m) return {r:+m[1],g:+m[2],b:+m[3]};
    m=color.match(/hsl\s*\(\s*(\d+(?:\.\d+)?)\s*(?:deg)?\s*,\s*(\d+(?:\.\d+)?)%\s*,\s*(\d+(?:\.\d+)?)%\s*\)/i); if(m){
      const h=(+m[1])/360,s=(+m[2])/100,l=(+m[3])/100;
      if(s===0){const v=Math.round(l*255);return{r:v,g:v,b:v}}
      const q=l<.5?l*(1+s):l+s-l*s,p=2*l-q,hk=h,t=[hk+1/3,hk,hk-1/3].map(t=>{if(t<0)t+=1;if(t>1)t-=1;if(t<1/6)return p+(q-p)*6*t;if(t<1/2)return q;if(t<2/3)return p+(q-p)*(2/3-t)*6;return p});
      return {r:Math.round(t[0]*255),g:Math.round(t[1]*255),b:Math.round(t[2]*255)};}
    return null;
  }
  function idealTextColorFor(bg,fallback=''){ const rgb=toRgb(bg); if(!rgb) return fallback; const l=(0.2126*rgb.r+0.7152*rgb.g+0.0722*rgb.b)/255; return l>0.6?'#000':'#fff' }
  function paintActiveStyle(el){ if(!el) return; const bg=pickRandomAccentColor(); const text=idealTextColorFor(bg,'#fff'); el.style.backgroundColor=bg; el.style.borderColor=bg; el.style.color=text; el.style.transform='translateY(-1px)'; el.style.boxShadow='0 6px 20px rgba(0,0,0,.18)'; }
  function clearActiveStyle(el){ if(!el) return; el.style.backgroundColor=''; el.style.borderColor=''; el.style.color=''; el.style.transform=''; el.style.boxShadow=''; }
  function setupChipInteractions(){
    const isTouch = () => ('ontouchstart' in window) || navigator.maxTouchPoints > 0;

    document.addEventListener('mouseenter', (e) => {
      const chip = e.target.closest('.chip');
      if (!chip || isTouch()) return;
      paintActiveStyle(chip);
      chip.classList.add('chip-colored');
      chip.style.color = '#fff';
    }, true);

    document.addEventListener('mouseleave', (e) => {
      const chip = e.target.closest('.chip');
      if (!chip || isTouch()) return;
      clearActiveStyle(chip);
      chip.classList.remove('chip-colored');
    }, true);

    document.addEventListener('click', (e) => {
      const chip = e.target.closest('.chip');
      if (!chip) return;
      paintActiveStyle(chip);
      setTimeout(() => { clearActiveStyle(chip); chip.classList.remove('chip-colored'); }, 160);
    }, true);

    document.addEventListener('touchstart', (e) => {
      const chip = e.target.closest('.chip');
      if (!chip) return;
      paintActiveStyle(chip);
      chip.classList.add('chip-colored');
    }, { passive: true, capture: true });

    document.addEventListener('touchend', (e) => {
      const chip = e.target.closest('.chip');
      if (!chip) return;
      setTimeout(() => { clearActiveStyle(chip); chip.classList.remove('chip-colored'); }, 100);
    }, true);
  }

  /* ======= HIGHLIGHTS ======= */
  function setupHighlightInteractions(){
    const highlights = document.querySelectorAll('.card.highlight');
    const HIGHLIGHT_PALETTE = [
      '#7D27F5', '#B587F5', '#E427F5', '#EC87F5', '#F5279F', '#F587C7',
      '#1E88E5', '#00ACC1', '#43A047', '#EF6C00', '#8E24AA', '#D81B60'
    ];
    const pickColor = () => HIGHLIGHT_PALETTE[Math.floor(Math.random() * HIGHLIGHT_PALETTE.length)];
    const applyColor = (el) => {
      const bg = pickColor();
      el.style.backgroundColor = bg;
      el.style.borderColor = bg;
      el.classList.add('hl-colored');
      el.style.transform = 'translateY(-1px)';
      el.style.boxShadow = '0 6px 20px rgba(0,0,0,.18)';
    };
    const clearColor = (el) => {
      el.style.backgroundColor = '';
      el.style.borderColor = '';
      el.style.transform = '';
      el.style.boxShadow = '';
      el.classList.remove('hl-colored');
    };
    const isTouch = () => ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
    highlights.forEach(card => {
      card.addEventListener('mouseenter', () => { if (!isTouch()) applyColor(card); });
      card.addEventListener('mouseleave', () => { if (!isTouch()) clearColor(card); });
      card.addEventListener('click', () => { applyColor(card); setTimeout(() => clearColor(card), 180); }, { passive: true });
    });
  }

  /* ======= I18N ======= */
  const langLabel = document.querySelector('[data-current-lang]') || document.getElementById('langLabel');
  window.applyLang = (lang) => {
    const dict = (window.I18N && window.I18N[lang]) || {};
    $$('[data-i18n]').forEach((el) => {
      const key = el.getAttribute('data-i18n'); const val = dict[key];
      if (val === undefined) return;
      if (typeof val === 'string') { if (val.includes('<')) el.innerHTML = val; else el.textContent = val; }
      else if (Array.isArray(val)) { el.innerHTML = val.map((item)=>`<li>${item}</li>`).join(''); }
    });
    $$('[data-i18n-list]').forEach((el) => {
      const key=el.getAttribute('data-i18n-list'); const arr=dict[key];
      if (Array.isArray(arr)) el.innerHTML = arr.map((item,i)=>`<span class="chip reveal" style="--i:${i}">${item}</span>`).join('');
    });
    document.documentElement.lang = lang;
    try { localStorage.setItem('site-lang', lang); } catch {}
    if (langLabel) langLabel.textContent = (lang || '').toUpperCase();

    // Actualiza el botón de CV según el idioma
    const cv = document.getElementById('cvBtn');
    if (cv) {
      const url = CV_LINKS[lang] || CV_LINKS.es;
      cv.setAttribute('href', url);
      cv.setAttribute('target', '_blank');
      cv.setAttribute('rel', 'noopener');
    }

    setupRevealGroups();
  };
  function setupLanguageDropdown(){
    $$('.lang-opt').forEach(btn =>
      btn.addEventListener('click', () => window.applyLang(btn.getAttribute('data-lang') || 'es'))
    );
  }

  /* ======= WhatsApp CTA ======= */
  function setupWhatsAppCTA(){
    const a = document.getElementById('whCTA'); if(!a) return;
    const phone = a.getAttribute('data-phone');
    const text  = a.getAttribute('data-text') || '';
    if(!phone) return;
    const href = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
    a.setAttribute('href', href);
    a.setAttribute('rel','noopener');
    a.setAttribute('target','_blank');
  }

  /* ======= Filtros Portafolio ======= */
  function setupPortfolioFilters(){
    const group = document.querySelector('.filters'); if(!group) return;
    const buttons = group.querySelectorAll('[data-filter]');
    const cards = document.querySelectorAll('.project-card');
    const columns = Array.from(cards).map(card => card.closest('[class*="col-"]'));

    function applyFilter(val){
      buttons.forEach(b => b.classList.toggle('active', b.getAttribute('data-filter') === val));
      cards.forEach((card, idx) => {
        const cat = (card.getAttribute('data-category') || '').toLowerCase();
        const show = (val === 'all') || (cat === val);
        const col = columns[idx];
        if (col) col.classList.toggle('d-none', !show);
      });
    }

    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        const val = btn.getAttribute('data-filter');
        applyFilter(val);
      });
    });

    applyFilter('all');
  }

  /* ======= Init ======= */
  function init(){
    setNavHeightVar();
    setupThemeToggle();
    setupAutoCloseOnNavLinks();
    setupChipInteractions();
    setupLanguageDropdown();
    setupWhatsAppCTA();
    setupPortfolioFilters();
    setupHighlightInteractions();

    // Año del footer
    const y = document.getElementById('year');
    if (y) y.textContent = new Date().getFullYear();

    // Idioma inicial
    let defaultLang = 'es';
    try { defaultLang = localStorage.getItem('site-lang') || defaultLang; } catch {}
    window.applyLang(defaultLang);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

  window.addEventListener('resize', setNavHeightVar);
})();
