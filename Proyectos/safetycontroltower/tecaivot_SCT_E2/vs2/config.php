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

    // Desarrollo en equipo local (XAMPP + phpMyAdmin).
    //
    // SEPARACIÓN TECAIVOT / SCT — paso 1 (2026-09): la base local pasa a
    // llamarse "safetyco_SCT" (antes "tecaivot_SCT"), igual que ya pasó
    // en el servidor. Cada dev tiene que tener localmente una base con
    // ese nombre exacto — no se renombra sola. Formas de dejarla lista:
    //   a) Renombrar la base local existente "tecaivot_SCT" a
    //      "safetyco_SCT" (phpMyAdmin > Operaciones > "Renombrar la
    //      base de datos a"), o
    //   b) Crear "safetyco_SCT" vacía e importar ahí
    //      "safetyco_SCT(vs1).sql" (mismo archivo que ya se usó para
    //      migrar el servidor — funciona igual en local).
    // Si tu base local todavía se llama "tecaivot_SCT" y no quieres
    // renombrarla todavía, se puede seguir apuntando a ella sin tocar
    // este archivo: seteando la variable de entorno DB_NAME=tecaivot_SCT
    // en tu máquina.
    $host     = getenv('DB_HOST') ?: '127.0.0.1';
    $port     = (int) (getenv('DB_PORT') ?: '3306');
    $dbname   = getenv('DB_NAME') ?: 'safetyco_SCT';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    //http://localhost/phpmyadmin/

} else {

    // Servidor de desarrollo / producción (cPanel).
    //
    // SEPARACIÓN TECAIVOT / SCT — paso 1 (2026-09): host, base y ahora
    // también usuario/password ya apuntan al servidor nuevo
    // (201.148.104.98, base "safetyco_SCT", usuario "safetyco"). Esto
    // reemplaza el estado anterior de esta sección, donde a propósito
    // no se dejaba usuario/password como fallback — el equipo decidió
    // volver a dejarlos acá igual que estaban para el servidor viejo,
    // así que si en algún momento este archivo termina en un
    // repositorio compartido o público, hay que sacar estas credenciales
    // de acá y dejarlas solo por variable de entorno (DB_USER/DB_PASS).
    //
    // Nota sobre el puerto: dejar '' como fallback (en vez de '3306')
    // hace que $port termine en 0 — no es un error: el cliente de MySQL
    // interpreta el puerto 0 como "usar el puerto por defecto (3306)",
    // así que el resultado es el mismo, solo que resuelto por la
    // librería de MySQL en vez de hardcodeado acá. Si el servidor nuevo
    // llegara a usar un puerto no estándar, hay que setear DB_PORT por
    // variable de entorno explícitamente.
    $host     = getenv('DB_HOST') ?: '201.148.104.98';
    $port     = (int) (getenv('DB_PORT') ?: '');
    $dbname   = getenv('DB_NAME') ?: 'safetyco_SCT';
    $username = getenv('DB_USER') ?: 'safetyco';
    $password = getenv('DB_PASS') ?: 'cBz1t89lJ6*Y+B';
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