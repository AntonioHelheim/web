// Cada tile se dibuja como una pequeña composición de formas (base + detalles)
// para que se parezca a lo que representa, en vez de un cuadrado de color
// plano. Usa un "azar" determinista basado en la posición del tile, así el
// mismo tile siempre se ve igual cada vez que se dibuja (no cambia entre
// visitas ni parpadea).
function seededRandom(seed) {
  const x = Math.sin(seed) * 10000;
  return x - Math.floor(x);
}

// Mapa de tipo de tile -> clave de textura real (ver PLAN-GRAPHICS-AUDIO.md).
// Se revisa en cada llamada a drawTile(); si la textura no cargó todavía
// (no hay archivo real para ese tipo), sigue usando el dibujo a mano de
// siempre — mismo patrón de respaldo automático que ya usamos en
// personajes (Player.js / PEOPLE_SPRITE_COMBOS).
const TILE_REAL_TEXTURES = { 4: 'tile_door', 6: 'tile_boulder' };

// Árbol suelto (tile 2): object_tree.png es una animación de 16 cuadros
// (grilla 4x4) con efecto de viento — a diferencia de puertas/roca
// (donde alcanza un cuadro estático), acá sí tiene sentido reproducir
// los 16 cuadros en loop continuo, ya que el efecto es un balanceo
// permanente, no algo que "se dispara" al usarlo. La animación
// (`tile_tree_sway`) se registra una sola vez en PreloadScene
// (defineTileTreeAnimation) y cada árbol la reproduce con play(true) —
// Phaser sincroniza automáticamente todas las instancias que usan la
// misma animación, así que no hace falta desfasarlas a mano.
function drawRealTreeTile(scene, wx, wy, ts) {
  const sprite = scene.add.sprite(wx, wy, 'tile_tree', 0).setDisplaySize(ts, ts).setDepth(0);
  if (scene.anims.exists('tile_tree_sway')) sprite.play('tile_tree_sway');
  return sprite;
}

// Puertas (tile 4) y roca (tile 6): imágenes reales de 128x128 en grilla
// 4x4 (32x32 por cuadro) — se muestra el primer cuadro (pose de reposo/
// cerrada). Animar la apertura de la puerta al usarla queda para una
// siguiente pasada (ver PLAN-GRAPHICS-AUDIO.md) — por ahora el salto
// visual importante es reemplazar la forma dibujada a mano por la
// textura real.
function drawRealTile(scene, wx, wy, ts, textureKey) {
  scene.add.sprite(wx, wy, textureKey, 0).setDisplaySize(ts, ts).setDepth(0);
}

// Agua (tile 3): water.png es una tira de 768x128px cuyo recorte exacto
// en cuadros de animación no está confirmado todavía (a diferencia de
// puertas/roca, que sí vienen en la convención clara de grilla 4x4) — en
// vez de arriesgar un recorte equivocado (mismo tipo de bug que ya
// tuvimos con los sprites de personaje), se usa como una textura ancha:
// cada tile de agua muestra un recorte fijo de 32x32 tomado de una
// posición determinista dentro de la tira, dando variedad visual entre
// tiles sin depender de saber su estructura interna exacta.
function drawRealWaterTile(scene, wx, wy, ts, seed) {
  const src = scene.textures.get('tile_water').getSourceImage();
  const cols = Math.floor(src.width / ts);
  const rows = Math.floor(src.height / ts);
  const col = Math.floor(seededRandom(seed + 500) * cols);
  const row = Math.floor(seededRandom(seed + 501) * rows);
  scene.add.image(wx, wy, 'tile_water')
    .setCrop(col * ts, row * ts, ts, ts)
    .setDisplaySize(ts, ts)
    .setDepth(0);
}

// Flores decorativas (Flowers1.png / Flowers2.png, 5 variantes de color
// cada una, sin animación) — aparecen ocasionalmente sobre camino (tile
// 0) y pasto alto (tile 1), con probabilidad baja para no saturar el
// mapa. Elección determinista por posición (seededRandom), así el mismo
// tile siempre muestra la misma flor entre visitas — mismo criterio que
// ya usa drawPath() para las brizas de pasto.
function drawFlowerDecoration(scene, wx, wy, ts, seed) {
  if (seededRandom(seed + 700) > 0.12) return; // ~12% de los tiles
  const textureKey = seededRandom(seed + 701) < 0.5 ? 'tile_flowers_1' : 'tile_flowers_2';
  if (!scene.textures.exists(textureKey)) return;
  const frame = Math.floor(seededRandom(seed + 702) * 5); // 5 variantes de color
  scene.add.sprite(wx, wy, textureKey, frame).setDisplaySize(ts * 0.7, ts * 0.7).setDepth(1);
}

