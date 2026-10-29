<?php
/**
 * api/centros/common.php
 * Helpers de acceso para el módulo de Centros/Sedes. Mismo criterio que
 * api/proyectos/common.php: reutiliza lib/auth.php, no un sistema propio.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../i18n.php';


function centrosIsGlobalAdmin(PDO $pdo): bool
{
    // Solo quien posea alcance global (`companies.view_all`) ve/gestiona centros de
    // CUALQUIER empresa. el resto de perfiles queda acotado a su propia
    // empresa (jerarquía definida en users_role_group); antes esta función
    // lo trataba igual que al super admin, lo que rompía el aislamiento
    // multiempresa exigido por el contrato.
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function centrosRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'centers.manage');
}

function centrosRequireLecturaApi(PDO $pdo): void
{
    requireCapability($pdo, 'centers.view');
}

function centrosRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'centers.manage', $redirectTo);
}

function centrosResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (centrosIsGlobalAdmin($pdo)) {
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
