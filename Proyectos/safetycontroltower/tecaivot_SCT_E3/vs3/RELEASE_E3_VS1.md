# Safety Control Tower — E3-VS1

Baseline: E2-VS4  
Fecha de corte: 07-10-2026

## Despliegue

1. Respaldar código y base de datos E2-VS4.
2. Conservar el `config.php` propio del ambiente; el paquete de entrega no debe sobrescribir credenciales.
3. Publicar el código E3-VS1.
4. Ejecutar `migracion sql/02_2026-10-07_e3_vs1_roles_permissions.sql`.
5. Confirmar en `schema_migrations` la versión `2026-10-07-e3-vs1`.
6. Validar login password -> OTP -> Bienvenida con cada perfil Demo.
7. Ejecutar regresión por matriz de perfiles según `docs/E3_VS1_ACCESS_REFACTOR_AUDIT.md`.

## Perfiles oficiales

1. SuperUsuario
2. Gerente
3. Administrador Cliente
4. Jefatura
5. Usuario
6. Paramédico
7. Contratista

`Trabajador` es una entidad operacional en `workers`, no un perfil de autenticación.

## Notas

- E3-VS1 es la única versión activa del código de esta rama.
- `permissions` + `role_permissions` son autoritativos sólo después de registrar la migración E3-VS1.
- El archivo legacy duplicado `lib/repositorios/auth.php` ya no existe.
- Navbar y Bienvenida comparten `app/Navigation/NavigationRegistry.php`.
- Los endpoints `api/` no ejecutan SQL directamente: delegan en repositories.
- El requerimiento documental específico de Contratista/subcontratista sigue pendiente de modelado de dominio; no se concedió acceso más amplio como atajo.

## Ajuste semántico Usuario / Trabajador + Jefatura

- `workers` representa personas/trabajadores como dato operacional.
- `users` representa identidades con acceso al sistema.
- `usuario` es el rol base de un trabajador/persona con cuenta SCT.
- `trabajador` deja de ser un rol de autenticación.
- `jefatura` vuelve a ser un perfil operativo independiente.
- `JefaturaEmpresaDemo@demoSCT.cl` vuelve a estar habilitado.
- `TrabajadorEmpresaDemo@demoSCT.cl` queda inactivo porque un trabajador sin acceso no debe poseer login.


## Onboarding obligatorio — 08-10-2026
- Obligatorio para todos los perfiles salvo `superusuario`.
- Paso 1: datos + aceptación de uso de datos.
- Paso 2: evaluación de 15 preguntas aleatorias sobre un banco de 70.
- Los usuarios existentes también quedan sujetos al gate si no han completado los pasos.
- Cuenta QA: `UsuarioNuevoEmpresaDemo@demoSCT.cl`.
- Se almacena puntaje; no se exige nota mínima hasta definición de Sponsor.


## Convención de entrega — 08-10-2026

- Las migraciones SQL se centralizan únicamente en `migracion sql/`.
- Se eliminan las ubicaciones SQL duplicadas heredadas de entregas anteriores.
- No se distribuye un dump completo de base de datos en la entrega operativa.
- Los cinco idiomas contractuales permanecen centralizados en `i18n.php`:
  `es`, `en`, `pt`, `fr`, `zh`.
- El nuevo onboarding utiliza el mismo sistema i18n y permite cambiar idioma
  antes de completar el registro.
- La aceptación de datos registra además el idioma del texto aceptado.


## Perfil inicial extendido — 08-10-2026

El Paso 1 de onboarding ahora confirma y almacena:
- grupo/nivel de usuario (informativo, no editable);
- proyecto al que pertenece;
- contratación mediante contratista y nombre de contratista cuando corresponde;
- mutualidad/afiliación laboral: ACHS / ISL / IST / Mutual de Seguridad;
- años de experiencia en el cargo actual;
- nombre y teléfono del contacto de emergencia;
- relación: Pareja / Familia directa / Amigo(a) / Otro.

Se agrega `user_profile_details` como maestro de antecedentes generales confirmados.
Los datos de afiliación de salud y contacto de emergencia se reutilizan como prellenado
del módulo de Salud, manteniendo `worker_health_profile` como declaración/snapshot sanitario.

