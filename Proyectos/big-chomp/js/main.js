/* ==========================================================================
   main.js — Completo y consolidado (sin duplicados)
   ========================================================================== */

/* ---------- Calendario (Flatpickr) + Regla 48h ---------- */
function minDate48h() {
  const d = new Date();
  d.setDate(d.getDate() + 2);
  d.setHours(0,0,0,0);
  return d;
}
function initDatePickers() {
  if (!window.flatpickr) return; // por si falla la CDN
  const opts = {
    minDate: minDate48h(),
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "j \\de F (D)",
    locale: flatpickr.l10ns.es,
    disableMobile: true,
  };
  document.querySelectorAll('#cartModal #orderDate, #cart #orderDate').forEach(el => {
    if (!el._fp) el._fp = flatpickr(el, opts);
  });
}

/* ---------- Utils ---------- */
const currency = v => '$' + Number(v || 0).toLocaleString('es-CL');
const pad2 = n => String(n).padStart(2, '0');
function todayPlus(days = 0){ const d=new Date(); d.setDate(d.getDate()+days); return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; }
function clamp(v,min,max){ return Math.min(max, Math.max(min, v)); }

/* ---------- Miniatura por producto ---------- */
function getProductThumb(item){
  // Tortas
  if (item.pid === 'p1') return 'images/products/product-1000x1000-1.png';           // Perro
  if (item.pid === 'p2') return 'images/products/product-1000x1000-gato-1.png';      // Gato
  if (item.pid === 'p4') return 'images/products/product-1000x1000-veggy-1.png';     // Herbívoros

  // Otros productos (banners)
  const name = (item.name||'').toLowerCase();
  if (name.includes('galletas personalizadas')) return 'images/banners/banner-900x675-galleta-1.png';
  if (name.includes('cupcakes')) return 'images/banners/banner-900x675_galleta-1.png';
  if (name.includes('muffins')) return 'images/banners/banner-900x675-muffins-1.png';
  if (name.includes('caja surtida')) return 'images/banners/banner-900x675-galleta-1.png';
  if (name.includes('galletas vacuno')) return 'images/banners/banner-900x675-galleta-2.png';
  if (name.includes('galletas pollo')) return 'images/banners/banner-900x675-galleta-1.png';
  if (name.includes('galletas verduras')) return 'images/banners/banner-900x675-galleta-1.png';
  if (name.includes('pack fiesta')) return 'images/banners/banner-900x675-galleta-1.png';

  // Fallback
  return 'images/products/product-1000x1000-1.png';
}

/* ---------- Carrito (estado y render) ---------- */
const CART = [];

function renderCartBase(){
  const list = document.getElementById('cartList');
  const total = document.getElementById('cartTotal');
  const badge = document.getElementById('cartCount');
  if(!list || !total) return;

  const itemsCount = CART.reduce((a,b)=> a + (b.qty||0), 0);
  if(badge){
    if(itemsCount>0){ badge.classList.remove('d-none'); badge.textContent = itemsCount; }
    else { badge.classList.add('d-none'); badge.textContent = '0'; }
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
          ${it.options.frosting? ' · Frosting: ' + it.options.frosting : ''}
        </small>`:''}
        ${it.options && it.options.notes ? `<small class="text-muted d-block">Notas: ${it.options.notes}</small>`:''}
        <div class="mt-1">
          <button class="btn btn-sm btn-ghost" onclick="minus(${idx})">−</button>
          <span class="mx-2">${it.qty}</span>
          <button class="btn btn-sm btn-ghost" onclick="plus(${idx})">+</button>
          <button class="btn btn-sm btn-link text-danger ms-2 p-0" onclick="removeFromCart(${idx})" title="Quitar"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>
      <div class="text-end">
        <div>${currency(it.price*it.qty)}</div>
      </div>
    </li>
  `).join('');

  const sum = CART.reduce((a,b)=> a + b.price*b.qty, 0);
  total.textContent = currency(sum);
}

