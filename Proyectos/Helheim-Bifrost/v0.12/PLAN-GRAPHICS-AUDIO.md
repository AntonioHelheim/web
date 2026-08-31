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
(imagen conceptual de prueba, no arte final) ya está en el proyecto
(`graphics/characters/people/male/001.png`, 128×192px confirmado, grilla
4×4 = 32×48px por cuadro) y **`Player.js` ya lo usa de verdad** — no solo
el plan:
- `PreloadScene.js` carga el spritesheet y registra las 4 animaciones de
  caminata (`defineCharacterAnimations()`).
- `Player.js` detecta automáticamente si existe un sprite real para la
  combinación género+preset actual (`scene.textures.exists(...)`) — si
  existe lo usa (sprite animado real), si no cae al dibujo a mano de
  siempre. Ningún otro preset (girl 1-3, boy 2-3) tiene archivo todavía,
  así que siguen usando el dibujo — nada se rompió para esos casos.
- `OverworldScene.createRemotePlayer()` y la sincronización de dirección
  de jugadores remotos usan la misma lógica — un jugador con "chico,
  opción 1" se ve con el sprite real también para los demás jugadores.
- La vista previa en `CharacterCreationScene` también usa el sprite real
  cuando existe.
- **Probado con un mock de la API de Phaser** (sin navegador real): 5
  escenarios — sprite disponible, sprite no disponible (cae al dibujo),
  cambio de dirección, reproducir/detener la animación de caminata al
  moverse, y cambiar a un preset sin sprite real desde uno que sí tenía.
  Los 5 pasaron.

**Pendiente por choque de nombres:** la imagen `female/001.png` se subió
al mismo tiempo que `male/001.png`, ambas con el nombre `001.png` — el
servidor de subida se quedó solo con la última, así que se perdió la
femenina. Hay que volver a mandarla (con un nombre distinto para evitar
que se repita, ej. `female_001.png`).

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

- **bkgindex1.png / bkgindex2.png**: fondo de `index.php` con
  transición cruzada entre ambas. Implementación planeada: dos `<img>` o
  `<div>` con `background-image` superpuestos (position: absolute, mismo
  tamaño), alternando `opacity` con una transición CSS cada N segundos
  (ej. 8s), sin JS de por medio salvo un `setInterval` que alterna una
  clase. Si las imágenes no cargan (404), la página debe seguir viéndose
  bien — mantener el fondo sólido actual (`--gb-darkest`) como
  `background-color` de respaldo debajo de las imágenes.
- **clouds.png**: grilla 2×5 (10 cuadros). Implementación planeada: un
  `<div>` con la imagen como sprite sheet CSS (`background-position`
  animado vía `@keyframes` o un pequeño loop JS), flotando lento sobre el
  fondo del hero. Decorativo — no bloquea nada si se agrega después.

## 2. `Autotiles/` — texturas de terreno

- **floorcave.png, floorsand.png**: reemplazan el color plano que hoy
  dibuja `drawTile()` para cueva/arena — se cargarían como una textura
  Phaser (`this.load.image`) y se usarían como `fillStyle` de textura o,
  más simple, un `TileSprite` repetido por celda en vez de
  `add.rectangle(...)`.
- **Flowers1.png, Flowers2.png**: decoración suelta sobre tiles de pasto
  existentes — candidatas a agregarse como un tile nuevo (ej. tipo 9)
  reutilizando el patrón de `TileVisuals.js`.
- **water.png**: dice "secuencia de imágenes" — hay que confirmar cuántos
  cuadros y en qué grilla al ver el archivo real (no especificado en el
  listado) antes de armar la animación.
- **Black.png**: relleno sólido — probablemente no hace falta como
  imagen, un `fillStyle(0x000000)` ya lo resuelve; se evalúa si vale la
  pena cuando se vea el archivo.

Esta categoría es la que más directamente reemplaza el trabajo actual de
`TileVisuals.js` — es el corazón del ítem 1 del roadmap (tilemap).

## 3. `Builds/` — animaciones de 16 cuadros (grilla 4×4)

- **doors1/2/3.png**: animación de puerta abriéndose. Implementación
  planeada: `this.load.spritesheet('door_1', ..., {frameWidth: W,
  frameHeight: H})` + `this.anims.create({key: 'door_1_open', frames:
  this.anims.generateFrameNumbers('door_1', {start: 0, end: 15}), frameRate:
  12, repeat: 0})`, disparada al pisar el tile de puerta (tile tipo 4 hoy
  usa `drawWarpGate()` — se reemplaza por este sprite animado).
- **Object boulder.png**: roca como borde/límite — probablemente solo
  necesita 1 cuadro estático (no toda la animación), a confirmar con el
  archivo real.
- **Object tree.png**: árbol con viento — mismo patrón que las puertas,
  pero en loop continuo (`repeat: -1`) en vez de una sola vez.

## 4. `Characters/animals/` — fauna ambiental

bats, bird, dogs (4 variantes), horse (macho/hembra) — mismo patrón de
grilla 4×4 = 4 direcciones × 4 cuadros de caminata que ya usa
`Player.js` conceptualmente (hoy con formas dibujadas, no sprites). Estos
serían las primeras entidades no controlables por el jugador — buen
punto de entrada para el ítem 7 del roadmap (máquinas de estado: IDLE,
PATROL) ya con sprite real en vez de esperar más.

## 5. `Characters/people/` — jugador y NPCs humanos
**🔶 Parcialmente implementado — ver nota al inicio del documento.**

female/male (3 opciones cada uno) + NPC (3 variantes). Reemplaza
`buildCharacterVisual()` de `CharacterVisual.js`. Ya implementado para
`male/001.png` (imagen conceptual de prueba); falta repetir para las
otras 5 combinaciones cuando lleguen sus archivos — el mecanismo ya
probado es:
1. `PreloadScene.js` carga el spritesheet (`this.load.spritesheet`) y
   registra sus 4 animaciones (`defineCharacterAnimations()`). Falta
   agregar una línea por cada archivo nuevo que llegue.
2. `Player.js` y `OverworldScene.createRemotePlayer()` detectan solos
   (`scene.textures.exists(...)`) si existe sprite real para esa
   combinación género+preset — no hace falta tocarlos de nuevo por cada
   archivo nuevo, ya generalizan automáticamente.
3. **El dato guardado no cambia** — sigue siendo `gender + preset (1-3)`,
   por eso se implementó ya el selector de presets sin esperar los
   archivos.
4. `CharacterVisual.js` (el dibujo a mano) queda como *fallback* — si el
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
bang" del resto del roadmap). Orden sugerido por impacto/riesgo:
1. **Autotiles + tilemap** (ítem 1) — el cuello de botella de rendimiento
   real, y lo que más se nota visualmente.
2. **Characters/people** — reemplaza el sistema de apariencia visual
   (el dato ya está listo desde hoy).
3. **Builds** (puertas, roca, árbol-viento) — detalle de ambientación.
4. **Characters/animals + tree** — fauna y flora ambiental, mayor
   esfuerzo de comportamiento (IDLE/PATROL, ítem 7) para aprovecharlos
   bien.
5. **Animations (index.php) + Pictures + Audio** — pulido final, menor
   riesgo, se pueden intercalar en cualquier momento sin bloquear nada
   más.
