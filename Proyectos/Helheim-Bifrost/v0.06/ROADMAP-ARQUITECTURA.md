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
  — los autotiles de RPG Maker/Pokémon Essentials tienen un formato de
  recorte específico (normalmente en bloques de 2×3 o 3×4 combinaciones
  por autotile), distinto a un tileset plano de grilla simple. Sin ver los
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
**Prioridad: alta.**

`js/data.js` y `api/config.php` → `species_catalog()` son hoy dos copias
mantenidas a mano del mismo catálogo de 24 criaturas — cada vez que se
agrega una criatura hay que recordar actualizar los dos archivos. El
documento pide justo lo contrario: *"Items, personajes, enemigos... deben
definirse mediante datos y no mediante lógica hardcodeada."*

- *Por qué importa:* elimina una clase entera de bugs por desincronía, y
  es el patrón que necesitamos para poder agregar items/movimientos/NPCs
  después sin tocar código cada vez.
- *Riesgo/esfuerzo:* Medio — mover el catálogo a un `.json` que tanto PHP
  como JS lean, en vez de dos arrays hardcodeados.
- *Depende de:* nada externo. Se puede hacer en paralelo al ítem 2.

## 5. CSRF y rate-limiting en el resto de los endpoints
**Prioridad: media-alta.**

Hoy `require_csrf()` y el rate-limiting solo protegen login/registro.
`save_game.php`, `challenge_send.php`, `battle_action.php`,
`update_position.php`, `wild_battle_start.php`, `wild_battle_action.php`,
etc. no lo tienen (nótese que ya son dos endpoints más desde el ítem 3).

- *Por qué importa:* *"Toda operación crítica debe validarse en el
  servidor... protección contra XSS y CSRF... rate limiting."*
- *Riesgo/esfuerzo:* Bajo-medio — el patrón ya existe (`require_csrf()`
  en `api/config.php`), solo hay que aplicarlo en más lugares y que el JS
  del juego mande el token en cada request sensible.
- *Depende de:* nada. Bajo riesgo, se puede hacer en cualquier momento.

## 6. Pipeline de assets gráficos y de audio
**Prioridad: alta, pero bloqueada por el listado de archivos.**

Diseñar cómo `PreloadScene.js` va a cargar `graphics/` (Characters,
Tilesets, Battlers, Icons, Autotiles, etc.) y una carpeta `audio/` nueva
(BGM/SE, típico de este tipo de proyectos) siguiendo esa misma
convención de carpetas.

- *Por qué importa:* es literalmente la integración del arte real que
  reemplazará las formas dibujadas a mano — el salto de calidad visual
  más grande que puede dar el proyecto.
- *Riesgo/esfuerzo:* Medio, pero depende 100% del ítem 1 (tilemap) para
  la parte de mapas, y de conocer los nombres/dimensiones reales de los
  archivos para todo lo demás (personajes, battlers, iconos).
- *Depende de:* **el listado de texto que vas a mandar** con cada
  elemento de las carpetas. Sin eso, cualquier código que escriba para
  esto sería adivinar nombres de archivo.

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
**Prioridad: baja-media, pero con una victoria fácil disponible ya.**

No hay pruebas automatizadas hoy. Pero en la práctica, **ya venimos
haciendo un tipo de test** cada vez que rediseñamos un mapa: el
flood-fill de conectividad que corro manualmente por consola antes de
entregar cualquier cambio de mapa. Formalizar eso en un script que quede
guardado en el repo (`scripts/check-maps.js` o similar) y se pueda correr
con un solo comando sería una victoria de bajo esfuerzo y cero riesgo.

- *Por qué importa:* *"El sistema debe disponer de logging suficiente
  para detectar errores... testing unitario, integración."*
- *Riesgo/esfuerzo:* Bajo para la parte de conectividad de mapas (ya la
  hacemos a mano, solo falta guardarla). Más alto para testing real de
  combate/API, que requeriría más infraestructura.

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

**Bloqueados por tu listado de `graphics`/`audio`:** ítems 1 y 6 (los de
mayor impacto visual).

**Resueltos:** ítem 2 (30-08-2026, `js/core/battleRules.js`) e ítem 3
(30-08-2026, `api/wild_battle_start.php` + `api/wild_battle_action.php`).

**Se pueden empezar ya, sin esperar nada:** ítem 4 (catálogo
data-driven — ahora con más urgencia: la fórmula de daño y el catálogo de
especies ya viven en 3 lugares: `js/data.js`, `js/core/battleRules.js`
y `api/config.php`), ítem 5 (CSRF/rate-limiting en el resto de
endpoints, incluyendo los 2 nuevos de batalla silvestre que se acaban de
agregar), y la parte de testing de mapas del ítem 10.

Mi sugerencia concreta para seguir: **ítem 4** (catálogo/reglas en una
sola fuente de datos) — con el ítem 3 recién resuelto, la fórmula de daño
y el catálogo de criaturas ya están duplicados en 3 archivos distintos
mantenidos a mano; es el momento más barato para unificarlos antes de que
se sigan agregando más lugares que dependan de ellos. Dime si prefieres
ese u otro de los "ya se pueden empezar".
