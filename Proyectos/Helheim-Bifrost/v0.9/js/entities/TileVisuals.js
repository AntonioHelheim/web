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
  for (let i = 0; i < 2; i++) {
    const rx = (seededRandom(seed + i) - 0.5) * ts * 0.6;
    const ry = (seededRandom(seed + i + 50) - 0.5) * ts * 0.6;
    scene.add.triangle(wx + rx, wy + ry, 0, ts * 0.08, -ts * 0.05, -ts * 0.08, ts * 0.05, -ts * 0.08, GB_PALETTE.dark)
      .setAlpha(0.5).setDepth(1);
  }
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
  scene.add.rectangle(wx, wy + ts * 0.28, ts * 0.16, ts * 0.3, GB_PALETTE.trunk).setDepth(1);
  const jitter = (seededRandom(seed) - 0.5) * ts * 0.06;
  scene.add.circle(wx - ts * 0.15 + jitter, wy - ts * 0.06, ts * 0.22, GB_PALETTE.dark).setDepth(1);
  scene.add.circle(wx + ts * 0.15 - jitter, wy - ts * 0.06, ts * 0.22, GB_PALETTE.dark).setDepth(1);
  scene.add.circle(wx, wy - ts * 0.24, ts * 0.25, GB_PALETTE.dark).setDepth(2);
}

function drawWater(scene, wx, wy, ts, seed) {
  scene.add.rectangle(wx, wy, ts, ts, GB_PALETTE.water).setDepth(0);
  for (let i = 0; i < 2; i++) {
    const ry = -ts * 0.18 + i * ts * 0.32 + (seededRandom(seed + i) - 0.5) * ts * 0.1;
    scene.add.ellipse(wx, wy + ry, ts * 0.55, ts * 0.07, GB_PALETTE.lightest).setAlpha(0.45).setDepth(1);
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

  // Tronco ancho en la base del bloque.
  const trunkH = heightPx * 0.5;
  scene.add.rectangle(cx, bottomY - trunkH / 2, widthPx * 0.32, trunkH, GB_PALETTE.trunk)
    .setStrokeStyle(2, 0x3b2513).setDepth(1);

  // Copa: varios círculos grandes superpuestos, un poco más ancha que el
  // bloque bloqueado, para que se vea frondosa e imponente.
  const canopyCenterY = topY + heightPx * 0.38;
  const baseR = widthPx * 0.34;
  const blobs = [
    [-0.32, 0.08, 0.85],
    [0.32, 0.08, 0.85],
    [0, -0.18, 1.05],
    [-0.14, 0.22, 0.7],
    [0.14, 0.22, 0.7],
  ];
  blobs.forEach(([ox, oy, scale]) => {
    scene.add.circle(cx + ox * widthPx, canopyCenterY + oy * heightPx, baseR * scale, GB_PALETTE.dark).setDepth(1);
  });
  // Brillo suave para dar sensación de volumen a la copa.
  scene.add.circle(cx - widthPx * 0.16, canopyCenterY - heightPx * 0.2, baseR * 0.45, GB_PALETTE.light)
    .setAlpha(0.5).setDepth(2);
}
