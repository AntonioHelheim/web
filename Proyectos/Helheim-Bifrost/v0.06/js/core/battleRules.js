/**
 * js/core/battleRules.js
 *
 * Reglas de combate del juego, SIN ninguna dependencia de Phaser (nada de
 * `this.add...`, nada de `Phaser.Math...` adentro de este archivo). Esta
 * es la fuente de verdad de "qué pasa" en una batalla — daño, huida,
 * recuperación tras un desmayo. Las escenas de Phaser (BattleScene.js)
 * solo llaman a estas funciones y dibujan el resultado; no deciden las
 * reglas por su cuenta.
 *
 * Por qué existe este archivo separado (ítem 2 de ROADMAP-ARQUITECTURA.md):
 * así el motor de batalla se puede probar sin abrir un navegador (ver
 * scripts/test-battle-rules.js, que lo corre directo con Node), y el día
 * de mañana que se quiera migrar de motor gráfico o correr estas mismas
 * reglas en un servidor de juego dedicado, no hay que reescribirlas —
 * solo hay que volver a conectarlas desde otro lado.
 *
 * IMPORTANTE — duplicación consciente con el servidor: api/battle_action.php
 * (PHP) calcula el daño de las batallas PvP con esta MISMA fórmula, porque
 * en PvP el servidor tiene que ser la autoridad final (no se puede confiar
 * en que el JS del navegador de nadie fue alterado). Si cambias algo acá,
 * cambia también la fórmula en battle_action.php — hasta que el ítem 4 del
 * roadmap (catálogo/reglas en una sola fuente de datos) unifique esto.
 * Las batallas silvestres (BattleScene.js) SÍ usan este archivo
 * directamente, porque ítem 3 del roadmap (volverlas autoritativas en el
 * servidor) todavía está pendiente.
 */

// Rango de variación aleatoria del daño (mismo valor que usa la fórmula
// espejo en api/battle_action.php).
const DAMAGE_VARIANCE_MIN = -2;
const DAMAGE_VARIANCE_MAX = 2;

// Probabilidad de escapar con éxito de una batalla silvestre al intentar
// huir (no aplica a PvP — ahí "huir" siempre funciona, ver battle_action.php).
const ESCAPE_CHANCE = 0.9;

// Fracción del HP máximo con la que un monstruo se recupera tras un
// desmayo (estilo clásico: no se queda en 0 HP, vuelve con un poco).
const FAINT_RECOVERY_FRACTION = 0.3;

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
