<?php
/**
 * scripts/test-wild-battles.php
 *
 * Prueba resolve_wild_battle_action() (api/config.php) de principio a
 * fin — ganar, perder con recuperación correcta, huir con la tasa
 * esperada, y las dos protecciones (no reusar una batalla terminada, no
 * poder actuar sobre la batalla de otro usuario) — contra una base de
 * datos SQLite en memoria con la misma forma de tablas que usa MySQL en
 * producción.
 *
 * Antes esto se probaba a mano con arneses temporales durante el
 * desarrollo de los ítems 3-5 del roadmap y se borraban al terminar; este
 * script los reemplaza como prueba permanente y reutilizable (ítem 10 de
 * ROADMAP-ARQUITECTURA.md).
 *
 * Importante: requiere el config.php REAL (no una copia ni un fragmento
 * extraído con eval()) para que __DIR__ resuelva exactamente igual que en
 * producción — con eval() de un fragmento, __DIR__ apunta al archivo que
 * llama a eval(), no a api/, y las pruebas pueden pasar por la razón
 * equivocada sin darse cuenta (pasó una vez durante el desarrollo).
 *
 * Uso: php scripts/test-wild-battles.php
 */
declare(strict_types=1);
require __DIR__ . '/../api/config.php';

$fallas = 0;
function verificar(bool $cond, string $msg): void
{
    global $fallas;
    if ($cond) {
        echo "OK: $msg\n";
    } else {
        echo "FALLA: $msg\n";
        $fallas++;
    }
}

function nuevaBaseDePrueba(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('CREATE TABLE saves (user_id INTEGER PRIMARY KEY, party_json TEXT)');
    $pdo->exec(
        'CREATE TABLE wild_battles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER, player_mon_json TEXT, enemy_mon_json TEXT,
            last_action TEXT, status TEXT DEFAULT "active", outcome TEXT
        )'
    );
    return $pdo;
}

function crearBatalla(PDO $pdo, int $userId, array $playerMon, array $enemyMon): int
{
    $pdo->prepare('INSERT OR REPLACE INTO saves (user_id, party_json) VALUES (?, ?)')
        ->execute([$userId, json_encode([$playerMon])]);
    $pdo->prepare('INSERT INTO wild_battles (user_id, player_mon_json, enemy_mon_json, last_action) VALUES (?, ?, ?, ?)')
        ->execute([$userId, json_encode($playerMon), json_encode($enemyMon), '...']);
    return (int) $pdo->lastInsertId();
}

echo "Ruta real de data/battle-rules.json (confirma que __DIR__ resuelve bien): " . (__DIR__ . '/../data/battle-rules.json') . "\n";
echo "¿Existe?: " . (file_exists(__DIR__ . '/../data/battle-rules.json') ? 'sí' : 'NO — algo está mal') . "\n\n";

// --- Escenario 1: jugador mucho más fuerte -> debería ganar ---
$pdo = nuevaBaseDePrueba();
$fuerte = ['name' => 'Granmaestro', 'maxHp' => 37, 'hp' => 37, 'atk' => 22, 'def' => 16];
$debil = ['name' => 'Plumín', 'maxHp' => 20, 'hp' => 20, 'atk' => 11, 'def' => 7];
$battleId = crearBatalla($pdo, 1, $fuerte, $debil);
$r = null;
for ($i = 0; $i < 10 && (!$r || $r['status'] === 'active'); $i++) {
    $r = resolve_wild_battle_action($pdo, 1, $battleId, 'attack');
}
verificar($r['outcome'] === 'win', 'jugador mucho más fuerte termina ganando la batalla');

