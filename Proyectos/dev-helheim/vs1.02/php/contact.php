<?php
/**
 * Helheim.cl — formulario de contacto
 * Compatible con PHP 7.3+
 *
 * Modos de transporte:
 * - log  : localhost/desarrollo local. No envía correo; registra la ejecución.
 * - mail : usa mail() del servidor web.
 * - smtp : usa PHPMailer + SMTP (recomendado en ambiente web).
 *
 * Variables de entorno opcionales:
 * HELHEIM_CONTACT_MODE=log|mail|smtp
 * HELHEIM_ENV=local|development|production
 *
 * Para SMTP:
 * HELHEIM_SMTP_HOST
 * HELHEIM_SMTP_PORT=587
 * HELHEIM_SMTP_USER
 * HELHEIM_SMTP_PASS
 * HELHEIM_SMTP_SECURE=tls|ssl|none
 * HELHEIM_SMTP_FROM=no-reply@helheim.cl
 * HELHEIM_SMTP_FROM_NAME=Helheim.cl Web
 */

declare(strict_types=1);

const HELHEIM_CONTACT_TO = 'juanantonioconchaloyola@gmail.com';
const HELHEIM_CONTACT_SUBJECT = 'contacto desde la web de helheim.cl';
const HELHEIM_CONTACT_FROM = 'no-reply@helheim.cl';

function envValue(string $name, string $default = ''): string
{
    $value = getenv($name);
    return $value === false || $value === '' ? $default : (string) $value;
}

function isLocalRequest(): bool
{
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
    $host = strtolower(trim($host));

    if ($host === '') {
        return false;
    }

    // Quita puerto en localhost:8080 / 127.0.0.1:8080.
    if ($host[0] === '[') {
        $closing = strpos($host, ']');
        if ($closing !== false) {
            $host = substr($host, 1, $closing - 1);
        }
    } else {
        $colon = strpos($host, ':');
        if ($colon !== false) {
            $host = substr($host, 0, $colon);
        }
    }

    return in_array($host, array('localhost', '127.0.0.1', '::1'), true)
        || substr($host, -6) === '.local';
}

function contactMode(): string
{
    $mode = strtolower(envValue('HELHEIM_CONTACT_MODE'));
    if (in_array($mode, array('log', 'mail', 'smtp'), true)) {
        return $mode;
    }

    // Por seguridad y conveniencia, localhost nunca intenta enviar por Internet
    // salvo que se fuerce HELHEIM_CONTACT_MODE.
    return isLocalRequest() ? 'log' : 'mail';
}

function appEnvironment(): string
{
    $env = strtolower(envValue('HELHEIM_ENV', isLocalRequest() ? 'local' : 'production'));
    return in_array($env, array('local', 'development', 'production'), true) ? $env : 'production';
}

function wantsJson(): bool
{
    $requestedWith = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
    $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

    return strcasecmp($requestedWith, 'XMLHttpRequest') === 0
        || strpos($accept, 'application/json') !== false;
}

