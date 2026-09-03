# Changelog — Bifrost

## v1.00-seed (31-08-2026) — punto de retorno

Primera versión completa y estable del proyecto. Se marca como
**"seed"** (semilla) porque ya tiene toda la base funcionando de punta a
punta — cuentas, mundo, multijugador, batallas — pero el arte real
(gráficos y audio) recién está empezando a integrarse. De aquí en
adelante, cualquier trabajo nuevo parte de este punto; si algo sale mal
en el camino, `git checkout v1.00-seed` vuelve exactamente acá.

### Autenticación y sesión
- Login sin contraseña: código de 6 dígitos enviado al correo (o
  mostrado en pantalla automáticamente en modo desarrollo local — sin
  necesitar configurar un servidor de correo para probar).
- Registro con usuario + correo, sin contraseña.
- Detección automática de entorno local vs. hosting
  (`detectarEntornoLocal()`), con override manual disponible
  (`FORZAR_ENTORNO_LOCAL`) y una consola de diagnóstico
  (`debug-entorno.php`) para verificar qué está detectando el servidor.
- Sesiones centralizadas (`session_bootstrap.php`): cookies
  `httponly`/`secure`/`samesite=Strict`, cierre automático tras 30 min de
  inactividad, token CSRF por sesión.
- CSRF y rate-limiting en los 9 endpoints POST que lo necesitaban (antes
  solo login/registro lo tenían) — límite de 10 retos PvP por minuto.
- Página de aterrizaje (`index.php`) rediseñada: navbar, hero,
  secciones informativas, footer, con el login como ventana modal.

### Personaje y apariencia
- Creación de personaje en 2 pasos: género, luego apariencia.
- **Cambio de jugabilidad (31-08-2026):** ya no se elige color
  libremente — se elige 1 de 3 opciones preestablecidas por género,
  pensado para cuando se integren sprites reales. El servidor resuelve
  los colores (`resolve_appearance_preset()`), el cliente nunca los
  declara directamente.
- **Sprites reales integrados** (primeros 2 de 6): `male/001.png`
  (128×192px) y `female/001.png` (256×256px) — tamaños de cuadro
  distintos entre sí, cada uno detectado y validado automáticamente
  (`validateCharacterSpritesheet()`). Los otros 4 (`male/002`,
  `male/003`, `female/002`, `female/003`) siguen usando el dibujo a mano
  como respaldo hasta que existan sus archivos — el sistema generaliza
  solo en cuanto se agreguen, sin tocar código.

### El mundo — mapa "Renca"
- Mapa principal inspirado en la comuna de Renca, Santiago de Chile:
  Cerros de Renca (con la Cueva de Don Emilio, leyenda real), río
  Mapocho, gran árbol en la esquina donde aparecen los jugadores nuevos.
- Escala calculada a partir de la altura real de los personajes
  (~1.75 m/tile) — 90×65 tiles, verificado con flood-fill que el 100%
  del área transitable es alcanzable.
- Hitos narrativos (`landmarks`) como etiquetas flotantes en el mundo.
- 4 rutas cardinales (Norte/Sur/Este/Oeste) con diseño genérico — quedan
  pendientes de expandir con la misma inspiración real más adelante.

### Multijugador
- Posiciones en vivo por sondeo (polling ~1.3s), jugadores cercanos
  visibles en el mismo mapa.
- Retos de batalla PvP estilo "cable link" (**R** junto a otro jugador).

### Batallas
- PvP y batallas silvestres **autoritativas en el servidor** — el daño
  se calcula una sola vez, en el backend; el cliente nunca puede alterar
  el resultado editando su propio JavaScript.
- `js/core/battleRules.js`: reglas de combate como funciones puras, sin
  ninguna dependencia de Phaser — probadas con Node
  (`scripts/test-battle-rules.js`) y con PHP contra MySQL/MariaDB real
  (`scripts/test-wild-battles.php`).

### Arquitectura (ver `ROADMAP-ARQUITECTURA.md`)
De los 12 ítems de buenas prácticas identificados: **5 resueltos por
completo** (reglas de combate separadas de Phaser, batallas
autoritativas, catálogo/reglas en una sola fuente de datos,
CSRF/rate-limiting, testing automatizado), y el resto catalogado o
planificado en detalle, a la espera de contenido (NPCs, misiones) o
archivos de arte reales.
- `data/species.json`, `data/battle-rules.json`: catálogo y reglas en
  una sola fuente, leída tanto por JS (navegador) como PHP (servidor) —
  ya no hay copias duplicadas mantenidas a mano.
- `data/audio-events.json` (27 archivos) y `data/graphics-catalog.json`
  (43 archivos): catalogados con su rol y convención de grilla, listos
  para cuando existan los archivos reales — ver `PLAN-GRAPHICS-AUDIO.md`
  para el plan técnico detallado de cada categoría.
- `npm test` corre las 4 suites de prueba (mapas, reglas de combate,
  batallas silvestres, CSRF/rate-limit) con un solo comando.

### Base de datos
- Esquema v1.0-seed consolidado (`sql/schema.sql`): sin la tabla
  `species` (redundante con `data/species.json`), índices en las
  consultas más frecuentes, relación FK entre retos y batallas PvP.
  Migraciones incrementales viejas (002-007) archivadas en
  `sql/archive/`.
- v1.1: columna `appearance_preset` para el sistema de presets.
- Todas las migraciones probadas contra MariaDB real (no solo teoría),
  preservando cuentas y progreso reales existentes.

### Limpieza de marca registrada
- Sin nombres de franquicias registradas en el código ni la
  documentación — catálogo de 24 criaturas 100% original, ítem de
  inventario renombrado (`pokeball` → `runa_captura`, temática nórdica
  acorde a "Bifrost"/"Helheim").

---

## v0.01 (30-08-2026)

Primera versión funcional completa: login por código, personaje
personalizable (colores libres, versión anterior al cambio de
jugabilidad), multijugador, batallas silvestres y PvP (todavía
calculadas en el cliente para las silvestres), mapa principal genérico
(antes de la inspiración en Renca).
