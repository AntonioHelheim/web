<?php
/**
 * logout.php
 * Cierra la sesión del usuario de forma completa:
 * 1. Vacía los datos de sesión.
 * 2. Elimina la cookie de sesión del navegador.
 * 3. Destruye la sesión en el servidor.
 */

require __DIR__ . '/session_bootstrap.php';

// 1. Vaciar datos de sesión
$_SESSION = [];

// 2. Eliminar la cookie de sesión (si el cliente usa cookies, que es lo normal)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// Volver al inicio
header('Location: index.php');
exit;