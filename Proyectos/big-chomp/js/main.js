/* ==========================================================================
   main.js — Versión consolidada
   Objetivos clave:
   - Abrir personalizador en sitio (sin imagen gigante) y una torta a la vez
   - Mini–carrusel 250×250 que cambia con el estilo (Clásico / Flores / Minimal)
   - “Sorpréndeme” fija un estilo aleatorio
   - Ingredientes con píldoras → panel único por grupo (galletas / snacks)
   - Carrito + Cupcakes (precio dinámico) + WhatsApp
   - Calculadora con ingredientes base visibles y efectos de botones
   ========================================================================== */

/* =====================================================
   [UTILS]
   ===================================================== */
const currency = v => '$' + Number(v || 0).toLocaleString('es-CL');
const pad2 = n => String(n).padStart(2, '0');
function todayPlus(days = 0) {
  const d = new Date();
  d.setDate(d.getDate() + days);
  return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

/* =====================================================
   [CARRITO] Estructura y helpers
   ===================================================== */
const CART = [];

function renderCart(){
  const list = document.getElementById('cartList');
  const total = document.getElementById('cartTotal');
  const badge = document.getElementById('cartCount');
  if(!list || !total) return;

  // total de ítems (sumando cantidades)
  const itemsCount = CART.reduce((a,b)=> a + (b.qty||0), 0);

  // Badge: muestra/oculta + número
  if(badge){
    if(itemsCount > 0){
      badge.classList.remove('d-none');
      badge.textContent = itemsCount;
    }else{
      badge.classList.add('d-none');
      badge.textContent = '0';
    }
  }

  if(!CART.length){
    list.innerHTML = `<li class="list-group-item text-center text-muted">Tu carrito está vacío</li>`;
    total.textContent = currency(0);
    return;
  }

  list.innerHTML = CART.map((it,idx)=>`
    <li class="list-group-item d-flex justify-content-between align-items-start">
      <div class="me-2">
        <div class="fw-semibold">${it.name}</div>
        ${it.options ? `<small class="text-muted d-block">
          ${it.options.tipo? it.options.tipo+' · ':''}
          ${it.options.prot? it.options.prot+' · ':''}
          ${it.options.sabor? it.options.sabor+' · ':''}
          ${it.options.colors? it.options.colors.join(' / ') + ' · ' :''}
          ${it.options.size? it.options.size+' · ':''}
          ${it.options.style? 'Estilo: '+it.options.style:''}
        </small>`:''}
        ${it.options && it.options.notes ? `<small class="text-muted d-block">Notas: ${it.options.notes}</small>`:''}
        <div class="mt-1">
          <button class="btn btn-sm btn-ghost" onclick="minus(${idx})">−</button>
          <span class="mx-2">${it.qty}</span>
          <button class="btn btn-sm btn-ghost" onclick="plus(${idx})">+</button>
        </div>
      </div>
      <div class="text-end">
        <div>${currency(it.price*it.qty)}</div>
        <button class="btn btn-sm btn-link text-danger" onclick="removeFromCart(${idx})"><i class="bi bi-x-lg"></i></button>
      </div>
    </li>
  `).join('');

  const sum = CART.reduce((a,b)=>a + b.price*b.qty, 0);
  total.textContent = currency(sum);
}


/* =====================================================
   [PRODUCTOS SIMPLES] y CUPCAKES (precio dinámico)
   ===================================================== */
function addSimple(name, price){
  const found = CART.find(i => i.name===name && i.price===price && !i.options);
  if(found){ found.qty++; } else { CART.push({name, price, qty:1}); }
  renderCart();
  new bootstrap.Offcanvas('#cart').show();
}

function updateCupPrice(){
  const sel = document.getElementById('cup_qty');
  const price = sel ? Number(sel.selectedOptions[0].dataset.price || 0) : 0;
  const tag = document.getElementById('cup_price');
  if(tag) tag.textContent = currency(price);
}

function addCupcakes(){
  const sel = document.getElementById('cup_qty');
  if(!sel) return;
  const qty = sel.value;
  const price = Number(sel.selectedOptions[0].dataset.price || 0);
  addSimple(`Cupcakes (${qty}u)`, price);
}

/* =====================================================
   [TORTAS] Agregar con opciones
   ===================================================== */
function addCake(cakeId, baseName){
  const options = {};

  // Sabor / Proteína / Tamaño
  ['sabor','prot','size','style'].forEach(k=>{
    const el = document.getElementById(`${cakeId}_${k}`);
    if(el) options[k] = el.value;
  });

  // Frosting (puede no existir en P4)
  let frostingExtra = 0;
  const frostEl = document.getElementById(`${cakeId}_frost`);
  if(frostEl){
    options.frosting = frostEl.value;
    const x = frostEl.selectedOptions[0]?.dataset.extra || '0';
    frostingExtra = Number(x);
  }

  // Hasta 2 colores principales (checkbox)
  const colorWrap = document.getElementById(`${cakeId}_colors`);
  if(colorWrap){
    const picked = Array.from(colorWrap.querySelectorAll('.pcolor:checked')).map(i=>i.value);
    options.colors = picked.slice(0,2); // seguridad por si el límite no se aplicó
  }

  // Tipo (perro/gato) si existe (P3 BARF)
  const tipo = document.querySelector(`input[name="${cakeId}_tipo"]:checked`);
  if(tipo) options.tipo = tipo.value;

  // Tamaño y base de precio segun dataset del <select size>
  const sizeEl = document.getElementById(`${cakeId}_size`);
  const price10 = Number(sizeEl?.dataset.price10 || 0);
  const price14 = Number(sizeEl?.dataset.price14 || 0);
  const size   = options.size || '10 cm';
  const base   = size === '14 cm' ? price14 : price10;

  // Extras (galletas alrededor) — puede que no exista (P4)
  const galletasEl = document.getElementById(`${cakeId}_galletas`);
  const extra = galletasEl && galletasEl.checked ? Number(galletasEl.dataset.extra||0) : 0;

  // Notas
  const notesEl = document.getElementById(`${cakeId}_notes`);
  if(notesEl) options.notes = notesEl.value.trim();

  // Precio final
  const finalPrice = base + extra + frostingExtra;

  // Unificar ítems iguales
  const key = JSON.stringify({baseName, options, base, extra, frostingExtra});
  const found = CART.find(i => i.key===key);
  if(found){ found.qty++; }
  else {
    CART.push({ name: baseName, price: finalPrice, qty: 1, key, options });
  }

  renderCart();
  new bootstrap.Offcanvas('#cart').show();
}


/* =====================================================
   [WHATSAPP] Enviar pedido
   ===================================================== */
function sendWhatsApp(){
  const phone = '56958513034'; // número de ejemplo
  if(!CART.length){
    alert('Tu carrito está vacío.');
    return;
  }
  const dateEl = document.getElementById('orderDate');
  const notes = document.getElementById('notes')?.value || '';
  const fecha = dateEl?.value ? dateEl.value.split('-').reverse().join('/') : '(por coordinar)';

  const lines = CART.map(it=>{
    const opt = it.options ? ` (${[
      it.options.tipo, it.options.prot, it.options.sabor,
      it.options.color, it.options.size, it.options.design
    ].filter(Boolean).join(' · ')})` : '';
    return `• ${it.qty} x ${it.name}${opt} — ${currency(it.price*it.qty)}`;
  });

  const total = CART.reduce((a,b)=>a + b.price*b.qty, 0);

  const msg =
`Hola Big Chomp 👋
Quiero hacer un pedido:

${lines.join('\n')}
Total: ${currency(total)}

Fecha: ${fecha}
Notas: ${notes}`;

  const url = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;
  window.open(url, '_blank');
}

/* =====================================================
   [PRECIOS + INGREDIENTES] auto etiquetas
   ===================================================== */
// P1: perro
function updateDogPricing(id){
  const sabor = document.getElementById(`${id}_sabor`);
  const size  = document.getElementById(`${id}_size`);
  if(!sabor || !size) return;
  const opt = sabor.selectedOptions[0];
  const p10 = Number(opt.dataset.p10 || 0);
  const p14 = Number(opt.dataset.p14 || 0);
  size.dataset.price10 = p10; size.dataset.price14 = p14;
  if(size.options[0]) size.options[0].textContent = `10 cm (${currency(p10)})`;
  if(size.options[1]) size.options[1].textContent = `14 cm (${currency(p14)})`;
}
function updateDogIngredients(id){
  const opt = document.getElementById(`${id}_sabor`)?.selectedOptions[0];
  const base = opt?.dataset.base || '';
  const tag = document.getElementById(`${id}_ing`);
  if(tag) tag.textContent = `Ingredientes base: avena, huevo y papa + ${base}.`;
}

// P2: gato
function updateCatPricing(id){
  const sabor = document.getElementById(`${id}_sabor`);
  const size  = document.getElementById(`${id}_size`);
  if(!sabor || !size) return;
  const p10 = Number(sabor.selectedOptions[0].dataset.p10 || 0);
  size.dataset.price10 = p10;
  if(size.options[0]) size.options[0].textContent = `10 cm (${currency(p10)})`;
}
function updateCatIngredients(id){
  const opt = document.getElementById(`${id}_sabor`)?.selectedOptions[0];
  const base = opt?.dataset.base || '';
  const tag = document.getElementById(`${id}_ing`);
  if(tag) tag.textContent = `Ingredientes base: avena y huevo + ${base}.`;
}

// P3: BARF
function updateBarfPricing(id){
  const size  = document.getElementById(`${id}_size`);
  const isGato = document.getElementById(`${id}_gato`)?.checked;
  const note = document.getElementById(`${id}_note`);
  if(!size) return;

  if(isGato){
    size.dataset.price10 = 14000;
    size.dataset.price14 = 14000;
    if(size.options[0]) size.options[0].textContent = `10 cm (${currency(14000)})`;
    if(size.options[1]) { size.options[1].disabled = true; size.options[1].textContent = `14 cm (no disponible)`; }
    if(note) note.textContent = 'Para gato sugerimos 10 cm. La versión 14 cm queda deshabilitada.';
  } else {
    size.dataset.price10 = 16000;
    size.dataset.price14 = 18000;
    if(size.options[0]) size.options[0].textContent = `10 cm (${currency(16000)})`;
    if(size.options[1]) { size.options[1].disabled = false; size.options[1].textContent = `14 cm (${currency(18000)})`; }
    if(note) note.textContent = '';
  }
}
function updateBarfIngredients(id){
  const isGato = document.getElementById(`${id}_gato`)?.checked;
  const tag = document.getElementById(`${id}_ing`);
  if(tag) tag.textContent = isGato
    ? 'Ingredientes base: proteína seleccionada (100%), sin verduras.'
    : 'Ingredientes base: proteína seleccionada + pequeñas verduras.';
}

/* =====================================================
   [PERSONALIZADOR] Abrir en sitio + Mini–carrusel por estilo
   ===================================================== */
function getCarouselEl(pid){ return document.querySelector(`#${pid} .mini-carousel`); }
function lockMiniCarousel(pid, lock=true){ getCarouselEl(pid)?.classList.toggle('locked', lock); }
function unlockMiniCarousel(pid){ lockMiniCarousel(pid, false); }

function setCarouselTo(pid, index){
  const el = getCarouselEl(pid); if (!el) return;
  const c  = bootstrap.Carousel.getOrCreateInstance(el, { interval:false, ride:false, wrap:false, touch:true });
  c.to(index); // API oficial
}

function setVariantClass(pid, variant){
  const wrap = getCarouselEl(pid); if(!wrap) return;
  wrap.classList.remove('variant-classic','variant-flowers','variant-minimal','variant-festive');
  // usamos 'flowers' y 'festive' como sinónimos visuales
  wrap.classList.add(`variant-${variant}`);
}

function valueToVariant(val){
  const v=(val||'').toLowerCase();
  if(v.includes('min')) return 'minimal';
  if(v.includes('flor') || v.includes('fest')) return 'flowers';
  return 'classic';
}
function styleIndexForVariant(variant){
  return variant==='minimal' ? 2 : variant==='flowers' ? 1 : 0;
}

function initStyleControls(pid){
  // radios (si existen)
  const radios = document.querySelectorAll(`input[name="${pid}_design"]`);
  if(radios.length){
    radios.forEach(r=>{
      if(r.dataset.bound==='1') return; r.dataset.bound='1';
      r.addEventListener('change', ()=>{
        const variant = valueToVariant(r.value);
        const idx = r.dataset.styleIndex ? Number(r.dataset.styleIndex) : styleIndexForVariant(variant);
        setVariantClass(pid, variant);
        setCarouselTo(pid, idx);
        lockMiniCarousel(pid, true);
      });
    });
    // disparo inicial
    const checked = document.querySelector(`input[name="${pid}_design"]:checked`);
    const initVariant = valueToVariant(checked?.value || 'Clásico');
    const initIdx = checked?.dataset.styleIndex ? Number(checked.dataset.styleIndex) : styleIndexForVariant(initVariant);
    setVariantClass(pid, initVariant);
    setCarouselTo(pid, initIdx);
  } else {
    // sin radios: default clásico
    setVariantClass(pid, 'classic');
    setCarouselTo(pid, 0);
  }

  // botón Sorpréndeme (icon-only)
  const btn = document.querySelector(`#${pid} .btn-surprise`);
  if(btn && btn.dataset.bound!=='1'){
    btn.dataset.bound='1';
    btn.addEventListener('click', ()=>{
      const opts=['classic','flowers','minimal'];
      const pick=opts[Math.floor(Math.random()*opts.length)];
      const idx = styleIndexForVariant(pick);
      // Marca radio si coincide por data-style-index
      const radio = document.querySelector(`input[name="${pid}_design"][data-style-index="${idx}"]`);
      if(radio){ radio.checked = true; radio.dispatchEvent(new Event('change',{bubbles:true})); }
      else { setVariantClass(pid, pick); setCarouselTo(pid, idx); lockMiniCarousel(pid, true); }
      btn.classList.add('is-active'); setTimeout(()=>btn.classList.remove('is-active'), 600);
    });
  }
}

function openPersonalizer(productId, colId) {
  // Cierra cualquier otra abierta (acordeón manual)
  document.querySelectorAll('#productsRow .collapse.show').forEach(el=>{
    if (el.id !== productId) bootstrap.Collapse.getOrCreateInstance(el).hide();
  });

  const row = document.getElementById('productsRow');
  const col = document.getElementById(colId);
  const collapseEl = document.getElementById(productId);
  if (!row || !col || !collapseEl) return;

  // Marca activa y aplica layout de personalización
  row.classList.add('personalizing');
  row.querySelectorAll('.product-col').forEach(c=>c.classList.remove('active'));
  col.classList.add('active');

  // Abre el panel
  bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle:true });

  // Al cerrar: solo quitamos "personalizing" si NO queda otra abierta
  collapseEl.addEventListener('hidden.bs.collapse', ()=>{
    col.classList.remove('active');
    const anyOpen = row.querySelector('.collapse.show');
    if(!anyOpen) row.classList.remove('personalizing');
  }, { once:true });
}


