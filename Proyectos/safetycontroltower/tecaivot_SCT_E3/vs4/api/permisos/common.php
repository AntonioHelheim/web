<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/repositorios/PermisoRepository.php';

function permisosRequireManageApi(PDO $pdo): array
{
    requireCapability($pdo, 'permissions.manage');
    return currentUserAccessContext($pdo);
}

function permisosRequireManagePage(PDO $pdo, string $redirectTo='../../acceso-denegado.php'): array
{
    requireCapabilityPage($pdo, 'permissions.manage', $redirectTo);
    $ctx = resolveCurrentUserAccessContext($pdo);
    if (!$ctx) { header('Location: '.$redirectTo); exit; }
    return $ctx;
}

function permisosReadJson(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return $_POST;
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

function permisosAssertCompanyScope(array $context, int $companyId): void
{
    if ($companyId <= 0) responderJSON(false, null, 'Empresa inválida.', 400);
    if (empty($context['is_global_admin']) && $companyId !== (int)$context['company_id']) {
        responderJSON(false, null, 'No puedes administrar permisos de otra empresa.', 403);
    }
}

function permisosAssertRoleScope(array $context, array $role): void
{
    permisosAssertCompanyScope($context, (int)$role['id_company']);
    $targetLevel = roleLevelFromName((string)($role['canonical_name'] ?? $role['name'] ?? ''));
    if ($targetLevel === null) responderJSON(false, null, 'Perfil de acceso inválido.', 400);
    if (!empty($context['is_global_admin'])) return;
    // Gerente sólo puede modificar matrices de perfiles inferiores: admin y perfiles operativos.
    if (!in_array($targetLevel, authManagedUserLevels($context), true)) {
        responderJSON(false, null, 'No puedes modificar la matriz de este perfil.', 403);
    }
}
