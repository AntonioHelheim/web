# Helheim.cl — vs1.2

## Baseline estable

Esta versión consolida la normalización frontend del sitio manteniendo HTML5, CSS3, Bootstrap y JavaScript como stack de la etapa actual.

## Cambios consolidados

- Se mantiene el carrusel desktop/móvil aprobado en la corrección anterior, sin alterar su HTML funcional.
- Se reorganizan los componentes por alcance:
  - `components/layout/`: navbar, footer y estilos compartidos.
  - `components/home/`: componentes exclusivos del index.
- Se reemplaza `js/includes.js` por `js/component-loader.js` con resolución automática de la raíz del sitio.
- El mismo navbar y footer del index se reutilizan en:
  - `index.html`
  - `section/01_CTI_ISDW_IS.html`
  - `section/10_DIN_IyC_DG.html`
- Los enlaces internos del navbar usan `data-site-href`, evitando dependencias de la profundidad de la página y permitiendo pruebas en subcarpetas de XAMPP.
- Se incorpora `data-nav-active`/`aria-current` para semántica de navegación sin modificar visualmente el componente aprobado.
- Los estilos de navbar/footer salen de `css/helheim.css` y quedan encapsulados en `components/layout/layout.css`.
- Se mantienen las mejoras previas de validación de formulario, seguridad de enlaces externos, SRI de Bootstrap y normalización de assets.
- No se incorporan archivos PHP ni lógica MySQL en esta etapa.

## Regla tecnológica de esta etapa

Frontend público: HTML5 + CSS3 + Bootstrap + JavaScript.

PHP 7.3+ se incorpora de forma acotada cuando una funcionalidad realmente requiere servidor. En vs1.2 el primer caso es el formulario de contacto. MySQL sigue reservado para autenticación, persistencia y módulos que lo requieran.

## 2026-09-15 — Reconstrucción de navegación global

- Se conserva el menú anterior completo dentro de `components/layout/navbar.html`, comentado para trazabilidad.
- Menú activo reducido a: Inicio, Consultoría TI, Diseño Industrial, Grabado Láser, Dibujo Técnico y Login.
- Consultoría TI, Diseño Industrial, Grabado Láser y Dibujo Técnico quedan preparados para navegar a futuras secciones del `index.html` mediante anclas estables.
- Escáner Vehicular, Drones, Intranet, Correo y accesos sociales del navbar dejan de renderizarse y permanecen comentados.
- Login incorpora únicamente la interfaz Bootstrap frontend: usuario/correo + código de acceso. No realiza autenticación ni llamadas al servidor.
- Se mantiene la arquitectura frontend HTML5 + CSS3 + Bootstrap + JavaScript.
- En este punto de la evolución, PHP/MySQL seguían fuera del alcance del Login; posteriormente vs1.2 incorpora PHP 7.3+ sólo para el formulario de contacto. MySQL continúa fuera de alcance.
- Se incrementa el identificador interno de caché a `1.2.0-20260915.3` sin cambiar la denominación oficial `vs1.2`.

## 2026-09-15 — Sincronización Navbar ↔ Nuestros Servicios

- `Nuestros Servicios` queda reducido a cuatro servicios activos, en relación 1:1 con el menú principal excluyendo `Inicio` y `Login`.
- Consultoría TI apunta a `index.html#consultoria-ti` tanto desde navbar como desde su tarjeta.
- Diseño Industrial apunta a `index.html#diseno-industrial` tanto desde navbar como desde su tarjeta.
- Grabado Láser apunta a `index.html#grabado-laser` tanto desde navbar como desde su tarjeta.
- Dibujo Técnico apunta a `index.html#dibujo-tecnico` tanto desde navbar como desde su tarjeta.
- Escáner Vehicular y Drones dejan de renderizarse en `Nuestros Servicios` y permanecen comentados para trazabilidad.
- La grilla de servicios activos se equilibra en 2×2 desde tablet/escritorio y 1 columna en móvil, manteniendo el diseño y flip 3D existentes.
- Se documenta el contrato de sincronización en `docs/SERVICES_v1.2.md`.

## Actualización contacto — 2026-09-15

- WhatsApp actualizado a +56 9 3544 4514 en todos los enlaces del paquete.
- Formulario del home migrado desde Formspree a `php/contact.php`.
- Destinatario: `juanantonioconchaloyola@gmail.com`.
- Asunto fijo: `contacto desde la web de helheim.cl`.
- Validación cliente/servidor, honeypot y envío AJAX con fallback HTML.
- `component-loader.js` ahora resuelve también `data-site-action` para formularios reutilizables.

