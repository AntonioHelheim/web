# Despliegue Helheim.cl — vs1.2

## Recomendación

Desplegar el contenido completo de esta carpeta como baseline de vs1.2.

## Requisitos

- Servir el sitio mediante HTTP/HTTPS (Apache/XAMPP en desarrollo o el hosting web en producción).
- No abrir el proyecto directamente mediante `file://`, ya que los componentes HTML se cargan con `fetch()` desde archivos estáticos.
- Mantener acceso a los CDN actualmente usados por Bootstrap, Bootstrap Icons y Google Fonts.
- PHP 7.3 o superior con la función `mail()` habilitada/configurada para el formulario de contacto.

## Archivos clave

```text
index.html
css/helheim.css
js/component-loader.js
js/helheim.js
components/layout/navbar.html
components/layout/footer.html
components/layout/layout.css
components/home/*
section/01_CTI_ISDW_IS.html
section/10_DIN_IyC_DG.html
php/contact.php
```

## Componentes compartidos

Navbar y footer se cargan así:

```html
<div data-component="layout/navbar.html"></div>
...
<div data-component="layout/footer.html"></div>
```

Una página interna debe incluir `layout.css`, Bootstrap Bundle y una ruta válida hacia `js/component-loader.js`.

## Backend

vs1.2 incorpora PHP 7.3+ únicamente para `php/contact.php`, endpoint responsable del formulario del home. No se incorpora MySQL en esta etapa. Login, autenticación, persistencia y sistemas anexos continúan fuera del alcance hasta que se defina su lógica.

El envío usa `mail()`, por lo que el hosting debe disponer de transporte de correo saliente correctamente configurado.
