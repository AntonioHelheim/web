# Safety Control Tower — E3-VS1
## Barrido de acceso por perfiles y superficie funcional

Fecha de análisis: 07-10-2026  
Baseline: código E2-VS4 refactorizado a E3-VS1.

### 1. Perfiles finales

| Nivel | Perfil | Alcance | Resultado efectivo E3-VS1 |
|---:|---|---|---|
| 1 | SuperUsuario | Global | Todos los permisos del catálogo; su matriz no puede degradarse desde UI |
| 2 | Gerente | Empresa propia | Uso total de módulos de su empresa; edita empresa; administra matrices de perfiles inferiores; crea y designa Administrador Cliente y niveles inferiores |
| 3 | Administrador Cliente | Empresa propia | Uso total operativo; crea/edita/valida usuarios de niveles Usuario/Paramédico/Contratista/Trabajador; no administra matriz de permisos ni designa otro Administrador Cliente |
| 4 | Usuario | Empresa propia | Visualización: empresa, trabajadores, proyectos, centros, eventos, auditorías, autoevaluaciones, formularios, protocolos, dashboard y programas; puede cargar/enviar formularios/documentos; edita datos propios; realiza inducción |
| 5 | Paramédico | Propio | Edita datos propios y realiza inducción |
| 6 | Contratista | Propio / documental | Acceso documental actual mediante formularios dinámicos: visualizar, cargar/enviar archivos. No tiene acceso general a trabajadores/proyectos/eventos. El modelo actual aún no distingue documentalmente personal contratista/subcontratista |
| 7 | Trabajador | Propio | Edita datos propios y realiza inducción; sin visualización transversal de Usuario |

### 2. Jerarquía de creación/asignación de perfiles

- SuperUsuario: niveles 1–7.
- Gerente: niveles 3–7.
- Administrador Cliente: niveles 4–7.
- Usuario, Paramédico, Contratista y Trabajador: no administran perfiles.

El perfil histórico `jefatura` se migra a `usuario` bajo principio de mínimo privilegio y requiere reclasificación explícita; no se promueve automáticamente a Administrador Cliente.

### 3. Matriz funcional resumida

| Función | SuperUsuario | Gerente | Admin Cliente | Usuario | Paramédico | Contratista | Trabajador |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Todas las empresas | ✓ | — | — | — | — | — | — |
| Editar empresa propia | ✓ | ✓ | ✓ | — | — | — | — |
| Matriz de permisos | ✓ | ✓ (perfiles inferiores) | — | — | — | — | — |
| Gestión de usuarios | ✓ | ✓ | ✓ | — | — | — | — |
| Designar Administrador Cliente | ✓ | ✓ | — | — | — | — | — |
| Dashboard | ✓ | ✓ | ✓ | Vista | — | — | — |
| Trabajadores | ✓ | ✓ | ✓ | Vista | — | —* | — |
| Proyectos / Centros | ✓ | ✓ | ✓ | Vista | — | — | — |
| Eventos | ✓ | ✓ | ✓ | Vista | — | — | — |
| Inducción administración | ✓ | ✓ | ✓ | — | — | — | — |
| Inducción ejecutar | ✓ | ✓ | ✓ | ✓ | ✓ | —** | ✓ |
| Auditorías | ✓ | ✓ | ✓ | Vista | — | — | — |
| Autoevaluaciones | ✓ | ✓ | ✓ | Vista | — | — | — |
| Formularios dinámicos | ✓ | ✓ | ✓ | Vista + envío | — | Vista + envío | — |
| Protocolos | ✓ | ✓ | ✓ | Vista | — | — | — |
| Programas | ✓ | ✓ | ✓ | Vista | — | — | — |
| Historial de cambios | ✓ | ✓ | ✓ | — | — | — | — |
| Perfil propio | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |

* Requisito Sponsor: Contratista debe ver sólo documentos de personal contratista/subcontratista. La VS4 no contiene una entidad/clasificación documental que permita garantizar ese filtro. E3-VS1 restringe al perfil Contratista al motor documental/formularios, pero falta modelar la relación documental de contratista/subcontratista antes de considerar este requisito cerrado.

** El requerimiento textual del Sponsor para Contratista no menciona inducción; por mínimo privilegio no se asignó `induction.execute`. Puede habilitarse si el Sponsor confirma que también debe realizar inducción.

### 4. Autorización técnica

