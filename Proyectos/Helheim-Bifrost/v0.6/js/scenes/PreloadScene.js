// Decide a qué escena mandar al jugador según lo que ya tenga hecho:
// sin personaje -> crearlo; con personaje pero sin equipo -> elegir inicial;
// con ambos -> directo al mapa. La usan tanto esta escena como
// CharacterCreationScene al terminar su propio paso.
function decideNextScene(data) {
  if (!data.characterCreated) return 'CharacterCreationScene';
  if (!data.party || data.party.length === 0) return 'StarterSelectionScene';
  return 'OverworldScene';
}

// Antes de dibujar el mapa necesitamos saber en cuál (overworld, route1...),
// en qué posición, si el jugador ya creó su personaje y si ya tiene equipo.
// Esta escena resuelve todo eso primero y decide a qué escena mandarlo.
class PreloadScene extends Phaser.Scene {
  constructor() {
    super('PreloadScene');
  }

  preload() {
    // Aquí cargarías spritesheets/tilesets reales cuando los tengas:
    //   this.load.spritesheet('player', 'assets/sprites/player.png', { frameWidth: 16, frameHeight: 16 });
  }

  async create() {
    const defaultAppearance = { gender: 'boy', skinColor: '#f1c27d', hairColor: '#2c1b18', eyeColor: '#3b2415' };
    let initial = {
      mapKey: 'overworld',
      x: MAPS.overworld.spawn.x,
      y: MAPS.overworld.spawn.y,
      party: [],
      inventory: { pokeball: 5 },
      appearance: defaultAppearance,
      characterCreated: false,
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
            appearance: data.appearance || defaultAppearance,
            characterCreated: !!data.characterCreated,
          };
        }
      }
    } catch (err) {
      // Backend no disponible: arrancamos igual con valores por defecto.
    }

    this.scene.start(decideNextScene(initial), initial);
  }
}
