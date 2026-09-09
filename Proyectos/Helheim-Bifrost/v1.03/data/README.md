# data/ — fuente única de contenido y reglas (ítem 4 de ROADMAP-ARQUITECTURA.md)

Estos archivos son la **única** fuente de verdad para el catálogo de
criaturas y las constantes de las reglas de combate. Antes vivían
duplicados a mano en 3 lugares (`js/data.js`, `js/core/battleRules.js` y
`api/config.php`) — ahora esos 3 archivos **leen** de acá en vez de tener
su propia copia.

- **`species.json`**: las 24 criaturas del juego (nombre, tipo, color,
  estadísticas, descripción). Lo carga `js/scenes/PreloadScene.js` (vía
  `this.load.json(...)`, el sistema de carga propio de Phaser) para
  poblar el global `SPECIES` en el navegador, y `api/config.php` →
  `species_catalog()` lo lee directo del disco en el servidor.

- **`battle-rules.json`**: variación de daño, probabilidad de escape,
  fracción de recuperación tras un desmayo. Lo usa `api/config.php` (en el
  servidor, para PvP y batallas silvestres) y `js/core/battleRules.js`
  (solo en Node, para `scripts/test-battle-rules.js` — ver la nota en ese
  archivo sobre por qué no aplica lo mismo si algún día se vuelve a cargar
  en el navegador).

- **`audio-events.json`**: catálogo de qué archivo `.ogg` corresponde a
  cada evento del juego (login, abrir/cerrar menú, cambio de mapa,
  templos elementales, desmayo, etc.), organizado en `bgm`/`se`/`me`
  (música de fondo / efectos de sonido / stings musicales cortos —
  convención estándar de RPG Maker). **Todavía no hay ningún archivo de
  audio real en el proyecto** — este `.json` es solo el catálogo/contrato
  a la espera de los archivos reales, listo para que un futuro
  `AudioManager` lo use en cuanto lleguen. No hay código en el juego
  todavía que reproduzca sonido.
  > ⚠️ Nota de transcripción: en el listado original venían dos secciones
  > llamadas "Subcarpeta BGM" — la segunda (`Door.ogg`, `hit1/2/3.ogg`)
  > son claramente efectos de sonido cortos, no música de fondo en loop,
  > así que se guardó como `se` (Sound Effects) en vez de duplicar `bgm`.
  > Avisa si en realidad debían ir en otra categoría.
  > ⚠️ Se renombró la clave `pokemon_lore` a `creature_lore` (y su
  > descripción) para no usar el nombre de una franquicia registrada en el
  > código del proyecto — el nombre del archivo físico (`pokemon.ogg`) se
  > dejó tal cual porque es el que realmente vas a subir; si quieres
  > purgar el término del todo, renombra ese archivo en tu paquete de
  > assets antes de subirlo y avísame para actualizar la referencia acá.

- **`graphics-catalog.json`**: catálogo de `graphics/` — 43 archivos ya
  integrados o con ruta confirmada (Animations, Autotiles, Builds,
  Characters/people, Pictures), más categorías nuevas de criaturas
  (`mythical_creatures`, `phantoms`, `dragons`, `devils`, `plants`,
  `seamonsters`, `npc_variety`, subcategorías nuevas de `animals`) como
  placeholders numéricos genéricos a la espera de arte original. El plan
  técnico de cómo se integra cada categoría vive en
  `PLAN-GRAPHICS-AUDIO.md`, en la raíz del proyecto.
  > ⚠️ **Nota de seguridad (31-08-2026):** se recibió un paquete grande
  > de imágenes que resultó ser en su mayoría el pack de recursos de
  > Pokémon Essentials — incluso archivos con nombres genéricos
  > contenían diseños de personajes de Pokémon reconocibles. **No se
  > integró ningún archivo de ese paquete al proyecto.** Ver la nota
  > completa al inicio de `graphics-catalog.json` y la sección
  > correspondiente en `PLAN-GRAPHICS-AUDIO.md`.

- **`npc-spawns.json`**: posiciones de NPCs ambientales por mapa (punto
  de origen, sprite a usar, radio de merodeo) — reemplaza lo que antes
  vivía hardcodeado en `OverworldScene.js` (ítem 4 del roadmap). Lo lee
  `PreloadScene.js` hacia el global `NPC_SPAWNS`, y
  `OverworldScene.spawnAmbientNPCs()` crea un `NPC` (`js/entities/NPC.js`)
  por cada entrada. Validado contra los mapas reales en
  `scripts/test-npc-wander.js`.

**Para agregar o cambiar contenido** (una criatura nueva, ajustar una
estadística, cambiar la probabilidad de escape): edita el `.json`
correspondiente. No hace falta tocar código en JS ni en PHP — es
justamente el punto de esto (ver "Arquitectura Data-Driven" en el
documento de buenas prácticas original).

⚠️ Si agregas una criatura nueva, recuerda subir `$ASSET_VERSION` en
`session_bootstrap.php` para que el navegador no siga usando una copia
vieja en caché de `species.json`.
