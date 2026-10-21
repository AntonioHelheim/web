<?php

require_once __DIR__ . '/app/Config/version.php';
require_once __DIR__ . '/app/Config/SessionRuntime.php';

/**
 * session_bootstrap.php
 * Arranca la sesión de forma consistente en todo el sitio:
 * - Nombre de cookie aislado por contexto público de SCT.
 * - Cookie httponly / secure (si hay HTTPS) / samesite=Strict.
 * - Path limitado a la instalación pública actual.
 * - Cierre automático por inactividad.
 *
 * Inclúyelo con require al inicio de CUALQUIER página o endpoint
 * que utilice $_SESSION.
 */

const SESSION_IDLE_TIMEOUT = 1800;

/* =========================================================
   1. CONFIGURACIÓN E INICIO DE SESIÓN
   ========================================================= */

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sctSession = sctSessionRuntimeConfig();

    session_name(
        (string)$sctSession['name']
    );

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => (string)$sctSession['cookie_path'],
        'domain' => '',
        'secure' => (bool)$sctSession['secure'],
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

/* =========================================================
   2. EXPIRACIÓN POR INACTIVIDAD
   ========================================================= */

$now = time();

if (
    isset($_SESSION['last_activity'])
    && is_numeric($_SESSION['last_activity'])
    && ($now - (int)$_SESSION['last_activity'])
        > SESSION_IDLE_TIMEOUT
) {
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        sctExpireCurrentSessionCookie();
        session_destroy();
    }

    // La misma solicitud necesita una sesión anónima limpia para poder
    // regenerar CSRF en páginas públicas, sin recuperar el ID expirado.
    session_id('');
    session_start();
}

$_SESSION['last_activity'] = $now;

/* =========================================================
   3. CABECERAS DE SEGURIDAD
   ========================================================= */

function aplicarCabecerasSeguridad(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header(
        'Referrer-Policy: strict-origin-when-cross-origin'
    );
}

/* =========================================================
   4. VERSIONADO DE ASSETS
   ========================================================= */

$ASSET_VERSION = SCT_ASSET_VERSION;