Migración nueva:
`migracion sql/05_2026-10-08_e3_vs1_profile_completion_extended.sql`.


# E3-VS1 FUNCIONAL — consolidación 08-10-2026

Esta entrega se declara como la versión funcional correspondiente a **Etapa 3 VS1**.

Ajuste final de afiliación:
- El campo obligatorio utiliza exclusivamente las cuatro opciones vigentes en SCT:
  - ACHS — Asociación Chilena de Seguridad
  - ISL — Instituto de Seguridad Laboral
  - IST — Instituto de Seguridad del Trabajo
  - Mutual — Mutual de Seguridad
- Se reutiliza `users.mutual_code` como fuente canónica.
- El onboarding replica la presentación visual con logos usada por la navbar de `bienvenida.php`.
- Se elimina la duplicación `user_profile_details.health_system`.
- Navbar, onboarding, API de mutualidad y formulario de salud comparten el mismo catálogo canónico `SctMutualityOptions`.

Migración incremental:
`migracion sql/06_2026-10-08_e3_vs1_mutuality_canonical.sql`


## E3-VS1 — mejora de onboarding y contratistas

- El Paso 2 permite volver explícitamente al Paso 1 mediante `Volver a mis datos`.
- Mientras la evaluación esté pendiente, `onboarding.php?step=profile` permite revisar y guardar nuevamente el formulario.
- La evaluación existente no se elimina ni se reinicia al retroceder.
- Se crea `contractor_companies` como catálogo normalizado por empresa cliente.
- Cuando el usuario declara contratación mediante contratista, debe seleccionar una empresa real.
- La selección utiliza búsqueda por nombre, nombre de fantasía o RUT.
- El selector es asíncrono, responsive y compatible con escritorio, tablet y teléfono.
- `user_profile_details.id_contractor_company` reemplaza el texto libre como fuente funcional.
- Se mantienen los cinco idiomas: es, en, pt, fr y zh.
- Nueva migración: `migracion sql/07_2026-10-08_e3_vs1_contractors_onboarding_back.sql`.


# ETAPA 3 VS1 — VERSIÓN FUNCIONAL CONSOLIDADA

Esta entrega pasa a ser la referencia funcional de **Etapa 3 VS1 (E3-VS1)**.

Cambios finales incorporados:
- Se elimina el gate independiente del "Formulario de Salud y Antecedentes Ocupacionales".
- El perfil `usuario` completa esos antecedentes obligatorios dentro del onboarding.
- `worker_health_profile` y `worker_health_declaration` continúan almacenando la información sensible e histórica.
- El formulario/modal de salud se conserva como editor voluntario desde `bienvenida.php` y `Mi Perfil`.
- La mutualidad mantiene una fuente única: `users.mutual_code`.
- Bienvenida muestra para Usuario la última evaluación realizada y su resultado.
- Se agrega `Mis evaluaciones`, con historial, estado, resultados e ingreso para retomar las evaluaciones disponibles.
- Usuario recibe `audits.execute` y `self_assessments.execute` únicamente para ejecutar sus propias asignaciones.
- Los módulos visibles para Usuario son:
  - Health & Safety
  - Medio Ambiente
  - Arqueología
  - Paleontología
  - Estándar 16
  - Major Risk
  - Trabajo de Alto Riesgo
  - One Safety
- Se mantienen los cinco idiomas oficiales: `es`, `en`, `pt`, `fr`, `zh`.
- Se mantienen criterios responsive para escritorio, tablet y teléfono.
- Migración incremental: `migracion sql/08_2026-10-08_e3_vs1_onboarding_health_user_experience.sql`.


## ETAPA 3 VS1 — corrección de onboarding y refactor visual

- Corregido el fallo genérico `No fue posible guardar el registro inicial`.
- Causa identificada en la integración de salud: `healthSaveProfile()` exigía
  `SCT_HEALTH_ENCRYPTION_KEY` antes de persistir el Paso 1. En localhost,
  donde la variable no estaba configurada, la transacción completa fallaba.
- En `local/development` se genera una clave aleatoria persistente en
  `var/private/health.key` con permisos restrictivos. El archivo no se incluye
  en la entrega y no debe versionarse.
