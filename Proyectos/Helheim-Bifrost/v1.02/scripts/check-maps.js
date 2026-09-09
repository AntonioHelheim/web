/**
 * scripts/check-maps.js
 *
 * Formaliza el chequeo que hasta ahora se hacía a mano por consola cada
 * vez que se rediseñaba un mapa (ítem 10 de ROADMAP-ARQUITECTURA.md):
 *
 *  1. Conectividad: desde el punto de aparición de cada mapa, ¿se puede
 *     llegar caminando a TODAS las celdas transitables? (flood-fill,
 *     mismo algoritmo usado a mano para validar el mapa de Renca).
 *  2. Warps: cada salida (`warps`) debe apuntar a un mapa que existe, con
 *     coordenadas de destino dentro de rango y sobre un tile transitable.
 *     Este chequeo específico habría detectado el bug real que se coló al
 *     agrandar el pueblo (las coordenadas de entrada a las rutas quedaron
 *     usando el sistema de coordenadas equivocado).
 *  3. Hitos (`landmarks`, si el mapa los tiene): coordenadas dentro de rango.
 *
 * Uso: node scripts/check-maps.js
 * Sale con código 0 si todo pasa, 1 si algo falla.
 */
const path = require('path');
const { MAPS, isTileBlocked } = require(path.join(__dirname, '..', 'js', 'maps.js'));

let fallas = 0;
let advertencias = 0;

function fallo(msg) {
  console.error(`  ❌ ${msg}`);
  fallas++;
}

function advertencia(msg) {
  console.warn(`  ⚠️  ${msg}`);
  advertencias++;
}

function ok(msg) {
  console.log(`  ✅ ${msg}`);
}

function dentroDeRango(map, x, y) {
  return Number.isInteger(x) && Number.isInteger(y) && x >= 0 && y >= 0 && x < map.layout[0].length && y < map.layout.length;
}

function checkConectividad(mapKey, map) {
  const rows = map.layout.length;
  const cols = map.layout[0].length;

  if (!dentroDeRango(map, map.spawn.x, map.spawn.y)) {
    fallo(`spawn (${map.spawn.x},${map.spawn.y}) está fuera de rango (mapa ${cols}x${rows})`);
    return;
  }
  if (isTileBlocked(mapKey, map.spawn.x, map.spawn.y)) {
    fallo(`spawn (${map.spawn.x},${map.spawn.y}) cae sobre un tile bloqueado`);
    return;
  }

  const visited = Array.from({ length: rows }, () => new Array(cols).fill(false));
  const stack = [[map.spawn.x, map.spawn.y]];
  visited[map.spawn.y][map.spawn.x] = true;
  let alcanzables = 0;

  while (stack.length) {
    const [x, y] = stack.pop();
    alcanzables++;
    for (const [dx, dy] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
      const nx = x + dx;
      const ny = y + dy;
      if (nx < 0 || ny < 0 || nx >= cols || ny >= rows || visited[ny][nx]) continue;
      if (isTileBlocked(mapKey, nx, ny)) continue;
      visited[ny][nx] = true;
      stack.push([nx, ny]);
    }
  }

  let totalTransitables = 0;
  const noAlcanzadas = [];
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      if (!isTileBlocked(mapKey, x, y)) {
        totalTransitables++;
        if (!visited[y][x]) noAlcanzadas.push(`(${x},${y})`);
      }
    }
  }

  if (noAlcanzadas.length === 0) {
    ok(`conectividad: ${alcanzables}/${totalTransitables} celdas transitables alcanzables`);
  } else {
    fallo(`conectividad: ${alcanzables}/${totalTransitables} alcanzables — sin alcanzar: ${noAlcanzadas.slice(0, 10).join(', ')}${noAlcanzadas.length > 10 ? ` (+${noAlcanzadas.length - 10} más)` : ''}`);
  }
}

function checkWarps(mapKey, map) {
  const entradas = Object.entries(map.warps || {});
  if (entradas.length === 0) {
    advertencia('no tiene ningún warp definido');
    return;
  }

  for (const [posicion, warp] of entradas) {
    const [x, y] = posicion.split(',').map(Number);

    if (!dentroDeRango(map, x, y)) {
      fallo(`warp en "${posicion}" está fuera de rango del propio mapa`);
      continue;
    }
    if (map.layout[y][x] !== 4) {
      advertencia(`warp en "${posicion}" no está sobre un tile tipo 4 (warp) — tiene tipo ${map.layout[y][x]}`);
    }

    const destino = MAPS[warp.to];
    if (!destino) {
      fallo(`warp en "${posicion}" apunta a un mapa inexistente: "${warp.to}"`);
      continue;
    }

    const { x: dx, y: dy } = warp.spawn || {};
    if (!dentroDeRango(destino, dx, dy)) {
      fallo(`warp en "${posicion}" -> "${warp.to}" tiene spawn de destino (${dx},${dy}) fuera de rango (${warp.to} es ${destino.layout[0].length}x${destino.layout.length})`);
      continue;
    }
    if (isTileBlocked(warp.to, dx, dy)) {
      fallo(`warp en "${posicion}" -> "${warp.to}" tiene spawn de destino (${dx},${dy}) sobre un tile bloqueado`);
      continue;
    }
  }

  ok(`${entradas.length} warp(s) verificados`);
}

function checkLandmarks(mapKey, map) {
  const landmarks = map.landmarks || [];
  if (landmarks.length === 0) return;

  const fueraDeRango = landmarks.filter((l) => !dentroDeRango(map, l.x, l.y));
  if (fueraDeRango.length > 0) {
    fallo(`${fueraDeRango.length} landmark(s) fuera de rango: ${fueraDeRango.map((l) => l.label).join(', ')}`);
  } else {
    ok(`${landmarks.length} landmark(s) dentro de rango`);
  }
}

console.log('=== Validando mapas de Bifrost ===\n');

for (const [mapKey, map] of Object.entries(MAPS)) {
  console.log(`${mapKey} (${map.label || 'sin label'}, ${map.layout[0].length}x${map.layout.length}):`);
  checkConectividad(mapKey, map);
  checkWarps(mapKey, map);
  checkLandmarks(mapKey, map);
  console.log('');
}

console.log('===================================');
if (fallas === 0) {
  console.log(`TODOS LOS MAPAS OK${advertencias > 0 ? ` (${advertencias} advertencia(s), revisar arriba)` : ''}`);
} else {
  console.log(`${fallas} PROBLEMA(S) ENCONTRADO(S)${advertencias > 0 ? ` + ${advertencias} advertencia(s)` : ''}`);
}
process.exit(fallas === 0 ? 0 : 1);
