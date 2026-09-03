# Plan técnico: Graphics y Audio (31-08-2026)

Este documento detalla CÓMO se va a implementar cada categoría de
`data/graphics-catalog.json` y `data/audio-events.json` — la parte que
todavía no se puede construir porque **no existen los archivos físicos en
el proyecto todavía**, solo su catálogo (nombre, rol, convención de
grilla). Referenciado desde `ROADMAP-ARQUITECTURA.md` (ítems 1 y 6).

**Ya implementado (no depende de archivos):** el cambio de jugabilidad de
apariencia (elegir 1 de 3 opciones preestablecidas por género en vez de
color libre) — ver `APPEARANCE_PRESETS` en `CharacterVisual.js`,
`resolve_appearance_preset()` en `config.php`, y
`sql/v1.1-appearance-presets-migration.sql`. Cuando lleguen los archivos
reales de `Characters/people/{gender}/00N.png`, solo hay que reemplazar
el renderizado (hoy: muñeco dibujado con colores del preset) por el
sprite real — el dato guardado (género + número de opción) no cambia.

**✅ Primera capa real implementada (31-08-2026):** `Characters/people/male/001.png`
y `Characters/people/female/001.png` (imágenes conceptuales de prueba, no
arte final) ya están en el proyecto y **`Player.js` ya los usa de
verdad** — no solo el plan:
- `PreloadScene.js` intenta cargar las **6 combinaciones completas**
  (`PEOPLE_SPRITE_COMBOS` en `CharacterVisual.js`: male/female × 1/2/3) y
  registra sus animaciones — las que no tienen archivo real todavía
  fallan la carga (404 esperado, sin romper nada; Phaser sigue con el
  resto de la cola) y quedan usando el dibujo a mano automáticamente. En
  cuanto subas un archivo nuevo con el nombre correcto
  (`graphics/characters/people/{male|female}/00{1|2|3}.png`), empieza a
  usarse solo, sin tocar código de nuevo.
- `Player.js` detecta automáticamente si existe un sprite real para la
  combinación género+preset actual (`scene.textures.exists(...)`) — si
  existe lo usa (sprite animado real), si no cae al dibujo a mano de
  siempre.
- `OverworldScene.createRemotePlayer()` y la sincronización de dirección
  de jugadores remotos usan la misma lógica — un jugador con "chico,
  opción 1" se ve con el sprite real también para los demás jugadores.
- La vista previa en `CharacterCreationScene` también usa el sprite real
  cuando existe.
- **Probado con un mock de la API de Phaser** (sin navegador real): 6
  escenarios en total, incluyendo el cálculo correcto de posición
  vertical con dos tamaños de cuadro distintos.

**🐛 Bug real encontrado y corregido (31-08-2026):** el masculino
(`male/001.png`) mide 128×192px (32×48px por cuadro) y el femenino
(`female/001.png`) mide **256×256px (64×64px por cuadro)** — tamaños de
cuadro totalmente distintos entre sí, no un tamaño único para todo
`Characters/people/`. La primera versión de este sistema cargaba las 6
combinaciones asumiendo siempre 32×48px — al probar un perfil que no
fuera `male/001` con esa suposición equivocada, Phaser recortaba el
archivo real en pedazos incorrectos sin avisar (con la imagen femenina,
habría generado 40 "cuadros" de 8×5 en vez de los 16 esperados de 4×4),
lo que se veía como comportamiento "raro" sin ningún error claro en
consola.

**Corrección aplicada:**
1. `PEOPLE_SPRITE_COMBOS` ahora define `frameWidth`/`frameHeight` **por
   combinación**, no un tamaño único global — 32×48 para las 3 del
   masculino (confirmado con `male/001.png`), 64×64 para las 3 del
   femenino (confirmado con `female/001.png`). Las 4 combinaciones sin
   archivo confirmado todavía (`male_2`, `male_3`, `female_2`,
   `female_3`) usan el tamaño de su propia carpeta como mejor estimación
   — se corrige en cuanto llegue cada archivo real.
