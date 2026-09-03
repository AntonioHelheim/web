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
    this.load.json('npcSpawnsData', `data/npc-spawns.json?v=${window.BIFROST_ASSET_VERSION || ''}`);

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

    // Texturas de tiles reales (ver PLAN-GRAPHICS-AUDIO.md) — mismo
    // patrón de respaldo automático: si alguna no carga, TileVisuals.js
    // sigue usando el dibujo a mano para ese tipo de tile.
    this.load.spritesheet('tile_door', `graphics/builds/doors1.png?v=${v}`, { frameWidth: 32, frameHeight: 32 });
    this.load.spritesheet('tile_boulder', `graphics/builds/object_boulder.png?v=${v}`, { frameWidth: 32, frameHeight: 32 });
    this.load.spritesheet('tile_tree', `graphics/builds/object_tree.png?v=${v}`, { frameWidth: 32, frameHeight: 32 });
    this.load.spritesheet('tile_flowers_1', `graphics/builds/Flowers1.png?v=${v}`, { frameWidth: 32, frameHeight: 32 });
    this.load.spritesheet('tile_flowers_2', `graphics/builds/Flowers2.png?v=${v}`, { frameWidth: 32, frameHeight: 32 });
    // El agua no se carga como spritesheet — su recorte en cuadros no
    // está confirmado (ver drawRealWaterTile en TileVisuals.js), así que
    // se carga como imagen simple y se recorta por posición.
    this.load.image('tile_water', `graphics/builds/water.png?v=${v}`);
    // Relleno sólido para paredes de edificio (drawBuildingWall en
    // TileVisuals.js) — imagen simple, no hace falta spritesheet.
    this.load.image('tile_black', `graphics/builds/Black.png?v=${v}`);
  }

  async create() {
    SPECIES = this.cache.json.get('speciesData') || {};
    NPC_SPAWNS = this.cache.json.get('npcSpawnsData') || {};
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
    // Mismo chequeo para las texturas de tiles (16 cuadros esperados en
    // grilla 4x4) — tile_water no se valida acá porque se cargó como
    // imagen simple, no spritesheet (ver PreloadScene.preload()).
    validateCharacterSpritesheet(this, 'tile_door');
    validateCharacterSpritesheet(this, 'tile_boulder');
    if (validateCharacterSpritesheet(this, 'tile_tree') && !this.anims.exists('tile_tree_sway')) {
      // Los 16 cuadros en secuencia, en loop continuo — no hay una
      // convención de direcciones acá (a diferencia de people/), es
      // simplemente el ciclo completo de balanceo por viento.
      this.anims.create({
        key: 'tile_tree_sway',
        frames: this.anims.generateFrameNumbers('tile_tree', { start: 0, end: 15 }),
        frameRate: 6,
        repeat: -1,
      });
    }
    // Flores: 5 cuadros (variantes de color), no 16 — no son una
    // animación, drawFlowerDecoration() elige un cuadro fijo al azar.
    validateCharacterSpritesheet(this, 'tile_flowers_1', 5);
    validateCharacterSpritesheet(this, 'tile_flowers_2', 5);

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
        // OJO: antes, si el mapa guardado no era válido (data.mapKey no
        // coincidía con ningún MAPS conocido), TODA la restauración se
        // saltaba — incluyendo characterCreated y appearance — y el
        // jugador volvía a "personaje nuevo" aunque estuviera guardado
        // en la base de datos. Se desacopla a propósito: si el mapa no es
        // válido, solo la POSICIÓN cae a un valor seguro (spawn de
        // overworld); characterCreated/appearance/party/inventory se
        // restauran siempre que la petición haya respondido bien, sin
        // depender de que el mapKey en particular sea válido.
        const mapaValido = !!MAPS[data.mapKey];
        initial = {
          mapKey: mapaValido ? data.mapKey : 'overworld',
          x: mapaValido ? data.x : MAPS.overworld.spawn.x,
          y: mapaValido ? data.y : MAPS.overworld.spawn.y,
          party: data.party || [],
          inventory: data.inventory || { runa_captura: 5 },
          appearance: data.appearance || defaultAppearance,
          characterCreated: !!data.characterCreated,
        };
        if (!mapaValido) {
          console.warn(`Bifrost: el mapa guardado ("${data.mapKey}") ya no existe — se usa overworld como respaldo, pero se conserva tu personaje y equipo.`);
        }
      } else {
        console.warn(`Bifrost: load_game.php respondió HTTP ${res.status} — se arranca con valores por defecto (puede pedir crear personaje de nuevo).`);
      }
    } catch (err) {
      console.warn('Bifrost: no se pudo contactar al servidor para cargar la partida — se arranca con valores por defecto.', err);
      // Backend no disponible: arrancamos igual con valores por defecto.
    }

    this.scene.start(decideNextScene(initial), initial);
  }
}