// --- Escenario 2: jugador débil -> debería perder, con recuperación correcta ---
$pdo = nuevaBaseDePrueba();
$debil2 = ['name' => 'Chispodrilo', 'maxHp' => 22, 'hp' => 22, 'atk' => 12, 'def' => 7];
$fuerte2 = ['name' => 'Abisalgo', 'maxHp' => 38, 'hp' => 38, 'atk' => 30, 'def' => 19];
$battleId2 = crearBatalla($pdo, 2, $debil2, $fuerte2);
$r2 = null;
for ($i = 0; $i < 10 && (!$r2 || $r2['status'] === 'active'); $i++) {
    $r2 = resolve_wild_battle_action($pdo, 2, $battleId2, 'attack');
}
$reglas = battle_rules();
$recuperacionEsperada = (int) ceil(22 * $reglas['faintRecoveryFraction']);
verificar($r2['outcome'] === 'lose', 'jugador débil termina perdiendo la batalla');
verificar($r2['you']['hp'] === $recuperacionEsperada, "recuperación tras desmayo coincide con data/battle-rules.json real ({$reglas['faintRecoveryFraction']} de 22 = {$recuperacionEsperada}, salió {$r2['you']['hp']})");

// Verificar que el HP quedó persistido en saves.party_json de inmediato,
// no solo en la respuesta (esto es lo que evita perder progreso si el
// jugador cierra el navegador a mitad de la batalla).
$row = $pdo->prepare('SELECT party_json FROM saves WHERE user_id = ?');
$row->execute([2]);
$partyGuardado = json_decode($row->fetch()['party_json'], true);
verificar($partyGuardado[0]['hp'] === $r2['you']['hp'], 'el HP final quedó persistido en saves.party_json (no solo en la respuesta)');

// --- Escenario 3: huir repetidamente -> tasa cercana a escapeChance del JSON real ---
$pdo = nuevaBaseDePrueba();
$escapes = 0;
$intentos = 150;
for ($i = 0; $i < $intentos; $i++) {
    $mon = ['name' => 'Test', 'maxHp' => 100, 'hp' => 100, 'atk' => 10, 'def' => 10];
    $enemy = ['name' => 'Rival', 'maxHp' => 100, 'hp' => 100, 'atk' => 1, 'def' => 100];
    $bId = crearBatalla($pdo, 100 + $i, $mon, $enemy);
    $r3 = resolve_wild_battle_action($pdo, 100 + $i, $bId, 'run');
    if ($r3['status'] === 'finished' && $r3['outcome'] === 'flee') {
        $escapes++;
    }
}
$tasaEsperada = $reglas['escapeChance'] * $intentos;
echo "\nEscapes: {$escapes}/{$intentos} (esperado ~{$tasaEsperada} según escapeChance={$reglas['escapeChance']})\n";
verificar(abs($escapes - $tasaEsperada) < 25, 'la tasa de escape ronda el valor real de data/battle-rules.json (tolerancia amplia, es aleatorio)');

// --- Protecciones ---
$pdo = nuevaBaseDePrueba();
$mon = ['name' => 'Test', 'maxHp' => 20, 'hp' => 20, 'atk' => 5, 'def' => 5];
$enemy = ['name' => 'Rival', 'maxHp' => 5, 'hp' => 5, 'atk' => 1, 'def' => 1];
$bId = crearBatalla($pdo, 5, $mon, $enemy);
$rFinal = null;
for ($i = 0; $i < 10 && (!$rFinal || $rFinal['status'] === 'active'); $i++) {
    $rFinal = resolve_wild_battle_action($pdo, 5, $bId, 'attack');
}
$rRepetido = resolve_wild_battle_action($pdo, 5, $bId, 'attack');
verificar(!$rRepetido['ok'], 'una batalla ya terminada no se puede volver a usar');

$bId2 = crearBatalla($pdo, 6, $mon, $enemy);
$rOtroUsuario = resolve_wild_battle_action($pdo, 999, $bId2, 'attack');
verificar(!$rOtroUsuario['ok'], 'un usuario distinto no puede actuar sobre la batalla de otro jugador');

echo "\n" . ($fallas === 0 ? 'TODAS LAS PRUEBAS PASARON' : "{$fallas} PRUEBA(S) FALLARON") . "\n";
exit($fallas === 0 ? 0 : 1);
