/**
 * scripts/test-preload-regression.js
 *
 * Test de regresión para un bug real encontrado el 01-09-2026: si el
 * mapa guardado (`data.mapKey`) no coincidía con ningún mapa conocido,
 * TODA la restauración de la partida se saltaba — incluyendo
 * characterCreated y apariencia — y el jugador volvía a "personaje
 * nuevo" aunque estuviera perfectamente guardado en la base de datos.
 * Se corrigió desacoplando: un mapa inválido solo afecta la POSICIÓN
 * (cae a overworld), characterCreated/apariencia/equipo/inventario se
 * restauran siempre que el servidor haya respondido bien.
 *
 * Esta prueba vivía como script suelto en /tmp durante esa sesión — se
 * formaliza acá para que una futura edición de PreloadScene.js no
 * pueda reintroducir el mismo bug sin que npm test lo note.
 *
 * Uso: node scripts/test-preload-regression.js
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

global.window = { BIFROST_ASSET_VERSION: 'test' };
global.MAPS = { overworld: { spawn: { x: 6, y: 13 } } };
global.SPECIES = {};
global.NPC_SPAWNS = {};
global.PEOPLE_SPRITE_COMBOS = [];
global.validateCharacterSpritesheet = () => false;
global.defineCharacterAnimations = () => {};
global.decideNextScene = (data) => (!data.characterCreated ? 'CharacterCreationScene' : 'OverworldScene');

function crearEscenaMock(respuestaFetch) {
  global.fetch = async () => ({
    ok: respuestaFetch.ok,
    status: respuestaFetch.status || 200,
    json: async () => respuestaFetch.body,
  });
  let resultado = null;
  return {
    load: { json() {}, spritesheet() {}, image() {} },
    cache: { json: { get: () => ({}) } },
    anims: { exists: () => false, create() {}, generateFrameNumbers: () => ({}) },
    textures: { exists: () => false },
    scene: { start: (key, data) => { resultado = { key, data }; } },
    _getResultado: () => resultado,
  };
}

const srcPath = path.join(__dirname, '..', 'js', 'scenes', 'PreloadScene.js');
const src = fs.readFileSync(srcPath, 'utf8')
  .replace('class PreloadScene extends Phaser.Scene {', 'class PreloadScene {')
  .replace("super('PreloadScene');", '');
// eslint-disable-next-line no-eval
eval(`${src}\nglobal.PreloadScene = PreloadScene;`);

async function correr() {
  console.log('=== Escenario del bug real: personaje SÍ creado, pero el mapa guardado ya no existe ===');
  const escena1 = crearEscenaMock({
    ok: true,
    body: {
      ok: true, mapKey: 'un_mapa_que_ya_no_existe', x: 5, y: 5,
      party: [{ name: 'Test' }], inventory: { runa_captura: 3 },
      characterCreated: true, appearance: { gender: 'girl', preset: 2 },
    },
  });
  const p1 = new PreloadScene();
  Object.assign(p1, escena1);
  await p1.create();
  const r1 = escena1._getResultado();
  verificar(r1.key === 'OverworldScene', 'con el arreglo: va directo a OverworldScene, NO vuelve a pedir crear personaje');
  verificar(r1.data.mapKey === 'overworld', 'la posición cae a overworld de forma segura');
  verificar(r1.data.appearance.gender === 'girl' && r1.data.appearance.preset === 2, 'la apariencia guardada se preserva íntegra');
  verificar(r1.data.party.length === 1, 'el equipo guardado se preserva');
  verificar(r1.data.characterCreated === true, 'characterCreated se preserva en true');

  console.log('\n=== Caso de control: todo válido (no debe romperse con el arreglo) ===');
  const escena2 = crearEscenaMock({
    ok: true,
    body: {
      ok: true, mapKey: 'overworld', x: 10, y: 10, party: [], inventory: { runa_captura: 5 },
      characterCreated: true, appearance: { gender: 'boy', preset: 1 },
    },
  });
  const p2 = new PreloadScene();
  Object.assign(p2, escena2);
  await p2.create();
  verificar(escena2._getResultado().key === 'OverworldScene', 'caso normal sigue yendo a OverworldScene');

  console.log('\n=== Caso de control: personaje realmente nuevo (debe seguir pidiendo crear personaje) ===');
  const escena3 = crearEscenaMock({
    ok: true,
    body: {
      ok: true, mapKey: 'overworld', x: 6, y: 13, party: [], inventory: { runa_captura: 5 },
      characterCreated: false, appearance: { gender: 'boy', preset: 1 },
    },
  });
  const p3 = new PreloadScene();
  Object.assign(p3, escena3);
  await p3.create();
  verificar(escena3._getResultado().key === 'CharacterCreationScene', 'personaje realmente nuevo sigue pidiendo crear personaje (no es un falso positivo)');

  console.log('\n=== Caso de control: servidor no responde (500) ===');
  const escena4 = crearEscenaMock({ ok: false, status: 500, body: { error: 'Error de base de datos' } });
  const p4 = new PreloadScene();
  Object.assign(p4, escena4);
  await p4.create();
  verificar(escena4._getResultado().key === 'CharacterCreationScene', 'si el servidor responde 500, arranca con valores por defecto (pide crear personaje) sin reventar');

  console.log(`\n${fallas === 0 ? 'TODAS LAS PRUEBAS PASARON' : `${fallas} PRUEBA(S) FALLARON`}`);
  process.exit(fallas === 0 ? 0 : 1);
}

correr();
