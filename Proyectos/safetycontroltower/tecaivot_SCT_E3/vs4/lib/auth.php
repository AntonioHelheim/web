<?php
/**
 * Safety Control Tower E3-VS1 — compatibility facade for authentication/authorization.
 *
 * New code lives under app/Auth. Existing modules keep the stable helper API from
 * lib/auth.php while responsibilities are separated behind services.
 */
require_once __DIR__ . '/../app/Config/version.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/../app/Auth/RolePolicy.php';
require_once __DIR__ . '/../app/Auth/SessionService.php';
require_once __DIR__ . '/../app/Auth/UserContext.php';
require_once __DIR__ . '/../app/Auth/AuthorizationService.php';
require_once __DIR__ . '/onboarding_gate.php';

if (!defined('SCT_ROLE_POLICY')) define('SCT_ROLE_POLICY', SctRolePolicy::ROLES);

function sctTextLength(string $value): int { return function_exists('mb_strlen') ? mb_strlen($value,'UTF-8') : strlen($value); }
function sctTextSubstr(string $value,int $start,?int $length=null): string {
    if (function_exists('mb_substr')) return $length===null ? mb_substr($value,$start,null,'UTF-8') : mb_substr($value,$start,$length,'UTF-8');
    return $length===null ? substr($value,$start) : substr($value,$start,$length);
}
function sctTextUpper(string $value): string { return function_exists('mb_strtoupper') ? mb_strtoupper($value,'UTF-8') : strtoupper($value); }
function sctStartsWith(string $value,string $prefix): bool { return $prefix==='' || strncmp($value,$prefix,strlen($prefix))===0; }

function sctAuthorization(PDO $pdo): SctAuthorizationService
{
    static $instances=[]; $key=spl_object_id($pdo);
    return $instances[$key] ??= new SctAuthorizationService($pdo);
}

function requireLogin(): void
{
    if (empty($_SESSION['logged_in'])) responderJSON(false,null,'Debes iniciar sesión para continuar.',401);
    global $pdo;
    if ($pdo instanceof PDO) {
        $id=currentUserId();
        if (!$id || sctAuthorization($pdo)->resolveContext($id)===null) {
            responderJSON(false,null,'Tu cuenta no tiene un perfil de acceso activo válido.',403);
        }
        if (!onboardingGateIsExemptRequest() && onboardingGateBlocked($pdo)) {
            responderJSON(false,['redirect'=>onboardingGateUrl($pdo)],onboardingGateMessage(),423);
        }
    }
}

function requireLoginPage(string $redirectTo='acceso-denegado.php'): void
{
    if (empty($_SESSION['logged_in'])) { header('Location: '.$redirectTo); exit; }
    global $pdo;
    if ($pdo instanceof PDO) {
        $id=currentUserId();
        if (!$id || sctAuthorization($pdo)->resolveContext($id)===null) { header('Location: '.$redirectTo); exit; }
        if (!onboardingGateIsExemptRequest() && onboardingGateBlocked($pdo)) { header('Location: '.onboardingGateUrl($pdo)); exit; }
    }
}

function currentUserId(): ?string { return SctSessionService::currentUserId(); }
function canonicalRoleName(string $roleName): string { return SctRolePolicy::canonical($roleName); }
function roleLevelFromName(string $roleName): ?int { return SctRolePolicy::level($roleName); }
function primaryRoleName(array $roleNames): ?string { return SctRolePolicy::primary($roleNames); }
function roleDisplayLabel(string $roleName): string { return SctRolePolicy::label($roleName); }

function currentUserRoles(PDO $pdo): array
{
    static $cache=[]; $id=currentUserId(); if(!$id) return [];
    $key=spl_object_id($pdo).'|'.$id;
    return $cache[$key] ??= sctAuthorization($pdo)->rolesForUser($id);
}

function currentUserCompanyId(PDO $pdo): ?int
{
    $id=currentUserId(); if(!$id) return null;
    $ctx=sctAuthorization($pdo)->resolveContext($id); return $ctx !== null ? $ctx->companyId : null;
}

