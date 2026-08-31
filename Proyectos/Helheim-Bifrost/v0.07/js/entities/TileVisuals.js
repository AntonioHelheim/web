// Cada tile se dibuja como una pequeña composición de formas (base + detalles)
// para que se parezca a lo que representa, en vez de un cuadrado de color
// plano. Usa un "azar" determinista basado en la posición del tile, así el
// mismo tile siempre se ve igual cada vez que se dibuja (no cambia entre
// visitas ni parpadea).
function seededRandom(seed) {
  const x = Math.sin(seed) * 10000;
  return x - Math.floor(x);
}

function drawPath(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.light).setDepth(0);
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
function drawTile(scene, x, y, ts, tileType) {
  const wx = x * ts + ts / 2;
  const wy = y * ts + ts / 2;
  const seed = x * 928.13 + y * 17.71;

  switch (tileType) {
    case 1: drawTallGrass(scene, wx, wy, ts, seed); break;
    case 2: drawSmallTree(scene, wx, wy, ts, seed); break;
    case 3: drawWater(scene, wx, wy, ts, seed); break;
    case 4: drawWarpGate(scene, wx, wy, ts); break;
    case 5: scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.trunk).setDepth(0); break;
    case 6: drawRock(scene, wx, wy, ts, seed); break;
    case 7: drawSand(scene, wx, wy, ts, seed); break;
    case 8: drawCaveEntrance(scene, wx, wy, ts, seed); break;
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
