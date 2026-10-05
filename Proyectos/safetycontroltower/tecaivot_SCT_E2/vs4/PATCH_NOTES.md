# Safety Control Tower (SCT) - vs5

## Base de esta entrega
- Fuente: `vs4_0410262211.rar`, copiada a una carpeta nueva `vs5`; vs4 no se modificó in situ.
- Alcance implementado: navbar superior/hamburguesa, mutualidad, bienvenida del trabajador, accesos personales, Logros y formulario de salud/antecedentes ocupacionales.
- Stack conservado: PHP nativo + PDO, MariaDB, Bootstrap 5.3, Bootstrap Icons y JavaScript vanilla.
- `sql/safetyco_SCT.sql` permanece byte a byte igual que en la fuente. Todo cambio de datos está en una migración aditiva nueva.

## Cambios funcionales

### Navbar
- Se reemplazó el menú de engranaje por un botón hamburguesa accesible (`bi-list` / `bi-x-lg`).
- El panel lista únicamente navegación habilitada por `currentUserHasCapability()`; las gestiones aparecen solo si corresponde al rol.
- Configuración queda al final del panel: tema claro/oscuro, idioma ES/EN/PT/FR/ZH y cierre de sesión como última acción.
- Cierre por backdrop, `Esc` y navegación; focus trap y devolución de foco al botón hamburguesa.
- Se agregó selector de mutualidad junto al logo: ACHS, Mutual de Seguridad, IST e ISL.
- La mutualidad se guarda por AJAX en el usuario de la sesión mediante POST + CSRF + whitelist, con auditoría de cambio.

### Bienvenida del trabajador
- Fila superior de tres tarjetas de altura uniforme: accesos directos, Logros y perfil.
- `Reportar` abre el alta de eventos; `Mis reportes` usa `?mine=1` y el filtro real se aplica en servidor con `currentUserId()`.
- Logros Health & Safety se calculan con asignaciones completadas (`state IN (2,3)`) y/o certificados respecto del total asignado.
- Estándar 16, TI y Medio Ambiente quedan en 0 con estado "Próximamente" y punto de extensión documentado en `LogrosRepository.php`.
- Grilla fija de 8 accesos: Health & Safety, Estándar 16, TI, Medio Ambiente, Cursos, Certificados, Mis Datos y Soporte.
- Se agregó hub Health & Safety respetando las capacidades existentes.
- Se agregó listado de certificados propio, filtrado exclusivamente por el usuario de sesión.
- Se agregó pantalla de perfil propio para no exponer Gestión de Usuarios al trabajador.
- Los roles no trabajador conservan sus bloques de Gestión debajo del layout de bienvenida.

### Formulario de salud y antecedentes ocupacionales
- Gate de servidor para roles definidos en `HEALTH_FORM_ROLES` (por defecto `trabajador`).
- Un trabajador sin formulario vigente es redirigido a `bienvenida.php` y recibe un modal bloqueante hasta completar y aceptar la declaración.
- Asistente de cinco pasos con progreso, campos condicionales, resumen final, validación por paso y actualización posterior desde Mis Datos.
- Validación de teléfonos, segundo contacto todo-o-nada, listas blancas, exclusividades Ninguna/Prefiero no informar y obligatorios condicionales tanto en cliente como en servidor.
- Cada actualización crea una nueva versión de perfil vigente y una nueva aceptación de declaración con fecha/hora, usuario, versión, hash SHA-256 del texto mostrado e IP opcional.
- Los campos libres de salud se cifran con AES-256-GCM. La clave se lee de `SCT_HEALTH_ENCRYPTION_KEY`; si falta o no sirve, lectura/escritura falla de forma segura.
- El perfil completo no se incorpora a listados. Un tercero solo puede consultar un usuario individual con `health_profile.view_emergency`; dicho acceso se audita sin registrar valores de salud.
- El permiso sensible mantiene fallback mínimo para `administrador_completo` y, además, puede concederse explícitamente mediante `permissions/role_permissions` sin activar globalmente la matriz granular que vs4 mantenía deshabilitada.

## Migración
Archivo: `sql/migrations/2026-10-05_vs5_navbar_bienvenida_salud.sql`

Agrega:
- `users.mutual_code VARCHAR(20) NULL`.
- `company_test.module_code VARCHAR(32) NOT NULL DEFAULT 'health_safety'`.
- `worker_health_profile`.
- `worker_health_declaration`.
- permiso `health_profile.view_emergency` si aún no existe.

La migración usa `ADD COLUMN IF NOT EXISTS`, `CREATE TABLE IF NOT EXISTS` e `INSERT ... WHERE NOT EXISTS`. Se verificó estructuralmente su repetibilidad para MariaDB 10.6+. El entorno de ejecución de esta entrega no dispone de un servidor MariaDB/MySQL para ejecutar físicamente dos pasadas; debe repetirse dos veces en staging antes de producción.

