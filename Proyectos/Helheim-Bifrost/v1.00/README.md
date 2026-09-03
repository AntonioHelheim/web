# Bifrost — juego 2D estilo Game Boy (multijugador)

## 🌱 v1.00-seed (31-08-2026) — punto de retorno

Primera versión completa y estable de todo el proyecto — login,
personaje, mundo, multijugador y batallas funcionando de punta a punta.
Se marca **"seed"** porque el arte real (gráficos/audio) recién empieza a
integrarse; todo lo demás ya está sólido. Ver **[CHANGELOG.md](./CHANGELOG.md)**
para el detalle completo de qué incluye este hito, y
**[VERSION](./VERSION)** para el marcador de versión actual.

Este es el punto al que volver si algo sale mal en trabajo futuro — está
etiquetado en git como `v1.00-seed` (`git checkout v1.00-seed` para
volver exactamente a este estado).

> 📋 **[ROADMAP-ARQUITECTURA.md](./ROADMAP-ARQUITECTURA.md)** — lista
> priorizada de buenas prácticas de arquitectura a aplicar en las próximas
> versiones (tilemaps reales, separar reglas del juego de Phaser, batallas
> silvestres autoritativas en servidor, etc.). Consultar en cada versión
> que revisemos de aquí en adelante.
>
> 🎨 **[PLAN-GRAPHICS-AUDIO.md](./PLAN-GRAPHICS-AUDIO.md)** — plan técnico
> para integrar los archivos reales de `graphics/`/`audio/` a medida que
> vayan llegando.

Proyecto base: Phaser 3 (frontend) + PHP/MySQL (backend de cuentas, guardado
y multijugador). Todo el arte del juego se dibuja con formas de Phaser
(rectángulos/círculos), así que corre "de fábrica" sin necesitar sprites —
listo para que sustituyas esas formas por tu propio pixel art.

## ⚠️ Importante: caché del navegador al actualizar archivos

Todos los `.js`/`.css` se cargan con un parámetro `?v=...`
(`$ASSET_VERSION`, definido en **un solo lugar**: `session_bootstrap.php`,
en la raíz del proyecto — `index.php`, `game.php` y `acceso-denegado.php`
lo heredan de ahí). **Cada vez que subas una versión nueva del proyecto,
cambia ese valor** (por ejemplo, a la fecha del día) — si no, el navegador
(o a veces el propio hosting) puede seguir sirviendo la versión vieja en
caché de esos archivos, y vas a estar probando código desactualizado sin
darte cuenta aunque el servidor ya tenga los cambios nuevos. Si algo "no
cambia" después de subir una actualización, antes de asumir que algo sigue
roto: sube el valor de `$ASSET_VERSION` en `session_bootstrap.php` y haz un
refresco forzado del navegador (Ctrl+Shift+R / Ctrl+F5), o prueba en una
ventana de incógnito.

## Login por código (sin contraseña)

> **`debug-entorno.php` es ahora una consola de diagnóstico completa**, no
> solo un chequeo de entorno. Además de mostrar `$isLocal` y de dónde salió
> ese valor, ahora también:
> - Verifica la conexión a la base de datos y qué tablas existen (te avisa
>   si falta importar alguna migración).
> - Te deja comprobar si un correo específico ya está registrado — la API
>   real (`api/login.php`) nunca revela esto a propósito, por seguridad,
>   así que si pruebas con un correo que aún no registraste, la respuesta
>   se ve igual de "exitosa" pero nunca llega ningún código. Esta sección
>   te dice la verdad directo desde la base de datos.
> - Prueba en vivo el endpoint real de login (`api/login.php`) y muestra
>   la respuesta cruda, tal como la vería el navegador — sin pasar por el
>   modal del juego.
> - Muestra las últimas líneas del log de errores de PHP, si se puede leer
>   desde tu servidor.
>
> **Bórralo antes de producción** — revela si un correo está registrado y
> el host/nombre de tu base de datos.

> **Detección de entorno más robusta:** si corres el proyecto en un puerto
> poco común (ej. `http://localhost:3000/...`) y el modo desarrollo no se
> detectaba, ya está corregido — además de que el puerto se descarta antes
> de comparar el hostname, se encontró un caso límite real: si `APP_ENV`
> llegaba a existir pero vacía (`""`), `detectarEntornoLocal()` la trataba
> como "definida explícitamente" y nunca llegaba a revisar el hostname.
> También se agregó una señal extra (si quien se conecta es la misma
> máquina donde corre el servidor, `REMOTE_ADDR` 127.0.0.1/`::1`) para
> configuraciones locales poco comunes. Y si tu máquina tiene algo
> realmente particular que ninguna de estas señales detecta bien, arriba
> de todo en `api/config.php` hay una constante `FORZAR_ENTORNO_LOCAL`
> (`null` por defecto = automático) que puedes poner en `true` para forzar
> modo local sin importar nada más, garantizado.

