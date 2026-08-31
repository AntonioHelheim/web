# Roadmap de arquitectura — Bifrost (Helheim)

Este documento nace de las indicaciones de buenas prácticas entregadas el
30-08-2026 (ver resumen de principios más abajo) y es la referencia que
debemos consultar **en cada versión que revisemos de Bifrost de ahora en
adelante**, tal como se acordó. No es un checklist para tachar de una sola
vez — es la brújula de priorización cuando decidamos en qué trabajar
después.

**Regla de oro para aplicar esta lista:** un ítem a la vez, con la misma
disciplina de validación que ya usamos (sintaxis, conectividad de mapas,
balance de PHP, etc. antes de dar por cerrado cualquier cambio). Nada de
"big bang" — el riesgo de romper algo que ya funciona es demasiado alto
como para migrar todo de golpe.

**🔶 Hito: base de datos v1.0-seed (31-08-2026).** Se consolidó
`sql/schema.sql` como línea base limpia (sin la tabla `species`
redundante, con índices en las consultas más frecuentes, con la relación
entre retos y batallas PvP declarada como FK, sin nombres de marcas
registradas) — ver la migración `sql/v1.0-seed-migration.sql` para
actualizar una base existente sin perder datos. No es un ítem numerado
del roadmap original, pero valía la pena registrarlo acá como punto de
referencia: de aquí en adelante, cualquier cambio de esquema parte de
esta línea base.

## Cómo leer las prioridades

Cada ítem tiene:
- **Por qué importa** (conectado al principio del documento original).
- **Riesgo/esfuerzo** estimado si lo abordamos.
- **Depende de** — si necesita que otro ítem esté resuelto antes, o si
  necesita algo externo (como el listado de assets gráficos).

---

## 1. Sistema de tilemap con texturas reales (Tiled/Phaser Tilemap)
**Prioridad: máxima.**

Hoy cada tile se dibuja como 1-6 formas de Phaser individuales
(rectángulos/círculos/triángulos), no una textura. Con el mapa de Renca ya
vamos en ~16.500 objetos por carga — es el cuello de botella de
rendimiento más urgente, y además es un **prerrequisito duro** para poder
usar la carpeta `graphics` que ya tienes lista (Tilesets, Autotiles,
Characters, etc.): sin esto, esas imágenes no se pueden aprovechar bien.

- *Por qué importa:* "Tilemaps y reutilización de recursos" del documento
  original — evitar duplicar imágenes, usar tilesets/spritesheets reales.
- *Riesgo/esfuerzo:* Alto — toca `TileVisuals.js`, `OverworldScene.js`,
  `maps.js` (los layouts numéricos actuales calzan bien con un tilemap de
  todos modos, así que no hay que rediseñar los mapas desde cero) y el
  sistema de personaje (`CharacterVisual.js`) si también migra a
  spritesheets de `Characters`.
- *Depende de:* **el listado de archivos de `graphics` que vas a mandar**
  — los autotiles al estilo RPG Maker XP tienen un formato de recorte
  específico (normalmente en bloques de 2×3 o 3×4 combinaciones por
  autotile), distinto a un tileset plano de grilla simple. Sin ver los
  archivos reales no puedo armar el loader correctamente.

## 2. Separar las reglas del juego de Phaser (núcleo independiente)
**✅ Resuelto (30-08-2026).** Ver `js/core/battleRules.js` — daño, huida y
recuperación tras desmayo ahora son funciones puras (sin `this.add...`,
sin nada de Phaser adentro), probadas directo con Node en
`scripts/test-battle-rules.js` (13/13 pruebas, corre con
`node scripts/test-battle-rules.js`).

**Actualización tras resolver el ítem 3:** `BattleScene.js` ya NO llama
estas funciones directamente — desde que las batallas silvestres pasaron
a resolverse en el servidor (`api/wild_battle_action.php`), la escena
solo pide la acción y dibuja la respuesta, igual que `PvpBattleScene.js`.
`battleRules.js` sigue en el proyecto (ya no se carga en `game.php`, pero
el archivo queda) como la referencia probada que `calculate_damage()` en
`config.php` (PHP) debe seguir replicando — y como base para un eventual
"preview" de daño estimado en el cliente antes de atacar, si algún día
se quiere esa mejora de UX.

Prioridad original: muy alta — no depende de assets, se puede empezar ya.

Hoy el cálculo de daño, tipos y catálogo de criaturas vive mezclado
dentro de `BattleScene.js`/`OverworldScene.js`. El documento original lo
dice explícito: *"el sistema de combate debe poder ejecutarse sin
depender directamente de Phaser. Phaser debería representar visualmente
el resultado del combate, no contener todas sus reglas."*

- *Por qué importa:* es la base para el ítem 3 (batallas autoritativas),
  para testing (ítem 10), y para arquitectura data-driven (ítem 4).
