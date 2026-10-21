# E3-VS1 — Alcance de gestión de usuarios

## Alcances

| Perfil | Alcance |
|---|---|
| SuperUsuario | Todos los usuarios del sistema |
| Gerente | Toda su empresa, excepto SuperUsuario |
| Administrador Cliente | Toda su empresa, excepto SuperUsuario |
| Jefatura | Sólo usuarios asignados; lectura y administración |
| Paramédico | Sólo usuarios asignados; lectura |
| Contratista | Sólo usuarios asignados; lectura |
| Usuario | Perfil propio |

## Relación de asignación

Tabla:
`user_supervision_assignments`

Campos principales:
- `id_company`
- `supervisor_user_id`
- `target_user_id`
- `state`
- `assigned_by`

Los responsables válidos son usuarios activos de la misma empresa con rol
Jefatura, Paramédico o Contratista.

Gerente, Administrador Cliente y SuperUsuario administran estas relaciones
desde Gestión de Usuarios.

## Enforcement

El alcance se aplica en servidor en:
- listado;
- detalle;
- actualización;
- estado;
- proceso de acceso;
- fotografía.

No se confía en filtros del navegador para autorización.
