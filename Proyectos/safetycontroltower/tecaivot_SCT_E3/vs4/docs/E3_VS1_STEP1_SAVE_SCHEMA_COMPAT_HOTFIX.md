# E3-VS1 — Hotfix de guardado del Paso 1

El repositorio de onboarding ahora soporta simultáneamente:

1. Esquema E3-VS1 normalizado:
   `users.mutual_code` y sin `user_profile_details.health_system`.

2. Esquema intermedio:
   `users.mutual_code` y `user_profile_details.health_system` todavía presente
   como columna obligatoria.

La detección se realiza mediante `information_schema.COLUMNS`. Si la columna
histórica existe, se completa con el mismo código de mutualidad canónica.

No se cambia la fuente de verdad ni se requiere una migración nueva para este
hotfix.

La transacción de `saveProfile()` añade etiquetas de etapa al error del
servidor para facilitar QA sin mostrar detalles SQL en la interfaz.
