<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$userId = require_login();
$battleId = (int) ($_GET['battleId'] ?? 0);

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM pvp_battles WHERE id = ?');
    $stmt->execute([$battleId]);
    $battle = $stmt->fetch();
    if (!$battle) {
        respond(['error' => 'Batalla no encontrada'], 404);
    }

    $isPlayer1 = $userId === (int) $battle['player1_id'];
    if (!$isPlayer1 && $userId !== (int) $battle['player2_id']) {
        respond(['error' => 'No autorizado'], 403);
    }

    $winner = null;
    if ($battle['winner_id'] !== null) {
        $winner = ((int) $battle['winner_id'] === $userId) ? 'you' : 'opponent';
    }

    respond([
        'ok' => true,
        'status' => $battle['status'],
        'yourTurn' => $battle['status'] === 'active' && (int) $battle['turn_user_id'] === $userId,
        'you' => json_decode($isPlayer1 ? $battle['mon1_json'] : $battle['mon2_json'], true),
        'opponent' => json_decode($isPlayer1 ? $battle['mon2_json'] : $battle['mon1_json'], true),
        'lastAction' => $battle['last_action'],
        'winner' => $winner,
    ]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
