<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
require_csrf((string) ($input['csrf_token'] ?? ''));
$challengeId = (int) ($input['challengeId'] ?? 0);
$accept = !empty($input['accept']);

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT * FROM battle_challenges WHERE id = ? AND to_user_id = ? AND status = "pending"'
    );
    $stmt->execute([$challengeId, $userId]);
    $challenge = $stmt->fetch();
    if (!$challenge) {
        respond(['error' => 'Ese reto ya no está disponible'], 404);
    }

    if (!$accept) {
        $pdo->prepare('UPDATE battle_challenges SET status = "declined" WHERE id = ?')->execute([$challengeId]);
        respond(['ok' => true, 'accepted' => false]);
    }

    $fromUserId = (int) $challenge['from_user_id'];
    $mon1 = monster_for_user($pdo, $fromUserId); // el retador
    $mon2 = monster_for_user($pdo, $userId);      // quien acepta

    $insertBattle = $pdo->prepare(
        'INSERT INTO pvp_battles (player1_id, player2_id, mon1_json, mon2_json, turn_user_id, last_action)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $insertBattle->execute([
        $fromUserId,
        $userId,
        json_encode($mon1),
        json_encode($mon2),
        $fromUserId, // el retador empieza el primer turno
        '¡La batalla ha comenzado!',
    ]);
    $battleId = (int) $pdo->lastInsertId();

    $pdo->prepare('UPDATE battle_challenges SET status = "accepted", battle_id = ? WHERE id = ?')
        ->execute([$battleId, $challengeId]);

    respond(['ok' => true, 'accepted' => true, 'battleId' => $battleId]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