> **Ajustes de fidelidad al patrón original** (tras comparar línea por
> línea con el archivo de referencia completo): se agregó `DB_PORT` al DSN
> de conexión (`api/config.php`, por defecto `3306`, igual que la
> referencia); se agregó la validación explícita de que
> `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS` no vengan vacíos antes de
> intentar conectar; se agregó la constante `BLOQUEO_MINUTOS` en
> `api/login.php` para que el mensaje de "demasiados intentos fallidos"
> diga los minutos exactos de espera (antes decía "unos minutos" en
> genérico); y `json_input()` ahora cae de vuelta a `$_POST` si el body no
> viene como JSON válido, igual que el patrón original.

`index.php` ahora es una página de aterrizaje normal (navbar, hero,
"Sobre el juego", "Características", footer) en vez de mostrar el
formulario de login directo — el botón **"Iniciar sesión"** (en la barra
superior y en el hero) abre el login como una ventana modal encima de la
página (`#login-modal-overlay` / clases `.gb-overlay` / `.gb-panel`, las
mismas que ya usa el menú del juego — se unificaron para no duplicar CSS).
Dentro del modal vive el mismo login de 2 pasos de siempre.

El login cambió de usuario+contraseña a **correo + código de un solo uso**
(2 pasos), con el mismo patrón de seguridad usado en otros proyectos:

1. **Pides el código** (`api/login.php`, `action=request_code`): escribes tu
   correo, el servidor genera un código de 6 dígitos, lo guarda con hash
   (`password_hash`) en `login_codes` con 10 minutos de vigencia, y lo
   envía.
2. **Verificas el código** (`action=verify_code`): si coincide (y no
   expiró, y no se agotaron los intentos), se abre tu sesión.

**En local (XAMPP) no se envía correo de verdad** — el código viaja
directo en la respuesta JSON (`dev_code`) y el formulario de `index.php`
lo autocompleta por ti, para poder probar sin configurar un servidor de
correo. **En tu hosting**, se intenta enviar con la función `mail()` de
PHP — funciona si tu hosting tiene un MTA configurado (la mayoría de los
cPanel lo traen listo), pero no es 100% garantizado. Si el correo no
llega en producción, revisa la carpeta de spam primero; si el problema
persiste, probablemente necesites cambiar `enviarCodigoPorCorreo()` en
`api/login.php` para usar SMTP real (por ejemplo con PHPMailer) en vez de
`mail()` — puedo ayudarte con eso si llegas a ese punto.

**Seguridad incluida:** token CSRF en cada envío (`$_SESSION['csrf_token']`,
generado en `session_bootstrap.php`), límite de solicitudes por IP y por
usuario, bloqueo tras varios intentos fallidos de verificación, el código
es de un solo uso, y las respuestas no revelan si un correo está o no
registrado (mismo mensaje genérico siempre) ni se demoran distinto según
el caso (para que no se pueda adivinar por temporización).

**¿Ya tenías cuentas creadas con usuario+contraseña?** Van a quedar sin
correo (`email` NULL) hasta que le agregues uno — si tu base es de antes
del 31-08-2026, revisa `sql/v1.0-seed-migration.sql`.

**Sesión y seguridad general:** `session_bootstrap.php` (nuevo, en la raíz)
centraliza el arranque de sesión para todo el sitio — cookie
`httponly`/`secure` (si hay HTTPS)/`samesite=Strict`, cierre automático de
sesión tras 30 minutos de inactividad, y el token CSRF. `api/config.php`
lo usa (en vez de tener su propio `session_start()` suelto) y además
detecta automáticamente si estás en local o en tu hosting
(`detectarEntornoLocal()`) para elegir las credenciales de base de datos
correctas — mismo patrón que ya usábamos para el resto del proyecto,
ahora aplicado también a la conexión a la base de datos. Si el jugador no
tiene sesión activa al entrar a `game.php`, lo manda a la nueva
`acceso-denegado.php` en vez de simplemente redirigir en silencio.

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