- En ambientes distintos de local/desarrollo se mantiene la exigencia de
  `SCT_HEALTH_ENCRYPTION_KEY`; no se degrada a texto plano.
- Los errores de cifrado/configuración ahora entregan un mensaje específico y
  siguen registrando el detalle técnico en el log del servidor.
- Paso 1 reorganizado por secciones:
  1. Cuenta y asignación.
  2. Datos personales y laborales.
  3. Relación contractual y mutualidad.
  4. Contactos de emergencia.
  5. Salud y antecedentes ocupacionales.
  6. Declaraciones y consentimiento.
- Contacto principal y segundo contacto utilizan el mismo orden:
  Nombre → Teléfono → Relación.
- Se diferencian explícitamente las etiquetas:
  `Relación con el contacto principal` y
  `Relación con el segundo contacto`.
- Se mantienen cinco idiomas y comportamiento responsive.


## ETAPA 3 VS1 — consentimiento, navbar y portal Usuario

### Consentimiento de onboarding
- Se reemplazan dos aceptaciones separadas por una única aceptación explícita.
- La interfaz informa de forma diferenciada:
  - datos personales/laborales;
  - datos sensibles de salud y antecedentes ocupacionales;
  - finalidades funcionales;
  - acceso restringido.
- La misma aceptación genera dos trazas técnicas independientes:
  - `user_data_consent`;
  - `worker_health_declaration`.
- La versión de consentimiento pasa a `SCT-UNIFIED-CONSENT-v2.0`.
- La redacción mejora claridad, alcance y trazabilidad funcional, pero debe ser
  revisada por asesoría jurídica antes de considerarse texto legal definitivo.

### Navbar
- `partials/app-navbar.php` sigue siendo el único navbar autenticado.
- Las opciones son resueltas centralmente desde `SctNavigationRegistry::navbar()`.
- Todos los perfiles autenticados reciben acceso `Inicio → bienvenida.php`.
- Para `usuario`, el navbar muestra únicamente:
  - Inicio;
  - Mis reportes;
  - Mis evaluaciones;
  - Mis datos;
  - Módulos.
- Los demás perfiles conservan navegación por capacidades y módulos de gestión.

### Bienvenida del perfil Usuario
Sólo se muestran como opciones funcionales:
1. Mis reportes.
2. Mis evaluaciones, con último resultado e historial.
3. Mis datos y edición de salud.
4. Módulos: Health & Safety, Medio Ambiente, Arqueología, Paleontología,
   Estándar 16, Major Risk, Trabajo de Alto Riesgo y One Safety.

Se ocultan para Usuario las secciones genéricas de accesos directos y gestión
que podían exponer opciones adicionales.


## ETAPA 3 VS1 — FIX consentimiento resumido + borrador evaluación

### Consentimiento
- Se mantiene una sola aceptación.
- La redacción fue resumida a una única declaración que cubre:
  - veracidad de la información;
  - datos personales/laborales;
  - datos de emergencia;
  - datos sensibles de salud y antecedentes ocupacionales;
  - finalidades operativas, de seguridad, salud ocupacional, emergencias,
    evaluaciones, cumplimiento y trazabilidad;
  - acceso restringido;
  - protección y conservación limitada;
  - derechos de acceso, actualización, rectificación o eliminación cuando
    corresponda según normativa aplicable.
- La versión pasa a `SCT-UNIFIED-CONSENT-v2.1`.
- Debe existir revisión jurídica formal antes de considerar el texto una
  cláusula legal definitiva.

### Borrador de evaluación inicial
- Se crea `onboarding_assessment_draft_answers`.
- Cada selección se guarda automáticamente como borrador.
- Al volver desde la evaluación al Paso 1, se fuerza un último guardado antes
  de navegar.
- Al regresar al Paso 2 se restauran automáticamente las opciones elegidas.
- Las respuestas finales continúan almacenándose únicamente en
  `onboarding_assessment_answers`.
- Al finalizar correctamente la evaluación, el borrador del intento se elimina.
- Nueva migración:
  `migracion sql/09_2026-10-08_e3_vs1_onboarding_assessment_draft.sql`.


