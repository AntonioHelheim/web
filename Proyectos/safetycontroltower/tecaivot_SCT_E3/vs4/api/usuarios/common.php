<?php
/**
 * Helpers de dominio para Gestión de Usuarios.
 *
 * IMPORTANTE: la autorización ya no vive en este módulo. Todas las reglas
 * de sesión, jerarquía de roles, alcance multiempresa y permisos están en
 * lib/auth.php. Este archivo se limita a lectura de request y consultas de
 * usuarios/roles.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/validation.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../app/Users/UserRepository.php';

function usuariosReadJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return $_POST;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

function usuariosRequireCsrf(array $input): void
{
    requireCsrfToken($input);
}

function usuariosRequireAccessContext(PDO $pdo): array
{
    return currentUserAccessContext($pdo);
}

function usuariosRoleLevelFromName(string $name): ?int
{
    return roleLevelFromName($name);
}

function usuariosRoleNameFromLevel(?int $level): ?string
{
    if ($level === null) {
        return null;
    }
    foreach (SCT_ROLE_POLICY as $name => $policy) {
        if ((int) $policy['level'] === $level) {
            return $name;
        }
    }
    return null;
}

function usuariosRoleLabelFromLevel(?int $level): string
{
    $name = usuariosRoleNameFromLevel($level);
    if ($name === null) {
        return t('role_none');
    }
    $key = 'role_' . $name;
    $translated = t($key);
    return $translated === $key ? roleDisplayLabel($name) : $translated;
}

function usuariosResolveAccessLevelFromRoles(array $roleNames): ?int
{
    $primary = primaryRoleName($roleNames);
    return $primary ? roleLevelFromName($primary) : null;
}

function usuariosAllowedManagedLevels(int $actorLevel): array
{
    foreach (SCT_ROLE_POLICY as $policy) {
        if ((int) $policy['level'] === $actorLevel) {
            return $policy['managed_user_levels'];
        }
    }
    return [];
}

function usuariosAllowedAssignableLevels(int $actorLevel): array
{
    foreach (SCT_ROLE_POLICY as $policy) {
        if ((int) $policy['level'] === $actorLevel) {
            return $policy['assignable_user_levels'];
        }
    }
    return [];
}

function usuariosCanCreateUsers(array $context): bool
{
    return authCanCreateUsers($context);
}

function usuariosCanViewTarget(PDO $pdo, array $context, array $target): bool
{
    return authCanViewUserTarget($context, $target, $pdo);
}

function usuariosCanManageTarget(PDO $pdo, array $context, array $target): bool
{
    return authCanManageUserTarget($context, $target, $pdo);
}

function usuariosCanAssignLevel(array $context, ?int $targetLevel): bool
{
    return authCanAssignUserLevel($context, $targetLevel);
}

function usuariosGetEditableFieldsForTarget(PDO $pdo, array $context, array $target): array
{
    return authEditableUserFields($context, $target, $pdo);
}

function usuariosCanAssignSupervision(array $context): bool
{
    return authCanAssignUserSupervision($context);
}

function usuariosSupervisionCandidates(
    PDO $pdo,
    array $context,
    int $companyId
): array {
    if (!usuariosCanAssignSupervision($context)) {
        return [];
    }

    if (
        empty($context['is_global_admin'])
        && $companyId !== (int)$context['company_id']
    ) {
        return [];
    }

    return usuariosRepository($pdo)->supervisionCandidates($companyId);
}

function usuariosAssignedSupervisors(
    PDO $pdo,
    string $targetUserId,
    int $companyId
): array {
    return usuariosRepository($pdo)->assignedSupervisors(
        $targetUserId,
        $companyId
    );
}

function usuariosRepository(PDO $pdo): SctUserRepository
{
    static $instances = [];
    $key = spl_object_id($pdo);
    return $instances[$key] ??= new SctUserRepository($pdo);
}

function usuariosFindCompany(PDO $pdo, int $companyId): ?array
{
    return usuariosRepository($pdo)->findCompany($companyId);
}

function usuariosListVisibleCompanies(PDO $pdo, array $context): array
{
    $rows = usuariosRepository($pdo)->visibleCompanies(!empty($context['is_global_admin']), (int)$context['company_id']);
    return array_map(static fn(array $row): array => [
        'id_company' => (int)$row['id_company'],
        'razon_social' => (string)$row['razon_social'],
    ], $rows);
}

function usuariosFindAssignableRoles(PDO $pdo, array $context, ?int $companyId = null): array
{
    $allowedLevels = authAssignableUserLevels($context);
    if (!$allowedLevels) return [];
    $targetCompany = !empty($context['is_global_admin']) ? $companyId : (int)$context['company_id'];
    if ($targetCompany === null || $targetCompany <= 0) return [];
    $result = [];
    foreach (usuariosRepository($pdo)->listRoleGroupsForCompany((int)$targetCompany) as $row) {
        $roleName = canonicalRoleName((string)$row['name']);
        $level = roleLevelFromName($roleName);
        if ($level === null || !in_array($level, $allowedLevels, true)) continue;
        $result[] = [
            'id_role_group' => (int)$row['id_role_group'],
            'id_company' => (int)$row['id_company'],
            'name' => $roleName,
            'description' => (string)($row['description'] ?? ''),
            'access_level' => $level,
            'access_label' => usuariosRoleLabelFromLevel($level),
        ];
    }
    return $result;
}

function usuariosFindActiveRole(PDO $pdo, int $companyId, int $roleGroupId): ?array
{
    return usuariosRepository($pdo)->activeRole($companyId, $roleGroupId);
}

function usuariosParseRoleNames(string $raw): array
{
    if (trim($raw) === '' || trim($raw) === 'Sin rol') {
        return [];
    }
    $roles = [];
    foreach (explode(',', $raw) as $item) {
        $role = canonicalRoleName(trim($item));
        if ($role !== '') {
            $roles[] = $role;
        }
    }
    return array_values(array_unique($roles));
}

function usuariosFindUserWithAccess(PDO $pdo, string $userId): ?array
{
    $row = usuariosRepository($pdo)->findUserWithAccess($userId);
    if (!$row) return null;

    $roles = usuariosParseRoleNames((string)($row['role_name'] ?? ''));
    $primaryRole = primaryRoleName($roles);
    $level = $primaryRole ? roleLevelFromName($primaryRole) : null;
    $credentialStatus = strtolower((string)($row['credential_status'] ?? 'pending_activation'));
    if (!in_array($credentialStatus, ['pending_activation','reset_required','active'], true)) {
        $credentialStatus = 'pending_activation';
    }

    $row['role_names'] = $roles;
    $row['primary_role'] = $primaryRole;
    $row['access_level'] = $level;
    $row['access_label'] = usuariosRoleLabelFromLevel($level);
    $row['password_configured'] = (bool)((int)($row['password_configured'] ?? 0));
    $row['credential_status'] = $credentialStatus;
    $row['access_status'] = $credentialStatus;
    return $row;
}