function renderCartIntoModal(){
  const list = document.getElementById('cartListModal');
  const total = document.getElementById('cartTotalModal');
  if(!list || !total) return;

  if(!CART.length){
    list.innerHTML = `<li class="list-group-item text-center text-muted">Tu carrito está vacío</li>`;
    total.textContent = currency(0);
    return;
  }

  list.innerHTML = CART.map((it,idx)=>`
    <li class="list-group-item border-0 p-0 mb-2">
      <div class="cart-item">
        <div class="thumb"><img src="${it.img || getProductThumb(it)}" alt=""></div>
        <div class="flex-grow-1">
          <div class="fw-bold">${it.name}</div>
          ${it.options ? `<small class="text-muted d-block">
            ${it.options.tipo? it.options.tipo+' · ':''}
            ${it.options.prot? it.options.prot+' · ':''}
            ${it.options.sabor? it.options.sabor+' · ':''}
            ${it.options.colors? it.options.colors.join(' / ') + ' · ' :''}
            ${it.options.size? it.options.size+' · ':''}
            ${it.options.style? 'Estilo: '+it.options.style:''}
            ${it.options.frosting? ' · Frosting: ' + it.options.frosting : ''}
          </small>`:''}
          ${it.options && it.options.notes ? `<small class="text-muted d-block">Notas: ${it.options.notes}</small>`:''}
          <div class="mt-1">
            <button class="btn btn-sm btn-ghost" onclick="minus(${idx})">−</button>
            <span class="mx-2">${it.qty}</span>
            <button class="btn btn-sm btn-ghost" onclick="plus(${idx})">+</button>
            <button class="btn btn-sm btn-link text-danger ms-2 p-0" title="Quitar" onclick="removeFromCart(${idx})"><i class="bi bi-trash"></i></button>
          </div>
        </div>
        <div class="text-end fw-bold">${currency(it.price*it.qty)}</div>
      </div>
    </li>
  `).join('');

  const sum = CART.reduce((a,b)=> a + b.price*b.qty, 0);
  total.textContent = currency(sum);
}

/* Persistencia */
const LS_KEY = 'bc_cart';
function loadCart(){ try{ const raw=localStorage.getItem(LS_KEY); const data=raw?JSON.parse(raw):[]; if(Array.isArray(data)){ CART.length=0; CART.push(...data); } }catch{} }
function saveCart(){ try{ localStorage.setItem(LS_KEY, JSON.stringify(CART)); }catch{} }

/* Render central que actualiza todo + guarda */
function renderCart(){
  renderCartBase();
  renderCartIntoModal();
  saveCart();
}

/* Cantidades */
function plus(i){ if(CART[i]){ CART[i].qty++; renderCart(); } }
function minus(i){
  if(!CART[i]) return;
  if (CART[i].qty > 1) {
    CART[i].qty--;
    renderCart();
  } else {
    const removed = CART[i];
    CART.splice(i,1);
    renderCart();
    notifyRemoved(removed); // aviso en eliminación por “−”
  }
}
function removeFromCart(i){
  if(!CART[i]) return;
  const removed = CART[i];
  CART.splice(i,1);
  renderCart();
  notifyRemoved(removed);
}

/* ---------- Productos simples / cupcakes ---------- */
function addSimple(name, price){
  let item;
  const f=CART.find(i=>i.name===name&&i.price===price&&!i.options);
  if(f){ f.qty++; item = f; }
  else {
    item = {name, price, qty:1};
    item.img = getProductThumb(item);
    CART.push(item);
  }
  renderCart();
  notifyAdded(item);   // pop-up ~3s + ding
}

function updateCupPrice(){
  const sel=document.getElementById('cup_qty');
  const price=sel?Number(sel.selectedOptions[0].dataset.price||0):0;
  const tag=document.getElementById('cup_price');
  if(tag) tag.textContent=currency(price);
}
function addCupcakes(){
  const sel=document.getElementById('cup_qty'); if(!sel) return;
  const qty=sel.value; const price=Number(sel.selectedOptions[0].dataset.price||0);
  addSimple(`Cupcakes (${qty}u)`, price);
}