2. **Red de seguridad nueva:** `validateCharacterSpritesheet()` revisa,
   después de cargar cada spritesheet, que haya resultado en exactamente
   16 cuadros. Si no calza (mismo problema que causó el bug, o cualquier
   archivo futuro con un tamaño distinto al supuesto), se quita esa
   textura antes de que el resto del código pueda usarla — cae al dibujo
   a mano automáticamente en vez de mostrar algo mal recortado. Probado
   con un mock que reproduce el escenario exacto del bug real (256×256
   cargado con el tamaño del masculino → 40 cuadros detectados
   correctamente como inválidos).

**Estado real de los 6 archivos (31-08-2026, actualizado):** las 6
combinaciones ahora existen como archivo — pero `male/002.png`,
`male/003.png`, `female/002.png` y `female/003.png` son **copias
literales** de `male/001.png`/`female/001.png` respectivamente (mismo
contenido, no arte distinto). Se completaron así a pedido explícito
(confirmaste que la estructura técnica es la misma para las 3 opciones
de cada carpeta) para que el sistema funcione de punta a punta sin caer
al dibujo a mano en ninguna combinación — esto también resolvió el
reporte de que "seleccionar un perfil masculino distinto de 001 se
comportaba raro": ahora las 3 opciones de cada género muestran
consistentemente un sprite real (antes 2 de las 3 caían al dibujo a
mano, que se ve muy distinto en estilo).

**Importante:** hoy, elegir cualquiera de las 3 opciones masculinas se
ve visualmente IGUAL entre sí (mismo caso para las 3 femeninas) — es el
mismo archivo duplicado, no 3 diseños distintos. En cuanto envíes arte
realmente distinto para cada opción, se reemplaza el archivo
correspondiente (`graphics/characters/people/{male|female}/00{2|3}.png`)
sin tocar nada de código — el sistema ya está preparado para eso.

## ⚠️ Categorías nuevas (31-08-2026) — pendientes de arte original

Un paquete grande de imágenes recibido resultó ser en su mayoría el pack
de recursos de Pokémon Essentials — **no se integró ningún archivo de
ese paquete al proyecto**, incluso los que tenían nombres genéricos (ej.
`horse001.png`, que coincidía exacto con la ruta ya catalogada, resultó
ser un diseño de Pokémon al revisarlo visualmente). Ver la nota de
seguridad al inicio de `data/graphics-catalog.json` para el detalle
completo.

Lo que sí se rescató de esa entrega fue la **estructura de carpetas y
convención de numeración**, que revela un catálogo de contenido más
ambicioso del que teníamos documentado — nuevas categorías de criaturas
más allá de las 24 de `data/species.json`:

