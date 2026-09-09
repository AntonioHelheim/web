<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$input = json_input();
require_csrf((string) ($input['csrf_token'] ?? ''));

if (!empty($_SESSION['user_id'])) {
    try {
        $pdo = db();
        $pdo->prepare('DELETE FROM player_positions WHERE user_id = ?')->execute([(int) $_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Si falla, el jugador de todas formas desaparecerá solo en 8s por inactividad.
    }
}

$_SESSION = [];

// Eliminar la cookie de sesión del navegador (además de vaciar los datos
// y destruir la sesión en el servidor, abajo) — así no queda ninguna
// cookie de sesión vieja dando vueltas en el navegador.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

respond(['ok' => true]);
