<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$userId = require_login();
$mapKey = substr((string) ($_GET['map'] ?? 'overworld'), 0, 64);

try {
    $pdo = db();
    // Solo se muestran jugadores que reportaron posición en los últimos 8
    // segundos: si alguien cierra la pestaña sin salir, desaparece solo.
    $stmt = $pdo->prepare(
        'SELECT username, pos_x, pos_y, facing
         FROM player_positions
         WHERE map_key = :map AND user_id != :uid
           AND updated_at > (NOW() - INTERVAL 8 SECOND)'
    );
    $stmt->execute([':map' => $mapKey, ':uid' => $userId]);
    $rows = $stmt->fetchAll();

    respond([
        'ok' => true,
        'players' => array_map(static function (array $r): array {
            return [
                'username' => $r['username'],
                'x' => (int) $r['pos_x'],
                'y' => (int) $r['pos_y'],
                'facing' => $r['facing'],
            ];
        }, $rows),
    ]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
