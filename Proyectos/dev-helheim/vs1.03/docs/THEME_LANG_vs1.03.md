# Tema e idiomas — Helheim.cl vs1.03

## Tema

`js/theme.js` administra `dark` y `light` mediante el atributo `data-theme` del elemento `<html>`.

Persistencia:

```text
localStorage["helheim-theme"] = "dark" | "light"
```

El tema predeterminado es `dark`.

Tema claro:

```text
Acento principal: #7a1732 (vino tinto)
Acento secundario/social: #198754 (verde)
Fondo: #f4efe9
Superficie: #fffaf7
Texto: #2e2427
```

En tema oscuro el botón de navegación móvil utiliza un trazo verde claro explícito para mantener contraste sobre el navbar negro.

## Carrusel responsive

`components/home/carousel.css` conserva las proporciones originales de las piezas gráficas:

```text
Desktop: 1350 × 650
Mobile:  1000 × 1200
```

Reglas de selección:

- Teléfono: pieza móvil en vertical y horizontal.
- Tablet 768–1024 px: pieza móvil en vertical; pieza desktop en horizontal si la altura útil es al menos 600 px.
- Escritorio ≥1025 px: pieza desktop.

No se fuerza altura sobre las imágenes, por lo que no hay deformación ni recorte mediante `object-fit: cover`.

## Idiomas

El motor se encuentra en `lang/i18n.js`. Los catálogos son independientes:

```text
lang/es.js  — 🇨🇱 Español
lang/en.js  — 🇺🇸 English
lang/pt.js  — 🇧🇷 Português
lang/ja.js  — 🇯🇵 日本語
lang/da.js  — 🇩🇰 Dansk
lang/eu.js  — 🇪🇸 Euskara
lang/is.js  — 🇮🇸 Íslenska
lang/fr.js  — 🇫🇷 Français
lang/zh.js  — 🇨🇳 中文
lang/ru.js  — 🇷🇺 Русский
```

Para Euskara se utiliza la bandera de España en el selector porque Unicode no define un emoji regional específico del País Vasco. El código del idioma sigue siendo `eu`.

Persistencia:

```text
localStorage["helheim-language"]
```

Valores permitidos:

```text
es | en | pt | ja | da | eu | is | fr | zh | ru
```

Español (`es-CL`) es el idioma predeterminado.

### Agregar un idioma futuro

1. Crear `lang/<codigo>.js` siguiendo la estructura actual.
2. Registrar el locale en `window.HelheimLocales.<codigo>`.
3. Añadir código, etiqueta, bandera, `meta.htmlLang`, `strings` y `whatsappMessage`.
4. Agregar el código a `SUPPORTED` en `lang/i18n.js`.
5. Añadir la opción al selector de `components/layout/navbar.html`.
6. Incorporar el script en `index.html`; `component-loader.js` debe incluirlo también si se requiere desde páginas internas.

El motor conserva el texto español original como fuente y aplica las traducciones al DOM y a los componentes cargados dinámicamente.

## Cierre visual vs1.03 (build 1.03.0-20260916.3)

- Los controles superiores se mantienen siempre visibles y ordenados: **Tema → Idioma → Menú**.
- En móvil se compactan las etiquetas, manteniendo visibles icono de tema, bandera/código de idioma y botón de menú.
- La tipografía aumenta progresivamente en viewports de escritorio: 105% desde 1280 px, 108% desde 1600 px, 112% desde 1920 px y 116% desde 2560 px.
- Tema claro: blanco invernal `#f7f3f5`, superficies `#fffafb`, texto principal casi negro `#111113`, acento vino `#7b1e3b` y acento secundario verde `#237a4b`.
- La equivalencia cromática es: verde del dark → vino en light; blanco del dark → negro en light; rojo del dark → verde en light. Los botones rellenos sobre vino conservan texto claro por contraste/accesibilidad.


## Estabilización de controles — build 1.03.0-20260916.4

El navbar utiliza una topbar independiente para `Tema → Idioma → Menú`. El panel de navegación colapsable se renderiza debajo de esa fila y nunca participa del mismo flujo flex. Los dropdowns de Tema/Idioma y el `collapse` son mutuamente excluyentes para evitar solapamientos. Los nombres del selector de idiomas son autónimos (Español, English, Português, 日本語, etc.).


## Paneles de preferencia — build 1.03.0-20260916.5

Tema e Idioma usan paneles flotantes propios y no Bootstrap Dropdown. Esto evita barras de desplazamiento y clipping dentro del navbar fijo. El panel de idioma muestra los doce idiomas en dos columnas y ambos paneles se posicionan dinámicamente dentro del viewport.


## Idiomas añadidos en Build .7
- 🇩🇪 Deutsch (`de`, `de-DE`)
- 🇮🇹 Italiano (`it`, `it-IT`)

La matriz activa queda en 12 idiomas y cada locale mantiene el mismo catálogo de claves.


## Cierre light/contacto/footer — build 1.03.0-20260916.8

- Light usa una escala de grises rosados invernales más oscura que el blanco puro para conservar relieve visual.
- Redes sociales de Contacto: Dark negro/rojo → hover verde/rojo; Light negro/verde → hover vino/verde.
- El formulario adopta una superficie gráfica clara en Light, con inputs claros, texto oscuro y acento vino.
- Footer con tipografía responsive aumentada al menos 10% sobre la base anterior.
- Footer incorpora “Quiénes Somos” y enlaces a los perfiles de los dos cofundadores.
- Los roles del footer se traducen en los 12 idiomas activos.
