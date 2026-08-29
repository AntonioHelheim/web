# Bifrost — juego 2D estilo Game Boy (multijugador)

Proyecto base: Phaser 3 (frontend) + PHP/MySQL (backend de cuentas, guardado
y multijugador). Todo el arte del juego se dibuja con formas de Phaser
(rectángulos/círculos), así que corre "de fábrica" sin necesitar sprites —
listo para que sustituyas esas formas por tu propio pixel art.

## Renombrado a "Bifrost" — qué hacer con tu base de datos existente

El código ahora asume una base de datos llamada `bifrost` (`api/config.php`
→ `DB_NAME`, `sql/schema.sql` → `CREATE DATABASE bifrost`). Si es una
instalación nueva desde cero, no tienes que hacer nada más: importa
`schema.sql` como siempre y listo.

Pero si ya tenías el proyecto corriendo (como es tu caso, en local y en tu
hosting) con una base llamada `pokeweb`, tienes dos caminos:

**Opción A — no tocar la base de datos (más simple y sin riesgo):**
Abre `api/config.php` y deja `DB_NAME` como el nombre real de tu base
actual (`pokeweb` en local; en tu hosting probablemente algo como
`usuario_pokeweb`). El nombre interno de la base de datos no lo ve nadie
que juegue — solo importa para que PHP se conecte al lugar correcto. Todo
lo demás del rebranding (título, footer, variable de usuario) sigue
aplicando igual.

**Opción B — renombrar la base de datos de verdad:**
- *En local (XAMPP, tienes el usuario root con todos los permisos):*
  entra a phpMyAdmin → selecciona la base `pokeweb` → pestaña
  **"Operaciones"** → en el campo **"Cambiar nombre de la base de datos a"**
  escribe `bifrost` → clic en **"Continuar"**. phpMyAdmin mueve todas las
  tablas y datos automáticamente.
- *En tu hosting (el usuario de la base normalmente NO puede crear/borrar
  bases de datos, así que el truco de arriba puede fallar ahí):*
  1. phpMyAdmin → selecciona tu base actual → pestaña **"Exportar"** →
     método rápido, formato SQL → Continuar (descarga un `.sql` con todo).
  2. cPanel → "Bases de datos MySQL" → crea una base nueva (te quedará
     con prefijo, ej. `usuario_bifrost`) y asígnale un usuario con todos
     los privilegios (puede ser el mismo usuario de antes).
  3. phpMyAdmin → selecciona la base nueva → pestaña "Importar" → elige
     el `.sql` que exportaste en el paso 1 → Continuar.
  4. Actualiza `api/config.php` con el nombre nuevo (`DB_NAME`) y, si
     creaste un usuario nuevo, también `DB_USER`/`DB_PASS`.
  5. Verifica que todo funcione antes de borrar la base vieja desde cPanel.

## Cómo funciona el multijugador

Se implementó con **sondeo periódico (polling)** en vez de WebSockets, a
propósito: el hosting compartido típico (cPanel, etc.) solo garantiza PHP +
MySQL, no procesos persistentes tipo Node/WebSocket. Con polling, todo
funciona en cualquier hosting normal, sin infraestructura extra.

Cada navegador, cada ~1.3 segundos:
1. Envía su propia posición (`api/update_position.php`).
2. Pide quién más está activo en su mismo mapa (`api/nearby_players.php`).
3. Pregunta si tiene algún reto de batalla pendiente (`api/challenge_poll.php`).

Esto da una experiencia "casi tiempo real" — los demás jugadores se ven
moverse con un pequeño salto cada ~1 segundo, no de forma perfectamente
fluida como en un juego con WebSockets. Es la misma técnica que usaban
muchos juegos de navegador antes de que los WebSockets fueran estándar, y es
más que suficiente para 2-4 personas jugando juntas.

**Compartir espacio o jugar en paralelo:** el proyecto trae 5 mapas conectados
(`overworld` en el centro, y una ruta por cada punto cardinal — ver
`js/maps.js`). Cada mapa es más grande que la pantalla (28x20 tiles, más del
triple de área que la versión inicial), así que la cámara sigue al jugador
dentro de los límites del mapa actual, como en el juego original. Si ambos
jugadores están en el mismo mapa se ven y pueden interactuar; si cada quien
recorre una ruta distinta, avanzan en paralelo sin interferirse.
Guardar y cargar partida funciona igual que antes, ahora recordando también
en qué mapa estabas.

