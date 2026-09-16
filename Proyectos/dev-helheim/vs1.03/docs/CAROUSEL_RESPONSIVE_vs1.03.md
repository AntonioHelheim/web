# Carrusel responsive — vs1.03

Objetivo: que las piezas del carrusel se muestren completas, sin estirar ni recortar el contenido gráfico.

## Perfiles considerados

- Teléfonos desde iPhone 12: 390×844 CSS px como referencia de vertical, y altura reducida en horizontal.
- Tablets desde iPad mini 4: 768×1024 CSS px como referencia; se cambia de composición según orientación.
- Escritorio 19–27 pulgadas: se prioriza la pieza 1350×650 y escala proporcionalmente con el ancho disponible.

## Criterio

Las imágenes usan `width:100%`, `height:auto` y `object-fit:contain`. Se eliminaron `h-100`, el `max-height` de 650 px y los `object-fit:cover` inline que podían deformar o recortar el arte.