- *Riesgo/esfuerzo:* Medio — extraer funciones puras (sin `this.add...`,
  sin nada de Phaser) a un módulo nuevo (ej. `js/core/battleRules.js`) y
  hacer que `BattleScene.js` solo lo llame y dibuje el resultado.
- *Depende de:* nada externo. **Buen candidato para empezar ahora mismo**
  mientras preparas el listado de `graphics`.

## 3. Batallas silvestres autoritativas en el servidor
**✅ Resuelto (30-08-2026).**

`api/wild_battle_start.php` (genera al enemigo y usa el equipo GUARDADO
del jugador como fuente de verdad, no lo que mande el cliente) +
`api/wild_battle_action.php` (resuelve "attack"/"run" con
`calculate_damage()`, la misma fórmula que `battleRules.js`, ahora también
en `config.php` para que PvP y batallas silvestres usen una sola función
en el servidor en vez de dos copias). `BattleScene.js` quedó igual de
"tonta" visualmente que `PvpBattleScene.js` — solo pide la acción y dibuja
la respuesta.

**Efecto secundario intencional:** cada acción persiste de inmediato el
HP del jugador en `saves.party_json` (`persist_party_first_hp()`), no
solo al presionar "S" — antes, si cerrabas el navegador a mitad de una
batalla sin guardar, ese daño se perdía. Ahora el servidor siempre tiene
el HP real como única fuente de verdad, lo cual además es lo que hace
posible que sea genuinamente autoritativo (no se puede confiar en
"el HP que mande el cliente al iniciar la siguiente batalla").

**Probado en 3 niveles** antes de dar por cerrado (no bastaba con que
`php -l` no marcara errores):
1. Funciones puras de PHP (`calculate_damage`, `faint_recovery_hp`) con
   500+ tiradas verificando el rango de daño y el mínimo de 1.
2. El flujo completo de `wild_battle_action.php` (con sus closures) contra
   una base de datos SQLite real en memoria — no solo simulado a mano.
3. Los 3 caminos posibles: ganar, perder (con recuperación del 30% del HP
   máximo), y huir (~90% de éxito verificado estadísticamente en 100
   intentos) — más protección contra reusar una batalla ya terminada, y
   contra que un usuario actúe sobre la batalla de otro.

*Por qué importaba:* *"el servidor debe ser autoritativo para
operaciones críticas como combate, daño..."* — literal del documento.

## 4. Arquitectura data-driven para contenido (una sola fuente de datos)
**✅ Resuelto (30-08-2026).**

`data/species.json` (24 criaturas, con `type` y `description` incluidos —
antes esos dos campos solo existían del lado JS) y
`data/battle-rules.json` (variación de daño, probabilidad de escape,
fracción de recuperación tras desmayo) son ahora la única fuente. Ver
`data/README.md` para el detalle de qué archivo lee cada lado.

- `js/scenes/PreloadScene.js` carga `species.json` con el sistema propio
  de Phaser (`this.load.json`) y puebla el global `SPECIES` antes de que
  cualquier otra escena lo necesite — `js/data.js` ya no tiene el
  catálogo hardcodeado, solo las funciones que lo usan.
- `api/config.php` → `species_catalog()`, `battle_rules()`,
  `calculate_damage()`, `attempt_escape()`, `faint_recovery_hp()` leen los
  mismos `.json` directo del disco en el servidor.
- `js/core/battleRules.js` (en Node, para `scripts/test-battle-rules.js`)
  también lee `battle-rules.json` en vez de tener sus propias constantes
  fijas — así el test siempre valida contra los números reales.

**Cómo se validó** (más allá de que no hubiera errores de sintaxis):
cambié cada `.json` real y confirmé que el cambio se reflejaba de
inmediato tanto en `battleRules.js` (Node) como en `config.php` (PHP) —
"prueba de fuego" en vez de solo confiar en la lectura del código. Nota
técnica para el futuro: las primeras veces que probé el lado PHP usé
`eval()` sobre fragmentos de texto extraídos, y `__DIR__` dentro de
`eval()` apunta al archivo que llama a `eval()`, no a `api/` — así que
esas primeras pruebas pasaban por la razón equivocada (caían al valor por
defecto, que coincidía con el real por casualidad). Las repetí como
archivos reales dentro de `api/` para que `__DIR__` resuelva igual que en
producción, y ahí sí quedó confirmado de verdad.

**Antes de este ítem:** la fórmula de daño y el catálogo vivían
duplicados en 3 lugares (`js/data.js`, `js/core/battleRules.js`,
`api/config.php`). Ahora hay 2 archivos de datos, y todo el código los
lee — cero copias mantenidas a mano.

