<?php
/**
 * api/config.local.php (PLANTILLA — este archivo se llama
 * "config.local.example.php" a propósito, para que nunca se confunda
 * con el real ni lo pise una entrega futura).
 *
 * CÓMO USARLO (una sola vez):
 * 1. Copia este archivo como `api/config.local.php` (sin ".example").
 * 2. Completa tus credenciales reales de producción abajo.
 * 3. Listo — `api/config.local.php` NUNCA se vuelve a entregar ni
 *    generar en futuras versiones del proyecto (está en .gitignore),
 *    así que tus credenciales sobreviven a cualquier actualización que
 *    te pase de aquí en adelante, sin importar cuántas veces
 *    sobrescribas el resto de los archivos del proyecto.
 *
 * Si tu servidor es de hosting compartido (cPanel), estos son los
 * valores que aparecen en "Bases de datos MySQL" — normalmente con un
 * prefijo, ej. `usuario_bifrost` (no solo `bifrost`).
 *
 * No hace falta que definas las 4 constantes — solo las que quieras
 * fijar acá. El resto sigue tomando el valor por defecto de
 * api/config.php como respaldo.
 */
declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'TU_USUARIO_bifrost');
define('DB_USER', 'TU_USUARIO_bifrost');
define('DB_PASS', 'TU_CONTRASEÑA_AQUI');