## Archivos nuevos
- `api/actividades/health-safety.php`
- `api/induccion/mis-certificados.php`
- `api/salud/common.php`
- `api/salud/formulario-obtener.php`
- `api/salud/formulario-guardar.php`
- `api/salud/emergencia-obtener.php`
- `api/usuarios/mi-perfil.php`
- `api/usuarios/mutualidad.php`
- `api/usuarios/perfil-propio-guardar.php`
- `js/health-form.js`
- `lib/health_crypto.php`
- `lib/health_gate.php`
- `lib/repositorios/LogrosRepository.php`
- `lib/repositorios/SaludRepository.php`
- `partials/health-form-modal.php`
- `sql/migrations/2026-10-05_vs5_navbar_bienvenida_salud.sql`
- `PATCH_NOTES.md`

## Archivos modificados
- `bienvenida.php`
- `config.php` (credenciales de servidor solo por variables de entorno; no se incorporaron secretos)
- `css/sct-v3-frontend.css`
- `js/eventos.js`
- `js/sct-v3-frontend.js`
- `lang/es.php`
- `lang/en.php`
- `lang/pt.php`
- `lang/fr.php`
- `lang/zh.php`
- `lib/auth.php`
- `lib/repositorios/EventoRepository.php`
- `partials/app-navbar.php`
- `api/eventos/eventos-listar.php`

## i18n y responsive
- Las claves nuevas están presentes en los cinco idiomas y las cadenas nuevas del flujo vs5 están traducidas.
- Navbar: panel derecho en desktop y ancho completo bajo la barra en móvil.
- Bienvenida: 4 tarjetas por fila en desktop, 2 en tablet/móvil y tamaño uniforme hasta 360 px.
- Formulario de salud: modal de pantalla completa en móvil y campos en una columna donde corresponde.
- Se conserva `html.sct-theme-dark` en navbar, bienvenida, tarjetas y formulario.

## Prueba de nivelación - siguiente tarea separada
No se implementó en esta entrega, tal como se solicitó separarla de 4.1-4.3. El motor actual usa `company_test.type` y asignaciones, pero no existe una persistencia específica del conjunto aleatorio de preguntas presentado por intento. Para hacer auditable un universo de 70 con selección de 15, la siguiente tarea debe definir esa traza por intento antes de agregar `nivelacion` o reutilizar `otro`; de lo contrario no se podría reconstruir qué 15 preguntas vio el trabajador.

## Validaciones ejecutadas
- `php -l`: 711 archivos PHP propios/no-vendor, 0 errores (CLI disponible: PHP 8.4.23).
- Escaneo de sintaxis nueva tocada: sin `match`, nullsafe `?->` ni arrow functions `fn`, manteniendo compatibilidad de sintaxis con PHP 7.3.
- `node --check`: 27 JS propios, 0 errores.
- ES/EN/PT/FR/ZH: misma cantidad de claves cargadas por PHP; claves nuevas disponibles en todos.
- Rutas principales del nuevo navbar y de las 8 tarjetas: existentes.
- `sql/safetyco_SCT.sql`: SHA-256 idéntico al baseline vs4.
- Pruebas unitarias locales del validador de salud: payload válido, exclusividad inválida y segundo contacto incompleto.
- Prueba local de cifrado: round-trip AES-256-GCM correcto y fallo seguro al retirar `SCT_HEALTH_ENCRYPTION_KEY`.
- Migración: chequeo estructural de guardas idempotentes aprobado; falta smoke test físico en MariaDB por no existir motor DB en este entorno.
- No se encontraron las credenciales sensibles del Plan de Trabajo dentro de vs5.

## Configuración necesaria antes de probar Salud
Definir en el servidor una variable de entorno `SCT_HEALTH_ENCRYPTION_KEY` de al menos 32 caracteres aleatorios. No guardar su valor en el repositorio. En servidor, `DB_USER` y `DB_PASS` también deben provenir de variables de entorno.

## Supuestos y preguntas abiertas (máx. 8)
1. Se mantienen exactamente las cuatro mutualidades indicadas; la lista está centralizada en `partials/app-navbar.php`.
2. `users_test_assigned.state IN (2,3)` se interpreta como actividad terminada, consistente con el Dashboard existente; un certificado también cuenta como completado.
3. Los módulos Estándar 16, TI y Medio Ambiente siguen sin fuente funcional y permanecen deshabilitados.
4. Salud bloquea por defecto solo a `trabajador`, mediante `HEALTH_FORM_ROLES`.
5. Mis Datos usa una vista propia porque `gestion-usuarios.php` administra terceros y no es adecuada para el trabajador.
6. La prueba de nivelación queda explícitamente para el siguiente parche/tarea, después de validar vs5.
7. Antes de producción falta ejecutar la migración dos veces en MariaDB staging y probar los cinco flujos con datos reales.