> **Nota:** `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS` ya no son una lista
> plana al principio de `api/config.php` — ahora viven dentro de un
> `if ($isLocal) { ... } else { ... }` (detección automática de entorno,
> ver la sección "Login por código" más arriba). Para tu hosting, edita
> los valores dentro del bloque `else`.

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
temporal de tu primer compañero del equipo guardado (o uno al azar si aún no
tienes equipo) y perder no afecta tu partida guardada.

## Creación de personaje

La primera vez que alguien entra (o cualquier cuenta que ya existiera antes
de esta actualización), antes de ver el mapa aparece un asistente de dos
pasos:
1. Elegir si el personaje es chico o chica (define la silueta: piernas o
   vestido acampanado — un recurso genérico, no basado en ningún personaje
   con derechos de autor).
2. Elegir una de **3 opciones de apariencia preestablecidas** para ese
   género (ya no se elige color libremente — cambio de jugabilidad del
   31-08-2026, pensado para cuando se integren los sprites reales de
   `Characters/people/`, ver `PLAN-GRAPHICS-AUDIO.md`). El servidor
   resuelve los colores de cada opción (`resolve_appearance_preset()` en
   `api/config.php`) — el cliente nunca declara colores directamente.

La elección se guarda en la base de datos (`saves.character_created = 1`,
`saves.appearance_preset`) y se usa para dibujar tanto a tu propio
personaje como el de los demás jugadores en el mapa compartido. Todo el
diseño es original — figuras y colores propios, no una recreación de
personajes de ninguna franquicia existente. Los personajes usan
proporción "chibi" (cabeza grande, cuerpo pequeño, como los sprites
clásicos de overworld de Game Boy), con brazos, piernas, sombra de
contacto y un brillo suave para dar volumen — ver
`js/entities/CharacterVisual.js`. Esto es un "placeholder" a propósito:
en cuanto existan los archivos reales de `Characters/people/{gender}/00N.png`,
se reemplaza el dibujo por el sprite real sin tener que tocar de nuevo la
base de datos ni el flujo (el dato guardado siempre fue "género + número
de opción 1-3").

**¿El juego te sigue preguntando esto cada vez que entras?** Casi seguro es
porque tu base de datos no tiene todavía las columnas de apariencia. Si tu
base ya tenía el login por código funcionando (o es de antes del
31-08-2026 en general), importa `sql/v1.0-seed-migration.sql` y luego
`sql/v1.1-appearance-presets-migration.sql`. Si es una instalación mucho
más vieja que nunca actualizaste, revisa
`sql/archive/002_add_character_appearance.sql` primero — sin esas
columnas, el backend falla al leer/guardar tu elección y el juego asume
que nunca creaste personaje.

**Cambiar apariencia después:** presiona **M** en cualquier momento dentro
del mapa para abrir el menú, y elige "Cambiar apariencia". Te lleva de
vuelta al mismo asistente (precargado con tus colores actuales); al
confirmar (o cancelar), vuelves al mapa en la misma posición, con el mismo
equipo e inventario.

> **Historial de arreglos del menú (por si es útil más adelante):** se
> intentaron varias correcciones sobre `MenuScene` como escena de Phaser —
> desactivar el teclado de `OverworldScene` al pausar (Phaser no lo hace
> solo), quitar un listener de teclado que se autodisparaba, blindar contra
> apilar escenas — pero el problema persistió incluso sin ningún error en
> consola (`MenuScene.create()` terminaba limpio y aun así el juego se
> sentía congelado al presionar M, sin que los botones respondieran).
>
> El menú en sí se reconstruyó como un **overlay HTML normal**
> (`#game-menu-overlay` en `game.php`, estilos `.gb-menu-overlay` /
> `.gb-menu-panel` en `assets/style.css`) — la misma idea que el botón
> "Salir", que nunca dio problemas por ser HTML simple.
>
> **Pero la causa raíz real apareció después:** al presionar "Cambiar
> apariencia" (que sí lanzaba una escena de Phaser — `CharacterCreationScene`
> — encima de `OverworldScene` pausada) volvió a congelarse exactamente
> igual. Eso confirmó el patrón real: **Phaser no responde bien a clics en
> una escena nueva lanzada con `scene.launch()` encima de otra pausada con
> `scene.pause()`**, sin importar cuál sea esa escena nueva. Probablemente
> afectaba también a las batallas (silvestres y PvP), que usaban el mismo
> patrón — solo que no se habían probado todavía.
>
> **Arreglo de raíz:** se reemplazó el patrón "pausar y lanzar encima" por
> **transición completa de escena** (`scene.start()`) en los tres lugares
> que lo usaban — batalla silvestre, batalla PvP y editor de apariencia —
> el mismo mecanismo que ya usan los warps entre mapas (`changeMap()`),
> que nunca dieron este problema. `OverworldScene.handoffTo(sceneKey, data)`
> guarda un snapshot de todo (mapa, posición, equipo, inventario,
> apariencia) antes de transicionar, y cada escena de destino hace
> `scene.start('OverworldScene', datosDeVuelta)` al terminar, en vez de
> `scene.stop() + scene.resume()`. Como consecuencia, cambiar de apariencia
> ya no actualiza el sprite "en caliente": ahora reconstruye el mapa
> completo (mismo mecanismo que un warp), lo cual es un poco menos
> instantáneo pero mucho más confiable.
>
> También se agregó captura global de errores en `game.php`
> (`window.addEventListener('error', ...)`) que deja cualquier error de
> JavaScript, de cualquier parte del juego, visible en la consola (F12)
> con el mensaje `[Bifrost] ERROR NO CAPTURADO: ...` — útil para cualquier
> problema futuro, no solo este.

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

