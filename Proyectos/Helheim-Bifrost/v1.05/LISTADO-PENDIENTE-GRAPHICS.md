# Listado de archivos PNG pendientes — Bifrost (01-09-2026)

Todo lo que falta para texturizar por completo el mapa y los personajes,
organizado por carpeta tal como me la describiste. Lo que **no** aparece
acá es porque ya está recibido, verificado e integrado — ver
`data/graphics-catalog.json` para el detalle exacto de cada archivo ya
integrado.

## 📐 Formato estandarizado (a partir de ahora, para todo lo nuevo)

**128×192px, grilla 4×4, 32×48px por cuadro** — el mismo formato ya
confirmado con `male/001.png`. Aplica a **todo** archivo nuevo de
`Characters/` (personas, animales, criaturas). Fila 0 = mirando/avanzando
hacia abajo, fila 1 = izquierda, fila 2 = derecha, fila 3 = arriba
(alejándose) — 4 cuadros de caminata por fila. Única excepción de
convención de filas: `Characters/tree/`, donde cada fila es una etapa de
crecimiento en vez de una dirección (ver más abajo).

**Nota importante:** `female/001.png` actual está en el formato viejo
(256×256px, 64×64px por cuadro) — no calza con el estándar nuevo. Si
vas a reemplazar los 3 archivos de `female/` de todos modos, hazlo en el
formato nuevo (128×192) y avísame para actualizar la configuración de
carga correspondiente.

---

## graphics/Autotiles/
- [ ] `floorcave.png` — piso de interior de cuevas
- [ ] `floorsand.png` — piso tipo arena

*(Formato: textura de terreno, no personaje — no sigue la grilla 4×4.
Si tiene variantes/animación, cuéntame cómo está armada antes de
mandarla, para no repetir el error que tuvimos con el agua.)*

## graphics/Characters/people/
- [ ] `male/004.png` — **reemplazar**: el archivo recibido el 04-09-2026
      se parecía a un diseño de superhéroe con capa reconocible, no se
      integró. Necesita un diseño genuinamente original.
- [ ] `female/004.png` — a confirmar: es idéntico a `female/002.png`
      (mismo archivo, probablemente sin querer al armar el paquete). Si
      quieres una 4ta opción femenina realmente distinta, mándala.

*(✅ Recibido y corregido el 04-09-2026: `male/002.png`, `male/003.png`,
`female/002.png`, `female/003.png` — arte real distinto, ya integrado.)*

## graphics/Characters/animals/
- [ ] `bats/bat001.png`
- [ ] `bird/bird001.png`
- [ ] `dogs/streetdog1.png`, `streetdog2.png`, `streetdog3.png`, `streetdog4.png`
- [ ] `horse/horse001.png`, `horse002.png` (macho/hembra)
- [ ] `fish/` — cantidad de slots por confirmar
- [ ] `insects/` — cantidad de slots por confirmar
- [ ] `sheep/` — cantidad de slots por confirmar
- [ ] `worm/` — cantidad de slots por confirmar

## graphics/Characters/tree/
*(NO sigue la convención de direcciones — cada fila es una etapa de
crecimiento del mismo árbol, ej. brote → tallo → ... Cada archivo es un
árbol distinto con su propia progresión completa.)*
- [ ] `001.png` a `006.png` (6 árboles)

## graphics/Characters/npc_variety/
- [ ] `npc/001.png` a `009.png` (9 NPCs genéricos ambientales)
- [ ] `police/001.png` (1)

## graphics/Characters/mythical_creatures/
*(Vinculadas al evento de audio "pactar con animales míticos" — una por
cada uno de los 9 templos elementales.)*
- [ ] `001.png` a `009.png` — sugerido: aire, oscuridad, electricidad,
      fuego, hielo, luz, mente, fuerza, agua (en ese orden)

## graphics/Characters/phantoms/
- [ ] `001.png` a `013.png` (13)

## graphics/Characters/dragons/
- [ ] `001.png` a `013.png` (13)

## graphics/Characters/devils/
- [ ] `001.png` a `013.png` (13)

## graphics/Characters/plants/
- [ ] `001.png` a `020.png` (20)

## graphics/Characters/seamonsters/
- [ ] `001.png` a `004.png` (4)

## graphics/Pictures/
*(Retrato de solo rostro, sin cuerpo — no sigue la grilla 4×4, es una
sola imagen. Dimensiones exactas por confirmar cuando llegue el primero.)*
- [ ] `female/001.png`, `002.png`, `003.png`
- [ ] `male/001.png`, `002.png`, `003.png`

---

## Ya integrado — no hace falta reenviar nada de esto
`Animations/` completa (bkgindex1, bkgindex2, clouds) · `Builds/`
completa (doors1-3, boulder, árbol-viento, agua, flores 1-2, negro) ·
**terreno base** (tierra/camino, pasto alto, arena — recibido 04-09-2026)
· `Characters/people/male/001-003.png` y `female/001-003.png` (arte
real; `female/001` sigue en formato viejo 256×256, el resto ya en el
formato nuevo 128×192) · **pared de edificio y mobiliario** (panel de
piedra, escritorio, cama — del kit `Tiles/`) · **10 criaturas de
combate** (`Battlers/` — glenys, hex, ley, linail, oratio, ouzo, prime,
wyvera —, `dragon/dragon.png`, `littledemon/001.png`), ya en
`data/species.json` y pobladas en los niveles nuevos del Gran Árbol y
la Cueva de Don Emilio.

## Total pendiente
**~131 archivos** (contando los que tienen cantidad confirmada; `fish`,
`insects`, `sheep`, `worm` y las 2 texturas de terreno todavía sin
cantidad/formato definido).
