<?php
/**
 * =========================================================
 * LOGIN.PHP — SAFETY CONTROL TOWER
 * =========================================================
 *
 * Autenticación de doble factor (2FA):
 *
 *   1. request_code
 *      { email, password, csrf_token }
 *      - valida correo + contraseña
 *      - solo después genera y envía el código
 *
 *   2. resend_code
 *      { email, csrf_token }
 *      - exige que la contraseña ya haya sido validada en esta sesión
 *
 *   3. verify_code
 *      { email, code, csrf_token }
 *      - valida el segundo factor y crea la sesión autenticada
 *
 * SEGURIDAD
 * ---------------------------------------------------------
 * - CSRF
 * - password_verify() con hash señuelo para reducir diferencias de timing
 * - Rate limiting por usuario e IP
 * - Código aleatorio de un solo uso
 * - Expiración de código
 * - Segundo factor ligado a la sesión que validó la contraseña
 * - Regeneración de ID de sesión al autenticar
 * - Mensajes genéricos frente a credenciales inválidas
 * =========================================================
 */

const CODIGO_LARGO                = 6;
const CODIGO_VIGENCIA_MINUTOS     = 10;
const PENDIENTE_2FA_MINUTOS       = 15;
const MAX_SOLICITUDES_CODIGO_IP   = 8;
const MAX_SOLICITUDES_CODIGO_USR  = 3;
const VENTANA_SOLICITUD_MINUTOS   = 15;
const MAX_INTENTOS_AUTENTICACION_USR = 5;
const MAX_INTENTOS_AUTENTICACION_IP  = 50; // resguardo alto para redes compartidas
const MAX_INTENTOS_CODIGO             = 5;
const VENTANA_INTENTOS_MINUTOS    = 10;
const BLOQUEO_MINUTOS             = 15;

require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/response.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/passwords.php';
require __DIR__ . '/i18n.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = trim((string) ($input['action'] ?? ''));
$email = strtolower(trim((string) ($input['email'] ?? '')));
$password = (string) ($input['password'] ?? '');
$code = trim((string) ($input['code'] ?? ''));
$csrfToken = (string) ($input['csrf_token'] ?? '');
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

if (
    empty($_SESSION['csrf_token']) ||
    $csrfToken === '' ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    responderJSON(
        false,
        null,
        'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.',
        403
    );
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responderJSON(false, null, 'Correo electrónico no válido.', 400);
}

$isLocal = isset($isLocal)
    ? (bool) $isLocal
    : ((getenv('APP_ENV') ?: 'local') === 'local');

/** Hash bcrypt válido para ejecutar password_verify() aunque la cuenta no exista. */
const DUMMY_PASSWORD_HASH = '$2y$12$eXl0YUpTJtHejei9lEo61uqvTt5ToI/ZgYlIdbTXu7IuhiRLfMuLy';

function generarCodigo(): string
{
    return str_pad(
        (string) random_int(0, (10 ** CODIGO_LARGO) - 1),
        CODIGO_LARGO,
        '0',
        STR_PAD_LEFT
    );
}

function enviarCodigoPorCorreo(string $email, string $codigo): bool
{
    $asunto = 'Tu código de acceso — Safety Control Tower';

    $cuerpo =
        "Tu código de verificación es: {$codigo}\n\n" .
        'Este código vence en ' . CODIGO_VIGENCIA_MINUTOS . " minutos.\n" .
        "La contraseña ya fue validada previamente.\n" .
        'Si no intentaste iniciar sesión, ignora este mensaje.';

    $cabeceras =
        "From: Safety Control Tower <no-responder@safetycontroltower.cl>\r\n" .
        "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($email, $asunto, $cuerpo, $cabeceras);
}

function registrarIntentoAutenticacion(
    PDO $pdo,
    string $identifier,
    string $ip,
    bool $success
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO login_attempts (identifier, ip_address, success)
         VALUES (:identifier, :ip, :success)'
    );

    $stmt->execute([
        'identifier' => $identifier,
        'ip' => $ip,
        'success' => $success ? 1 : 0,
    ]);
}

