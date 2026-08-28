// Catálogo de criaturas. Coincide con los datos sembrados en sql/schema.sql.
// Son especies originales de ejemplo — reemplázalas por tu propio diseño.
const SPECIES = {
  mon_fire: { name: 'Flamlet', color: 0xd94f2b, hp: 22, atk: 12, def: 8 },
  mon_water: { name: 'Aquabub', color: 0x3a6ea5, hp: 24, atk: 9, def: 12 },
  mon_grass: { name: 'Leafkin', color: 0x4c9a2a, hp: 23, atk: 10, def: 11 },
};

function randomSpeciesKey() {
  const keys = Object.keys(SPECIES);
  return keys[Math.floor(Math.random() * keys.length)];
}

function makeMonsterInstance(speciesKey) {
  const base = SPECIES[speciesKey];
  return {
    speciesKey,
    name: base.name,
    color: base.color,
    maxHp: base.hp,
    hp: base.hp,
    atk: base.atk,
    def: base.def,
  };
}
