# E3-VS1 — Consentimiento unificado, navbar y portal Usuario

## Consentimiento

La interfaz del onboarding utiliza una única casilla de aceptación.

La aceptación cubre:
- identidad y antecedentes laborales;
- información de emergencia;
- información sensible de salud;
- antecedentes ocupacionales;
- finalidades de gestión, seguridad, salud ocupacional, emergencias,
  evaluaciones, cumplimiento y trazabilidad.

La aplicación mantiene dos registros independientes porque cumplen funciones
de auditoría diferentes:
- `user_data_consent`: consentimiento general versionado;
- `worker_health_declaration`: declaración asociada al snapshot de salud.

Por tanto, se simplifica la UX sin eliminar trazabilidad.

La versión funcional es:
`SCT-UNIFIED-CONSENT-v2.0`.

La redacción debe someterse a revisión jurídica antes de ser considerada una
cláusula legal definitiva.

## Navbar autenticado

Todos los módulos autenticados consumen:
`partials/app-navbar.php`.

Las opciones se resuelven desde:
`SctNavigationRegistry::navbar()`.

El navbar cambia por rol/capacidades, no por implementaciones HTML diferentes.

Perfil Usuario:
- Inicio.
- Mis reportes.
- Mis evaluaciones.
- Mis datos.
- Módulos.

## Portal Usuario

`bienvenida.php` muestra sólo:
- reportes;
- evaluaciones;
- datos/salud;
- módulos.

Los módulos son:
- Health & Safety;
- Medio Ambiente;
- Arqueología;
- Paleontología;
- Estándar 16;
- Major Risk;
- Trabajo de Alto Riesgo;
- One Safety.