function drawPath(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.light).setDepth(0);
  drawFlowerDecoration(scene, wx, wy, ts, seed);
  // El mapa de Renca es mucho más grande que antes (12.9x más tiles), y el
  // camino liso es la mayoría de ellos — dibujar brizas en cada uno
  // multiplicaría demasiado la cantidad de objetos. Se saltan ~55% de las
  // veces (determinista según la posición) y como máximo una por tile en
  // vez de dos; sigue viéndose con textura, sin recargar el navegador.
  if (seededRandom(seed + 99) > 0.45) return;
  const rx = (seededRandom(seed) - 0.5) * ts * 0.6;
  const ry = (seededRandom(seed + 50) - 0.5) * ts * 0.6;
  scene.add.triangle(wx + rx, wy + ry, 0, ts * 0.08, -ts * 0.05, -ts * 0.08, ts * 0.05, -ts * 0.08, GB_PALETTE.dark)
    .setAlpha(0.5).setDepth(1);
}

function drawTallGrass(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.dark).setDepth(0);
  drawFlowerDecoration(scene, wx, wy, ts, seed + 900); // +900: semilla distinta a drawPath para el mismo tile no repita el resultado
  for (let i = 0; i < 5; i++) {
    const rx = (seededRandom(seed + i * 3) - 0.5) * ts * 0.75;
    const ry = (seededRandom(seed + i * 3 + 1) - 0.5) * ts * 0.55;
    const h = ts * (0.32 + seededRandom(seed + i * 3 + 2) * 0.28);
    scene.add.triangle(wx + rx, wy + ry, 0, h * 0.5, -ts * 0.055, -h * 0.5, ts * 0.055, -h * 0.5, GB_PALETTE.light)
      .setDepth(1);
  }
}

function drawSmallTree(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.light).setDepth(0);

  // Variación sutil de tamaño por árbol (determinista), para que el
  // bosque no se vea como el mismo arbolito repetido en cada celda.
  const sizeVar = 0.92 + seededRandom(seed + 5) * 0.22;

  // Raíz: una sombra ovalada oscura en la base del tronco.
  scene.add.ellipse(wx, wy + ts * 0.4, ts * 0.2, ts * 0.06, 0x000000).setAlpha(0.2).setDepth(0);

  scene.add.rectangle(wx, wy + ts * 0.28, ts * 0.16, ts * 0.3 * sizeVar, GB_PALETTE.trunk).setDepth(1);
  const jitter = (seededRandom(seed) - 0.5) * ts * 0.06;
  const r = ts * 0.22 * sizeVar;
  scene.add.circle(wx - ts * 0.15 + jitter, wy - ts * 0.06, r, GB_PALETTE.dark).setDepth(1);
  scene.add.circle(wx + ts * 0.15 - jitter, wy - ts * 0.06, r, GB_PALETTE.dark).setDepth(1);
  scene.add.circle(wx, wy - ts * 0.24, r * 1.1, GB_PALETTE.dark).setDepth(2);
  // Toque de tono más claro para dar volumen a la copa.
  scene.add.circle(wx - ts * 0.06, wy - ts * 0.3, r * 0.5, GB_PALETTE.light).setAlpha(0.3).setDepth(3);
}

function drawWater(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.water).setDepth(0);
  for (let i = 0; i < 3; i++) {
    const ry = -ts * 0.28 + i * ts * 0.26 + (seededRandom(seed + i) - 0.5) * ts * 0.1;
    const width = ts * (0.4 + seededRandom(seed + i + 10) * 0.25);
    scene.add.ellipse(wx, wy + ry, width, ts * 0.06, GB_PALETTE.lightest).setAlpha(0.4).setDepth(1);
  }
  // Pequeño destello de reflejo, da sensación de superficie brillante.
  if (seededRandom(seed + 20) > 0.5) {
    scene.add.circle(wx + ts * 0.18, wy - ts * 0.15, ts * 0.05, 0xffffff).setAlpha(0.3).setDepth(2);
  }
}

