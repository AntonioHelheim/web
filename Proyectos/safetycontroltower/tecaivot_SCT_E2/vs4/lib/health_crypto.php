<?php
/** Cifrado de campos libres de salud. La clave nunca se almacena en el repositorio. */
function healthEncryptionKey(): string
{
    $raw = getenv('SCT_HEALTH_ENCRYPTION_KEY');
    if (!is_string($raw) || strlen(trim($raw)) < 32) {
        throw new RuntimeException('SCT_HEALTH_ENCRYPTION_KEY no está configurada o es demasiado corta.');
    }
    return hash('sha256', $raw, true);
}

function healthEncrypt(?string $plaintext): ?string
{
    $plaintext = $plaintext === null ? null : trim($plaintext);
    if ($plaintext === null || $plaintext === '') return null;
    if (!function_exists('openssl_encrypt')) throw new RuntimeException('OpenSSL no está disponible.');
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', healthEncryptionKey(), OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($cipher === false) throw new RuntimeException('No fue posible cifrar el dato.');
    return base64_encode(json_encode([
        'v' => 1,
        'iv' => base64_encode($iv),
        'tag' => base64_encode($tag),
        'ct' => base64_encode($cipher),
    ], JSON_UNESCAPED_SLASHES));
}

function healthDecrypt(?string $payload): ?string
{
    if ($payload === null || trim($payload) === '') return null;
    if (!function_exists('openssl_decrypt')) throw new RuntimeException('OpenSSL no está disponible.');
    $decoded = base64_decode($payload, true);
    $data = $decoded !== false ? json_decode($decoded, true) : null;
    if (!is_array($data) || empty($data['iv']) || empty($data['tag']) || empty($data['ct'])) {
        throw new RuntimeException('Formato cifrado inválido.');
    }
    $iv = base64_decode((string)$data['iv'], true);
    $tag = base64_decode((string)$data['tag'], true);
    $ct = base64_decode((string)$data['ct'], true);
    if ($iv === false || $tag === false || $ct === false) throw new RuntimeException('Formato cifrado inválido.');
    $plain = openssl_decrypt($ct, 'aes-256-gcm', healthEncryptionKey(), OPENSSL_RAW_DATA, $iv, $tag, '');
    if ($plain === false) throw new RuntimeException('No fue posible descifrar el dato.');
    return $plain;
}
