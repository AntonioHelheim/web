# Cambios en esta entrega (e2_vs5 + tecaivot-site)

Sobre la base de `e2_vs4` (conexión a `safetyco_SCT`). Dos entregables
separados esta vez, para dos hostings distintos:

- `e2_vs5.tar.gz` — la app de SCT (todo lo que ya existía: auth,
  empresas, usuarios, proyectos, centros, trabajadores), con la marca
  y el sitio público actualizados.
- `tecaivot-site.tar.gz` — sitio nuevo, standalone, para
  `www.tecaivot.cl`. No depende de PHP ni de la base de datos — es HTML
  autocontenido, pensado para poder vivir en cualquier hosting sin
  arrastrar nada del proyecto de SCT.

## 1. Favicon y colores de SCT (a partir del logo provisto)

- Colores extraídos por muestreo directo de la imagen (no a ojo):
  navy `#002259`, celeste `#00A3F4`, dorado `#FDC500`. Aplicados en
  `css/style.css` reemplazando los valores de las variables ya
  existentes (`--primary`, `--primary-dark`, `--primary-darker`,
  `--primary-darkest`, `--accent`, `--accent-dark`) — no se tocó
  ningún otro lugar del CSS, los +100 usos de esas variables se
  actualizan solos.
- El fondo del logo se quitó por croma (no por color de fondo fijo),
  con limpieza de ruido de compresión JPEG y corrección del halo claro
  en los bordes. Verificado visualmente a tamaño real (32/64/100/150px)
  antes de darlo por bueno.
- Generado el set completo: `favicon.ico`, `favicon-16x16.png`,
  `favicon-32x32.png`, `apple-touch-icon.png`,
  `android-chrome-192x192.png`, `android-chrome-512x512.png`,
  `logo512-512.png` (todos sobre fondo blanco sólido, salvo los
  favicons que mantienen transparencia).
- `Logo-SCT.png` (color, para fondos claros) y `Logo-SCT-white.png`
  (silueta blanca, para fondos oscuros) reemplazan a
  `Logo-SCT.svg`/`Logo-SCT-white.svg` — se optó por PNG en vez de
  recrear un SVG a mano, para garantizar fidelidad exacta al logo
  aprobado en vez de arriesgar una interpretación distinta al tracear
  el diseño. Mismo nombre de archivo en los casos donde no cambió el
  path, así que la mayoría de los usos (`bienvenida.php`,
  `login-modal.php`, los 5 `gestion-*.php`) no necesitaron editarse,
  solo la extensión `.svg` → `.png`.
- `site.webmanifest` actualizado (nombre "Safety Control Tower",
  `theme_color` navy). `head.php` con favicon.ico explícito, meta
  `theme-color`, y `og:url`/`og:image`/`twitter:*` apuntando a
  `www.safetycontroltower.cl`.

## 2. Sitio de SCT enfocado solo en el producto

- Navbar: logo y texto de marca pasan de Tecaivot a SCT.
- "Nosotros" → reenfocado como "Beneficios": ya no habla de Tecaivot
  como empresa, habla de por qué usar SCT (trazabilidad, cumplimiento
  normativo, visibilidad en tiempo real).
- "Productos" → reenfocado como "Funcionalidades": la descripción del
  producto ahora menciona los módulos reales (empresas, proyectos,
  trabajadores, eventos, inducción, auditorías) en vez de una frase
  genérica.
- Contacto: correo y el ítem de "Empresa: Tecaivot" pasan a
  `contacto@safetycontroltower.cl` y "Producto: Safety Control Tower".
  El checkbox de privacidad ya no menciona a Tecaivot.
- Correo de login (`login.php`): asunto y remitente pasan a Safety
  Control Tower / `no-responder@safetycontroltower.cl`. Si el hosting
  nuevo no tiene SPF/DKIM configurado para ese dominio, estos correos
  pueden llegar a spam — no es algo que el código pueda resolver solo.
- Footer: se mantiene la mención a Tecaivot como pediste — "© Tecaivot.
  Todos los derechos reservados. · Desarrollado por Helheim.cl" (con
  link a helheim.cl). Es la única mención a Tecaivot que queda en todo
  el sitio de SCT, junto con el meta `copyright` de `head.php` (mismo
  criterio, ahí también corresponde legalmente).
- Las 5 traducciones (es/en/fr/pt/zh) se actualizaron completas y en
  paralelo — verificado que las 5 tienen exactamente las mismas 89
  claves, ninguna quedó desincronizada. Recomendación: dado el volumen
  de texto nuevo, vale la pena una revisión nativa de en/fr/pt/zh antes
  de publicar, sobre todo si hay matices de tono que quieran ajustar.

## 3. Sitio nuevo `tecaivot-site/`

- HTML autocontenido (sin dependencias de PHP ni de la base de datos),
  con la paleta ORIGINAL de Tecaivot (la que tenía todo el proyecto
  antes de este cambio: azul `#3D66FA`, navy `#1C257C`/`#0F172A`,
  púrpura `#8D78CB`) — deliberadamente independiente del CSS de SCT.
- Contenido: reutiliza el texto real de "Nosotros" que ya existía
  (Compromiso / Calidad técnica / Cercanía), no se inventó desde cero.
- Banner destacado "¿Buscas información sobre Safety Control Tower?"
  con link a `https://www.safetycontroltower.cl`, tal como pediste.
- Favicon propio: como no se proveyó un ícono de marca de Tecaivot
  separado del logotipo (que es un wordmark ancho, no sirve como
  ícono), se generó un monograma simple "T" en el azul de Tecaivot
  como placeholder — reemplazar si existe un ícono de marca real.
- Solo en español por ahora (a diferencia del sitio de SCT). Si se
  quiere el mismo esquema multi-idioma ahí, es un paso aparte.
- Renderizado y verificado visualmente (desktop y mobile) antes de
  entregarlo, no solo por lectura de código.

## Pendiente, fuera de este entregable

- Nadie desplegó todavía ninguno de los dos sitios — este es el
  entregable de código, falta la parte de hosting/DNS para que
  `www.safetycontroltower.cl` y `www.tecaivot.cl` respondan de verdad.
- El favicon del sitio de Tecaivot es un placeholder (ver arriba).
- Revisión nativa de las traducciones no-español recomendada.
