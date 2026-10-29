# E3-VS1 FUNCIONAL — Mutualidad obligatoria

La afiliación solicitada durante el onboarding se normaliza como mutualidad/organismo de seguridad laboral.

Opciones canónicas:
- `achs` — ACHS / Asociación Chilena de Seguridad
- `isl` — Instituto de Seguridad Laboral
- `ist` — Instituto de Seguridad del Trabajo
- `mutual` — Mutual de Seguridad

La fuente única de la selección vigente del usuario es `users.mutual_code`.

El catálogo de nombres y logos está centralizado en:

`app/Config/MutualityOptions.php`

Lo consumen:
- `partials/app-navbar.php`
- `api/usuarios/mutualidad.php`
- `onboarding.php`
- `app/Onboarding/OnboardingService.php`
- `app/Health/HealthRepository.php`

Esto evita divergencias entre bienvenida, onboarding y salud.

Nota de nomenclatura:
La implementación histórica de bienvenida utiliza `ACHS`. Por consistencia técnica y visual
se mantiene `ACHS` como código/nombre canónico, aunque en requerimientos informales pueda
aparecer abreviado como “ACS”.
