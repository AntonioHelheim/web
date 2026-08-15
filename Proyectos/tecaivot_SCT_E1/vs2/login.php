<?php
/**
 * login.php
 * Recibe { email, password } por POST (JSON) desde el modal de login,
 * verifica contra la tabla `users` (id_users = identificador, rut = contraseña)
 * y responde en JSON.
 *
 * IMPORTANTE: ajusta el nombre real de la columna que guarda el correo
 * si "id_users" no es el correo, sino un ID/username distinto.
 */

session_start();
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

$email    = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Correo y contraseña son obligatorios.']);
    exit;
}

// Rate limiting básico por IP (opcional pero recomendable en producción real)

try {
    // Ajusta "id_users" si esa columna no corresponde al correo electrónico
    $stmt = $pdo->prepare(
        'SELECT * FROM users WHERE id_users = :identifier LIMIT 1'
    );
    $stmt->execute(['identifier' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos.'
        ]);
        exit;
    }

    // --- Verificación actual: comparación directa contra el campo "rut" ---
    // ADVERTENCIA: esto asume que la contraseña se guarda en texto plano
    // igual al RUT. No es seguro a largo plazo (ver recomendación de migración
    // a password_hash / password_verify más abajo).
    $passwordValida = hash_equals((string) $user['rut'], $password);

    if (!$passwordValida) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos.'
        ]);
        exit;
    }

    // Login correcto: crear sesión
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id_users'];
    $_SESSION['user_email'] = $email;
    $_SESSION['logged_in']  = true;

    echo json_encode([
        'success'  => true,
        'message'  => 'Inicio de sesión exitoso.',
        'redirect' => 'bienvenida.php'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al verificar el usuario.']);
}

/*
 * ---------------------------------------------------------------
 * MIGRACIÓN RECOMENDADA A FUTURO (contraseñas hasheadas):
 *
 * 1. Añadir columna `password_hash` a la tabla `users`.
 * 2. Al migrar cada usuario:
 *    UPDATE users SET password_hash = ? WHERE id_users = ?
 *    -- generado con password_hash($rutActual, PASSWORD_DEFAULT) en PHP
 * 3. Reemplazar la verificación por:
 *    $passwordValida = password_verify($password, $user['password_hash']);
 * ---------------------------------------------------------------
 */