# Componentes Helheim vs1.2

La carpeta `components/` se divide por alcance:

- `layout/`: componentes compartidos por todo el sitio.
  - `navbar.html`
  - `footer.html`
  - `layout.css`
- `home/`: componentes exclusivos del index.
  - `carousel-desktop.html`
  - `carousel-mobile.html`
  - `about.html` / `about.css`
  - `services.html` / `services.css`
  - `consultoria-ti.html` / `consultoria-ti.css`
  - `diseno-industrial.html` / `diseno-industrial.css`
  - `grabado-laser.html` / `grabado-laser.css`
  - `dibujo-tecnico.html` / `dibujo-tecnico.css`
  - `contact.html` / `contact.css`

Los componentes son HTML estático y se cargan en cliente mediante `js/component-loader.js`.
No requieren PHP ni MySQL.
