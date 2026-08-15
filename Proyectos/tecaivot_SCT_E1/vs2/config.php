<?php
/**
 * config.php
 * Conexión centralizada a la base de datos usando PDO.
 * Nunca dejes contraseñas reales en el código como valor "por defecto".
 * Configura estas variables como variables de entorno reales en el servidor
 * (ej. en el vhost de Apache, en un archivo .env cargado por tu framework, etc).
 */

// ---- Configuración (con fallback a valores por defecto) ----
$host     = getenv('DB_HOST') ?: '201.148.105.87';
$port     = (int) (getenv('DB_PORT') ?: '3306');
$dbname   = getenv('DB_NAME') ?: 'tecaivot_SCT';
$username = getenv('DB_USER') ?: 'tecaivot';
$password = getenv('DB_PASS') ?: '81V+hwXUb6.6Gz';


if (!$host || !$dbname || !$username || $password === false) {
    http_response_code(500);
    // Log interno para depurar (no se muestra al usuario)
    error_log('config.php: faltan variables de entorno de la base de datos.');
    die(json_encode(['success' => false, 'message' => 'Configuración de base de datos incompleta.']));
}
 
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
 
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
 
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('config.php: error de conexión a la base de datos - ' . $e->getMessage());
    // No exponer el mensaje real del error al cliente
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}