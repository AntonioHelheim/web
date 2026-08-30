<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
$battleId = (int) ($input['battleId'] ?? 0);
$action = (string) ($input['action'] ?? '');

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM pvp_battles WHERE id = ? AND status = "active"');
    $stmt->execute([$battleId]);
    $battle = $stmt->fetch();
    if (!$battle) {
        respond(['error' => 'Batalla no encontrada o ya terminó'], 404);
    }

    $isPlayer1 = $userId === (int) $battle['player1_id'];
    if (!$isPlayer1 && $userId !== (int) $battle['player2_id']) {
        respond(['error' => 'No autorizado'], 403);
    }
    if ((int) $battle['turn_user_id'] !== $userId) {
        respond(['error' => 'No es tu turno'], 409);
    }

    $opponentId = $isPlayer1 ? (int) $battle['player2_id'] : (int) $battle['player1_id'];

    if ($action === 'run') {
        $pdo->prepare(
            'UPDATE pvp_battles SET status = "finished", winner_id = ?, last_action = ? WHERE id = ?'
        )->execute([$opponentId, 'El rival huyó de la batalla.', $battleId]);
        respond(['ok' => true]);
    }

    $mineField = $isPlayer1 ? 'mon1_json' : 'mon2_json';
    $theirField = $isPlayer1 ? 'mon2_json' : 'mon1_json';
    $mine = json_decode($battle[$mineField], true);
    $theirs = json_decode($battle[$theirField], true);

    // Cálculo de daño: misma fórmula que js/core/battleRules.js
    // (calculateDamage) y calculate_damage() en config.php, ejecutada acá
    // en el servidor para que ambos jugadores vean el mismo resultado sin
    // importar la latencia de cada uno, y para que ninguno pueda alterar
    // el resultado editando su propio JS. Si cambias la fórmula en un
    // lado, cambia también en el otro — ver la nota en battleRules.js
    // (ítem 4 del roadmap: unificar esto en una sola fuente de datos).
    $dmg = calculate_damage($mine, $theirs);
    $theirs['hp'] = max(0, $theirs['hp'] - $dmg);
    $log = "{$mine['name']} atacó a {$theirs['name']} por {$dmg} de daño.";

    if ($theirs['hp'] <= 0) {
        $log .= " ¡{$theirs['name']} fue derrotado!";
        $pdo->prepare(
            "UPDATE pvp_battles SET {$theirField} = ?, status = 'finished', winner_id = ?, last_action = ? WHERE id = ?"
        )->execute([json_encode($theirs), $userId, $log, $battleId]);
        respond(['ok' => true]);
    }

    $pdo->prepare(
        "UPDATE pvp_battles SET {$theirField} = ?, turn_user_id = ?, last_action = ? WHERE id = ? AND turn_user_id = ?"
    )->execute([json_encode($theirs), $opponentId, $log, $battleId, $userId]);

    respond(['ok' => true]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