## El mapa principal está inspirado en Renca, a escala real

A partir de esta versión, el mapa "Pueblo Origen" (`overworld` en el
código) representa la comuna de **Renca**, en el sector norponiente de
Santiago de Chile.

**Sobre la escala:** el personaje mide 1 tile de alto en el juego (32px).
Con una altura real de 1.65-1.80m según el género, cada tile representa
aproximadamente **1.75 metros reales**. Con ese dato hice el cálculo de
cuánto ocuparía un mapa 1:1 de Renca completo (24 km², según el censo) —
y la respuesta honesta es que **no es renderizable así**: un solo tramo
norte-sur real (unos 4-5 km, del Cerro Renca al río Mapocho) necesitaría
más de 2.500 tiles de alto, y multiplicado por el ancho serían millones de
objetos de Phaser — ningún navegador podría dibujar eso con el método
actual (cada tile se compone de varias formas dibujadas a mano, no una
textura). Lo que sí hice: expandir el mapa mucho más que antes para que
las distancias entre hitos se sientan a escala, y luego compactarlo un
10% (por pedido explícito, se sentía con demasiado espacio vacío) y
llenarlo con más árboles — representando un sector de Renca, no el 1:1
literal completo:

| | v1 | v2 (muy grande) | v3 (actual) |
|---|---|---|---|
| Tamaño | 28×20 (560 tiles) | 100×72 (7.200 tiles) | **90×65 (5.850 tiles)** |
| Área real aprox. | 49m × 35m | 175m × 126m | **158m × 114m** |
| Árboles sueltos (fuera del borde) | 0 | 0 | **205, repartidos por todo el mapa** |

(La versión v2 creció demasiado y se sentía vacía porque el área subió
12.9× pero el contenido no la acompañó proporcionalmente. La v3 la achica
~19% en área y agrega 205 árboles sueltos en grupos irregulares — no un
cuadriculado perfecto — para que se sienta poblado en vez de vacío.
`drawPath()` sigue dibujando menos brizas de pasto por tile de camino
liso, para mantener la cantidad de objetos bajo control. Si notas que va
lento en tu máquina, avísame: el siguiente paso sería cambiar a un
sistema de tilemap con textura en vez de formas dibujadas una por una,
mucho más eficiente para mapas grandes, pero es un cambio de arquitectura
más grande.)

**Detalles reales incorporados:**
- **El gran árbol** ahora en la **esquina superior izquierda** exacta del
  mapa (10×10 tiles ≈ 17.5m — tamaño real de un árbol grande y viejo).
  **Los jugadores nuevos aparecen justo a sus pies** (`spawn: {x:6, y:13}`,
  inmediatamente debajo del bloque del árbol).
- **Los Cerros de Renca**, una franja montañosa grande y creíble corrida
  hacia el centro-norte (para no chocar con el árbol de la esquina) — en
  la realidad, cerca del 25% del territorio de Renca son cerros, y
  coincide con el límite norte real de la comuna, con Quilicura.
- **Dos cuevas** en ese cerro: la más grande es la **Cueva de Don Emilio**,
  una leyenda real y muy conocida en Renca (un minero, Emilio Lazo, que
  excavó buscando oro a fines del 1800 y nunca lo encontró). Por ahora son
  solo un hito visual en la superficie — el interior de las cuevas queda
  pendiente para una próxima sesión.
- **El río Mapocho**, una banda ancha (6 tiles ≈ 10.5m, un río urbano
  real) cruzando todo el extremo sur — en la vida real, el Mapocho es
  justamente el límite sur de Renca (con Cerro Navia y Quinta Normal al
  otro lado). Se dejó un puente transitable alineado con la salida sur.
