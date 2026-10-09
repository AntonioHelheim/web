# E3-VS1 — Confirmación del resultado de evaluación inicial

Después de responder las 15 preguntas, el Usuario presiona `Aceptar y guardar`.

La evaluación se persiste primero. Con la respuesta confirmada por el servidor,
el frontend muestra un modal bloqueante con:

- porcentaje y cantidad de respuestas correctas;
- porcentaje y cantidad de respuestas incorrectas;
- resultado final;
- confirmación de que el proceso inicial fue completado.

El resultado final utiliza verde para 60% o más y rojo para menos de 60%.

No existe redirección automática. El usuario debe presionar `Aceptar y continuar`;
recién entonces el flujo continúa a `bienvenida.php`.

El modal está traducido en `es`, `en`, `pt`, `fr` y `zh`.
