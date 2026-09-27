<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/repositorios/PermisoRepository.php';

function permisosRequireManageApi(PDO $pdo): void
{
    requireCapability($pdo, 'permissions.manage');
}

function permisosRequireManagePage(PDO $pdo, string $redirectTo='../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'permissions.manage', $redirectTo);
}

function permisosReadJson(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return $_POST;
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}
