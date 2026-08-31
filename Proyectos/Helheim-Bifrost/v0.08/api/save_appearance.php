<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
require_csrf((string) ($input['csrf_token'] ?? ''));

$gender = ($input['gender'] ?? '') === 'girl' ? 'girl' : 'boy';

$hexPattern = '/^#[0-9a-fA-F]{6}$/';
$skin = (string) ($input['skinColor'] ?? '#f1c27d');
$hair = (string) ($input['hairColor'] ?? '#2c1b18');
$eyes = (string) ($input['eyeColor'] ?? '#3b2415');

foreach ([$skin, $hair, $eyes] as $color) {
    if (!preg_match($hexPattern, $color)) {
        respond(['error' => 'Color inválido'], 422);
    }
}

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'UPDATE saves
         SET gender = ?, skin_color = ?, hair_color = ?, eye_color = ?, character_created = 1
         WHERE user_id = ?'
    );
    $stmt->execute([$gender, $skin, $hair, $eyes, $userId]);
    respond(['ok' => true]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
