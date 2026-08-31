/**
 * scripts/test-maps.js
 *
 * Formaliza el chequeo que hasta ahora corría a mano por consola cada vez
 * que se rediseñaba un mapa (ítem 10 de ROADMAP-ARQUITECTURA.md) — mismo
 * flood-fill de conectividad, ahora guardado como script reutilizable en
 * vez de tener que acordarme de escribirlo de nuevo cada vez.
 *
 * Verifica, para cada mapa en MAPS:
 *   1. El layout es rectangular (todas las filas del mismo largo).
 *   2. El punto de aparición (`spawn`) está dentro del mapa y no es un
 *      tile bloqueado.
 *   3. El 100% de los tiles transitables son alcanzables desde el spawn
 *      (flood-fill) — ningún bolsón de terreno aislado por accidente.
 *   4. Cada tile de warp (valor 4) en el layout tiene una entrada
 *      correspondiente en `warps`, y viceversa — ningún warp "huérfano"
 *      (un tile 4 sin entrada, que rompería el juego al pisarlo) ni una
 *      entrada de `warps` que apunte a una celda que ya no es un warp.
 *   5. El mapa de destino de cada warp existe en MAPS, y el punto de
 *      aparición de destino está dentro de rango y no bloqueado — este
 *      chequeo específico habría detectado el bug real que se coló al
 *      agrandar el pueblo (coordenadas de destino en el sistema de
 *      coordenadas equivocado).
 *
 * Uso: node scripts/test-maps.js
 * Sale con código 0 si todo pasa, 1 si algo falla.
 */
const path = require('path');
const fs = require('fs');

// js/maps.js está pensado para cargarse como <script> normal en el
// navegador (define MAPS y funciones como globals, no usa module.exports)
// — se evalúa tal cual acá para reutilizarlo sin duplicar su contenido.
const mapsSrc = fs.readFileSync(path.join(__dirname, '..', 'js', 'maps.js'), 'utf8');
// eslint-disable-next-line no-eval
eval(mapsSrc);

let fallas = 0;
function fallo(msg) {
  console.error(`  FALLA: ${msg}`);
  fallas++;
}
function ok(msg) {
  console.log(`  OK: ${msg}`);
}

for (const mapKey of Object.keys(MAPS)) {
  const map = MAPS[mapKey];
  console.log(`\n=== ${mapKey} (${map.label || 'sin label'}) ===`);

  // 1. Layout rectangular
  const rows = map.layout.length;
  const cols = map.layout[0].length;
  const irregular = map.layout.some((row) => row.length !== cols);
  if (irregular) {
    fallo(`el layout no es rectangular (alguna fila no mide ${cols} columnas)`);
    continue; // sin esto no tiene sentido seguir revisando este mapa
  }
  ok(`layout rectangular ${cols}x${rows} (${cols * rows} tiles)`);

  // 2. Spawn válido
  const { x: sx, y: sy } = map.spawn;
  const spawnEnRango = sx >= 0 && sy >= 0 && sx < cols && sy < rows;
  if (!spawnEnRango) {
    fallo(`spawn (${sx},${sy}) está fuera del rango del mapa`);
    continue;
  }
  if (isTileBlocked(mapKey, sx, sy)) {
    fallo(`spawn (${sx},${sy}) cae sobre un tile bloqueado (tile=${tileAt(mapKey, sx, sy)})`);
  } else {
    ok(`spawn (${sx},${sy}) válido y transitable`);
  }

  // 3. Conectividad (flood-fill)
  const visited = Array.from({ length: rows }, () => new Array(cols).fill(false));
  if (spawnEnRango) {
    const stack = [[sx, sy]];
    visited[sy][sx] = true;
    while (stack.length) {
      const [x, y] = stack.pop();
      for (const [dx, dy] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
        const nx = x + dx;
        const ny = y + dy;
        if (nx < 0 || ny < 0 || nx >= cols || ny >= rows || visited[ny][nx]) continue;
        if (isTileBlocked(mapKey, nx, ny)) continue;
        visited[ny][nx] = true;
        stack.push([nx, ny]);
      }
    }
  }
  let totalTransitables = 0;
  let alcanzables = 0;
  const noAlcanzadas = [];
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      if (isTileBlocked(mapKey, x, y)) continue;
      totalTransitables++;
      if (visited[y][x]) alcanzables++;
      else noAlcanzadas.push(`(${x},${y})`);
    }
  }
  if (alcanzables === totalTransitables) {
    ok(`conectividad 100% (${alcanzables}/${totalTransitables} tiles transitables alcanzables)`);
  } else {
    fallo(
      `conectividad incompleta: ${alcanzables}/${totalTransitables} alcanzables — ` +
      `celdas aisladas: ${noAlcanzadas.slice(0, 10).join(', ')}${noAlcanzadas.length > 10 ? ', ...' : ''}`
    );
  }

  // 4. Consistencia de warps: cada tile=4 tiene entrada, cada entrada apunta a un tile=4
  const tilesWarp = new Set();
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      if (map.layout[y][x] === 4) tilesWarp.add(`${x},${y}`);
    }
  }
  const entradasWarp = new Set(Object.keys(map.warps || {}));
  const sinEntrada = [...tilesWarp].filter((k) => !entradasWarp.has(k));
  const sinTile = [...entradasWarp].filter((k) => !tilesWarp.has(k));
  if (sinEntrada.length) {
    fallo(`tile(s) de warp sin entrada en 'warps': ${sinEntrada.join(', ')} — el juego se rompería si alguien pisa ahí`);
  }
  if (sinTile.length) {
    fallo(`entrada(s) en 'warps' que ya no apuntan a un tile de warp real: ${sinTile.join(', ')}`);
  }
  if (!sinEntrada.length && !sinTile.length) {
    ok(`${tilesWarp.size} tile(s) de warp, todos con su entrada correspondiente en 'warps'`);
  }

  // 5. Cada warp apunta a un mapa/spawn de destino válido
  for (const [origen, warp] of Object.entries(map.warps || {})) {
    const destino = MAPS[warp.to];
    if (!destino) {
      fallo(`warp en (${origen}) apunta a un mapa inexistente: "${warp.to}"`);
      continue;
    }
    const dCols = destino.layout[0].length;
    const dRows = destino.layout.length;
    const { x: dx, y: dy } = warp.spawn;
    if (dx < 0 || dy < 0 || dx >= dCols || dy >= dRows) {
      fallo(`warp en (${origen}) hacia "${warp.to}" tiene spawn de destino (${dx},${dy}) fuera de rango (mapa ${dCols}x${dRows})`);
      continue;
    }
    if (isTileBlocked(warp.to, dx, dy)) {
      fallo(`warp en (${origen}) hacia "${warp.to}" tiene spawn de destino (${dx},${dy}) sobre un tile bloqueado`);
      continue;
    }
    ok(`warp (${origen}) -> "${warp.to}" (${dx},${dy}): destino válido y transitable`);
  }
}

console.log(`\n${'='.repeat(50)}`);
console.log(fallas === 0 ? `TODOS LOS MAPAS PASARON (${Object.keys(MAPS).length} mapas verificados)` : `${fallas} PROBLEMA(S) ENCONTRADO(S)`);
process.exit(fallas === 0 ? 0 : 1);
