// Antes de dibujar el mapa necesitamos saber en cuál (overworld, route1...)
// y en qué posición debe aparecer el jugador, así que esta escena resuelve
// eso primero y solo entonces arranca OverworldScene con esos datos ya listos.
class PreloadScene extends Phaser.Scene {
  constructor() {
    super('PreloadScene');
  }

  preload() {
    // Aquí cargarías spritesheets/tilesets reales cuando los tengas:
    //   this.load.spritesheet('player', 'assets/sprites/player.png', { frameWidth: 16, frameHeight: 16 });
  }

  async create() {
    let initial = {
      mapKey: 'overworld',
      x: MAPS.overworld.spawn.x,
      y: MAPS.overworld.spawn.y,
      party: [],
      inventory: { pokeball: 5 },
    };

    try {
      const res = await fetch('api/load_game.php', { credentials: 'include' });
      if (res.ok) {
        const data = await res.json();
        if (MAPS[data.mapKey]) {
          initial = {
            mapKey: data.mapKey,
            x: data.x,
            y: data.y,
            party: data.party || [],
            inventory: data.inventory || { pokeball: 5 },
          };
        }
      }
    } catch (err) {
      // Backend no disponible: arrancamos igual en el spawn por defecto.
    }

    this.scene.start('OverworldScene', initial);
  }
}
