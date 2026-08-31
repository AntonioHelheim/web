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
const FORZAR_ENTORNO_LOCAL = null;

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
        define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
        define('DB_PORT', (int) (getenv('DB_PORT') ?: '3306'));
        define('DB_NAME', getenv('DB_NAME') ?: 'bifrost');
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
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_PORT', (int) (getenv('DB_PORT') ?: '3306'));
        define('DB_NAME', getenv('DB_NAME') ?: 'TU_USUARIO_bifrost');
        define('DB_USER', getenv('DB_USER') ?: 'TU_USUARIO_bifrost');
        define('DB_PASS', getenv('DB_PASS') ?: 'TU_CONTRASEÑA_AQUI');
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
   6. CATÁLOGO DE ESPECIES (para batallas PvP en el servidor)
   ========================================================= */

// Catálogo de especies espejo de js/data.js. Vive también en PHP porque las
// batallas PvP se calculan en el servidor (autoridad única de daño/turnos),
// así que el servidor necesita poder generar/validar monstruos por su cuenta.
function species_catalog(): array
{
    return [
        // Fuego: inspirados en dragones
        'fire_1' => ['name' => 'Chispodrilo', 'color' => 0xe0703a, 'hp' => 22, 'atk' => 12, 'def' => 7],
        'fire_2' => ['name' => 'Braseryx', 'color' => 0xd94f2b, 'hp' => 28, 'atk' => 16, 'def' => 11],
        'fire_3' => ['name' => 'Vulcanor', 'color' => 0xb83214, 'hp' => 35, 'atk' => 21, 'def' => 15],
        // Agua: inspirados en serpientes marinas
        'water_1' => ['name' => 'Marejino', 'color' => 0x5ea8c9, 'hp' => 24, 'atk' => 9, 'def' => 10],
        'water_2' => ['name' => 'Corrientauro', 'color' => 0x3a6ea5, 'hp' => 30, 'atk' => 13, 'def' => 14],
        'water_3' => ['name' => 'Abisalgo', 'color' => 0x1f4e79, 'hp' => 38, 'atk' => 17, 'def' => 19],
        // Planta: inspirados en insectos
        'grass_1' => ['name' => 'Brotalín', 'color' => 0x7cb342, 'hp' => 23, 'atk' => 10, 'def' => 9],
        'grass_2' => ['name' => 'Espigón', 'color' => 0x4c9a2a, 'hp' => 29, 'atk' => 14, 'def' => 13],
        'grass_3' => ['name' => 'Follascorpio', 'color' => 0x2e6b1f, 'hp' => 36, 'atk' => 18, 'def' => 17],
        // Electricidad: inspirados en equidnas
        'electric_1' => ['name' => 'Chispequín', 'color' => 0xe8c268, 'hp' => 21, 'atk' => 13, 'def' => 6],
        'electric_2' => ['name' => 'Voltígero', 'color' => 0xd4a017, 'hp' => 27, 'atk' => 17, 'def' => 10],
        'electric_3' => ['name' => 'Amperidna', 'color' => 0xb8860b, 'hp' => 33, 'atk' => 22, 'def' => 13],
        // Lucha: inspirados en artes marciales
        'fighting_1' => ['name' => 'Puñolet', 'color' => 0xa0522d, 'hp' => 24, 'atk' => 13, 'def' => 8],
        'fighting_2' => ['name' => 'Katáfaro', 'color' => 0x8b3a1a, 'hp' => 30, 'atk' => 17, 'def' => 12],
        'fighting_3' => ['name' => 'Granmaestro', 'color' => 0x6b2c12, 'hp' => 37, 'atk' => 22, 'def' => 16],
        // Volador: inspirados en aves
        'flying_1' => ['name' => 'Plumín', 'color' => 0xa8d8e8, 'hp' => 20, 'atk' => 11, 'def' => 7],
        'flying_2' => ['name' => 'Ventizarro', 'color' => 0x7ec4dd, 'hp' => 26, 'atk' => 15, 'def' => 10],
        'flying_3' => ['name' => 'Tormenpluma', 'color' => 0x4a90b8, 'hp' => 32, 'atk' => 19, 'def' => 13],
        // Oscuro: inspirados en gatos
        'dark_1' => ['name' => 'Sombrigato', 'color' => 0x5b4b8a, 'hp' => 22, 'atk' => 12, 'def' => 8],
        'dark_2' => ['name' => 'Penumbraz', 'color' => 0x3d2f5c, 'hp' => 28, 'atk' => 16, 'def' => 11],
        'dark_3' => ['name' => 'Eclipsino', 'color' => 0x241b38, 'hp' => 35, 'atk' => 20, 'def' => 15],
        // Diurno: inspirados en perros
        'day_1' => ['name' => 'Solete', 'color' => 0xf2c14e, 'hp' => 23, 'atk' => 11, 'def' => 9],
        'day_2' => ['name' => 'Auroraz', 'color' => 0xe8a33d, 'hp' => 29, 'atk' => 15, 'def' => 12],
        'day_3' => ['name' => 'Radialbo', 'color' => 0xd4822a, 'hp' => 36, 'atk' => 19, 'def' => 16],
    ];
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
   7. REGLAS DE COMBATE (espejo en PHP de js/core/battleRules.js)
   ========================================================= */

// Misma fórmula que calculateDamage() en js/core/battleRules.js: ataque -
// defensa/2 + variación aleatoria, mínimo 1. La usan tanto
// battle_action.php (PvP) como wild_battle_action.php (batallas
// silvestres) — antes cada endpoint tenía su propia copia de esta
// fórmula escrita a mano; ahora hay una sola función en el servidor. Si
// cambias esta fórmula, cambia también la de battleRules.js (ítem 4 del
// roadmap: unificar esto en una sola fuente de datos para JS y PHP).
function calculate_damage(array $attacker, array $defender): int
{
    $raw = $attacker['atk'] - $defender['def'] / 2;
    $variance = random_int(-2, 2);
    return max(1, (int) round($raw + $variance));
}

// HP con el que un monstruo vuelve tras un desmayo (30% de su HP máximo,
// redondeado hacia arriba) — mismo valor que FAINT_RECOVERY_FRACTION en
// js/core/battleRules.js.
function faint_recovery_hp(array $monster): int
{
    return (int) ceil($monster['maxHp'] * 0.3);
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
