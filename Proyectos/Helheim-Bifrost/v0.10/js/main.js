// Constantes compartidas por todas las escenas.
const TILE_SIZE = 32;
// El canvas se queda de este tamaño siempre; los mapas pueden ser más
// grandes que esto — la cámara sigue al jugador dentro de cada mapa.
const VIEWPORT_COLS = 15;
const VIEWPORT_ROWS = 11;

const GB_PALETTE = {
  darkest: 0x0f380f,
  dark: 0x306230,
  light: 0x8bac0f,
  lightest: 0x9bbc0f,
  water: 0x3a6ea5,
  trunk: 0x5a3921, // tronco del gran árbol de la plaza
  rock: 0x6e6e6e,  // roca de la ruta norte
  sand: 0xd8c078,  // arena de la ruta sur
};

const config = {
  type: Phaser.AUTO,
  parent: 'game-container',
  width: VIEWPORT_COLS * TILE_SIZE,
  height: VIEWPORT_ROWS * TILE_SIZE,
  pixelArt: true,
  backgroundColor: '#0f380f',
  // El canvas se dibuja siempre a esta resolución interna fija, pero
  // Phaser.Scale.FIT lo escala (con letterboxing) para llenar el
  // contenedor #game-container, que a su vez es responsive por CSS —
  // así se adapta tanto a pantallas de escritorio grandes como a móviles.
  scale: {
    mode: Phaser.Scale.FIT,
    autoCenter: Phaser.Scale.CENTER_BOTH,
  },
  scene: [PreloadScene, CharacterCreationScene, MenuScene, OverworldScene, BattleScene, PvpBattleScene],
};

const game = new Phaser.Game(config);
