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
  if(!list || !total) return;

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
          ${it.options.color? it.options.color+' · ':''}
          ${it.options.size? it.options.size+' · ':''}
          ${it.options.design? it.options.design:''}
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

function plus(i){ CART[i].qty++; renderCart(); }
function minus(i){
  CART[i].qty = Math.max(0, CART[i].qty - 1);
  if(!CART[i].qty) CART.splice(i,1);
  renderCart();
}
function removeFromCart(i){ CART.splice(i,1); renderCart(); }

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
  ['sabor','color','size','prot'].forEach(k=>{
    const el = document.getElementById(`${cakeId}_${k}`);
    if(el) options[k] = el.value;
  });

  const design = document.querySelector(`input[name="${cakeId}_design"]:checked`);
  if(design) options.design = design.value;

  const tipo = document.querySelector(`input[name="${cakeId}_tipo"]:checked`);
  if(tipo) options.tipo = tipo.value;

  const sizeEl = document.getElementById(`${cakeId}_size`);
  const price10 = Number(sizeEl?.dataset.price10 || 0);
  const price14 = Number(sizeEl?.dataset.price14 || 0);
  const size   = options.size || '10 cm';
  const base   = size === '14 cm' ? price14 : price10;

  const galletasEl = document.getElementById(`${cakeId}_galletas`);
  const extra = galletasEl && galletasEl.checked ? Number(galletasEl.dataset.extra||0) : 0;

  const notesEl = document.getElementById(`${cakeId}_notes`);
  if(notesEl) options.notes = notesEl.value.trim();

  const key = JSON.stringify({baseName, options, base, extra});
  const found = CART.find(i => i.key===key);
  if(found){ found.qty++; }
  else {
    CART.push({ name: baseName, price: base + extra, qty: 1, key, options });
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
  // Cierra otros (acordeón)
  document.querySelectorAll('#productsRow .collapse.show').forEach(el=>{
    if (el.id !== productId) bootstrap.Collapse.getOrCreateInstance(el).hide();
  });

  const row = document.getElementById('productsRow');
  const col = document.getElementById(colId);
  const collapseEl = document.getElementById(productId);
  if (!row || !col || !collapseEl) return;

  // Marca activa (para ocultar botón "Seleccionar")
  row.classList.add('personalizing');
  row.querySelectorAll('.product-col').forEach(c=>c.classList.remove('active'));
  col.classList.add('active');

  // Abre colapsable sin mover imagen
  bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle:true });

  // Inicializa UI del panel
  unlockMiniCarousel(productId);
  initStyleControls(productId);

  // Al cerrar: limpia estados
  collapseEl.addEventListener('hidden.bs.collapse', ()=>{
    row.classList.remove('personalizing');
    col.classList.remove('active');
    setCarouselTo(productId, 0);
    unlockMiniCarousel(productId);
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
  const portion = document.getElementById('portionCalc');
  if(!portion) return;

  const especieEl = document.getElementById('pc_especie');
  const pesoEl    = document.getElementById('pc_peso');
  const invEl     = document.getElementById('pc_invitados');
  const btnCalc   = document.getElementById('pc_btn');
  const out       = document.getElementById('pc_result');

  const PRESETS = { perro: { base: 80, extra: 15 }, gato: { base: 50, extra: 10 } };

  function porcionGramos(especie, pesoKg){
    const p = PRESETS[especie] || PRESETS.perro;
    return Math.round(p.base + p.extra * Math.log2(Math.max(1,pesoKg)));
  }
  function elegirTortas(gramos){
    const piezas = [{ s:'10 cm', g:400 },{ s:'14 cm', g:650 }];
    let rest = gramos, taken=[];
    while(rest>0){ const pick = rest>500 ? piezas[1] : piezas[0]; taken.push(pick); rest-=pick.g; }
    const desc = taken.map(t=>t.s).join(' + ');
    return { texto:`${desc}`, tortas:taken, totalG:gramos };
  }

  function calc(){
    const especie = especieEl.value;
    const peso = parseFloat(pesoEl.value);
    const invitados = Math.max(0, parseInt(invEl.value||'0',10));
    out.classList.remove('text-danger');

    if(!peso || peso<=0){
      out.classList.add('text-danger');
      out.textContent = 'Ingresa el peso del cumpleañero.';
      return;
    }

    const gramosPorMascota = porcionGramos(especie, peso);
    const asistentes = 1 + invitados;
    const gramosNecesarios = asistentes * gramosPorMascota;

    const recomend = elegirTortas(gramosNecesarios);
    const porcionesEstimadas = Math.floor(recomend.totalG / gramosPorMascota);

    const baseIng = especie === 'gato'
      ? 'proteína animal, huevo (prioridad proteína)'
      : 'avena, huevo, papa y fruta (bases clásicas)';

    out.innerHTML = `
      <strong>Recomendación:</strong> ${recomend.texto}.<br>
      <strong>Porción aprox. ${especie==='gato'?'por gato':'por perro'}:</strong> ${gramosPorMascota} g.<br>
      <strong>Asistentes (mascotas):</strong> ${asistentes} · <strong>Porciones estimadas:</strong> ${porcionesEstimadas}.<br>
      <div class="mt-1"><strong>Ingredientes base:</strong> ${baseIng}.</div>
      ${recomend.tortas.length>1 ? '<div class="mt-1"><em>Sugerencia:</em> para tu grupo conviene pedir más de una torta.</div>' : ''}
      <div class="mt-1"><small class="text-muted">Porciones como referencia; consulta a tu vet si tu mascota tiene condiciones especiales.</small></div>
    `;
  }

  btnCalc?.addEventListener('click', calc);
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
   [INGREDIENTES] Píldoras → controlar carouseles
   ===================================================== */
const ING_CAROUSEL_MAP = {
  galletas: ['vacuno','pollo','verduras'],
  snacks: ['pollo-desh','vacuno-desh']
};

function gotoIngredientSlide(group, flavor){
  const index = ING_CAROUSEL_MAP[group]?.indexOf(flavor);
  if (index < 0) return;

  const ids = group === 'galletas'
    ? ['ingCarouselGalletas','ingCarouselGalletasM']
    : ['ingCarouselSnacks','ingCarouselSnacksM'];

  ids.forEach(id => {
    const el = document.getElementById(id);
    if(!el) return;
    const c = bootstrap.Carousel.getOrCreateInstance(el, { interval:false, ride:false, wrap:false, touch:true });
    c.to(index);
  });
}

document.addEventListener('click', (e) => {
  const pill = e.target.closest('.flavor-pill');
  if (!pill) return;

  const group = pill.dataset.group;
  const flavor = pill.dataset.flavor;

  // Activar píldora
  document.querySelectorAll(`.flavor-pill[data-group="${group}"]`).forEach(p => p.classList.remove('active'));
  pill.classList.add('active');

  // Ir a la diapositiva correspondiente (desktop y mobile sincronizados)
  gotoIngredientSlide(group, flavor);
});
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