function cargarUltimosFallosUsuario(PDO $pdo, string $identifier, int $limite): array
{
    $limite = max(1, $limite);
    $stmt = $pdo->prepare(
        'SELECT UNIX_TIMESTAMP(la.created_at) AS failed_at,
                UNIX_TIMESTAMP(NOW()) AS now_at
         FROM login_attempts la
         WHERE la.success = 0
           AND la.identifier = :identifier
           AND la.created_at >= COALESCE(
               (SELECT uc.password_changed_at
                FROM user_credentials uc
                WHERE uc.id_users = :credential_user
                LIMIT 1),
               \'1970-01-01 00:00:00\'
           )
         ORDER BY la.created_at DESC
         LIMIT ' . $limite
    );
    $stmt->execute([
        'identifier' => $identifier,
        'credential_user' => $identifier,
    ]);
    return $stmt->fetchAll();
}

function cargarUltimosFallosIp(PDO $pdo, string $ip, int $limite): array
{
    $limite = max(1, $limite);
    $stmt = $pdo->prepare(
        'SELECT UNIX_TIMESTAMP(created_at) AS failed_at,
                UNIX_TIMESTAMP(NOW()) AS now_at
         FROM login_attempts
         WHERE success = 0
           AND ip_address = :ip
         ORDER BY created_at DESC
         LIMIT ' . $limite
    );
    $stmt->execute(['ip' => $ip]);
    return $stmt->fetchAll();
}

/**
 * Devuelve segundos restantes de bloqueo si los últimos $limite fallos
 * ocurrieron dentro de la ventana configurada. El bloqueo comienza con el
 * fallo que completa el límite y dura BLOQUEO_MINUTOS desde ese momento.
 * Los registros success=1 nunca participan en esta evaluación.
 */
function segundosBloqueoPorRafaga(array $rows, int $limite): int
{
    if (count($rows) < $limite) {
        return 0;
    }

    $timestamps = [];
    $now = time();
    foreach ($rows as $row) {
        $ts = (int) ($row['failed_at'] ?? 0);
        if ($ts > 0) {
            $timestamps[] = $ts;
        }
        if (!empty($row['now_at'])) {
            $now = (int) $row['now_at'];
        }
    }

    if (count($timestamps) < $limite) {
        return 0;
    }

    $newest = max($timestamps);
    $oldest = min($timestamps);
    if (($newest - $oldest) > (VENTANA_INTENTOS_MINUTOS * 60)) {
        return 0;
    }

    return max(0, ($newest + (BLOQUEO_MINUTOS * 60)) - $now);
}

function verificarBloqueo(PDO $pdo, string $email, string $ip): void
{
    $segundosUsuario = segundosBloqueoPorRafaga(
        cargarUltimosFallosUsuario($pdo, $email, MAX_INTENTOS_AUTENTICACION_USR),
        MAX_INTENTOS_AUTENTICACION_USR
    );

    if ($segundosUsuario > 0) {
        $minutos = max(1, (int) ceil($segundosUsuario / 60));
        responderJSON(
            false,
            ['retry_after_seconds' => $segundosUsuario],
            'Se alcanzó el límite de 5 intentos fallidos en 10 minutos. La cuenta está bloqueada temporalmente; intenta nuevamente en ' . $minutos . ' minuto(s).',
            429
        );
    }

    // Protección secundaria contra abuso masivo desde una misma red. El umbral
    // es deliberadamente alto para no castigar oficinas donde varios usuarios
    // comparten IP pública. También cuenta exclusivamente success=0.
    $segundosIp = segundosBloqueoPorRafaga(
        cargarUltimosFallosIp($pdo, $ip, MAX_INTENTOS_AUTENTICACION_IP),
        MAX_INTENTOS_AUTENTICACION_IP
    );

    if ($segundosIp > 0) {
        $minutos = max(1, (int) ceil($segundosIp / 60));
        responderJSON(
            false,
            ['retry_after_seconds' => $segundosIp],
            'Se detectaron demasiados accesos fallidos desde esta conexión. Intenta nuevamente en ' . $minutos . ' minuto(s).',
            429
        );
    }
}

function solicitudesCodigoPorIp(PDO $pdo, string $ip): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM login_codes
         WHERE ip_address = :ip
           AND created_at >= (NOW() - INTERVAL :minutos MINUTE)'
    );

    $stmt->execute([
        'ip' => $ip,
        'minutos' => VENTANA_SOLICITUD_MINUTOS,
    ]);

    return (int) $stmt->fetchColumn();
}

function solicitudesCodigoPorUsuario(PDO $pdo, string $idUsers): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM login_codes
         WHERE id_users = :id_users
           AND created_at >= (NOW() - INTERVAL :minutos MINUTE)'
    );

    $stmt->execute([
        'id_users' => $idUsers,
        'minutos' => VENTANA_SOLICITUD_MINUTOS,
    ]);

    return (int) $stmt->fetchColumn();
}