function drawWarpGate(scene, wx, wy, ts) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.lightest).setDepth(0);
  scene.add.rectangle(wx, wy + ts * 0.16, ts * 0.08, ts * 0.46, GB_PALETTE.trunk).setDepth(1);
  scene.add.rectangle(wx, wy - ts * 0.1, ts * 0.34, ts * 0.16, 0x8b6b3a).setStrokeStyle(1, GB_PALETTE.trunk).setDepth(2);
}

function drawRock(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.light).setDepth(0);
  scene.add.ellipse(wx, wy + ts * 0.22, ts * 0.5, ts * 0.13, 0x2f2f2f).setAlpha(0.25).setDepth(0);
  const j = (seededRandom(seed) - 0.5) * ts * 0.08;
  const points = [
    -ts * 0.26, ts * 0.18,
    -ts * 0.3 + j, -ts * 0.04,
    -ts * 0.08, -ts * 0.27,
    ts * 0.18, -ts * 0.2,
    ts * 0.28, ts * 0.06,
    ts * 0.14, ts * 0.22,
  ];
  scene.add.polygon(wx, wy, points, GB_PALETTE.rock).setStrokeStyle(1, 0x4a4a4a).setDepth(1);
}

function drawSand(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.sand).setDepth(0);
  for (let i = 0; i < 3; i++) {
    const rx = (seededRandom(seed + i * 2) - 0.5) * ts * 0.7;
    const ry = (seededRandom(seed + i * 2 + 1) - 0.5) * ts * 0.7;
    scene.add.circle(wx + rx, wy + ry, ts * 0.03, 0xb89552).setDepth(1);
  }
}

// Entrada de cueva: marco rocoso irregular con una boca oscura al centro.
// Es un hito visual (bloqueado) — el interior de las cuevas se diseñará
// más adelante; por ahora solo se ven desde afuera.
function drawCaveEntrance(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.light).setDepth(0);
  const j = (seededRandom(seed) - 0.5) * ts * 0.05;
  const framePoints = [
    -ts * 0.3, ts * 0.28,
    -ts * 0.34 + j, -ts * 0.02,
    -ts * 0.14, -ts * 0.3,
    ts * 0.14, -ts * 0.3,
    ts * 0.34 - j, -ts * 0.02,
    ts * 0.3, ts * 0.28,
  ];
  scene.add.polygon(wx, wy, framePoints, GB_PALETTE.rock).setStrokeStyle(1, 0x4a4a4a).setDepth(1);
  scene.add.ellipse(wx, wy + ts * 0.06, ts * 0.34, ts * 0.4, 0x120a06).setDepth(2);
  scene.add.ellipse(wx, wy + ts * 0.14, ts * 0.3, ts * 0.2, 0x000000).setAlpha(0.35).setDepth(3);
}

// Dibuja un tile individual. tileType 5 (tronco del gran árbol) solo pone
// una base de color; el árbol grande de verdad se dibuja aparte, una sola
// vez por bloque completo, con drawBigTree().
// Pared de edificio (tile 9: comisaría/hospital/hotel, ver
// PLAN-GRAPHICS-AUDIO.md). Usa Black.png como base cuando está cargada
// (textura real, aunque sea solo un relleno sólido) + una ventana
// dibujada a mano encima para dar variedad — no hay un archivo de
// "pared de edificio" real todavía, así que esto es un intermedio: ya
// usa PNG real donde se puede, el resto sigue siendo dibujo a mano hasta
// tener texturas de pared/techo genuinas.
function drawBuildingWall(scene, wx, wy, ts, seed) {
  if (scene.textures.exists('tile_black')) {
    scene.add.image(wx, wy, 'tile_black').setDisplaySize(ts, ts).setDepth(0);
  } else {
    scene.add.rectangle(wx, wy, ts, ts, 0x1a1a1a).setDepth(0);
  }
  // Ventana: aparece en la mayoría de los tiles de pared (no todos, para
  // que no se vea como una grilla perfecta) — posición fija al centro,
  // variando solo si aparece o no, determinista por posición.
  if (seededRandom(seed + 800) < 0.7) {
    scene.add.rectangle(wx, wy, ts * 0.32, ts * 0.32, 0xffe9a8).setStrokeStyle(1, 0x0f380f).setDepth(1);
  }
}

