<?php
/**
 * lib/passwords.php
 * Ciclo de vida de contraseñas para Safety Control Tower.
 *
 * - provisión/activación de cuentas nuevas
 * - restablecimiento de contraseña
 * - consumo seguro de tokens de un solo uso
 *
 * Reutiliza la tabla password_resets existente. El token en texto plano
 * solo existe en memoria y en el enlace enviado por correo; la base de
 * datos guarda únicamente SHA-256(token), ya que el token tiene 256 bits
 * de entropía y no necesita un hash lento como una contraseña humana.
 */

const PASSWORD_MIN_LENGTH = 12;
const PASSWORD_MAX_LENGTH = 128;
const PASSWORD_RESET_TTL_MINUTES = 60;
const PASSWORD_ACTIVATION_TTL_MINUTES = 1440; // 24 horas
const PASSWORD_RESET_MAX_PER_IP = 20;
const PASSWORD_RESET_MAX_PER_USER = 6;
const PASSWORD_RESET_WINDOW_MINUTES = 60;

const PASSWORD_CREDENTIAL_PENDING_ACTIVATION = 'pending_activation';
const PASSWORD_CREDENTIAL_RESET_REQUIRED = 'reset_required';
const PASSWORD_CREDENTIAL_ACTIVE = 'active';
const PASSWORD_PURPOSE_ACTIVATION = 'activation';
const PASSWORD_PURPOSE_RESET = 'reset';


function passwordsStringLength(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}

function passwordsLower(string $value): string
{
    return function_exists('mb_strtolower')
        ? mb_strtolower($value, 'UTF-8')
        : strtolower($value);
}

/**
 * Valida una contraseña nueva. Devuelve null si cumple la política o un
 * mensaje legible si debe rechazarse.
 */
function passwordsValidateNewPassword(string $password, string $email = '', string $rut = ''): ?string
{
    $length = passwordsStringLength($password);

    if ($length < PASSWORD_MIN_LENGTH) {
        return 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres.';
    }

    if ($length > PASSWORD_MAX_LENGTH) {
        return 'La contraseña no puede superar ' . PASSWORD_MAX_LENGTH . ' caracteres.';
    }

    if (preg_match('/\x00/', $password)) {
        return 'La contraseña contiene caracteres no permitidos.';
    }

    if (!preg_match('/\p{L}/u', $password) || !preg_match('/\d/u', $password)) {
        return 'La contraseña debe incluir al menos una letra y un número.';
    }

    $normalized = passwordsLower(trim($password));
    $common = [
        'password1234', 'password12345', 'contraseña123', 'contrasena123',
        'safetycontroltower', 'safetycontroltower123', '123456789012',
        'qwerty123456', 'admin12345678',
    ];

    if (in_array($normalized, $common, true)) {
        return 'Elige una contraseña menos predecible.';
    }

    if ($email !== '') {
        $localPart = passwordsLower((string) strstr($email, '@', true));
        if (passwordsStringLength($localPart) >= 4 && strpos($normalized, $localPart) !== false) {
            return 'La contraseña no debe contener tu correo o nombre de usuario.';
        }
    }

    // El RUT es un dato de identidad, no una credencial. Se normaliza solo
    // para impedir que una contraseña nueva se derive del RUT con o sin
    // puntos/guion. Nunca se usa el RUT para generar un hash.
    if ($rut !== '') {
        $normalizedRut = passwordsLower((string) preg_replace('/[^0-9k]/i', '', $rut));
        $passwordComparable = passwordsLower((string) preg_replace('/[^0-9a-záéíóúüñ]/iu', '', $password));

        if (
            passwordsStringLength($normalizedRut) >= 6
            && $normalizedRut !== ''
            && strpos($passwordComparable, $normalizedRut) !== false
        ) {
            return 'La contraseña no debe contener ni derivarse de tu RUT.';
        }
    }

    return null;
}

function passwordsNormalizeCredentialStatus(?string $status): string
{
    $status = strtolower(trim((string) $status));

    if (in_array($status, [
        PASSWORD_CREDENTIAL_PENDING_ACTIVATION,
        PASSWORD_CREDENTIAL_RESET_REQUIRED,
        PASSWORD_CREDENTIAL_ACTIVE,
    ], true)) {
        return $status;
    }

    return PASSWORD_CREDENTIAL_PENDING_ACTIVATION;
}

