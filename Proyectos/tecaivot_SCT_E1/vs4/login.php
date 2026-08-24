<?php
/**
 * login.php
 * Login sin contraseña, en 2 pasos, vía JSON:
 *   action=request_code  { email, csrf_token }
 *   action=verify_code   { email, code, csrf_token }
 *
 * Seguridad implementada:
 * - CSRF contra el token guardado en sesión.
 * - No se revela si un correo está o no registrado (mismo mensaje siempre).
 * - Rate limiting independiente para solicitar código (por IP) y para
 *   verificarlo (por correo + IP, igual que el login anterior).
 * - El código se guarda con password_hash (nunca en texto plano) y expira
 *   a los 10 minutos; se invalida tras usarse una vez.
 * - Cookie de sesión endurecida (heredada de session_bootstrap.php).
 *
 * Respuestas: todas vía responderJSON() (lib/response.php), para que el
 * formato sea idéntico al resto de los endpoints del proyecto.
 */

const CODIGO_LARGO               = 6;
const CODIGO_VIGENCIA_MINUTOS    = 10;
const MAX_SOLICITUDES_CODIGO_IP  = 8;    // solicitudes de código por IP
const MAX_SOLICITUDES_CODIGO_USR = 3;    // solicitudes de código por correo
const VENTANA_SOLICITUD_MINUTOS  = 15;

const MAX_INTENTOS_VERIFICACION  = 5;    // intentos de código fallidos
const VENTANA_VERIFICACION_MIN   = 15;
const BLOQUEO_MINUTOS            = 15;

require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/response.php';
require __DIR__ . '/lib/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$action    = (string) ($input['action'] ?? '');
$email     = trim((string) ($input['email'] ?? ''));
$code      = trim((string) ($input['code'] ?? ''));
$csrfToken = (string) ($input['csrf_token'] ?? '');
$ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responderJSON(false, null, 'Correo electrónico no válido.', 400);
}

/**
 * Genera un código numérico de CODIGO_LARGO dígitos, con ceros a la izquierda.
 */
function generarCodigo(): string
{
    return str_pad((string) random_int(0, 10 ** CODIGO_LARGO - 1), CODIGO_LARGO, '0', STR_PAD_LEFT);
}

/**
 * Envío del código por correo.
 * NOTA: mail() depende de que el servidor tenga un MTA local configurado
 * (habitual en hosting cPanel). Para asegurar entrega a la bandeja de
 * entrada en producción, se recomienda migrar a un proveedor transaccional
 * (SMTP + PHPMailer, SendGrid, Mailgun, Amazon SES, etc.) en vez de mail().
 */
function enviarCodigoPorCorreo(string $email, string $codigo): bool
{
    $asunto = 'Tu código de acceso — Tecaivot';
    $cuerpo = "Tu código de acceso es: {$codigo}\n\n"
            . 'Este código vence en ' . CODIGO_VIGENCIA_MINUTOS . " minutos.\n"
            . 'Si no solicitaste este código, puedes ignorar este mensaje.';
    $cabeceras = "From: Tecaivot <no-responder@tecaivot.cl>\r\n"
               . "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($email, $asunto, $cuerpo, $cabeceras);
}