## Contact transport modes
- Localhost now defaults to `log` transport and records executions in `storage/logs/contact.log` instead of failing because no local MTA is installed.
- Web environments may use `mail()` or SMTP through PHPMailer.
- Added `composer.json` for optional/recommended SMTP transport.
- Added `storage/.htaccess` to prevent HTTP access to development logs.


## Ajuste index / navegación — 2026-09-15
- Se agrega `Contacto` al navbar activo, apuntando a `index.html#formcontacto`.
- Se retiran Escáner Vehicular y Drones del carrusel desktop y móvil.
- Se eliminan sus cuatro banners específicos del paquete.
- Se elimina contenido residual de Escáner Vehicular y Drones en `Nuestros Servicios`.
- Se elimina la referencia a drones en la tarjeta activa de Consultoría TI del index.
- El loader reaplica el hash una vez cargados los componentes para navegación confiable desde páginas internas.
- Metadatos del index actualizados a los cuatro servicios activos.

## 2026-09-15 — Consultoría TI integrada al index

- Se agrega `components/home/consultoria-ti.html` bajo Nuestros Servicios y antes de Contacto.
- Se agrega `components/home/consultoria-ti.css` con estilos encapsulados `cti-*`.
- Se mantiene el contrato `index.html#consultoria-ti` usado por navbar y tarjeta de servicio.
- Se incorpora contenido Full-Stack, stack tecnológico, proyectos, metodologías e Hidrógeno Verde desde la página de Consultoría TI.
- Se excluye Drones DJI del nuevo componente para mantener coherencia con la oferta vigente del index.
- CTA actualizado al WhatsApp +56 9 3544 4514 y al formulario `#formcontacto`.
- Cache-busting actualizado a `1.2.0-20260915.7`.


## 2026-09-15 — Ajuste de proyectos destacados Consultoría TI

- El portafolio destacado del componente `consultoria-ti` queda limitado a Safety Control Tower, Kudo Chile, Tecaivot y Helheim Bifrost v1.04.
- Se eliminan del bloque `Proyectos destacados` los enlaces anteriores a sistemas, reportes y desarrollos del CFT de Magallanes.
- Las referencias de experiencia con CFT de Magallanes se conservan únicamente como texto no navegable dentro de `Experiencia institucional`.
- Se desactivan también los hipervínculos CFT existentes en la página legacy `section/01_CTI_ISDW_IS.html`.
- Cache-busting actualizado a `1.2.0-20260915.7`.

## 2026-09-15 — Diseño Industrial integrado al index

- Se agrega `components/home/diseno-industrial.html` después de Consultoría TI y antes de Contacto.
- Se agrega `components/home/diseno-industrial.css` con estilos encapsulados `din-*`.
- Se materializa el destino `#diseno-industrial` usado por navbar y tarjeta de servicio.
- Se incorporan del contenido legacy: Nuestro Origen, Áreas de Diseño, Galería y Proceso de Trabajo.
- La galería utiliza lightbox Bootstrap mediante delegación en `js/helheim.js`, compatible con componentes cargados dinámicamente.
- Se elimina del nuevo componente la navegación legacy y referencias a Escáner Vehicular, Intranet, Correo y WhatsApp antiguo.
- Se evita cargar AOS/Popper adicionales y el GIF `coffe 1.gif` de ~22 MB.
- Se generan siete derivados WebP optimizados para el index (~1 MB total), conservando los originales para la página legacy.
- CTA actualizado a WhatsApp +56 9 3544 4514 y `#formcontacto`.
- Cache-busting actualizado a `1.2.0-20260915.8`.

## 2026-09-15 — Grabado Láser y Dibujo Técnico integrados al index

- Se agrega `components/home/grabado-laser.html` + `grabado-laser.css` después de Diseño Industrial.
- La sección describe el flujo de creación punta a punta utilizando Creality CR-Laser Falcon 10W: diseño, preparación, prueba de material, grabado/corte, terminación y entrega.
- Se incluyen categorías de productos realizables y materiales compatibles sujetos a validación.
- Se agrega `components/home/dibujo-tecnico.html` + `dibujo-tecnico.css` después de Grabado Láser.
- Dibujo Técnico incorpora AutoCAD, Blender, entregables 2D/3D y salida física con Canon PIXMA iX6810 y Brother HL-1202.
- Se explicita que la Canon cubre A3 y hasta 33 × 48 cm; la Brother HL-1202 se utiliza para monocromo hasta A4/Legal.
- `Nuestros Servicios` se actualiza para reflejar el contenido real de ambas secciones.
- El orden del index queda: Servicios → Consultoría TI → Diseño Industrial → Grabado Láser → Dibujo Técnico → Contacto.
- Cache-busting actualizado a `1.2.0-20260915.9`.
