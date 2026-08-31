<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$userId = require_login();

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT map_key, pos_x, pos_y, party_json, inventory_json,
                character_created, gender, skin_color, hair_color, eye_color
         FROM saves WHERE user_id = ?'
    );
    $stmt->execute([$userId]);
    $save = $stmt->fetch();

    if (!$save) {
        respond(['error' => 'No hay partida guardada'], 404);
    }

    respond([
        'ok' => true,
        'mapKey' => $save['map_key'],
        'x' => (int) $save['pos_x'],
        'y' => (int) $save['pos_y'],
        'party' => json_decode($save['party_json'], true),
        'inventory' => json_decode($save['inventory_json'], true),
        'characterCreated' => (bool) $save['character_created'],
        'appearance' => [
            'gender' => $save['gender'] ?? 'boy',
            'skinColor' => $save['skin_color'],
            'hairColor' => $save['hair_color'],
            'eyeColor' => $save['eye_color'],
        ],
    ]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