function passwordsPurposeForCredentialStatus(?string $status): string
{
    return passwordsNormalizeCredentialStatus($status) === PASSWORD_CREDENTIAL_PENDING_ACTIVATION
        ? PASSWORD_PURPOSE_ACTIVATION
        : PASSWORD_PURPOSE_RESET;
}

function passwordsTtlForPurpose(string $purpose): int
{
    return $purpose === PASSWORD_PURPOSE_ACTIVATION
        ? PASSWORD_ACTIVATION_TTL_MINUTES
        : PASSWORD_RESET_TTL_MINUTES;
}

function passwordsTokenHash(string $token): string
{
    return hash('sha256', $token);
}

function passwordsIsLocal(): bool
{
    if (isset($GLOBALS['isLocal'])) {
        return (bool) $GLOBALS['isLocal'];
    }

    if (function_exists('detectarEntornoLocal')) {
        return detectarEntornoLocal();
    }

    return (getenv('APP_ENV') ?: '') === 'local';
}

/**
 * URL base de la aplicación. En servidor conviene definir APP_URL, por
 * ejemplo https://www.safetycontroltower.cl. En local se infiere el host.
 */
function passwordsBaseUrl(): string
{
    $configured = trim((string) (getenv('APP_URL') ?: ''));
    if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_URL)) {
        $scheme = strtolower((string) parse_url($configured, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https'], true)) {
            return rtrim($configured, '/');
        }
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    if (!preg_match('/^[A-Za-z0-9.-]+(?::\d{1,5})?$/', $host)) {
        $host = 'localhost';
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = '';

    // Los endpoints públicos viven en la raíz; los endpoints /api/usuarios
    // deben igualmente generar un enlace hacia la raíz de la instalación.
    if ($scriptName !== '') {
        $marker = '/api/';
        $pos = strpos($scriptName, $marker);
        if ($pos !== false) {
            $basePath = substr($scriptName, 0, $pos);
        } else {
            $basePath = rtrim(dirname($scriptName), '/.');
        }
    }

    return ($https ? 'https' : 'http') . '://' . $host . $basePath;
}

function passwordsResetUrl(string $token): string
{
    return passwordsBaseUrl() . '/restablecer-password.php?token=' . rawurlencode($token);
}

function passwordsRecentRequestsByIp(PDO $pdo, string $ip): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM password_resets
         WHERE ip_address = :ip
           AND created_at >= (NOW() - INTERVAL ' . PASSWORD_RESET_WINDOW_MINUTES . ' MINUTE)'
    );
    $stmt->execute(['ip' => $ip]);
    return (int) $stmt->fetchColumn();
}

function passwordsRecentRequestsByUser(PDO $pdo, string $idUsers): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM password_resets
         WHERE id_users = :id_users
           AND created_at >= (NOW() - INTERVAL ' . PASSWORD_RESET_WINDOW_MINUTES . ' MINUTE)'
    );
    $stmt->execute(['id_users' => $idUsers]);
    return (int) $stmt->fetchColumn();
}

function passwordsCanIssueToken(PDO $pdo, string $idUsers, string $ip): bool
{
    if (passwordsRecentRequestsByIp($pdo, $ip) >= PASSWORD_RESET_MAX_PER_IP) {
        return false;
    }

    if (passwordsRecentRequestsByUser($pdo, $idUsers) >= PASSWORD_RESET_MAX_PER_USER) {
        return false;
    }

    return true;
}

/**
 * Garantiza que toda cuenta activa pueda entrar al ciclo de contraseña.
 * No crea una contraseña ni modifica una ya existente: solo crea la fila
 * de estado cuando falta. Esto repara cuentas históricas que fueron creadas
 * antes de introducir user_credentials.
 */