## ETAPA 3 VS1 — FIX login, etiquetas, accesos Usuario y editor unificado

### Login / límites
Se aplican los valores solicitados:
- MAX_SOLICITUDES_CODIGO_IP = 10
- MAX_SOLICITUDES_CODIGO_USR = 5
- VENTANA_SOLICITUD_MINUTOS = 30
- MAX_INTENTOS_AUTENTICACION_USR = 10
- MAX_INTENTOS_AUTENTICACION_IP = 500
- MAX_INTENTOS_CODIGO = 10
- VENTANA_INTENTOS_MINUTOS = 10

Los mismos valores se reflejan en `SctAuthConfig`, que es la fuente efectiva
consumida por el controlador de autenticación.

### Onboarding
- Se normaliza la etiqueta de teléfono para ambos contactos de emergencia.
- El formulario de edición de salud utiliza también la misma terminología.

### Bienvenida Usuario
- Mis reportes, Mis evaluaciones y Datos de usuario adoptan el mismo patrón
  visual de tarjetas/iconos de los módulos.
- Mis reportes abre directamente los reportes del usuario.
- Mis evaluaciones muestra la última evaluación:
  - estado aprobado: verde;
  - estado reprobado: rojo;
  - cuando sólo existe porcentaje: >=60% verde, <60% rojo.
- Datos de usuario abre el editor unificado.

### Editor unificado de usuario + salud
El modal existente de salud incorpora:
- nombre;
- apellido;
- correo (informativo);
- RUT (informativo);
- idioma;
- teléfono;
- mutualidad;
- contactos de emergencia;
- antecedentes de salud y ocupacionales.

La actualización de datos básicos y salud se guarda de forma transaccional.
Para el perfil `usuario`, el antiguo `mi-perfil.php` redirige al editor
unificado de `bienvenida.php?edit=profile`.

### QA
El reset de usuarios nivel `usuario` elimina además los códigos OTP y los
registros de intentos de acceso creados durante el día actual para esas cuentas,
sin borrar la auditoría funcional/de negocio.


## ETAPA 3 VS1 — FIX banco de preguntas multidioma + idioma onboarding

### Banco de 70 preguntas
- Se crean versiones completas en:
  - Español (`es`)
  - English (`en`)
  - Português (`pt`)
  - Français (`fr`)
  - 中文 (`zh`)
- Cada idioma contiene 70 preguntas y 280 alternativas.
- La respuesta correcta sigue siendo una propiedad única de la alternativa
  base; las traducciones no duplican lógica de corrección.
- Nuevas tablas:
  - `onboarding_question_translations`
  - `onboarding_question_option_translations`
- Migración:
  `migracion sql/10_2026-10-08_e3_vs1_onboarding_question_i18n.sql`.

### Administración
- Se crea `api/preguntas/gestion-preguntas.php`.
- El acceso depende exclusivamente de `questions.manage`.
- La misma capability puede asignarse mediante la matriz RBAC al perfil que
  corresponda.
- El editor permite:
  - crear preguntas;
  - editar las cinco traducciones;
  - editar cuatro alternativas por idioma;
  - definir la respuesta correcta;
  - activar/desactivar la pregunta.
- Una pregunta asignada a un intento en curso no desaparece si un
  administrador la desactiva posteriormente.

### Idioma del onboarding
- El selector guarda inmediatamente `users.language`.
- La sesión i18n se actualiza en el mismo cambio.
- En Paso 1 se actualizan los textos sin perder los valores aún no guardados.
- En Paso 2 se guarda primero el borrador de respuestas y luego se recarga el
  mismo intento en el idioma elegido.
- Las 15 preguntas siempre se resuelven utilizando el idioma persistido del
  usuario, con fallback a español.
- Tras guardar Paso 1, la evaluación continúa respetando el idioma elegido.

### QA
- El reset del nivel `usuario` restablece también `users.language='es'`.
- Se mantienen la limpieza de OTP y `login_attempts` del día para las cuentas
  QA, evitando bloqueos de autenticación durante las pruebas.


## ETAPA 3 VS1 — FIX guardado inicial tras i18n

