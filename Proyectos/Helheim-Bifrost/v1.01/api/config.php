<?php
declare(strict_types=1);

/* =========================================================
   1. SESIÓN (cookies seguras, CSRF, cierre por inactividad)
   ========================================================= */

require __DIR__ . '/../session_bootstrap.php';

// Todas las respuestas de la API son JSON (las páginas HTML, en cambio,
// llaman aplicarCabecerasSeguridad() en vez de esto).
header('Content-Type: application/json; charset=utf-8');

/* =========================================================
   2. ENTORNO DE EJECUCIÓN (local vs. hosting)
   ========================================================= */

/* =========================================================
   2. ENTORNO DE EJECUCIÓN (local vs. hosting)
   ========================================================= */

/**
 * ¿La detección automática de abajo no acierta en tu máquina (por
 * ejemplo, un puerto o proxy poco común que hace que el hostname no
 * llegue como se espera)? Pon aquí `true` (local) o `false` (hosting) en
 * vez de `null`, y se usará ese valor siempre, sin importar nada más.
 * Vuelve a dejarlo en `null` para que la detección automática decida.
 */

const FORZAR_ENTORNO_LOCAL = null; // normalmente se deja en null para que la detección automática decida, pero si tu entorno local es poco común (proxys, puertos no estándar, etc.) puedes forzar true/false acá.
//const FORZAR_ENTORNO_LOCAL = false; //Esto forzará al sistema a usar las credenciales del hosting en TODO momento, sin importar cómo se detecte el entorno.

/**
 * Determina si estamos en un entorno local de desarrollo.
 *
 * Orden de prioridad:
 *   0) Si FORZAR_ENTORNO_LOCAL está en true/false (no null), manda eso
 *      siempre — es el respaldo garantizado para configuraciones locales
 *      poco comunes (proxys, puertos no estándar, etc.).
 *   1) Si APP_ENV está definida explícitamente y no vacía (variable de
 *      entorno real, ej. seteada en el vhost de Apache), manda ella.
 *   2) Si no, se detecta por el host con el que se accedió (localhost,
 *      127.0.0.1, o dominios típicos de desarrollo local como *.test /
 *      *.local, con o sin puerto). Así nadie tiene que acordarse de
 *      configurar nada en su máquina para que ande, y el servidor real
 *      (con otro host) nunca cae en modo local por descuido.
 *   3) Si por algún motivo el hostname no ayuda (proxys locales, nombres
 *      poco comunes), se revisa también si quien se conecta es la misma
 *      máquina donde corre el servidor (REMOTE_ADDR 127.0.0.1/::1) —
 *      típico de cualquier entorno de desarrollo local.
 *   4) En CLI (sin HTTP_HOST, ej. scripts de mantenimiento), se asume NO
 *      local salvo que APP_ENV o FORZAR_ENTORNO_LOCAL lo digan explícitamente.
 */
function detectarEntornoLocal(): bool
{
    if (FORZAR_ENTORNO_LOCAL !== null) {
        return FORZAR_ENTORNO_LOCAL;
    }

    $appEnv = getenv('APP_ENV');
    // OJO: getenv() devuelve false si la variable no existe, pero si
    // existiera vacía ("") seguiría siendo distinta de false — sin este
    // segundo chequeo, un APP_ENV vacío por accidente bloqueaba para
    // siempre la detección automática por hostname de más abajo.
    if ($appEnv !== false && $appEnv !== '') {
        return $appEnv === 'local';
    }

    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    $host = strtolower(explode(':', $host)[0]); // quita el puerto (ej. localhost:3000 -> localhost)

    if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        return true;
    }

    if (preg_match('/\.(test|local)$/', $host)) {
        return true;
    }

    // Señal adicional: si quien se conecta es la misma máquina donde
    // corre el servidor, es un entorno de desarrollo local aunque el
    // hostname/puerto no haya calzado con los casos de arriba.
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
    if (in_array($remoteAddr, ['127.0.0.1', '::1'], true)) {
        return true;
    }

    return false;
}

