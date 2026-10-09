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
require_once __DIR__ . '/../../i18n.php';


function trabajadoresIsGlobalAdmin(PDO $pdo): bool
{
    // Solo quien posea alcance global (`companies.view_all`) ve/gestiona trabajadores
    // de CUALQUIER empresa. el resto de perfiles queda acotado a su propia
    // empresa; antes esta función lo trataba igual que al super admin.
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function trabajadoresRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'workers.manage');
}

function trabajadoresRequireLecturaApi(PDO $pdo): void
{
    requireCapability($pdo, 'workers.view');
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
