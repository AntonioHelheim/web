<?php

/* =========================================================
   1. ENTORNO DE EJECUCIÓN
   ========================================================= */

/**
 * Determina si estamos en un entorno local de desarrollo.
 *
 * Orden de prioridad:
 *   1) Si APP_ENV está definida explícitamente (variable de entorno real,
 *      ej. seteada en el vhost de Apache o en el .htaccess), manda ella.
 *   2) Si no está definida, se detecta por el host con el que se accedió
 *      (localhost, 127.0.0.1, o dominios típicos de desarrollo local como
 *      *.test / *.local de XAMPP/Laragon/Valet). Esto es más confiable que
 *      asumir un valor por defecto a ciegas: nadie tiene que acordarse de
 *      configurar nada en su máquina local para que funcione, y el
 *      servidor real (con un host distinto) nunca cae en modo local por
 *      descuido — que es justamente lo que gatilla que login.php exponga
 *      el código OTP en la respuesta JSON (ver más abajo, sección 3).
 *   3) En contexto CLI (sin $_SERVER['HTTP_HOST'], ej. scripts de prueba),
 *      se asume NO local salvo que APP_ENV lo diga explícitamente.
 */
function detectarEntornoLocal(): bool
{
    $appEnv = getenv('APP_ENV');
    if ($appEnv !== false) {
        return $appEnv === 'local';
    }

    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    $host = strtolower(explode(':', $host)[0]); // quita el puerto si viene (ej. localhost:8080)

    if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        return true;
    }

    if (preg_match('/\.(test|local)$/', $host)) {
        return true;
    }

    return false;
}

$isLocal = detectarEntornoLocal();


/* =========================================================
   2. CREDENCIALES DE BASE DE DATOS POR ENTORNO
   ========================================================= */

if ($isLocal) {

    // Desarrollo en equipo local (XAMPP/MAMP/Laragon + phpMyAdmin).
    $host     = getenv('DB_HOST') ?: '127.0.0.1';
    $port     = (int) (getenv('DB_PORT') ?: '3306');
    $dbname   = getenv('DB_NAME') ?: 'tecaivot_SCT';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    //http://localhost/phpmyadmin/

} else {

    // Servidor de desarrollo / producción (cPanel). Es el resultado por
    // defecto salvo que se acceda desde un host local reconocido.
    $host     = getenv('DB_HOST') ?: '201.148.105.87';
    $port     = (int) (getenv('DB_PORT') ?: '3306');
    $dbname   = getenv('DB_NAME') ?: 'tecaivot_SCT';
    $username = getenv('DB_USER') ?: 'tecaivot';
    $password = getenv('DB_PASS') ?: '81V+hwXUb6.6Gz';
}


/* =========================================================
   3. VALIDACIÓN DE CONFIGURACIÓN
   ========================================================= */

if (!$host || !$dbname || !$username || $password === false) {
    http_response_code(500);
    // Log interno para depurar (no se muestra al usuario)
    error_log('config.php: faltan variables de entorno de la base de datos.');
    die(json_encode(['success' => false, 'message' => 'Configuración de base de datos incompleta.']));
}

/* =========================================================
   4. DSN
   ========================================================= */

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

/* =========================================================
   5. OPCIONES PDO
   ========================================================= */

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

/* =========================================================
   6. CONEXIÓN
   ========================================================= */
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('config.php: error de conexión a la base de datos - ' . $e->getMessage());
    // No exponer el mensaje real del error al cliente
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}