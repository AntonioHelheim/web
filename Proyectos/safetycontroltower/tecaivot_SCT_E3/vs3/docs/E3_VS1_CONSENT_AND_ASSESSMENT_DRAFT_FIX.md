# E3-VS1 — Consentimiento resumido y borrador de evaluación

## Consentimiento

La interfaz utiliza una sola casilla de aceptación y una sola declaración
resumida. La versión es:

`SCT-UNIFIED-CONSENT-v2.1`

La declaración resume veracidad, categorías de datos, información sensible,
finalidades, acceso restringido, protección, conservación y derechos de
actualización/rectificación/eliminación cuando corresponda.

La redacción es funcional para SCT y debe ser revisada jurídicamente antes de
adoptarse como cláusula legal definitiva.

## Borrador de evaluación

Nueva tabla:

`onboarding_assessment_draft_answers`

Clave primaria:
`(id_attempt, id_question)`

El navegador guarda las selecciones 250 ms después de cada cambio. Al pulsar
`Volver a mis datos`, se ejecuta un guardado inmediato y sólo se navega si el
servidor confirma el borrador.

`evaluacion-obtener.php` devuelve `draft_answers`; el frontend marca las
alternativas correspondientes al reconstruir las 15 preguntas.

Al finalizar, las respuestas definitivas se escriben en
`onboarding_assessment_answers` y el borrador se elimina.
