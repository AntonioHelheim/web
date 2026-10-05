SCT vs3 — normalización visual basada en vs4 (02-10-2026)

Objetivo:
- Mantener vs3 como base funcional.
- Portar únicamente interfaz/UX desde vs4.

Se conservaron sin reemplazo:
- APIs funcionales de vs3.
- JS de módulos de vs3.
- rutas y nombres de archivos existentes.
- lógica de permisos, consultas y contratos JSON de vs3.
- estructura y SQL de la aplicación.

Se incorporó:
- paleta y foundation CSS de vs4;
- normalización responsive y accesibilidad visual;
- navbar interno reutilizable, sin mocks ni APIs nuevas;
- navegación desktop/tablet/mobile;
- modo oscuro local (localStorage);
- búsqueda local de secciones en la pantalla;
- estilos coherentes para cards, formularios, tablas y modales;
- clases visuales del landing público de vs4.

Archivos nuevos principales:
- partials/app-navbar.php
- css/sct-v3-frontend.css
- js/sct-v3-frontend.js
- hojas sct-*.css visuales importadas desde vs4
- images/flags/*