function authColumnExists(PDO $pdo,string $table,string $column): bool
{
    static $cache=[];$key=$table.'.'.$column;if(array_key_exists($key,$cache))return $cache[$key];
    if(!preg_match('/^[A-Za-z0-9_]+$/',$table)||!preg_match('/^[A-Za-z0-9_]+$/',$column))return $cache[$key]=false;
    try{$stmt=$pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table_name AND COLUMN_NAME=:column_name');$stmt->execute(['table_name'=>$table,'column_name'=>$column]);return $cache[$key]=((int)$stmt->fetchColumn()>0);}catch(Throwable $e){error_log('authColumnExists: '.$e->getMessage());return $cache[$key]=false;}
}

function currentUserProfile(PDO $pdo): ?array
{
    static $cache=[];$id=currentUserId();if(!$id)return null;$key=spl_object_id($pdo).'|'.$id;if(array_key_exists($key,$cache))return $cache[$key];
    $photo=authColumnExists($pdo,'users','profile_photo_path')?'profile_photo_path':'NULL AS profile_photo_path';
    $mutual=authColumnExists($pdo,'users','mutual_code')?'mutual_code':'NULL AS mutual_code';
    $stmt=$pdo->prepare('SELECT id_users,id_company,id_worker,name,lastname,language,'.$photo.','.$mutual.',state FROM users WHERE id_users=:id_users LIMIT 1');
    $stmt->execute(['id_users'=>$id]);$row=$stmt->fetch();return $cache[$key]=$row?:null;
}

function resolveCurrentUserAccessContext(PDO $pdo): ?array
{
    $id=currentUserId();if(!$id)return null;$ctx=sctAuthorization($pdo)->resolveContext($id);if(!$ctx)return null;
    $result=$ctx->toArray();
    $result += [
        'can_create_user'=>currentUserHasCapability($pdo,'users.create'),
        'can_view_users'=>currentUserHasCapability($pdo,'users.view'),
        'can_manage_users'=>currentUserHasCapability($pdo,'users.edit'),
        'can_assign_roles'=>currentUserHasCapability($pdo,'users.assign_roles'),
        'can_state_users'=>currentUserHasCapability($pdo,'users.state'),
        'can_access_users'=>currentUserHasCapability($pdo,'users.access'),
        'can_photo_users'=>currentUserHasCapability($pdo,'users.photo'),
    ];
    return $result;
}

function currentUserAccessContext(PDO $pdo): array
{
    requireLogin();$context=resolveCurrentUserAccessContext($pdo);
    if($context===null)responderJSON(false,null,'Tu cuenta no tiene un perfil de acceso activo válido.',403);
    return $context;
}

function authRoleHasCapability(string $roleName,string $capability): bool { return SctRolePolicy::fallbackHas($roleName,$capability); }
function authPermissionsDatabaseEnabled(PDO $pdo): bool { return sctAuthorization($pdo)->permissionsDatabaseEnabled(); }
function currentUserDatabaseCapabilities(PDO $pdo): array { $id=currentUserId();return $id?sctAuthorization($pdo)->capabilitiesForUser($id):[]; }
function currentUserHasCapability(PDO $pdo,string $capability): bool { $id=currentUserId();return $id?sctAuthorization($pdo)->hasCapability($id,$capability):false; }
function currentUserHasAnyCapability(PDO $pdo,array $capabilities): bool { foreach($capabilities as $c)if(currentUserHasCapability($pdo,(string)$c))return true;return false; }

function requireCapability(PDO $pdo,string $capability): void { requireLogin();if(!currentUserHasCapability($pdo,$capability))responderJSON(false,null,'No tienes permisos para realizar esta acción.',403); }
function requireCapabilityPage(PDO $pdo,string $capability,string $redirectTo='acceso-denegado.php'): void { requireLoginPage($redirectTo);if(!currentUserHasCapability($pdo,$capability)){header('Location: '.$redirectTo);exit;} }
function requireRole(PDO $pdo,array $rolesPermitidos): void { requireLogin();$allowed=array_map('canonicalRoleName',$rolesPermitidos);if(count(array_intersect(currentUserRoles($pdo),$allowed))===0)responderJSON(false,null,'No tienes permisos para realizar esta acción.',403); }
function requireRolePage(PDO $pdo,array $rolesPermitidos,string $redirectTo='acceso-denegado.php'): void { requireLoginPage($redirectTo);$allowed=array_map('canonicalRoleName',$rolesPermitidos);if(count(array_intersect(currentUserRoles($pdo),$allowed))===0){header('Location: '.$redirectTo);exit;} }

function authViewableUserLevels(array $context): array
{
    $role = canonicalRoleName((string)($context['primary_role'] ?? ''));
    return SCT_ROLE_POLICY[$role]['viewable_user_levels']
        ?? SCT_ROLE_POLICY[$role]['managed_user_levels']
        ?? [];
}

function authManagedUserLevels(array $context): array
{
    $role = canonicalRoleName((string)($context['primary_role'] ?? ''));
    return SCT_ROLE_POLICY[$role]['managed_user_levels'] ?? [];
}

function authAssignableUserLevels(array $context): array
{
    $role = canonicalRoleName((string)($context['primary_role'] ?? ''));
    return SCT_ROLE_POLICY[$role]['assignable_user_levels'] ?? [];
}

function authUserScopeMode(array $context): string
{
    $role = canonicalRoleName((string)($context['primary_role'] ?? ''));
    return (string)(SCT_ROLE_POLICY[$role]['user_scope'] ?? 'self');
}

function authCanManageAssignedUsers(array $context): bool
{
    $role = canonicalRoleName((string)($context['primary_role'] ?? ''));
    return !empty(SCT_ROLE_POLICY[$role]['can_manage_assigned']);
}

function authCanAssignUserSupervision(array $context): bool
{
    $role = canonicalRoleName((string)($context['primary_role'] ?? ''));
    return in_array(
        $role,
        ['superusuario','gerente','administrador_cliente'],
        true
    );
}

function authCanAssignSupervisionForTarget(
    array $context,
    ?int $targetLevel
): bool {
    if (!authCanAssignUserSupervision($context)) {
        return false;
    }

    if ($targetLevel !== 2) {
        return true;
    }

    $role = canonicalRoleName((string)($context['primary_role'] ?? ''));

    return in_array(
        $role,
        ['superusuario','administrador_cliente'],
        true
    );
}

function authCanCreateUsers(array $context): bool
{
    return array_key_exists('can_create_user',$context)
        ? (bool)$context['can_create_user']
        : authRoleHasCapability(
            (string)($context['primary_role'] ?? ''),
            'users.create'
        );
}

function authSupervisionAssignmentsReady(PDO $pdo): bool
{
    static $cache = [];
    $key = spl_object_id($pdo);

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $stmt = $pdo->prepare(
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA=DATABASE()
               AND TABLE_NAME='user_supervision_assignments'
             LIMIT 1"
        );
        $stmt->execute();
        return $cache[$key] = (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('authSupervisionAssignmentsReady: '.$e->getMessage());
        return $cache[$key] = false;
    }
}

function authIsAssignedUserTarget(
    PDO $pdo,
    array $context,
    array $target
): bool {
    if (!authSupervisionAssignmentsReady($pdo)) {
        return false;
    }

    $supervisor = (string)($context['session_user_id'] ?? '');
    $targetUser = (string)($target['id_users'] ?? '');
    $companyId = (int)($context['company_id'] ?? 0);
    $targetCompany = (int)($target['id_company'] ?? 0);

    if (
        $supervisor === ''
        || $targetUser === ''
        || $companyId <= 0
        || $targetCompany !== $companyId
    ) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT 1
             FROM user_supervision_assignments
             WHERE id_company=:id_company
               AND supervisor_user_id=:supervisor_user_id
               AND target_user_id=:target_user_id
               AND state=1
             LIMIT 1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'supervisor_user_id'=>$supervisor,
            'target_user_id'=>$targetUser,
        ]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('authIsAssignedUserTarget: '.$e->getMessage());
        return false;
    }
}

