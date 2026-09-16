<?php
// En terminal Iniciamos el servidor de MySQL con localhost y puerto 3306 



// ---- Configuración (con fallback a valores por defecto) ----
$host     = getenv('DB_HOST') ?: 'localhost';
$port     = (int) (getenv('DB_PORT') ?: 3306);
$dbname   = getenv('DB_NAME') ?: 'helheimc_ruko140';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// Habilitar excepciones en mysqli (mejor manejo de errores)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $dbname, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Si se accede directamente a este archivo, mostrar un error legible.
    if (php_sapi_name() !== 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo "<!doctype html><html lang='es'><head><meta charset='utf-8'><title>Error de conexión</title>";
        echo "<style>body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;background:#0f1115;color:#e6e6e6;padding:24px}
        .card{background:#141823;border:1px solid #22263a;border-radius:12px;padding:16px}</style></head><body>";
        echo "<div class='card'><h3>❌ Error de conexión</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p><strong>Host:</strong> " . htmlspecialchars($host) . " &middot; <strong>DB:</strong> " . htmlspecialchars($dbname) . " &middot; <strong>Usuario:</strong> " . htmlspecialchars($username) . "</p>";
        echo "<p>Verifica que el usuario tenga permisos desde este host, el puerto 3306 esté abierto y el <code>bind-address</code> permita conexiones remotas.</p>";
        echo "</div></body></html>";
        exit;
    }
    // Si está incluido por otro script, relanzar la excepción para que el caller la maneje.
    throw $e;
}

// ---- Self-Test (solo al acceder directamente a este archivo) ----
if (php_sapi_name() !== 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!doctype html><html lang='es'><head><meta charset='utf-8'><title>DB Self-Test</title>";
    echo "<style>body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;background:#0f1115;color:#e6e6e6;padding:24px}
    code{background:#1a1d24;border-radius:6px;padding:2px 6px} .ok{color:#4ade80} .warn{color:#f59e0b} .err{color:#f87171}
    .card{background:#141823;border:1px solid #22263a;border-radius:12px;padding:16px;margin:12px 0}
    ul{margin:0;padding-left:20px}</style></head><body>";
    echo "<h2>🔎 Prueba de conexión a base de datos</h2>";

    echo "<div class='card'><h3 class='ok'>✅ Conexión establecida</h3>";
    echo "<p><strong>Host:</strong> <code>" . htmlspecialchars($host) . ":" . (int)$port . "</code></p>";
    echo "<p><strong>Usuario:</strong> <code>" . htmlspecialchars($username) . "</code></p>";
    echo "<p><strong>Base de datos:</strong> <code>" . htmlspecialchars($dbname) . "</code></p></div>";

    // Listar tablas
    try {
        $tables = $conn->query("SHOW TABLES");
        if ($tables && $tables->num_rows > 0) {
            echo "<div class='card'><h3>📂 Tablas encontradas</h3><ul>";
            while ($r = $tables->fetch_array(MYSQLI_NUM)) {
                echo "<li>" . htmlspecialchars($r[0]) . "</li>";
            }
            echo "</ul></div>";
        } else {
            echo "<div class='card'><h3 class='warn'>⚠️ Sin tablas</h3><p>No se encontraron tablas o el usuario no posee permisos.</p></div>";
        }
    } catch (mysqli_sql_exception $e) {
        echo "<div class='card'><h3 class='err'>❌ Error al listar tablas</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre></div>";
    }

    // Prueba de consulta rápida
    try {
        $res = $conn->query("SELECT 1 AS ok");
        $row = $res ? $res->fetch_assoc() : null;
        if ($row && (int)$row['ok'] === 1) {
            echo "<div class='card'><h3 class='ok'>🧪 SELECT 1 → OK</h3></div>";
        } else {
            echo "<div class='card'><h3 class='warn'>🧪 SELECT 1 → Falló</h3></div>";
        }
    } catch (mysqli_sql_exception $e) {
        echo "<div class='card'><h3 class='err'>❌ Error en SELECT 1</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre></div>";
    }

    echo "<p style='opacity:.7'>Última verificación: " . date('d/m/Y H:i:s') . "</p>";
    echo "</body></html>";
    exit;
}

// Nota: cuando se incluye este archivo, NO imprime nada.
// $conn queda disponible para usar en el resto de la aplicación.