- Se elimina la escritura redundante de `users.language` desde `onboarding.php`.
- El cambio de idioma queda serializado: primero se persiste idioma y luego se recarga.
- Durante el cambio de idioma en Paso 1 se guarda un borrador local en `sessionStorage` y se restaura tras la recarga, evitando perder datos escritos.
- Mientras el idioma se está cambiando, se bloquea el submit lógico del Paso 1.
- `perfil-guardar.php` resuelve el texto de consentimiento en el idioma enviado por el formulario **antes** de iniciar el guardado y, después del commit, sólo sincroniza la sesión; no vuelve a escribir el registro del usuario.
- El banco de preguntas multidioma y la migración 10 no cambian.
- No requiere una nueva migración SQL.


## ETAPA 3 VS1 — FIX confirmación de resultado evaluación inicial

### Evaluación inicial
- El botón final se presenta como `Aceptar y guardar` en los cinco idiomas.
- Las 15 respuestas se guardan antes de mostrar el resultado.
- El endpoint devuelve:
  - total;
  - correctas;
  - incorrectas;
  - porcentaje correcto;
  - porcentaje incorrecto;
  - puntaje final.
- Después del guardado se abre un modal de confirmación en el idioma activo.
- El modal informa:
  - que el proceso inicial fue completado;
  - respuestas correctas y su porcentaje;
  - respuestas incorrectas y su porcentaje;
  - resultado final.
- El resultado final usa la convención visual vigente:
  - >= 60%: verde;
  - < 60%: rojo.
- El modal no se cierra con Escape ni haciendo clic fuera; el Usuario debe
  presionar `Aceptar y continuar`.
- Sólo después de esa confirmación se navega a `bienvenida.php`.

### Responsive / accesibilidad
- Diseño centrado para escritorio/tablet.
- En teléfono los resultados pasan a una columna y el botón ocupa todo el ancho.
- `aria-labelledby`, `aria-describedby` y región modal de Bootstrap.
- Soporte de los cinco idiomas del sistema.

### QA SQL
Se separan dos scripts:
- `QA_RESET_USUARIOS_NIVEL_USUARIO_E3_VS1.sql`
- `QA_RESET_LOGS_DIA_USUARIOS_NIVEL_USUARIO_E3_VS1.sql`

El primero reinicia onboarding/evaluación/salud del nivel Usuario.
El segundo limpia únicamente OTP e intentos de autenticación del día para
esas cuentas QA.


## ETAPA 3 VS1 — FIX visual evaluación inicial onboarding

### Aprovechamiento del espacio
- El bloque de evaluación utiliza un ancho mayor que el formulario de perfil.
- En escritorio y tablet con ancho suficiente, las cuatro alternativas se
  distribuyen en dos columnas.
- En teléfono pasan automáticamente a una sola columna.
- Se reducen márgenes laterales en pantallas muy estrechas sin disminuir los
  objetivos táctiles.

### Relación visual pregunta / alternativa
- Cada pregunta rota entre cinco gamas neutrales:
  - azul;
  - cian;
  - violeta;
  - ámbar;
  - índigo.
- No se utilizan rojo ni verde para las selecciones, evitando comunicar
  aprobado/reprobado antes de finalizar la evaluación.
- Al seleccionar una alternativa:
  - se conserva el radio seleccionado;
  - se resalta la línea completa de la alternativa;
  - se resalta la tarjeta completa de la pregunta;
  - se refuerza el número de la pregunta con el mismo acento.

### Interacción
- Se añade una microanimación al cambiar de alternativa.
- La animación puede repetirse al cambiar la respuesta de la misma pregunta.
- Se respeta `prefers-reduced-motion`.
- Las respuestas restauradas desde borrador aparecen correctamente resaltadas
  sin ejecutar una animación inicial artificial.

### Compatibilidad
No modifica:
- banco multidioma;
- IDs de preguntas u opciones;
- borradores;
- cálculo de resultados;
- modal final de resultado;
- flujo de guardado;
- lógica de idiomas.


## ETAPA 3 VS1 — FIX visual unificado datos de usuario + cuestionario

