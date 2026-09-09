<?php
/**
 * api/wild_battle_start.php
 *
 * Inicia una batalla contra una criatura salvaje, calculada en el
 * servidor (ítem 3 de ROADMAP-ARQUITECTURA.md). Antes de esto, el
 * enemigo y todo el cálculo de daño vivían en BattleScene.js — cualquiera
 * con las herramientas de desarrollador del navegador podía alterar el
 * resultado. Ahora el servidor:
 *  - Genera la criatura enemiga (random_monster(), en config.php),
 *    eligiendo del pool específico del mapa donde el jugador REALMENTE
 *    está parado (leído de la base de datos, no de lo que mande el
 *    cliente — mismo principio que el punto de abajo, para que nadie
 *    pueda "pedir" pelear contra el dragón del nivel 3 mandando ese
 *    mapKey en la petición sin haber llegado hasta ahí de verdad).
 *  - Usa el PRIMER monstruo del equipo GUARDADO del jugador como punto de
 *    partida (fuente de verdad: la base de datos, no lo que mande el
 *    cliente) — así nadie puede empezar una batalla con un monstruo
 *    inventado o con más HP del que realmente tiene guardado.
 */
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
require_csrf((string) ($input['csrf_token'] ?? ''));

try {
    $pdo = db();

    $stmt = $pdo->prepare('SELECT party_json, map_key FROM saves WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    $party = $row ? json_decode($row['party_json'], true) : [];
    $mapKey = $row ? (string) $row['map_key'] : null;

    if (!is_array($party) || count($party) === 0) {
        respond(['error' => 'Todavía no tienes ningún compañero.'], 422);
    }

    $playerMon = $party[0];
    if ((int) ($playerMon['hp'] ?? 0) <= 0) {
        respond(['error' => 'Tu compañero está debilitado y no puede pelear.'], 422);
    }

    $enemyMon = random_monster($mapKey);
    $mensaje = "Un {$enemyMon['name']} salvaje apareció.";

    $stmt = $pdo->prepare(
        'INSERT INTO wild_battles (user_id, player_mon_json, enemy_mon_json, last_action)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, json_encode($playerMon), json_encode($enemyMon), $mensaje]);

    respond([
        'ok' => true,
        'battleId' => (int) $pdo->lastInsertId(),
        'you' => $playerMon,
        'enemy' => $enemyMon,
        'message' => $mensaje,
    ]);
} catch (PDOException $e) {
    error_log('wild_battle_start.php: ' . $e->getMessage());
    respond(['error' => 'Error de base de datos'], 500);
}
