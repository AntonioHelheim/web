<?php
/**
 * api/centros/common.php
 * Helpers de acceso para el módulo de Centros/Sedes. Mismo criterio que
 * api/proyectos/common.php: reutiliza lib/auth.php, no un sistema propio.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

const CENTROS_ROLES_GESTION = ['administrador', 'administrador_completo', 'cliente', 'jefatura'];
const CENTROS_ROLES_LECTURA = ['administrador', 'administrador_completo', 'cliente', 'jefatura', 'trabajador'];

function centrosIsGlobalAdmin(PDO $pdo): bool
{
    // Solo 'administrador_completo' (super admin) ve/gestiona centros de
    // CUALQUIER empresa. 'administrador' es un rol acotado a su propia
    // empresa (jerarquía definida en users_role_group); antes esta función
    // lo trataba igual que al super admin, lo que rompía el aislamiento
    // multiempresa exigido por el contrato.
    $roles = currentUserRoles($pdo);
    return in_array('administrador_completo', $roles, true);
}

function centrosRequireGestionApi(PDO $pdo): void
{
    requireRole($pdo, CENTROS_ROLES_GESTION);
}

function centrosRequireLecturaApi(PDO $pdo): void
{
    requireRole($pdo, CENTROS_ROLES_LECTURA);
}

function centrosRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireRolePage($pdo, CENTROS_ROLES_GESTION, $redirectTo);
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
