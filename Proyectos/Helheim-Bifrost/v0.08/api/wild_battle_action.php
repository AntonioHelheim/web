<?php
/**
 * api/wild_battle_action.php
 *
 * Resuelve una acción ("attack" o "run") de una batalla silvestre activa.
 * Todo el cálculo de daño pasa por calculate_damage() (config.php, misma
 * fórmula que js/core/battleRules.js) — el cliente solo manda la acción
 * elegida, nunca un resultado. Cada respuesta persiste de inmediato el HP
 * del jugador en saves.party_json (persist_party_first_hp()), así el
 * servidor siempre tiene el HP real como fuente de verdad, incluso si el
 * jugador cierra el navegador a mitad de la batalla sin presionar "S".
 */
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$userId = require_login();
$input = json_input();
require_csrf((string) ($input['csrf_token'] ?? ''));
$battleId = (int) ($input['battleId'] ?? 0);
$action = (string) ($input['action'] ?? '');

if (!in_array($action, ['attack', 'run'], true)) {
    respond(['error' => 'Acción no reconocida'], 400);
}

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM wild_battles WHERE id = ? AND user_id = ? AND status = "active"');
    $stmt->execute([$battleId, $userId]);
    $battle = $stmt->fetch();
    if (!$battle) {
        respond(['error' => 'Batalla no encontrada o ya terminó'], 404);
    }

    $you = json_decode($battle['player_mon_json'], true);
    $enemy = json_decode($battle['enemy_mon_json'], true);

    // Termina la batalla: aplica recuperación tras desmayo si corresponde,
    // persiste el HP final en el equipo guardado, y responde.
    $finish = function (string $outcome, string $mensaje) use ($pdo, $battleId, $userId, &$you, &$enemy) {
        if ($outcome === 'lose') {
            $you['hp'] = faint_recovery_hp($you);
        }
        persist_party_first_hp($pdo, $userId, (int) $you['hp']);
        $pdo->prepare(
            'UPDATE wild_battles SET status = "finished", outcome = ?, player_mon_json = ?, enemy_mon_json = ?, last_action = ? WHERE id = ?'
        )->execute([$outcome, json_encode($you), json_encode($enemy), $mensaje, $battleId]);
        respond(['ok' => true, 'status' => 'finished', 'outcome' => $outcome, 'you' => $you, 'enemy' => $enemy, 'message' => $mensaje]);
    };

    // Contraataque del enemigo — se reutiliza tanto si el jugador atacó y
    // no derrotó al rival, como si intentó huir y no lo logró. Prefija el
    // mensaje del ataque del jugador (si hubo uno) para narrar la ronda
    // completa en un solo mensaje.
    $enemyStrikes = function (string $mensajePrevio = '') use (&$you, &$enemy, $finish, $pdo, $battleId, $userId) {
        $dmg = calculate_damage($enemy, $you);
        $you['hp'] = max(0, $you['hp'] - $dmg);
        $mensaje = trim($mensajePrevio . " {$enemy['name']} contraataca. {$dmg} de daño.");

        if ($you['hp'] <= 0) {
            $finish('lose', "{$mensaje} {$you['name']} no puede continuar...");
        }

        persist_party_first_hp($pdo, $userId, (int) $you['hp']);
        $pdo->prepare('UPDATE wild_battles SET player_mon_json = ?, enemy_mon_json = ?, last_action = ? WHERE id = ?')
            ->execute([json_encode($you), json_encode($enemy), $mensaje, $battleId]);
        respond(['ok' => true, 'status' => 'active', 'you' => $you, 'enemy' => $enemy, 'message' => $mensaje]);
    };

    if ($action === 'run') {
        if (attempt_escape()) {
            $finish('flee', 'Escapaste con éxito.');
        }
        $enemyStrikes('¡No pudiste escapar!');
    }

    // action === 'attack'
    $dmg = calculate_damage($you, $enemy);
    $enemy['hp'] = max(0, $enemy['hp'] - $dmg);
    $mensajeAtaque = "{$you['name']} ataca. {$dmg} de daño.";

    if ($enemy['hp'] <= 0) {
        $finish('win', "{$mensajeAtaque} ¡{$enemy['name']} fue derrotado!");
    }

    $enemyStrikes($mensajeAtaque);
} catch (PDOException $e) {
    error_log('wild_battle_action.php: ' . $e->getMessage());
    respond(['error' => 'Error de base de datos'], 500);
}
