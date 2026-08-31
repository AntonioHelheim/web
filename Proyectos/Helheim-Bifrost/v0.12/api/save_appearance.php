<?php
/**
 * api/save_appearance.php
 *
 * Guarda la apariencia elegida — desde el 31-08-2026, el cliente solo
 * manda género + número de opción preestablecida (1-3), nunca colores
 * libres (cambio de jugabilidad: pool de apariencias en vez de elegir
 * cualquier color). El servidor resuelve los colores reales por su
 * cuenta con resolve_appearance_preset() (config.php) — no se confía en
 * lo que el cliente diga que representa cada color.
 */
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
require_csrf((string) ($input['csrf_token'] ?? ''));

$gender = ($input['gender'] ?? '') === 'girl' ? 'girl' : 'boy';
$preset = (int) ($input['preset'] ?? 0);

$colores = resolve_appearance_preset($gender, $preset);
if ($colores === null) {
    respond(['error' => 'Opción de apariencia no válida'], 422);
}

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'UPDATE saves
         SET gender = ?, appearance_preset = ?, skin_color = ?, hair_color = ?, eye_color = ?, character_created = 1
         WHERE user_id = ?'
    );
    $stmt->execute([$gender, $preset, $colores['skinColor'], $colores['hairColor'], $colores['eyeColor'], $userId]);
    respond(['ok' => true]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
