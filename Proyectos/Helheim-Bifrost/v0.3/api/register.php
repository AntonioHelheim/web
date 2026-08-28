<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$input = json_input();
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (strlen($username) < 3 || strlen($username) > 32) {
    respond(['error' => 'El usuario debe tener entre 3 y 32 caracteres'], 422);
}
if (strlen($password) < 6) {
    respond(['error' => 'La contraseña debe tener al menos 6 caracteres'], 422);
}

try {
    $pdo = db();

    $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $check->execute([$username]);
    if ($check->fetch()) {
        respond(['error' => 'Ese usuario ya existe'], 409);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
    $insert->execute([$username, $hash]);
    $userId = (int) $pdo->lastInsertId();

    // Crea una partida inicial vacía para que el jugador arranque en el mapa base.
    $initialParty = json_encode([]);
    $initialInventory = json_encode(['pokeball' => 5]);
    $seed = $pdo->prepare(
        'INSERT INTO saves (user_id, map_key, pos_x, pos_y, party_json, inventory_json)
         VALUES (?, "overworld", 5, 5, ?, ?)'
    );
    $seed->execute([$userId, $initialParty, $initialInventory]);

    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;

    respond(['ok' => true, 'user' => ['id' => $userId, 'username' => $username]], 201);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
