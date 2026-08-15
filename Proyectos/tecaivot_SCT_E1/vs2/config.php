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

//https://www.tecaivot.cl/dev/tecaivot_SCT_E1/vs2/php/db_prod.php//

if (!$host || !$dbname || !$username) {
    http_response_code(500);
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
    // No exponer el mensaje real del error en producción
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}