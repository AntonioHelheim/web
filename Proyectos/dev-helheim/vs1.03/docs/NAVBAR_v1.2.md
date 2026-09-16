# Navbar global — Helheim vs1.2

El navbar se mantiene como componente global reutilizable:

`components/layout/navbar.html`

Todas las páginas que incluyan:

```html
<div data-component="layout/navbar.html"></div>
```

recibirán automáticamente la misma navegación mediante `js/component-loader.js`.

## Menú activo

| Opción | Destino |
|---|---|
| Inicio | `index.html` |
| Consultoría TI | `index.html#consultoria-ti` |
| Diseño Industrial | `index.html#diseno-industrial` |
| Grabado Láser | `index.html#grabado-laser` |
| Dibujo Técnico | `index.html#dibujo-tecnico` |
| Contacto | `index.html#formcontacto` |
| Login | Modal Bootstrap `#helheimLoginModal` |

Las cuatro anclas de servicios son contratos de navegación preparados para las secciones que se construirán a continuación en el index.

## Menú anterior

Los elementos legacy que siguen siendo útiles para trazabilidad permanecen comentados dentro de `navbar.html`. Escáner Vehicular y Drones se retiraron también de ese bloque durante la limpieza del index.

## Login

La vs1.2 sólo prepara la interfaz de Login con dos campos:

- Usuario o correo.
- Código de acceso.

No hay autenticación, persistencia, envío HTTP ni almacenamiento local de credenciales en esta etapa. El botón `Ingresar` permanece deshabilitado hasta incorporar la lógica backend acordada posteriormente con PHP 7.3+ y MySQL.

