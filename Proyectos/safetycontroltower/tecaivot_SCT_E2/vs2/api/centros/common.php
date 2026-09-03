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
    $roles = currentUserRoles($pdo);
    return in_array('administrador', $roles, true) || in_array('administrador_completo', $roles, true);
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
