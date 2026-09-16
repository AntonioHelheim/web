# Consultoría TI integrada al index — vs1.2

## Objetivo

Integrar al `index.html` una sección de Consultoría TI reutilizando el contenido vigente de `01_CTI_ISDW_IS.html`, sin incorporar navbar/footer propios ni reintroducir servicios retirados.

## Ubicación

La sección se carga como componente entre:

1. `home/services.html`
2. `home/consultoria-ti.html`
3. `home/contact.html`

El contrato de navegación sigue siendo:

`index.html#consultoria-ti`

## Componentes nuevos

- `components/home/consultoria-ti.html`
- `components/home/consultoria-ti.css`

## Contenido incorporado

- Presentación de Consultoría TI.
- Full-Stack Development.
- Stack Front-End, Back-End y bases de datos.
- Proyectos destacados (portafolio seleccionado):
  - Safety Control Tower — `https://www.safetycontroltower.cl`
  - Kudo Chile — `https://www.kudochile.cl`
  - Tecaivot — `https://www.tecaivot.cl`
  - Helheim Bifrost (videojuego en desarrollo) — `https://www.helheim.cl/Proyectos/Helheim-Bifrost/v1.04/index.php`
- Metodologías de desarrollo y gestión.
- Integración de soluciones TI en Hidrógeno Verde.
- CTA a WhatsApp y formulario de contacto.

## Contenido deliberadamente excluido

- Navbar y footer de la página interna original.
- Back-to-top propio.
- Bloque Drones DJI.
- Links legacy de Escáner Vehicular/Intranet/Correo del navbar antiguo.
- Número de WhatsApp anterior.

## Convenciones

Los estilos usan prefijo `cti-` para evitar colisiones con otros componentes. El elemento raíz de la sección usa `id="consultoria-ti"` y `scroll-margin-top` para navegación con navbar fijo.


## Hipervínculos CFT de Magallanes

Los desarrollos y reportes asociados al CFT de Magallanes pueden mantenerse como referencia textual de experiencia, pero sus hipervínculos quedan desactivados. La sección integrada al index no contiene ningún `href` hacia dominios `cftdemagallanes.cl`. La página legacy `section/01_CTI_ISDW_IS.html` fue normalizada con la misma regla.