function respond(int $status, bool $ok, string $message, array $extra = array()): void
{
    http_response_code($status);

    if (wantsJson()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(
            array_merge(array('ok' => $ok, 'message' => $message), $extra),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    header('Content-Type: text/html; charset=UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($ok ? 'Mensaje procesado' : 'No fue posible procesar el mensaje', ENT_QUOTES, 'UTF-8');

    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . $safeTitle . ' — Helheim.cl</title></head>';
    echo '<body style="font-family:Arial,sans-serif;background:#080a08;color:#fff;padding:2rem;">';
    echo '<main style="max-width:680px;margin:4rem auto;padding:2rem;border:1px solid #263129;border-radius:12px;background:#111611;">';
    echo '<h1 style="font-size:1.5rem;">' . $safeTitle . '</h1>';
    echo '<p>' . $safeMessage . '</p>';
    echo '<p><a href="../index.html#formcontacto" style="color:#22c55e;">Volver a Helheim.cl</a></p>';
    echo '</main></body></html>';
    exit;
}

function logLocalContact(string $name, string $email, string $message): bool
{
    $root = dirname(__DIR__);
    $directory = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    $file = $directory . DIRECTORY_SEPARATOR . 'contact.log';

    if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
        error_log('Helheim contact: no fue posible crear ' . $directory);
        return false;
    }

    $entry = array(
        'timestamp' => date(DATE_ATOM),
        'transport' => 'log',
        'to' => HELHEIM_CONTACT_TO,
        'subject' => HELHEIM_CONTACT_SUBJECT,
        'name' => $name,
        'email' => $email,
        'message' => $message
    );

    $encoded = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) {
        return false;
    }

    return @file_put_contents($file, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}

function buildBody(string $name, string $email, string $message): string
{
    $body = "Nuevo contacto recibido desde helheim.cl\n\n";
    $body .= "Nombre: " . $name . "\n";
    $body .= "Correo: " . $email . "\n\n";
    $body .= "Mensaje:\n" . $message . "\n\n";
    $body .= "Fecha servidor: " . date('Y-m-d H:i:s T') . "\n";
    return $body;
}

function sendUsingMail(string $email, string $body): bool
{
    if (!function_exists('mail')) {
        error_log('Helheim contact: mail() no está disponible.');
        return false;
    }

    $headers = array(
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: Helheim.cl Web <' . HELHEIM_CONTACT_FROM . '>',
        'Reply-To: ' . $email,
        'X-Mailer: PHP/' . phpversion()
    );

    return @mail(
        HELHEIM_CONTACT_TO,
        HELHEIM_CONTACT_SUBJECT,
        $body,
        implode("\r\n", $headers)
    );
}

function sendUsingSmtp(string $name, string $email, string $body): bool
{
    $autoload = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    if (!is_file($autoload)) {
        error_log('Helheim contact: SMTP solicitado, pero vendor/autoload.php no existe. Ejecuta composer install.');
        return false;
    }

    require_once $autoload;

    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        error_log('Helheim contact: PHPMailer no está disponible.');
        return false;
    }

    $host = envValue('HELHEIM_SMTP_HOST');
    $user = envValue('HELHEIM_SMTP_USER');
    $pass = envValue('HELHEIM_SMTP_PASS');
    $from = envValue('HELHEIM_SMTP_FROM', HELHEIM_CONTACT_FROM);
    $fromName = envValue('HELHEIM_SMTP_FROM_NAME', 'Helheim.cl Web');
    $secure = strtolower(envValue('HELHEIM_SMTP_SECURE', 'tls'));
    $port = (int) envValue('HELHEIM_SMTP_PORT', $secure === 'ssl' ? '465' : '587');

    if ($host === '' || $user === '' || $pass === '') {
        error_log('Helheim contact: faltan variables HELHEIM_SMTP_HOST/USER/PASS.');
        return false;
    }

    try {
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->SMTPAuth = true;
        $mailer->Username = $user;
        $mailer->Password = $pass;
        $mailer->Timeout = 15;

        if ($secure === 'ssl') {
            $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($secure === 'tls') {
            $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mailer->SMTPAutoTLS = false;
            $mailer->SMTPSecure = '';
        }

        $mailer->setFrom($from, $fromName);
        $mailer->addAddress(HELHEIM_CONTACT_TO);
        $mailer->addReplyTo($email, $name);
        $mailer->Subject = HELHEIM_CONTACT_SUBJECT;
        $mailer->Body = $body;
        $mailer->isHTML(false);
        $mailer->send();
        return true;
    } catch (\Throwable $e) {
        error_log('Helheim contact SMTP: ' . $e->getMessage());
        return false;
    }
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, false, 'Este endpoint sólo acepta solicitudes POST.');
}

// Honeypot: para un bot se responde como éxito, pero no se envía ni registra el contenido.
$website = isset($_POST['website']) ? trim((string) $_POST['website']) : '';
if ($website !== '') {
    respond(200, true, 'Gracias. Hemos recibido tu mensaje.');
}

$name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';

// Evita inyección de cabeceras en Reply-To.
$email = str_replace(array("\r", "\n"), '', $email);

if ($name === '' || strlen($name) > 100) {
    respond(422, false, 'Por favor ingresa un nombre válido.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    respond(422, false, 'Por favor ingresa un correo electrónico válido.');
}

if (strlen($message) < 5 || strlen($message) > 5000) {
    respond(422, false, 'El mensaje debe contener entre 5 y 5000 caracteres.');
}

$name = str_replace(array("\r\n", "\r"), "\n", $name);
$message = str_replace(array("\r\n", "\r"), "\n", $message);
$body = buildBody($name, $email, $message);
$mode = contactMode();

if ($mode === 'log') {
    if (!logLocalContact($name, $email, $message)) {
        respond(500, false, 'El formulario fue recibido, pero no fue posible registrar la prueba local. Revisa permisos de storage/logs.');
    }

    respond(
        200,
        true,
        'Modo local: el formulario se ejecutó correctamente. No se envió correo; la prueba quedó registrada en storage/logs/contact.log.',
        array('transport' => 'log')
    );
}

$sent = false;
if ($mode === 'smtp') {
    $sent = sendUsingSmtp($name, $email, $body);
} else {
    $sent = sendUsingMail($email, $body);
}

if (!$sent) {
    $environment = appEnvironment();
    if ($environment === 'development') {
        respond(
            500,
            false,
            'El formulario llegó correctamente a PHP, pero falló el transporte de correo (' . $mode . '). Revisa la configuración del servidor y el log de errores de PHP.',
            array('transport' => $mode)
        );
    }

    respond(500, false, 'No fue posible enviar el mensaje en este momento. Intenta nuevamente o contáctanos por WhatsApp.');
}

respond(
    200,
    true,
    'Tu mensaje fue enviado correctamente. Te responderemos a la brevedad.',
    array('transport' => $mode)
);
