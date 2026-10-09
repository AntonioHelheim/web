# E3-VS1 — Mejora visual de la evaluación inicial

La evaluación mantiene la lógica funcional existente y modifica únicamente la
presentación e interacción.

## Responsive

- Escritorio: opciones en dos columnas y tarjeta de evaluación ampliada.
- Tablet: dos columnas cuando existe ancho suficiente.
- Teléfono: una columna.
- Teléfono estrecho: reducción adicional de padding.

## Selección

La alternativa seleccionada conserva el radio nativo y además recibe resaltado
completo. Su pregunta asociada se destaca con la misma gama cromática.

Las preguntas rotan entre azul, cian, violeta, ámbar e índigo. No se utilizan
rojo o verde para no anticipar un significado de correcto/incorrecto.

## Animación

Cada selección ejecuta una microanimación sobre la alternativa y el número de
pregunta. `prefers-reduced-motion` desactiva estas animaciones.

Las respuestas restauradas desde borrador reciben el estado visual seleccionado
sin animación.
