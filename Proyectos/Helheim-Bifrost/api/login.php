<?php
/**
 * api/login.php
 *
 * Login sin contraseña en 2 pasos (mismo patrón usado en otros proyectos
 * propios, adaptado al esquema de Bifrost):
 *
 *   1. action=request_code   { email, csrf_token }
 *   2. action=verify_code    { email, code, csrf_token }
 *
 * LOCAL (XAMPP): no se envía correo — el código viaja en la respuesta
 * JSON (data.dev_code) para poder probar sin configurar un servidor de
 * correo. PRODUCCIÓN: se intenta enviar por correo con mail() y el
 * código nunca viaja en la respuesta.
 *
 * Seguridad: CSRF, límite de solicitudes por IP y por usuario, código de
 * un solo uso con expiración de 10 minutos, bloqueo tras varios intentos
 * fallidos, tiempos de respuesta parejos (no revela si un correo existe),
 * regeneración de ID de sesión al iniciar sesión.
 */
declare(strict_types=1);
require __DIR__ . '/config.php';

const CODIGO_LARGO = 6;
const CODIGO_VIGENCIA_MINUTOS = 10;
const MAX_SOLICITUDES_CODIGO_IP = 8;
const MAX_SOLICITUDES_CODIGO_USR = 3;
const VENTANA_SOLICITUD_MINUTOS = 15;
const MAX_INTENTOS_VERIFICACION = 5;
const VENTANA_VERIFICACION_MIN = 15;
const BLOQUEO_MINUTOS = 15;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$input = json_input();
$action = (string) ($input['action'] ?? '');
$email = trim((string) ($input['email'] ?? ''));
$code = trim((string) ($input['code'] ?? ''));
$csrfToken = (string) ($input['csrf_token'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

require_csrf($csrfToken);

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['error' => 'Correo electrónico no válido.'], 400);
}

function generarCodigo(): string
{
    return str_pad((string) random_int(0, 10 ** CODIGO_LARGO - 1), CODIGO_LARGO, '0', STR_PAD_LEFT);
}

function enviarCodigoPorCorreo(string $email, string $codigo): bool
{
    $asunto = 'Tu código de acceso — Bifrost';
    $cuerpo = "Tu código de acceso es: {$codigo}\n\n"
        . 'Este código vence en ' . CODIGO_VIGENCIA_MINUTOS . " minutos.\n"
        . 'Si no solicitaste este código, puedes ignorar este mensaje.';
    $cabeceras = "From: Bifrost <no-responder@bifrost.local>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    return @mail($email, $asunto, $cuerpo, $cabeceras);
}

function registrarIntentoVerificacion(PDO $pdo, string $identifier, string $ip, bool $success): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO login_attempts (identifier, ip_address, success) VALUES (?, ?, ?)'
    );
    $stmt->execute([$identifier, $ip, $success ? 1 : 0]);
}

function intentosVerificacionFallidosRecientes(PDO $pdo, string $identifier, string $ip, int $minutos): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE success = 0
           AND created_at >= (NOW() - INTERVAL :minutos MINUTE)
           AND (identifier = :identifier OR ip_address = :ip)'
    );
    $stmt->execute(['minutos' => $minutos, 'identifier' => $identifier, 'ip' => $ip]);
    return (int) $stmt->fetchColumn();
}

