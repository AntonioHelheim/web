# Tema e idiomas — Helheim.cl vs1.03

## Tema

`js/theme.js` administra `dark` y `light` mediante el atributo `data-theme` del elemento `<html>`.

Persistencia:

```text
localStorage["helheim-theme"] = "dark" | "light"
```

El tema predeterminado es `dark`.

Los tokens de color están centralizados en `css/theme.css`. Para el tema claro:

```text
Acento principal: #7a1732 (vino tinto)
Acento secundario/social: #198754 (verde)
Fondo: #f4efe9
Superficie: #fffaf7
Texto: #2e2427
```

## Idiomas

El motor se encuentra en `lang/i18n.js` y los catálogos en archivos independientes:

```text
lang/es.js
lang/en.js
lang/pt.js
```

Persistencia:

```text
localStorage["helheim-language"] = "es" | "en" | "pt"
```

Español es el idioma predeterminado.

### Agregar un idioma

1. Crear `lang/<codigo>.js` siguiendo la estructura de `en.js` o `pt.js`.
2. Registrar el locale en `window.HelheimLocales.<codigo>`.
3. Agregar el código a `SUPPORTED` en `lang/i18n.js`.
4. Agregar la opción al selector de `components/layout/navbar.html`.
5. Incorporar el script antes de `lang/i18n.js` en `index.html` o extender el auto-loader.

El motor conserva el texto español original como fuente y aplica las traducciones al DOM actual y a componentes inyectados posteriormente.
