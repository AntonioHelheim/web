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

/** Runtime helpers with safe fallbacks for hosting environments where mbstring
 * is not enabled. SCT still prefers mbstring when available. */
function sctTextLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function sctTextSubstr(string $value, int $start, ?int $length = null): string
{
    if (function_exists('mb_substr')) {
        return $length === null
            ? mb_substr($value, $start, null, 'UTF-8')
            : mb_substr($value, $start, $length, 'UTF-8');
    }
    return $length === null ? substr($value, $start) : substr($value, $start, $length);
}

function sctTextUpper(string $value): string
{
    return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
}

function sctStartsWith(string $value, string $prefix): bool
{
    if ($prefix === '') {
        return true;
    }
    return strncmp($value, $prefix, strlen($prefix)) === 0;
}

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
            'audits.manage',
            'self_assessments.manage',
            'dynamic_forms.manage',
            'protocols.manage',
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
            'audits.manage',
            'self_assessments.manage',
            'dynamic_forms.manage',
            'protocols.manage',
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
            'audits.manage',
            'self_assessments.manage',
            'dynamic_forms.manage',
            'protocols.manage',
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
            'audits.manage',
            'self_assessments.manage',
            'dynamic_forms.manage',
            'protocols.manage',
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

/** Permisos de acción de Etapa 3 usados como fallback seguro antes de activar la matriz BD. */
const SCT_GRANULAR_PERMISSION_DEFAULTS = [
    'companies.manage_all' => ['administrador_completo'],
    'companies.view_all' => ['administrador_completo'],
    'companies.edit_all' => ['administrador_completo'],
    'companies.view_own' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'companies.edit_own' => ['administrador_completo', 'administrador', 'cliente'],
    'companies.create_all' => ['administrador_completo'],
    'companies.state_all' => ['administrador_completo'],
    'users.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'users.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'users.assign_roles' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'users.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'users.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'users.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'users.access' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'users.photo' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'workers.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'projects.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'centers.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'events.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'induction.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'audits.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'self_assessments.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'dynamic_forms.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'protocols.manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'questions.manage' => ['administrador_completo', 'administrador'],
    'workers.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'workers.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'workers.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'workers.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'workers.photo' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'projects.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'projects.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'projects.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'projects.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'projects.assign_workers' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'centers.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'centers.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'centers.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'centers.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'events.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'events.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'events.evidence_upload' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'events.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'events.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'events.evidence_manage' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'events.tracking' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'induction.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'induction.execute' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'induction.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'induction.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'induction.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'induction.assign' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'induction.materials' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'induction.questions' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'audits.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'audits.execute' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'audits.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'audits.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'audits.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'audits.assign' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'audits.questions' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'self_assessments.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'self_assessments.execute' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'self_assessments.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'self_assessments.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'self_assessments.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'self_assessments.assign' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'self_assessments.questions' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'dynamic_forms.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'dynamic_forms.submit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'dynamic_forms.files' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'dynamic_forms.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'dynamic_forms.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'dynamic_forms.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'dynamic_forms.fields' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'dynamic_forms.submissions' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'protocols.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'protocols.execute' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'protocols.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'protocols.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'protocols.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'protocols.forms' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'protocols.assign' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'protocols.review' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'protocols.tracking' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'dashboard.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'dashboard.global' => ['administrador_completo'],
    'permissions.manage' => ['administrador_completo'],
    'change_history.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'change_history.global' => ['administrador_completo'],
    'programs.view' => ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'],
    'programs.create' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'programs.edit' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'programs.state' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
    'programs.tracking' => ['administrador_completo', 'administrador', 'cliente', 'jefatura'],
];

const SCT_LEGACY_CAPABILITY_EXPANSIONS = [
    'companies.manage_all' => ['companies.view_all','companies.edit_all','companies.create_all','companies.state_all'],
    'users.manage' => ['users.create','users.assign_roles','users.edit','users.state','users.access','users.photo'],
    'workers.manage' => ['workers.create','workers.edit','workers.state','workers.photo'],
    'projects.manage' => ['projects.create','projects.edit','projects.state','projects.assign_workers'],
    'centers.manage' => ['centers.create','centers.edit','centers.state'],
    'events.manage' => ['events.edit','events.state','events.evidence_manage','events.tracking'],
    'induction.manage' => ['induction.create','induction.edit','induction.state','induction.assign','induction.materials','induction.questions'],
    'audits.manage' => ['audits.create','audits.edit','audits.state','audits.assign','audits.questions'],
    'self_assessments.manage' => ['self_assessments.create','self_assessments.edit','self_assessments.state','self_assessments.assign','self_assessments.questions'],
    'dynamic_forms.manage' => ['dynamic_forms.create','dynamic_forms.edit','dynamic_forms.state','dynamic_forms.fields','dynamic_forms.submissions'],
    'protocols.manage' => ['protocols.create','protocols.edit','protocols.state','protocols.forms','protocols.assign','protocols.review','protocols.tracking'],
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
    $normalized = function_exists('mb_strtolower')
        ? mb_strtolower(trim($roleName), 'UTF-8')
        : strtolower(trim($roleName));
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

/**
 * Comprueba de forma cacheada si una columna existe en la base actual.
 *
 * Esto evita que una diferencia temporal entre el esquema local y el de
 * desarrollo web derribe toda una página con HTTP 500 durante un despliegue.
 * Las migraciones siguen siendo obligatorias para habilitar la funcionalidad,
 * pero la lectura puede degradar de forma segura mientras se sincroniza BD.
 */
function authColumnExists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
        return $cache[$key] = false;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = :column_name'
        );
        $stmt->execute([
            'table_name' => $table,
            'column_name' => $column,
        ]);
        return $cache[$key] = ((int) $stmt->fetchColumn() > 0);
    } catch (Throwable $e) {
        error_log('lib/auth.php authColumnExists: ' . $e->getMessage());
        return $cache[$key] = false;
    }
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

    $photoSelect = authColumnExists($pdo, 'users', 'profile_photo_path')
        ? 'profile_photo_path'
        : 'NULL AS profile_photo_path';

    $stmt = $pdo->prepare(
        'SELECT id_users, id_company, name, lastname, language, ' . $photoSelect . ', state
'
        . 'FROM users
'
        . 'WHERE id_users = :id_users
'
        . 'LIMIT 1'
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
        'can_create_user' => currentUserHasCapability($pdo, 'users.create'),
        'can_view_users' => currentUserHasCapability($pdo, 'users.view'),
        'can_manage_users' => currentUserHasCapability($pdo, 'users.edit'),
        'can_assign_roles' => currentUserHasCapability($pdo, 'users.assign_roles'),
        'can_state_users' => currentUserHasCapability($pdo, 'users.state'),
        'can_access_users' => currentUserHasCapability($pdo, 'users.access'),
        'can_photo_users' => currentUserHasCapability($pdo, 'users.photo'),
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

    if (in_array($capability, SCT_ROLE_POLICY[$role]['capabilities'], true)) {
        return true;
    }

    return isset(SCT_GRANULAR_PERMISSION_DEFAULTS[$capability])
        && in_array($role, SCT_GRANULAR_PERMISSION_DEFAULTS[$capability], true);
}

