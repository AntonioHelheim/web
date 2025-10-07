/* ===== Util ===== */
const currency = v => '$' + v.toLocaleString('es-CL');

/* ===== Carrito ===== */
const CART = [];

function addSimple(name, price){
  const found = CART.find(i => i.name===name && i.price===price && !i.options);
  if(found){ found.qty++; } else { CART.push({name, price, qty:1}); }
  renderCart(); new bootstrap.Offcanvas('#cart').show();
}

// Cupcakes con cantidad y precio visible
function updateCupPrice(){
  const sel = document.getElementById('cup_qty');
  if(!sel) return;
  const price = Number(sel.selectedOptions[0].dataset.price);
  const priceEl = document.getElementById('cup_price');
  if(priceEl) priceEl.textContent = currency(price);
}
function addCupcakes(){
  const sel = document.getElementById('cup_qty');
  const units = sel.value;
  const price = Number(sel.selectedOptions[0].dataset.price);
  const name = `Cupcakes (${units}u)`;
  const found = CART.find(i => i.name===name && i.price===price && !i.options);
  if(found){ found.qty++; } else { CART.push({name, price, qty:1}); }
  renderCart(); new bootstrap.Offcanvas('#cart').show();
}

/* Torta especial */
function addSpecialCake(){
  const notes = document.getElementById('customCakeNotes').value.trim();
  CART.push({
    name: 'Torta especial (precio referencial)',
    price: 10000,
    qty: 1,
    options: { notes: (notes? notes + ' — ' : '') + 'Precio referencial: el valor final se confirma por chat.' }
  });
  renderCart(); new bootstrap.Offcanvas('#cart').show();
}

/* Agregar torta con opciones */
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
  const base = size === '14 cm' ? price14 : price10;

  const galletasEl = document.getElementById(`${cakeId}_galletas`);
  const extra = galletasEl && galletasEl.checked ? Number(galletasEl.dataset.extra||0) : 0;

  const notesEl = document.getElementById(`${cakeId}_notes`);
  if(notesEl) options.notes = notesEl.value.trim();

  const key = JSON.stringify({baseName, options, base, extra});
  const found = CART.find(i => i.key===key);
  if(found){ found.qty++; }
  else {
    CART.push({ key, name: baseName, price: base+extra, qty:1, options:{...options, galletas: !!(galletasEl && galletasEl.checked)} });
  }
  renderCart(); new bootstrap.Offcanvas('#cart').show();
}

function removeFromCart(idx){ CART.splice(idx,1); renderCart(); }
function plus(idx){ CART[idx].qty++; renderCart(); }
function minus(idx){ CART[idx].qty = Math.max(1, CART[idx].qty-1); renderCart(); }

function renderCart(){
  const list = document.getElementById('cartList');
  const total = document.getElementById('cartTotal');
  const count = document.getElementById('cartCount');

  list.innerHTML = CART.length ? CART.map((it,idx)=>`
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
          ${it.options.galletas?' · +Galletas':''}
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
    </li>`).join('') :
    `<li class="list-group-item text-center text-muted">Tu carrito está vacío</li>`;

  const sum = CART.reduce((a,b)=>a+b.price*b.qty,0);
  total.textContent = currency(sum);

  const items = CART.reduce((a,b)=>a+b.qty,0);
  if(items>0){ count.textContent = items; count.classList.remove('d-none'); }
  else { count.classList.add('d-none'); }
}

/* ===== Personalizador ===== */
const row = document.getElementById('productsRow');

function openPersonalizer(panelId, colId){
  document.querySelectorAll('#productsRow .collapse.show').forEach(el=>{
    if(el.id !== panelId){ bootstrap.Collapse.getOrCreateInstance(el).hide(); }
  });
  const panel = document.getElementById(panelId);
  bootstrap.Collapse.getOrCreateInstance(panel).show();

  document.querySelectorAll('#productsRow .product-col').forEach(col=>{
    const btn = col.querySelector('.btn-personalize');
    if(col.id === colId){ col.classList.add('active'); btn?.classList.add('d-none'); }
    else { col.classList.remove('active'); btn?.classList.remove('d-none'); }
  });
  row.classList.add('personalizing');
}

function moveProductImage(collapseEl, toSlot){
  const card = collapseEl.closest('.product-card');
  const slot = collapseEl.querySelector('.img-slot');
  const origin = card.querySelector('.product-image-wrap');
  if(toSlot){
    if(origin && slot && origin.firstElementChild){
      slot.appendChild(origin.firstElementChild);
      slot.querySelector('img')?.classList.add('img-fluid');
    }
  }else{
    if(origin && slot && slot.firstElementChild){
      origin.appendChild(slot.firstElementChild);
      origin.querySelector('img')?.classList.remove('img-fluid');
    }
  }
}

