# Nuestros Servicios — relación con navbar (vs1.2)

La sección `components/home/services.html` mantiene una relación 1:1 con los servicios activos del menú principal.

Se excluyen de esta relación `Inicio`, `Contacto` y `Login`, porque no representan un servicio.

| Clave | Navbar | Tarjeta "Nuestros Servicios" | Destino común |
|---|---|---|---|
| `consultoria` | Consultoría TI | Consultoría TI | `index.html#consultoria-ti` |
| `diseno` | Diseño Industrial | Diseño Industrial | `index.html#diseno-industrial` |
| `grabado-laser` | Grabado Láser | Grabado Láser | `index.html#grabado-laser` |
| `dibujo-tecnico` | Dibujo Técnico | Dibujo Técnico | `index.html#dibujo-tecnico` |

Escáner Vehicular y Drones fueron retirados del componente del index y de sus banners de carrusel.

## Regla de mantenimiento

Cuando se agregue, retire o renombre un servicio activo, se deben actualizar en conjunto:

1. `components/layout/navbar.html`
2. `components/home/services.html`
3. La sección destino correspondiente dentro de `index.html`/sus componentes.

En la baseline actual los cuatro destinos ya están materializados: `#consultoria-ti`, `#diseno-industrial`, `#grabado-laser` y `#dibujo-tecnico`.

El atributo `data-site-href` debe coincidir exactamente entre navbar y tarjeta de servicio para el mismo `data-nav-key` / `data-service-key`.