- **205 árboles sueltos** repartidos en 18 grupos irregulares por el resto
  del mapa (no un patrón perfecto — cada grupo tiene ~70% de probabilidad
  de completarse por celda, así no se ve como un cuadriculado).
- Las etiquetas de estos hitos (`landmarks` en `js/maps.js`) se dibujan
  como texto flotando en el mundo — ver `OverworldScene.drawLandmarks()`.
  Es puramente decorativo por ahora, no activa nada al acercarse.

**Bug corregido — jugadores atascados entre rocas:** cuando el mapa se
agrandó, las coordenadas de partidas ya guardadas (de antes del cambio)
quedaron apuntando a terreno que ahora es roca/cerro en el mapa nuevo, y
el jugador aparecía atrapado sin poder moverse. Se agregó una validación
en `OverworldScene.resolveSafeStartTile()`: si la posición guardada (o la
que venga de cualquier warp) cae fuera de rango o sobre un tile bloqueado,
se usa el punto de aparición seguro del mapa en su lugar. Esto protege
contra este problema para siempre, sin importar cuánto rediseñemos los
mapas de aquí en adelante.

**Las 4 rutas** (Norte/Sur/Este/Oeste) siguen con su tamaño y diseño
genérico de antes (28×20) — quedan pendientes de expandir y rediseñar con
inspiración real en una próxima sesión, según lo conversado. Ya actualicé
los puntos de entrada/salida entre el pueblo y las 4 rutas para que
calcen con las nuevas puertas del pueblo (también corregí un bug propio
de la versión anterior: las coordenadas de entrada a las rutas usaban por
error el sistema de coordenadas del pueblo en vez del de cada ruta).

Verifiqué con el mismo chequeo de conectividad que uso para todos los
mapas (flood-fill desde el punto de aparición) que el 100% de las 4.167
celdas transitables del pueblo son alcanzables, que las 4 rutas siguen
100% conectadas internamente, y que los 8 puntos de entrada/salida entre
el pueblo y las rutas caen en terreno transitable en ambos sentidos.

## Cómo se ven los tiles

Cada tipo de terreno se dibuja como una pequeña composición de formas (no un
cuadrado de color plano), en `js/entities/TileVisuals.js`: los árboles tienen
tronco y copa redondeada, el agua tiene ondas, la hierba alta tiene brizas,
las rocas son polígonos irregulares con sombra, la arena tiene textura de
puntitos. Sigue siendo 100% formas de Phaser (nada de imágenes), así que
puedes seguir ejecutándolo sin necesitar ningún archivo de arte adicional.

**El gran árbol de la plaza, más grande y detallado:** el bloque de tronco
bloqueado pasó de 4×4 a 6×6 tiles (el código detecta automáticamente el
bloque completo y dibuja UNA copa que lo cubre entero, en vez de repetir un
árbol pequeño en cada celda). La copa ahora tiene 9 círculos oscuros de
base más 4 en un tono más claro encima (dos capas de follaje, no un color
plano), raíces visibles hundidas en la tierra, y una franja de corteza más
oscura en el tronco. Sigue siendo transitable alrededor por completo — lo
verifiqué con el mismo chequeo de conectividad que uso para diseñar los
mapas (100% del área caminable sigue siendo alcanzable). El punto de
aparición en el pueblo se movió un tile más abajo para seguir quedando
justo frente al árbol y no dentro de él.

**Árboles individuales** (bosques/bordes) ahora varían un poco de tamaño
entre sí (determinista según su posición, no cambia entre visitas), tienen
una sombra de raíz en la base, y un toque de tono más claro en la copa para
dar sensación de volumen — antes eran todos idénticos.

**Agua** con tres capas de ondas en vez de dos, de ancho variable, más un
pequeño destello de reflejo ocasional.

**Entrada de cueva** (nueva): marco rocoso irregular con una boca oscura al
centro — se usa para la Cueva de Don Emilio y otras cuevas en los cerros.
Por ahora es solo un hito visual bloqueado (no se puede entrar todavía).

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
original), pero ahora simula caminar en vez de deslizarse. En cada paso,
`Player.animateWalkStep()` (en `js/entities/Player.js`) anima varias cosas
a la vez:
- El cuerpo rebota hacia arriba y se "aplasta/estira" levemente
  (squash & stretch, como en animación clásica) para que el paso se sienta
  con peso, no solo un vaivén suave.
