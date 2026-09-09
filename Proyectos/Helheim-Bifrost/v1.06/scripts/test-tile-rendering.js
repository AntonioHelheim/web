/**
 * scripts/test-tile-rendering.js
 *
 * Consolida las pruebas ad-hoc escritas durante el desarrollo del
 * terreno/edificios/decoración (31-08 al 05-09-2026) en un test
 * permanente — antes vivían como scripts sueltos en /tmp, fuera del
 * proyecto, así que nada detectaba una regresión futura en
 * TileVisuals.js. Usa un mock mínimo de Phaser (sin depender de un
 * navegador), mismo patrón que scripts/test-battle-rules.js.
 *
 * Cubre, para cada tipo de tile/decoración con textura real:
 *   1. Con la textura cargada → usa la imagen real.
 *   2. Sin la textura cargada → cae al dibujo a mano (nunca revienta).
 * Y por separado: determinismo de las flores, y la proporción real de
 * aparición.
 *
 * Uso: node scripts/test-tile-rendering.js
 */

const fs = require('fs');
const path = require('path');

let fallas = 0;
function verificar(cond, msg) {
  if (cond) {
    console.log(`OK: ${msg}`);
  } else {
    console.log(`FALLA: ${msg}`);
    fallas++;
  }
}

// ---------- Mock mínimo de Phaser ----------
global.GB_PALETTE = {
  darkest: 0, dark: 0, light: 0, lightest: 0xffffff,
  water: 0, trunk: 0, rock: 0, sand: 0,
};

function mockShape(tipo, eventos) {
  return {
    tipo,
    setDepth() { return this; },
    setDisplaySize() { return this; },
    setAlpha() { return this; },
    setStrokeStyle() { return this; },
    setCrop(x, y, w, h) { eventos.push(`crop(${x},${y})`); return this; },
    play(key) { eventos.push(`play:${key}`); return this; },
    setFrame() { return this; },
  };
}

function crearEscena(texturasCargadas, eventos) {
  return {
    textures: {
      _cargadas: new Set(texturasCargadas),
      exists(k) { return this._cargadas.has(k); },
      get: () => ({ getSourceImage: () => ({ width: 768, height: 128 }) }),
    },
    anims: { exists: () => true },
    add: {
      rectangle: () => { eventos.push('rectangle'); return mockShape('rectangle', eventos); },
      ellipse: () => { eventos.push('ellipse'); return mockShape('ellipse', eventos); },
      circle: () => { eventos.push('circle'); return mockShape('circle', eventos); },
      polygon: () => { eventos.push('polygon'); return mockShape('polygon', eventos); },
      triangle: () => { eventos.push('triangle'); return mockShape('triangle', eventos); },
      sprite: (x, y, key, frame) => { eventos.push(`sprite:${key}:${frame}`); return mockShape('sprite', eventos); },
      image: (x, y, key) => { eventos.push(`image:${key}`); return mockShape('image', eventos); },
    },
  };
}

const srcPath = path.join(__dirname, '..', 'js', 'entities', 'TileVisuals.js');
const src = fs.readFileSync(srcPath, 'utf8');
// eslint-disable-next-line no-eval
eval(`${src}\nglobal.drawTile = drawTile; global.drawDecoration = drawDecoration;`);

// ---------- 1. Tiles con textura real + respaldo a dibujo a mano ----------
// [tileType, textureKey, etiqueta]
const TILES_CON_TEXTURA = [
  [0, 'tile_terrain_earth', 'camino/tierra'],
  [1, 'tile_terrain_grass', 'pasto alto'],
  [2, 'tile_tree', 'árbol suelto'],
  [3, 'tile_water', 'agua'],
  [4, 'tile_door', 'puerta'],
  [6, 'tile_boulder', 'roca'],
  [7, 'tile_terrain_sand', 'arena'],
  [9, 'tile_wall_panel', 'pared de edificio'],
  [10, 'tile_terrain_cave_floor', 'piso de cueva'],
];

console.log('=== Tiles: con textura real ===');
TILES_CON_TEXTURA.forEach(([tileType, textureKey, etiqueta]) => {
  const eventos = [];
  const escena = crearEscena([textureKey], eventos);
  drawTile(escena, 20, 20, 32, tileType);
  const usaTextura = eventos.some((e) => e.includes(textureKey));
  verificar(usaTextura, `tile ${tileType} (${etiqueta}) usa "${textureKey}" cuando está cargada`);
});