/* ---------- Tortas: agregar con opciones ---------- */
function addCake(cakeId, baseName){
  const options = {};
  ['sabor','prot','size','style'].forEach(k=>{
    const el=document.getElementById(`${cakeId}_${k}`); if(el) options[k]=el.value;
  });

  let frostingExtra=0;
  const frostEl=document.getElementById(`${cakeId}_frost`);
  if(frostEl){ options.frosting=frostEl.value; frostingExtra=Number(frostEl.selectedOptions[0]?.dataset.extra||'0'); }

  const colorWrap=document.getElementById(`${cakeId}_colors`);
  if(colorWrap){ const picked=Array.from(colorWrap.querySelectorAll('.pcolor:checked')).map(i=>i.value); options.colors=picked.slice(0,3); }

  const sizeEl=document.getElementById(`${cakeId}_size`);
  const price10=Number(sizeEl?.dataset.price10||0), price14=Number(sizeEl?.dataset.price14||0);
  const size=options.size||'10 cm'; const base=(size==='14 cm')?price14:price10;

  const galletasEl=document.getElementById(`${cakeId}_galletas`);
  const extra=galletasEl&&galletasEl.checked?Number(galletasEl.dataset.extra||0):0;

  const notesEl=document.getElementById(`${cakeId}_notes`); if(notesEl) options.notes=notesEl.value.trim();

  const qty=Math.max(1, parseInt(document.getElementById(`${cakeId}_qty`)?.value||'1',10));
  const finalPrice=(base||0)+extra+frostingExtra;

  const key=JSON.stringify({baseName,options,base,extra,frostingExtra});
  let item = CART.find(i=>i.key===key);
  if(item){ item.qty+=qty; }
  else {
    item = { name:baseName, price:finalPrice, qty, key, options, pid:cakeId };
    item.img = getProductThumb(item);
    CART.push(item);
  }
  renderCart();
  notifyAdded(item);   // pop-up ~3s + ding

  // Cerrar el modal de esta torta si está abierto
  const modalEl = document.getElementById(`modal-${cakeId}`);
  if (modalEl) {
    const inst = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
    inst.hide();
  }
}

