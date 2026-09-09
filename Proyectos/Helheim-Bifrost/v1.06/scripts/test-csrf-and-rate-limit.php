<?php
/**
 * scripts/test-csrf-and-rate-limit.php
 *
 * Prueba require_csrf() (bloquea token vacío/incorrecto, deja pasar el
 * correcto) y count_recent_challenges() + MAX_RETOS_POR_MINUTO
 * (api/config.php) contra una base de datos SQLite en memoria — ítem 10
 * de ROADMAP-ARQUITECTURA.md, reemplaza los arneses temporales usados a
 * mano durante el desarrollo del ítem 5.
 *
 * require_csrf() llama exit() cuando el token es inválido — cada caso se
 * corre en un subproceso PHP aparte (no se puede seguir ejecutando en el
 * mismo proceso después de un exit()), así que este script actúa como
 * "orquestador" que lanza scripts/_csrf_case.php tres veces y revisa el
 * CONTENIDO de la salida de cada uno (no el código de salida del
 * proceso: la respond() real usa `exit;` sin argumento, que en PHP
 * siempre sale con código 0 sin importar el http_response_code() fijado
 * antes — el 403 va en el cuerpo/status HTTP, no en el código de salida).
 *
 * Uso: php scripts/test-csrf-and-rate-limit.php
 */
declare(strict_types=1);

// Se carga primero (antes de cualquier echo) para que su propio
// session_start() interno no choque con "headers ya enviados" — la parte
// de CSRF de abajo no necesita nada de acá (corre subprocesos aparte),
// pero la de rate-limiting sí (count_recent_challenges, MAX_RETOS_POR_MINUTO).
require __DIR__ . '/../api/config.php';

$fallas = 0;
function verificar(bool $cond, string $msg): void
{
    global $fallas;
    if ($cond) {
        echo "OK: $msg\n";
    } else {
        echo "FALLA: $msg\n";
        $fallas++;
    }
}

echo "=== require_csrf() ===\n";
$casoScript = __DIR__ . '/_csrf_case.php';

// OJO: la respond() real del proyecto usa `exit;` sin argumento al final
// (ver api/config.php), y en PHP eso siempre sale con código 0 pase lo
// que pase — el código HTTP (403, etc.) va en el cuerpo/http_response_code,
// no en el código de salida del proceso. Por eso acá se verifica el
// CONTENIDO de la salida, no el código de salida.
exec('php ' . escapeshellarg($casoScript) . ' ' . escapeshellarg(''), $out1);
verificar(str_contains(implode("\n", $out1), '"error"'), 'token vacío queda bloqueado (la respuesta trae "error")');

exec('php ' . escapeshellarg($casoScript) . ' ' . escapeshellarg('token-adivinado-por-un-atacante'), $out2);
verificar(str_contains(implode("\n", $out2), '"error"'), 'token incorrecto (adivinado) queda bloqueado');

exec('php ' . escapeshellarg($casoScript) . ' ' . escapeshellarg('token-correcto-de-la-sesion'), $out3);
verificar(str_contains(implode("\n", $out3), 'PASO'), 'token correcto pasa normalmente (sin "error")');

echo "\n=== count_recent_challenges() / MAX_RETOS_POR_MINUTO ===\n";

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec(
    "CREATE TABLE battle_challenges (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        from_user_id INTEGER, to_user_id INTEGER,
        status TEXT DEFAULT 'pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )"
);

$userId = 1;
$bloqueadoEnIntento = null;
for ($i = 1; $i <= MAX_RETOS_POR_MINUTO + 5; $i++) {
    if (count_recent_challenges($pdo, $userId) >= MAX_RETOS_POR_MINUTO) {
        if ($bloqueadoEnIntento === null) {
            $bloqueadoEnIntento = $i;
        }
        continue;
    }
    $pdo->prepare('INSERT INTO battle_challenges (from_user_id, to_user_id) VALUES (?, ?)')->execute([$userId, 2]);
}

verificar(
    $bloqueadoEnIntento === MAX_RETOS_POR_MINUTO + 1,
    "se bloquea justo después de MAX_RETOS_POR_MINUTO (" . MAX_RETOS_POR_MINUTO . ") retos — se bloqueó en el intento {$bloqueadoEnIntento}"
);

// Un usuario distinto no debería verse afectado por el límite del primero.
verificar(count_recent_challenges($pdo, 2) === 0, 'el límite es por usuario, no global (otro usuario sigue en 0)');

echo "\n" . ($fallas === 0 ? 'TODAS LAS PRUEBAS PASARON' : "{$fallas} PRUEBA(S) FALLARON") . "\n";
exit($fallas === 0 ? 0 : 1);
