// Decide a qué escena mandar al jugador según lo que ya tenga hecho:
// sin personaje -> crearlo; con personaje -> directo al mapa. Ya no se pasa
// por una selección de inicial: el jugador empieza sin compañeros y los irá
// domesticando conforme avance (sistema pendiente, se revisará más adelante).
function decideNextScene(data) {
  if (!data.characterCreated) return 'CharacterCreationScene';
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
    // Catálogo de criaturas: única fuente de datos compartida con
    // api/config.php (ítem 4 de ROADMAP-ARQUITECTURA.md) — ya no vive
    // hardcodeado en js/data.js. Phaser espera a que termine de cargar
    // antes de llamar a create(), así que SPECIES ya está poblado para
    // cuando cualquier otra escena lo necesite.
    this.load.json('speciesData', `data/species.json?v=${window.BIFROST_ASSET_VERSION || ''}`);

    // Aquí cargarías spritesheets/tilesets reales cuando los tengas:
    //   this.load.spritesheet('player', 'assets/sprites/player.png', { frameWidth: 16, frameHeight: 16 });
  }

  async create() {
    SPECIES = this.cache.json.get('speciesData') || {};

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
