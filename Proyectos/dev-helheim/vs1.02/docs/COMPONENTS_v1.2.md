# Arquitectura de componentes — Helheim vs1.2

## Objetivo

Permitir que navbar y footer se reutilicen sin duplicar HTML en el index, las páginas de `section/` y futuras rutas internas, manteniendo la etapa actual exclusivamente en HTML5, CSS3, Bootstrap y JavaScript.

## Estructura

```text
components/
├── layout/
│   ├── navbar.html
│   ├── footer.html
│   └── layout.css
└── home/
    ├── carousel-desktop.html
    ├── carousel-mobile.html
    ├── about.html
    ├── about.css
    ├── services.html / services.css
    ├── consultoria-ti.html / consultoria-ti.css
    ├── diseno-industrial.html / diseno-industrial.css
    ├── grabado-laser.html / grabado-laser.css
    ├── dibujo-tecnico.html / dibujo-tecnico.css
    └── contact.html / contact.css

js/
├── component-loader.js
└── helheim.js
```

`layout/` contiene componentes globales. `home/` contiene componentes que pertenecen únicamente a la portada.

## Reutilizar navbar y footer

En una página ubicada en `section/`:

```html
<link rel="stylesheet" href="../components/layout/layout.css?v=1.2.0-20260915.4">
```

En el body:

```html
<body data-nav-active="consultoria">
    <div data-component="layout/navbar.html"></div>

    <!-- contenido de la página -->

    <div data-component="layout/footer.html"></div>
```

Después de Bootstrap Bundle:

```html
<script src="../js/component-loader.js?v=1.2.0-20260915.4"></script>
```

En una página con mayor profundidad sólo cambia la ruta hacia el loader y el CSS, por ejemplo `../../js/component-loader.js`. El loader calcula la raíz real del sitio desde su propia URL, por lo que los enlaces internos del navbar continúan apuntando al lugar correcto.

## Menú activo

El atributo opcional `data-nav-active` permite indicar la sección actual:

- `home`
- `consultoria`
- `diseno`
- `grabado-laser`
- `dibujo-tecnico`
- `contacto`

El componente aplica `aria-current="page"` al enlace correspondiente.

## Rutas internas reutilizables

Dentro de un componente global se utiliza `data-site-href` cuando un enlace debe resolverse desde la raíz del proyecto:

```html
<a href="section/01_CTI_ISDW_IS.html"
   data-site-href="section/01_CTI_ISDW_IS.html">
   Consultoría TI
</a>
```

Esto permite que el mismo archivo funcione tanto desde `/index.html` como desde `/section/...` y también cuando Helheim se prueba en una subcarpeta de XAMPP.

## Dependencias

Los componentes compartidos esperan que la página cargue Bootstrap 5.3.x y Bootstrap Icons. `layout.css` contiene los estilos propios del navbar/footer. Para replicar exactamente la tipografía del index se cargan `Bebas Neue` y `Barlow Condensed` en las páginas actuales.

## Ejecución local

Los componentes se obtienen como archivos HTML estáticos mediante `fetch()`. Por políticas del navegador, el proyecto debe abrirse mediante HTTP/HTTPS (por ejemplo XAMPP/Apache) y no directamente como `file://`.

La composición de componentes continúa siendo frontend puro mediante HTML5, CSS3, Bootstrap y JavaScript. PHP 7.3+ se utiliza de forma acotada en `php/contact.php` para el formulario de contacto. MySQL no es necesario para la composición del sitio ni para el envío de correo; queda reservado para login, autenticación y persistencia futura.
