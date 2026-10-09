# E3-VS1 — Bienvenida unificada

Todos los perfiles presentan una cabecera común con:

- Eventos reportados.
- Mis evaluaciones.
- Datos de usuario.

La cabecera reutiliza `worker-module-grid--user-shortcuts`, por lo que mantiene
el comportamiento responsivo existente:
- tres tarjetas en escritorio;
- dos columnas en tablet;
- una columna en teléfono.

## RBAC

Eventos reportados respeta `events.view`; sin capability la tarjeta se muestra
deshabilitada.

Mis evaluaciones utiliza el repositorio personal del usuario autenticado.

Datos de usuario:
- Usuario: abre el editor unificado de perfil + salud.
- Otros perfiles: abre `api/usuarios/mi-perfil.php`.

Debajo de la cabecera se conservan los módulos y módulos de gestión que
corresponden a cada perfil según sus capabilities.