try {
    $pdo = db();

    /* ============ PASO 1: SOLICITAR CÓDIGO ============ */
    if ($action === 'request_code') {

        // Límite por IP.
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_codes
             WHERE ip_address = :ip AND created_at >= (NOW() - INTERVAL :minutos MINUTE)'
        );
        $stmt->execute(['ip' => $ip, 'minutos' => VENTANA_SOLICITUD_MINUTOS]);
        if ((int) $stmt->fetchColumn() >= MAX_SOLICITUDES_CODIGO_IP) {
            respond(['error' => 'Demasiadas solicitudes desde esta conexión. Intenta nuevamente en unos minutos.'], 429);
        }

        // Buscar usuario por correo (sin revelar si existe o no en la respuesta final).
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Límite por usuario.
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM login_codes
                 WHERE user_id = :uid AND created_at >= (NOW() - INTERVAL :minutos MINUTE)'
            );
            $stmt->execute(['uid' => $user['id'], 'minutos' => VENTANA_SOLICITUD_MINUTOS]);
            $solicitudesPorUsuario = (int) $stmt->fetchColumn();

            if ($solicitudesPorUsuario < MAX_SOLICITUDES_CODIGO_USR) {
                $codigo = generarCodigo();
                $codigoHash = password_hash($codigo, PASSWORD_DEFAULT);
                $expiraEn = date('Y-m-d H:i:s', time() + CODIGO_VIGENCIA_MINUTOS * 60);

                $stmt = $pdo->prepare(
                    'INSERT INTO login_codes (user_id, code_hash, expires_at, ip_address)
                     VALUES (:uid, :hash, :expira, :ip)'
                );
                $stmt->execute(['uid' => $user['id'], 'hash' => $codigoHash, 'expira' => $expiraEn, 'ip' => $ip]);

                if ($isLocal) {
                    // En desarrollo no se envía correo: el código viaja en la
                    // respuesta para poder probar sin configurar un servidor
                    // de correo local.
                    respond([
                        'ok' => true,
                        'dev_code' => $codigo,
                        'message' => 'Modo desarrollo: tu código de acceso es ' . $codigo . '.',
                    ]);
                }

                enviarCodigoPorCorreo($email, $codigo);
            }
        }

        // Misma respuesta exista o no el correo, y sin importar si se generó
        // código o no: así nadie puede usar este endpoint para averiguar
        // qué correos están registrados.
        respond(['ok' => true, 'message' => 'Si el correo está registrado, recibirás un código de acceso.']);
    }

    /* ============ PASO 2: VERIFICAR CÓDIGO ============ */
    if ($action === 'verify_code') {

        if (!preg_match('/^\d{' . CODIGO_LARGO . '}$/', $code)) {
            respond(['error' => 'Código inválido.'], 400);
        }

        $intentosFallidos = intentosVerificacionFallidosRecientes($pdo, $email, $ip, VENTANA_VERIFICACION_MIN);
        if ($intentosFallidos >= MAX_INTENTOS_VERIFICACION) {
            respond(['error' => 'Demasiados intentos fallidos. Solicita un nuevo código en ' . BLOQUEO_MINUTOS . ' minutos.'], 429);
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Hash señuelo: mantiene el tiempo de respuesta parecido aunque el
        // usuario o el código no existan, para no filtrar esa información
        // por temporización.
        static $dummyHash = '$2y$10$abcdefghijklmnopqrstuuVQjV1n0z0e0e0e0e0e0e0e0e0e0e0e';
        $codigoValido = false;
        $idLoginCode = null;

        if ($user) {
            $stmt = $pdo->prepare(
                'SELECT id_login_code, code_hash FROM login_codes
                 WHERE user_id = :uid AND used_at IS NULL AND expires_at >= NOW() AND attempts < :max
                 ORDER BY created_at DESC LIMIT 1'
            );
            $stmt->execute(['uid' => $user['id'], 'max' => MAX_INTENTOS_VERIFICACION]);
            $registroCodigo = $stmt->fetch();

            if ($registroCodigo) {
                $codigoValido = password_verify($code, $registroCodigo['code_hash']);
                $idLoginCode = $registroCodigo['id_login_code'];
            } else {
                password_verify($code, $dummyHash);
            }
        } else {
            password_verify($code, $dummyHash);
        }

        if (!$user || !$codigoValido) {
            registrarIntentoVerificacion($pdo, $email, $ip, false);
            if ($idLoginCode) {
                $pdo->prepare('UPDATE login_codes SET attempts = attempts + 1 WHERE id_login_code = ?')
                    ->execute([$idLoginCode]);
            }
            respond(['error' => 'Código inválido o expirado.'], 401);
        }

        $pdo->prepare('UPDATE login_codes SET used_at = NOW() WHERE id_login_code = ?')->execute([$idLoginCode]);
        registrarIntentoVerificacion($pdo, $email, $ip, true);

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $email;
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        respond(['ok' => true, 'redirect' => 'game.php'], 200);
    }

    respond(['error' => 'Acción no reconocida.'], 400);
} catch (PDOException $e) {
    error_log('login.php: ' . $e->getMessage());
    respond(['error' => 'Error al procesar la solicitud.'], 500);
}
