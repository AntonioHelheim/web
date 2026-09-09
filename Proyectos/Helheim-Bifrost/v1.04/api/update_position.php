<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
require_csrf((string) ($input['csrf_token'] ?? ''));

$mapKey = substr((string) ($input['mapKey'] ?? 'overworld'), 0, 64);
$x = (int) ($input['x'] ?? 0);
$y = (int) ($input['y'] ?? 0);
$facing = substr((string) ($input['facing'] ?? 'down'), 0, 8);
$username = $_SESSION['username'];

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO player_positions (user_id, username, map_key, pos_x, pos_y, facing)
         VALUES (:uid, :uname, :map, :x, :y, :facing)
         ON DUPLICATE KEY UPDATE
            username = VALUES(username),
            map_key = VALUES(map_key),
            pos_x = VALUES(pos_x),
            pos_y = VALUES(pos_y),
            facing = VALUES(facing)'
    );
    $stmt->execute([
        ':uid' => $userId,
        ':uname' => $username,
        ':map' => $mapKey,
        ':x' => $x,
        ':y' => $y,
        ':facing' => $facing,
    ]);
    respond(['ok' => true]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
