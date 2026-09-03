# Cambios en esta entrega (e2_vs4)

Sobre la base de `e2_vs3` (Trabajadores). Paso 1 de "separar Tecaivot de
SCT": a qué base de datos apunta el sistema.

## `config.php` actualizado

**Rama local (`$isLocal`):**
- `DB_NAME` por defecto pasa de `tecaivot_SCT` a `safetyco_SCT`.
- Resto sin cambios (host `127.0.0.1`, puerto `3306`, usuario `root`,
  password vacía).

**Acción pendiente para cada developer:** la base local no se renombra
sola. Dos formas de dejarla lista:
1. Renombrar la base local existente `tecaivot_SCT` a `safetyco_SCT`
   (phpMyAdmin → Operaciones → "Renombrar la base de datos a"), o
2. Crear `safetyco_SCT` vacía e importar ahí
   `safetyco_SCT(vs1).sql` — el mismo archivo que ya se usó para migrar
   el servidor sirve igual para dejar una base local limpia con los 4
   usuarios y los roles ya sembrados.

Si alguien no quiere renombrar su base local todavía, puede seguir
apuntando a `tecaivot_SCT` sin tocar `config.php`: seteando
`DB_NAME=tecaivot_SCT` como variable de entorno en su máquina.

**Rama de servidor (`else`):**
- Host, base, usuario y password ya apuntan al servidor nuevo:
  `201.148.104.98` / `safetyco_SCT` / `safetyco` / la password
  provista. Antes de esta entrega, usuario y password quedaban
  intencionalmente sin fallback (para forzar variable de entorno); el
  equipo pidió volver a dejarlos hardcodeados acá como estaban para el
  servidor viejo, así que quedaron así.
- **Importante:** si este archivo llega a subirse a un repositorio
  compartido o público en algún momento, hay que sacar la password de
  acá y dejarla solo por variable de entorno (`DB_PASS`) — quedó una
  nota de esto en el propio comentario del archivo para que no se
  pierda de vista.
- El puerto queda con fallback `''` en vez de `'3306'` — **verificado
  que esto sí conecta**: `(int) ''` da `0`, y el cliente de MySQL
  interpreta el puerto `0` como "usar el puerto por defecto (3306)".
  Probado explícitamente contra una base real con `port=0` en el DSN.
  Si el servidor nuevo llegara a requerir un puerto no estándar, hay
  que setear `DB_PORT` por variable de entorno.

## Verificado

- Único lugar del proyecto que abre una conexión (`new PDO(...)`) sigue
  siendo `config.php` — `lib/db.php` es un puente que lo reutiliza, no
  hay conexiones duplicadas en ningún endpoint.
- Corrida completa de `config.php` (rama local, vía `APP_ENV=local`)
  contra una base `safetyco_SCT` recién migrada: conecta y devuelve las
  2 empresas existentes sin errores.
- Port `0` → puerto por defecto: confirmado con una conexión PDO real,
  no solo por documentación.

## Fuera de alcance de este paso (para cuando toque)

"Separar Tecaivot de SCT" no termina en la base de datos. El resto de la
marca Tecaivot sigue presente en el código y no se tocó acá:
`partials/navbar.php`, `partials/footer.php`, `partials/contacto.php`,
`partials/head.php` (meta tags `og:url`/`og:image`/`twitter:*` apuntan a
`www.tecaivot.cl`), los 5 archivos de `lang/` (`en/es/fr/pt/zh.php`),
`css/style.css`, `js/auth.js`, `images/logos/site.webmanifest`, y el
remitente del correo en `login.php` (`no-responder@tecaivot.cl`). Es
bastante más que un cambio de config — cuando corresponda avanzar con
esto, mejor tratarlo como su propio paso, no colarlo dentro de éste.
