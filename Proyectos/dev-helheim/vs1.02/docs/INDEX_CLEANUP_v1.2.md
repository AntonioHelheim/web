# Helheim.cl vs1.2 — limpieza de index

## Navegación

El navbar activo incorpora **Contacto**, que apunta a `index.html#formcontacto`. Inicio, Contacto y Login son utilidades de navegación y no generan tarjetas dentro de `Nuestros Servicios`.

La relación 1:1 de servicios se mantiene para:

- Consultoría TI → `index.html#consultoria-ti`
- Diseño Industrial → `index.html#diseno-industrial`
- Grabado Láser → `index.html#grabado-laser`
- Dibujo Técnico → `index.html#dibujo-tecnico`

## Carrusel

Se retiraron del carrusel y del paquete del index los banners correspondientes a servicios que ya no forman parte de la oferta activa:

- Escáner Vehicular
- Drones

Los carruseles desktop y móvil conservan IDs, controles, autoplay e intervalo de 3000 ms.

## Contenido

Se eliminó del componente `Nuestros Servicios` el contenido comentado de Escáner Vehicular y Drones. También se retiró la referencia a drones dentro de la tarjeta activa de Consultoría TI.

Las páginas internas existentes no fueron modificadas en esta limpieza, porque el alcance solicitado corresponde al index.

## Navegación por hash

`component-loader.js` ahora reaplica el hash después de cargar los componentes. Esto permite que enlaces como `index.html#formcontacto` funcionen correctamente al llegar desde páginas internas.
