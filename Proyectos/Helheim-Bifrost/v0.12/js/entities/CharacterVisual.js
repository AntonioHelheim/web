// Cambio de jugabilidad (31-08-2026): el jugador ya no elige colores
// libremente — elige una de 3 opciones preestablecidas por género (ver
// data/graphics-catalog.json → characters.people). Mientras no tengamos
// cargados los sprites reales (Characters/people/{gender}/00N.png), cada
// opción se sigue viendo como esta combinación de colores dibujados a
// mano; el día que carguen esos archivos, esto se reemplaza por el
// sprite real sin tener que tocar de nuevo la base de datos ni el flujo
// de creación de personaje — el dato guardado siempre fue "género +
// número de opción (1-3)", nunca colores libres. Espejo exacto en PHP:
// resolve_appearance_preset() en api/config.php — si cambias esto,
// cambia eso también.
const APPEARANCE_PRESETS = {
  boy: [
    { skinColor: '#f1c27d', hairColor: '#2c1b18', eyeColor: '#3b2415' },
    { skinColor: '#c68642', hairColor: '#4a2c1a', eyeColor: '#5b4636' },
    { skinColor: '#8d5524', hairColor: '#1a1a1a', eyeColor: '#2f6b3a' },
  ],
  girl: [
    { skinColor: '#f1c27d', hairColor: '#2c1b18', eyeColor: '#3b2415' },
    { skinColor: '#f1c27d', hairColor: '#e8c268', eyeColor: '#274b8f' },
    { skinColor: '#8d5524', hairColor: '#1a1a1a', eyeColor: '#2f6b3a' },
  ],
};

// === Sprites reales de personaje (ver PLAN-GRAPHICS-AUDIO.md) ===
//
// El valor interno de género ('boy'/'girl', usado en toda la BD y el
// código) no es el mismo texto que el nombre de carpeta real de los
// assets (Characters/people/male|female/00N.png) — esta función traduce
// entre ambos, en un solo lugar.
function spriteFolderForGender(gender) {
  return gender === 'girl' ? 'female' : 'male';
}

// Clave de textura de Phaser para una apariencia dada. Coincide con las
// claves usadas en data/graphics-catalog.json (people_{carpeta}_{preset}).
function spriteKeyForAppearance(appearance) {
  const folder = spriteFolderForGender(appearance.gender);
  const preset = appearance.preset || 1;
  return `people_${folder}_${preset}`;
}

// Fila 0=down, 1=left, 2=right, 3=up (ver data/graphics-catalog.json →
// _convencion_grilla_personajes) — primer cuadro de cada fila = pose de
// reposo mirando hacia esa dirección.
const SPRITE_DIRECTION_ROW = { down: 0, left: 1, right: 2, up: 3 };
function idleFrameForDirection(direction) {
  return SPRITE_DIRECTION_ROW[direction] * 4;
}

// Registra las 4 animaciones de caminata (una por dirección) de un
// spritesheet de personaje ya cargado — se llama una vez por textura
// desde PreloadScene.js. No hace nada si la textura no existe (para
// poder llamarla sin miedo aunque ese sprite todavía no se haya subido)
// ni si las animaciones ya estaban creadas (evita errores si la escena
// se reinicia).
function defineCharacterAnimations(scene, spriteKey) {
  if (!scene.textures.exists(spriteKey)) return;
  Object.entries(SPRITE_DIRECTION_ROW).forEach(([direction, row]) => {
    const animKey = `${spriteKey}_${direction}`;
    if (scene.anims.exists(animKey)) return;
    scene.anims.create({
      key: animKey,
      frames: scene.anims.generateFrameNumbers(spriteKey, { start: row * 4, end: row * 4 + 3 }),
      frameRate: 8,
      repeat: -1,
    });
  });
}

// Construye la figura del personaje con formas de Phaser (nada de sprites
// con derechos de autor). Proporción "chibi" (cabeza grande, cuerpo
// pequeño), como los sprites clásicos de overworld de Game Boy. El género
// define la silueta base (piernas vs. vestido acampanado — un recurso
// genérico usado en incontables juegos originales) y tres colores
// personalizables: piel, pelo y ojos. La ropa usa colores fijos de tono
// tierra/vino para no depender de ninguna paleta de personaje existente.
//
// Piernas, brazos y ojos se devuelven como sub-contenedores propios
// (container.legLeft, .armLeft, .eyeLeft, etc.) para que Player.js pueda
// animarlos/ocultarlos sin reconstruir todo el personaje.
const OUTFIT_BOY = 0x6b5030;
const OUTFIT_BOY_SHADE = 0x4a3720;
const OUTFIT_GIRL = 0x9c4f61;
const OUTFIT_GIRL_SHADE = 0x6e3745;
const SHOE_COLOR = 0x3b2513;
const BLUSH_COLOR = 0xe8848c;
const MOUTH_COLOR = 0x8a4a3a;

