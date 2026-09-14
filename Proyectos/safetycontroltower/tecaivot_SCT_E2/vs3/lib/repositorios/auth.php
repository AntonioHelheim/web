<?php
/**
 * lib/auth.php
 * Autenticación y autorización centralizadas para Safety Control Tower.
 *
 * Este archivo es la única fuente de verdad para:
 * - sesión activa;
 * - roles activos del usuario;
 * - jerarquía de roles;
 * - alcance multiempresa;
 * - política de gestión de usuarios;
 * - capacidades funcionales básicas de Etapa 1.
 *
 * Etapa 3 podrá reemplazar gradualmente esta política estática por
 * permissions/role_permissions sin cambiar la firma de los helpers.
 */

require_once __DIR__ . '/response.php';

const SCT_ROLE_POLICY = [
    'administrador_completo' => [
        'level' => 1,
        'global_scope' => true,
        'managed_user_levels' => [1, 2, 3, 4, 5],
        'assignable_user_levels' => [1, 2, 3, 4, 5],
        'capabilities' => [
            'companies.manage_all',
            'companies.view_all',
            'companies.edit_all',
            'users.manage',
            'users.create',
            'users.assign_roles',
            'workers.manage',
            'projects.manage',
            'centers.manage',
            'events.manage',
            'induction.manage',
            'questions.manage',
        ],
    ],
    'administrador' => [
        'level' => 2,
        'global_scope' => false,
        'managed_user_levels' => [2, 3, 4, 5],
        'assignable_user_levels' => [2, 3, 4, 5],
        'capabilities' => [
            'companies.view_own',
            'companies.edit_own',
            'users.manage',
            'users.create',
            'users.assign_roles',
            'workers.manage',
            'projects.manage',
            'centers.manage',
            'events.manage',
            'induction.manage',
            'questions.manage',
        ],
    ],
    'cliente' => [
        'level' => 3,
        'global_scope' => false,
        'managed_user_levels' => [4, 5],
        'assignable_user_levels' => [4, 5],
        'capabilities' => [
            'companies.view_own',
            'companies.edit_own',
            'users.manage',
            'users.create',
            'users.assign_roles',
            'workers.manage',
            'projects.manage',
            'centers.manage',
            'events.manage',
            'induction.manage',
        ],
    ],
    'jefatura' => [
        'level' => 4,
        'global_scope' => false,
        'managed_user_levels' => [5],
        'assignable_user_levels' => [5],
        'capabilities' => [
            'companies.view_own',
            'users.manage',
            'users.create',
            'users.assign_roles',
            'workers.manage',
            'projects.manage',
            'centers.manage',
            'events.manage',
            'induction.manage',
        ],
    ],
    'trabajador' => [
        'level' => 5,
        'global_scope' => false,
        'managed_user_levels' => [],
        'assignable_user_levels' => [],
        'capabilities' => [
            'companies.view_own',
        ],
    ],
];

function requireLogin(): void
{
    if (empty($_SESSION['logged_in'])) {
        responderJSON(false, null, 'Debes iniciar sesión para continuar.', 401);
    }
}

