// Catálogo de criaturas: contenido 100% original, organizado en 8 tipos
// propios (no los de ninguna franquicia existente), 3 criaturas cada uno,
// pensadas como una progresión ligera dentro de su tipo (cría -> joven ->
// adulta).
//
// El catálogo YA NO vive hardcodeado acá (ítem 4 de
// ROADMAP-ARQUITECTURA.md) — se carga desde data/species.json, la misma
// fuente que lee api/config.php en el servidor. PreloadScene.js lo carga
// con el sistema propio de Phaser (this.load.json) y lo asigna acá antes
// de que cualquier otra escena lo necesite (por eso empieza como objeto
// vacío: se puebla en cuanto termina de cargar, siempre antes de que se
// use por primera vez).
let SPECIES = {};

// Posiciones de NPCs ambientales por mapa — se puebla desde
// data/npc-spawns.json en PreloadScene.js, mismo patrón que SPECIES
// (ítem 4 del roadmap: data-driven en vez de hardcodeado en el código
// de la escena). OverworldScene.spawnAmbientNPCs() lo lee.
let NPC_SPAWNS = {};

// Las 3 opciones que se ofrecen al empezar la partida (una cría por tipo
// fuego/agua/planta, siguiendo la tríada clásica de inicial).
const STARTER_KEYS = ['fire_1', 'water_1', 'grass_1'];

function randomSpeciesKey() {
  const keys = Object.keys(SPECIES);
  return keys[Math.floor(Math.random() * keys.length)];
}

function makeMonsterInstance(speciesKey) {
  const base = SPECIES[speciesKey];
  return {
    speciesKey,
    name: base.name,
    type: base.type,
    color: base.color,
    maxHp: base.hp,
    hp: base.hp,
    atk: base.atk,
    def: base.def,
  };
}