function validarLimiteEnvioCodigo(PDO $pdo, string $idUsers, string $ip): void
{
    if (solicitudesCodigoPorIp($pdo, $ip) >= MAX_SOLICITUDES_CODIGO_IP) {
        responderJSON(
            false,
            null,
            'Demasiadas solicitudes desde esta conexión. Intenta nuevamente en unos minutos.',
            429
        );
    }

    if (solicitudesCodigoPorUsuario($pdo, $idUsers) >= MAX_SOLICITUDES_CODIGO_USR) {
        responderJSON(
            false,
            null,
            'Se alcanzó el límite de códigos solicitados. Intenta nuevamente en unos minutos.',
            429
        );
    }
}

function iniciarSegundoFactor(string $idUsers): void
{
    $_SESSION['2fa_pending_user'] = strtolower($idUsers);
    $_SESSION['2fa_password_verified_at'] = time();
}

function limpiarSegundoFactor(): void
{
    unset(
        $_SESSION['2fa_pending_user'],
        $_SESSION['2fa_password_verified_at']
    );
}

function segundoFactorPendienteValido(string $email): bool
{
    $pendingUser = (string) ($_SESSION['2fa_pending_user'] ?? '');
    $verifiedAt = (int) ($_SESSION['2fa_password_verified_at'] ?? 0);

    if ($pendingUser === '' || $verifiedAt <= 0) {
        return false;
    }

    if (!hash_equals($pendingUser, strtolower($email))) {
        return false;
    }

    return (time() - $verifiedAt) <= (PENDIENTE_2FA_MINUTOS * 60);
}

/**
 * Crea un código nuevo e invalida los códigos anteriores todavía activos.
 * Devuelve el código en texto plano solo para que el llamador pueda enviarlo;
 * nunca se guarda sin hash en la base de datos.
 */
function crearCodigo(PDO $pdo, string $idUsers, string $ip): string
{
    $codigo = generarCodigo();
    $codigoHash = password_hash($codigo, PASSWORD_DEFAULT);
    $expiraEn = date('Y-m-d H:i:s', time() + (CODIGO_VIGENCIA_MINUTOS * 60));

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'UPDATE login_codes
             SET used_at = NOW()
             WHERE id_users = :id_users
               AND used_at IS NULL'
        );
        $stmt->execute(['id_users' => $idUsers]);

        $stmt = $pdo->prepare(
            'INSERT INTO login_codes
                (id_users, code_hash, expires_at, attempts, ip_address)
             VALUES
                (:id_users, :code_hash, :expires_at, 0, :ip_address)'
        );

        $stmt->execute([
            'id_users' => $idUsers,
            'code_hash' => $codigoHash,
            'expires_at' => $expiraEn,
            'ip_address' => $ip,
        ]);

        $pdo->commit();
        return $codigo;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * Cuentas DEMO oficiales de SCT.
 *
 * Estas cuentas utilizan direcciones de demostración sin buzón real. Por ese
 * motivo el OTP se muestra en pantalla tanto en localhost como en los ambientes
 * web, pero SOLO para estas cuatro cuentas explícitamente autorizadas.
 *
 * No existe bypass del flujo: primero debe validarse la contraseña y después el
 * usuario debe ingresar el OTP, que se comprueba contra login_codes igual que
 * para cualquier otra cuenta.
 */
function esCuentaDemoSct(string $email): bool
{
    static $cuentasDemo = [
        'gerenteempresademo@demosct.cl',
        'jefaturaempresademo@demosct.cl',
        'usuarioempresademo@demosct.cl',
        'adminempresademo@demosct.cl',
    ];

    return in_array(strtolower(trim($email)), $cuentasDemo, true);
}

function debeMostrarOtpEnPantalla(bool $isLocal, string $email): bool
{
    // Localhost conserva el comportamiento actual para cualquier usuario.
    if ($isLocal) {
        return true;
    }

    // En web solo las cuentas DEMO oficiales muestran el OTP en pantalla.
    return esCuentaDemoSct($email);
}