### Paso 1 — Datos de usuario
- El formulario adopta el mismo lenguaje visual del cuestionario:
  - tarjetas por sección;
  - numeración destacada;
  - cinco gamas de acento rotativas;
  - mejor uso del ancho disponible.
- Las selecciones de mutualidad, salud, antecedentes y consentimiento
  resaltan la alternativa completa y el bloque relacionado.
- Los grupos radio/checkbox responden visualmente a la selección.
- Las reglas exclusivas de salud siguen sincronizando correctamente estados
  visuales cuando una opción desmarca otras.
- Se añade una microanimación al seleccionar.

### Paso 2 — Cuestionario
- Se conserva el fix visual ya validado:
  - dos columnas de alternativas en escritorio/tablet;
  - una columna en teléfono;
  - pregunta y alternativa seleccionada resaltadas;
  - azul, cian, violeta, ámbar e índigo;
  - sin rojo/verde durante captura;
  - animación discreta.

### Responsive
- Escritorio: ancho útil mayor y grupos de salud de hasta tres columnas.
- Tablet: dos columnas donde el ancho lo permite.
- Teléfono: una columna, padding reducido y objetivos táctiles conservados.
- Se mantiene soporte `prefers-reduced-motion`.

### Compatibilidad
No modifica:
- validaciones;
- payload de onboarding;
- guardado inicial;
- cambio de idioma;
- banco de 70 preguntas;
- borradores;
- resultado final;
- base de datos.


## ETAPA 3 VS1 — HOTFIX Paso 1 + contrato indefinido + validación visual

### Corrección del Paso 1
- La capa visual agregada en el fix anterior queda desacoplada del estado
  funcional de los inputs.
- Los efectos visuales ya no intervienen en `checked`, `value` ni en la
  construcción del payload.
- Antes de llamar a `perfil-guardar.php`, el frontend valida:
  - campos HTML requeridos;
  - RUT;
  - teléfonos;
  - selección de contratista cuando corresponde;
  - contactos de emergencia;
  - condiciones de salud;
  - medicamentos;
  - alergias y reacción severa;
  - antecedentes ocupacionales;
  - restricciones;
  - consentimiento.
- Un formulario incompleto no llega al backend.

### Contrato indefinido
- El selector de proyecto incluye `Contrato indefinido` en los cinco idiomas.
- Se representa como una relación sin proyecto específico:
  `confirmed_project_id = NULL`.
- No se crea un proyecto ficticio y no se viola la FK hacia `projects`.
- No requiere migración de base de datos.

### Validación visual
- Una sección completamente respondida se refuerza usando su color de acento.
- Si al guardar falta información:
  - se marca en rojo la sección pendiente;
  - se marca el campo o grupo correspondiente;
  - se hace scroll suave a la primera sección incompleta;
  - se posiciona el foco en el primer control pendiente.
- El rojo se reserva exclusivamente para error de validación; las selecciones
  normales continúan usando azul/cian/violeta/ámbar/índigo.

### Responsive
Se conserva el comportamiento para escritorio, tablet y teléfono y el soporte
`prefers-reduced-motion`.


## ETAPA 3 VS1 — HOTFIX guardado Paso 1 / compatibilidad de esquema

### Causa revisada
El guardado de `user_profile_details` asumía exclusivamente el esquema final
donde `health_system` ya había sido eliminado por la migración de mutualidad.

En instalaciones de QA que conservan la columna histórica
`user_profile_details.health_system` como `NOT NULL`, el INSERT del Paso 1
falla con una excepción SQL y `perfil-guardar.php` responde con el mensaje
genérico de guardado inicial.

### Corrección
- `saveProfileDetails()` detecta en tiempo de ejecución si la columna histórica
  `health_system` continúa presente.
- Si existe, escribe el mismo código canónico de mutualidad utilizado por
  `users.mutual_code`.
- Si no existe, utiliza únicamente el esquema normalizado actual.
- No se modifica el modelo canónico: `users.mutual_code` sigue siendo la fuente
  de verdad.
- No requiere nueva migración para desbloquear el onboarding.

### Diagnóstico
La transacción del Paso 1 ahora registra la etapa exacta en `error_log`:
- ensure_state
- user_profile
- mutuality
- profile_details
- consent
- health_profile
- mark_profile
- commit