document.querySelectorAll('#productsRow .collapse').forEach(el=>{
  el.addEventListener('hidden.bs.collapse', ()=>{
    moveProductImage(el, false);
    const card = el.closest('.product-card');
    card?.querySelector('.btn-personalize')?.classList.remove('d-none');
    if(!document.querySelector('#productsRow .collapse.show')){
      row.classList.remove('personalizing');
      document.querySelectorAll('#productsRow .product-col').forEach(c=>c.classList.remove('active'));
    }
  });
  el.addEventListener('shown.bs.collapse', ()=>{
    row.classList.add('personalizing');
    const activeCol = el.closest('.product-col');
    activeCol?.classList.add('active');
    moveProductImage(el, true);
    activeCol?.querySelector('.btn-personalize')?.classList.add('d-none');
    activeCol?.scrollIntoView({behavior:'smooth',block:'start',inline:'nearest'});
  });
});

/* ===== Precios + Ingredientes ===== */
function updateDogPricing(id){
  const sabor = document.getElementById(`${id}_sabor`);
  const size = document.getElementById(`${id}_size`);
  const opt = sabor.selectedOptions[0];
  const p10 = Number(opt.dataset.p10); const p14 = Number(opt.dataset.p14);
  size.dataset.price10 = p10; size.dataset.price14 = p14;
  size.options[0].textContent = `10 cm (${currency(p10)})`;
  size.options[1].textContent = `14 cm (${currency(p14)})`;
}
function updateDogIngredients(id){
  const opt = document.getElementById(`${id}_sabor`).selectedOptions[0];
  const base = opt.dataset.base;
  document.getElementById(`${id}_ing`).textContent =
    `Ingredientes: avena, huevo y papa + ${base}.`;
}

function updateCatPricing(id){
  const sabor = document.getElementById(`${id}_sabor`);
  const size  = document.getElementById(`${id}_size`);
  const p10 = Number(sabor.selectedOptions[0].dataset.p10);
  size.dataset.price10 = p10;
  size.options[0].textContent = `10 cm (${currency(p10)})`;
}
function updateCatIngredients(id){
  const opt = document.getElementById(`${id}_sabor`).selectedOptions[0];
  const base = opt.dataset.base;
  document.getElementById(`${id}_ing`).textContent =
    `Ingredientes: avena y huevo + ${base}.`;
}

function updateBarfPricing(id){
  const size  = document.getElementById(`${id}_size`);
  const tipoGato = document.getElementById(`${id}_gato`)?.checked;
  const note = document.getElementById(`${id}_note`);
  if(tipoGato){
    size.dataset.price10 = 14000;
    size.dataset.price14 = 14000;
    size.options[0].textContent = `10 cm (${currency(14000)})`;
    size.options[1].disabled = true;
    size.value = '10 cm';
    note.textContent = 'Para gatos ofrecemos 10 cm.';
  }else{
    size.options[1].disabled = false;
    size.dataset.price10 = 16000;
    size.dataset.price14 = 18000;
    size.options[0].textContent = `10 cm (${currency(16000)})`;
    size.options[1].textContent = `14 cm (${currency(18000)})`;
    note.textContent = 'Para perros: incluye un poco de verduras.';
  }
}
function updateBarfIngredients(id){
  const tipoGato = document.getElementById(`${id}_gato`)?.checked;
  const prot = document.getElementById(`${id}_prot`).value;
  const txt = tipoGato
    ? `Ingredientes: ${prot.toLowerCase()} 100% (sin cereales ni verduras).`
    : `Ingredientes: ${prot.toLowerCase()} 100% + pequeñas verduras (sin cereales).`;
  document.getElementById(`${id}_ing`).textContent = txt;
}

/* ===== Fecha: mínimo 2 días y no pasado ===== */
function setOrderDateLimits(){
  const input = document.getElementById('orderDate');
  if(!input) return;

  const today = new Date();
  const min = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 2);
  const toISO = d => d.toISOString().slice(0,10);

  input.min = toISO(min);

  const validate = () => {
    input.setCustomValidity('');
    input.classList.remove('is-invalid');
    if(!input.value) return;

    const chosen = new Date(input.value + 'T00:00:00');
    if(chosen < min){
      input.setCustomValidity('Por favor elige una fecha con al menos 2 días de anticipación.');
      input.classList.add('is-invalid');
    }
  };

  input.addEventListener('input', validate);
  input.addEventListener('change', validate);
  validate();
}
setOrderDateLimits();