function responderCodigoEnviado(bool $mostrarEnPantalla, string $codigo, bool $reenviado = false, bool $cuentaDemo = false): void
{
    if ($mostrarEnPantalla) {
        responderJSON(
            true,
            [
                'display_code' => $codigo,
                'display_mode' => $cuentaDemo ? 'demo' : 'local',
            ],
            $cuentaDemo
                ? 'Cuenta DEMO validada. Usa el código mostrado en pantalla para completar el segundo factor.'
                : 'Modo desarrollo local: usa el código mostrado en pantalla para completar el segundo factor.'
        );
    }

    responderJSON(
        true,
        null,
        $reenviado
            ? 'Código reenviado. Revisa tu correo.'
            : 'Contraseña validada. Enviamos un código de verificación a tu correo.'
    );
}

try {
    /* =====================================================
       PASO 1 — VALIDAR CONTRASEÑA Y SOLICITAR CÓDIGO
       ===================================================== */
    if ($action === 'request_code') {
        verificarBloqueo($pdo, $email, $ip);

        if ($password === '' || strlen($password) > 255) {
            registrarIntentoAutenticacion($pdo, $email, $ip, false);
            verificarBloqueo($pdo, $email, $ip);
            responderJSON(false, null, 'Correo o contraseña incorrectos. Si tu cuenta aún no tiene una contraseña nueva, usa “¿Olvidaste tu contraseña?”.', 401);
        }

        $stmt = $pdo->prepare(
            'SELECT u.id_users, uc.password_hash, uc.credential_status
             FROM users u
             LEFT JOIN user_credentials uc ON uc.id_users = u.id_users
             WHERE u.id_users = :email
               AND u.state = 1
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        $storedHash = ($user && !empty($user['password_hash']))
            ? (string) $user['password_hash']
            : DUMMY_PASSWORD_HASH;

        $passwordValid = password_verify($password, $storedHash);

        if (
            !$user
            || ($user['credential_status'] ?? '') !== PASSWORD_CREDENTIAL_ACTIVE
            || empty($user['password_hash'])
            || !$passwordValid
        ) {
            registrarIntentoAutenticacion($pdo, $email, $ip, false);
            limpiarSegundoFactor();
            verificarBloqueo($pdo, $email, $ip);
            responderJSON(false, null, 'Correo o contraseña incorrectos. Si tu cuenta aún no tiene una contraseña nueva, usa “¿Olvidaste tu contraseña?”.', 401);
        }

        // Eleva hashes antiguos al algoritmo/costo actual después de una validación válida.
        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'UPDATE user_credentials
                 SET password_hash = :password_hash,
                     password_changed_at = NOW(),
                     updated_at = NOW()
                 WHERE id_users = :id_users'
            );
            $stmt->execute([
                'password_hash' => $nuevoHash,
                'id_users' => $user['id_users'],
            ]);
        }

        validarLimiteEnvioCodigo($pdo, (string) $user['id_users'], $ip);

        $codigo = crearCodigo($pdo, (string) $user['id_users'], $ip);
        $mostrarOtpEnPantalla = debeMostrarOtpEnPantalla($isLocal, $email);
        $cuentaDemo = esCuentaDemoSct($email);

        if (!$mostrarOtpEnPantalla && !enviarCodigoPorCorreo($email, $codigo)) {
            error_log('login.php: no fue posible enviar el código 2FA por correo.');
            limpiarSegundoFactor();
            responderJSON(false, null, 'No fue posible enviar el código. Intenta nuevamente.', 500);
        }

        iniciarSegundoFactor((string) $user['id_users']);
        responderCodigoEnviado($mostrarOtpEnPantalla, $codigo, false, $cuentaDemo);
    }

    /* =====================================================
       REENVÍO — SOLO DESPUÉS DE VALIDAR CONTRASEÑA
       ===================================================== */
    if ($action === 'resend_code') {
        if (!segundoFactorPendienteValido($email)) {
            limpiarSegundoFactor();
            responderJSON(
                false,
                null,
                'La validación de contraseña expiró. Ingresa nuevamente tus credenciales.',
                403
            );
        }

        $stmt = $pdo->prepare(
            'SELECT id_users
             FROM users
             WHERE id_users = :email
               AND state = 1
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            limpiarSegundoFactor();
            responderJSON(false, null, 'No fue posible continuar la autenticación.', 403);
        }

        validarLimiteEnvioCodigo($pdo, (string) $user['id_users'], $ip);
        $codigo = crearCodigo($pdo, (string) $user['id_users'], $ip);
        $mostrarOtpEnPantalla = debeMostrarOtpEnPantalla($isLocal, $email);
        $cuentaDemo = esCuentaDemoSct($email);

        if (!$mostrarOtpEnPantalla && !enviarCodigoPorCorreo($email, $codigo)) {
            error_log('login.php: no fue posible reenviar el código 2FA por correo.');
            responderJSON(false, null, 'No fue posible reenviar el código. Intenta nuevamente.', 500);
        }

        // El reenvío renueva la ventana de segundo factor porque la contraseña
        // ya fue validada dentro de la misma sesión y la solicitud sigue activa.
        iniciarSegundoFactor((string) $user['id_users']);
        responderCodigoEnviado($mostrarOtpEnPantalla, $codigo, true, $cuentaDemo);
    }

    /* =====================================================
       PASO 2 — VERIFICAR CÓDIGO
       ===================================================== */
    if ($action === 'verify_code') {
        if (!segundoFactorPendienteValido($email)) {
            limpiarSegundoFactor();
            responderJSON(
                false,
                null,
                'La validación previa expiró. Ingresa nuevamente tu correo y contraseña.',
                403
            );
        }

        if (!preg_match('/^\d{' . CODIGO_LARGO . '}$/', $code)) {
            responderJSON(false, null, 'Código inválido.', 400);
        }

        $stmt = $pdo->prepare(
            'SELECT id_users, language
             FROM users
             WHERE id_users = :email
               AND state = 1
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            limpiarSegundoFactor();
            responderJSON(false, null, 'Código inválido o expirado.', 401);
        }

        $stmt = $pdo->prepare(
            'SELECT id_login_code, code_hash, attempts
             FROM login_codes
             WHERE id_users = :id_users
               AND used_at IS NULL
               AND expires_at >= NOW()
               AND attempts < :max_intentos
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute([
            'id_users' => $user['id_users'],
            'max_intentos' => MAX_INTENTOS_CODIGO,
        ]);
        $registroCodigo = $stmt->fetch();

        $codigoValido = false;
        if ($registroCodigo) {
            $codigoValido = password_verify($code, (string) $registroCodigo['code_hash']);
        } else {
            // Trabajo criptográfico señuelo cuando ya no existe un código utilizable.
            password_verify($code, DUMMY_PASSWORD_HASH);
        }

        if (!$registroCodigo || !$codigoValido) {
            if ($registroCodigo) {
                $stmt = $pdo->prepare(
                    'UPDATE login_codes
                     SET attempts = attempts + 1
                     WHERE id_login_code = :id_login_code'
                );
                $stmt->execute(['id_login_code' => $registroCodigo['id_login_code']]);

                $nuevoTotal = ((int) $registroCodigo['attempts']) + 1;
                if ($nuevoTotal >= MAX_INTENTOS_CODIGO) {
                    limpiarSegundoFactor();
                    responderJSON(
                        false,
                        null,
                        'Demasiados intentos fallidos. Vuelve a iniciar el acceso en unos minutos.',
                        429
                    );
                }
            }

            responderJSON(false, null, 'Código inválido o expirado.', 401);
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'UPDATE login_codes
                 SET used_at = NOW()
                 WHERE id_login_code = :id_login_code'
            );
            $stmt->execute(['id_login_code' => $registroCodigo['id_login_code']]);

            $stmt = $pdo->prepare(
                'UPDATE users
                 SET last_access = NOW()
                 WHERE id_users = :id_users'
            );
            $stmt->execute(['id_users' => $user['id_users']]);

            registrarIntentoAutenticacion($pdo, $email, $ip, true);
            $pdo->commit();

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        session_regenerate_id(true);
        limpiarSegundoFactor();

        $_SESSION['user_id'] = $user['id_users'];
        $_SESSION['user_email'] = $email;
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Requisito: al iniciar sesión, el sistema debe adoptar el idioma
        // configurado en el perfil del usuario (users.language), sin importar
        // qué idioma tenía elegido la sesión mientras no había iniciado sesión
        // (por ejemplo, el selector de la pantalla de login).
        $_SESSION['site_lang'] = normalizarIdiomaUsuario($user['language'] ?? null);

        responderJSON(
            true,
            ['redirect' => 'bienvenida.php'],
            'Inicio de sesión exitoso.'
        );
    }

    responderJSON(false, null, 'Acción no reconocida.', 400);

} catch (PDOException $e) {
    error_log('login.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al procesar la solicitud.', 500);
} catch (Throwable $e) {
    error_log('login.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al procesar la solicitud.', 500);
}