Esto no expone el error SQL al Usuario, pero permite aislar inmediatamente
cualquier incidencia restante de integración.


## ETAPA 3 VS1 — FIX bienvenida unificada para todos los perfiles

### Cabecera funcional
Todos los perfiles autenticados muestran el mismo bloque superior:
1. Eventos reportados.
2. Mis evaluaciones.
3. Datos de usuario.

La presentación reutiliza exactamente las tarjetas responsivas ya validadas
para el perfil `usuario`.

### Eventos reportados
- Se reemplaza el texto `Mis reportes` por `Eventos reportados`.
- El cambio está traducido en `es`, `en`, `pt`, `fr` y `zh`.
- La tarjeta abre los eventos asociados al usuario cuando existe
  `events.view`.
- Si un perfil no dispone de esa capability, la tarjeta se mantiene visible
  pero deshabilitada para respetar RBAC.

### Mis evaluaciones
- La última evaluación se calcula ahora para cualquier perfil autenticado.
- Se conserva la lógica visual vigente:
  - estado aprobado: verde;
  - estado reprobado: rojo;
  - cuando sólo existe porcentaje: >=60% verde, <60% rojo.
- La tarjeta sigue enlazando al historial completo del usuario.

### Datos de usuario
- Para `usuario` se conserva el editor unificado de datos + salud.
- Para los demás perfiles se utiliza `api/usuarios/mi-perfil.php`.
- Se elimina `my_data` del bloque inferior de accesos directos de los perfiles
  no Usuario para evitar duplicar la misma función.

### Contenido inferior
Después de las tres tarjetas comunes:
- `usuario` conserva sus módulos operacionales;
- los demás perfiles conservan sus módulos directos permitidos;
- los módulos de gestión continúan filtrándose por capability/RBAC.

No se modifica onboarding, autenticación, base de datos ni permisos.


## ETAPA 3 VS1 — FIX alcance de usuarios por perfil

### Política canónica de alcance
- `superusuario`
  - visualiza y administra todos los usuarios del sistema.
- `gerente`
  - visualiza y administra todos los perfiles de su empresa salvo
    `superusuario`.
- `administrador_cliente`
  - visualiza y administra todos los perfiles de su empresa salvo
    `superusuario`.
- `jefatura`
  - visualiza y administra exclusivamente usuarios explícitamente asignados.
- `paramedico`
  - visualiza exclusivamente usuarios explícitamente asignados.
  - no recibe permisos de edición por este cambio.
- `contratista`
  - visualiza exclusivamente usuarios explícitamente asignados.
  - no recibe permisos de edición por este cambio.
- `usuario`
  - conserva autogestión de su propio perfil.

### Asignaciones
Se crea `user_supervision_assignments`.

Gerente, Administrador Cliente y SuperUsuario pueden asociar un usuario a una
o más cuentas con rol:
- Jefatura.
- Paramédico.
- Contratista.

La relación se administra desde Gestión de Usuarios.

### RBAC
- Paramédico y Contratista reciben `users.view`.
- El permiso por sí solo no amplía alcance: la capa de autorización exige una
  relación activa en `user_supervision_assignments`.
- Jefatura conserva sus capabilities actuales, pero las operaciones de
  administración quedan limitadas a usuarios asignados.
- Gerente y Administrador Cliente pueden administrar niveles 2–7 de su propia
  empresa; SuperUsuario continúa con alcance global.

### Seguridad
- Administrador/Gerente no pueden visualizar ni administrar SuperUsuario.
- Los endpoints de listado, detalle, edición, estado, acceso y fotografía
  utilizan la misma política de alcance.
- Paramédico/Contratista acceden a Gestión de Usuarios en modo lectura.
- Una asignación antigua se desactiva al reemplazar responsables o mover un
  usuario de empresa.

### UI
El formulario de Gestión de Usuarios incorpora un selector múltiple responsivo
de responsables asignados. Sólo aparece para actores autorizados a mantener
estas relaciones.

### Migración
`migracion sql/11_2026-10-08_e3_vs1_user_management_scope.sql`