console.log('\n=== Tiles: sin ninguna textura (respaldo al dibujo a mano, nunca debe reventar) ===');
TILES_CON_TEXTURA.forEach(([tileType, , etiqueta]) => {
  const eventos = [];
  const escena = crearEscena([], eventos);
  let exploto = false;
  try {
    drawTile(escena, 20, 20, 32, tileType);
  } catch (e) {
    exploto = true;
  }
  verificar(!exploto, `tile ${tileType} (${etiqueta}) no revienta sin textura`);
  verificar(eventos.length > 0, `tile ${tileType} (${etiqueta}) dibuja algo a mano de respaldo`);
});

// ---------- 2. Pared: respaldo de 2 niveles (real -> black -> dibujo a mano) ----------
console.log('\n=== Pared de edificio: respaldo de 2 niveles ===');
{
  const eventos = [];
  drawTile(crearEscena(['tile_black'], eventos), 20, 20, 32, 9);
  verificar(eventos.some((e) => e === 'image:tile_black'), 'sin tile_wall_panel, cae a tile_black');
}

// ---------- 3. tile 8 (entrada de cueva) — nunca tuvo textura real, siempre dibujo a mano ----------
console.log('\n=== Entrada de cueva (tile 8) — siempre dibujo a mano, es un hito visual ===');
{
  const eventos = [];
  // Incluso con TODAS las demás texturas cargadas, tile 8 no debe usarlas.
  const todasLasTexturas = TILES_CON_TEXTURA.map(([, k]) => k);
  drawTile(crearEscena(todasLasTexturas, eventos), 20, 20, 32, 8);
  verificar(!eventos.some((e) => e.startsWith('image:') || e.startsWith('sprite:')), 'tile 8 nunca usa una textura de otro tipo, sigue con su dibujo distintivo');
}

// ---------- 4. Flores: determinismo + proporción real ----------
console.log('\n=== Flores: determinismo y proporción ===');
{
  const texturas = ['tile_terrain_earth', 'tile_flowers_1', 'tile_flowers_2'];
  const e1 = []; const e2 = [];
  drawTile(crearEscena(texturas, e1), 42, 42, 32, 0);
  drawTile(crearEscena(texturas, e2), 42, 42, 32, 0);
  verificar(JSON.stringify(e1) === JSON.stringify(e2), 'el mismo tile (42,42) da siempre el mismo resultado (determinista)');

  let apariciones = 0;
  for (let x = 0; x < 300; x += 1) {
    const eventos = [];
    drawTile(crearEscena(texturas, eventos), x, 900, 32, 0); // fila alta, sin chocar con mapas reales
    if (eventos.some((e) => e.startsWith('sprite:tile_flowers'))) apariciones += 1;
  }
  const proporcion = apariciones / 300;
  verificar(proporcion > 0.06 && proporcion < 0.2, `proporción de flores (~12% esperado) está en rango razonable (salió ${(proporcion * 100).toFixed(1)}%)`);
}

// ---------- 5. Decoraciones (drawDecoration) ----------
console.log('\n=== Decoraciones: con textura real ===');
const DECORACIONES_CON_TEXTURA = [
  ['desk', 'furniture_desk'],
  ['bed', 'furniture_bed'],
  ['dragon', 'battle_dragon_ancestral'],
  ['demon', 'battle_demonio_menor'],
];
DECORACIONES_CON_TEXTURA.forEach(([tipo, textureKey]) => {
  const eventos = [];
  drawDecoration(crearEscena([textureKey], eventos), 50, 50, 32, tipo);
  verificar(eventos.some((e) => e.includes(textureKey)), `decoración "${tipo}" usa "${textureKey}" cuando está cargada`);
});

console.log('\n=== Decoraciones: sin textura (respaldo al dibujo a mano) ===');
['desk', 'bed', 'dragon', 'demon', 'cross', 'star'].forEach((tipo) => {
  const eventos = [];
  let exploto = false;
  try {
    drawDecoration(crearEscena([], eventos), 50, 50, 32, tipo);
  } catch (e) {
    exploto = true;
  }
  verificar(!exploto, `decoración "${tipo}" no revienta sin textura`);
  verificar(eventos.length > 0, `decoración "${tipo}" dibuja algo de respaldo`);
});

{
  const eventos = [];
  let exploto = false;
  try {
    drawDecoration(crearEscena([], eventos), 50, 50, 32, 'tipo_inexistente');
  } catch (e) {
    exploto = true;
  }
  verificar(!exploto && eventos.length === 0, 'un tipo de decoración desconocido no dibuja nada y no revienta');
}

// ---------- Resultado ----------
console.log(`\n${fallas === 0 ? 'TODAS LAS PRUEBAS PASARON' : `${fallas} PRUEBA(S) FALLARON`}`);
process.exit(fallas === 0 ? 0 : 1);
