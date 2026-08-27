# PokeWeb — starter de juego 2D estilo Game Boy (multijugador)

Proyecto base: Phaser 3 (frontend) + PHP/MySQL (backend de cuentas, guardado
y multijugador). Todo el arte del juego se dibuja con formas de Phaser
(rectángulos/círculos), así que corre "de fábrica" sin necesitar sprites —
listo para que sustituyas esas formas por tu propio pixel art.

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

**Compartir espacio o jugar en paralelo:** el proyecto trae dos mapas
conectados (`overworld` y `route1`, ver `js/maps.js`) unidos por una salida.
Si ambos jugadores están en el mismo mapa se ven y pueden interactuar; si
cada quien recorre un mapa distinto, avanzan en paralelo sin interferirse.
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
1. Elegir si el personaje es chico o chica (define la silueta: pantalones
   o vestido — un recurso genérico, no basado en ningún personaje con
   derechos de autor).
2. Personalizar color de piel, pelo y ojos con flechas `< >` sobre una
   vista previa en vivo.

La elección se guarda en la base de datos (`saves.character_created = 1`) y
se usa para dibujar tanto a tu propio personaje como el de los demás
jugadores en el mapa compartido. Todo el diseño es original — figuras y
colores propios, no una recreación de personajes de Nintendo ni de ninguna
otra franquicia.

## Estructura

```
pokeweb/
├── index.php            # Login / registro
├── game.php             # Página del juego (requiere sesión)
├── assets/style.css      # Estilos retro compartidos
├── js/
│   ├── main.js             # Config de Phaser + constantes
│   ├── data.js             # Catálogo de criaturas (contenido original)
│   ├── maps.js             # Mapas del juego + warps entre ellos
│   ├── entities/Player.js  # Movimiento en cuadrícula
│   └── scenes/
│       ├── PreloadScene.js    # Carga la partida guardada y arranca el mapa correcto
│       ├── OverworldScene.js  # Mapa, movimiento, encuentros, multijugador
│       ├── BattleScene.js     # Batalla por turnos contra criaturas salvajes
│       └── PvpBattleScene.js  # Batalla por turnos contra otro jugador (PvP)
├── api/                     # Endpoints PHP (JSON)
│   ├── config.php             # Conexión PDO + helpers de sesión/especies
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   ├── save_game.php
│   ├── load_game.php
│   ├── update_position.php    # Reporta tu posición en vivo
│   ├── nearby_players.php     # Otros jugadores activos en tu mapa
│   ├── challenge_send.php     # Retar a un jugador adyacente
│   ├── challenge_poll.php     # Sondeo de retos entrantes/aceptados/rechazados
│   ├── challenge_respond.php  # Aceptar o rechazar un reto
│   ├── pvp_battle_state.php   # Estado actual de una batalla PvP
│   └── battle_action.php      # Enviar Atacar/Huir (el servidor calcula el daño)
└── sql/schema.sql        # Esquema MySQL (usuarios, partidas, especies, multijugador)
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

> **¿Ya tenías el proyecto instalado antes de esta actualización?** Además
> de `schema.sql`, importa también `sql/002_add_character_appearance.sql`
> (mismo proceso: phpMyAdmin → tu base → pestaña "Importar"). Si es una
> instalación nueva desde cero, no hace falta: `schema.sql` ya incluye todo.

Si no tienes el backend corriendo, el juego igual es jugable: simplemente no
podrás guardar ni cargar partidas (verás un aviso en pantalla al presionar **S**).

## Cómo jugar

- Flechas o WASD: moverse por el mapa (cuadrícula, como el Pokémon original).
- Entrar a la hierba alta (verde oscuro) tiene una probabilidad de encuentro.
- Caminar hasta el borde derecho del pueblo te lleva a la Ruta 1 (y viceversa).
- **S**: guardar la partida (posición, equipo, inventario) en MySQL.
- **R**: retar a batalla a un jugador que tengas justo al lado.
- **Y / N**: aceptar o rechazar un reto entrante.
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
