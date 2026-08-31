/**
 * js/core/battleRules.js
 *
 * Reglas de combate del juego, SIN ninguna dependencia de Phaser (nada de
 * `this.add...`, nada de `Phaser.Math...` adentro de este archivo). Esta
 * es la fuente de verdad de "qué pasa" en una batalla — daño, huida,
 * recuperación tras un desmayo.
 *
 * Estado actual (actualizado tras los ítems 3 y 4 de
 * ROADMAP-ARQUITECTURA.md): ya NO lo carga el navegador — desde que las
 * batallas silvestres pasaron a resolverse en el servidor
 * (api/wild_battle_action.php, ítem 3), ninguna escena de Phaser llama
 * estas funciones directamente. Este archivo sigue existiendo por dos
 * motivos: (1) es la especificación probada de la fórmula que
 * api/config.php (calculate_damage(), faint_recovery_hp()) debe replicar,
 * con scripts/test-battle-rules.js corriéndolo directo con Node como
 * prueba; y (2) queda listo por si algún día se quiere un "preview" de
 * daño estimado en el cliente antes de atacar.
 *
 * Las CONSTANTES de más abajo ya no están hardcodeadas de forma
 * independiente: en contexto Node (el único donde este archivo corre hoy)
 * se leen directo de data/battle-rules.json, la misma fuente que usa
 * api/config.php en el servidor — así scripts/test-battle-rules.js
 * siempre valida contra los números reales, no una copia que se puede
 * desincronizar. Si este archivo alguna vez se vuelve a cargar en un
 * navegador, usa los valores por defecto de abajo (que deberían
 * mantenerse iguales a los del JSON a mano, ya que un navegador no puede
 * leer archivos del disco de forma síncrona).
 */

// Valores por defecto — se sobreescriben con data/battle-rules.json más
// abajo cuando se corre en Node (ver bloque al final del archivo).
let DAMAGE_VARIANCE_MIN = -2;
let DAMAGE_VARIANCE_MAX = 2;
let ESCAPE_CHANCE = 0.9;
let FAINT_RECOVERY_FRACTION = 0.3;

// Generador de enteros por defecto (min y max inclusive). BattleScene.js
// le puede pasar Phaser.Math.Between en su lugar si prefiere el generador
// de Phaser — el resultado es equivalente, esto solo evita que la función
// de más abajo DEPENDA de que exista Phaser para poder ejecutarse.
function defaultIntRange(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * Calcula el daño de un ataque. Fórmula: ataque - defensa/2 + variación
 * aleatoria, con un mínimo de 1 (un ataque nunca hace 0 de daño).
 * `randInt(min, max)` es inyectable para poder probar la función con un
 * resultado determinista (ver scripts/test-battle-rules.js).
 */
function calculateDamage(attacker, defender, randInt = defaultIntRange) {
  const raw = attacker.atk - defender.def / 2;
  const variance = randInt(DAMAGE_VARIANCE_MIN, DAMAGE_VARIANCE_MAX);
  return Math.max(1, Math.round(raw + variance));
}

/**
 * HP resultante de aplicarle daño a un monstruo (nunca baja de 0). No
 * muta el objeto — quien llama decide si guardar el resultado en el
 * mismo monstruo o en una copia.
 */
function applyDamage(monster, amount) {
  return Math.max(0, monster.hp - amount);
}

function isFainted(monster) {
  return monster.hp <= 0;
}

/**
 * Intento de huir de una batalla silvestre. `rand()` debe devolver un
 * número entre 0 y 1 (por defecto Math.random) — inyectable por el mismo
 * motivo que randInt en calculateDamage().
 */
function attemptEscape(rand = Math.random) {
  return rand() < ESCAPE_CHANCE;
}

/**
 * HP con el que un monstruo vuelve tras un desmayo.
 */
function faintRecoveryHp(monster) {
  return Math.ceil(monster.maxHp * FAINT_RECOVERY_FRACTION);
}

// Disponible tanto para <script> normal en el navegador (donde estas
// funciones quedan como globals, igual que el resto del proyecto) como
// para Node (usado solo por scripts/test-battle-rules.js, para poder
// probar las reglas sin necesitar un navegador).
if (typeof module !== 'undefined' && module.exports) {
  // En Node: lee los valores reales desde data/battle-rules.json, la
  // fuente única compartida con PHP (api/config.php) — así el test
  // siempre valida contra los mismos números que usa el servidor de
  // verdad, no una copia que se puede desincronizar (ítem 4 de
  // ROADMAP-ARQUITECTURA.md).
  try {
    const fs = require('fs');
    const path = require('path');
    const raw = fs.readFileSync(path.join(__dirname, '..', '..', 'data', 'battle-rules.json'), 'utf8');
    const reglas = JSON.parse(raw);
    DAMAGE_VARIANCE_MIN = reglas.damageVarianceMin;
    DAMAGE_VARIANCE_MAX = reglas.damageVarianceMax;
    ESCAPE_CHANCE = reglas.escapeChance;
    FAINT_RECOVERY_FRACTION = reglas.faintRecoveryFraction;
  } catch (err) {
    // Si no se puede leer (ej. corriendo en un contexto raro), se quedan
    // los valores por defecto declarados arriba.
  }

  module.exports = {
    DAMAGE_VARIANCE_MIN,
    DAMAGE_VARIANCE_MAX,
    ESCAPE_CHANCE,
    FAINT_RECOVERY_FRACTION,
    calculateDamage,
    applyDamage,
    isFainted,
    attemptEscape,
    faintRecoveryHp,
  };
}
