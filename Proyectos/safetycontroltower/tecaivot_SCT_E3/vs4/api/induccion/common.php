<?php
/**
 * api/induccion/common.php
 * Mismo criterio que proyectos/centros/trabajadores: usa lib/auth.php,
 * no un sistema de permisos propio.
 *
 * Reglas de negocio específicas de este módulo:
 * - Gestión de cursos/asignaciones: perfiles con induction.manage,
 *   perfiles autorizados mediante capabilities (igual que el resto de los módulos).
 * - Banco de preguntas (crear/editar): según capabilities RBAC,
 *   porque la tabla questions es global (sin id_company) — ver el
 *   comentario en InduccionRepository.php.
 * - Rendir un curso asignado: cualquier usuario logueado, sin importar
 *   el rol — la asignación es por id_users, no por rol.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../i18n.php';


function induccionIsGlobalAdmin(PDO $pdo): bool
{
    // Solo quien posea alcance global (`companies.view_all`) ve/gestiona inducción de
    // CUALQUIER empresa. el resto de perfiles queda acotado a su propia
    // empresa; antes esta función lo trataba igual que al super admin.
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function induccionRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'induction.manage');
}

function induccionRequireLecturaApi(PDO $pdo): void
{
    requireCapability($pdo, 'induction.view');
}

function induccionRequireBancoPreguntasApi(PDO $pdo): void
{
    requireCapability($pdo, 'questions.manage');
}

function induccionRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'induction.manage', $redirectTo);
}

function induccionResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (induccionIsGlobalAdmin($pdo)) {
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
