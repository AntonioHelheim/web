<?php
/**
 * api/dashboard/common.php
 * Mismo criterio que el resto: usa lib/auth.php. Es un módulo de solo
 * lectura — no hay "gestión" acá, cualquier rol logueado ve el
 * dashboard de su propia empresa; el admin global elige cuál ver.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

function dashboardIsGlobalAdmin(PDO $pdo): bool
{
    $roles = currentUserRoles($pdo);
    return in_array('administrador', $roles, true) || in_array('administrador_completo', $roles, true);
}

function dashboardResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (dashboardIsGlobalAdmin($pdo)) {
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