function passwordsEnsureCredentialRecord(PDO $pdo, string $idUsers): array
{
    $stmt = $pdo->prepare(
        'INSERT INTO user_credentials (
             id_users, password_hash, credential_status, password_changed_at,
             legacy_password_invalidated_at, created_at, updated_at
         ) VALUES (
             :id_users, NULL, :credential_status, NULL, NULL, NOW(), NOW()
         )
         ON DUPLICATE KEY UPDATE id_users = VALUES(id_users)'
    );
    $stmt->execute([
        'id_users' => $idUsers,
        'credential_status' => PASSWORD_CREDENTIAL_PENDING_ACTIVATION,
    ]);

    $stmt = $pdo->prepare(
        'SELECT id_users, password_hash, credential_status
         FROM user_credentials
         WHERE id_users = :id_users
         LIMIT 1'
    );
    $stmt->execute(['id_users' => $idUsers]);
    $credential = $stmt->fetch();

    if (!$credential) {
        throw new RuntimeException('No fue posible inicializar el estado de credenciales.');
    }

    $hash = trim((string) ($credential['password_hash'] ?? ''));
    $status = passwordsNormalizeCredentialStatus($credential['credential_status'] ?? null);

    // Autorreparación de inconsistencias: un hash en user_credentials solo
    // puede provenir del flujo nuevo, por lo que debe estar activo. Si el
    // estado dice active pero no hay hash, debe restablecer/activar.
    $expectedStatus = $status;
    if ($hash !== '') {
        $expectedStatus = PASSWORD_CREDENTIAL_ACTIVE;
    } elseif ($status === PASSWORD_CREDENTIAL_ACTIVE) {
        $expectedStatus = PASSWORD_CREDENTIAL_RESET_REQUIRED;
    }

    if ($expectedStatus !== $status) {
        $stmt = $pdo->prepare(
            'UPDATE user_credentials
             SET credential_status = :credential_status,
                 password_changed_at = CASE
                     WHEN :has_hash = 1 THEN COALESCE(password_changed_at, NOW())
                     ELSE NULL
                 END,
                 updated_at = NOW()
             WHERE id_users = :id_users'
        );
        $stmt->execute([
            'credential_status' => $expectedStatus,
            'has_hash' => $hash !== '' ? 1 : 0,
            'id_users' => $idUsers,
        ]);
        $credential['credential_status'] = $expectedStatus;
    }

    return $credential;
}

/**
 * Crea un token de un solo uso e invalida tokens anteriores del usuario.
 * @return array{token:string,expires_at:string,url:string}
 */
