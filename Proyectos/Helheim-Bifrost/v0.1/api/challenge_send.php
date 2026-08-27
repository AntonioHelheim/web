<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
$toUsername = trim((string) ($input['toUsername'] ?? ''));

try {
    $pdo = db();

    $target = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $target->execute([$toUsername]);
    $targetRow = $target->fetch();
    if (!$targetRow) {
        respond(['error' => 'Jugador no encontrado'], 404);
    }
    $toUserId = (int) $targetRow['id'];
    if ($toUserId === $userId) {
        respond(['error' => 'No puedes retarte a ti mismo'], 422);
    }

    // Verificamos con las posiciones reales del servidor que de verdad están
    // uno junto al otro, para que no se pueda retar a distancia.
    $pos = $pdo->prepare(
        'SELECT user_id, map_key, pos_x, pos_y FROM player_positions WHERE user_id IN (?, ?)'
    );
    $pos->execute([$userId, $toUserId]);
    $byId = [];
    foreach ($pos->fetchAll() as $row) {
        $byId[(int) $row['user_id']] = $row;
    }
    if (!isset($byId[$userId]) || !isset($byId[$toUserId])) {
        respond(['error' => 'Ambos jugadores deben estar activos en el mapa'], 422);
    }
    $p1 = $byId[$userId];
    $p2 = $byId[$toUserId];
    if ($p1['map_key'] !== $p2['map_key']) {
        respond(['error' => 'Deben estar en el mismo mapa'], 422);
    }
    $distance = max(abs((int) $p1['pos_x'] - (int) $p2['pos_x']), abs((int) $p1['pos_y'] - (int) $p2['pos_y']));
    if ($distance > 1) {
        respond(['error' => 'Debes estar junto al otro jugador'], 422);
    }

    // Evita duplicar retos si el jugador presiona la tecla varias veces.
    $dupe = $pdo->prepare(
        'SELECT id FROM battle_challenges WHERE from_user_id = ? AND to_user_id = ? AND status = "pending"'
    );
    $dupe->execute([$userId, $toUserId]);
    if (!$dupe->fetch()) {
        $insert = $pdo->prepare('INSERT INTO battle_challenges (from_user_id, to_user_id) VALUES (?, ?)');
        $insert->execute([$userId, $toUserId]);
    }

    respond(['ok' => true]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
