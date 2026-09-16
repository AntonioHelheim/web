# Helheim.cl vs1.2 — ejecución del formulario de contacto

## Comportamiento

El endpoint `php/contact.php` soporta tres transportes:

- `log`: recomendado en localhost. No envía correo y guarda una línea JSON por prueba en `storage/logs/contact.log`.
- `mail`: utiliza `mail()` de PHP. Requiere que el servidor tenga MTA/sendmail correctamente configurado.
- `smtp`: utiliza PHPMailer y un servidor SMTP. Es el modo recomendado para ambiente web.

Si no se define `HELHEIM_CONTACT_MODE`, localhost usa `log` automáticamente y un host web usa `mail` para mantener compatibilidad con la versión anterior.

## Localhost con XAMPP/Linux

No es necesario configurar correo saliente para probar la aplicación. Sirve el sitio por Apache/XAMPP y envía el formulario. La respuesta debe indicar que se ejecutó en modo local y el registro queda en:

`storage/logs/contact.log`

Si Apache no puede escribir allí, asigna permisos al usuario que ejecuta Apache.

## Ambiente web recomendado: SMTP

Instala dependencias con Composer en la raíz de `vs1.2`:

`composer install --no-dev --optimize-autoloader`

Configura estas variables de entorno en Apache, el panel del hosting o el servicio que ejecute PHP:

- `HELHEIM_ENV=development` (cambiar a `production` al publicar)
- `HELHEIM_CONTACT_MODE=smtp`
- `HELHEIM_SMTP_HOST=<host SMTP>`
- `HELHEIM_SMTP_PORT=587`
- `HELHEIM_SMTP_USER=<usuario SMTP>`
- `HELHEIM_SMTP_PASS=<contraseña o secreto SMTP>`
- `HELHEIM_SMTP_SECURE=tls`
- `HELHEIM_SMTP_FROM=no-reply@helheim.cl`
- `HELHEIM_SMTP_FROM_NAME=Helheim.cl Web`

No guardes la contraseña SMTP dentro del repositorio ni en archivos que se publiquen con el proyecto.

## MySQL

MySQL no es necesario para enviar el formulario. Sólo tendría sentido agregarlo posteriormente si se requiere persistir contactos, trazabilidad, estados de atención o reintentos de envío.
