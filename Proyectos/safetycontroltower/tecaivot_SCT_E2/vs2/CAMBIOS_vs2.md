# Cambios en esta entrega (e2_vs2)

Sobre la base de `e2_vs1`. Resumen para el equipo, no reemplaza al
Registro de cambios de alcance del Plan de Trabajo.

## 1. Módulo nuevo: Proyectos

- `lib/repositorios/ProyectoRepository.php`
- `api/proyectos/` completo: `common.php`, `listar.php`, `crear.php`,
  `editar.php`, `detalle.php`, `cambiar-estado.php`,
  `empresas-disponibles.php`, `gestion-proyectos.php`
- Asociación trabajador↔proyecto: `trabajadores-listar.php`,
  `trabajadores-buscar.php`, `trabajadores-asociar.php`,
  `trabajadores-desasociar.php`
- `js/proyectos.js`

Permisos: `administrador` / `administrador_completo` gestionan cualquier
empresa (deben elegirla en el selector); `cliente` / `jefatura` gestionan
solo la propia; `trabajador` solo lectura. Usa `lib/auth.php`
(`requireRole`), no un sistema de permisos propio.

**Pendiente real, no de este entregable:** la búsqueda de trabajadores
para asociar (`trabajadores-buscar.php`) va a devolver siempre vacío
hasta que exista el módulo de Trabajadores con datos — la tabla
`workers` sigue vacía. El código ya está listo para cuando eso pase, no
va a requerir cambios.

## 2. Módulo nuevo: Centros/Sedes

Mismo patrón que Proyectos, sin asociación de trabajadores (no aplica).
`lib/repositorios/CentroRepository.php`, `api/centros/` completo,
`js/centros.js`.

## 3. Hallazgo mientras se construía esto

`empresasIsGlobalAdmin()` en `api/empresas/common.php` solo reconoce el
rol `administrador_completo`, no `administrador`. Como ningún usuario
sembrado hoy tiene ese rol exacto (todos tienen `administrador` simple),
**nadie puede hoy acceder a `api/empresas/listar.php` ni ver la tarjeta
"Gestión de Empresas"**. Es la contracara del bug ya reportado en
`editar.php`/`detalle.php` de empresas (que sí aceptan `administrador`
pero no `administrador_completo`). No se tocó `api/empresas/` en esta
entrega — por eso `empresas-disponibles.php` (nuevo, usado por
Proyectos/Centros) acepta ambos roles a propósito en vez de reusar el
endpoint existente.

## 4. Cache-busting de assets (`$ASSET_VERSION`)

Definido en `session_bootstrap.php`, calculado solo a partir del
`filemtime()` más reciente entre los CSS/JS del proyecto. Aplicado con
comentario `<!-- build: ... -->` en todas las páginas HTML existentes y
en las 2 nuevas. En `index.php` hubo que agregar `global $ASSET_VERSION;`
dentro de `cargarPartial()`, porque los partials se cargan con `require`
adentro de una función y no ven variables globales sin declararlas.

## 5. Migración a servidor nuevo (`safetyco_SCT`)

- `config.php`: host/nombre de base actualizados a
  `201.148.104.98` / `safetyco_SCT`. Usuario/password del servidor
  viejo (`tecaivot` / la password de 105.87) NO se reutilizan — hay que
  configurar `DB_USER` y `DB_PASS` por variable de entorno en el
  hosting nuevo, o la app corta con "Configuración de base de datos
  incompleta" en vez de intentar conectar con datos equivocados.
- `safetyco_SCT(vs1).sql`: dump completo para importar en el servidor
  nuevo. Basado en el dump del 24-ago (`tecaivot_SCT(vs4).sql`, se
  elimina de este paquete porque queda reemplazado por este), con dos
  diferencias:
  - Se omiten los datos de `login_attempts` y `login_codes` (arranque
    limpio, son datos transitorios/de sesión).
  - Se agrega un bloque de backfill que siembra `administrador_completo`
    y `jefatura` para toda empresa que todavía no los tenga (el dump
    original solo tenía los 3 roles viejos). Idempotente.
  - **Probado de punta a punta** contra una instancia MariaDB real:
    import limpio, los 4 usuarios existentes migran con su empresa
    correcta, el backfill deja 5 roles por empresa, y correrlo dos
    veces no duplica filas.
- Pendiente fuera de este entregable: si además cambia el dominio (hoy
  hay varias referencias a `tecaivot.cl` en `head.php`, `contacto.php`,
  `bienvenida.php` y el "From" del correo en `login.php`), eso no se
  tocó acá — avisar si corresponde.
