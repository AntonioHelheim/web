<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$input = json_input();
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        respond(['error' => 'Usuario o contraseña incorrectos'], 401);
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];

    respond(['ok' => true, 'user' => ['id' => $user['id'], 'username' => $user['username']]]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
