# Cambios en esta entrega (e2_vs3)

Sobre la base de `e2_vs2` (ver `CAMBIOS_vs2.md` para lo de esa entrega:
Proyectos, Centros/Sedes, cache-busting, migración a `safetyco_SCT`).

## Módulo nuevo: Trabajadores (Perfil Trabajador)

- `lib/repositorios/TrabajadorRepository.php`
- `api/trabajadores/` completo: `common.php`, `listar.php` (con
  búsqueda por RUT/nombre/apellido/cargo + filtro de estado),
  `crear.php`, `editar.php`, `detalle.php`, `cambiar-estado.php`,
  `empresas-disponibles.php`, `gestion-trabajadores.php`
- `subir-foto.php`: endpoint separado (multipart, no JSON) para la
  foto — valida tamaño (3 MB), que el archivo sea una imagen real con
  `getimagesize()` (no alcanza con mirar la extensión), y guarda con
  nombre regenerado (no el nombre original del archivo que manda el
  navegador) en `uploads/trabajadores/`, carpeta con su propio
  `.htaccess` que bloquea ejecución de PHP como defensa adicional.
- `js/trabajadores.js`. Tarjeta agregada en el hub de Gestiones.

Mismo criterio de permisos que Proyectos/Centros (`lib/auth.php`,
`administrador`/`administrador_completo`/`cliente`/`jefatura` gestionan,
`+trabajador` solo lectura) — mismo patrón en las 3 entidades ya, no se
suma un cuarto esquema.

## Decisiones de diseño a tener presente

- **El RUT no se puede editar** después de creado — es la llave de
  negocio (`uq_worker_rut_company`) y ya puede estar referenciado desde
  `worker_projects`. Si hay un error de tipeo, se da de baja el registro
  y se crea uno nuevo, no se corrige en caliente.
- **Mismo RUT en dos empresas distintas SÍ está permitido** (un
  trabajador que presta servicio a más de un cliente) — la restricción
  real de la tabla es RUT + empresa, no RUT solo. Probado explícitamente.
- **"Historial básico"** (mencionado en el alcance de Etapa 1): no existe
  una tabla dedicada de historial de trabajador en el diccionario de
  base de datos, así que se interpretó como mostrar
  `date_create`/`last_update`/`created_by` (ya vienen en la tabla
  `workers`) en vez de construir un sistema de auditoría nuevo. Si hace
  falta un historial de cambios real campo a campo, es un alcance
  aparte — avisar si corresponde antes de asumir que ya está cubierto.
- **Fotos servidas desde una carpeta pública** (`uploads/trabajadores/`)
  con nombre no adivinable, no detrás de un login. Es la misma lógica de
  exposición que ya tiene, por ejemplo, el logo de la empresa en el
  sitio público — no es información sensible por sí sola, pero si más
  adelante se necesita que las fotos solo sean visibles para usuarios
  logueados de la misma empresa, eso requiere un endpoint dedicado que
  sirva el archivo con `readfile()` detrás de `requireLogin()`, no
  quedó así ahora. Marcado como decisión, no como olvido.

## Probado de punta a punta

14 pruebas de integración contra una base de datos real (`safetyco_SCT`
recién migrada): alta, RUT duplicado dentro de la empresa (se rechaza) y
en otra empresa (se permite), búsqueda por apellido, edición, foto, baja
lógica — y la interacción cruzada con Proyectos: un trabajador recién
creado aparece en el buscador de "agregar trabajador" de un proyecto, se
puede asociar, deja de aparecer como disponible una vez asociado, y se
puede desasociar. Todo pasó.
