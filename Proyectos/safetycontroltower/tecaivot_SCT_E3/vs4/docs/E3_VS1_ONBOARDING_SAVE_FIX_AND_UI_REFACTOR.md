# E3-VS1 — Corrección de guardado y refactor del onboarding

## Causa del error de guardado

Al integrar el Formulario de Salud dentro del Paso 1, `healthSaveProfile()` pasó
a ejecutarse dentro de la misma transacción del onboarding.

La función llamaba a `healthEncryptionKey()` de forma obligatoria. Si el ambiente
local no tenía `SCT_HEALTH_ENCRYPTION_KEY`, se lanzaba una excepción antes de
insertar `worker_health_profile`. El endpoint ocultaba la excepción interna y
mostraba únicamente:

`No fue posible guardar el registro inicial.`

## Corrección

Producción:
- Sigue siendo obligatorio configurar `SCT_HEALTH_ENCRYPTION_KEY`.
- No existe fallback a texto plano.

Local / development:
- Se genera una clave aleatoria de 256 bits.
- Se guarda en `var/private/health.key`.
- Se aplican permisos restrictivos cuando el sistema operativo lo permite.
- `var/private/.gitignore` evita incorporarla al repositorio.
- La clave no forma parte del ZIP de entrega.

Esto permite probar el flujo local sin debilitar la política de producción.

## Refactor visual del Paso 1

El formulario se organiza ahora en secciones funcionales:

1. Cuenta y asignación.
2. Datos personales y laborales.
3. Relación contractual y mutualidad.
4. Contactos de emergencia.
5. Salud y antecedentes ocupacionales.
6. Declaraciones y consentimiento.

Los dos contactos de emergencia mantienen la misma distribución visual:
Nombre, Teléfono, Relación.

Las relaciones se identifican expresamente como:
- Relación con el contacto principal.
- Relación con el segundo contacto.

El segundo contacto se identifica como opcional; si se informa cualquiera de
sus datos, nombre, teléfono y relación pasan a ser requeridos como conjunto.

## Responsive

- Escritorio: distribución en 2/3 columnas según el tipo de dato.
- Tablet: campos principales en 2 columnas.
- Teléfono: campos apilados, acciones a ancho completo.