// Decoración de interiores (escritorio, cama, símbolo de cruz/estrella)
// — dibujo a mano, no hay archivos de mobiliario real todavía (ver
// PLAN-GRAPHICS-AUDIO.md). Puramente visual: no bloquea el paso (mismo
// espíritu que las flores), para no tener que revalidar conectividad
// cada vez que se agregue una decoración nueva.
function drawDecoration(scene, wx, wy, ts, type) {
  switch (type) {
    case 'desk': // escritorio de recepción: base + tablero superior
      scene.add.rectangle(wx, wy + ts * 0.1, ts * 0.8, ts * 0.55, 0x6b4226).setDepth(2);
      scene.add.rectangle(wx, wy - ts * 0.2, ts * 0.85, ts * 0.14, 0x8b5a2b).setStrokeStyle(1, 0x3b2415).setDepth(3);
      break;
    case 'bed': // cama: colchón + almohada
      scene.add.rectangle(wx, wy, ts * 0.85, ts * 0.9, 0xcc4444).setStrokeStyle(1, 0x7a1f1f).setDepth(2);
      scene.add.rectangle(wx, wy - ts * 0.3, ts * 0.6, ts * 0.22, 0xf5f5f5).setStrokeStyle(1, 0xcccccc).setDepth(3);
      break;
    case 'cross': // símbolo médico
      scene.add.rectangle(wx, wy, ts * 0.16, ts * 0.55, 0xdc3030).setDepth(2);
      scene.add.rectangle(wx, wy, ts * 0.55, ts * 0.16, 0xdc3030).setDepth(2);
      break;
    case 'star': // símbolo policial (estrella de 5 puntas simplificada)
      scene.add.polygon(wx, wy, [
        0, -ts * 0.32, ts * 0.1, -ts * 0.1, ts * 0.32, -ts * 0.1,
        ts * 0.14, ts * 0.06, ts * 0.2, ts * 0.3, 0, ts * 0.16,
        -ts * 0.2, ts * 0.3, -ts * 0.14, ts * 0.06, -ts * 0.32, -ts * 0.1, -ts * 0.1, -ts * 0.1,
      ], 0xf0c419).setStrokeStyle(1, 0x8a6d00).setDepth(2);
      break;
    default:
      break;
  }
}

function drawTile(scene, x, y, ts, tileType) {
  const wx = x * ts + ts / 2;
  const wy = y * ts + ts / 2;
  const seed = x * 928.13 + y * 17.71;

  // Agua y árbol tienen su propio camino: agua porque su textura no
  // viene en la convención de grilla clara (ver drawRealWaterTile), y
  // árbol porque necesita reproducir la animación en loop (ver
  // drawRealTreeTile) en vez de solo mostrar el cuadro 0 como
  // puertas/roca.
  if (tileType === 3 && scene.textures.exists('tile_water')) {
    drawRealWaterTile(scene, wx, wy, ts, seed);
    return;
  }
  if (tileType === 2 && scene.textures.exists('tile_tree')) {
    drawRealTreeTile(scene, wx, wy, ts);
    return;
  }
  const textureKey = TILE_REAL_TEXTURES[tileType];
  if (textureKey && scene.textures.exists(textureKey)) {
    drawRealTile(scene, wx, wy, ts, textureKey);
    return;
  }

  switch (tileType) {
    case 1: drawTallGrass(scene, wx, wy, ts, seed); break;
    case 2: drawSmallTree(scene, wx, wy, ts, seed); break;
    case 3: drawWater(scene, wx, wy, ts, seed); break;
    case 4: drawWarpGate(scene, wx, wy, ts); break;
    case 5: scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.trunk).setDepth(0); break;
    case 6: drawRock(scene, wx, wy, ts, seed); break;
    case 7: drawSand(scene, wx, wy, ts, seed); break;
    case 8: drawCaveEntrance(scene, wx, wy, ts, seed); break;
    case 9: drawBuildingWall(scene, wx, wy, ts, seed); break;
    default: drawPath(scene, wx, wy, ts, seed); break;
  }
}

