/************ Utilidades ************/
const clamp = (n, min, max) => Math.min(max, Math.max(min, n));
const fmtMMSS = (sec) => {
  const v = Math.max(0, Math.ceil(sec));
  const m = Math.floor(v / 60).toString().padStart(2,'0');
  const s = (v % 60).toString().padStart(2,'0');
  return `${m}:${s}`;
};

/************ Audio helpers (campanas) ************/
let _AUDIO_CTX = null;
function getAudioCtx(){
  if(!_AUDIO_CTX){
    _AUDIO_CTX = new (window.AudioContext || window.webkitAudioContext)();
  }
  return _AUDIO_CTX;
}

/**
 * Campana sintetizada con envolvente y armónicos.
 * @param {Object} o
 * @param {number} o.volume 0..1
 * @param {number} o.pitch  frecuencia base (Hz)
 * @param {number} o.duration dur. total aprox (s)
 * @param {boolean} o.doubleHit si true, “doble golpe”
 */
function playBell({ volume = 0.25, pitch = 880, duration = 0.5, doubleHit = false } = {}){
  try{
    const ctx = getAudioCtx();

    const makeHit = (startOffset = 0) => {
      const now = ctx.currentTime + startOffset;

      // Armónicos para timbre de campana (seno + triángulo)
      const osc1 = ctx.createOscillator();
      const osc2 = ctx.createOscillator();
      const gain = ctx.createGain();

      osc1.type = 'sine';
      osc2.type = 'triangle';

      osc1.frequency.value = pitch;        // base
      osc2.frequency.value = pitch * 2.0;  // parcial alto

      osc1.connect(gain);
      osc2.connect(gain);
      gain.connect(ctx.destination);

      const v = clamp(volume, 0, 1);
      gain.gain.setValueAtTime(0.0001, now);
      gain.gain.exponentialRampToValueAtTime(v, now + 0.01);                 // ataque
      gain.gain.exponentialRampToValueAtTime(Math.max(v*0.35, 0.0002), now + duration*0.5); // decay
      gain.gain.exponentialRampToValueAtTime(0.0001, now + duration);        // release

      osc1.start(now);
      osc2.start(now);
      osc1.stop(now + duration + 0.05);
      osc2.stop(now + duration + 0.05);
    };

    makeHit(0);
    if(doubleHit){
      makeHit(0.12); // segundo golpe
    }
  }catch(e){}
}

/************ Temporizador genérico ************/
class CountdownTimer {
  constructor(durationSec, { onTick, onEnd } = {}) {
    this.baseDuration = durationSec; // en segundos
    this.remaining = durationSec;
    this.running = false;
    this._startEpoch = 0;
    this._pausedAt = 0;
    this.onTick = onTick || (()=>{});
    this.onEnd  = onEnd  || (()=>{});
  }
  start() {
    if (this.running) return;
    this._startEpoch = performance.now();
    this.running = true;
  }
  restart() {
    this.reset();
    this.start();
  }
  pause() {
    if (!this.running) return;
    this.running = false;
    this._pausedAt = performance.now();
  }
  resume() {
    if (this.running) return;
    const pausedDur = performance.now() - this._pausedAt;
    this._startEpoch += pausedDur;
    this.running = true;
  }
  reset() {
    this.running = false;
    this.baseDuration = Math.max(this.baseDuration, 0); // seguridad
    this.remaining = this.baseDuration;
    this._startEpoch = 0;
    this._pausedAt = 0;
    this.onTick(this.remaining);
  }
  tick(nowMs) {
    if (!this.running) return;
    const elapsed = (nowMs - this._startEpoch) / 1000;
    this.remaining = clamp(this.baseDuration - elapsed, 0, this.baseDuration);
    this.onTick(this.remaining);
    if (this.remaining <= 0) {
      this.running = false;
      this.onEnd();
    }
  }
  setRemainingToCurrent() {
    this.baseDuration = Math.ceil(this.remaining);
    this._startEpoch = performance.now();
  }
}