function passwordsCreateToken(PDO $pdo, string $idUsers, string $ip, int $ttlMinutes, string $purpose): array
{
    if (!in_array($purpose, [PASSWORD_PURPOSE_ACTIVATION, PASSWORD_PURPOSE_RESET], true)) {
        throw new InvalidArgumentException('Propósito de token de contraseña inválido.');
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = passwordsTokenHash($token);
    $expiresAt = date('Y-m-d H:i:s', time() + ($ttlMinutes * 60));

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'UPDATE password_resets
             SET used_at = NOW()
             WHERE id_users = :id_users
               AND used_at IS NULL'
        );
        $stmt->execute(['id_users' => $idUsers]);

        $stmt = $pdo->prepare(
            'INSERT INTO password_resets
                (id_users, token_hash, purpose, expires_at, used_at, ip_address, created_at)
             VALUES
                (:id_users, :token_hash, :purpose, :expires_at, NULL, :ip_address, NOW())'
        );
        $stmt->execute([
            'id_users' => $idUsers,
            'token_hash' => $tokenHash,
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
            'ip_address' => $ip,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    return [
        'token' => $token,
        'expires_at' => $expiresAt,
        'url' => passwordsResetUrl($token),
        'purpose' => $purpose,
    ];
}

/**
 * Busca un token vigente. No lo consume.
 */
function passwordsFindValidToken(PDO $pdo, string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT pr.id_reset, pr.id_users, pr.purpose, pr.expires_at, uc.password_hash, uc.credential_status, u.state, u.language, u.rut
         FROM password_resets pr
         INNER JOIN users u ON u.id_users = pr.id_users
         LEFT JOIN user_credentials uc ON uc.id_users = u.id_users
         WHERE pr.token_hash = :token_hash
           AND pr.used_at IS NULL
           AND pr.expires_at >= NOW()
           AND u.state = 1
         ORDER BY pr.id_reset DESC
         LIMIT 1'
    );
    $stmt->execute(['token_hash' => passwordsTokenHash($token)]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Consume el token y establece una contraseña nueva. Devuelve el usuario
 * afectado o null si el token dejó de ser válido entre la validación y el POST.
 */
function passwordsConsumeToken(PDO $pdo, string $token, string $newPassword): ?string
{
    if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
        return null;
    }

    $tokenHash = passwordsTokenHash($token);
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    if ($passwordHash === false) {
        throw new RuntimeException('No fue posible generar el hash de contraseña.');
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'SELECT pr.id_reset, pr.id_users, pr.purpose, uc.credential_status
             FROM password_resets pr
             INNER JOIN users u ON u.id_users = pr.id_users
             LEFT JOIN user_credentials uc ON uc.id_users = u.id_users
             WHERE pr.token_hash = :token_hash
               AND pr.used_at IS NULL
               AND pr.expires_at >= NOW()
               AND u.state = 1
             ORDER BY pr.id_reset DESC
             LIMIT 1
             FOR UPDATE'
        );
        $stmt->execute(['token_hash' => $tokenHash]);
        $row = $stmt->fetch();

        if (!$row) {
            $pdo->rollBack();
            return null;
        }

        $idUsers = (string) $row['id_users'];

        // Las credenciales viven fuera de users. La existencia de esta fila
        // indica que la cuenta ya completó la activación de contraseña.
        $stmt = $pdo->prepare(
            'INSERT INTO user_credentials (
                 id_users,
                 password_hash,
                 credential_status,
                 password_changed_at,
                 legacy_password_invalidated_at,
                 created_at,
                 updated_at
             ) VALUES (
                 :id_users,
                 :password_hash,
                 :credential_status,
                 NOW(),
                 NULL,
                 NOW(),
                 NOW()
             )
             ON DUPLICATE KEY UPDATE
                 password_hash = VALUES(password_hash),
                 credential_status = VALUES(credential_status),
                 password_changed_at = NOW(),
                 legacy_password_invalidated_at = NULL,
                 updated_at = NOW()'
        );
        $stmt->execute([
            'password_hash' => $passwordHash,
            'credential_status' => PASSWORD_CREDENTIAL_ACTIVE,
            'id_users' => $idUsers,
        ]);

        // Un token usado invalida todos los enlaces de activación/reset previos.
        $stmt = $pdo->prepare(
            'UPDATE password_resets
             SET used_at = NOW()
             WHERE id_users = :id_users
               AND used_at IS NULL'
        );
        $stmt->execute(['id_users' => $idUsers]);

        // Si había un OTP de login emitido antes del cambio de contraseña,
        // también se invalida para forzar un flujo 2FA completamente nuevo.
        $stmt = $pdo->prepare(
            'UPDATE login_codes
             SET used_at = NOW()
             WHERE id_users = :id_users
               AND used_at IS NULL'
        );
        $stmt->execute(['id_users' => $idUsers]);

        $pdo->commit();
        return $idUsers;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * Envía el enlace de activación/restablecimiento. No incluye contraseñas
 * temporales ni datos sensibles en el correo.
 */
function passwordsSendLinkEmail(string $email, string $url, string $purpose): bool
{
    $activation = $purpose === PASSWORD_PURPOSE_ACTIVATION;

    $subject = $activation
        ? 'Activa tu acceso — Safety Control Tower'
        : 'Restablece tu contraseña — Safety Control Tower';

    $intro = $activation
        ? 'Se creó una cuenta para ti en Safety Control Tower.'
        : 'Recibimos una solicitud para restablecer la contraseña de tu cuenta en Safety Control Tower.';

    $ttlText = $activation ? '24 horas' : '60 minutos';

    $body =
        $intro . "\n\n" .
        "Define una nueva contraseña usando este enlace:\n{$url}\n\n" .
        "El enlace vence en {$ttlText} y solo puede utilizarse una vez.\n" .
        "Después de definir la contraseña, el ingreso seguirá requiriendo el código de verificación enviado por correo.\n\n" .
        "Si no esperabas este mensaje, puedes ignorarlo.";

    $headers =
        "From: Safety Control Tower <no-responder@safetycontroltower.cl>\r\n" .
        "Reply-To: no-responder@safetycontroltower.cl\r\n" .
        "MIME-Version: 1.0\r\n" .
        "Content-Type: text/plain; charset=UTF-8\r\n" .
        "Content-Transfer-Encoding: 8bit\r\n";

    // Codificar el asunto evita problemas de entrega con caracteres UTF-8.
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    return @mail($email, $encodedSubject, $body, $headers);
}
