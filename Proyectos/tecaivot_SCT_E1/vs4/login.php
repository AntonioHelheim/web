<?php
/**
 * login.php
 * Recibe { email, password, csrf_token } por POST (JSON) desde el modal de login,
 * verifica contra la tabla `users` y responde en JSON.
 *
 * Seguridad implementada:
 * - Validación de token CSRF contra el guardado en sesión.
 * - Verificación con password_hash/password_verify, con migración
 *   automática y transparente desde el esquema viejo (rut en texto plano).
 * - Rate limiting: bloqueo temporal tras varios intentos fallidos.
 * - Cookie de sesión endurecida (httponly, secure, samesite=Strict).
 * - Validación de formato de correo.
 * - Tiempo de respuesta equivalente exista o no el usuario.
 */

// --- Config de rate limiting ---
const MAX_INTENTOS       = 5;   // intentos fallidos permitidos
const VENTANA_MINUTOS    = 15;  // ventana de tiempo para contar intentos
const BLOQUEO_MINUTOS    = 15;  // minutos de bloqueo tras exceder el límite

require __DIR__ . '/session_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/config.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Leer body JSON (o form-urlencoded como fallback)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$email      = trim($input['email'] ?? '');
$password   = trim($input['password'] ?? '');
$csrfToken  = (string) ($input['csrf_token'] ?? '');
$ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// --- Validación de token CSRF ---
// Debe existir un token en sesión (generado al mostrar el modal) y coincidir
// exactamente con el enviado por el formulario. hash_equals evita timing attacks.
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.'
    ]);
    exit;
}

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Correo y contraseña son obligatorios.']);
    exit;
}

// Validar formato de correo antes de tocar la base de datos
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Correo electrónico no válido.']);
    exit;
}

/**
 * Registra un intento de login (exitoso o fallido) para efectos de rate limiting.
 */
function registrarIntento(PDO $pdo, string $identifier, string $ip, bool $success): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO login_attempts (identifier, ip_address, success) VALUES (:identifier, :ip, :success)'
    );
    $stmt->execute([
        'identifier' => $identifier,
        'ip'         => $ip,
        'success'    => $success ? 1 : 0,
    ]);
}

/**
 * Cuenta los intentos fallidos recientes para un correo o IP dados.
 */
function intentosFallidosRecientes(PDO $pdo, string $identifier, string $ip, int $minutos): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE success = 0
           AND created_at >= (NOW() - INTERVAL :minutos MINUTE)
           AND (identifier = :identifier OR ip_address = :ip)'
    );
    $stmt->execute([
        'minutos'    => $minutos,
        'identifier' => $identifier,
        'ip'         => $ip,
    ]);
    return (int) $stmt->fetchColumn();
}

try {
    // --- 1. Verificar si está bloqueado por demasiados intentos fallidos ---
    $intentosFallidos = intentosFallidosRecientes($pdo, $email, $ip, VENTANA_MINUTOS);

    if ($intentosFallidos >= MAX_INTENTOS) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => "Demasiados intentos fallidos. Intenta nuevamente en unos " . BLOQUEO_MINUTOS . " minutos."
        ]);
        exit;
    }

    // --- 2. Buscar usuario ---
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id_users = :identifier LIMIT 1');
    $stmt->execute(['identifier' => $email]);
    $user = $stmt->fetch();

    // Hash "señuelo" para que la verificación tarde lo mismo aunque el usuario no exista
    // (evita que el tiempo de respuesta revele si un correo está registrado).
    static $dummyHash = '$2y$10$abcdefghijklmnopqrstuuVQjV1n0z0e0e0e0e0e0e0e0e0e0e0e';

    $passwordValida = false;
    $necesitaMigrarHash = false;

    if ($user) {
        if (!empty($user['password_hash'])) {
            // Camino nuevo: ya tiene hash guardado
            $passwordValida = password_verify($password, $user['password_hash']);
        } else {
            // Camino de transición: aún no se ha migrado, compara contra rut
            $passwordValida = hash_equals((string) $user['rut'], $password);
            $necesitaMigrarHash = $passwordValida; // solo migramos si el login fue correcto
        }
    } else {
        // Usuario no existe: igualamos el costo de tiempo verificando contra un hash señuelo
        password_verify($password, $dummyHash);
    }

    if (!$user || !$passwordValida) {
        registrarIntento($pdo, $email, $ip, false);
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos.'
        ]);
        exit;
    }

    // --- 3. Migración transparente a password_hash ---
    if ($necesitaMigrarHash) {
        $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id_users = :identifier');
        $update->execute([
            'hash'       => $nuevoHash,
            'identifier' => $user['id_users'],
        ]);
    }

    // --- 4. Login correcto: registrar y crear sesión ---
    registrarIntento($pdo, $email, $ip, true);

    session_regenerate_id(true);
    $_SESSION['user_id']       = $user['id_users'];
    $_SESSION['user_email']    = $email;
    $_SESSION['logged_in']     = true;
    $_SESSION['last_activity'] = time();

    // Registrar ultimo acceso real sin bloquear el login si este update falla.
    try {
        $lastAccessStmt = $pdo->prepare('UPDATE users SET last_access = NOW(), last_update = NOW() WHERE id_users = :identifier LIMIT 1');
        $lastAccessStmt->execute(['identifier' => $user['id_users']]);
    } catch (PDOException $e) {
        error_log('login.php: no se pudo actualizar last_access para ' . $user['id_users'] . ' - ' . $e->getMessage());
    }

    // Se regenera el token CSRF tras un login exitoso para evitar su reutilización.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    echo json_encode([
        'success'  => true,
        'message'  => 'Inicio de sesión exitoso.',
        'redirect' => 'bienvenida.php'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al verificar el usuario.']);
}