/************ Estado de puntaje/faltas (centésimas) ************/
const state = {
  white: { scoreCents: 0, fouls: 0 },
  blue:  { scoreCents: 0, fouls: 0 }
};
const scoreEls = {
  white: document.getElementById('scoreWhite'),
  blue:  document.getElementById('scoreBlue')
};
const foulEls = {
  white: document.getElementById('foulsWhite'),
  blue:  document.getElementById('foulsBlue')
};
function renderSide(side){
  // Mostrar puntaje entero (sin decimales)
  const points = Math.round(state[side].scoreCents / 100);
  scoreEls[side].textContent = String(points);

  foulEls[side].textContent  = String(state[side].fouls);
}

function applyDelta(side, type, delta){
  if (type === 'score'){
    state[side].scoreCents = clamp(state[side].scoreCents + delta, 0, 99900);
  } else {
    state[side].fouls = clamp(state[side].fouls + delta, 0, 99);
  }
  renderSide(side);
}

/************ DOM refs ************/
const elMainClock   = document.getElementById('mainClock');
const mainBar       = document.getElementById('mainBar');

const btnMainStart  = document.getElementById('btnMainStart');
const btnMainReset  = document.getElementById('btnMainReset');
const btnMainStop   = document.getElementById('btnMainStop');

const btnAllReset   = document.getElementById('btnAllReset');
const btnFullscreen = document.getElementById('btnFullscreen');

const t30LeftEl  = document.getElementById('t30Left');
const t30LeftBar = document.getElementById('t30LeftBar');
const t30RightEl  = document.getElementById('t30Right');
const t30RightBar = document.getElementById('t30RightBar');

/************ Flags para alarmas ************/
let mainWarn30Played = false;

/************ Timers ************/
const mainTimer = new CountdownTimer(180, {
  onTick: (rem) => {
    // reloj
    elMainClock.textContent = fmtMMSS(rem);

    // barra
    const w = (rem / 180) * 100;
    mainBar.style.width = `${w}%`;
    mainBar.setAttribute('aria-valuenow', String(Math.ceil(rem)));

    // Advertencia a 30 s (una sola vez)
    if (!mainWarn30Played && rem <= 30 && rem > 0) {
      mainWarn30Played = true;
      playBell({ volume: 0.22, pitch: 880, duration: 0.45, doubleHit: false });
    }
  },
  onEnd:  () => {
    elMainClock.textContent = '00:00';
    mainBar.style.width = '0%';
    mainBar.setAttribute('aria-valuenow', '0');
    // campana fuerte (doble golpe)
    playBell({ volume: 0.55, pitch: 820, duration: 0.6, doubleHit: true });
  }
});

const t30Left = new CountdownTimer(30, {
  onTick: (rem) => {
    t30LeftEl.textContent = fmtMMSS(rem);
    t30LeftBar.style.width = (rem/30*100)+'%';
    t30LeftBar.setAttribute('aria-valuenow', String(Math.ceil(rem)));
  },
  onEnd:  () => {
    t30LeftEl.textContent = '00:00';
    t30LeftBar.style.width = '0%';
    playBell({ volume: 0.38, pitch: 900, duration: 0.5, doubleHit: false });
  }
});

const t30Right = new CountdownTimer(30, {
  onTick: (rem) => {
    t30RightEl.textContent = fmtMMSS(rem);
    t30RightBar.style.width = (rem/30*100)+'%';
    t30RightBar.setAttribute('aria-valuenow', String(Math.ceil(rem)));
  },
  onEnd:  () => {
    t30RightEl.textContent = '00:00';
    t30RightBar.style.width = '0%';
    playBell({ volume: 0.38, pitch: 900, duration: 0.5, doubleHit: false });
  }
});

/************ Loop único ************/
let rafId = 0;
function loop(now){
  mainTimer.tick(now);
  t30Left.tick(now);
  t30Right.tick(now);
  rafId = requestAnimationFrame(loop);
}
rafId = requestAnimationFrame(loop);