/* ===== Calculadora de porciones ===== */
(function initPortionCalc(){
  const btn = document.getElementById('pc_btn');
  if(!btn) return;

  const especieEl = document.getElementById('pc_especie');
  const pesoEl    = document.getElementById('pc_peso');
  const invEl     = document.getElementById('pc_invitados');
  const out       = document.getElementById('pc_result');

  function porcionGramos(especie, pesoKg){
    if(especie === 'gato'){
      if(pesoKg <= 4) return 20;
      if(pesoKg <= 6) return 30;
      return 40;
    }else{
      if(pesoKg < 5) return 40;
      if(pesoKg < 10) return 70;
      if(pesoKg < 20) return 120;
      if(pesoKg < 35) return 180;
      return 220;
    }
  }

  function elegirTortas(gramosNecesarios){
    const g10 = 200, g14 = 300;
    if(gramosNecesarios <= g10) return {texto:'10 cm', totalG:g10, tortas:[{size:'10 cm', g:g10}]};
    if(gramosNecesarios <= g14) return {texto:'14 cm', totalG:g14, tortas:[{size:'14 cm', g:g14}]};
    let resto = gramosNecesarios;
    const tortas = [];
    while(resto > 0){
      if(resto >= 260){ tortas.push({size:'14 cm', g:g14}); resto -= g14; }
      else { tortas.push({size:'10 cm', g:g10}); resto -= g10; }
    }
    const texto = tortas.length===1 ? tortas[0].size : `${tortas.length} tortas (mix 14/10 cm)`;
    const totalG = tortas.reduce((a,b)=>a+b.g,0);
    return {texto, totalG, tortas};
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

    out.innerHTML = `
      <strong>Recomendación:</strong> ${recomend.texto}.<br>
      <strong>Porción aprox. ${especie==='gato'?'por gato':'por perro'}:</strong> ${gramosPorMascota} g.<br>
      <strong>Asistentes (mascotas):</strong> ${asistentes} · <strong>Porciones estimadas:</strong> ${porcionesEstimadas}.<br>
      ${recomend.tortas.length>1 ? '<em>Sugerencia:</em> para tu grupo conviene pedir más de una torta.' : ''}
      <div class="mt-1"><small class="text-muted">Porciones como golosina ≤10% de calorías diarias (guía WSAVA/UC Davis). Consulta a tu vet si tu mascota tiene condiciones especiales.</small></div>
    `;
  }

  btn.addEventListener('click', calc);
})();

/* ===== Inicial ===== */
updateCupPrice();
['p1','p2','p3'].forEach(id=>{
  if(document.getElementById(id)){
    if(id==='p1'){ updateDogPricing(id); updateDogIngredients(id); }
    if(id==='p2'){ updateCatPricing(id); updateCatIngredients(id); }
    if(id==='p3'){ updateBarfPricing(id); updateBarfIngredients(id); }
  }
});

/* ===== WhatsApp ===== */
function sendWhatsApp(){
  const phone = '56958513034';
  if(!CART.length){ alert('Agrega al menos un producto 😊'); return; }

  const dateInput = document.getElementById('orderDate');
  dateInput.reportValidity();
  if(!dateInput.checkValidity()) return;

  let msg = `Hola Big Chomp! Me gustaría hacer el siguiente pedido:%0A%0A`;
  CART.forEach((it,i)=>{
    const line = it.options
      ? `${i+1}. ${it.name} x${it.qty} — ${currency(it.price*it.qty)}%0A   - ${[
          it.options.tipo, it.options.prot, it.options.sabor, it.options.color, it.options.size, it.options.design
        ].filter(Boolean).join(' · ')}${it.options.galletas?' · +Galletas':''}${it.options.notes?`%0A   - Notas: ${encodeURIComponent(it.options.notes)}`:''}%0A`
      : `${i+1}. ${it.name} x${it.qty} — ${currency(it.price*it.qty)}%0A`;
    msg += line;
  });
  const sum = CART.reduce((a,b)=>a+b.price*b.qty,0);
  const date = dateInput.value;
  const notes = document.getElementById('notes').value.trim();
  if(date){ msg += `%0AFecha solicitada: ${date}`; }
  if(notes){ msg += `%0ANotas generales: ${encodeURIComponent(notes)}`; }
  msg += `%0A%0ATotal: ${currency(sum)}`;
  window.open(`https://wa.me/${phone}?text=${msg}`, '_blank');
}
