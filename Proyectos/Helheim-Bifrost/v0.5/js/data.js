// Catálogo de criaturas: contenido 100% original, organizado en 8 tipos
// propios (no los de ninguna franquicia existente), 3 criaturas cada uno,
// pensadas como una progresión ligera dentro de su tipo (cría -> joven ->
// adulta). Coincide con el catálogo espejo de api/config.php, necesario
// porque las batallas PvP se calculan en el servidor.
const SPECIES = {
  // --- Fuego: inspirados en dragones ---
  fire_1: {
    name: 'Chispodrilo', type: 'fuego', color: 0xe0703a, hp: 22, atk: 12, def: 7,
    description: 'Un dragón diminuto que apenas empieza a soltar chispas por la nariz.',
  },
  fire_2: {
    name: 'Braseryx', type: 'fuego', color: 0xd94f2b, hp: 28, atk: 16, def: 11,
    description: 'Dragón joven con una cresta de brasas que nunca se apaga.',
  },
  fire_3: {
    name: 'Vulcanor', type: 'fuego', color: 0xb83214, hp: 35, atk: 21, def: 15,
    description: 'Dragón adulto cuyas alas parecen ríos de lava en movimiento.',
  },

  // --- Agua: inspirados en serpientes marinas ---
  water_1: {
    name: 'Marejino', type: 'agua', color: 0x5ea8c9, hp: 24, atk: 9, def: 10,
    description: 'Pequeña serpiente marina translúcida que vive en charcas de marea.',
  },
  water_2: {
    name: 'Corrientauro', type: 'agua', color: 0x3a6ea5, hp: 30, atk: 13, def: 14,
    description: 'Serpiente marina de aletas onduladas, nada más rápido que la corriente.',
  },
  water_3: {
    name: 'Abisalgo', type: 'agua', color: 0x1f4e79, hp: 38, atk: 17, def: 19,
    description: 'Serpiente de aguas profundas; casi nadie la ha visto completa.',
  },

  // --- Planta: inspirados en insectos ---
  grass_1: {
    name: 'Brotalín', type: 'planta', color: 0x7cb342, hp: 23, atk: 10, def: 9,
    description: 'Insecto pequeño recién salido del capullo, con hojas tiernas en el lomo.',
  },
  grass_2: {
    name: 'Espigón', type: 'planta', color: 0x4c9a2a, hp: 29, atk: 14, def: 13,
    description: 'Insecto de antenas floreadas y caparazón cubierto de musgo.',
  },
  grass_3: {
    name: 'Follascorpio', type: 'planta', color: 0x2e6b1f, hp: 36, atk: 18, def: 17,
    description: 'Insecto grande con pinzas envueltas en enredaderas espinosas.',
  },

  // --- Electricidad: inspirados en equidnas ---
  electric_1: {
    name: 'Chispequín', type: 'electricidad', color: 0xe8c268, hp: 21, atk: 13, def: 6,
    description: 'Cría de equidna cuyas espinas apenas cargan un cosquilleo eléctrico.',
  },
  electric_2: {
    name: 'Voltígero', type: 'electricidad', color: 0xd4a017, hp: 27, atk: 17, def: 10,
    description: 'Equidna adulta: cada espina chisporrotea con cada paso que da.',
  },
  electric_3: {
    name: 'Amperidna', type: 'electricidad', color: 0xb8860b, hp: 33, atk: 22, def: 13,
    description: 'Equidna gigante; sus púas liberan chispas visibles a distancia.',
  },

  // --- Lucha: inspirados en artes marciales ---
  fighting_1: {
    name: 'Puñolet', type: 'lucha', color: 0xa0522d, hp: 24, atk: 13, def: 8,
    description: 'Aprendiz de artes marciales que entrena sus puños desde el amanecer.',
  },
  fighting_2: {
    name: 'Katáfaro', type: 'lucha', color: 0x8b3a1a, hp: 30, atk: 17, def: 12,
    description: 'Luchador de cinturón trenzado cuyas patadas rara vez fallan.',
  },
  fighting_3: {
    name: 'Granmaestro', type: 'lucha', color: 0x6b2c12, hp: 37, atk: 22, def: 16,
    description: 'Maestro veterano; se dice que un solo golpe suyo basta.',
  },

  // --- Volador: inspirados en aves ---
  flying_1: {
    name: 'Plumín', type: 'volador', color: 0xa8d8e8, hp: 20, atk: 11, def: 7,
    description: 'Polluelo emplumado que todavía tropieza al intentar despegar.',
  },
  flying_2: {
    name: 'Ventizarro', type: 'volador', color: 0x7ec4dd, hp: 26, atk: 15, def: 10,
    description: 'Ave joven y veloz que surca corrientes de aire sin esfuerzo.',
  },
  flying_3: {
    name: 'Tormenpluma', type: 'volador', color: 0x4a90b8, hp: 32, atk: 19, def: 13,
    description: 'Ave mayor cuyo batir de alas desata pequeñas tormentas.',
  },

  // --- Oscuro: inspirados en gatos ---
  dark_1: {
    name: 'Sombrigato', type: 'oscuro', color: 0x5b4b8a, hp: 22, atk: 12, def: 8,
    description: 'Gatito sigiloso que parece fundirse con cualquier sombra.',
  },
  dark_2: {
    name: 'Penumbraz', type: 'oscuro', color: 0x3d2f5c, hp: 28, atk: 16, def: 11,
    description: 'Gato adulto de mirada penetrante y pasos que nadie escucha.',
  },
  dark_3: {
    name: 'Eclipsino', type: 'oscuro', color: 0x241b38, hp: 35, atk: 20, def: 15,
    description: 'Su pelaje absorbe la luz por completo; solo se le ven los ojos.',
  },

  // --- Diurno: inspirados en perros ---
  day_1: {
    name: 'Solete', type: 'diurno', color: 0xf2c14e, hp: 23, atk: 11, def: 9,
    description: 'Cachorro alegre cuyo pelaje dorado brilla bajo el sol.',
  },
  day_2: {
    name: 'Auroraz', type: 'diurno', color: 0xe8a33d, hp: 29, atk: 15, def: 12,
    description: 'Perro guardián leal, siempre rodeado de un aura cálida.',
  },
  day_3: {
    name: 'Radialbo', type: 'diurno', color: 0xd4822a, hp: 36, atk: 19, def: 16,
    description: 'Su pelaje irradia una luz cálida, como el sol del mediodía.',
  },
};

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
