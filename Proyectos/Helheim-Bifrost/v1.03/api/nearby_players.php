<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$userId = require_login();
$mapKey = substr((string) ($_GET['map'] ?? 'overworld'), 0, 64);

try {
    $pdo = db();
    // Solo se muestran jugadores que reportaron posición en los últimos 8
    // segundos: si alguien cierra la pestaña sin salir, desaparece solo.
    // Se une con `saves` para traer también su apariencia (no cambia en
    // cada movimiento, así que no hace falta reenviarla cada tick).
    $stmt = $pdo->prepare(
        'SELECT pp.username, pp.pos_x, pp.pos_y, pp.facing,
                s.gender, s.appearance_preset, s.skin_color, s.hair_color, s.eye_color
         FROM player_positions pp
         JOIN saves s ON s.user_id = pp.user_id
         WHERE pp.map_key = :map AND pp.user_id != :uid
           AND pp.updated_at > (NOW() - INTERVAL 8 SECOND)'
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
                'appearance' => [
                    'gender' => $r['gender'] ?? 'boy',
                    'preset' => $r['appearance_preset'] !== null ? (int) $r['appearance_preset'] : 1,
                    'skinColor' => $r['skin_color'],
                    'hairColor' => $r['hair_color'],
                    'eyeColor' => $r['eye_color'],
                ],
            ];
        }, $rows),
    ]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