/************ Eventos – Cronómetro principal (Iniciar/Reset/Stop) ************/
btnMainStart.addEventListener('click', () => {
  try{ getAudioCtx().resume?.(); }catch(e){}
  mainWarn30Played = false;
  mainTimer.baseDuration = 180; // por si cambió
  mainTimer.reset();            // muestra 03:00 y barra llena
  mainTimer.start();            // arranca desde 03:00
});

btnMainReset.addEventListener('click', () => {
  mainWarn30Played = false;
  mainTimer.baseDuration = 180;
  mainTimer.reset();            // vuelve a 03:00 y se detiene
});

btnMainStop.addEventListener('click', () => {
  // mismo concepto que los 30s: Stop = pausa en el tiempo actual (no reinicia)
  mainTimer.pause();
  // opcional: no fijamos baseDuration, para que si más adelante se desea “resume” sea posible añadir un botón.
});

/************ Eventos – 30 segundos ************/
document.getElementById('btnT30LeftStart').addEventListener('click', () => {
  try{ getAudioCtx().resume?.(); }catch(e){}
  t30Left.restart();
});
document.getElementById('btnT30LeftReset').addEventListener('click', () => t30Left.reset());
document.getElementById('btnT30LeftStop').addEventListener('click',  () => t30Left.pause());

document.getElementById('btnT30RightStart').addEventListener('click', () => {
  try{ getAudioCtx().resume?.(); }catch(e){}
  t30Right.restart();
});
document.getElementById('btnT30RightReset').addEventListener('click', () => t30Right.reset());
document.getElementById('btnT30RightStop').addEventListener('click',  () => t30Right.pause());

/************ Eventos – Puntaje/Faltas (delegación) ************/
function bindControls(containerId, side){
  const el = document.getElementById(containerId);
  el.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-type]');
    if(!btn) return;
    const type = btn.getAttribute('data-type'); // score | foul
    const delta = parseInt(btn.getAttribute('data-delta'), 10);
    applyDelta(side, type, delta);
  });
}
bindControls('controlsWhite','white');
bindControls('controlsBlue','blue');

document.getElementById('resetWhite').addEventListener('click', () => {
  state.white.scoreCents = 0; state.white.fouls = 0; renderSide('white');
});
document.getElementById('resetBlue').addEventListener('click', () => {
  state.blue.scoreCents = 0; state.blue.fouls = 0; renderSide('blue');
});

/************ Reset total ************/
btnAllReset.addEventListener('click', () => {
  mainWarn30Played = false;
  mainTimer.baseDuration = 180;
  mainTimer.reset();
  t30Left.reset(); t30Right.reset();
  state.white = { scoreCents:0, fouls:0 };
  state.blue  = { scoreCents:0, fouls:0 };
  renderSide('white'); renderSide('blue');
});

/************ Pantalla completa ************/
btnFullscreen.addEventListener('click', () => {
  if (!document.fullscreenElement){
    document.documentElement.requestFullscreen?.();
  } else {
    document.exitFullscreen?.();
  }
});

/************ Teclado (opcional) ************/
// Adaptado a la nueva semántica: Space ahora detiene (Stop) en vez de pausar/reanudar.
document.addEventListener('keydown', (e)=>{
  if(e.code === 'Space'){ e.preventDefault(); btnMainStop.click(); }
  if(e.key.toLowerCase() === 'r'){ btnMainReset.click(); }
  if(e.key === '1'){ document.getElementById('btnT30LeftStart').click(); }
  if(e.key === '2'){ document.getElementById('btnT30RightStart').click(); }
});

/************ Tooltips Bootstrap ************/
(() => {
  const list = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  list.forEach(el => new bootstrap.Tooltip(el));
})();

/************ Init ************/
mainTimer.reset();              // pinta 03:00 y barra llena
t30Left.reset(); t30Right.reset();
renderSide('white'); renderSide('blue');
