# Diseño Industrial integrado al index — vs1.2

## Objetivo

Integrar el contenido vigente de `section/10_DIN_IyC_DG.html` dentro del `index` con la misma arquitectura modular usada para Consultoría TI.

## Orden del index

1. Nosotros / presentación
2. Nuestros Servicios
3. Consultoría TI (`#consultoria-ti`)
4. Diseño Industrial (`#diseno-industrial`)
5. Contacto (`#formcontacto`)

## Archivos

- `components/home/diseno-industrial.html`
- `components/home/diseno-industrial.css`
- `images/diseno/index-optimized/*.webp`
- `js/helheim.js` — lightbox delegado para contenido dinámico

## Contenido incorporado

- Nuestro origen.
- Áreas de Diseño: Gráfico, Web e Industrial.
- Galería interactiva.
- Proceso de trabajo: Investigación, Concepto, Diseño, Producción y Entrega.
- CTA a WhatsApp +56 9 3544 4514 y al formulario del index.

## Contenido no trasladado

- Navbar y footer legacy.
- Escáner Vehicular, Intranet y Correo.
- WhatsApp antiguo.
- Dependencia AOS duplicada.
- Popper independiente (Bootstrap Bundle ya lo incluye).
- GIF de portada `coffe 1.gif` (~22 MB), para evitar una regresión de rendimiento en el home.

## Imágenes

Las imágenes originales se conservan para compatibilidad con la página legacy. Para el index se crearon derivados WebP en `images/diseno/index-optimized/`, reduciendo las siete imágenes utilizadas de aproximadamente 28 MB a ~1 MB total.

## Navegación

El contrato permanece:

- Navbar → `index.html#diseno-industrial`
- Tarjeta Nuestros Servicios → `index.html#diseno-industrial`
- Sección destino → `<section id="diseno-industrial">`