/* =====================================================
   [INGREDIENTES] Píldoras → Mostrar panel correspondiente
   ===================================================== */
document.addEventListener('click', (e) => {
  const pill = e.target.closest('.flavor-pill');
  if (!pill) return;

  const group = pill.dataset.group;
  const flavor = pill.dataset.flavor;

  // Activar píldora
  document.querySelectorAll(`.flavor-pill[data-group="${group}"]`).forEach(p => p.classList.remove('active'));
  pill.classList.add('active');

  // Mostrar solo el panel elegido
  document.querySelectorAll(`.flavor-panel[data-group="${group}"]`).forEach(p => p.classList.add('d-none'));
  document.querySelector(`.flavor-panel[data-group="${group}"][data-flavor="${flavor}"]`)
    ?.classList.remove('d-none');
});

/* =====================================================
   [CALCULADORA DE PORCIONES]
   ===================================================== */
(function(){
  const especieEl = document.getElementById('pc_especie');
  const tamanoEl  = document.getElementById('pc_tamano');
  const pesoEl    = document.getElementById('pc_peso');
  const invEl     = document.getElementById('pc_invitados');
  const btnCalc   = document.getElementById('pc_btn');
  const out       = document.getElementById('pc_result');

  if(!btnCalc || !especieEl) return;

  // al cambiar especie, habilitar tamaño de perro
  especieEl.addEventListener('change', ()=>{
    const isDog = especieEl.value === 'perro';
    tamanoEl.disabled = !isDog;
  });

  // Porciones sugeridas por mascota (en gramos) — internas
  const BASE = {
    perro: {
      pequeno: { base: 60, extra: 12 },
      mediano: { base: 80, extra: 15 },
      grande:  { base: 100, extra: 18 }
    },
    gato: { base: 50, extra: 10 }
  };

  // Cálculo de porción por mascota (no se muestra “gramos” al usuario)
  function porcionGramos(especie, pesoKg, tamano){
    if(especie==='perro'){
      const p = BASE.perro[tamano||'mediano'];
      return Math.round(p.base + p.extra * Math.log2(Math.max(1,pesoKg)));
    }
    const g = BASE.gato;
    return Math.round(g.base + g.extra * Math.log2(Math.max(1,pesoKg)));
  }

  // Mapeo interno de tamaños de torta a su rendimiento (NO mostrar gramos)
  // 10 cm ~ 270 g, 14 cm ~ 420 g (solo para cálculo)
  const TORTAS = [{ size:'10 cm', g:270 }, { size:'14 cm', g:420 }];

  function seleccionarTortas(gramosNecesarios){
    let rest = gramosNecesarios, seleccion=[];
    while(rest > 0){
      const pick = rest > 360 ? TORTAS[1] : TORTAS[0]; // 14 si se necesita más, si no 10
      seleccion.push(pick);
      rest -= pick.g;
      // seguridad para no loop infinito
      if(seleccion.length>6) break;
    }
    return seleccion; // array de objetos { size, g }
  }

  function textoSugerenciaExtras(nTortas){
    if(nTortas <= 1) return '';
    return `<div class="mt-1"><em>Sugerencia:</em> si necesitas más de una torta, puedes complementar con cup cakes o galletas como extras.</div>`;
  }

  function calc(){
    out.classList.remove('text-danger');

    const especie = especieEl.value;
    const peso = parseFloat(pesoEl.value);
    const invitados = Math.max(0, parseInt(invEl.value||'0',10));
    const tamano = especie==='perro' ? (tamanoEl.value || 'mediano') : null;

    if(!peso || peso<=0){
      out.classList.add('text-danger');
      out.textContent = 'Ingresa el peso del cumpleañero.';
      return;
    }

    const porcionPorMascota = porcionGramos(especie, peso, tamano);
    const asistentes = 1 + invitados;
    const gramosTotales = asistentes * porcionPorMascota;

    const seleccion = seleccionarTortas(gramosTotales);
    const sizesDesc = seleccion.map(s=>s.size).join(' + ');

    // Porciones estimadas (no mostrar gramos)
    const porcionesEstimadas = asistentes; // 1 porción por asistente según la porción sugerida

    // “Qué tamaño corresponde a la porción sugerida”
    const porcionSola = porcionPorMascota <= 270 ? '10 cm' : '14 cm';

    out.innerHTML = `
      <strong>Porción sugerida:</strong> corresponde a una torta de <strong>${porcionSola}</strong> por mascota.<br>
      <strong>Recomendación para tu evento:</strong> ${sizesDesc}.<br>
      <strong>Asistentes (mascotas):</strong> ${asistentes} · <strong>Porciones estimadas:</strong> ${porcionesEstimadas}.
      ${textoSugerenciaExtras(seleccion.length)}
      <div class="mt-1"><small class="text-muted">Valores referenciales; ajusta según el apetito y la actividad de tu mascota.</small></div>
    `;
  }

  btnCalc.addEventListener('click', calc);
})();

