# E3-VS1 — Perfil inicial extendido

## Objetivo

Ampliar el Paso 1 del onboarding sin duplicar responsabilidades de los módulos existentes.

## Datos

- Grupo/nivel de usuario: se obtiene desde `users_role` + `users_role_group`; sólo lectura.
- Proyecto: se obtiene desde `worker_projects` + `projects`; el usuario confirma uno de los proyectos permitidos.
- Contratista: se registra Sí/No y nombre cuando corresponde.
- Afiliación de salud: FONASA, ISAPRE, Otro, Prefiere no indicar.
- Experiencia: años en el cargo actual.
- Contacto de emergencia: nombre, teléfono y relación.
- Relación: Pareja, Familia directa, Amigo(a), Otro.

## Persistencia

Los datos confirmados quedan en `user_profile_details`, vinculados a `users`, `workers`,
`company` y `projects`.

`worker_health_profile` continúa siendo la declaración sanitaria histórica. El formulario
de salud puede tomar los datos generales confirmados como prellenado, y al guardar una
declaración sanitaria vuelve a sincronizar afiliación de salud y contacto principal en
`user_profile_details`.

## Internacionalización

Toda la interfaz nueva mantiene los cinco idiomas oficiales:
`es`, `en`, `pt`, `fr`, `zh`.

## Migración

Única ubicación:
`migracion sql/05_2026-10-08_e3_vs1_profile_completion_extended.sql`.

## Usuarios existentes

Aunque un usuario ya hubiera completado el onboarding anterior, el Paso 1 vuelve a quedar
pendiente mientras no exista un registro completo en `user_profile_details`.

Una evaluación ya completada se conserva. Por ello, un usuario existente normalmente hará:

`Login -> completar nuevos antecedentes -> bienvenida.php`

y no tendrá que repetir las 15 preguntas salvo que su evaluación también esté pendiente.
