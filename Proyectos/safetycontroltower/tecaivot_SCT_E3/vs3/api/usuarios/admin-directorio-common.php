<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/validation.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../app/Users/AdminUserDirectoryPolicy.php';
require_once __DIR__ . '/../../app/Users/AdminUserDirectoryRepository.php';
require_once __DIR__ . '/../../app/Users/AdminUserProfileService.php';
require_once __DIR__ . '/../../app/Users/AdminSupervisionRepository.php';
require_once __DIR__ . '/../../app/Users/AdminManagementRepository.php';
require_once __DIR__ . '/../../app/Users/AdminUserActionLog.php';
require_once __DIR__ . '/../../app/Users/AdminPersonCreationService.php';
require_once __DIR__ . '/../../app/Users/AdminUserScopeCatalogService.php';

function sctAdminDirectoryContext(PDO $pdo): array
{
    requireLogin();

    $context=currentUserAccessContext($pdo);
    $role=primaryRoleName(currentUserRoles($pdo));

    if (
        !$context
        || !in_array(
            $role,
            ['administrador_cliente','gerente'],
            true
        )
    ) {
        responderJSON(false,null,t('common_forbidden'),403);
    }

    $context['directory_role']=$role;
    $context['supervisor_scope']=null;
    $context['managed_levels']=
        SctAdminUserDirectoryPolicy::managedLevelsForActorRole($role);

    return $context;
}

function sctDirectoryTargetAllowed(
    PDO $pdo,
    array $context,
    array $target
): bool {
    $role=(string)($context['directory_role'] ?? '');
    $assigned=true;

    return SctAdminUserDirectoryPolicy::canManageForActor(
        $role,
        (int)$context['company_id'],
        (int)($target['id_company'] ?? 0),
        isset($target['access_level'])
            ? (int)$target['access_level']
            : null,
        $assigned
    );
}

function sctAdminDirectoryRepo(PDO $pdo): SctAdminUserDirectoryRepository
{
    return new SctAdminUserDirectoryRepository($pdo);
}