*Por qué importaba:* *"Items, personajes, enemigos... deben definirse
mediante datos y no mediante lógica hardcodeada."* — literal del
documento original.

## 5. CSRF y rate-limiting en el resto de los endpoints
**✅ Resuelto (30-08-2026).**

`require_csrf()` ahora protege los 9 endpoints POST que no lo tenían:
`save_game.php`, `save_appearance.php`, `update_position.php`,
`challenge_send.php`, `challenge_respond.php`, `battle_action.php`,
`wild_battle_start.php`, `wild_battle_action.php` y `logout.php` (este
último además sumó verificación de método POST, que no tenía). Los
endpoints de solo lectura (`load_game.php`, `nearby_players.php`,
`challenge_poll.php`, `pvp_battle_state.php`) se dejaron sin CSRF a
propósito — es un GET que no cambia estado, no aplica el mismo riesgo.

- `game.php` ahora expone `window.BIFROST_CSRF_TOKEN` (mismo patrón que
  ya tenía `index.php`), y las 9 llamadas `fetch()` correspondientes en
  el frontend lo mandan en el body.
- **Rate-limiting** en `challenge_send.php`: máximo 10 retos por minuto
  por jugador, reutilizando la columna `created_at` que ya existía en
  `battle_challenges` — no hizo falta tabla nueva. Los demás endpoints no
  recibieron rate-limiting dedicado por ahora (no hay evidencia de que lo
  necesiten hoy — revisar si se abusa de alguno más adelante).

**Probado con PHP real** (no solo que `php -l` no marcara errores):
token vacío → bloqueado (403), token adivinado por un atacante →
bloqueado, token correcto → pasa normalmente. El límite de retos se
probó con 15 intentos seguidos: los primeros 10 pasan, del 11 en
adelante quedan bloqueados.

*Por qué importaba:* *"Toda operación crítica debe validarse en el
servidor... protección contra XSS y CSRF... rate limiting."* — literal
del documento original.

## 6. Pipeline de assets gráficos y de audio
**Prioridad: alta, parcialmente avanzada — sigue bloqueada para la parte visual.**

Diseñar cómo `PreloadScene.js` va a cargar `graphics/` (Characters,
Tilesets, Battlers, Icons, Autotiles, etc.) y `audio/` (BGM/SE/ME)
siguiendo esa misma convención de carpetas.

**🔶 Avance parcial (30-08-2026):** ya tengo el listado de `audio/` (27
archivos, con la descripción de cuándo debe sonar cada uno) — lo
catalogué en `data/audio-events.json` (ver `data/README.md`). Todavía
falta: (a) los archivos `.ogg` reales (el catálogo por ahora es solo el
"contrato", no hay audio real en el proyecto), y (b) escribir el
`AudioManager` que realmente los reproduzca en los momentos correctos —
no tiene sentido escribirlo antes de tener los archivos para probarlo de
verdad. El listado de `graphics/` sigue pendiente por completo — sin eso
no se puede avanzar la parte visual (ítem 1), que es la de mayor impacto.

- *Por qué importa:* es literalmente la integración del arte real que
  reemplazará las formas dibujadas a mano — el salto de calidad visual
  más grande que puede dar el proyecto.
- *Riesgo/esfuerzo:* Medio, pero depende 100% del ítem 1 (tilemap) para
  la parte de mapas, y de conocer los nombres/dimensiones reales de los
  archivos para todo lo demás (personajes, battlers, iconos).
- *Depende de:* el listado de `graphics/` (audio ya está catalogado) y
  los archivos reales de ambas carpetas — sin los archivos de verdad,
  cualquier loader que escriba no se puede probar de extremo a extremo.

## 7. Máquinas de estado explícitas (NPCs / IA)
**Prioridad: media — no urgente porque todavía no hay NPCs.**

El documento pide estados claros (IDLE, PATROL, CHASE, ATTACK, DEAD).
Hoy las escenas de Phaser ya hacen de "estado grueso" del juego completo
(Overworld/Battle/etc.), pero no hay FSM para comportamiento de
entidades individuales, porque no hay entidades con IA todavía.

- *Depende de:* que existan NPCs (que a su vez dependen del ítem 6 para
  tener sprites que mostrar) — buen candidato para cuando lleguen los
  personajes/trainers de `graphics/characters` y `graphics/trainers`.

## 8. Sistema de eventos para quests/diálogos
**Prioridad: media — bloqueado por contenido narrativo real.**

Mismo caso que el ítem 7: tiene sentido diseñarlo cuando empecemos a
construir diálogos/quests de verdad (por ejemplo, cuando le demos
funcionalidad real a "Cueva de Don Emilio", que hoy es solo un hito
visual).

## 9. Chunking / carga de mapas por regiones
**Prioridad: baja por ahora.**

