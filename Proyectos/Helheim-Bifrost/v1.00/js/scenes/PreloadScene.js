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

    // Sprites de personaje reales — se intentan cargar las 6
    // combinaciones posibles (PEOPLE_SPRITE_COMBOS, en CharacterVisual.js),
    // cada una con SU PROPIO tamaño de cuadro confirmado (no todas miden
    // lo mismo — ver la nota en PEOPLE_SPRITE_COMBOS). Las que no tengan
    // archivo real todavía (ver PLAN-GRAPHICS-AUDIO.md para el estado
    // exacto) van a fallar la carga con un 404 — es esperado y no rompe
    // nada: Phaser sigue con el resto de la cola sin problema, y
    // Player.js cae al dibujo a mano para esas automáticamente. En
    // cuanto subas un archivo nuevo con el nombre correcto, empieza a
    // usarse solo, sin tocar código de nuevo.
    const v = window.BIFROST_ASSET_VERSION || '';
    PEOPLE_SPRITE_COMBOS.forEach(({ folder, preset, frameWidth, frameHeight }) => {
      const key = `people_${folder}_${preset}`;
      const path = `graphics/characters/people/${folder}/00${preset}.png`;
      this.load.spritesheet(key, `${path}?v=${v}`, { frameWidth, frameHeight });
    });
  }

  async create() {
    SPECIES = this.cache.json.get('speciesData') || {};
    // Se valida cada spritesheet cargado arriba (¿tiene los 16 cuadros
    // esperados, o el archivo real no calzaba con el tamaño supuesto?) y
    // se registran las 4 animaciones de caminata de los que sí calzan —
    // ambas funciones no hacen nada si esa textura en particular no
    // existe o no pasó la validación, así que es seguro llamarlas para
    // las 6 aunque todavía falten varias o alguna tenga un tamaño
    // distinto al esperado.
    PEOPLE_SPRITE_COMBOS.forEach(({ folder, preset }) => {
      const key = `people_${folder}_${preset}`;
      if (validateCharacterSpritesheet(this, key)) {
        defineCharacterAnimations(this, key);
      }
    });

    const defaultAppearance = { gender: 'boy', preset: 1, skinColor: '#f1c27d', hairColor: '#2c1b18', eyeColor: '#3b2415' };
    let initial = {
      mapKey: 'overworld',
      x: MAPS.overworld.spawn.x,
      y: MAPS.overworld.spawn.y,
      party: [],
      inventory: { runa_captura: 5 },
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
            inventory: data.inventory || { runa_captura: 5 },
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