/* ---------- WhatsApp (con validación 48h) ---------- */
function sendWhatsApp(){
  const phone='56958513034';
  if(!CART.length){ alert('Tu carrito está vacío.'); return; }

  const modalDate = document.querySelector('#cartModal #orderDate');
  const legacyDate = document.querySelector('#cart #orderDate');
  const dateEl = modalDate || legacyDate;

  let fechaTxt = '(por coordinar)';

  if (dateEl && dateEl.value) {
    const fechaISO = dateEl.value; // YYYY-MM-DD
    const picked = new Date(fechaISO + 'T00:00:00');
    if (picked < minDate48h()) {
      alert('Por favor elige una fecha con al menos 48 horas de anticipación.');
      return;
    }
    const [y,m,d] = fechaISO.split('-');
    fechaTxt = `${d}/${m}/${y}`;
  }

  const notes=document.getElementById('notes')?.value||'';

  const lines=CART.map(it=>{
    const opt = it.options ? ` (${[
      it.options.tipo, it.options.prot, it.options.sabor,
      (it.options.colors && it.options.colors.length ? it.options.colors.join('/') : null),
      it.options.size, (it.options.style ? 'Estilo ' + it.options.style : null),
      it.options.frosting ? ('Frosting ' + it.options.frosting) : null
    ].filter(Boolean).join(' · ')})` : '';
    return `• ${it.qty} x ${it.name}${opt} — ${currency(it.price*it.qty)}`;
  });

  const total=CART.reduce((a,b)=>a+b.price*b.qty,0);
  const msg=`Hola Big Chomp 👋
Quiero hacer un pedido:

${lines.join('\n')}
Total: ${currency(total)}

Fecha: ${fechaTxt}
Notas: ${notes}`;

  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`,'_blank');
}

/* ---------- Precios / ingredientes visibles ---------- */
// P1 perro
function updateDogPricing(id){
  const sabor=document.getElementById(`${id}_sabor`);
  const size=document.getElementById(`${id}_size`);
  if(!sabor||!size) return;
  const opt=sabor.selectedOptions[0];
  const p10=Number(opt.dataset.p10||0), p14=Number(opt.dataset.p14||0);
  size.dataset.price10=p10; size.dataset.price14=p14;
  if(size.options[0]) size.options[0].textContent=`10 cm (${currency(p10)})`;
  if(size.options[1]) size.options[1].textContent=`14 cm (${currency(p14)})`;
  previewCakePrice(id);
}
function updateDogIngredients(id){
  // Se elimina la leyenda "Ingredientes base: ..."
  const tag=document.getElementById(`${id}_ing`);
  if(tag) tag.textContent='';
}
// P2 gato
function updateCatPricing(id){
  const sabor=document.getElementById(`${id}_sabor`); const size=document.getElementById(`${id}_size`);
  if(!sabor||!size) return;
  const p10=Number(sabor.selectedOptions[0].dataset.p10||0);
  size.dataset.price10=p10;
  if(size.options[0]) size.options[0].textContent=`10 cm (${currency(p10)})`;
  previewCakePrice(id);
}
function updateCatIngredients(id){
  // Se elimina la leyenda "Ingredientes base: ..."
  const tag=document.getElementById(`${id}_ing`);
  if(tag) tag.textContent='';
}
// P3 BARF (compatibilidad si aún existiera)
function updateBarfPricing(id){
  const size=document.getElementById(`${id}_size`), isGato=document.getElementById(`${id}_gato`)?.checked;
  const note=document.getElementById(`${id}_note`); if(!size) return;
  if(isGato){
    size.dataset.price10=14000; size.dataset.price14=14000;
    if(size.options[0]) size.options[0].textContent=`10 cm (${currency(14000)})`;
    if(size.options[1]){ size.options[1].disabled=true; size.options[1].textContent=`14 cm (no disponible)`; }
    if(note) note.textContent='Para gato sugerimos 10 cm. La versión 14 cm queda deshabilitada.';
  }else{
    size.dataset.price10=16000; size.dataset.price14=18000;
    if(size.options[0]) size.options[0].textContent=`10 cm (${currency(16000)})`;
    if(size.options[1]){ size.options[1].disabled=false; size.options[1].textContent=`14 cm (${currency(18000)})`; }
    if(note) note.textContent='';
  }
  previewCakePrice(id);
}
function updateBarfIngredients(id){
  // Se elimina la leyenda "Ingredientes base: ..."
  const tag=document.getElementById(`${id}_ing`);
  if(tag) tag.textContent='';
}
function previewCakePrice(id){
  let frostingExtra=0; const frostEl=document.getElementById(`${id}_frost`);
  if(frostEl){ frostingExtra=Number(frostEl.selectedOptions[0]?.dataset.extra||'0'); }
  const sizeEl=document.getElementById(`${id}_size`); if(!sizeEl) return;
  const price10=Number(sizeEl.dataset.price10||0), price14=Number(sizeEl.dataset.price14||0);
  const sizeTxt=sizeEl.value||'10 cm'; const base=(sizeTxt==='14 cm')?price14:price10;
  const galletasEl=document.getElementById(`${id}_galletas`);
  const extra=galletasEl&&galletasEl.checked?Number(galletasEl.dataset.extra||0):0;
  const finalPrice=base+extra+frostingExtra;
  const tag=document.getElementById(`${id}_price`); if(tag) tag.textContent=currency(finalPrice);
}

/* ---------- Personalizador (acordeón + estado .personalizing) ---------- */
function openPersonalizer(productId, colId){
  const row=document.getElementById('productsRow');
  const col=document.getElementById(colId);
  const target=document.getElementById(productId);
  if(!row||!col||!target) return;

  if(!target.hasAttribute('data-bs-parent')) target.setAttribute('data-bs-parent','#productsRow');

  row.classList.add('personalizing');
  row.querySelectorAll('.product-col').forEach(c=>c.classList.remove('active'));
  col.classList.add('active');

  const opened=Array.from(document.querySelectorAll('#productsRow .collapse.show')).filter(el=>el.id!==productId);

  const showTarget=()=>{
    const instance=bootstrap.Collapse.getOrCreateInstance(target,{toggle:false});
    instance.show();

    const onShown=()=>{
      row.classList.add('personalizing');
      row.querySelectorAll('.product-col').forEach(c=>c.classList.toggle('active', c.id===colId));
      target.removeEventListener('shown.bs.collapse', onShown);
    };
    target.addEventListener('shown.bs.collapse', onShown);

    const onHidden=()=>{
      col.classList.remove('active');
      const anyOpen=row.querySelector('.collapse.show');
      if(!anyOpen){
        row.classList.remove('personalizing');
        row.querySelectorAll('.product-col').forEach(x=>x.classList.remove('active'));
      }
      target.removeEventListener('hidden.bs.collapse', onHidden);
    };
    target.addEventListener('hidden.bs.collapse', onHidden);
  };

  if(opened.length){
    let pending=opened.length;
    const done=()=>{ if(--pending===0) showTarget(); };
    opened.forEach(el=>{
      const inst=bootstrap.Collapse.getOrCreateInstance(el,{toggle:false});
      const onceHidden=()=>{ el.removeEventListener('hidden.bs.collapse', onceHidden); done(); };
      el.addEventListener('hidden.bs.collapse', onceHidden);
      inst.hide();
    });
  }else{
    showTarget();
  }
}

/* ---------- Ingredientes: carrusel + lightbox ---------- */
(function(){
  const carouselEl=document.getElementById('ingCarousel'); if(!carouselEl) return;
  const caro=bootstrap.Carousel.getOrCreateInstance(carouselEl,{interval:false,ride:false,wrap:false,touch:true});
  const slides=Array.from(carouselEl.querySelectorAll('.carousel-item'));
  const indexMap=new Map(); slides.forEach((item,i)=>indexMap.set(`${item.dataset.group}:${item.dataset.flavor}`,i));
  document.addEventListener('click',(e)=>{
    const pill=e.target.closest('.flavor-pill'); if(!pill) return;
    const key=`${pill.dataset.group}:${pill.dataset.flavor}`; if(!indexMap.has(key)) return;
    document.querySelectorAll('.flavor-pill').forEach(p=>p.classList.remove('active'));
    pill.classList.add('active'); caro.to(indexMap.get(key));
  });
  carouselEl.addEventListener('slid.bs.carousel',()=>{
    const active=carouselEl.querySelector('.carousel-item.active'); if(!active) return;
    document.querySelectorAll('.flavor-pill').forEach(p=>{
      const on=p.dataset.group===active.dataset.group && p.dataset.flavor===active.dataset.flavor;
      p.classList.toggle('active',on);
    });
  });

  const modalEl=document.getElementById('ingLightbox'); if(!modalEl) return;
  const modal=new bootstrap.Modal(modalEl);
  const zoomImg=document.getElementById('zoomImg');
  const zoomInBtn=document.getElementById('zoomIn');
  const zoomOutBtn=document.getElementById('zoomOut');
  const zoomResetBtn=document.getElementById('zoomReset');

  let scale=1, tx=0, ty=0, isPanning=false, startX=0, startY=0;
  const MIN=1, MAX=4, STEP=.25;

  function applyTransform(){ zoomImg.style.transform=`translate3d(${tx}px, ${ty}px, 0) scale(${scale})`; }
  function openLightboxFor(item){
    const isDesktop=window.matchMedia('(min-width:768px)').matches;
    const src=isDesktop?item.dataset.desktop:item.dataset.mobile;
    zoomImg.src=src||''; scale=1; tx=0; ty=0; applyTransform(); modal.show();
  }
  modalEl.addEventListener('shown.bs.modal',()=>{ scale=1; tx=0; ty=0; applyTransform(); });
  carouselEl.addEventListener('click',(e)=>{
    const link=e.target.closest('.ing-link'); if(!link) return; e.preventDefault();
    const item=e.target.closest('.carousel-item'); if(item) openLightboxFor(item);
  });

  zoomInBtn?.addEventListener('click',()=>{ scale=clamp(scale+STEP,MIN,MAX); applyTransform(); });
  zoomOutBtn?.addEventListener('click',()=>{ scale=clamp(scale-STEP,MIN,MAX); applyTransform(); });
  zoomResetBtn?.addEventListener('click',()=>{ scale=1; tx=0; ty=0; applyTransform(); });

  zoomImg?.addEventListener('dblclick',()=>{ if(scale===1) scale=2; else {scale=1; tx=0; ty=0;} applyTransform(); });
  zoomImg?.addEventListener('wheel',(ev)=>{ ev.preventDefault(); const d=Math.sign(ev.deltaY); scale=clamp(scale-d*STEP,MIN,MAX); applyTransform(); },{passive:false});
  zoomImg?.addEventListener('mousedown',(ev)=>{ if(scale===1) return; isPanning=true; startX=ev.clientX-tx; startY=ev.clientY-ty; zoomImg.style.cursor='grabbing'; });
  window.addEventListener('mousemove',(ev)=>{ if(!isPanning) return; tx=ev.clientX-startX; ty=ev.clientY-startY; applyTransform(); });
  window.addEventListener('mouseup',()=>{ isPanning=false; zoomImg.style.cursor=''; });

  let touchMode=null, startDist=0, baseScale=1, baseTx=0, baseTy=0;
  function dist(t1,t2){ const dx=t1.clientX-t2.clientX, dy=t1.clientY-t2.clientY; return Math.hypot(dx,dy); }
  zoomImg?.addEventListener('touchstart',(ev)=>{
    if(ev.touches.length===1){ touchMode='pan'; baseTx=tx; baseTy=ty; startX=ev.touches[0].clientX; startY=ev.touches[0].clientY; }
    else if(ev.touches.length===2){ touchMode='pinch'; startDist=dist(ev.touches[0],ev.touches[1]); baseScale=scale; }
  },{passive:false});
  zoomImg?.addEventListener('touchmove',(ev)=>{
    ev.preventDefault();
    if(touchMode==='pan' && ev.touches.length===1 && scale>1){ tx=baseTx+(ev.touches[0].clientX-startX); ty=baseTy+(ev.touches[0].clientY-startY); applyTransform(); }
    else if(touchMode==='pinch' && ev.touches.length===2){ const newDist=dist(ev.touches[0],ev.touches[1]); scale=clamp(baseScale*(newDist/startDist),MIN,MAX); applyTransform(); }
  },{passive:false});
  zoomImg?.addEventListener('touchend',()=>{ touchMode=null; });
})();

/* ---------- Preloader + Footer patrón ---------- */
(function(){
  const PRELOADER_MIN_MS=3000;
  const preloader=document.getElementById('preloader'); if(!preloader) return;
  const startExit=()=> preloader.classList.add('is-done');
  const onLoaded=()=>{ const elapsed=performance.now(); const wait=Math.max(0, PRELOADER_MIN_MS - elapsed); setTimeout(startExit, wait); };
  if(document.readyState==='complete'){ onLoaded(); } else { window.addEventListener('load', onLoaded); setTimeout(startExit, PRELOADER_MIN_MS+5000); }
})();
(function(){
  const footer=document.querySelector('footer.footer'); if(!footer) return;
  let layer=footer.querySelector('.footer-pattern');
  if(!layer){ layer=document.createElement('div'); layer.className='footer-pattern'; layer.setAttribute('aria-hidden','true'); footer.prepend(layer); }
  function calcCount(rect){ const area=rect.width*rect.height; const base=window.matchMedia('(min-width: 992px)').matches?14000:12000; return Math.max(14, Math.min(80, Math.round(area/base))); }
  function rand(min,max){ return Math.random()*(max-min)+min; }
  function renderPattern(){
    const rect=layer.getBoundingClientRect(); const count=calcCount(rect);
    layer.innerHTML='';
    for(let i=0;i<count;i++){
      const el=document.createElement('i'); el.className='bi bi-cake2 pattern-icon'; el.setAttribute('aria-hidden','true');
      const x=rand(2,98), y=rand(5,95), size=rand(18,36), rot=rand(-25,25), opa=rand(0.10,0.22);
      el.style.left=x+'%'; el.style.top=y+'%'; el.style.fontSize=size+'px'; el.style.opacity=opa.toFixed(2);
      el.style.position='absolute'; el.style.transform=`translate(-50%,-50%) rotate(${rot.toFixed(1)}deg)`; layer.appendChild(el);
    }
  }
  let t; function onResize(){ clearTimeout(t); t=setTimeout(renderPattern,150); }
  window.addEventListener('resize', onResize); window.addEventListener('orientationchange', onResize);
  const ro=new ResizeObserver(onResize); ro.observe(footer); renderPattern();
})();

/* ---------- DOM Ready ---------- */
document.addEventListener('DOMContentLoaded', ()=>{
  // Limitar colores a 3
  ['p1','p2'].forEach(pid=>{
    const wrap=document.getElementById(`${pid}_colors`); if(!wrap) return;
    wrap.addEventListener('change',()=>{
      const checks=Array.from(wrap.querySelectorAll('.pcolor:checked'));
      if(checks.length>3){ checks[0].checked=false; }
    });
  });

  // Forzar acordeón real
  document.querySelectorAll('#productsRow .collapse[id^="p"]').forEach(c=>{
    if(!c.hasAttribute('data-bs-parent')) c.setAttribute('data-bs-parent','#productsRow');
    c.addEventListener('hidden.bs.collapse', ()=>{
      const row=document.getElementById('productsRow');
      const col=document.getElementById(`col-${c.id}`);
      col?.classList.remove('active');
      if(!row.querySelector('.collapse.show')){
        row.classList.remove('personalizing');
        row.querySelectorAll('.product-col').forEach(x=>x.classList.remove('active'));
      }
    });
  });

  // Calendario “popup” con 48 h mínimo
  initDatePickers();

  // Cupcakes
  updateCupPrice();

  // Preview de precios en cambios
  ['p1','p2','p4'].forEach(pid=>{
    document.getElementById(`${pid}_frost`)?.addEventListener('change', ()=>previewCakePrice(pid));
    document.getElementById(`${pid}_galletas`)?.addEventListener('change', ()=>previewCakePrice(pid));
    document.getElementById(`${pid}_size`)?.addEventListener('change', ()=>previewCakePrice(pid));
  });
  updateDogPricing('p1'); updateCatPricing('p2'); previewCakePrice('p4');

  // Offcanvas bajo navbar
  const nav=document.querySelector('.navbar'); const cart=document.getElementById('cart');
  if(nav && cart){
    const setOffset=()=>{ const h=nav.getBoundingClientRect().height||56; cart.style.setProperty('--nav-offset', `${Math.round(h)}px`); };
    setOffset(); window.addEventListener('resize', setOffset);
    document.addEventListener('shown.bs.collapse', setOffset);
    document.addEventListener('hidden.bs.collapse', setOffset);
  }

  // Miniaturas → carrusel pequeño
  document.querySelectorAll('.thumbs[data-thumbs-for]').forEach(group=>{
    const targetSel=group.getAttribute('data-thumbs-for');
    const caroEl=document.querySelector(targetSel); if(!caroEl) return;
    const caro=bootstrap.Carousel.getOrCreateInstance(caroEl,{interval:false,ride:false,wrap:false,touch:true});
    const buttons=group.querySelectorAll('.thumb-circle');
    buttons.forEach((btn,idx)=>{
      btn.addEventListener('click', ()=>{
        caro.to(idx);
        buttons.forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
      });
    });
    caroEl.addEventListener('slid.bs.carousel', ()=>{
      const items=Array.from(caroEl.querySelectorAll('.carousel-item'));
      const i=items.indexOf(caroEl.querySelector('.carousel-item.active'));
      buttons.forEach(b=>b.classList.remove('active'));
      if(buttons[i]) buttons[i].classList.add('active');
    });
  });

  // Cargar carrito y pintar
  loadCart(); renderCart();

  // Botones que abren carrito SOLO si tienen clase explícita
  document.querySelectorAll('.js-open-cart').forEach(btn=>{
    btn.addEventListener('click', (e)=>{ e.preventDefault(); openCartModal(); });
  });
});

/* ---------- Carrito como MODAL ---------- */
function openCartModal(){
  const el = document.getElementById('cartModal');
  if(!el) return;
  const m = bootstrap.Modal.getOrCreateInstance(el, { backdrop:true, keyboard:true });
  renderCartIntoModal();
  m.show();
}

/* ---------- Precio mínimo visible en las cards ---------- */
function updateMinPrice(id){
  let p10 = 0;
  const sizeEl = document.getElementById(`${id}_size`);
  if (sizeEl) p10 = Number(sizeEl.dataset.price10 || 0);
  if (!p10) {
    if (id === 'p1') {
      const sabor = document.getElementById('p1_sabor');
      p10 = Number(sabor?.selectedOptions[0]?.dataset.p10 || 0);
    } else if (id === 'p2') {
      const sabor = document.getElementById('p2_sabor');
      p10 = Number(sabor?.selectedOptions[0]?.dataset.p10 || 0);
    } else if (id === 'p4') {
      p10 = Number(sizeEl?.dataset.price10 || sizeEl?.options?.[0]?.dataset?.price || 0);
    }
  }
  const tag = document.getElementById(`${id}_min`);
  if (tag) tag.textContent = currency(p10 || 0);
}

/* Inicializar y enganchar cambios del “mínimo” */
document.addEventListener('DOMContentLoaded', () => {
  updateMinPrice('p1');
  updateMinPrice('p2');
  updateMinPrice('p4');

  document.getElementById('p1_sabor')?.addEventListener('change', () => {
    updateDogPricing('p1');
    updateMinPrice('p1');
  });
  document.getElementById('p2_sabor')?.addEventListener('change', () => {
    updateCatPricing('p2');
    updateMinPrice('p2');
  });
  document.getElementById('p4_sabor')?.addEventListener('change', () => {
    previewCakePrice('p4');
    updateMinPrice('p4');
  });
});

/* ---------- Exports a window (para onclick en HTML) ---------- */
window.addSimple = addSimple;
window.updateCupPrice = updateCupPrice;
window.addCupcakes = addCupcakes;
window.addCake = addCake;
window.removeFromCart = removeFromCart;
window.plus = plus;
window.minus = minus;
window.openCartModal = openCartModal;
window.sendWhatsApp = sendWhatsApp;
window.updateDogPricing = updateDogPricing;
window.updateDogIngredients = updateDogIngredients;
window.updateCatPricing = updateCatPricing;
window.updateCatIngredients = updateCatIngredients;
window.updateBarfPricing = updateBarfPricing;
window.updateBarfIngredients = updateBarfIngredients;
window.openPersonalizer = openPersonalizer;
window.updateMinPrice = updateMinPrice;

/* =========================================
   Big Chomp — Notificador (flyIn / flyOut)
   ========================================= */
function ensureNoteStack() {
  let stack = document.getElementById('noteStack');
  if (!stack) {
    stack = document.createElement('div');
    stack.id = 'noteStack';
    stack.setAttribute('aria-live', 'polite');
    stack.setAttribute('aria-atomic', 'true');
    // Fuerza z-index muy alto para quedar sobre modales/backdrops
    stack.style.zIndex = '20000';
    document.body.appendChild(stack);
  }
  return stack;
}

/* beep cortito sin archivos (Web Audio) */
function playConfirm(){
  try{
    const ctx = new (window.AudioContext||window.webkitAudioContext)();
    const o = ctx.createOscillator();
    const g = ctx.createGain();
    o.type = 'sine';
    o.frequency.value = 880; // A5
    g.gain.value = 0.0001;
    o.connect(g); g.connect(ctx.destination);
    o.start();
    // breve envolvente
    const now = ctx.currentTime;
    g.gain.exponentialRampToValueAtTime(0.06, now + 0.01);
    g.gain.exponentialRampToValueAtTime(0.0001, now + 0.18);
    o.stop(now + 0.2);
  }catch{}
}

/**
 * Crea y muestra un toast con efecto flyIn/flyOut
 * - Desktop: entra por derecha
 * - Mobile: entra desde arriba (centrada)
 */
function showNote({ variant = 'info', title = '', meta = '' }) {
  const stack = ensureNoteStack();

  const note = document.createElement('div');
  note.className = `bc-note ${variant}`;

  const content = document.createElement('div');
  content.className = 'bc-note__content';

  const iconWrap = document.createElement('div');
  iconWrap.className = 'bc-note__icon';
  const svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
  svg.setAttribute('class','bc-note__icon-svg');
  svg.setAttribute('role','img');
  svg.setAttribute('aria-hidden','true');
  const use = document.createElementNS('http://www.w3.org/2000/svg','use');
  use.setAttributeNS('http://www.w3.org/1999/xlink','href',
    variant === 'success' ? '#bc-success' : '#bc-error');
  svg.appendChild(use);
  iconWrap.appendChild(svg);

  const text = document.createElement('div');
  text.className = 'bc-note__text';
  const t = document.createElement('div'); t.className = 'bc-note__title'; t.textContent = title || 'Notificación';
  const m = document.createElement('div'); m.className = 'bc-note__meta'; m.textContent = meta || '';
  text.appendChild(t); if (meta) text.appendChild(m);

  const btns = document.createElement('div'); btns.className = 'bc-note__btns';
  const btn = document.createElement('button'); btn.type='button'; btn.className='bc-note__btn'; btn.textContent='OK';
  // click + touchend para compatibilidad móvil
  const closeHandler = (ev)=>{ ev.preventDefault(); dismissNote(note); };
  btn.addEventListener('click', closeHandler);
  btn.addEventListener('touchend', closeHandler, { passive:false });
  btns.appendChild(btn);

  content.appendChild(iconWrap);
  content.appendChild(text);
  note.appendChild(content);
  note.appendChild(btns);
  stack.appendChild(note);

  // animación de entrada (desktop desde la derecha / mobile desde arriba)
  const isMobile = window.matchMedia('(max-width: 575.98px)').matches;
  note.style.animation = (isMobile ? 'flyInTop' : 'flyIn') + ' .3s ease-out';

  // autodestruir a los 3s; si el usuario toca la notificación, cancelamos el timer
  const timer = setTimeout(() => dismissNote(note), 3000);
  note.addEventListener('pointerdown', () => clearTimeout(timer), { once:true });
}

function dismissNote(note){
  if(!note || !note.isConnected) return;
  const isMobile = window.matchMedia('(max-width: 575.98px)').matches;
  note.style.animation = (isMobile ? 'flyOutTop' : 'flyOut') + ' .3s ease-out forwards';

  let removed = false;
  const done = () => {
    if (removed) return;
    removed = true;
    try { note.remove(); } catch {}
  };

  note.addEventListener('animationend', done, { once:true });
  // Fallback por si el navegador no lanza animationend
  setTimeout(done, 400);
}

/* Wrappers específicos del carrito */
function notifyAdded(item) {
  const name = item?.name || 'Producto';
  const qty  = item?.qty || 1;
  showNote({ variant:'success', title:'¡Agregado al carrito!', meta:`${qty} × ${name}` });
  playConfirm();
}
function notifyRemoved(item) {
  const name = item?.name || 'Producto';
  const qty  = item?.qty || 1;
  showNote({ variant:'danger', title:'Eliminado del carrito', meta:`${qty} × ${name}` });
}

/* Stepper para inputs #p1_qty / #p2_qty / #p4_qty */
function stepQty(pid, delta){
  const input = document.getElementById(`${pid}_qty`);
  if(!input) return;
  const cur = parseInt(input.value || '1', 10) || 1;
  const next = Math.max(1, cur + (delta||0));
  input.value = next;
}
window.stepQty = stepQty; // por los onclick del HTML