/* =====================================================
   [PRELOADER]
   ===================================================== */
(function(){
  const PRELOADER_MIN_MS = 3000;
  const preloader = document.getElementById('preloader');
  if(!preloader) return;

  const startExit = ()=> preloader.classList.add('is-done');

  const onLoaded = ()=>{
    const elapsed = performance.now();
    const wait = Math.max(0, PRELOADER_MIN_MS - elapsed);
    setTimeout(startExit, wait);
  };

  if (document.readyState === 'complete') {
    onLoaded();
  } else {
    window.addEventListener('load', onLoaded);
    setTimeout(startExit, PRELOADER_MIN_MS + 5000); // fail-safe
  }
})();

/* =====================================================
   [INIT] DOM ready
   ===================================================== */
document.addEventListener('DOMContentLoaded', ()=>{
  // Limitar colores a 2 (para p1, p2, p3)
['p1','p2','p3'].forEach(pid=>{
  const wrap = document.getElementById(`${pid}_colors`);
  if(!wrap) return;
  wrap.addEventListener('change', ()=>{
    const checks = Array.from(wrap.querySelectorAll('.pcolor:checked'));
    if(checks.length <= 2) return;
    // si marcó un 3ro, desmarcar el más antiguo
    checks[0].checked = false;
  });
});
  // Acordeón garantizado en tortas
  document.querySelectorAll('#productsRow .collapse[id^="p"]').forEach(c=>{
    if(!c.hasAttribute('data-bs-parent')) c.setAttribute('data-bs-parent','#productsRow');
  });

  // Fecha mínima del pedido: hoy + 2 días
  const dateEl = document.getElementById('orderDate');
  if(dateEl) dateEl.min = todayPlus(2);

  // Precio inicial cupcakes
  updateCupPrice();
});