function authCanViewUserTarget(
    array $context,
    array $target,
    ?PDO $pdo = null
): bool {
    $actorCompany = (int)($context['company_id'] ?? 0);
    $targetCompany = (int)($target['id_company'] ?? 0);
    $targetLevel = isset($target['access_level'])
        ? (int)$target['access_level']
        : null;
    $isSelf = (string)($target['id_users'] ?? '')
        === (string)($context['session_user_id'] ?? '');

    if ($isSelf) {
        return true;
    }

    $scope = authUserScopeMode($context);

    if ($scope === 'global' || !empty($context['is_global_admin'])) {
        return true;
    }

    if (
        array_key_exists('can_view_users',$context)
        && empty($context['can_view_users'])
    ) {
        return false;
    }

    if (
        $actorCompany <= 0
        || $actorCompany !== $targetCompany
        || $targetLevel === null
        || $targetLevel === 1
    ) {
        return false;
    }

    if (
        !in_array(
            $targetLevel,
            authViewableUserLevels($context),
            true
        )
    ) {
        return false;
    }

    if ($scope === 'company') {
        return true;
    }

    if ($scope === 'assigned') {
        $db = $pdo instanceof PDO
            ? $pdo
            : (($GLOBALS['pdo'] ?? null) instanceof PDO
                ? $GLOBALS['pdo']
                : null);

        return $db instanceof PDO
            ? authIsAssignedUserTarget($db,$context,$target)
            : false;
    }

    return false;
}

