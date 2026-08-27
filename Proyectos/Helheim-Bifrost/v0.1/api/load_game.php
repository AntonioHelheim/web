<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$userId = require_login();

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT map_key, pos_x, pos_y, party_json, inventory_json FROM saves WHERE user_id = ?'
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
    ]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
