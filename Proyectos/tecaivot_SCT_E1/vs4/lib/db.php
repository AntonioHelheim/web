<?php
/**
 * lib/db.php
 * Punto único de conexión a base de datos para los módulos nuevos en /api/.
 *
 * No duplica la lógica de conexión: reutiliza exactamente config.php (raíz
 * del proyecto), que ya resuelve credenciales por variable de entorno y el
 * manejo de errores de conexión. Los archivos en /api/{modulo}/*.php deben
 * incluir este archivo (no config.php directamente), para que si algún día
 * cambia dónde vive config.php, solo haya que actualizar este puente.
 *
 * Deja disponible la variable $pdo, igual que hoy hace config.php.
 */

require_once __DIR__ . '/../config.php';