function requireLoginPage(string $redirectTo = 'acceso-denegado.php'): void
{
    if (empty($_SESSION['logged_in'])) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function currentUserId(): ?string
{
    $id = $_SESSION['user_id'] ?? $_SESSION['user_email'] ?? null;
    return is_string($id) && trim($id) !== '' ? trim($id) : null;
}

function canonicalRoleName(string $roleName): string
{
    $normalized = mb_strtolower(trim($roleName), 'UTF-8');
    $normalized = str_replace(['-', ' '], '_', $normalized);
    return $normalized === 'admin_completo' ? 'administrador_completo' : $normalized;
}

function roleLevelFromName(string $roleName): ?int
{
    $key = canonicalRoleName($roleName);
    return isset(SCT_ROLE_POLICY[$key]) ? (int) SCT_ROLE_POLICY[$key]['level'] : null;
}

function primaryRoleName(array $roleNames): ?string
{
    $bestName = null;
    $bestLevel = PHP_INT_MAX;

    foreach ($roleNames as $roleName) {
        $canonical = canonicalRoleName((string) $roleName);
        $level = roleLevelFromName($canonical);
        if ($level !== null && $level < $bestLevel) {
            $bestLevel = $level;
            $bestName = $canonical;
        }
    }

    return $bestName;
}

function roleDisplayLabel(string $roleName): string
{
    static $labels = [
        'administrador_completo' => 'Administrador Completo',
        'administrador' => 'Administrador de Empresa',
        'cliente' => 'Gerente de Empresa',
        'jefatura' => 'Jefatura de Empresa',
        'trabajador' => 'Trabajador',
    ];

    $key = canonicalRoleName($roleName);
    return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
}

function currentUserRoles(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $idUsers = currentUserId();
    if (!$idUsers) {
        return $cache = [];
    }

    $stmt = $pdo->prepare(
        'SELECT DISTINCT g.name
         FROM users u
         INNER JOIN users_role ur
            ON ur.id_users = u.id_users
           AND ur.state = 1
         INNER JOIN users_role_group g
            ON g.id_role_group = ur.id_role_group
           AND g.state = 1
           AND g.id_company = u.id_company
         WHERE u.id_users = :id_users
           AND u.state = 1'
    );
    $stmt->execute(['id_users' => $idUsers]);

    $roles = [];
    foreach ($stmt->fetchAll() as $row) {
        $role = canonicalRoleName((string) ($row['name'] ?? ''));
        if ($role !== '' && isset(SCT_ROLE_POLICY[$role])) {
            $roles[] = $role;
        }
    }

    return $cache = array_values(array_unique($roles));
}

function currentUserCompanyId(PDO $pdo): ?int
{
    $idUsers = currentUserId();
    if (!$idUsers) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id_company FROM users WHERE id_users = :id_users AND state = 1 LIMIT 1');
    $stmt->execute(['id_users' => $idUsers]);
    $row = $stmt->fetch();

    return $row ? (int) $row['id_company'] : null;
}

function currentUserProfile(PDO $pdo): ?array
{
    static $cache = null;
    static $consulted = false;

    if ($consulted) {
        return $cache;
    }
    $consulted = true;

    $idUsers = currentUserId();
    if (!$idUsers) {
        return $cache = null;
    }

    $stmt = $pdo->prepare(
        'SELECT id_users, id_company, name, lastname, language, profile_photo_path, state
         FROM users
         WHERE id_users = :id_users
         LIMIT 1'
    );
    $stmt->execute(['id_users' => $idUsers]);
    $row = $stmt->fetch();

    return $cache = ($row ?: null);
}

function resolveCurrentUserAccessContext(PDO $pdo): ?array
{
    $profile = currentUserProfile($pdo);
    if (!$profile || (int) ($profile['state'] ?? 0) !== 1) {
        return null;
    }

    $roles = currentUserRoles($pdo);
    $primaryRole = primaryRoleName($roles);
    $level = $primaryRole ? roleLevelFromName($primaryRole) : null;

    if ($primaryRole === null || $level === null) {
        return null;
    }

    return [
        'session_user_id' => (string) $profile['id_users'],
        'company_id' => (int) $profile['id_company'],
        'actor_roles' => $roles,
        'primary_role' => $primaryRole,
        'actor_level' => $level,
        'actor_label' => roleDisplayLabel($primaryRole),
        'is_global_admin' => (bool) SCT_ROLE_POLICY[$primaryRole]['global_scope'],
        'can_create_user' => authRoleHasCapability($primaryRole, 'users.create'),
        'profile' => $profile,
    ];
}

function currentUserAccessContext(PDO $pdo): array
{
    requireLogin();
    $context = resolveCurrentUserAccessContext($pdo);
    if ($context === null) {
        responderJSON(false, null, 'Tu cuenta no tiene un rol activo válido.', 403);
    }
    return $context;
}

function authRoleHasCapability(string $roleName, string $capability): bool
{
    $role = canonicalRoleName($roleName);
    if (!isset(SCT_ROLE_POLICY[$role])) {
        return false;
    }

    return in_array($capability, SCT_ROLE_POLICY[$role]['capabilities'], true);
}

function currentUserHasCapability(PDO $pdo, string $capability): bool
{
    foreach (currentUserRoles($pdo) as $role) {
        if (authRoleHasCapability($role, $capability)) {
            return true;
        }
    }
    return false;
}

function requireCapability(PDO $pdo, string $capability): void
{
    requireLogin();
    if (!currentUserHasCapability($pdo, $capability)) {
        responderJSON(false, null, 'No tienes permisos para realizar esta acción.', 403);
    }
}

function requireCapabilityPage(PDO $pdo, string $capability, string $redirectTo = 'acceso-denegado.php'): void
{
    requireLoginPage($redirectTo);
    if (!currentUserHasCapability($pdo, $capability)) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function requireRole(PDO $pdo, array $rolesPermitidos): void
{
    requireLogin();
    $allowed = array_map('canonicalRoleName', $rolesPermitidos);
    if (count(array_intersect(currentUserRoles($pdo), $allowed)) === 0) {
        responderJSON(false, null, 'No tienes permisos para realizar esta acción.', 403);
    }
}

function requireRolePage(PDO $pdo, array $rolesPermitidos, string $redirectTo = 'acceso-denegado.php'): void
{
    requireLoginPage($redirectTo);
    $allowed = array_map('canonicalRoleName', $rolesPermitidos);
    if (count(array_intersect(currentUserRoles($pdo), $allowed)) === 0) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function authManagedUserLevels(array $context): array
{
    $role = canonicalRoleName((string) ($context['primary_role'] ?? ''));
    return isset(SCT_ROLE_POLICY[$role]) ? SCT_ROLE_POLICY[$role]['managed_user_levels'] : [];
}

function authAssignableUserLevels(array $context): array
{
    $role = canonicalRoleName((string) ($context['primary_role'] ?? ''));
    return isset(SCT_ROLE_POLICY[$role]) ? SCT_ROLE_POLICY[$role]['assignable_user_levels'] : [];
}

function authCanCreateUsers(array $context): bool
{
    $role = canonicalRoleName((string) ($context['primary_role'] ?? ''));
    return authRoleHasCapability($role, 'users.create');
}

function authCanViewUserTarget(array $context, array $target): bool
{
    $actorLevel = (int) ($context['actor_level'] ?? 0);
    $actorCompany = (int) ($context['company_id'] ?? 0);
    $targetCompany = (int) ($target['id_company'] ?? 0);
    $targetLevel = isset($target['access_level']) ? (int) $target['access_level'] : null;
    $isSelf = (string) ($target['id_users'] ?? '') === (string) ($context['session_user_id'] ?? '');

    if (!empty($context['is_global_admin'])) {
        return true;
    }

    if ($isSelf) {
        return true;
    }

    if ($actorCompany <= 0 || $actorCompany !== $targetCompany) {
        return false;
    }

    if ($actorLevel === 5) {
        return false;
    }

    if ($targetLevel === null) {
        return false;
    }

    return in_array($targetLevel, authManagedUserLevels($context), true);
}

function authCanManageUserTarget(array $context, array $target): bool
{
    $actorCompany = (int) ($context['company_id'] ?? 0);
    $targetCompany = (int) ($target['id_company'] ?? 0);
    $targetLevel = isset($target['access_level']) ? (int) $target['access_level'] : null;

    if (empty($context['is_global_admin']) && $actorCompany !== $targetCompany) {
        return false;
    }

    if ($targetLevel === null) {
        return !empty($context['is_global_admin']);
    }

    return in_array($targetLevel, authManagedUserLevels($context), true);
}

function authCanAssignUserLevel(array $context, ?int $targetLevel): bool
{
    return $targetLevel !== null && in_array($targetLevel, authAssignableUserLevels($context), true);
}

function authEditableUserFields(array $context, array $target): array
{
    $isSelf = (string) ($target['id_users'] ?? '') === (string) ($context['session_user_id'] ?? '');
    $actorLevel = (int) ($context['actor_level'] ?? 0);

    if ($isSelf && $actorLevel === 5) {
        return ['name', 'lastname', 'language', 'profile_photo_path'];
    }

    if (!authCanManageUserTarget($context, $target)) {
        return $isSelf ? ['name', 'lastname', 'language', 'profile_photo_path'] : [];
    }

    $fields = ['name', 'lastname', 'rut', 'language', 'id_role_group', 'profile_photo_path'];
    if (!empty($context['is_global_admin'])) {
        $fields[] = 'id_company';
    }

    return $fields;
}

function requireCsrfToken(array $input, string $field = 'csrf_token'): void
{
    $csrfToken = (string) ($input[$field] ?? '');
    if (empty($_SESSION['csrf_token']) || $csrfToken === '' || !hash_equals((string) $_SESSION['csrf_token'], $csrfToken)) {
        responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
    }
}

function capitalizarNombre(string $texto): string
{
    $texto = trim($texto);
    if ($texto === '') {
        return $texto;
    }

    return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
}
