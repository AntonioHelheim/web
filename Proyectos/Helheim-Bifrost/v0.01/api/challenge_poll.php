<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$userId = require_login();

try {
    $pdo = db();

    // ¿Alguien me retó y sigue pendiente de respuesta?
    $incoming = $pdo->prepare(
        'SELECT bc.id, u.username AS from_username
         FROM battle_challenges bc
         JOIN users u ON u.id = bc.from_user_id
         WHERE bc.to_user_id = ? AND bc.status = "pending"
         ORDER BY bc.created_at DESC LIMIT 1'
    );
    $incoming->execute([$userId]);
    $incomingRow = $incoming->fetch();

    // ¿Un reto que YO envié acaba de ser aceptado? Se entrega una sola vez
    // y luego se borra, para no reabrir la batalla en cada sondeo.
    $accepted = $pdo->prepare(
        'SELECT id, battle_id FROM battle_challenges
         WHERE from_user_id = ? AND status = "accepted" AND battle_id IS NOT NULL
         ORDER BY created_at DESC LIMIT 1'
    );
    $accepted->execute([$userId]);
    $acceptedRow = $accepted->fetch();
    if ($acceptedRow) {
        $pdo->prepare('DELETE FROM battle_challenges WHERE id = ?')->execute([$acceptedRow['id']]);
    }

    // ¿Un reto que yo envié fue rechazado? También se entrega una sola vez.
    $declined = $pdo->prepare(
        'SELECT id FROM battle_challenges WHERE from_user_id = ? AND status = "declined" ORDER BY created_at DESC LIMIT 1'
    );
    $declined->execute([$userId]);
    $declinedRow = $declined->fetch();
    if ($declinedRow) {
        $pdo->prepare('DELETE FROM battle_challenges WHERE id = ?')->execute([$declinedRow['id']]);
    }

    respond([
        'ok' => true,
        'incoming' => $incomingRow
            ? ['challengeId' => (int) $incomingRow['id'], 'fromUsername' => $incomingRow['from_username']]
            : null,
        'acceptedBattleId' => $acceptedRow ? (int) $acceptedRow['battle_id'] : null,
        'declined' => (bool) $declinedRow,
    ]);
} catch (PDOException $e) {
    respond(['error' => 'Error de base de datos'], 500);
}
