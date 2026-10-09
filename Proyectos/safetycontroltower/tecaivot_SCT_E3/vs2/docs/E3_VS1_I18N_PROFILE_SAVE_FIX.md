# E3-VS1 — Fix guardado inicial después del cambio multidioma

La revisión del último fix detectó una ruta de escritura redundante sobre `users.language`:

1. `api/onboarding/idioma-guardar.php`;
2. `onboarding.php?lang=...`;
3. `api/onboarding/perfil-guardar.php`.

La UI permitía además conservar el formulario activo mientras esa sincronización se encontraba en curso. Esto podía producir colisión de escrituras/estado durante el guardado inicial y terminar en el catch genérico del endpoint.

La corrección deja una sola escritura de idioma durante el cambio, serializa el flujo y conserva el Paso 1 en `sessionStorage` antes de recargar.

`perfil-guardar.php` ahora usa directamente el idioma enviado por el formulario para resolver el texto versionado del consentimiento y no vuelve a modificar `users` después del commit.

No se modifica la estructura de BD. La migración 10 sigue siendo la última requerida.