- Después de aplicar la migración E3-VS1, `permissions` + `role_permissions` son la fuente autoritativa.
- El permiso `system.permissions.enabled` funciona como switch transaccional: su mera existencia en el catálogo no activa RBAC; la migración lo asigna a los perfiles finales una vez reconstruida la matriz.
- Antes de aplicar la migración se usa exclusivamente el fallback seguro de `SctRolePolicy`, para permitir despliegue coordinado sin leer una matriz antigua como si fuese E3-VS1.
- Los módulos que todavía utilizaban listas de roles históricas fueron cambiados a capacidades (`*.view`, `*.manage`, etc.).

### 5. Superficie de capacidades observada en código

- `audits.assign`: 3 archivo(s) — api/auditorias/asignaciones-crear.php, api/auditorias/asignaciones-listar.php, api/auditorias/auditores-disponibles.php
- `audits.create`: 1 archivo(s) — api/auditorias/auditorias-crear.php
- `audits.edit`: 1 archivo(s) — api/auditorias/auditorias-editar.php
- `audits.execute`: 3 archivo(s) — api/auditorias/mis-asignaciones.php, api/auditorias/rendir-detalle.php, api/auditorias/rendir-responder.php
- `audits.manage`: 2 archivo(s) — api/auditorias/common.php, api/usuarios/gestiones.php
- `audits.questions`: 3 archivo(s) — api/auditorias/auditoria-preguntas-agregar.php, api/auditorias/auditoria-preguntas-quitar.php, api/auditorias/preguntas-listar.php
- `audits.state`: 1 archivo(s) — api/auditorias/auditorias-cambiar-estado.php
- `audits.view`: 5 archivo(s) — api/auditorias/auditorias-detalle.php, api/auditorias/auditorias-listar.php, api/auditorias/common.php, api/auditorias/gestion-auditorias.php, api/auditorias/mis-auditorias.php
- `centers.create`: 1 archivo(s) — api/centros/crear.php
- `centers.edit`: 1 archivo(s) — api/centros/editar.php
- `centers.manage`: 3 archivo(s) — api/centros/common.php, api/centros/gestion-centros.php, api/usuarios/gestiones.php
- `centers.state`: 1 archivo(s) — api/centros/cambiar-estado.php
- `centers.view`: 5 archivo(s) — api/centros/common.php, api/centros/detalle.php, api/centros/empresas-disponibles.php, api/centros/gestion-centros.php, api/centros/listar.php
- `change_history.global`: 1 archivo(s) — api/historial/common.php
- `change_history.view`: 2 archivo(s) — api/historial/common.php, api/usuarios/gestiones.php
- `companies.create_all`: 2 archivo(s) — api/empresas/crear.php, api/empresas/gestion-empresas.php
- `companies.edit_all`: 1 archivo(s) — api/empresas/common.php
- `companies.edit_own`: 1 archivo(s) — api/empresas/common.php
- `companies.manage_all`: 1 archivo(s) — api/empresas/common.php
- `companies.state_all`: 4 archivo(s) — api/empresas/baja.php, api/empresas/gestion-empresas.php, api/empresas/listar.php, api/empresas/reactivar.php
- `companies.view_all`: 20 archivo(s) — api/auditorias/common.php, api/autoevaluaciones/common.php, api/centros/common.php, api/centros/empresas-disponibles.php, api/dashboard/common.php, api/empresas/common.php, api/empresas/gestion-empresas.php, api/empresas/listar.php…
- `companies.view_own`: 3 archivo(s) — api/empresas/common.php, api/empresas/gestion-empresas.php, api/empresas/listar.php
- `dashboard.global`: 1 archivo(s) — api/dashboard/common.php
- `dashboard.view`: 6 archivo(s) — api/dashboard/centros-disponibles.php, api/dashboard/dashboard.php, api/dashboard/empresas-disponibles.php, api/dashboard/indicadores-consolidados.php, api/dashboard/indicadores.php, api/dashboard/proyectos-disponibles.php
- `dynamic_forms.create`: 1 archivo(s) — api/formularios/formularios-crear.php
- `dynamic_forms.edit`: 1 archivo(s) — api/formularios/formularios-editar.php
- `dynamic_forms.fields`: 3 archivo(s) — api/formularios/campos-agregar.php, api/formularios/campos-editar.php, api/formularios/campos-eliminar.php
- `dynamic_forms.manage`: 4 archivo(s) — api/formularios/archivo-descargar.php, api/formularios/common.php, api/formularios/envio-detalle.php, api/usuarios/gestiones.php
- `dynamic_forms.state`: 1 archivo(s) — api/formularios/formularios-cambiar-estado.php
- `dynamic_forms.submissions`: 1 archivo(s) — api/formularios/envios-listar.php
- `dynamic_forms.submit`: 1 archivo(s) — api/formularios/formulario-enviar.php
- `dynamic_forms.view`: 5 archivo(s) — api/formularios/formulario-publico-detalle.php, api/formularios/formularios-listar.php, api/formularios/gestion-formularios.php, api/formularios/mis-formularios-listar.php, api/formularios/mis-formularios.php
- `events.create`: 2 archivo(s) — api/eventos/common.php, api/eventos/eventos-crear.php
- `events.edit`: 1 archivo(s) — api/eventos/eventos-editar.php
- `events.evidence_manage`: 1 archivo(s) — api/eventos/evidencia-eliminar.php
- `events.evidence_upload`: 1 archivo(s) — api/eventos/evidencia-subir.php
- `events.manage`: 2 archivo(s) — api/eventos/common.php, api/eventos/gestion-eventos.php
- `events.state`: 1 archivo(s) — api/eventos/eventos-cambiar-estado.php
- `events.tracking`: 1 archivo(s) — api/eventos/tracking-crear.php
- `events.view`: 9 archivo(s) — api/eventos/centros-disponibles.php, api/eventos/empresas-disponibles.php, api/eventos/eventos-detalle.php, api/eventos/eventos-listar.php, api/eventos/evidencia-descargar.php, api/eventos/gestion-eventos.php, api/eventos/proyectos-disponibles.php, api/eventos/tipos-listar.php…
- `health_profile.view_emergency`: 1 archivo(s) — api/salud/emergencia-obtener.php
- `induction.assign`: 3 archivo(s) — api/induccion/asignaciones-crear.php, api/induccion/asignaciones-listar.php, api/induccion/usuarios-disponibles.php
- `induction.create`: 1 archivo(s) — api/induccion/cursos-crear.php
- `induction.edit`: 1 archivo(s) — api/induccion/cursos-editar.php
- `induction.execute`: 3 archivo(s) — api/induccion/mis-asignaciones.php, api/induccion/rendir-detalle.php, api/induccion/rendir-responder.php
- `induction.manage`: 4 archivo(s) — api/induccion/certificado-descargar.php, api/induccion/common.php, api/induccion/gestion-induccion.php, api/usuarios/gestiones.php
- `induction.materials`: 2 archivo(s) — api/induccion/materiales-crear.php, api/induccion/materiales-eliminar.php
- `induction.questions`: 2 archivo(s) — api/induccion/curso-preguntas-agregar.php, api/induccion/curso-preguntas-quitar.php
- `induction.state`: 1 archivo(s) — api/induccion/cursos-cambiar-estado.php
- `induction.view`: 8 archivo(s) — api/induccion/common.php, api/induccion/cursos-detalle.php, api/induccion/cursos-listar.php, api/induccion/empresas-disponibles.php, api/induccion/gestion-induccion.php, api/induccion/materiales-listar.php, api/induccion/mis-certificados.php, api/induccion/mis-induccion.php
- `permissions.manage`: 2 archivo(s) — api/permisos/common.php, api/usuarios/gestiones.php
- `programs.create`: 3 archivo(s) — api/programas/catalogos-empresa.php, api/programas/gestion-programas.php, api/programas/programas-crear.php
- `programs.edit`: 3 archivo(s) — api/programas/catalogos-empresa.php, api/programas/gestion-programas.php, api/programas/programas-editar.php
- `programs.state`: 2 archivo(s) — api/programas/gestion-programas.php, api/programas/programas-cambiar-estado.php
- `programs.tracking`: 2 archivo(s) — api/programas/gestion-programas.php, api/programas/tracking-guardar.php
- `programs.view`: 6 archivo(s) — api/programas/catalogos-empresa.php, api/programas/common.php, api/programas/empresas-disponibles.php, api/programas/programa-detalle.php, api/programas/programas-listar.php, api/usuarios/gestiones.php
- `projects.assign_workers`: 3 archivo(s) — api/proyectos/trabajadores-asociar.php, api/proyectos/trabajadores-buscar.php, api/proyectos/trabajadores-desasociar.php
- `projects.create`: 1 archivo(s) — api/proyectos/crear.php
- `projects.edit`: 1 archivo(s) — api/proyectos/editar.php
- `projects.manage`: 3 archivo(s) — api/proyectos/common.php, api/proyectos/gestion-proyectos.php, api/usuarios/gestiones.php
- `projects.state`: 1 archivo(s) — api/proyectos/cambiar-estado.php
- `projects.view`: 6 archivo(s) — api/proyectos/common.php, api/proyectos/detalle.php, api/proyectos/empresas-disponibles.php, api/proyectos/gestion-proyectos.php, api/proyectos/listar.php, api/proyectos/trabajadores-listar.php
- `protocols.assign`: 4 archivo(s) — api/protocolos/asignacion-cambiar-estado.php, api/protocolos/asignaciones-crear.php, api/protocolos/asignaciones-listar.php, api/protocolos/catalogos-empresa.php
- `protocols.create`: 1 archivo(s) — api/protocolos/protocolos-crear.php
- `protocols.edit`: 1 archivo(s) — api/protocolos/protocolos-editar.php
- `protocols.execute`: 4 archivo(s) — api/protocolos/ejecucion-enviar.php, api/protocolos/ejecutar-protocolo.php, api/protocolos/mis-asignaciones.php, api/protocolos/mis-ejecuciones.php
- `protocols.forms`: 3 archivo(s) — api/protocolos/formularios-disponibles.php, api/protocolos/protocolo-formularios-agregar.php, api/protocolos/protocolo-formularios-quitar.php
- `protocols.manage`: 3 archivo(s) — api/protocolos/common.php, api/protocolos/ejecucion-detalle.php, api/usuarios/gestiones.php
- `protocols.review`: 2 archivo(s) — api/protocolos/ejecucion-revisar.php, api/protocolos/ejecuciones-listar.php
- `protocols.state`: 1 archivo(s) — api/protocolos/protocolos-cambiar-estado.php
- `protocols.tracking`: 3 archivo(s) — api/protocolos/tracking-cambiar-estado.php, api/protocolos/tracking-crear.php, api/protocolos/tracking-listar.php
- `protocols.view`: 4 archivo(s) — api/protocolos/gestion-protocolos.php, api/protocolos/mis-protocolos.php, api/protocolos/protocolos-detalle.php, api/protocolos/protocolos-listar.php
- `questions.manage`: 6 archivo(s) — api/auditorias/gestion-auditorias.php, api/auditorias/preguntas-crear.php, api/autoevaluaciones/gestion-autoevaluaciones.php, api/autoevaluaciones/preguntas-crear.php, api/induccion/common.php, api/induccion/gestion-induccion.php
- `self_assessments.assign`: 3 archivo(s) — api/autoevaluaciones/asignaciones-crear.php, api/autoevaluaciones/asignaciones-listar.php, api/autoevaluaciones/usuarios-disponibles.php
- `self_assessments.create`: 1 archivo(s) — api/autoevaluaciones/autoevaluaciones-crear.php
- `self_assessments.edit`: 1 archivo(s) — api/autoevaluaciones/autoevaluaciones-editar.php
- `self_assessments.execute`: 3 archivo(s) — api/autoevaluaciones/mis-asignaciones.php, api/autoevaluaciones/rendir-detalle.php, api/autoevaluaciones/rendir-responder.php
- `self_assessments.manage`: 2 archivo(s) — api/autoevaluaciones/common.php, api/usuarios/gestiones.php
- `self_assessments.questions`: 3 archivo(s) — api/autoevaluaciones/autoevaluacion-preguntas-agregar.php, api/autoevaluaciones/autoevaluacion-preguntas-quitar.php, api/autoevaluaciones/preguntas-listar.php
- `self_assessments.state`: 1 archivo(s) — api/autoevaluaciones/autoevaluaciones-cambiar-estado.php
- `self_assessments.view`: 4 archivo(s) — api/autoevaluaciones/autoevaluaciones-detalle.php, api/autoevaluaciones/autoevaluaciones-listar.php, api/autoevaluaciones/gestion-autoevaluaciones.php, api/autoevaluaciones/mis-autoevaluaciones.php
- `users.access`: 1 archivo(s) — api/usuarios/listar.php
- `users.edit`: 1 archivo(s) — api/usuarios/listar.php
- `users.photo`: 1 archivo(s) — api/usuarios/listar.php
- `users.state`: 1 archivo(s) — api/usuarios/listar.php
- `workers.create`: 1 archivo(s) — api/trabajadores/crear.php
- `workers.edit`: 1 archivo(s) — api/trabajadores/editar.php
- `workers.manage`: 3 archivo(s) — api/trabajadores/common.php, api/trabajadores/gestion-trabajadores.php, api/usuarios/gestiones.php
- `workers.photo`: 1 archivo(s) — api/trabajadores/subir-foto.php
- `workers.state`: 1 archivo(s) — api/trabajadores/cambiar-estado.php
- `workers.view`: 5 archivo(s) — api/trabajadores/common.php, api/trabajadores/detalle.php, api/trabajadores/empresas-disponibles.php, api/trabajadores/gestion-trabajadores.php, api/trabajadores/listar.php

