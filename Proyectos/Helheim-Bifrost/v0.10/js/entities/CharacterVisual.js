// Construye la figura del personaje con formas de Phaser (nada de sprites
// con derechos de autor). Proporción "chibi" (cabeza grande, cuerpo
// pequeño), como los sprites clásicos de overworld de Game Boy. El género
// define la silueta base (piernas vs. vestido acampanado — un recurso
// genérico usado en incontables juegos originales) y tres colores
// personalizables: piel, pelo y ojos. La ropa usa un color fijo de tono
// tierra/vino para no depender de ninguna paleta de personaje existente.
//
// Las piernas se devuelven como sub-contenedores propios (container.legLeft
// / container.legRight) para que Player.js pueda animarlas al caminar
// (efecto de tijera) sin tener que reconstruir todo el personaje.
const OUTFIT_BOY = 0x6b5030;
const OUTFIT_BOY_SHADE = 0x4a3720;
const OUTFIT_GIRL = 0x9c4f61;
const OUTFIT_GIRL_SHADE = 0x6e3745;

function buildCharacterVisual(scene, tileSize, appearance) {
  const isGirl = appearance.gender === 'girl';
  const skin = Phaser.Display.Color.HexStringToColor(appearance.skinColor).color;
  const hair = Phaser.Display.Color.HexStringToColor(appearance.hairColor).color;
  const eyes = Phaser.Display.Color.HexStringToColor(appearance.eyeColor).color;
  const outfit = isGirl ? OUTFIT_GIRL : OUTFIT_BOY;
  const outfitShade = isGirl ? OUTFIT_GIRL_SHADE : OUTFIT_BOY_SHADE;
  const parts = [];

  // Sombra de contacto en el piso: da sensación de volumen sin necesitar gradientes.
  parts.push(scene.add.ellipse(0, tileSize * 0.42, tileSize * 0.46, tileSize * 0.1, 0x000000).setAlpha(0.18));

  // Piernas: cada una es su propio mini-contenedor (pierna + zapato, si
  // aplica) posicionado en el mundo, para poder animarlas de forma
  // independiente sin desincronizar el zapato de la pierna.
  let legLeft;
  let legRight;
  if (isGirl) {
    legLeft = scene.add.container(-tileSize * 0.07, tileSize * 0.38, [
      scene.add.rectangle(0, 0, tileSize * 0.08, tileSize * 0.1, skin),
    ]);
    legRight = scene.add.container(tileSize * 0.07, tileSize * 0.38, [
      scene.add.rectangle(0, 0, tileSize * 0.08, tileSize * 0.1, skin),
    ]);
    parts.push(legLeft, legRight);
    // Vestido acampanado: dos elipses superpuestas para una silueta curva.
    parts.push(scene.add.ellipse(0, tileSize * 0.24, tileSize * 0.5, tileSize * 0.3, outfitShade));
    parts.push(scene.add.ellipse(0, tileSize * 0.16, tileSize * 0.4, tileSize * 0.32, outfit));
  } else {
    legLeft = scene.add.container(-tileSize * 0.09, tileSize * 0.36, [
      scene.add.rectangle(0, 0, tileSize * 0.13, tileSize * 0.22, outfitShade),
      scene.add.ellipse(0, tileSize * 0.1, tileSize * 0.15, tileSize * 0.07, 0x3b2513), // zapato
    ]);
    legRight = scene.add.container(tileSize * 0.09, tileSize * 0.36, [
      scene.add.rectangle(0, 0, tileSize * 0.13, tileSize * 0.22, outfitShade),
      scene.add.ellipse(0, tileSize * 0.1, tileSize * 0.15, tileSize * 0.07, 0x3b2513),
    ]);
    parts.push(legLeft, legRight);
    // Torso.
    parts.push(scene.add.ellipse(0, tileSize * 0.16, tileSize * 0.4, tileSize * 0.36, outfit));
  }

  // Brazos: cada uno es su propio mini-contenedor (igual que las piernas)
  // para poder balancearlos al caminar, en sentido contrario a las piernas.
  const armLeft = scene.add.container(-tileSize * 0.22, tileSize * 0.1, [
    scene.add.circle(0, 0, tileSize * 0.09, outfit),
  ]);
  const armRight = scene.add.container(tileSize * 0.22, tileSize * 0.1, [
    scene.add.circle(0, 0, tileSize * 0.09, outfit),
  ]);
  parts.push(armLeft, armRight);

  // Pelo largo detrás de la cabeza (solo chica; se asoma por los lados y hombros).
  if (isGirl) {
    parts.push(scene.add.ellipse(0, -tileSize * 0.16, tileSize * 0.56, tileSize * 0.52, hair));
  }

  // Cabeza grande, proporción "chibi".
  parts.push(scene.add.circle(0, -tileSize * 0.26, tileSize * 0.22, skin));

  // Pelo corto / flequillo, forma ovalada en vez de un bloque plano.
  const fringeWidth = isGirl ? tileSize * 0.48 : tileSize * 0.42;
  parts.push(scene.add.ellipse(0, -tileSize * 0.4, fringeWidth, tileSize * 0.24, hair));

  // Ojos: elipses en vez de puntos, más expresivos. Se guardan como
  // referencia para poder ocultar uno u otro según hacia dónde mire el
  // personaje (ver applyFacingToVisual más abajo).
  const eyeLeft = scene.add.ellipse(-tileSize * 0.075, -tileSize * 0.24, tileSize * 0.05, tileSize * 0.065, eyes);
  const eyeRight = scene.add.ellipse(tileSize * 0.075, -tileSize * 0.24, tileSize * 0.05, tileSize * 0.065, eyes);
  parts.push(eyeLeft, eyeRight);

  // Brillo suave en la cabeza para dar sensación de volumen.
  parts.push(scene.add.circle(-tileSize * 0.09, -tileSize * 0.32, tileSize * 0.06, 0xffffff).setAlpha(0.22));

  // Nuca: círculo del color del pelo que tapa toda la cabeza. Se queda
  // oculto normalmente; solo se muestra cuando el personaje camina hacia
  // arriba (de espaldas a la cámara) — así se nota la diferencia de
  // dirección, en vez de verse siempre igual sin importar hacia dónde va.
  const backOfHead = scene.add.circle(0, -tileSize * 0.26, tileSize * 0.225, hair).setVisible(false);
  parts.push(backOfHead);

  const container = scene.add.container(0, 0, parts);
  container.legLeft = legLeft;
  container.legRight = legRight;
  container.armLeft = armLeft;
  container.armRight = armRight;
  container.eyeLeft = eyeLeft;
  container.eyeRight = eyeRight;
  container.backOfHead = backOfHead;
  return container;
}

// Ajusta la cara del personaje según hacia dónde mira: de frente se ven
// ambos ojos, de espaldas (hacia arriba) se tapa toda la cara con el color
// del pelo, y de perfil (izquierda/derecha) se oculta el ojo del lado
// contrario, sugiriendo un giro de cabeza. Se usa tanto para el jugador
// local (Player.js) como para los demás jugadores en el mapa compartido.
function applyFacingToVisual(visual, facing) {
  if (!visual || !visual.eyeLeft || !visual.eyeRight || !visual.backOfHead) return;
  if (facing === 'up') {
    visual.backOfHead.setVisible(true);
    visual.eyeLeft.setVisible(false);
    visual.eyeRight.setVisible(false);
  } else {
    visual.backOfHead.setVisible(false);
    visual.eyeLeft.setVisible(facing !== 'right');
    visual.eyeRight.setVisible(facing !== 'left');
  }
}