$isLocal = detectarEntornoLocal();

/* =========================================================
   3. CREDENCIALES DE BASE DE DATOS POR ENTORNO
   ========================================================= */

if ($isLocal) {
    // Desarrollo en tu máquina (XAMPP + phpMyAdmin).
    if (!defined('DB_HOST')) {
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
//        define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
        define('DB_PORT', (int) (getenv('DB_PORT') ?: '3306'));
        define('DB_NAME', getenv('DB_NAME') ?: 'helheim1_bifrost');
        define('DB_USER', getenv('DB_USER') ?: 'root');
        define('DB_PASS', getenv('DB_PASS') ?: '');
    }
} else {
    // Hosting. ⚠️ Reemplaza estos valores por los reales de tu cPanel
    // ("Bases de datos MySQL"); ahí normalmente vienen con prefijo, ej.
    // usuario_bifrost. Si prefieres no tocar el código, puedes setearlos
    // como variables de entorno del servidor (DB_HOST/DB_NAME/DB_USER/DB_PASS)
    // en vez de escribirlos aquí.
    if (!defined('DB_HOST')) {
        //define('DB_HOST', getenv('DB_HOST') ?: '186.64.114.120');
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_PORT', (int) (getenv('DB_PORT') ?: '3306'));
        define('DB_NAME', getenv('DB_NAME') ?: 'helheim1_bifrost');
        define('DB_USER', getenv('DB_USER') ?: 'helheim1');
        define('DB_PASS', getenv('DB_PASS') ?: '0IG]zc3pTo7Y3!');
    }
}

/* =========================================================
   3.1. VALIDACIÓN DE CONFIGURACIÓN
   ========================================================= */

if (!DB_HOST || !DB_NAME || !DB_USER || DB_PASS === false) {
    error_log('config.php: faltan variables de configuración de la base de datos.');
    respond(['error' => 'Configuración de base de datos incompleta.'], 500);
}

/* =========================================================
   4. CONEXIÓN
   ========================================================= */

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('config.php: error de conexión a la base de datos - ' . $e->getMessage());
            respond(['error' => 'Error de conexión a la base de datos.'], 500);
        }
    }
    return $pdo;
}

/* =========================================================
   5. HELPERS COMPARTIDOS POR TODA LA API
   ========================================================= */

function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        // Si el body no vino como JSON válido, probamos con datos de
        // formulario normales (application/x-www-form-urlencoded) —
        // mismo respaldo que usa el patrón de referencia.
        $data = $_POST;
    }
    return is_array($data) ? $data : [];
}

function respond(array $payload, int $status = 200): void
{
    // Encabezados explícitos de no-caché — PHP los manda automáticamente
    // por defecto (session.cache_limiter), pero eso depende de la
    // configuración de cada servidor. Se ponen a mano acá para que el
    // comportamiento sea el mismo sin importar el hosting: las
    // respuestas de la API (datos de la partida, apariencia, etc.)
    // nunca deben quedar en caché del navegador.
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        respond(['error' => 'No has iniciado sesión'], 401);
    }
    return (int) $_SESSION['user_id'];
}

/**
 * Verifica el token CSRF que debe venir en el body de cada POST sensible
 * (login, registro). Corta la ejecución con un 403 si no coincide.
 */
