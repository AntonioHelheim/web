# Validación Helheim.cl — vs1.2

La validación de esta entrega contempla:

- existencia de todos los componentes y recursos locales activos;
- sintaxis JavaScript de `component-loader.js` y `helheim.js`;
- sintaxis PHP de `php/contact.php`;
- reutilización de navbar/footer en index y páginas internas;
- ausencia de IDs duplicados al componer el index;
- navbar activo con Inicio, cuatro servicios, Contacto y Login;
- relación 1:1 entre los cuatro servicios del navbar y `Nuestros Servicios`;
- `Contacto` apuntando a `index.html#formcontacto`;
- navegación por hash posterior a la carga asíncrona de componentes;
- carruseles desktop y móvil con 5 slides activos, un único `active`, autoplay de Bootstrap e intervalo de 3000 ms;
- ausencia de Escáner Vehicular y Drones en los componentes activos del index;
- eliminación de los banners desktop/mobile de Escáner Vehicular y Drones;
- existencia de todos los assets activos referenciados por los carruseles;
- protección `noopener noreferrer` en enlaces externos con `target="_blank"`;
- JSON válido del manifest;
- formulario de contacto conservado con endpoint PHP y WhatsApp +56 9 3544 4514.

Las páginas internas `section/` no se depuraron en esta tarea porque el alcance solicitado fue el index.

## Secciones de servicio materializadas en el index

- `#consultoria-ti`
- `#diseno-industrial`
- `#grabado-laser`
- `#dibujo-tecnico`

El orden validado es: Nuestros Servicios → Consultoría TI → Diseño Industrial → Grabado Láser → Dibujo Técnico → Contacto.
