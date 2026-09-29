# SCT vs3 — P79

Fecha: 23-09-2026

## Objetivo

Ajuste de UX/UI del constructor de actividades y normalización responsive/ortográfica de las superficies visuales autenticadas, conservando la arquitectura y contratos existentes de P78.

## Cambios principales

- **Paso 3 — Preguntas:** se corrigió la distribución de Tipo de respuesta, Dificultad y Peso / puntaje para evitar saltos, superposiciones y pérdida de espacio. El tooltip `?` de puntaje queda pequeño y alineado con el label, reutilizando `sctInfoTip()`.
- **Multimedia de preguntas:** el selector de archivos mantiene la carga existente, pero utiliza un botón más compacto y una columna multimedia más angosta en escritorio.
- **Vigencia de cursos:** en Inducción ya no se solicita vigencia en Datos básicos. La vigencia visible se define una sola vez en el paso final **Asignar**.
- Para mantener compatibilidad con las APIs actuales —que requieren un curso persistido antes de adjuntar material o preguntas y exigen fechas de vigencia al crear— el borrador usa fechas técnicas provisionales ocultas. Estas se sustituyen por la vigencia elegida por el usuario antes de publicar. No se modificó el contrato API ni la base de datos.
- **Responsive transversal:** se reforzaron las 26 superficies visuales existentes dentro de `/api` para tablet y mobile: contenedores sin overflow de página, controles legibles, inputs de 16 px en mobile, objetivos táctiles cercanos a 44 px, modales adaptados y tablas con scroll local cuando no es viable reflujo. Panel y Gestiones conservan sus CSS especializados y reciben sólo límites complementarios de lectura/interacción.
- **Ortografía y mayúsculas:** se revisaron textos visibles en español y fallbacks relacionados, aplicando acentos y capitalización tipo oración de forma consistente. Se corrigieron, entre otros, `período`, títulos de gestión y etiquetas visibles. No se renombraron variables, rutas, claves, valores de BD ni identificadores técnicos.
- Se agregaron las nuevas ayudas de Inducción en ES/EN/PT-BR/FR/ZH manteniendo paridad entre idiomas.
- Se actualizó cache-busting de las superficies afectadas para asegurar la carga de los estilos/JS de P79.

## Alcance por rol

- Administrador Completo, Administrador, Gerencia/Cliente y Jefatura: ajustes del constructor, responsive de gestiones y textos visibles.
- Usuario Demo/Trabajador: responsive y correcciones textuales en las vistas personales, Dashboard y Gestiones; no se alteró su lógica ni permisos.

## Sin cambios

- Sin migraciones ni cambios de esquema de base de datos.
- Sin cambios de rutas públicas ni estructura del proyecto.
- Sin cambios de sesión, jerarquía, capabilities, aislamiento multiempresa ni motor de tooltips/colapsables.
- Sin creación de APIs paralelas.

## Validaciones ejecutadas

- `php -l`: 719 archivos PHP correctos.
- `node --check`: 35 archivos JavaScript correctos.
- CSS: 17 archivos revisados, llaves y comentarios balanceados.
- Traducciones: 1.997 claves en cada idioma (ES, EN, PT, FR, ZH), sin diferencias de claves.
- Las 26 superficies visuales `/api` mantienen `meta viewport` y cargan `sct-main-sections.css` P79.
- No se incluyeron archivos SQL ni modificaciones en `db/`.

## Smoke test recomendado en localhost/desarrollo

1. Crear un curso nuevo y comprobar que **Datos básicos** ya no solicita vigencia.
2. En **Preguntas**, verificar escritorio/tablet/mobile: label `Peso / puntaje`, `?`, selector multimedia, alternativas y panel de preguntas sin superposición.
3. Llegar a **Asignar**, definir vigencia, asignar uno o más usuarios y publicar.
4. Reabrir el curso y confirmar que la vigencia persiste y se muestra correctamente.
5. Revisar las gestiones y vistas personales a 1024/900/768-700/599/390/360 px, incluido modo oscuro.
6. Confirmar que no existe overflow horizontal de página; las tablas densas deben usar únicamente scroll local.

> Nota: el entorno de construcción no dispone del driver `pdo_mysql` ni de un servidor MariaDB/MySQL activo, por lo que no se ejecutó un smoke test conectado a la base real.