### 6. Endpoints/páginas autenticados sin capability explícita

Estos archivos dependen de propiedad del recurso, autogestión u otra validación de dominio. Deben permanecer bajo revisión porque `requireLogin()` por sí solo no expresa una capability de módulo:

- `api/actividades/health-safety.php`
- `api/empresas/detalle.php`
- `api/empresas/editar.php`
- `api/empresas/eliminar-logo.php`
- `api/empresas/subir-logo.php`
- `api/formularios/formularios-detalle.php`
- `api/permisos/gestion-permisos.php`
- `api/usuarios/gestion-usuarios.php`
- `api/usuarios/idioma.php`
- `api/usuarios/mi-perfil.php`
- `api/usuarios/mutualidad.php`
- `api/usuarios/perfil-propio-guardar.php`

### 7. Hallazgos de acceso posteriores al refactor

1. Se eliminó el fallback visual de una cuenta sin rol hacia Trabajador. Una cuenta sin perfil reconocido no obtiene sesión operacional.
2. Se eliminaron listas de roles históricas en los helpers de Eventos, Trabajadores, Centros, Proyectos, Inducción y Auditorías; el control pasa a capabilities.
3. Gerente ahora puede entrar al módulo Empresa de su propia compañía y editar sus datos, sin obtener creación/baja de otras empresas.
4. Gerente puede administrar matrices únicamente de niveles inferiores; Administrador Cliente no posee `permissions.manage`.
5. SuperUsuario conserva obligatoriamente todos los permisos funcionales al guardar matrices.
6. El perfil Usuario no puede modificar datos operacionales ni administrar usuarios; sólo recibe permisos de lectura transversal, envío documental y ejecución de inducción.
7. Paramédico y Trabajador quedan deliberadamente fuera del hub Health & Safety salvo cursos/certificados y perfil propio.
8. Contratista sólo recibe capacidades de formularios/documentos; no recibe lectura general de trabajadores. Falta el modelo de datos para filtrar específicamente personal contratista/subcontratista, por lo que ese requisito queda identificado como gap funcional, no se simula con un permiso más amplio.

