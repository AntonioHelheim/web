<?php
/**
 * scripts/_csrf_case.php
 *
 * Auxiliar de scripts/test-csrf-and-rate-limit.php — NO se corre solo.
 * Simula una sesión con un token CSRF conocido y llama require_csrf()
 * (api/config.php, la función real) con el token que se le pase por
 * argumento. require_csrf() responde y termina el proceso (exit) si el
 * token no coincide — por eso esto vive en un subproceso aparte, para
 * que el script que orquesta las 3 pruebas pueda seguir corriendo las
 * demás después de que esta falle a propósito.
 *
 * Código de salida: 0 si el token era válido y la ejecución siguió, 1 si
 * require_csrf() cortó la ejecución (session_start + header ya hechos
 * por config.php, así que respond() sale con http_response_code(403) —
 * en CLI eso no se puede verificar directo, pero si el script llega a
 * "echo 'PASO'" es porque no se cortó).
 */
declare(strict_types=1);

session_start();
$_SESSION['csrf_token'] = 'token-correcto-de-la-sesion';

require __DIR__ . '/../api/config.php';

$tokenRecibido = $argv[1] ?? '';
require_csrf($tokenRecibido); // corta con exit() acá si el token no coincide

echo "PASO: token válido, la ejecución continuó normalmente.\n";
exit(0);