function registrarIntentoVerificacion(PDO $pdo, string $identifier, string $ip, bool $success): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO login_attempts (identifier, ip_address, success) VALUES (:identifier, :ip, :success)'
    );
    $stmt->execute(['identifier' => $identifier, 'ip' => $ip, 'success' => $success ? 1 : 0]);
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

    /* =======================================================
       PASO 1 — SOLICITAR CÓDIGO
    ======================================================= */
    if ($action === 'request_code') {

        // Rate limit por IP: aplica exista o no el correo, para no filtrar
        // información sobre qué correos están registrados.
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_codes
             WHERE ip_address = :ip AND created_at >= (NOW() - INTERVAL :minutos MINUTE)'
        );
        $stmt->execute(['ip' => $ip, 'minutos' => VENTANA_SOLICITUD_MINUTOS]);
        $solicitudesPorIp = (int) $stmt->fetchColumn();

        if ($solicitudesPorIp >= MAX_SOLICITUDES_CODIGO_IP) {
            responderJSON(false, null, 'Demasiadas solicitudes desde esta conexión. Intenta nuevamente en unos minutos.', 429);
        }

        $stmt = $pdo->prepare('SELECT id_users FROM users WHERE id_users = :email AND state = 1 LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM login_codes
                 WHERE id_users = :identifier AND created_at >= (NOW() - INTERVAL :minutos MINUTE)'
            );
            $stmt->execute(['identifier' => $user['id_users'], 'minutos' => VENTANA_SOLICITUD_MINUTOS]);
            $solicitudesPorUsuario = (int) $stmt->fetchColumn();

            // Si ya se pasó del límite por usuario, no se genera ni envía un
            // código nuevo, pero igual respondemos el mensaje genérico más
            // abajo para no revelar el motivo exacto.
            if ($solicitudesPorUsuario < MAX_SOLICITUDES_CODIGO_USR) {
                $codigo     = generarCodigo();
                $codigoHash = password_hash($codigo, PASSWORD_DEFAULT);
                $expiraEn   = date('Y-m-d H:i:s', time() + CODIGO_VIGENCIA_MINUTOS * 60);

                $stmt = $pdo->prepare(
                    'INSERT INTO login_codes (id_users, code_hash, expires_at, ip_address)
                     VALUES (:identifier, :hash, :expira, :ip)'
                );
                $stmt->execute([
                    'identifier' => $user['id_users'],
                    'hash'       => $codigoHash,
                    'expira'     => $expiraEn,
                    'ip'         => $ip,
                ]);

                enviarCodigoPorCorreo($email, $codigo);
            }
        }

        // Respuesta idéntica exista o no el usuario / se haya enviado o no
        // un código nuevo: evita que alguien pueda usar este endpoint para
        // averiguar qué correos están registrados en el sistema.
        responderJSON(true, null, 'Si el correo está registrado, recibirás un código de acceso.');
    }

    /* =======================================================
       PASO 2 — VERIFICAR CÓDIGO
    ======================================================= */
    if ($action === 'verify_code') {

        if (!preg_match('/^\d{' . CODIGO_LARGO . '}$/', $code)) {
            responderJSON(false, null, 'Código inválido.', 400);
        }

        // Mismo esquema de bloqueo por intentos fallidos que el login anterior.
        $intentosFallidos = intentosVerificacionFallidosRecientes($pdo, $email, $ip, VENTANA_VERIFICACION_MIN);
        if ($intentosFallidos >= MAX_INTENTOS_VERIFICACION) {
            responderJSON(false, null, 'Demasiados intentos fallidos. Solicita un nuevo código en ' . BLOQUEO_MINUTOS . ' minutos.', 429);
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id_users = :email AND state = 1 LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Hash señuelo para igualar el tiempo de respuesta si el usuario no existe.
        static $dummyHash = '$2y$10$abcdefghijklmnopqrstuuVQjV1n0z0e0e0e0e0e0e0e0e0e0e0e';

        $codigoValido = false;
        $idLoginCode  = null;

        if ($user) {
            $stmt = $pdo->prepare(
                'SELECT id_login_code, code_hash FROM login_codes
                 WHERE id_users = :identifier
                   AND used_at IS NULL
                   AND expires_at >= NOW()
                   AND attempts < :maxIntentos
                 ORDER BY created_at DESC
                 LIMIT 1'
            );
            $stmt->execute(['identifier' => $user['id_users'], 'maxIntentos' => MAX_INTENTOS_VERIFICACION]);
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
                $pdo->prepare('UPDATE login_codes SET attempts = attempts + 1 WHERE id_login_code = :id')
                    ->execute(['id' => $idLoginCode]);
            }
            responderJSON(false, null, 'Código inválido o expirado.', 401);
        }

        // Código correcto: se marca usado (no se puede reutilizar) y se crea la sesión.
        $pdo->prepare('UPDATE login_codes SET used_at = NOW() WHERE id_login_code = :id')
            ->execute(['id' => $idLoginCode]);

        registrarIntentoVerificacion($pdo, $email, $ip, true);

        session_regenerate_id(true);
        $_SESSION['user_id']       = $user['id_users'];
        $_SESSION['user_email']    = $email;
        $_SESSION['logged_in']     = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token']    = bin2hex(random_bytes(32));

        responderJSON(true, ['redirect' => 'bienvenida.php'], 'Inicio de sesión exitoso.');
    }

    responderJSON(false, null, 'Acción no reconocida.', 400);

} catch (PDOException $e) {
    // El detalle real queda en el log del servidor; al cliente solo un mensaje genérico.
    error_log('login.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al procesar la solicitud.', 500);
}