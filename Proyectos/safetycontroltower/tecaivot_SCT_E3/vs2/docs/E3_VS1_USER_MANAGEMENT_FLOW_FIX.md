# E3-VS1 — Gestión de Usuarios

## Causa del error JavaScript

`usuarios.js` utilizaba controles de fotografía que no estaban renderizados en
`gestion-usuarios.php`. `setPhotoPreview()` y `updateViewPhoto()` intentaban
ejecutar `classList` sobre `null`.

El fix restaura esos elementos y añade protección nula adicional.

## Alta desde workers

La creación de una cuenta ya no comienza directamente en `users`.

Primero se consulta `workers` dentro de la empresa permitida. Cuando se
selecciona un trabajador disponible:

- `users.id_worker` recibe el `id_worker`;
- nombre, apellido y RUT provienen del registro maestro;
- el email del worker, si existe, debe coincidir con `users.id_users`;
- un worker ya vinculado a una cuenta no puede reutilizarse.

Si no existe un worker coincidente, el administrador puede continuar con una
cuenta independiente después de haber realizado la validación.

## Acceso / recuperación

`enviar-acceso.php` conserva las verificaciones de:
- `users.access`;
- alcance por perfil;
- estado activo;
- throttling;
- propósito activación/reset;
- generación de token;
- envío de correo en producción;
- auditoría.

## Responsive

Los modales utilizan el mismo patrón visual que onboarding y se adaptan a
desktop, tablet y teléfono.