- Una leve inclinación lateral (2-3°) que alterna de lado en cada paso.
- Las piernas se mueven en tijera (una adelante, otra atrás).
- Los brazos se balancean al contrario que las piernas, como al caminar
  de verdad (brazo derecho adelanta con la pierna izquierda).
- El indicador de dirección rebota junto con el cuerpo.
- Una pequeña nube de polvo aparece bajo el pie que pisa, justo cuando
  "toca suelo" (con un breve retraso respecto al inicio del paso), y se
  desvanece rápido — sensación de contacto real con el piso.

Cuando el personaje está quieto, un tween infinito y muy sutil
(`this.idleTween` en el constructor de `Player`) hace que "respire" —
sin esto se veía completamente inmóvil entre pasos. Anima el contenedor
exterior (no la parte visual que usan los tweens del paso), así que nunca
compite con la animación de caminar.

Todo esto reutiliza los sub-contenedores de brazo/pierna que expone
`buildCharacterVisual()` en `CharacterVisual.js` (`container.legLeft`,
`.legRight`, `.armLeft`, `.armRight`), animados con tweens cortos (75ms,
`Sine.easeOut`, con `yoyo: true`) sincronizados con la duración del paso.

**Proyección según la dirección:** además del movimiento, la cara del
personaje cambia según hacia dónde mira (`applyFacingToVisual()` en
`CharacterVisual.js`) — de frente (hacia abajo) se ven ambos ojos; de
espaldas (hacia arriba) se tapa toda la cabeza con el color del pelo,
simulando ver la nuca; de perfil (izquierda/derecha) se oculta el ojo del
lado contrario, como si girara la cabeza. Esto aplica tanto al jugador
local como a los demás jugadores del mapa compartido (la posición ya traía
el dato `facing` desde `nearby_players.php`, antes no se usaba para nada
visual).

## Calidad gráfica del personaje

`CharacterVisual.js` pasó de ~16 formas por personaje a unas 30, todas
organizadas en capas para que el resultado se vea como un personaje y no
como figuras geométricas sueltas:

- **Cuello** conectando la cabeza con el cuerpo (antes la cabeza quedaba
  flotando directamente sobre el torso).
- **Manos**: pequeños círculos en tono piel al final de cada brazo.
- **Cejas** y **boca** (una sonrisa pequeña), ambas con expresión — se
  ocultan/muestran junto con los ojos según hacia dónde mira el personaje.
- **Mejillas sonrosadas**: un toque de calidez muy simple pero efectivo.
- **Brillos**: en el pelo, en la cabeza y un pequeño destello blanco dentro
  de cada ojo — dan sensación de volumen sin necesitar gradientes reales.
- **Chico**: cinturón y una solapa en V en el pecho (un triángulo más
  oscuro), silueta de torso más ancha.
- **Chica**: cinturón/lazo en la cintura, zapatos a juego con las piernas
  (antes no tenía), y un lazo en el pelo (dos triángulos + nudo central).
- **Pelo largo y lazo con vida propia**: al caminar, se balancean con un
  ligero retraso respecto al cuerpo (`angle` + desplazamiento lateral en
  `Player.animateWalkStep()`), en vez de ir pegados y rígidos al resto del
  personaje.
- **Ojos con 4 capas**: esclerótica blanca, iris del color elegido, pupila
  y un brillo — mucho más expresivos que un óvalo de un solo color.
- **Sombreado de volumen** en el cuerpo y el pelo, con óvalos semitransparentes
  (negro para sombra, blanco para brillo) en vez de un color plano — esta
  técnica funciona con cualquier color que el jugador elija, sin tener que
  calcular tonos más oscuros/claros a partir del color base.
- **Puños de manga** en ambos brazos (tono más oscuro), para que se note
  que es ropa y no un círculo pegado al cuerpo.

**Corrección de silueta:** el vestido de la chica se veía "cómicamente
redondo" — el torso y la falda eran dos óvalos casi circulares muy
superpuestos, que se leían como una sola masa redonda en vez de un
vestido. Ahora es una silueta tipo "A": torso angosto y más alto que
ancho (`0.24 x 0.3` tiles) + falda ancha y achatada (`0.46 x 0.22`), con
el cinturón marcando la transición entre ambos. De paso, el torso del
chico también pasó de ser casi un círculo a una elipse más ancha que alta
(hombros anchos en vez de óvalo).

Todo sigue siendo 100% formas de Phaser con colores personalizables (piel,
pelo, ojos) — nada de imágenes ni sprites, para que el proyecto siga
corriendo sin depender de ningún archivo de arte externo.

## Estructura