- `mythical_creatures` — posible vínculo con el evento de audio
  `mythical_pact` ("al pactar o jurar con alguno de tus animales
  míticos/mágicos") — 9 slots sugeridos, uno por templo elemental.
- `phantoms`, `dragons`, `devils` — 13 slots sugeridos cada una.
- `plants`, `seamonsters` — cantidad de slots por confirmar.
- `npc_variety` (gente ambiental genérica) y `animals` ampliado (fish,
  insects, sheep, worm, además de bats/bird/dogs/horse).

Todo esto quedó catalogado en `data/graphics-catalog.json` como
**placeholders numéricos genéricos** (`{n}.png`, sin ningún nombre
específico de Pokémon) — listos para recibir arte 100% original cuando
esté disponible. Ninguna de estas categorías tiene código de carga en
`PreloadScene.js` todavía (a diferencia de `people/`, que sí lo tiene
porque ya hay 2 archivos reales verificados) — no tiene sentido escribir
código de carga para contenido que todavía no existe.

**✅ Las 3 preguntas quedaron resueltas (31-08-2026):**
1. Cantidad de slots confirmada tal cual: 9 para `mythical_creatures`,
   13 para `phantoms`/`dragons`/`devils`.
2. `plants`: 20 slots, `seamonsters`: 4 slots — conteo real del listado
   de archivos recibido (se descontaron duplicados accidentales
   marcados "(copia)" en el paquete original).
3. Confirmado: `mythical_creatures` se vincula a los 9 templos
   elementales, un guardián/pacto por templo — ver `ordenPorTemplo` en
   `data/graphics-catalog.json`.

Con esto, el catálogo de categorías nuevas queda cerrado en cuanto a
estructura — falta únicamente el arte original para cada slot.

## ✅ Edificios: Comisaría, Hospital, Hotel (01-09-2026)

Primer contenido de "edificio" en el mapa — no hay archivos reales de
pared/techo todavía, así que se creó un **tile tipo 9** (pared de
edificio) que usa `Black.png` (ya verificado) como base real + una
ventana dibujada a mano encima (aparece en ~70% de los tiles de pared,
para que no se vea como una grilla perfecta). Cada edificio es un bloque
de 4×3 tiles, con una sola puerta (tile tipo 4, la misma textura real ya
integrada) en el medio de la pared sur:

| Edificio | Posición (x, y) | Puerta |
|---|---|---|
| Comisaría | 15-18, 12-14 | (16, 14) |
| Hospital | 30-33, 12-14 | (31, 14) |
| Hotel | 45-48, 12-14 | (46, 14) |

Las puertas por ahora son solo visuales — no están conectadas a ningún
interior (no existe ese mapa todavía). Caminar hasta la puerta no hace
nada (no hay entrada en `warps` para esa posición), lo cual es un estado
intermedio seguro: no rompe nada, y conectar un interior real más
adelante es agregar una entrada a `warps` sin tener que rediseñar el
edificio.

**✅ Actualización (01-09-2026): interiores conectados.** Los 3 edificios
ahora tienen un interior real (`comisaria_interior`, `hospital_interior`,
`hotel_interior` en `js/maps.js`) — una habitación simple de 9×7 (paredes
tile 9, piso tile 0, puerta de salida tile 4), sin decoración específica
todavía (no hay archivos de mobiliario/interiores reales). Cada uno tiene
un NPC atendiendo (`data/npc-spawns.json`), reutilizando sprites ya
verificados. Validado con `check-maps.js` (conectividad 36/36 en cada
interior, los 3 nuevos warps de entrada verificados) y
`test-npc-wander.js` (los 3 NPCs nuevos, radio de merodeo 1 dado lo chico
del cuarto).

**✅ Actualización (01-09-2026): interiores decorados.** Cada interior
ahora tiene mobiliario simple, dibujado a mano (`drawDecoration()` en
`TileVisuals.js` — no hay archivos de mobiliario real todavía):
escritorio de recepción en los 3, estrella policial en la Comisaría,
cruz médica y una cama en el Hospital, una cama en el Hotel. Puramente
visual (no bloquea el paso, mismo criterio que las flores) — probado con
mock las 4 categorías de decoración más el caso de un tipo desconocido
(no rompe nada). `check-maps.js` confirma que la conectividad de los 3
interiores sigue en 36/36 tras agregar las decoraciones.

**Sobre el terreno base (pasto/camino/arena/cueva):** sigue sin archivos
reales — `floorcave.png`/`floorsand.png` nunca llegaron en ninguna
entrega. No hay nada que reemplazar ahí todavía; el dibujo a mano actual
sigue siendo lo único disponible para esos tipos de tile.

**Validado con cuidado, porque agregar tiles bloqueados a un mapa ya
verificado es sensible:**
- `check-maps.js`: conectividad exacta 4134/4134 (bajó de 4167, la
  diferencia son exactamente 33 = 3 edificios × 11 tiles de pared cada
  uno — 12 tiles por edificio menos 1 puerta transitable).
- **Encontré y corregí un error propio en el camino:** al reescribir el
  layout con un script, el primer intento usó una búsqueda de texto
  ambigua para encontrar dónde terminaba el arreglo del mapa, y se comió
  por accidente el bloque `spawn`/`warps`/`landmarks` completo de
  Pueblo Origen. Lo noté porque `check-maps.js` explotó con un error
  claro (`Cannot read properties of undefined`) en vez de fallar en
  silencio — se restauró desde git y se rehizo apuntando a los números
  de línea exactos del arreglo (65 filas, límites confirmados antes de
  tocar nada), en vez de confiar en una búsqueda de texto.
- **Segundo error encontrado en el camino:** un `git checkout` de
  recuperación también revirtió sin querer un cambio anterior a
  `isTileBlocked()` (el que agregaba el tile 9 como bloqueado) —
  `test-npc-wander.js` lo detectó solo: un NPC "terminó" dentro de una
  pared tras la simulación, algo que no debería pasar nunca. Se corrigió
  y se volvió a validar todo desde cero.
- 3 NPCs nuevos ubicados junto a cada puerta (`data/npc-spawns.json`),
  probados con `test-npc-wander.js` — ninguno atraviesa las paredes.

## ✅ Flores decorativas y NPCs data-driven (31-08-2026)

- **Flowers1.png / Flowers2.png**: integradas como decoración ocasional
  (~12% de probabilidad, determinista por posición) sobre camino (tile
  0) y pasto alto (tile 1) — `drawFlowerDecoration()` en
  `TileVisuals.js`. Alternan entre las 2 texturas y sus 5 variantes de
  color al azar, pero el mismo tile siempre da el mismo resultado entre
  visitas (mismo criterio que el resto de la decoración del mapa).
  Probado con mock: proporción real de aparición (~11.5%) cerca del 12%
  esperado, respaldo correcto si la textura no carga, determinismo
  confirmado en 2 llamadas separadas al mismo tile.
- **NPCs ahora data-driven**: `data/npc-spawns.json` reemplaza la lista
  hardcodeada que tenía `OverworldScene.spawnAmbientNPCs()` — agregar o
  mover un NPC ahora es editar el JSON, no tocar código (ítem 4 del
  roadmap). `scripts/test-npc-wander.js` (nuevo, en `npm test`) valida
  que cada NPC definido ahí tenga un punto de origen transitable y se
  mantenga dentro de su radio de merodeo tras 500 pasos simulados,
  contra los mapas reales del proyecto.

## Antes de escribir código de carga real

Para CADA categoría de abajo hace falta al menos un archivo real subido
al proyecto (en `graphics/<subcarpeta>/...`, misma ruta relativa que
`data/graphics-catalog.json`) para poder:
1. Confirmar las dimensiones reales en píxeles de cada cuadro (la
   descripción dice "cuadradas" pero no el tamaño exacto — probar con
   32×32, 48×48 o el que corresponda).
2. Cargarlo de verdad con `this.load.spritesheet(...)` y confirmar que
   Phaser lo recorta como se espera, antes de dar por buena la
   integración (misma disciplina de "probar con datos reales, no solo
   que el código compile" que usamos en todo el proyecto).

## 1. `Animations/` — fondo de index.php + nubes
**✅ Implementado (31-08-2026).**

- **bkgindex1.png / bkgindex2.png**: fondo de `index.php`, verificados
  como genéricos (patrones abstractos, sin nada de Pokémon). Implementado
  con dos `<div>` superpuestos (position: absolute) alternando `opacity`
  vía `@keyframes` CSS, desfasados con `animation-delay` para lograr el
  fundido cruzado sin JS. Respeta `prefers-reduced-motion`. Si las
  imágenes no cargan, el fondo sólido de `.gb-hero` sigue funcionando
  igual (las capas no reemplazan nada, solo se superponen).
- **clouds.png**: verificado como genérico. Implementado como
  `background-repeat: repeat-x` con `background-position` animado por
  `@keyframes`, flotando lento sobre el hero.
- Cache-busting con `$ASSET_VERSION` vía estilo inline (CSS puro no tiene
  acceso a la variable de PHP).

## 2. `Autotiles/` — texturas de terreno
**🔶 Parcial — agua integrada, piso de cueva/arena sin recibir todavía.**

- **water.png**: recibido y verificado (genérico). Su recorte exacto en
  cuadros de animación **no está confirmado** (a diferencia de
  puertas/roca/personajes, que sí vienen en la convención clara de
  grilla 4×4) — en vez de arriesgar un recorte equivocado (mismo tipo de
  bug que ya tuvimos con los sprites de personaje, ver más abajo), se
  cargó como imagen simple (no spritesheet) y cada tile de agua toma un
  recorte de 32×32 de una posición determinista distinta dentro de la
  tira (`drawRealWaterTile()` en `TileVisuals.js`) — variedad visual sin
  depender de saber su estructura interna exacta.
- **floorcave.png, floorsand.png**: no llegaron en la entrega recibida —
  siguen pendientes. El plan original (textura Phaser + `TileSprite` o
  reemplazo directo del `fillStyle`) sigue vigente para cuando lleguen.
- **Flowers1.png, Flowers2.png**: recibidas y verificadas (genéricas,
  160×32px = 5 cuadros de 32×32 cada una), pero **no integradas
  todavía** — candidatas a decoración suelta sobre tiles de pasto/camino.
- **Black.png**: recibido y verificado, sin uso claro identificado
  todavía (un `fillStyle(0x000000)` ya cubre lo mismo).

Esta categoría es la que más directamente reemplaza el trabajo actual de
`TileVisuals.js` — es el corazón del ítem 1 del roadmap (tilemap).

## 3. `Builds/` — animaciones de 16 cuadros (grilla 4×4)
**🔶 Parcial — puerta y roca integradas (cuadro estático), sin animación de apertura todavía.**

- **doors1.png**: recibido, verificado como genérico, y **cargado como
  textura real** (`tile_door`) — reemplaza `drawWarpGate()` para el tile
  tipo 4. Por ahora se muestra el cuadro 0 (pose cerrada/de reposo); la
  animación de apertura al usar la puerta (`this.anims.create(...)`
  disparada al pisar el tile) queda para una siguiente pasada — el
  reemplazo visual (forma dibujada → textura real) ya es la mejora más
  importante. `doors2.png`/`doors3.png` también verificados como
  genéricos, sin cargar todavía (quedan como variantes futuras, ej. para
  distinguir tipos de edificio).
- **object_boulder.png**: recibido, verificado, y **cargado como textura
  real** (`tile_boulder`) — reemplaza `drawRock()` para el tile tipo 6,
  cuadro estático (no se identificó necesidad de animarlo).
- **object_tree.png**: recibido y verificado como genérico, pero **no
  integrado todavía** — pendiente de decidir cómo combinarlo con la
  variación de tamaño que ya tiene `drawSmallTree()` (`sizeVar`), para no
  perder esa variedad visual entre árboles.

**Validación aplicada:** mismo patrón de respaldo automático que en
personajes (`scene.textures.exists(...)`) — si `tile_door`/`tile_boulder`
no cargan, `TileVisuals.js` cae solo al dibujo a mano. Probado con un
mock de Phaser en 4 escenarios (sin texturas, con las 3 cargadas, mezcla
parcial, tipos de tile sin textura mapeada) — los 4 pasaron.

## 4. `Characters/animals/` — fauna ambiental

bats, bird, dogs (4 variantes), horse (macho/hembra) — mismo patrón de
grilla 4×4 = 4 direcciones × 4 cuadros de caminata que ya usa
`Player.js` conceptualmente (hoy con formas dibujadas, no sprites). Estos
serían las primeras entidades no controlables por el jugador — buen
punto de entrada para el ítem 7 del roadmap (máquinas de estado: IDLE,
PATROL) ya con sprite real en vez de esperar más.

## 5. `Characters/people/` — jugador y NPCs humanos
**🔶 2 de 6 confirmados — ver nota al inicio del documento.**

female/male (3 opciones cada uno) + NPC (3 variantes). Reemplaza
`buildCharacterVisual()` de `CharacterVisual.js`. Confirmados y
funcionando: `male/001.png` (128×192, 32×48/cuadro) y `female/001.png`
(256×256, 64×64/cuadro — **tamaño de cuadro distinto al masculino**, no
asumir que todos miden lo mismo). Falta repetir para las otras 4
combinaciones cuando lleguen sus archivos — el mecanismo ya probado es:
1. `PreloadScene.js` carga el spritesheet (`this.load.spritesheet`) con
   el `frameWidth`/`frameHeight` de `PEOPLE_SPRITE_COMBOS`
   (`CharacterVisual.js`) y registra sus 4 animaciones
   (`defineCharacterAnimations()`) — ya recorre las 6 combinaciones en un
   bucle, así que un archivo nuevo con el nombre correcto no necesita
   ninguna línea de código adicional. Sí hay que **actualizar el
   `frameWidth`/`frameHeight` de esa combinación en `PEOPLE_SPRITE_COMBOS`**
   una vez confirmadas las dimensiones reales del archivo (por ahora usan
   el mismo tamaño que el preset 1 de su carpeta como estimación).
2. `validateCharacterSpritesheet()` protege contra que un tamaño
   equivocado se use silenciosamente — si no calza, cae al dibujo a mano
   en vez de mostrar algo mal recortado (ver el bug real que motivó
   esto, arriba).
3. `Player.js` y `OverworldScene.createRemotePlayer()` detectan solos
   (`scene.textures.exists(...)`) si existe sprite real para esa
   combinación género+preset — no hace falta tocarlos de nuevo por cada
   archivo nuevo, ya generalizan automáticamente.
4. **El dato guardado no cambia** — sigue siendo `gender + preset (1-3)`,
   por eso se implementó ya el selector de presets sin esperar los
   archivos.
5. `CharacterVisual.js` (el dibujo a mano) queda como *fallback* — si el
   sprite no carga por algún motivo, seguir mostrando el muñeco dibujado
   en vez de nada, mismo espíritu que el resto del proyecto ("degradar
   con gracia" en vez de romper).

## 6. `Characters/tree/` — árboles con etapas de vida

Caso especial: la grilla 4×4 NO son direcciones, son etapas de
crecimiento (fila) × balanceo por viento (columna). 6 árboles distintos
(001-006). Reemplazaría el árbol grande y los árboles sueltos de
`TileVisuals.js` (`drawBigTree()`, `drawSmallTree()`) con sprites reales,
eligiendo la fila según qué tan "grande" deba verse ese árbol en el mapa
(quizás ligado a la variación de tamaño que ya existe en
`drawSmallTree()`, `sizeVar`).

## 7. `Pictures/` — retratos de rostro

female/male (3 cada uno), sin cuerpo. Uso directo: reemplazar el
`buildCharacterVisual()` en miniatura que hoy se muestra como vista
previa en `CharacterCreationScene.showPresetStep()` por el retrato real
correspondiente a cada opción — cambio pequeño y aislado una vez estén
los archivos.

## Audio — `data/audio-events.json`

Catalogado, sin `AudioManager` todavía (no hay archivos `.ogg` reales en
el proyecto). Cuando existan:
1. `PreloadScene.js` los carga con `this.load.audio(key, url)`.
2. Un `AudioManager` nuevo (`js/core/audioManager.js`, sin depender de
   Phaser directamente en la lógica de "qué evento dispara qué sonido",
   mismo patrón que `battleRules.js`) expone algo como
   `playEvent('menu_toggle')` que las escenas llaman en los puntos
   correspondientes (abrir/cerrar menú, entrar a un mapa nuevo, etc.).
3. BGM (música de fondo) se maneja con un solo `Phaser.Sound` global que
   hace crossfade al cambiar de pista, no una instancia nueva por mapa.

## Orden sugerido cuando lleguen los archivos

No conviene intentar todo de una vez (mismo principio de "nada de big
bang" del resto del roadmap). Progreso real vs. el orden original:
1. **Autotiles + tilemap** (ítem 1) — 🔶 parcial: agua/puerta/roca con
   textura real, falta piso de cueva/arena (no recibidos todavía) y
   terminar de decidir árbol-viento/flores.
2. **Characters/people** — 🔶 parcial: 2 de 6 combinaciones con sprite
   real (male/female, preset 1).
3. **Builds** (puertas, roca, árbol-viento) — 🔶 parcial: puerta y roca
   integradas, árbol-viento verificado pero sin integrar.
4. **Characters/animals + tree** — ⏳ sin empezar, sigue bloqueado por
   contenido genuinamente original (todo lo recibido en `animals/` del
   último paquete resultó ser Pokémon, incluido `horse001.png`).
5. **Animations (index.php) + Pictures + Audio** — ✅ `Animations/`
   implementado (fondo + nubes). `Pictures/` y `Audio` siguen sin
   archivos recibidos.

**Pendiente de recibir (no llegaron en la entrega del 31-08-2026):**
`floorcave.png`, `floorsand.png`, todo `Pictures/`, y todo `Audio/`.