/* =====================================================
   [STUBS de seguridad si algo faltara externamente]
   (No reemplazan las funciones reales definidas arriba)
   ===================================================== */
window.addSimple = window.addSimple || function(){};
window.addCupcakes = window.addCupcakes || function(){};
window.updateCupPrice = window.updateCupPrice || function(){};
window.addCake = window.addCake || function(){};
window.updateDogPricing = window.updateDogPricing || function(){};
window.updateDogIngredients = window.updateDogIngredients || function(){};
window.updateCatPricing = window.updateCatPricing || function(){};
window.updateCatIngredients = window.updateCatIngredients || function(){};
window.updateBarfPricing = window.updateBarfPricing || function(){};
window.updateBarfIngredients = window.updateBarfIngredients || function(){};
window.sendWhatsApp = window.sendWhatsApp || sendWhatsApp;


/* =====================================================
   [INGREDIENTES] Píldoras ↔ Carrusel ÚNICO + Lightbox con Zoom
   ===================================================== */
(function(){
  const carouselEl = document.getElementById('ingCarousel');
  if(!carouselEl) return;

  const caro = bootstrap.Carousel.getOrCreateInstance(carouselEl, {
    interval:false, ride:false, wrap:false, touch:true
  });

  // Construye mapa (group:flavor) → index
  const slides = Array.from(carouselEl.querySelectorAll('.carousel-item'));
  const indexMap = new Map();
  slides.forEach((item, i) => indexMap.set(`${item.dataset.group}:${item.dataset.flavor}`, i));

  // Píldoras → ir a la slide
  document.addEventListener('click', (e) => {
    const pill = e.target.closest('.flavor-pill');
    if(!pill) return;
    const key = `${pill.dataset.group}:${pill.dataset.flavor}`;
    if(!indexMap.has(key)) return;

    document.querySelectorAll('.flavor-pill').forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    caro.to(indexMap.get(key));
  });

  // Al deslizar el carrusel, sincroniza la píldora activa
  carouselEl.addEventListener('slid.bs.carousel', () => {
    const active = carouselEl.querySelector('.carousel-item.active');
    if(!active) return;
    document.querySelectorAll('.flavor-pill').forEach(p => {
      const on = p.dataset.group === active.dataset.group && p.dataset.flavor === active.dataset.flavor;
      p.classList.toggle('active', on);
    });
  });

  /* -------- Lightbox con zoom (sin abrir nueva pestaña) -------- */
  const modalEl = document.getElementById('ingLightbox');
  const modal = new bootstrap.Modal(modalEl);
  const zoomImg = document.getElementById('zoomImg');
  const zoomInBtn = document.getElementById('zoomIn');
  const zoomOutBtn = document.getElementById('zoomOut');
  const zoomResetBtn = document.getElementById('zoomReset');

  let scale = 1, tx = 0, ty = 0, isPanning = false, startX = 0, startY = 0;
  const MIN = 1, MAX = 4, STEP = 0.25;

  function applyTransform(){
  zoomImg.style.transform = `translate3d(${tx}px, ${ty}px, 0) scale(${scale})`;
}
function openLightboxFor(item){
  const isDesktop = window.matchMedia('(min-width: 768px)').matches;
  const src = isDesktop ? item.dataset.desktop : item.dataset.mobile;
  zoomImg.src = src || '';
  // Reset total: imagen completa, sin zoom
  scale = 1; tx = 0; ty = 0;
  applyTransform();
  modal.show();
}

// Asegura que cada vez que se muestre, parta "contenida"
modalEl.addEventListener('shown.bs.modal', () => {
  scale = 1; tx = 0; ty = 0;
  applyTransform();
});


  // Click en imagen del carrusel → abrir modal con zoom
  carouselEl.addEventListener('click', (e) => {
    const link = e.target.closest('.ing-link');
    if(!link) return;
    e.preventDefault(); // NO abrir pestaña nueva
    const item = e.target.closest('.carousel-item');
    if(item) openLightboxFor(item);
  });

  // Controles
  zoomInBtn.addEventListener('click', () => { scale = clamp(scale + STEP, MIN, MAX); applyTransform(); });
  zoomOutBtn.addEventListener('click', () => { scale = clamp(scale - STEP, MIN, MAX); applyTransform(); });
  zoomResetBtn.addEventListener('click', () => { scale = 1; tx = 0; ty = 0; applyTransform(); });

  // Doble click para alternar zoom
  zoomImg.addEventListener('dblclick', () => {
    if (scale === 1) scale = 2; else { scale = 1; tx = 0; ty = 0; }
    applyTransform();
  });

  // Rueda del mouse para zoom
  zoomImg.addEventListener('wheel', (ev) => {
    ev.preventDefault();
    const delta = Math.sign(ev.deltaY);
    scale = clamp(scale - delta * STEP, MIN, MAX);
    applyTransform();
  }, { passive:false });

  // Pan con mouse
  zoomImg.addEventListener('mousedown', (ev) => {
    if (scale === 1) return;
    isPanning = true;
    startX = ev.clientX - tx;
    startY = ev.clientY - ty;
    zoomImg.style.cursor = 'grabbing';
  });
  window.addEventListener('mousemove', (ev) => {
    if(!isPanning) return;
    tx = ev.clientX - startX;
    ty = ev.clientY - startY;
    applyTransform();
  });
  window.addEventListener('mouseup', () => {
    isPanning = false;
    zoomImg.style.cursor = '';
  });

  // Pan y pinch en touch
  let touchMode = null, startDist = 0, baseScale = 1, baseTx = 0, baseTy = 0;
  function dist(t1, t2){ const dx=t1.clientX-t2.clientX, dy=t1.clientY-t2.clientY; return Math.hypot(dx,dy); }

  zoomImg.addEventListener('touchstart', (ev) => {
    if (ev.touches.length === 1) {
      touchMode = 'pan';
      baseTx = tx; baseTy = ty;
      startX = ev.touches[0].clientX;
      startY = ev.touches[0].clientY;
    } else if (ev.touches.length === 2) {
      touchMode = 'pinch';
      startDist = dist(ev.touches[0], ev.touches[1]);
      baseScale = scale;
    }
  }, { passive:false });

  zoomImg.addEventListener('touchmove', (ev) => {
    ev.preventDefault();
    if (touchMode === 'pan' && ev.touches.length === 1 && scale > 1) {
      tx = baseTx + (ev.touches[0].clientX - startX);
      ty = baseTy + (ev.touches[0].clientY - startY);
      applyTransform();
    } else if (touchMode === 'pinch' && ev.touches.length === 2) {
      const newDist = dist(ev.touches[0], ev.touches[1]);
      scale = clamp(baseScale * (newDist / startDist), MIN, MAX);
      applyTransform();
    }
  }, { passive:false });

  zoomImg.addEventListener('touchend', () => { touchMode = null; });

  // Reset al cerrar
  modalEl.addEventListener('hidden.bs.modal', () => {
    zoomImg.src = '';
    scale = 1; tx = 0; ty = 0; applyTransform();
  });
})();


