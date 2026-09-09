<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$input = json_input();
$username = trim($input['username'] ?? '');
$email = trim((string) ($input['email'] ?? ''));
$csrfToken = (string) ($input['csrf_token'] ?? '');

require_csrf($csrfToken);

if (strlen($username) < 3 || strlen($username) > 32) {
    respond(['error' => 'El usuario debe tener entre 3 y 32 caracteres'], 422);
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['error' => 'Correo electrónico no válido.'], 422);
}

try {
    $pdo = db();

    $check = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $check->execute([$username, $email]);
    if ($check->fetch()) {
        respond(['error' => 'Ese usuario o correo ya está registrado'], 409);
    }

    // Sin contraseña: el acceso es por código de un solo uso enviado al
    // correo (ver api/login.php). password_hash queda NULL a propósito.
    $insert = $pdo->prepare('INSERT INTO users (username, email) VALUES (?, ?)');
    $insert->execute([$username, $email]);
    $userId = (int) $pdo->lastInsertId();

    // Crea una partida inicial vacía para que el jugador arranque en el mapa base.
    $initialParty = json_encode([]);
    $initialInventory = json_encode(['runa_captura' => 5]);
    $seed = $pdo->prepare(
        'INSERT INTO saves (user_id, map_key, pos_x, pos_y, party_json, inventory_json)
         VALUES (?, "overworld", 5, 5, ?, ?)'
    );
    $seed->execute([$userId, $initialParty, $initialInventory]);

    respond(['ok' => true, 'user' => ['id' => $userId, 'username' => $username, 'email' => $email]], 201);
} catch (PDOException $e) {
    error_log('register.php: ' . $e->getMessage());
    respond(['error' => 'Error de base de datos'], 500);
}