## Checklist manual por rol
### Trabajador nuevo
- [ ] Iniciar sesión y comprobar redirección a Bienvenida con modal bloqueante.
- [ ] Intentar abrir por URL directa Dashboard/Cursos/Eventos y comprobar que vuelve a Bienvenida.
- [ ] Probar todos los condicionales, exclusividades y segundo contacto parcial.
- [ ] Aceptar declaración; comprobar desbloqueo, fecha/hora/versión/hash y que el formulario pueda reabrirse desde Mis Datos.
- [ ] Elegir mutualidad y recargar: la selección debe persistir.
- [ ] Reportar un evento y comprobar que aparece en Mis reportes.

### Trabajador existente
- [ ] Confirmar que Bienvenida no bloquea si tiene perfil vigente v1.0.
- [ ] Verificar 3 tarjetas superiores y grilla 4x2 en desktop; 2 columnas en tablet/móvil.
- [ ] Revisar Logros Health & Safety con datos reales.
- [ ] Ver Certificados y descargar únicamente uno propio.
- [ ] Actualizar perfil de salud y comprobar nueva aceptación/versionado.

### Jefatura
- [ ] Confirmar que no se activa el gate de salud.
- [ ] Abrir hamburguesa y revisar navegación/gestiones autorizadas.
- [ ] Confirmar que los bloques de Gestión siguen visibles en Bienvenida.
- [ ] Verificar que Mis reportes con `mine=1` nunca permite indicar otro usuario.

### Administrador / Administrador completo
- [ ] Confirmar accesos de gestión existentes y que no se activa el gate de salud.
- [ ] Verificar empresas/permisos/historial según rol.
- [ ] Con `health_profile.view_emergency`, probar consulta individual de emergencia y revisar que se genere auditoría.
- [ ] Sin dicho permiso, confirmar HTTP 403.
- [ ] Probar claro/oscuro, idiomas y logout desde el hamburguesa.

## Ajuste visual 05-10-2026 — Accesos directos responsive
- Los 8 accesos directos de `bienvenida.php` ahora usan tarjetas rectangulares con esquinas redondeadas.
- Paleta limitada a colores de marca SCT: navy, celeste y dorado.
- Ícono y título se presentan como un bloque centrado, con el ícono a la izquierda del título.
- Espaciado y alturas uniformes para evitar saltos visuales entre tarjetas.
- Responsive específico: 4 columnas en escritorio, 2 en tablet y 1 en móvil; ajuste adicional para 360 px.
- Se conserva la lógica, rutas, permisos, estados "Próximamente", modo claro/oscuro y accesibilidad de foco.
- Archivos modificados: `bienvenida.php` y `css/sct-v3-frontend.css`.

## Ajuste visual/navegación 05-10-2026 — Mutualidad, hamburguesa y logos
- El selector de mutualidad del navbar muestra el logotipo correspondiente a ACHS, Mutual de Seguridad, IST o ISL, manteniendo nombre y persistencia existentes.
- El menú hamburguesa usa como navegación principal los mismos 8 módulos y el mismo orden de `Accesos directos` de Bienvenida: Health & Safety, Estándar 16, TI, Medio Ambiente, Cursos, Certificados, Mis Datos y Contactar soporte.
- Estándar 16, TI y Medio Ambiente conservan el estado `Próximamente` y no generan una ruta falsa.
- Los accesos de gestión de roles superiores siguen disponibles en una sección separada `Gestión`, según `lib/auth.php`.
- El isotipo SCT del navbar ya no usa fondo degradado, borde ni caja: se renderiza directamente para conservar sus colores originales.
- Cuando la empresa del usuario posee `company.logo_path`, Bienvenida muestra ese logotipo directamente junto al saludo, sin tarjeta, fondo, borde, sombra ni filtros de color.
- En tablet y móvil se reducen de forma proporcional los logos y el selector; en móvil el logo corporativo pasa a una posición centrada sin perder proporción.
- Archivos modificados: `partials/app-navbar.php`, `bienvenida.php`, `js/sct-v3-frontend.js`, `css/sct-v3-frontend.css` y este `PATCH_NOTES.md`.
- No hay cambios de base de datos ni de contratos API en este ajuste.

## Ajuste visual 05-10-2026 — mutualidades y menú hamburguesa

- Los logos de ACHS, Mutual de Seguridad, IST e ISL ahora se muestran con `object-fit: contain`, dimensiones máximas y proporción original, evitando recortes o deformaciones.
- Se amplió el espacio útil del logo en el selector superior y en el listado desplegable, con medidas específicas para escritorio, tablet, móvil y 390 px.
- Se ajustó la tipografía de los 8 accesos directos del menú hamburguesa para mantener legibilidad y evitar desbordes en textos largos.
- No se modificaron endpoints, base de datos ni lógica funcional.