function require_csrf(string $token): void
{
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        respond(['error' => 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.'], 403);
    }
}

/* =========================================================
   6. CATÁLOGO DE ESPECIES (para batallas PvP y silvestres en el servidor)
   ========================================================= */

// Lee data/species.json — única fuente de datos (ítem 4 de
// ROADMAP-ARQUITECTURA.md), la misma que carga js/scenes/PreloadScene.js
// en el navegador. Antes había un array de 24 criaturas escrito a mano
// acá, duplicado del catálogo real en js/data.js — ahora ambos leen del
// mismo archivo, así que no se pueden desincronizar por accidente.
function species_catalog(): array
{
    static $catalogo = null;
    if ($catalogo === null) {
        $ruta = __DIR__ . '/../data/species.json';
        $leido = json_decode((string) @file_get_contents($ruta), true);
        $catalogo = is_array($leido) ? $leido : [];
    }
    return $catalogo;
}

function random_monster(): array
{
    $catalog = species_catalog();
    $keys = array_keys($catalog);
    $key = $keys[array_rand($keys)];
    $base = $catalog[$key];
    return [
        'speciesKey' => $key,
        'name' => $base['name'],
        'color' => $base['color'],
        'maxHp' => $base['hp'],
        'hp' => $base['hp'],
        'atk' => $base['atk'],
        'def' => $base['def'],
    ];
}

// Usa el primer monstruo del equipo guardado del jugador si tiene uno con
// vida; si no, genera uno al azar. La copia usada en la batalla PvP es un
// snapshot independiente: perder un duelo no daña tu equipo guardado.
function monster_for_user(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT party_json FROM saves WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if ($row) {
        $party = json_decode($row['party_json'], true);
        if (is_array($party) && count($party) > 0 && ($party[0]['hp'] ?? 0) > 0) {
            return $party[0];
        }
    }
    return random_monster();
}

/* =========================================================
   7. REGLAS DE COMBATE (leídas de data/battle-rules.json — ítem 4 de
      ROADMAP-ARQUITECTURA.md: única fuente de datos, compartida con
      js/core/battleRules.js. Ya no hay valores hardcodeados por
      separado en cada lado.)
   ========================================================= */

function battle_rules(): array
{
    static $reglas = null;
    if ($reglas === null) {
        $ruta = __DIR__ . '/../data/battle-rules.json';
        $leido = json_decode((string) @file_get_contents($ruta), true);
        // Si el archivo no se puede leer por algún motivo, se usan estos
        // valores por defecto (deberían coincidir siempre con el JSON) en
        // vez de que el juego quede sin poder calcular daño.
        $reglas = is_array($leido) ? $leido : [
            'damageVarianceMin' => -2,
            'damageVarianceMax' => 2,
            'escapeChance' => 0.9,
            'faintRecoveryFraction' => 0.3,
        ];
    }
    return $reglas;
}

// Fórmula: ataque - defensa/2 + variación aleatoria, mínimo 1. La usan
// tanto battle_action.php (PvP) como wild_battle_action.php (batallas
// silvestres) — antes cada endpoint tenía su propia copia de esta
// fórmula escrita a mano; ahora hay una sola función en el servidor, que
// además lee sus constantes de la misma fuente que el espejo en
// js/core/battleRules.js.
function calculate_damage(array $attacker, array $defender): int
{
    $r = battle_rules();
    $raw = $attacker['atk'] - $defender['def'] / 2;
    $variance = random_int((int) $r['damageVarianceMin'], (int) $r['damageVarianceMax']);
    return max(1, (int) round($raw + $variance));
}

// Intento de huir de una batalla silvestre (no aplica a PvP — ahí "huir"
// siempre funciona, ver battle_action.php).
function attempt_escape(): bool
{
    $r = battle_rules();
    $porcentaje = (int) round(((float) $r['escapeChance']) * 100);
    return random_int(1, 100) <= $porcentaje;
}

// HP con el que un monstruo vuelve tras un desmayo.
function faint_recovery_hp(array $monster): int
{
    $r = battle_rules();
    return (int) ceil($monster['maxHp'] * (float) $r['faintRecoveryFraction']);
}

// Guarda el HP actual del primer monstruo del equipo en saves.party_json,
// sin tocar el resto del equipo. Se llama después de cada acción de una
// batalla silvestre para que el progreso (o el daño recibido) quede
// guardado de inmediato, no solo cuando el jugador presiona "S" — así el
// servidor siempre tiene el HP real como fuente de verdad, sin tener que
// confiar en lo que mande el cliente.
function persist_party_first_hp(PDO $pdo, int $userId, int $hp): void
{
    $stmt = $pdo->prepare('SELECT party_json FROM saves WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row) {
        return;
    }
    $party = json_decode($row['party_json'], true);
    if (!is_array($party) || count($party) === 0) {
        return;
    }
    $party[0]['hp'] = $hp;
    $pdo->prepare('UPDATE saves SET party_json = ? WHERE user_id = ?')
        ->execute([json_encode($party), $userId]);
}

/**
 * Resuelve una acción ("attack" o "run") de una batalla silvestre activa
 * y devuelve el resultado — NO llama respond() ni exit() acá adentro, a
 * diferencia del patrón que usan los endpoints. Esto es a propósito: así
 * la misma función sirve tanto para el endpoint real
 * (api/wild_battle_action.php, que solo llama esto y responde con el
 * resultado) como para scripts/test-wild-battles.php (que la prueba
 * contra una base de datos SQLite en memoria) — ítem 10 de
 * ROADMAP-ARQUITECTURA.md. Antes esta lógica vivía escrita una sola vez
 * dentro del endpoint, y las pruebas la duplicaban a mano en un archivo
 * aparte; ahora hay una sola copia que ambos usan.
 *
 * Devuelve ['ok' => false, 'error' => ..., 'httpStatus' => ...] si algo
 * salió mal (batalla no encontrada, etc.), o el resultado normal de la
 * ronda si todo salió bien (con 'status' => 'active'|'finished').
 */
function resolve_wild_battle_action(PDO $pdo, int $userId, int $battleId, string $action): array
{
    $stmt = $pdo->prepare('SELECT * FROM wild_battles WHERE id = ? AND user_id = ? AND status = "active"');
    $stmt->execute([$battleId, $userId]);
    $battle = $stmt->fetch();
    if (!$battle) {
        return ['ok' => false, 'error' => 'Batalla no encontrada o ya terminó', 'httpStatus' => 404];
    }

    $you = json_decode($battle['player_mon_json'], true);
    $enemy = json_decode($battle['enemy_mon_json'], true);

    // Termina la batalla: aplica recuperación tras desmayo si corresponde,
    // persiste el HP final en el equipo guardado, y devuelve el resultado.
    $finalizar = function (string $outcome, string $mensaje) use ($pdo, $battleId, $userId, &$you, &$enemy): array {
        if ($outcome === 'lose') {
            $you['hp'] = faint_recovery_hp($you);
        }
        persist_party_first_hp($pdo, $userId, (int) $you['hp']);
        $pdo->prepare(
            'UPDATE wild_battles SET status = "finished", outcome = ?, player_mon_json = ?, enemy_mon_json = ?, last_action = ? WHERE id = ?'
        )->execute([$outcome, json_encode($you), json_encode($enemy), $mensaje, $battleId]);
        return ['ok' => true, 'status' => 'finished', 'outcome' => $outcome, 'you' => $you, 'enemy' => $enemy, 'message' => $mensaje];
    };

    // Contraataque del enemigo — se reutiliza tanto si el jugador atacó y
    // no derrotó al rival, como si intentó huir y no lo logró. Prefija el
    // mensaje del ataque del jugador (si hubo uno) para narrar la ronda
    // completa en un solo mensaje.
    $contraataqueEnemigo = function (string $mensajePrevio = '') use (&$you, &$enemy, $finalizar, $pdo, $battleId, $userId): array {
        $dmg = calculate_damage($enemy, $you);
        $you['hp'] = max(0, $you['hp'] - $dmg);
        $mensaje = trim($mensajePrevio . " {$enemy['name']} contraataca. {$dmg} de daño.");

        if ($you['hp'] <= 0) {
            return $finalizar('lose', "{$mensaje} {$you['name']} no puede continuar...");
        }

        persist_party_first_hp($pdo, $userId, (int) $you['hp']);
        $pdo->prepare('UPDATE wild_battles SET player_mon_json = ?, enemy_mon_json = ?, last_action = ? WHERE id = ?')
            ->execute([json_encode($you), json_encode($enemy), $mensaje, $battleId]);
        return ['ok' => true, 'status' => 'active', 'you' => $you, 'enemy' => $enemy, 'message' => $mensaje];
    };

    if ($action === 'run') {
        if (attempt_escape()) {
            return $finalizar('flee', 'Escapaste con éxito.');
        }
        return $contraataqueEnemigo('¡No pudiste escapar!');
    }

    // action === 'attack'
    $dmg = calculate_damage($you, $enemy);
    $enemy['hp'] = max(0, $enemy['hp'] - $dmg);
    $mensajeAtaque = "{$you['name']} ataca. {$dmg} de daño.";

    if ($enemy['hp'] <= 0) {
        return $finalizar('win', "{$mensajeAtaque} ¡{$enemy['name']} fue derrotado!");
    }

    return $contraataqueEnemigo($mensajeAtaque);
}

// Límite de retos PvP: máximo por minuto, para que no se pueda usar
// challenge_send.php para hostigar a otros jugadores con retos en cadena.
// Función aparte (en vez de la consulta escrita directo en el endpoint)
// para que scripts/test-challenge-rate-limit.php la pueda probar tal
// cual, sin duplicar la consulta a mano — mismo motivo que
// resolve_wild_battle_action() más arriba (ítem 10 del roadmap).
const MAX_RETOS_POR_MINUTO = 10;

function count_recent_challenges(PDO $pdo, int $userId): int
{
    // SQLite (usado en las pruebas, ver scripts/) no entiende la sintaxis
    // de fechas de MySQL (usado en producción) — se adapta según el
    // driver activo para que la misma función sirva en ambos casos.
    $esSqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    $ventana = $esSqlite ? "datetime('now', '-1 minute')" : '(NOW() - INTERVAL 1 MINUTE)';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM battle_challenges WHERE from_user_id = ? AND created_at >= {$ventana}");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

/* =========================================================
   8. APARIENCIA DEL PERSONAJE (presets, espejo de
      APPEARANCE_PRESETS en js/entities/CharacterVisual.js)
   ========================================================= */

// Cambio de jugabilidad (31-08-2026): el jugador ya no elige colores
// libremente — elige una de 3 opciones preestablecidas por género. El
// cliente solo manda género + número de opción (1-3); el servidor resuelve
// los colores por su cuenta, igual que el resto de datos críticos del
// juego no se toman de lo que declare el cliente. Si cambias esto, cambia
// también APPEARANCE_PRESETS en CharacterVisual.js — misma tabla en los
// dos lados (ítem 4 del roadmap: sería buen candidato para unificar en
// data/ más adelante, igual que species.json y battle-rules.json).
function resolve_appearance_preset(string $gender, int $preset): ?array
{
    $tabla = [
        'boy' => [
            1 => ['skinColor' => '#f1c27d', 'hairColor' => '#2c1b18', 'eyeColor' => '#3b2415'],
            2 => ['skinColor' => '#c68642', 'hairColor' => '#4a2c1a', 'eyeColor' => '#5b4636'],
            3 => ['skinColor' => '#8d5524', 'hairColor' => '#1a1a1a', 'eyeColor' => '#2f6b3a'],
        ],
        'girl' => [
            1 => ['skinColor' => '#f1c27d', 'hairColor' => '#2c1b18', 'eyeColor' => '#3b2415'],
            2 => ['skinColor' => '#f1c27d', 'hairColor' => '#e8c268', 'eyeColor' => '#274b8f'],
            3 => ['skinColor' => '#8d5524', 'hairColor' => '#1a1a1a', 'eyeColor' => '#2f6b3a'],
        ],
    ];
    return $tabla[$gender][$preset] ?? null;
}
