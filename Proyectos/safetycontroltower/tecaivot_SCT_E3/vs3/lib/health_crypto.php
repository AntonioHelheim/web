<?php

/**
 * Cifrado de campos libres de salud.
 *
 * Producción:
 *   SCT_HEALTH_ENCRYPTION_KEY debe existir en el entorno y tener al menos
 *   32 caracteres.
 *
 * Desarrollo/local:
 *   si APP_ENV es "local" (o no está definido), se genera una clave aleatoria
 *   persistente en var/private/health.key. Esa clave NO debe versionarse ni
 *   copiarse entre ambientes.
 */

function healthEncryptionKeySourcePath(): string
{
    return dirname(__DIR__) . '/var/private/health.key';
}

function healthEnsureLocalEncryptionKey(): string
{
    $path = healthEncryptionKeySourcePath();
    $dir = dirname($path);

    if (!is_dir($dir)) {
        if (!mkdir($dir, 0700, true) && !is_dir($dir)) {
            throw new RuntimeException(
                'No fue posible crear el directorio privado para la clave de salud.'
            );
        }
    }

    if (is_file($path)) {
        $raw = trim((string)file_get_contents($path));
        if (strlen($raw) >= 32) {
            return $raw;
        }
    }

    try {
        $raw = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        throw new RuntimeException(
            'No fue posible generar la clave local de cifrado de salud.',
            0,
            $e
        );
    }

    $written = @file_put_contents($path, $raw . PHP_EOL, LOCK_EX);
    if ($written === false) {
        throw new RuntimeException(
            'No fue posible guardar la clave local de cifrado de salud.'
        );
    }

    @chmod($dir, 0700);
    @chmod($path, 0600);

    return $raw;
}

function healthEncryptionKey(): string
{
    $raw = getenv('SCT_HEALTH_ENCRYPTION_KEY');

    if (is_string($raw) && strlen(trim($raw)) >= 32) {
        return hash('sha256', trim($raw), true);
    }

    $environment = strtolower(trim((string)(getenv('APP_ENV') ?: 'local')));

    if ($environment === 'local' || $environment === 'development' || $environment === 'dev') {
        return hash('sha256', healthEnsureLocalEncryptionKey(), true);
    }

    throw new RuntimeException(
        'SCT_HEALTH_ENCRYPTION_KEY no está configurada o es demasiado corta.'
    );
}

function healthEncryptionReady(): bool
{
    try {
        healthEncryptionKey();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function healthEncrypt(?string $plaintext): ?string
{
    $plaintext = $plaintext === null ? null : trim($plaintext);

    if ($plaintext === null || $plaintext === '') {
        return null;
    }

    if (!function_exists('openssl_encrypt')) {
        throw new RuntimeException('OpenSSL no está disponible.');
    }

    $iv = random_bytes(12);
    $tag = '';

    $cipher = openssl_encrypt(
        $plaintext,
        'aes-256-gcm',
        healthEncryptionKey(),
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($cipher === false) {
        throw new RuntimeException('No fue posible cifrar el dato.');
    }

    return base64_encode(json_encode([
        'v' => 1,
        'iv' => base64_encode($iv),
        'tag' => base64_encode($tag),
        'ct' => base64_encode($cipher),
    ], JSON_UNESCAPED_SLASHES));
}

function healthDecrypt(?string $payload): ?string
{
    if ($payload === null || trim($payload) === '') {
        return null;
    }

    if (!function_exists('openssl_decrypt')) {
        throw new RuntimeException('OpenSSL no está disponible.');
    }

    $decoded = base64_decode($payload, true);
    $data = $decoded !== false ? json_decode($decoded, true) : null;

    if (
        !is_array($data)
        || empty($data['iv'])
        || empty($data['tag'])
        || empty($data['ct'])
    ) {
        throw new RuntimeException('Formato cifrado inválido.');
    }

    $iv = base64_decode((string)$data['iv'], true);
    $tag = base64_decode((string)$data['tag'], true);
    $ct = base64_decode((string)$data['ct'], true);

    if ($iv === false || $tag === false || $ct === false) {
        throw new RuntimeException('Formato cifrado inválido.');
    }

    $plain = openssl_decrypt(
        $ct,
        'aes-256-gcm',
        healthEncryptionKey(),
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        ''
    );

    if ($plain === false) {
        throw new RuntimeException('No fue posible descifrar el dato.');
    }

    return $plain;
}
