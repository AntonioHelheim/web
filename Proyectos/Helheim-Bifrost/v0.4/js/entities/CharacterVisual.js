// Construye la figura del personaje con formas de Phaser (nada de sprites
// con derechos de autor). El género define la silueta base (pantalones vs.
// vestido, un recurso genérico usado en incontables juegos originales) y
// tres colores personalizables: piel, pelo y ojos. La ropa usa un color fijo
// de tono tierra/vino para no depender de ninguna paleta de personaje.
const OUTFIT_BOY = 0x6b5030;
const OUTFIT_GIRL = 0x9c4f61;

function buildCharacterVisual(scene, tileSize, appearance) {
  const isGirl = appearance.gender === 'girl';
  const skin = Phaser.Display.Color.HexStringToColor(appearance.skinColor).color;
  const hair = Phaser.Display.Color.HexStringToColor(appearance.hairColor).color;
  const eyes = Phaser.Display.Color.HexStringToColor(appearance.eyeColor).color;
  const outfit = isGirl ? OUTFIT_GIRL : OUTFIT_BOY;
  const parts = [];

  // 1. Cuerpo: rectángulo para el chico, silueta de vestido (más ancho abajo) para la chica.
  let body;
  if (isGirl) {
    body = scene.add.polygon(
      0, tileSize * 0.14,
      [
        -tileSize * 0.16, -tileSize * 0.16,
        tileSize * 0.16, -tileSize * 0.16,
        tileSize * 0.30, tileSize * 0.30,
        -tileSize * 0.30, tileSize * 0.30,
      ],
      outfit
    );
  } else {
    body = scene.add.rectangle(0, tileSize * 0.12, tileSize * 0.4, tileSize * 0.46, outfit);
  }
  body.setStrokeStyle(1, GB_PALETTE.darkest);
  parts.push(body);

  // 2. Pelo largo detrás de la cabeza (solo chica, se asoma a los lados).
  if (isGirl) {
    parts.push(scene.add.ellipse(0, -tileSize * 0.22, tileSize * 0.5, tileSize * 0.44, hair));
  }

  // 3. Cabeza (va encima del pelo largo, si lo hay).
  parts.push(scene.add.circle(0, -tileSize * 0.22, tileSize * 0.17, skin).setStrokeStyle(1, GB_PALETTE.darkest));

  // 4. Flequillo / pelo corto encima de la cabeza (ambos géneros).
  const fringeWidth = isGirl ? tileSize * 0.36 : tileSize * 0.32;
  parts.push(scene.add.rectangle(0, -tileSize * 0.34, fringeWidth, tileSize * 0.14, hair));

  // 5. Ojos.
  parts.push(scene.add.circle(-tileSize * 0.06, -tileSize * 0.2, tileSize * 0.04, eyes));
  parts.push(scene.add.circle(tileSize * 0.06, -tileSize * 0.2, tileSize * 0.04, eyes));

  return scene.add.container(0, 0, parts);
}