## ETAPA 3 VS1 — FIX corrección de alcance Gerente

### Regla corregida
Se separa formalmente el alcance de visualización del alcance de administración.

- `superusuario`
  - ve y administra niveles 1–7.
- `administrador_cliente`
  - ve y administra niveles 2–7 de su empresa.
  - puede administrar Gerentes.
- `gerente`
  - ve niveles 2–7 de su empresa.
  - administra únicamente niveles 3–7.
  - no puede crear, editar, desactivar, gestionar acceso, cambiar rol ni
    administrar permisos de otro Gerente.
- `jefatura`
  - ve niveles 2–7 sólo cuando existe asignación explícita.
  - administra niveles 3–7 sólo cuando existe asignación explícita.
  - un Gerente asignado queda en modo lectura.
- `paramedico`
  - ve niveles 2–7 sólo cuando existe asignación explícita.
  - no administra usuarios.
- `contratista`
  - ve niveles 2–7 sólo cuando existe asignación explícita.
  - no administra usuarios.
- `usuario`
  - conserva acceso a su propio perfil.

### Autogestión
Un Gerente conserva la edición básica de su propio perfil mediante las reglas
de autogestión existentes. Esto no le permite modificar rol, empresa, estado,
credenciales administrativas ni permisos.

### Implementación
`RolePolicy` incorpora `viewable_user_levels` separado de
`managed_user_levels`.

`authCanViewUserTarget()` usa `viewable_user_levels`.
`authCanManageUserTarget()` usa `managed_user_levels`.

La regla también afecta cualquier módulo que use `authManagedUserLevels()`,
incluida la administración de permisos.

No requiere migración SQL ni cambios de estilos.


## ETAPA 3 VS1 — FIX Gestión de Usuarios

### Nuevo usuario
El flujo de alta ahora comienza obligatoriamente validando `workers`.

1. Seleccionar empresa cuando corresponda.
2. Buscar por RUT, correo o nombre.
3. Si existe un worker sin cuenta:
   - se selecciona el worker;
   - se reutilizan nombre, apellido y RUT maestros;
   - si posee email, el correo de acceso debe coincidir;
   - `users.id_worker` queda vinculado al worker.
4. Si el worker ya posee cuenta:
   - no puede crearse una identidad duplicada.
5. Sólo si la búsqueda no encuentra workers se habilita continuar creando una
   cuenta sin vínculo previo.

No se crean ni duplican registros de `workers` desde Gestión de Usuarios.

### Corrección Visualizar / Editar
`usuarios.js` esperaba elementos de fotografía que no existían en
`gestion-usuarios.php`:
- `viewUserPhoto`
- `viewUserPhotoPlaceholder`
- `userPhotoEditor`
- `editUserPhoto`
- `editUserPhotoPlaceholder`
- `userPhotoFile`
- `userPhotoUploadBtn`
- `userPhotoRemoveBtn`

Esto provocaba `Cannot read properties of null (reading 'classList')`.

Los elementos fueron restaurados y las funciones de fotografía también fueron
endurecidas contra referencias nulas.

### Recuperación / activación de acceso
Se revisó el flujo:
- la acción sólo se muestra con `users.access`;
- el endpoint vuelve a comprobar capability y alcance del usuario;
- genera token mediante `passwordsCreateToken()`;
- determina activación o reset según `credential_status`;
- en producción envía mediante `passwordsSendLinkEmail()`;
- registra auditoría;
- respeta el throttling de enlaces.

### Estilo
Los modales de Visualizar, Nuevo Usuario y Editar reutilizan el lenguaje visual
del onboarding:
- tarjetas con borde/acento;
- numeración por etapas;
- azul/cian/violeta;
- mejor distribución horizontal;
- responsive para escritorio, tablet y teléfono;
- soporte dark theme y reduced motion.

### QA reset
El reset de usuarios nivel Usuario fue corregido para ser no destructivo:
- conserva `users.name`, `lastname`, `rut`, `language`;
- conserva `workers.phone` y `workers.position`;
- sólo reinicia estado de onboarding, salud, consentimiento, evaluación inicial,
  perfil extendido, asignaciones QA y mutualidad.
