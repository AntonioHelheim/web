# Helheim.cl — vs1.03

## Cierre de versión

vs1.03 consolida el index modular construido durante la normalización previa e incorpora dos capacidades transversales: temas e internacionalización.

### Tema

- El tema **oscuro** es el predeterminado y conserva la paleta aprobada de la versión anterior.
- Se incorpora tema **claro**.
- En tema claro, el acento verde principal pasa a **rojo vino tinto** (`#7a1732`).
- Los acentos/iconos sociales rojos pasan a **verde** (`#198754`).
- La elección se guarda en `localStorage` bajo `helheim-theme`.
- `css/theme.css` centraliza los tokens y overrides de tema.
- `js/theme.js` administra selección, persistencia y estado de controles.

### Idiomas

Idiomas iniciales:

- Español (`es`) — predeterminado.
- English (`en`).
- Português (`pt`).

La arquitectura vive en `/lang`:

- `lang/es.js`
- `lang/en.js`
- `lang/pt.js`
- `lang/i18n.js`

La elección se guarda en `localStorage` bajo `helheim-language`. El motor traduce contenido cargado dinámicamente por `component-loader.js`, atributos accesibles, placeholders, metadatos y mensajes conocidos del formulario.

### Componentes

El navbar global incorpora controles para tema e idioma. `component-loader.js` puede cargar automáticamente los recursos globales de tema/idioma en páginas que reutilicen los componentes compartidos.

### Compatibilidad

- Frontend: HTML5, CSS3, Bootstrap 5 y JavaScript.
- Contacto: PHP 7.3+.
- MySQL no es requerido por esta versión.
