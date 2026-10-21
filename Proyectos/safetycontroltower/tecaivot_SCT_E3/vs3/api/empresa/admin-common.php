<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/validation.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../app/Users/AdminUserDirectoryPolicy.php';
require_once __DIR__ . '/../../app/Company/AdminCompanyManagementRepository.php';
require_once __DIR__ . '/../../app/Company/AdminCompanyManagementService.php';

function sctAdminCompanyContext(PDO $pdo): array
{
    requireLogin();

    $context=currentUserAccessContext($pdo);

    $role=primaryRoleName(currentUserRoles($pdo));

    if (
        !$context
        || $role!=='administrador_cliente'
    ) {
        responderJSON(false,null,t('common_forbidden'),403);
    }

    $context['company_management_role']=$role;
    $context['company_management_readonly']=false;

    return $context;
}


function sctAdminCompanyRequireCompanyWrite(array $context): void
{
    if (
        (string)($context['company_management_role'] ?? '')
        !== 'administrador_cliente'
    ) {
        responderJSON(false,null,t('common_forbidden'),403);
    }
}

function sctAdminCompanyRequireCenterWrite(array $context): void
{
    $role=(string)($context['company_management_role'] ?? '');

    if ($role!=='administrador_cliente') {
        responderJSON(
            false,
            null,
            t('common_forbidden'),
            403
        );
    }
}

function sctAdminCompanyRequireScopeWrite(array $context): void
{
    $role=(string)($context['company_management_role'] ?? '');

    if ($role!=='administrador_cliente') {
        responderJSON(
            false,
            null,
            t('common_forbidden'),
            403
        );
    }
}