// Encuentra bloques conectados de tile 5 (el tronco del árbol grande) para
// poder dibujar UNA sola copa grande que cubra todo el bloque, en vez de
// que cada celda dibuje un árbol pequeño repetido.
function findTile5Regions(layout) {
  const rows = layout.length;
  const cols = layout[0].length;
  const visited = Array.from({ length: rows }, () => new Array(cols).fill(false));
  const regions = [];

  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      if (layout[y][x] !== 5 || visited[y][x]) continue;

      let minX = x, maxX = x, minY = y, maxY = y;
      const stack = [[x, y]];
      visited[y][x] = true;
      while (stack.length) {
        const [cx, cy] = stack.pop();
        minX = Math.min(minX, cx); maxX = Math.max(maxX, cx);
        minY = Math.min(minY, cy); maxY = Math.max(maxY, cy);
        for (const [dx, dy] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
          const nx = cx + dx;
          const ny = cy + dy;
          if (nx < 0 || ny < 0 || nx >= cols || ny >= rows || visited[ny][nx]) continue;
          if (layout[ny][nx] !== 5) continue;
          visited[ny][nx] = true;
          stack.push([nx, ny]);
        }
      }
      regions.push({ minX, maxX, minY, maxY });
    }
  }
  return regions;
}

function drawBigTree(scene, region, ts) {
  const widthPx = (region.maxX - region.minX + 1) * ts;
  const heightPx = (region.maxY - region.minY + 1) * ts;
  const cx = region.minX * ts + widthPx / 2;
  const bottomY = (region.maxY + 1) * ts;
  const topY = region.minY * ts;
  const rootColor = 0x3b2513;

  // Raíces: pequeños óvalos oscuros hundidos en la base, para que el
  // tronco se vea plantado en la tierra en vez de flotando sobre ella.
  [-0.2, -0.08, 0.08, 0.2].forEach((ox) => {
    scene.add.ellipse(cx + ox * widthPx, bottomY - heightPx * 0.02, widthPx * 0.14, heightPx * 0.05, rootColor)
      .setAlpha(0.55).setDepth(1);
  });

  // Tronco ancho con una franja central más oscura, simulando corteza.
  const trunkH = heightPx * 0.52;
  scene.add.rectangle(cx, bottomY - trunkH / 2, widthPx * 0.34, trunkH, GB_PALETTE.trunk)
    .setStrokeStyle(2, 0x3b2513).setDepth(1);
  scene.add.rectangle(cx, bottomY - trunkH / 2, widthPx * 0.07, trunkH * 0.92, 0x3b2513)
    .setAlpha(0.4).setDepth(2);
  scene.add.rectangle(cx - widthPx * 0.1, bottomY - trunkH / 2, widthPx * 0.04, trunkH * 0.85, 0x3b2513)
    .setAlpha(0.25).setDepth(2);

  // Copa: muchos círculos superpuestos en capas (base oscura + un tono
  // medio encima), bien frondosa y notablemente más ancha que el bloque
  // de tronco — un árbol imponente, no un arbolito repetido.
  const canopyCenterY = topY + heightPx * 0.34;
  const baseR = widthPx * 0.4;
  const darkBlobs = [
    [-0.4, 0.16, 0.85], [0.4, 0.16, 0.85],
    [-0.22, -0.1, 0.95], [0.22, -0.1, 0.95],
    [0, -0.3, 0.9],
    [-0.46, -0.06, 0.6], [0.46, -0.06, 0.6],
    [-0.16, 0.3, 0.65], [0.16, 0.3, 0.65],
  ];
  darkBlobs.forEach(([ox, oy, scale]) => {
    scene.add.circle(cx + ox * widthPx, canopyCenterY + oy * heightPx, baseR * scale, GB_PALETTE.dark).setDepth(1);
  });

  // Tono medio encima: da sensación de dos capas de follaje, no un solo
  // color plano.
  const midBlobs = [
    [-0.2, -0.02, 0.55], [0.22, 0.02, 0.5], [0, -0.22, 0.62], [-0.05, 0.14, 0.45],
  ];
  midBlobs.forEach(([ox, oy, scale]) => {
    scene.add.circle(cx + ox * widthPx, canopyCenterY + oy * heightPx, baseR * scale, GB_PALETTE.light)
      .setAlpha(0.35).setDepth(2);
  });

  // Brillo suave arriba a la izquierda, para dar sensación de volumen y de
  // luz cayendo sobre la copa.
  scene.add.circle(cx - widthPx * 0.2, canopyCenterY - heightPx * 0.28, baseR * 0.42, 0xffffff)
    .setAlpha(0.22).setDepth(3);
}
