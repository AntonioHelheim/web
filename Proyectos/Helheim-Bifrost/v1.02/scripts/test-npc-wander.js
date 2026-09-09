/**
 * scripts/test-npc-wander.js
 *
 * Prueba la lógica de merodeo de NPC.js (elegir una dirección válida:
 * transitable y dentro del radio respecto al punto de origen) contra los
 * mapas reales del proyecto — sin necesitar Phaser ni un navegador, igual
 * que scripts/check-maps.js. Reemplaza el script temporal usado durante
 * el desarrollo del ítem 7 de ROADMAP-ARQUITECTURA.md.
 *
 * No importa la clase NPC directamente (vive en un archivo pensado para
 * el navegador, con `scene`/Phaser de por medio) — en su lugar reimplementa
 * la función pura de "elegir dirección válida" con el mismo criterio
 * exacto que usa NPC.tryStep(), y la prueba con muchos pasos simulados.
 * Si cambias esa lógica en NPC.js, cambia también acá.
 *
 * Uso: node scripts/test-npc-wander.js
 */

const path = require('path');
const { MAPS, isTileBlocked } = require(path.join(__dirname, '..', 'js', 'maps.js'));

let fallas = 0;
function verificar(cond, msg) {
  if (cond) {
    console.log(`OK: ${msg}`);
  } else {
    console.log(`FALLA: ${msg}`);
    fallas++;
  }
}

// Mismo criterio exacto que NPC.tryStep() en js/entities/NPC.js.
function elegirDireccionValida(mapKey, tileX, tileY, homeX, homeY, wanderRadius) {
  const direcciones = [[0, -1, 'up'], [0, 1, 'down'], [-1, 0, 'left'], [1, 0, 'right']];
  const opciones = direcciones.filter(([dx, dy]) => {
    const nx = tileX + dx;
    const ny = tileY + dy;
    if (isTileBlocked(mapKey, nx, ny)) return false;
    if (Math.abs(nx - homeX) > wanderRadius) return false;
    if (Math.abs(ny - homeY) > wanderRadius) return false;
    return true;
  });
  if (opciones.length === 0) return null;
  return opciones[Math.floor(Math.random() * opciones.length)];
}

function simular(mapKey, homeX, homeY, wanderRadius, pasos) {
  let tileX = homeX;
  let tileY = homeY;
  let sinOpciones = 0;
  for (let i = 0; i < pasos; i++) {
    const paso = elegirDireccionValida(mapKey, tileX, tileY, homeX, homeY, wanderRadius);
    if (!paso) { sinOpciones++; continue; }
    const [dx, dy] = paso;
    tileX += dx;
    tileY += dy;
  }
  return { tileX, tileY, sinOpciones };
}

console.log('=== Validando NPCs definidos en data/npc-spawns.json contra los mapas reales ===\n');

const npcSpawns = require(path.join(__dirname, '..', 'data', 'npc-spawns.json'));
const PASOS_SIMULADOS = 500;

for (const [mapKey, spawns] of Object.entries(npcSpawns)) {
  if (mapKey.startsWith('_')) continue; // saltar campos de nota (_nota, etc.)
  if (!MAPS[mapKey]) {
    console.log(`FALLA: data/npc-spawns.json define NPCs para "${mapKey}", que no existe en MAPS`);
    fallas++;
    continue;
  }

  spawns.forEach((s, i) => {
    const etiqueta = `${mapKey}[${i}] home=(${s.homeX},${s.homeY}) radio=${s.wanderRadius}`;

    verificar(
      !isTileBlocked(mapKey, s.homeX, s.homeY),
      `${etiqueta}: el punto de origen es transitable`
    );

    const { tileX, tileY, sinOpciones } = simular(mapKey, s.homeX, s.homeY, s.wanderRadius, PASOS_SIMULADOS);
    const dentroDelRadio = Math.abs(tileX - s.homeX) <= s.wanderRadius && Math.abs(tileY - s.homeY) <= s.wanderRadius;

    verificar(
      dentroDelRadio,
      `${etiqueta}: tras ${PASOS_SIMULADOS} pasos simulados, se mantiene dentro del radio de merodeo (quedó en (${tileX},${tileY}))`
    );
    verificar(
      sinOpciones < PASOS_SIMULADOS,
      `${etiqueta}: encontró al menos una dirección válida en algún momento (quedó sin opciones ${sinOpciones}/${PASOS_SIMULADOS} veces)`
    );
  });
}

console.log('\n' + (fallas === 0 ? 'TODAS LAS PRUEBAS PASARON' : `${fallas} PRUEBA(S) FALLARON`));
process.exit(fallas === 0 ? 0 : 1);