// ===== Footer: patrón random con <i class="bi bi-cake2"></i> =====
(function(){
  const footer = document.querySelector('footer.footer');
  if (!footer) return;

  // Crea capa si no existe
  let layer = footer.querySelector('.footer-pattern');
  if (!layer) {
    layer = document.createElement('div');
    layer.className = 'footer-pattern';
    layer.setAttribute('aria-hidden','true');
    footer.prepend(layer);
  }

  // Densidad adaptable: +área => +iconos (con límites)
  function calcCount(rect){
    const area = rect.width * rect.height;
    const base = window.matchMedia('(min-width: 992px)').matches ? 14000 : 12000; // px² por icono aprox.
    return Math.max(14, Math.min(80, Math.round(area / base)));
  }

  function rand(min, max){ return Math.random() * (max - min) + min; }

  function renderPattern(){
    const rect = layer.getBoundingClientRect();
    const count = calcCount(rect);

    // Limpia anteriores
    layer.innerHTML = '';

    for (let i = 0; i < count; i++){
      const el = document.createElement('i');
      el.className = 'bi bi-cake2 pattern-icon';
      el.setAttribute('aria-hidden','true');

      // Posición aleatoria dentro del footer
      const x = rand(2, 98);   // % para evitar recortes
      const y = rand(5, 95);

      // Tamaño, rotación y opacidad aleatorias
      const size = rand(18, 36);             // px
      const rot  = rand(-25, 25);            // grados suaves
      const opa  = rand(0.10, 0.22);         // visibilidad

      el.style.left = x + '%';
      el.style.top  = y + '%';
      el.style.fontSize = size + 'px';
      el.style.opacity = opa.toFixed(2);
      el.style.transform = `translate(-50%, -50%) rotate(${rot.toFixed(1)}deg)`;

      layer.appendChild(el);
    }
  }

  // Render inicial y al redimensionar (con debounce)
  let t;
  function onResize(){ clearTimeout(t); t = setTimeout(renderPattern, 150); }

  window.addEventListener('resize', onResize);
  window.addEventListener('orientationchange', onResize);

  // Si el footer cambia de alto por contenido dinámico:
  const ro = new ResizeObserver(onResize);
  ro.observe(footer);

  // Primera ejecución
  renderPattern();
})();


document.addEventListener('DOMContentLoaded', ()=>{
  const especieEl = document.getElementById('pc_especie');
  const tamanoEl  = document.getElementById('pc_tamano');
  if(especieEl && tamanoEl){
    const toggleDogSize = ()=> tamanoEl.disabled = (especieEl.value !== 'perro');
    especieEl.addEventListener('change', toggleDogSize);
    toggleDogSize();
  }
});