### 8. Deuda DAL/Repository detectada

La arquitectura canónica ya movió Auth y repositorios de Eventos, Protocolos, Formularios, Programas, Salud y Evaluaciones bajo `app/`. Los archivos `lib/repositorios/*` correspondientes son shims de compatibilidad. Sin embargo, el barrido aún detecta SQL directo en algunos endpoints legacy. Estos deben migrarse de forma incremental a los repositories de dominio sin alterar contratos JSON. No se considera seguro hacer una sustitución genérica de SQL sólo para eliminar coincidencias estáticas.
### 9. Validación final del corte E3-VS1

Barrido posterior a la refactorización:

- Sintaxis PHP: **OK** en todos los archivos propios del proyecto.
- Prueba de política de roles: **OK** (`tests/e3_vs1_role_policy_test.php`).
- SQL directo bajo `api/`: **0 llamadas**. Los endpoints delegan acceso a datos a repositories/capas de dominio.
- Implementación duplicada `lib/repositorios/auth.php`: **eliminada**.
- Referencias ejecutables a generaciones frontend VS3/VS4/VS5: **0**.
- `bienvenida.php` y `partials/app-navbar.php`: consumen el mismo `SctNavigationRegistry`.
- Gestión de permisos: **reactivada** y protegida por `permissions.manage`; Gerente sólo puede modificar matrices de perfiles inferiores y SuperUsuario mantiene matriz completa.
- Activación del RBAC autoritativo: se realiza sólo cuando `schema_migrations` confirma `2026-10-07-e3-vs1`, evitando tratar un seed parcial como migración completa.
- El login termina en 403 si la identidad autenticada no posee un perfil SCT activo reconocido.
- Nuevas cuentas Demo derivan su `id_company` desde `AdminEmpresaDemo`, sin asumir un ID fijo de empresa.

### 10. Gap funcional que permanece deliberadamente abierto

El perfil **Contratista** está restringido actualmente al motor documental/formularios (`dynamic_forms.view`, `dynamic_forms.submit`, `dynamic_forms.files`). El requisito de Sponsor exige que pueda visualizar **sólo documentos correspondientes a personal contratista y subcontratista**. El modelo E2-VS4 no contiene una clasificación/relación inequívoca entre documento, empresa contratista/subcontratista y personal objetivo. Por seguridad, E3-VS1 no amplía `workers.view` ni simula ese filtro. Este punto requiere modelado de dominio específico antes de habilitar la visualización documental de terceros.
