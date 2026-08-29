<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if (!empty($_SESSION['user_id'])) {
    try {
        $pdo = db();
        $pdo->prepare('DELETE FROM player_positions WHERE user_id = ?')->execute([(int) $_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Si falla, el jugador de todas formas desaparecerá solo en 8s por inactividad.
    }
}

$_SESSION = [];
session_destroy();

respond(['ok' => true]);
