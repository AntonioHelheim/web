<?php

require_once __DIR__ . '/session_bootstrap.php';

/**
 * Cierra únicamente la sesión correspondiente a la instalación
 * SCT desde la que se invocó logout.php.
 *
 * Esto evita que una sesión abierta mediante Apache/XAMPP elimine
 * la cookie de otra instancia ejecutada mediante PHP Server, o viceversa.
 */

$_SESSION = [];

if (session_status() === PHP_SESSION_ACTIVE) {
    sctExpireCurrentSessionCookie();
    session_destroy();
}

header('Location: index.php');
exit;
