<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
require_csrf((string) ($input['csrf_token'] ?? ''));

$mapKey = $input['mapKey'] ?? 'overworld';
$posX = (int) ($input['x'] ?? 0);
$posY = (int) ($input['y'] ?? 0);
$party = json_encode($input['party'] ?? []);
$inventory = json_encode($input['inventory'] ?? []);

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO saves (user_id, map_key, pos_x, pos_y, party_json, inventory_json)
         VALUES (:uid, :map, :x, :y, :party, :inv)
         ON DUPLICATE KEY UPDATE
            map_key = VALUES(map_key),
            pos_x = VALUES(pos_x),
            pos_y = VALUES(pos_y),
            party_json = VALUES(party_json),
            inventory_json = VALUES(inventory_json)'
    );
    $stmt->execute([
        ':uid' => $userId,
        ':map' => $mapKey,
        ':x' => $posX,
        ':y' => $posY,
        ':party' => $party,
        ':inv' => $inventory,
    ]);

    respond(['ok' => true]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
