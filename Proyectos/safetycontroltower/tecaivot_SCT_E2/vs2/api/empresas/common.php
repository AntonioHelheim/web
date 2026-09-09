<?php
/** Helpers de acceso del módulo Empresas. Toda autorización usa lib/auth.php. */
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';

function empresasIsGlobalAdmin(PDO $pdo): bool
{
    return currentUserHasCapability($pdo, 'companies.manage_all');
}

function empresasRequireGlobalAdminApi(PDO $pdo): void
{
    requireCapability($pdo, 'companies.manage_all');
}

function empresasRequireGlobalAdminPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'companies.manage_all', $redirectTo);
}

function empresasCanView(PDO $pdo, int $idCompany): bool
{
    if (currentUserHasCapability($pdo, 'companies.view_all')) {
        return true;
    }
    return currentUserHasCapability($pdo, 'companies.view_own')
        && currentUserCompanyId($pdo) === $idCompany;
}

function empresasCanEdit(PDO $pdo, int $idCompany): bool
{
    if (currentUserHasCapability($pdo, 'companies.edit_all')) {
        return true;
    }
    return currentUserHasCapability($pdo, 'companies.edit_own')
        && currentUserCompanyId($pdo) === $idCompany;
}
