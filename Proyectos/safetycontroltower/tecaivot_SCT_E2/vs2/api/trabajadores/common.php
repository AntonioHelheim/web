<?php
/**
 * api/trabajadores/common.php
 * Mismo criterio que api/proyectos/common.php y api/centros/common.php:
 * usa lib/auth.php, no un sistema de permisos propio.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';

const TRABAJADORES_ROLES_GESTION = ['administrador', 'administrador_completo', 'cliente', 'jefatura'];
const TRABAJADORES_ROLES_LECTURA = ['administrador', 'administrador_completo', 'cliente', 'jefatura', 'trabajador'];

function trabajadoresIsGlobalAdmin(PDO $pdo): bool
{
    // Solo 'administrador_completo' (super admin) ve/gestiona trabajadores
    // de CUALQUIER empresa. 'administrador' es un rol acotado a su propia
    // empresa; antes esta función lo trataba igual que al super admin.
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function trabajadoresRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'workers.manage');
}

function trabajadoresRequireLecturaApi(PDO $pdo): void
{
    requireRole($pdo, TRABAJADORES_ROLES_LECTURA);
}

function trabajadoresRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'workers.manage', $redirectTo);
}

function trabajadoresResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (trabajadoresIsGlobalAdmin($pdo)) {
        if (!$idCompanySolicitado) {
            responderJSON(false, null, 'Debes indicar la empresa.', 400);
        }
        return $idCompanySolicitado;
    }

    $idCompanyPropia = currentUserCompanyId($pdo);
    if (!$idCompanyPropia) {
        responderJSON(false, null, 'Tu cuenta no tiene una empresa asociada.', 403);
    }

    return $idCompanyPropia;
}
