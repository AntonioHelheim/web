# E3-VS1 — Corrección de alcance Gerente

## Matriz efectiva

| Actor | Puede ver | Puede administrar |
|---|---|---|
| SuperUsuario | Todos | Todos |
| Administrador Cliente | Empresa, niveles 2–7 | Empresa, niveles 2–7 |
| Gerente | Empresa, niveles 2–7 | Empresa, niveles 3–7 |
| Jefatura | Sólo asignados, niveles 2–7 | Sólo asignados, niveles 3–7 |
| Paramédico | Sólo asignados, niveles 2–7 | Ninguno |
| Contratista | Sólo asignados, niveles 2–7 | Ninguno |
| Usuario | Propio perfil | Autogestión propia |

El nivel 2 corresponde a Gerente. Por tanto sólo SuperUsuario y Administrador
Cliente administran información de Gerentes.

## Diferencia entre ver y administrar

`viewable_user_levels` determina si el actor puede obtener/listar el perfil.

`managed_user_levels` determina si puede realizar acciones administrativas
sobre ese perfil.

Esto impide que una capability amplia (`users.manage`, `users.edit`,
`permissions.manage`, etc.) salte la restricción jerárquica.

## Gerente sobre Gerente

Un Gerente puede visualizar a otro Gerente de su misma empresa, pero las
acciones administrativas quedan deshabilitadas y rechazadas también en
servidor.

El Gerente puede editar los campos básicos de su propio perfil por la regla de
autogestión; no puede administrarse como Gerente desde la jerarquía.