**Batallas entre jugadores (estilo cable link):** parándote junto a otro
jugador y presionando **R** le envías un reto. Si lo acepta, ambos entran a
una batalla por turnos donde el **servidor calcula el daño** (nunca el
navegador de cada quien), así que los dos ven siempre el mismo resultado sin
importar la latencia de cada uno. Es una batalla "amistosa": usa una copia
temporal de tu primer Pokémon del equipo guardado (o uno al azar si aún no
tienes equipo) y perder no afecta tu partida guardada.

## Creación de personaje

La primera vez que alguien entra (o cualquier cuenta que ya existiera antes
de esta actualización), antes de ver el mapa aparece un asistente de dos
pasos:
1. Elegir si el personaje es chico o chica (define la silueta: piernas o
   vestido acampanado — un recurso genérico, no basado en ningún personaje
   con derechos de autor).
2. Personalizar color de piel, pelo y ojos con flechas `< >` sobre una
   vista previa en vivo.

La elección se guarda en la base de datos (`saves.character_created = 1`) y
se usa para dibujar tanto a tu propio personaje como el de los demás
jugadores en el mapa compartido. Todo el diseño es original — figuras y
colores propios, no una recreación de personajes de Nintendo ni de ninguna
otra franquicia. Los personajes usan proporción "chibi" (cabeza grande,
cuerpo pequeño, como los sprites clásicos de overworld de Game Boy), con
brazos, piernas, sombra de contacto y un brillo suave para dar volumen —
ver `js/entities/CharacterVisual.js`.

**¿El juego te sigue preguntando esto cada vez que entras?** Casi seguro es
porque tu base de datos no tiene todavía las columnas de apariencia — repasa
la sección de migraciones más abajo e importa `002_add_character_appearance.sql`
si no lo has hecho. Sin esas columnas, el backend falla al leer/guardar tu
elección y el juego asume que nunca creaste personaje.

**Cambiar apariencia después:** presiona **M** en cualquier momento dentro
del mapa para abrir el menú, y elige "Cambiar apariencia". Te lleva de
vuelta al mismo asistente (precargado con tus colores actuales), y al
confirmar tu personaje se actualiza al instante sin perder tu posición ni
tu partida — internamente reconstruye solo la parte visual del sprite
(`Player.setAppearance()`), sin recrear el mapa ni reiniciar el multijugador.

> **Arreglo importante:** el menú (y el resto de pantallas superpuestas:
> batallas, PvP) se abre con `scene.pause()` sobre `OverworldScene`, pero
> Phaser **no desactiva el teclado automáticamente** al pausar una escena.
> Sin desactivarlo a mano, `OverworldScene` seguía "escuchando" teclas de
> fondo al mismo tiempo que la escena de encima, causando comportamiento
> errático (el motivo por el que el menú no andaba bien). Ahora
> `pauseForOverlay()` en `OverworldScene.js` desactiva `input.keyboard`
> explícitamente al pausar y lo reactiva al reanudar — cualquier pantalla
> nueva que se lance por encima del mapa (`this.pauseForOverlay('Tal', {...})`
> en vez de `scene.pause()` + `scene.launch()` a mano) queda protegida del
> mismo problema automáticamente.

## Pantalla responsive

El canvas del juego usa `Phaser.Scale.FIT`: dibuja siempre a la misma
resolución interna (15×11 tiles) pero se escala automáticamente para llenar
el contenedor `#game-container`, que en el CSS ocupa el 100% del ancho
disponible hasta un máximo de 800px, manteniendo la proporción 15:11. Esto
hace que se vea bien tanto en un monitor de escritorio grande como en un
teléfono, sin configurar nada manualmente — se ajusta solo al redimensionar
la ventana o girar el teléfono.

> **Nota sobre esta actualización de mapas:** los cambios de este paso son
> 100% de frontend (`js/main.js`, `js/maps.js`, `js/scenes/OverworldScene.js`).
> No se tocó la base de datos, así que **no** hace falta importar ninguna
> migración nueva — solo vuelve a copiar/subir los archivos `.js` actualizados.

## Cómo se ven los tiles

Cada tipo de terreno se dibuja como una pequeña composición de formas (no un
cuadrado de color plano), en `js/entities/TileVisuals.js`: los árboles tienen
tronco y copa redondeada, el agua tiene ondas, la hierba alta tiene brizas,
las rocas son polígonos irregulares con sombra, la arena tiene textura de
puntitos. El gran árbol de la plaza es un caso especial: el código detecta
automáticamente el bloque completo de tiles bloqueados y dibuja UNA copa
grande que lo cubre entero, en vez de repetir un árbol pequeño en cada celda.
Sigue siendo 100% formas de Phaser (nada de imágenes), así que puedes seguir
ejecutándolo sin necesitar ningún archivo de arte adicional.