El mapa más grande (Renca, 90×65) todavía carga completo sin problemas
perceptibles. Revisar esto si seguimos agrandando mapas mucho más, o si
el número total de mapas crece lo suficiente como para que cargar todos
de una sea un problema real. No hay evidencia hoy de que lo sea.

## 10. Testing automatizado y observabilidad
**✅ Resuelto (30-08-2026).**

`scripts/check-maps.js` formaliza el flood-fill de conectividad que antes
se corría a mano por consola en cada rediseño de mapa, más una validación
nueva que antes NO se hacía sistemáticamente: que cada warp apunte a un
mapa que existe, con coordenadas de destino dentro de rango y sobre un
tile transitable. **Prueba de fuego:** reintroduje a propósito el mismo
bug real que se coló al agrandar el pueblo (coordenadas de destino en el
sistema equivocado) y el script lo detectó correctamente; con el archivo
restaurado, vuelve a pasar limpio.

`js/maps.js` ahora también funciona en Node (mismo patrón que
`js/core/battleRules.js`, ítem 2) para que este script lo pueda `require`.

**Testing de combate/API contra base de datos** (lo que faltaba):
- `scripts/test-wild-battles.php`: ganar, perder (con recuperación
  correcta), huir (tasa estadística correcta), y las 2 protecciones
  (batalla terminada / usuario ajeno) — contra SQLite en memoria.
- `scripts/test-csrf-and-rate-limit.php` (+ auxiliar `_csrf_case.php`):
  token vacío/incorrecto/correcto, y el límite de 10 retos/minuto.
- Para que esto fuera posible sin duplicar lógica a mano (como pasaba con
  los arneses temporales de los ítems 3-5), extraje
  `resolve_wild_battle_action()` y `count_recent_challenges()` de los
  endpoints hacia `api/config.php` — mismo patrón que `battleRules.js`
  (ítem 2): la lógica real vive en un solo lugar reutilizable, y tanto el
  endpoint HTTP como el test la llaman igual. Los endpoints
  (`wild_battle_action.php`, `challenge_send.php`) quedaron más cortos y
  enfocados solo en la parte HTTP (leer input, validar sesión/CSRF,
  responder).
- Encontré y corregí un detalle real en el camino: la `respond()` del
  proyecto usa `exit;` sin argumento, que en PHP **siempre** sale con
  código 0 sin importar el `http_response_code()` fijado antes — mi
  primer intento de probar CSRF verificando el código de salida del
  proceso no detectaba nada. Se corrigió verificando el *contenido* de la
  respuesta en su lugar.

`package.json` (0 dependencias) corre las 4 suites con un solo comando:
`npm test` → `check-maps.js` + `test-battle-rules.js` (Node) +
`test-wild-battles.php` + `test-csrf-and-rate-limit.php` (PHP).

*Por qué importaba:* *"El sistema debe disponer de logging suficiente
para detectar errores... testing unitario, integración."* — literal del
documento original.

## 11. Caching en capas (Redis, etc.)
**Prioridad: baja.** El documento mismo lo marca como algo para
"posteriormente". A la escala actual de Bifrost (una BD chica, tráfico
bajo) no hay problema de rendimiento que esto resuelva todavía.

## 12. Object pooling
**Prioridad: baja por ahora.** Relevante cuando haya elementos que
aparecen/desaparecen con frecuencia (proyectiles, partículas de batalla,
efectos). Hoy casi no hay nada de eso — buen candidato para cuando
lleguemos a `graphics/animations` (animaciones de batalla).

---

## Resumen: por dónde seguir

**Bloqueado por el listado de `graphics/`:** ítem 1 (tilemap/texturas —
el de mayor impacto visual) e ítem 6 (pipeline completo, la parte visual
— audio ya quedó catalogado).

**Resueltos:** ítems 2, 3, 4, 5 y 10 (todos 30-08-2026).

**Catalogado, a la espera de archivos reales:** audio (ítem 6 parcial).

**Pendientes, bloqueados por contenido que todavía no existe:** ítem 7
(NPCs/IA — necesita sprites de `graphics/characters`), ítem 8 (quests/
diálogos — necesita contenido narrativo real).

**Sin urgencia (no hay evidencia de que se necesiten hoy):** ítem 9
(chunking de mapas), ítem 11 (caching/Redis), ítem 12 (object pooling —
esperando `graphics/animations`).

De los 12 ítems del roadmap original, quedan 5 resueltos, 1 parcial
(audio catalogado) y 6 pendientes — la mayoría de los pendientes dependen
de `graphics/` (visuales) o de contenido que aún no existe (NPCs,
misiones). Con el listado de `graphics/` que vas a mandar, se pueden
destrabar los ítems 1, 6, 7 (una vez existan sprites de personajes) y 12
de una sola vez.
