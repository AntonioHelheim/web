<?php
require_once __DIR__ . '/repositorios/SaludRepository.php';

function healthGateIsExemptRequest(): bool
{
    $path = str_replace('\\','/', (string)($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''));
    if (preg_match('~/api/salud/(?:formulario-obtener|formulario-guardar)\.php$~', $path)) return true;
    if (preg_match('~/(?:login|logout|bienvenida)\.php$~', $path)) return true;
    return false;
}

function healthGateApplies(PDO $pdo): bool
{
    $roles = currentUserRoles($pdo);
    return count(array_intersect($roles, HEALTH_FORM_ROLES)) > 0;
}

function healthGateBlocked(PDO $pdo): bool
{
    $id = currentUserId();
    return $id && healthGateApplies($pdo) && healthUserRequiresForm($pdo, $id);
}

function healthGateWelcomeUrl(): string
{
    $script = str_replace('\\','/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $pos = strpos($script, '/api/');
    if ($pos !== false) return substr($script, 0, $pos) . '/bienvenida.php?health_required=1';
    return rtrim(dirname($script), '/') . '/bienvenida.php?health_required=1';
}

function healthGateMessage(): string
{
    if (function_exists('t')) {
        return t('health_gate_required');
    }
    $allowed = ['es','en','pt','fr','zh'];
    $lang = isset($_SESSION['site_lang']) && in_array($_SESSION['site_lang'], $allowed, true) ? $_SESSION['site_lang'] : 'es';
    $file = dirname(__DIR__) . '/lang/' . $lang . '.php';
    if (is_file($file)) {
        $strings = require $file;
        if (isset($strings['health_gate_required'])) return (string)$strings['health_gate_required'];
    }
    return 'Complete your health form before continuing.';
}
