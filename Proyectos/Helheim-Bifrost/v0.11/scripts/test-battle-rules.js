/**
 * scripts/test-battle-rules.js
 *
 * Corre las reglas de combate (js/core/battleRules.js) directo con Node,
 * sin abrir un navegador y sin que exista Phaser en ningún lado — la
 * prueba concreta de que la separación núcleo/motor gráfico (ítem 2 de
 * ROADMAP-ARQUITECTURA.md) realmente funciona.
 *
 * Uso: node scripts/test-battle-rules.js
 * Sale con código 0 si todo pasa, 1 si algo falla (para poder engancharlo
 * a un pipeline de CI más adelante, ítem 10 del roadmap).
 */
const path = require('path');
const {
  calculateDamage,
  applyDamage,
  isFainted,
  attemptEscape,
  faintRecoveryHp,
  ESCAPE_CHANCE,
} = require(path.join(__dirname, '..', 'js', 'core', 'battleRules.js'));

let fallas = 0;

function assertEqual(actual, expected, mensaje) {
  if (actual !== expected) {
    console.error(`FALLA: ${mensaje} — esperaba ${expected}, salió ${actual}`);
    fallas++;
  } else {
    console.log(`OK: ${mensaje}`);
  }
}

function assertTrue(condicion, mensaje) {
  assertEqual(condicion, true, mensaje);
}

// --- calculateDamage: fórmula ataque - defensa/2 + variación, con randInt inyectado ---
const atacante = { atk: 20, def: 10 };
const defensor = { atk: 5, def: 10 };

assertEqual(
  calculateDamage(atacante, defensor, () => 0),
  15, // 20 - 10/2 + 0 = 15
  'calculateDamage sin variación da atk - def/2'
);
assertEqual(
  calculateDamage(atacante, defensor, () => 2),
  17,
  'calculateDamage con variación +2'
);
assertEqual(
  calculateDamage(atacante, defensor, () => -2),
  13,
  'calculateDamage con variación -2'
);

// --- Mínimo 1 de daño, incluso si el defensor es mucho más fuerte ---
const atacanteDebil = { atk: 1, def: 1 };
const defensorFuerte = { atk: 1, def: 100 };
assertEqual(
  calculateDamage(atacanteDebil, defensorFuerte, () => -2),
  1,
  'calculateDamage nunca baja de 1 de daño'
);

// --- applyDamage: nunca baja de 0 ---
assertEqual(applyDamage({ hp: 10 }, 3), 7, 'applyDamage resta el daño normalmente');
assertEqual(applyDamage({ hp: 5 }, 999), 0, 'applyDamage nunca deja HP negativo');

// --- isFainted ---
assertTrue(isFainted({ hp: 0 }), 'isFainted true con 0 HP');
assertTrue(!isFainted({ hp: 1 }), 'isFainted false con HP > 0');

// --- attemptEscape: respeta el rand inyectado contra ESCAPE_CHANCE ---
assertTrue(attemptEscape(() => 0), 'attemptEscape con rand=0 siempre escapa');
assertTrue(!attemptEscape(() => 0.999), 'attemptEscape con rand=0.999 no escapa (por encima de ESCAPE_CHANCE)');
assertEqual(ESCAPE_CHANCE, 0.9, 'ESCAPE_CHANCE sigue en 0.9 (ojo si cambia, hay que avisar)');

// --- faintRecoveryHp: redondeado hacia arriba, 30% del HP máximo ---
assertEqual(faintRecoveryHp({ maxHp: 30 }), 9, 'faintRecoveryHp: 30% de 30 = 9');
assertEqual(faintRecoveryHp({ maxHp: 22 }), 7, 'faintRecoveryHp: 30% de 22 = 6.6 -> redondea a 7');

console.log(`\n${fallas === 0 ? 'TODAS LAS PRUEBAS PASARON' : `${fallas} PRUEBA(S) FALLARON`}`);
process.exit(fallas === 0 ? 0 : 1);