/**
 * El marcador se crea al final de la migración. Si no existe, el sistema
 * mantiene la política compatible de Etapas 1/2 para permitir despliegues
 * código→SQL sin dejar usuarios bloqueados.
 */
function authPermissionsDatabaseEnabled(PDO $pdo): bool
{
    static $enabled = null;
    if ($enabled !== null) {
        return $enabled;
    }

    try {
        $stmt = $pdo->prepare('SELECT id_permission FROM permissions WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => 'system.permissions.enabled']);
        return $enabled = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('lib/auth.php authPermissionsDatabaseEnabled: ' . $e->getMessage());
        return $enabled = false;
    }
}

/** @return array<int,string> */
function currentUserDatabaseCapabilities(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $idUsers = currentUserId();
    if (!$idUsers) {
        return $cache = [];
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT DISTINCT p.code
             FROM users u
             INNER JOIN users_role ur
                ON ur.id_users = u.id_users AND ur.state = 1
             INNER JOIN users_role_group g
                ON g.id_role_group = ur.id_role_group
               AND g.id_company = u.id_company
               AND g.state = 1
             INNER JOIN role_permissions rp
                ON rp.id_role_group = g.id_role_group
             INNER JOIN permissions p
                ON p.id_permission = rp.id_permission
             WHERE u.id_users = :id_users
               AND u.state = 1
               AND p.code <> :marker'
        );
        $stmt->execute([
            'id_users' => $idUsers,
            'marker' => 'system.permissions.enabled',
        ]);
        $codes = [];
        foreach ($stmt->fetchAll() as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code !== '') $codes[] = $code;
        }
        return $cache = array_values(array_unique($codes));
    } catch (Throwable $e) {
        // Con el sistema ya activado, un fallo de lectura debe negar permisos,
        // no volver silenciosamente a una política más amplia.
        error_log('lib/auth.php currentUserDatabaseCapabilities: ' . $e->getMessage());
        return $cache = [];
    }
}

function currentUserHasCapability(PDO $pdo, string $capability): bool
{
    if (authPermissionsDatabaseEnabled($pdo)) {
        $codes = currentUserDatabaseCapabilities($pdo);
        if (in_array($capability, $codes, true)) {
            return true;
        }
        foreach (SCT_LEGACY_CAPABILITY_EXPANSIONS[$capability] ?? [] as $granular) {
            if (in_array($granular, $codes, true)) {
                return true;
            }
        }
        return false;
    }

    foreach (currentUserRoles($pdo) as $role) {
        if (authRoleHasCapability($role, $capability)) {
            return true;
        }
    }
    return false;
}

function currentUserHasAnyCapability(PDO $pdo, array $capabilities): bool
{
    foreach ($capabilities as $capability) {
        if (currentUserHasCapability($pdo, (string) $capability)) {
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
    if (array_key_exists('can_create_user', $context)) {
        return (bool) $context['can_create_user'];
    }
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

    if (array_key_exists('can_view_users', $context) && empty($context['can_view_users'])) {
        return false;
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
    if (array_key_exists('can_assign_roles', $context) && empty($context['can_assign_roles'])) {
        return false;
    }
    return $targetLevel !== null && in_array($targetLevel, authAssignableUserLevels($context), true);
}

function authEditableUserFields(array $context, array $target): array
{
    $isSelf = (string) ($target['id_users'] ?? '') === (string) ($context['session_user_id'] ?? '');
    $actorLevel = (int) ($context['actor_level'] ?? 0);

    if ($isSelf && $actorLevel === 5) {
        return ['name', 'lastname', 'language', 'profile_photo_path'];
    }

    if (!authCanManageUserTarget($context, $target)
        || (array_key_exists('can_manage_users', $context) && empty($context['can_manage_users']))) {
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

    if (function_exists('mb_convert_case') && defined('MB_CASE_TITLE')) {
        return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(strtolower($texto));
}