## Catálogo de criaturas

24 criaturas 100% originales en `js/data.js` (con su espejo en
`api/config.php` para las batallas PvP), organizadas en 8 tipos propios, 3
por tipo, cada una pensada como una progresión ligera cría → joven → adulta:

| Tipo | Inspiración | Criaturas |
|---|---|---|
| Fuego | Dragones | Chispodrilo, Braseryx, Vulcanor |
| Agua | Serpientes marinas | Marejino, Corrientauro, Abisalgo |
| Planta | Insectos | Brotalín, Espigón, Follascorpio |
| Electricidad | Equidnas | Chispequín, Voltígero, Amperidna |
| Lucha | Artes marciales | Puñolet, Katáfaro, Granmaestro |
| Volador | Aves | Plumín, Ventizarro, Tormenpluma |
| Oscuro | Gatos | Sombrigato, Penumbraz, Eclipsino |
| Diurno | Perros | Solete, Auroraz, Radialbo |

**Sin compañero inicial (a propósito):** el juego empieza con `party` vacío
y así se queda hasta que construyamos el sistema de domesticación. Caminar
por hierba alta sin compañeros muestra un mensaje de sabor ("algo se movió
en la hierba...") pero no inicia batalla — no hay con qué pelear todavía.
`StarterSelectionScene.js` (la pantalla de elegir entre las 3 crías) quedó
en el proyecto pero desconectada del flujo (no se carga en `game.php` ni
está en la lista de escenas de `main.js`): lo más probable es que reutilicemos
ese mismo patrón de tarjetas más adelante para elegir qué hacer con una
criatura recién atrapada.

## Cómo se mueve el personaje

El movimiento sigue siendo por cuadrícula (un tile a la vez, como el
original), pero ahora simula caminar en vez de deslizarse: en cada paso el
cuerpo rebota levemente hacia arriba y las piernas se mueven en tijera (una
adelante, otra atrás, alternando de lado), ver `Player.animateWalkStep()`
en `js/entities/Player.js`. Esto reutiliza los mismos sub-contenedores de
pierna que expone `buildCharacterVisual()` en `CharacterVisual.js`.

## Estructura

```
bifrost/
├── index.php            # Login / registro
├── game.php             # Página del juego (requiere sesión)
├── assets/style.css      # Estilos retro compartidos
├── js/
│   ├── main.js                    # Config de Phaser + constantes
│   ├── data.js                    # Catálogo de 24 criaturas (contenido original)
│   ├── maps.js                    # Mapas del juego + warps entre ellos
│   ├── entities/
│   │   ├── Player.js              # Movimiento en cuadrícula
│   │   ├── CharacterVisual.js     # Silueta del personaje (personalizable)
│   │   └── TileVisuals.js         # Composición visual de cada tipo de tile
│   └── scenes/
│       ├── PreloadScene.js            # Carga la partida y decide a qué escena ir
│       ├── CharacterCreationScene.js  # Género + personalización de apariencia
│       ├── StarterSelectionScene.js   # En pausa (no se carga), ver nota más arriba
│       ├── MenuScene.js               # Menú en juego (tecla M), cambiar apariencia
│       ├── OverworldScene.js          # Mapa, movimiento, encuentros, multijugador
│       ├── BattleScene.js             # Batalla por turnos contra criaturas salvajes
│       └── PvpBattleScene.js          # Batalla por turnos contra otro jugador (PvP)
├── api/                     # Endpoints PHP (JSON)
│   ├── config.php             # Conexión PDO + helpers de sesión/especies
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   ├── save_game.php
│   ├── load_game.php
│   ├── save_appearance.php    # Guarda género/piel/pelo/ojos
│   ├── update_position.php    # Reporta tu posición en vivo
│   ├── nearby_players.php     # Otros jugadores activos en tu mapa
│   ├── challenge_send.php     # Retar a un jugador adyacente
│   ├── challenge_poll.php     # Sondeo de retos entrantes/aceptados/rechazados
│   ├── challenge_respond.php  # Aceptar o rechazar un reto
│   ├── pvp_battle_state.php   # Estado actual de una batalla PvP
│   └── battle_action.php      # Enviar Atacar/Huir (el servidor calcula el daño)
└── sql/
    ├── schema.sql                        # Esquema completo (instalación nueva)
    ├── 002_add_character_appearance.sql  # Migración: apariencia del personaje
    └── 003_expand_species.sql            # Migración: catálogo de 24 criaturas
```

## Requisitos

- PHP 8.0+
- MySQL / MariaDB
- Un navegador moderno (no se necesita Node ni build step: todo son `<script>` planos)

## Puesta en marcha

1. Crea la base de datos e importa el esquema:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
2. Ajusta credenciales en `api/config.php` (`DB_HOST`, `DB_USER`, `DB_PASS`).
3. Levanta un servidor PHP desde la raíz del proyecto:
   ```bash
   php -S localhost:8000
   ```
   (o coloca la carpeta en `htdocs/` si usas XAMPP/MAMP)
4. Abre `http://localhost:8000/index.php`, crea una cuenta y juega.

> **¿Ya tenías el proyecto instalado antes de esta actualización?** Si tu
> base de datos ya existía antes de las últimas actualizaciones, importa
> (en este orden, mismo proceso de siempre: phpMyAdmin → tu base →
> "Importar") los archivos de migración que aún no hayas corrido:
> - `sql/002_add_character_appearance.sql` — columnas de apariencia del personaje.
> - `sql/003_expand_species.sql` — actualiza la tabla informativa `species`.
>
> Si ya corriste alguno en un paso anterior, no pasa nada por saltarlo. Si
> es una instalación nueva desde cero, no hace falta ninguno: `schema.sql`
> ya incluye todo. El catálogo real que usa el juego vive en el código
> (`js/data.js`), así que cualquier equipo que ya tuvieras guardado
> (Flamlet/Aquabub/Leafkin, si jugaste antes de esta actualización) sigue
> funcionando sin problema aunque ya no aparezcan en el catálogo.

Si no tienes el backend corriendo, el juego igual es jugable: simplemente no
podrás guardar ni cargar partidas (verás un aviso en pantalla al presionar **S**).

## Cómo jugar

- Flechas o WASD: moverse por el mapa (cuadrícula, como el Pokémon original).
- Entrar a la hierba alta (verde oscuro) tiene una probabilidad de encuentro.
- Caminar hasta cualquier borde del Pueblo Origen te lleva a la ruta de ese
  punto cardinal (Norte, Sur, Este u Oeste), y viceversa para volver.
- En el centro del pueblo hay una plaza con un gran árbol — se puede
  caminar todo alrededor de él.
- **S**: guardar la partida (posición, equipo, inventario) en MySQL.
- **R**: retar a batalla a un jugador que tengas justo al lado.
- **Y / N**: aceptar o rechazar un reto entrante.
- **M**: abrir/cerrar el menú (para cambiar tu apariencia).
- En cualquier batalla: **Atacar** o **Huir**.

Abre el juego en dos navegadores (o uno normal y uno en modo incógnito) con
dos cuentas distintas para probar el multijugador tú mismo.

## Próximos pasos sugeridos

- **Arte real**: sustituye los rectángulos por spritesheets en `PreloadScene.js`
  (`this.load.spritesheet(...)`) y anima al jugador con `this.anims.create(...)`.
- **Mapas más grandes**: diseña niveles en [Tiled](https://www.mapeditor.org/)
  (gratis) y cárgalos con `this.load.tilemapTiledJSON(...)` en vez del array
  `MAP_LAYOUT` hardcodeado.
- **Más criaturas y movimientos**: amplía `js/data.js` y la tabla `species` en
  MySQL; añade una tabla `moves` si quieres ataques variados en vez de un único
  "Atacar".
- **Selección de inicial**: ahora mismo se asigna una criatura al azar en el
  primer combate; podrías añadir una escena inicial de elección.
- **NPCs y diálogos**: añade objetos interactivos al mapa con `setInteractive()`
  y una caja de texto estilo GB.
- **Seguridad**: antes de producción, agrega rate-limiting al login/registro y
  valida el origen de las peticiones a la API.
- **Ajustar el ritmo del multijugador**: `MP_TICK_MS` en `OverworldScene.js`
  controla cada cuánto se reporta posición y se consultan otros jugadores
  (1300ms por defecto). Súbelo si tu hosting es muy limitado, bájalo si
  quieres que se vea más fluido.
- **WebSockets de verdad**: si en el futuro tienes un VPS o un hosting con
  soporte de Node.js persistente, podrías reemplazar el polling por
  Socket.IO o `ws` para multijugador instantáneo en vez de con el pequeño
  salto de ~1 segundo que tiene el polling.
- **Trading entre jugadores**: con la base de retos de batalla ya montada,
  un sistema de intercambio de criaturas seguiría un patrón muy similar
  (tabla `trade_offers`, sondeo, confirmación de ambos lados).