function authCanManageUserTarget(
    array $context,
    array $target,
    ?PDO $pdo = null
): bool {
    $targetLevel = isset($target['access_level'])
        ? (int)$target['access_level']
        : null;

    if ($targetLevel === null) {
        return !empty($context['is_global_admin']);
    }

    if (!authCanViewUserTarget($context,$target,$pdo)) {
        return false;
    }

    $scope = authUserScopeMode($context);

    if ($scope === 'assigned' && !authCanManageAssignedUsers($context)) {
        return false;
    }

    if (
        array_key_exists('can_manage_users',$context)
        && empty($context['can_manage_users'])
    ) {
        return false;
    }

    return in_array(
        $targetLevel,
        authManagedUserLevels($context),
        true
    );
}

function authCanAssignUserLevel(
    array $context,
    ?int $targetLevel
): bool {
    if (
        array_key_exists('can_assign_roles',$context)
        && empty($context['can_assign_roles'])
    ) {
        return false;
    }

    return $targetLevel !== null
        && in_array(
            $targetLevel,
            authAssignableUserLevels($context),
            true
        );
}

function authEditableUserFields(
    array $context,
    array $target,
    ?PDO $pdo = null
): array {
    $isSelf = (string)($target['id_users'] ?? '')
        === (string)($context['session_user_id'] ?? '');

    if (!authCanManageUserTarget($context,$target,$pdo)) {
        return $isSelf
            ? ['name','lastname','language','profile_photo_path']
            : [];
    }

    $fields = [
        'name',
        'lastname',
        'rut',
        'language',
        'profile_photo_path',
    ];

    if (!empty(authAssignableUserLevels($context))) {
        $fields[] = 'id_role_group';
    }

    if (!empty($context['is_global_admin'])) {
        $fields[] = 'id_company';
    }

    return $fields;
}

function requireCsrfToken(array $input,string $field='csrf_token'): void { $token=(string)($input[$field]??'');if(empty($_SESSION['csrf_token'])||$token===''||!hash_equals((string)$_SESSION['csrf_token'],$token))responderJSON(false,null,'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.',403); }
function capitalizarNombre(string $texto): string { $texto=trim($texto);if($texto==='')return $texto;return function_exists('mb_convert_case')&&defined('MB_CASE_TITLE')?mb_convert_case($texto,MB_CASE_TITLE,'UTF-8'):ucwords(strtolower($texto)); }