```
bifrost/
├── index.php              # Página de aterrizaje + modal de login por código
├── game.php               # Página del juego (requiere sesión)
├── acceso-denegado.php    # Se muestra si no hay sesión activa o expiró
├── debug-entorno.php      # Diagnóstico de detección local/hosting — bórralo en producción
├── session_bootstrap.php  # Sesión centralizada: cookies seguras, CSRF, $ASSET_VERSION
├── ROADMAP-ARQUITECTURA.md  # Buenas prácticas de arquitectura, priorizadas — leer antes de tocar código
├── PLAN-GRAPHICS-AUDIO.md   # Plan técnico para integrar graphics/audio reales cuando existan
├── CHANGELOG.md             # Qué incluye cada versión — ver v1.00-seed para el hito actual
├── VERSION                  # Marcador simple de versión actual (1.00-seed)
├── package.json           # 0 dependencias — solo `npm test` para correr scripts/
├── assets/style.css      # Estilos retro compartidos
├── data/                    # Única fuente de datos (ítem 4 del roadmap) — ver data/README.md
│   ├── species.json           # Catálogo de 24 criaturas (nombre, tipo, color, stats, descripción)
│   ├── battle-rules.json      # Variación de daño, prob. de escape, recuperación tras desmayo
│   ├── audio-events.json      # Catálogo de audio (bgm/se/me) — sin archivos .ogg reales todavía
│   └── graphics-catalog.json  # Catálogo de graphics (43 archivos) — sin archivos .png reales todavía
├── js/
│   ├── main.js                    # Config de Phaser + constantes
│   ├── data.js                    # Funciones sobre el catálogo (el catálogo en sí vive en data/species.json)
│   ├── maps.js                    # Mapas del juego + warps entre ellos
│   ├── core/
│   │   └── battleRules.js         # Reglas de combate puras (sin Phaser) — ver ROADMAP ítem 2
│   ├── entities/
│   │   ├── Player.js              # Movimiento en cuadrícula
│   │   ├── CharacterVisual.js     # Silueta del personaje (personalizable)
│   │   └── TileVisuals.js         # Composición visual de cada tipo de tile
│   └── scenes/
│       ├── PreloadScene.js            # Carga species.json + la partida, decide a qué escena ir
│       ├── CharacterCreationScene.js  # Género + personalización de apariencia
│       ├── StarterSelectionScene.js   # En pausa (no se carga), ver nota más arriba
│       ├── MenuScene.js               # En pausa (no se carga), ver nota más arriba
│       ├── OverworldScene.js          # Mapa, movimiento, encuentros, multijugador
│       ├── BattleScene.js             # Batalla contra criaturas salvajes (servidor autoritativo)
│       └── PvpBattleScene.js          # Batalla contra otro jugador (PvP, servidor autoritativo)
├── scripts/
│   ├── check-maps.js                   # Conectividad + warps de todos los mapas — ROADMAP ítem 10
│   ├── test-battle-rules.js            # Prueba battleRules.js con Node, sin navegador — ROADMAP ítem 2
│   ├── test-wild-battles.php           # Ganar/perder/huir contra SQLite en memoria — ROADMAP ítem 10
│   ├── test-csrf-and-rate-limit.php    # Token CSRF + límite de retos por minuto — ROADMAP ítem 10
│   └── _csrf_case.php                  # Auxiliar de test-csrf-and-rate-limit.php (no se corre solo)
├── api/                     # Endpoints PHP (JSON)
│   ├── config.php             # Conexión PDO (entorno auto) + helpers + reglas de combate compartidas
│   ├── register.php            # Usuario + correo (sin contraseña)
│   ├── login.php               # Login por código: request_code / verify_code
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
│   ├── battle_action.php      # Enviar Atacar/Huir en PvP (el servidor calcula el daño)
│   ├── wild_battle_start.php  # Inicia una batalla silvestre (el servidor genera al enemigo)
│   └── wild_battle_action.php # Enviar Atacar/Huir en batalla silvestre (servidor autoritativo)
└── sql/
    ├── schema.sql                            # Esquema v1.1 completo (instalación nueva)
    ├── v1.0-seed-migration.sql               # Actualiza una base de antes del 31-08-2026, sin perder datos
    ├── v1.1-appearance-presets-migration.sql # Agrega el sistema de presets de apariencia
    └── archive/                              # Migraciones históricas 002-007, ya no hace falta importarlas
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
2. Ajusta credenciales en `api/config.php` si tu MySQL local no usa
   `root`/sin contraseña (`detectarEntornoLocal()` ya elige estos valores
   por defecto para localhost/127.0.0.1 automáticamente).
3. Levanta un servidor PHP desde la raíz del proyecto:
   ```bash
   php -S localhost:8000
   ```
   (o coloca la carpeta en `htdocs/` si usas XAMPP/MAMP)
4. Abre `http://localhost:8000/index.php`, crea una cuenta (usuario +
   correo) y entra con el código que te muestra la misma pantalla (modo
   desarrollo: no hace falta revisar correo).