function buildCharacterVisual(scene, tileSize, appearance) {
  const isGirl = appearance.gender === 'girl';
  const skin = Phaser.Display.Color.HexStringToColor(appearance.skinColor).color;
  const hair = Phaser.Display.Color.HexStringToColor(appearance.hairColor).color;
  const eyes = Phaser.Display.Color.HexStringToColor(appearance.eyeColor).color;
  const outfit = isGirl ? OUTFIT_GIRL : OUTFIT_BOY;
  const outfitShade = isGirl ? OUTFIT_GIRL_SHADE : OUTFIT_BOY_SHADE;
  const parts = [];

  // 1. Sombra de contacto en el piso: da sensación de volumen sin necesitar gradientes.
  parts.push(scene.add.ellipse(0, tileSize * 0.44, tileSize * 0.46, tileSize * 0.1, 0x000000).setAlpha(0.18));

  // 2. Piernas: cada una es su propio mini-contenedor (pierna + zapato)
  // para poder animarlas de forma independiente sin desincronizar el
  // zapato de la pierna.
  let legLeft;
  let legRight;
  if (isGirl) {
    legLeft = scene.add.container(-tileSize * 0.08, tileSize * 0.38, [
      scene.add.rectangle(0, 0, tileSize * 0.07, tileSize * 0.12, skin),
      scene.add.ellipse(0, tileSize * 0.08, tileSize * 0.12, tileSize * 0.06, SHOE_COLOR),
    ]);
    legRight = scene.add.container(tileSize * 0.08, tileSize * 0.38, [
      scene.add.rectangle(0, 0, tileSize * 0.07, tileSize * 0.12, skin),
      scene.add.ellipse(0, tileSize * 0.08, tileSize * 0.12, tileSize * 0.06, SHOE_COLOR),
    ]);
  } else {
    legLeft = scene.add.container(-tileSize * 0.09, tileSize * 0.36, [
      scene.add.rectangle(0, 0, tileSize * 0.13, tileSize * 0.22, outfitShade),
      scene.add.ellipse(0, tileSize * 0.1, tileSize * 0.15, tileSize * 0.07, SHOE_COLOR),
    ]);
    legRight = scene.add.container(tileSize * 0.09, tileSize * 0.36, [
      scene.add.rectangle(0, 0, tileSize * 0.13, tileSize * 0.22, outfitShade),
      scene.add.ellipse(0, tileSize * 0.1, tileSize * 0.15, tileSize * 0.07, SHOE_COLOR),
    ]);
  }
  parts.push(legLeft, legRight);

  // 3. Cuerpo, con un detalle de ropa distinto por género y una sombra
  // lateral sutil (semitransparente, funciona con cualquier color elegido)
  // para que no se vea plano.
  if (isGirl) {
    // Vestido: silueta más alta que ancha (torso angosto arriba + falda
    // ancha abajo, tipo "A"), en vez de dos óvalos casi circulares
    // superpuestos que se veían como una sola masa redonda.
    parts.push(scene.add.ellipse(0, tileSize * 0.28, tileSize * 0.46, tileSize * 0.22, outfitShade)); // falda
    parts.push(scene.add.ellipse(0, tileSize * 0.1, tileSize * 0.24, tileSize * 0.3, outfit)); // torso/bodice
    parts.push(scene.add.ellipse(tileSize * 0.07, tileSize * 0.2, tileSize * 0.15, tileSize * 0.22, 0x000000).setAlpha(0.1));
    // Cinturón marcando la cintura, justo donde el torso pasa a falda.
    parts.push(scene.add.rectangle(0, tileSize * 0.2, tileSize * 0.2, tileSize * 0.03, outfitShade));
  } else {
    // Torso: más ancho que alto, para leerse como hombros anchos y no
    // como un óvalo redondo.
    parts.push(scene.add.ellipse(0, tileSize * 0.15, tileSize * 0.44, tileSize * 0.3, outfit));
    parts.push(scene.add.ellipse(tileSize * 0.1, tileSize * 0.15, tileSize * 0.2, tileSize * 0.25, 0x000000).setAlpha(0.1));
    // Cinturón.
    parts.push(scene.add.rectangle(0, tileSize * 0.3, tileSize * 0.4, tileSize * 0.035, SHOE_COLOR));
    // Cuello en V (solapa), un pequeño triángulo más oscuro en el pecho.
    parts.push(scene.add.triangle(
      0, tileSize * 0.02,
      -tileSize * 0.07, -tileSize * 0.04,
      tileSize * 0.07, -tileSize * 0.04,
      0, tileSize * 0.08,
      outfitShade
    ));
  }

  // 4. Brazos: mini-contenedores (igual que las piernas) para poder
  // balancearlos al caminar, en sentido contrario a las piernas. Incluyen
  // un pequeño puño de manga (tono más oscuro) para que se note que es
  // ropa y no solo un círculo pegado al cuerpo.
  const armLeft = scene.add.container(-tileSize * 0.22, tileSize * 0.1, [
    scene.add.circle(0, 0, tileSize * 0.09, outfit),
    scene.add.ellipse(0, tileSize * 0.07, tileSize * 0.14, tileSize * 0.05, outfitShade),
  ]);
  const armRight = scene.add.container(tileSize * 0.22, tileSize * 0.1, [
    scene.add.circle(0, 0, tileSize * 0.09, outfit),
    scene.add.ellipse(0, tileSize * 0.07, tileSize * 0.14, tileSize * 0.05, outfitShade),
  ]);
  parts.push(armLeft, armRight);

  // 5. Manos: pequeños círculos en tono piel al final de cada brazo.
  parts.push(scene.add.circle(-tileSize * 0.22, tileSize * 0.19, tileSize * 0.045, skin));
  parts.push(scene.add.circle(tileSize * 0.22, tileSize * 0.19, tileSize * 0.045, skin));

  // 6. Cuello: conecta la cabeza con el cuerpo (antes la cabeza quedaba
  // flotando directamente sobre el torso).
  parts.push(scene.add.rectangle(0, -tileSize * 0.05, tileSize * 0.1, tileSize * 0.09, skin));

  // 7. Pelo largo detrás de la cabeza (solo chica; se asoma por los lados y hombros).
  let longHair = null;
  if (isGirl) {
    longHair = scene.add.ellipse(0, -tileSize * 0.16, tileSize * 0.58, tileSize * 0.54, hair);
    parts.push(longHair);
  }

  // 8. Cabeza grande, proporción "chibi".
  parts.push(scene.add.circle(0, -tileSize * 0.26, tileSize * 0.22, skin));

  // 9. Mejillas sonrosadas: un toque de calidez muy simple pero efectivo.
  parts.push(scene.add.circle(-tileSize * 0.13, -tileSize * 0.19, tileSize * 0.045, BLUSH_COLOR).setAlpha(0.35));
  parts.push(scene.add.circle(tileSize * 0.13, -tileSize * 0.19, tileSize * 0.045, BLUSH_COLOR).setAlpha(0.35));

  // 10. Pelo corto / flequillo, con sombra de volumen y un brillo suave
  // encima para dar textura (ambos semitransparentes: funcionan con
  // cualquier color de pelo elegido, sin tener que calcular tonos).
  const fringeWidth = isGirl ? tileSize * 0.5 : tileSize * 0.44;
  parts.push(scene.add.ellipse(0, -tileSize * 0.4, fringeWidth, tileSize * 0.25, hair));
  parts.push(scene.add.ellipse(tileSize * 0.09, -tileSize * 0.38, fringeWidth * 0.45, tileSize * 0.18, 0x000000).setAlpha(0.13));
  parts.push(scene.add.ellipse(-tileSize * 0.1, -tileSize * 0.44, fringeWidth * 0.3, tileSize * 0.07, 0xffffff).setAlpha(0.18));

  // 11. Cejas: dan expresión; se ocultan junto con el ojo de su mismo lado.
  const browLeft = scene.add.rectangle(-tileSize * 0.075, -tileSize * 0.305, tileSize * 0.075, tileSize * 0.02, hair);
  const browRight = scene.add.rectangle(tileSize * 0.075, -tileSize * 0.305, tileSize * 0.075, tileSize * 0.02, hair);
  parts.push(browLeft, browRight);

  // 12. Ojos: 4 capas (esclerótica blanca, iris del color elegido, pupila,
  // brillo) en vez de un óvalo plano — mucho más expresivos. Todo agrupado
  // en su propio contenedor para que ocultar el ojo oculte todas sus capas.
  const eyeLeft = scene.add.container(-tileSize * 0.075, -tileSize * 0.24, [
    scene.add.ellipse(0, 0, tileSize * 0.055, tileSize * 0.07, 0xffffff),
    scene.add.circle(0, tileSize * 0.006, tileSize * 0.024, eyes),
    scene.add.circle(0, tileSize * 0.01, tileSize * 0.012, 0x1a1a1a),
    scene.add.circle(-tileSize * 0.01, -tileSize * 0.014, tileSize * 0.009, 0xffffff),
  ]);
  const eyeRight = scene.add.container(tileSize * 0.075, -tileSize * 0.24, [
    scene.add.ellipse(0, 0, tileSize * 0.055, tileSize * 0.07, 0xffffff),
    scene.add.circle(0, tileSize * 0.006, tileSize * 0.024, eyes),
    scene.add.circle(0, tileSize * 0.01, tileSize * 0.012, 0x1a1a1a),
    scene.add.circle(tileSize * 0.011, -tileSize * 0.014, tileSize * 0.009, 0xffffff),
  ]);
  parts.push(eyeLeft, eyeRight);

  // 13. Boca: una pequeña sonrisa.
  const mouth = scene.add.ellipse(0, -tileSize * 0.16, tileSize * 0.07, tileSize * 0.025, MOUTH_COLOR).setAlpha(0.75);
  parts.push(mouth);

  // 14. Brillo suave en la cabeza para dar sensación de volumen.
  parts.push(scene.add.circle(-tileSize * 0.09, -tileSize * 0.32, tileSize * 0.06, 0xffffff).setAlpha(0.22));

  // 15. Lazo en el pelo (solo chica): un accesorio simple con dos
  // triángulos y un nudo central.
  let bow = null;
  if (isGirl) {
    bow = scene.add.container(tileSize * 0.17, -tileSize * 0.44, [
      scene.add.triangle(-tileSize * 0.045, 0, 0, -tileSize * 0.045, 0, tileSize * 0.045, -tileSize * 0.09, 0, hair),
      scene.add.triangle(tileSize * 0.045, 0, 0, -tileSize * 0.045, 0, tileSize * 0.045, tileSize * 0.09, 0, hair),
      scene.add.circle(0, 0, tileSize * 0.025, outfitShade),
    ]);
    parts.push(bow);
  }

  // 16. Nuca: círculo del color del pelo que tapa toda la cabeza (incluida
  // la cara, que queda debajo). Oculto normalmente; solo se muestra cuando
  // el personaje camina hacia arriba (de espaldas a la cámara).
  const backOfHead = scene.add.circle(0, -tileSize * 0.26, tileSize * 0.225, hair).setVisible(false);
  parts.push(backOfHead);

  const container = scene.add.container(0, 0, parts);
  container.legLeft = legLeft;
  container.legRight = legRight;
  container.armLeft = armLeft;
  container.armRight = armRight;
  container.eyeLeft = eyeLeft;
  container.eyeRight = eyeRight;
  container.browLeft = browLeft;
  container.browRight = browRight;
  container.mouth = mouth;
  container.backOfHead = backOfHead;
  container.longHair = longHair;
  container.bow = bow;
  return container;
}

// Ajusta la cara del personaje según hacia dónde mira: de frente se ven
// ambos ojos/cejas/boca, de espaldas (hacia arriba) se tapa toda la cara
// con el color del pelo, y de perfil (izquierda/derecha) se oculta el ojo
// y la ceja del lado contrario, sugiriendo un giro de cabeza. Se usa tanto
// para el jugador local (Player.js) como para los demás jugadores en el
// mapa compartido.
function applyFacingToVisual(visual, facing) {
  if (!visual || !visual.eyeLeft || !visual.eyeRight || !visual.backOfHead) return;
  const showFace = facing !== 'up';
  visual.backOfHead.setVisible(!showFace);
  if (visual.mouth) visual.mouth.setVisible(showFace);
  visual.eyeLeft.setVisible(showFace && facing !== 'right');
  visual.eyeRight.setVisible(showFace && facing !== 'left');
  if (visual.browLeft) visual.browLeft.setVisible(showFace && facing !== 'right');
  if (visual.browRight) visual.browRight.setVisible(showFace && facing !== 'left');
}
