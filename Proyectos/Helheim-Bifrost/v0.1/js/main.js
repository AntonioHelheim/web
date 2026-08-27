// Constantes compartidas por todas las escenas.
const TILE_SIZE = 32;
const MAP_COLS = 15;
const MAP_ROWS = 11;

const GB_PALETTE = {
  darkest: 0x0f380f,
  dark: 0x306230,
  light: 0x8bac0f,
  lightest: 0x9bbc0f,
  water: 0x3a6ea5,
};

const config = {
  type: Phaser.AUTO,
  parent: 'game-container',
  width: MAP_COLS * TILE_SIZE,
  height: MAP_ROWS * TILE_SIZE,
  pixelArt: true,
  backgroundColor: '#0f380f',
  scene: [PreloadScene, OverworldScene, BattleScene, PvpBattleScene],
};

const game = new Phaser.Game(config);