> **¿Ya tenías el proyecto instalado antes de esta actualización?** Si tu
> base de datos ya existía antes del 31-08-2026 (v1.0-seed), importa
> `sql/v1.0-seed-migration.sql` (phpMyAdmin → tu base → "Importar") — es
> seguro de correr una sola vez, y no borra usuarios, partidas guardadas
> ni historial de login. Actualiza tu base para que quede igual a una
> instalación nueva: agrega la tabla `wild_battles` (si te faltaba, las
> batallas silvestres no funcionan sin ella), renombra una clave interna
> del inventario, quita la tabla `species` (ya redundante con
> `data/species.json`), y agrega un par de índices/relaciones que antes
> faltaban. Las migraciones más viejas (002 a 007) quedaron archivadas en
> `sql/archive/` — ya no hace falta importarlas, `v1.0-seed-migration.sql`
> las reemplaza a todas.
>
> Si es una instalación nueva desde cero, no hace falta ninguna migración:
> `schema.sql` ya incluye todo. El catálogo real que usa el juego vive en
> `data/species.json` (una sola fuente para JS y PHP, ver
> `data/README.md`), así que agregar o ajustar una criatura no requiere
> tocar la base de datos para nada.
>
> **¿Tu base ya está en v1.0-seed pero es de antes del 31-08-2026 por la
> tarde?** Importa también `sql/v1.1-appearance-presets-migration.sql` —
> agrega el sistema de presets de apariencia (elegir 1 de 3 opciones por
> género, ya no color libre). No cambia el look de tus cuentas ya
> creadas; solo la próxima vez que abran "Cambiar apariencia" van a ver
> el selector nuevo.

Si no tienes el backend corriendo, el juego igual es jugable: simplemente no
podrás guardar ni cargar partidas (verás un aviso en pantalla al presionar **S**).

## Cómo jugar

- Flechas o WASD: moverse por el mapa (cuadrícula, como los RPG 2D clásicos de Game Boy).
- Entrar a la hierba alta (verde oscuro) tiene una probabilidad de encuentro.
- Caminar hasta cualquier borde de Renca (el mapa principal) te lleva a la
  ruta de ese punto cardinal (Norte, Sur, Este u Oeste), y viceversa para
  volver.
- El gran árbol está en la esquina superior izquierda de Renca (ahí
  aparecen los jugadores nuevos) — se puede caminar todo alrededor de él.
  Los Cerros de Renca (con dos cuevas, una es la Cueva de Don Emilio) están
  más hacia el centro-norte, y el río Mapocho cruza todo el extremo sur
  del mapa (con un puente transitable justo frente a la salida sur).
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
- **Domesticación de criaturas**: el juego ahora empieza sin compañeros a
  propósito (ver la sección "Sin compañero inicial" más arriba) —
  `StarterSelectionScene.js` quedó guardado como referencia para cuando se
  construya este sistema.
- **NPCs y diálogos**: añade objetos interactivos al mapa con `setInteractive()`
  y una caja de texto estilo GB.
- **Multi-idioma**: a propósito no se incluyó (lo pediste así por ahora). Si
  más adelante lo quieres, el patrón típico es un `i18n.php` con una función
  `t('clave')` que devuelve el texto según `$_SESSION['lang']`, cargado antes
  que cualquier archivo que muestre texto — igual a como ya cargas
  `session_bootstrap.php` primero en cada página.
- **Correo real en producción**: `enviarCodigoPorCorreo()` en `api/login.php`
  usa `mail()`, que depende de que tu hosting tenga un MTA configurado. Si no
  llegan los correos, lo más simple es cambiar esa función para usar SMTP de
  verdad (PHPMailer + las credenciales SMTP de tu hosting o de un proveedor
  como Gmail/SendGrid).
- **CSRF en más endpoints**: por ahora `require_csrf()` solo protege
  login/registro (los puntos de entrada del patrón que pediste). Extenderlo
  a `save_game.php`, `challenge_send.php`, etc. es el mismo patrón, solo
  hay que mandar `csrf_token` desde el JS del juego también.
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
