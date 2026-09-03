<?php
/**
 * api/trabajadores/common.php
 * Mismo criterio que api/proyectos/common.php y api/centros/common.php:
 * usa lib/auth.php, no un sistema de permisos propio.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

const TRABAJADORES_ROLES_GESTION = ['administrador', 'administrador_completo', 'cliente', 'jefatura'];
const TRABAJADORES_ROLES_LECTURA = ['administrador', 'administrador_completo', 'cliente', 'jefatura', 'trabajador'];

function trabajadoresIsGlobalAdmin(PDO $pdo): bool
{
    $roles = currentUserRoles($pdo);
    return in_array('administrador', $roles, true) || in_array('administrador_completo', $roles, true);
}

function trabajadoresRequireGestionApi(PDO $pdo): void
{
    requireRole($pdo, TRABAJADORES_ROLES_GESTION);
}

function trabajadoresRequireLecturaApi(PDO $pdo): void
{
    requireRole($pdo, TRABAJADORES_ROLES_LECTURA);
}

function trabajadoresRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireRolePage($pdo, TRABAJADORES_ROLES_GESTION, $redirectTo);
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
