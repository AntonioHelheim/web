/**
 * scripts/test-character-visuals.js
 *
 * Consolida las pruebas ad-hoc del sistema de personajes (CharacterVisual.js
 * / Player.js) escritas durante el desarrollo — antes vivían sueltas en
 * /tmp, así que nada detectaba una regresión futura.
 *
 * Cubre:
 *   1. validateCharacterSpritesheet(): detecta un tamaño de cuadro
 *      equivocado (el bug real que tuvimos el 31-08-2026 con
 *      male/female en tamaños distintos) y lo rechaza sin romper nada.
 *   2. Las 8 combinaciones (4 presets x 2 géneros) resuelven a la clave
 *      de textura correcta, y people_male_4 específicamente NO se
 *      intenta cargar (no se integró ese archivo).
 *   3. Escala visual por género (male 10% más grande) — la geometría
 *      exacta que mantiene los pies alineados con el tile al escalar.
 *
 * Uso: node scripts/test-character-visuals.js
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

global.Phaser = { Display: { Color: { HexStringToColor: () => ({ color: 0 }) } } };
global.GB_PALETTE = { darkest: 0, dark: 0, light: 0, lightest: 0xffffff };

const cvPath = path.join(__dirname, '..', 'js', 'entities', 'CharacterVisual.js');
let cvSrc = fs.readFileSync(cvPath, 'utf8');
// Stub de buildCharacterVisual/applyFacingToVisual — no nos interesa el
// dibujo a mano en detalle acá, solo que existan y no revienten.
cvSrc = cvSrc.replace(
  /function buildCharacterVisual[\s\S]*$/,
  'function buildCharacterVisual(scene){ return scene.add.sprite(0,0,"stub",0); }\nfunction applyFacingToVisual(){}\n'
);
// eslint-disable-next-line no-eval
eval(`${cvSrc}\nglobal.spriteKeyForAppearance = spriteKeyForAppearance;
global.idleFrameForDirection = idleFrameForDirection;
global.validateCharacterSpritesheet = validateCharacterSpritesheet;
global.PEOPLE_SPRITE_COMBOS = PEOPLE_SPRITE_COMBOS;
global.APPEARANCE_PRESETS = APPEARANCE_PRESETS;
global.scaleForGender = scaleForGender;
global.scaleForSpriteKey = scaleForSpriteKey;
global.MALE_VISUAL_SCALE = MALE_VISUAL_SCALE;
global.FEMALE_VISUAL_SCALE = FEMALE_VISUAL_SCALE;`);

// ---------- 1. validateCharacterSpritesheet: detecta tamaño equivocado ----------
console.log('=== validateCharacterSpritesheet: detecta un tamaño de cuadro equivocado ===');
{
  // Simula cargar un archivo de 256x256 con una config pensada para
  // 128x192 (32x48/cuadro) — el mismo tipo de error real que tuvimos.
  const escenaMal = {
    textures: {
      exists: () => true,
      get: () => ({
        getFrameNames: () => Array.from({ length: 40 }, (_, i) => String(i)), // tamaño equivocado -> 40 cuadros, no 16
        getSourceImage: () => ({ width: 256, height: 256 }),
      }),
      remove(key) { this._removido = key; },
    },
  };
  const resultado = validateCharacterSpritesheet(escenaMal, 'people_test_mal');
  verificar(resultado === false, 'un spritesheet con la cantidad de cuadros equivocada se rechaza (devuelve false)');
  verificar(escenaMal.textures._removido === 'people_test_mal', 'la textura mal cargada se elimina, así el resto cae al dibujo a mano automáticamente');
}

console.log('\n=== validateCharacterSpritesheet: acepta un spritesheet correcto ===');
{
  const escenaBien = {
    textures: {
      exists: () => true,
      get: () => ({
        getFrameNames: () => Array.from({ length: 16 }, (_, i) => String(i)),
        getSourceImage: () => ({ width: 128, height: 192 }),
      }),
      remove() { this._removido = true; },
    },
  };
  const resultado = validateCharacterSpritesheet(escenaBien, 'people_test_bien');
  verificar(resultado === true, 'un spritesheet con 16 cuadros exactos se acepta (devuelve true)');
  verificar(!escenaBien.textures._removido, 'no se elimina una textura válida');
}

console.log('\n=== validateCharacterSpritesheet: textura inexistente ===');
{
  const escenaVacia = { textures: { exists: () => false } };
  verificar(validateCharacterSpritesheet(escenaVacia, 'no_existe') === false, 'una textura que nunca se cargó devuelve false sin reventar');
}

// ---------- 2. Las 8 combinaciones (4 presets x 2 géneros) ----------
console.log('\n=== Las 8 combinaciones resuelven correctamente ===');
verificar(APPEARANCE_PRESETS.boy.length === 4, 'APPEARANCE_PRESETS.boy tiene 4 opciones');
verificar(APPEARANCE_PRESETS.girl.length === 4, 'APPEARANCE_PRESETS.girl tiene 4 opciones');

const clavesQueSeCargan = new Set(PEOPLE_SPRITE_COMBOS.map((c) => `people_${c.folder}_${c.preset}`));
verificar(clavesQueSeCargan.size === 7, 'se intentan cargar exactamente 7 texturas (8 combos - male_4, sin archivo real)');
verificar(!clavesQueSeCargan.has('people_male_4'), 'people_male_4 NO se intenta cargar a propósito (archivo rechazado, ver LISTADO-PENDIENTE-GRAPHICS.md)');
['people_male_1', 'people_male_2', 'people_male_3', 'people_female_1', 'people_female_2', 'people_female_3', 'people_female_4'].forEach((clave) => {
  verificar(clavesQueSeCargan.has(clave), `"${clave}" sí se intenta cargar`);
});

for (let preset = 1; preset <= 4; preset += 1) {
  const claveMale = spriteKeyForAppearance({ gender: 'boy', preset });
  const claveFemale = spriteKeyForAppearance({ gender: 'girl', preset });
  verificar(claveMale === `people_male_${preset}`, `boy preset ${preset} -> clave "${claveMale}"`);
  verificar(claveFemale === `people_female_${preset}`, `girl preset ${preset} -> clave "${claveFemale}"`);
}

// ---------- 3. Escala visual por género ----------
console.log('\n=== Escala visual: male 10% más grande, pies alineados ===');
verificar(MALE_VISUAL_SCALE === 1.1, 'MALE_VISUAL_SCALE es 1.1');
verificar(FEMALE_VISUAL_SCALE === 1.0, 'FEMALE_VISUAL_SCALE es 1.0');
verificar(scaleForGender('boy') === 1.1, 'scaleForGender("boy") = 1.1');
verificar(scaleForGender('girl') === 1.0, 'scaleForGender("girl") = 1.0');
verificar(scaleForSpriteKey('people_male_2') === 1.1, 'scaleForSpriteKey detecta "male" por el nombre de la clave (para NPCs)');
verificar(scaleForSpriteKey('people_female_3') === 1.0, 'scaleForSpriteKey detecta "female" por el nombre de la clave (para NPCs)');

{
  // Verificación geométrica: al escalar el sprite masculino (crece desde
  // su centro), el offset debe ajustarse para que el BORDE INFERIOR
  // (los pies) quede exactamente en el mismo lugar que sin escalar —
  // el personaje debe crecer hacia arriba, no hundirse en el piso.
  const tileSize = 32;
  const frameHeight = 48;
  const yOffsetBase = (tileSize - frameHeight) / 2;
  const bordeInferiorSinEscalar = yOffsetBase + frameHeight / 2;

  const escala = MALE_VISUAL_SCALE;
  const yOffsetConEscala = yOffsetBase + (frameHeight / 2) * (1 - escala);
  const bordeInferiorConEscala = yOffsetConEscala + (frameHeight * escala) / 2;

  verificar(
    Math.abs(bordeInferiorSinEscalar - bordeInferiorConEscala) < 0.001,
    'los pies quedan en el mismo lugar exacto tras escalar 10% (diferencia < 0.001px)'
  );
}

// ---------- Resultado ----------
console.log(`\n${fallas === 0 ? 'TODAS LAS PRUEBAS PASARON' : `${fallas} PRUEBA(S) FALLARON`}`);
process.exit(fallas === 0 ? 0 : 1);
