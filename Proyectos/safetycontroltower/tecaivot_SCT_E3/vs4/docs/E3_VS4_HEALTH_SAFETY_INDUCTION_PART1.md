# E3 VS4 — Health & Safety / Inducción ENGIE — Parte 1

Fecha: 2026-10-28

## Objetivo

Reemplazar el contenido previo de `api/actividades/health-safety.php`, conservando el navbar compartido, por el primer bloque visual de la inducción de Seguridad y Salud Ocupacional de ENGIE Chile.

## Fuente

- Presentación: `PRESENTACION ENGIE_IRL_SCL(1).pptx`
- Parte: 1 de 5
- Láminas incorporadas: 25
- Total esperado informado para la inducción completa: 125

## Arquitectura implementada

- `api/actividades/health-safety.php`: vista y control de acceso.
- `app/Induction/HealthSafetyContent.php`: manifest de contenido y metadatos.
- `css/health-safety-induction.css`: presentación responsive y dark mode.
- `js/health-safety-induction.js`: navegación, índice, progreso, fullscreen, teclado y swipe.
- `assets/inductions/health-safety/part-01/`: 25 láminas web optimizadas.

## Principios respetados

- Navbar existente sin modificación.
- Autenticación y autorización existentes sin modificación de política.
- Separación de contenido, vista, estilos y comportamiento.
- Assets versionados mediante `SCT_ASSET_VERSION`.
- Sin cambios de base de datos ni nuevas migraciones para esta entrega.
- Preparado para sumar `part-02` a `part-05` sin rediseñar el visor.
- El PPTX fuente no se incorpora al despliegue web.

## UX incorporada

- Visor responsive 16:9.
- Índice visual de láminas.
- Botones anterior/siguiente.
- Barra de progreso.
- Persistencia local de última lámina visualizada.
- Acceso directo mediante `?slide=N`.
- Navegación con teclado.
- Swipe táctil en móviles/tablets.
- Pantalla completa.
- Compatibilidad con modo oscuro SCT.

## Identidad de release

`app/Config/version.php` actualizado a `E3-VS4`, fecha `2026-10-28`.

## Corrección multimedia — VS4 Parte 1

La presentación original contiene multimedia embebida que no queda incluida al renderizar cada lámina como JPG. Esta corrección integra los medios originales al visor web:

- 10 audios AAC/M4A vinculados a sus láminas originales.
- 3 videos MP4 vinculados y posicionados sobre el área de video original de la PPT.
- 6 animaciones GIF de las reglas que salvan vidas, preservadas como contenido animado.
- Los videos de mayor tamaño fueron optimizados a H.264/AAC para reproducción web y descarga progresiva.
- Al cambiar de lámina, cualquier audio o video activo se pausa automáticamente.
- Los navegadores modernos bloquean autoplay con sonido, por lo que los audios y videos se inician mediante interacción del usuario. El botón de reproducción aparece sólo cuando la lámina contiene multimedia.

La metadata de medios continúa centralizada en `app/Induction/HealthSafetyContent.php` y el comportamiento en `js/health-safety-induction.js`, evitando lógica multimedia acoplada directamente a la vista.
