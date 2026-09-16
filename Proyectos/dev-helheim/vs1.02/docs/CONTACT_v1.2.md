# Contacto — vs1.2

## WhatsApp

Número oficial configurado en esta versión: `+56 9 3544 4514`.
Los enlaces `wa.me` usan el formato internacional `56935444514`.

## Formulario de contacto

El formulario del home ya no usa Formspree. Envía `POST` a:

`php/contact.php`

El endpoint es compatible con **PHP 7.3+** y envía mediante la función nativa `mail()`:

- Destinatario: `juanantonioconchaloyola@gmail.com`
- Asunto: `contacto desde la web de helheim.cl`
- Datos enviados: nombre, correo y mensaje del formulario.
- `Reply-To`: correo ingresado por el visitante.
- Protección básica: validación servidor + honeypot antispam.

El navegador envía el formulario con `fetch()` y muestra éxito/error sin abandonar el index. Sin JavaScript, el endpoint sigue aceptando el POST y muestra una respuesta HTML simple.

## Requisito de hosting

`mail()` requiere que el servidor/hosting tenga transporte de correo PHP configurado. El código del sitio no necesita credenciales SMTP, pero la entrega real del mensaje depende de esa configuración y de la reputación/SPF/DKIM del dominio.
