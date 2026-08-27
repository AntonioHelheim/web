// Catálogo de mapas. Cada uno es una cuadrícula de 15x11 tiles:
// 0 camino, 1 hierba alta (encuentros), 2 árbol (bloqueado), 3 agua (bloqueado), 4 warp a otro mapa.
// Tener varios mapas permite que dos jugadores recorran zonas distintas en
// paralelo, o se encuentren si entran al mismo.
const MAPS = {
  overworld: {
    label: 'Pueblo Origen',
    layout: [
      [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2],
      [2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2],
      [2, 0, 1, 1, 1, 0, 0, 0, 3, 3, 0, 0, 0, 0, 2],
      [2, 0, 1, 1, 1, 0, 0, 0, 3, 3, 0, 0, 0, 0, 2],
      [2, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2],
      [2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 4],
      [2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2],
      [2, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 2],
      [2, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 2],
      [2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2],
      [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2],
    ],
    spawn: { x: 7, y: 5 },
    warps: { '14,5': { to: 'route1', spawn: { x: 1, y: 5 } } },
  },
  route1: {
    label: 'Ruta 1',
    layout: [
      [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2],
      [2, 1, 1, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0, 0, 2],
      [2, 1, 1, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0, 0, 2],
      [2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2],
      [2, 0, 3, 3, 0, 0, 0, 0, 0, 3, 3, 0, 0, 0, 2],
      [4, 0, 3, 3, 0, 0, 0, 0, 0, 3, 3, 0, 0, 0, 2],
      [2, 0, 3, 3, 0, 0, 0, 0, 0, 3, 3, 0, 0, 0, 2],
      [2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2],
      [2, 1, 1, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0, 0, 2],
      [2, 1, 1, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0, 0, 2],
      [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2],
    ],
    spawn: { x: 1, y: 5 },
    warps: { '0,5': { to: 'overworld', spawn: { x: 13, y: 5 } } },
  },
};

function tileAt(mapKey, x, y) {
  const map = MAPS[mapKey];
  if (!map || y < 0 || y >= map.layout.length || x < 0 || x >= map.layout[0].length) return 2;
  return map.layout[y][x];
}

function isTileBlocked(mapKey, x, y) {
  const tile = tileAt(mapKey, x, y);
  return tile === 2 || tile === 3;
}

function warpAt(mapKey, x, y) {
  const map = MAPS[mapKey];
  return map.warps[`${x},${y}`] || null;
}
