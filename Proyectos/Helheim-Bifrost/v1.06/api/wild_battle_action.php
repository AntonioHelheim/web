<?php
/**
 * api/wild_battle_action.php
 *
 * Resuelve una acción ("attack" o "run") de una batalla silvestre activa.
 * Toda la lógica real vive en resolve_wild_battle_action() (config.php) —
 * este archivo solo se encarga de la parte HTTP: leer el input, validar
 * sesión/CSRF, llamar a la función, y responder. Esto permite que
 * scripts/test-wild-battles.php pruebe exactamente la misma lógica sin
 * pasar por HTTP (ítem 10 de ROADMAP-ARQUITECTURA.md).
 *
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
    $resultado = resolve_wild_battle_action(db(), $userId, $battleId, $action);
    if (!$resultado['ok']) {
        respond(['error' => $resultado['error']], $resultado['httpStatus'] ?? 400);
    }
    respond($resultado);
} catch (PDOException $e) {
    error_log('wild_battle_action.php: ' . $e->getMessage());
    respond(['error' => 'Error de base de datos'], 500);
}
