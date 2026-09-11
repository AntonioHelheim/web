<?php
/**
 * api/proyectos/common.php
 * Helpers de acceso para el módulo de Proyectos. Reutiliza lib/auth.php
 * (capacidades y alcance multiempresa) igual que el resto de módulos.
 * La autorización de gestión se delega en la política única de lib/auth.php.
 *
 * Reglas de negocio:
 * - administrador_completo: puede gestionar proyectos de cualquier empresa.
 * - administrador: gestiona solo los proyectos de su propia empresa.
 * - cliente / jefatura: gestionan solo los proyectos de su propia
 *   empresa (id_company se fuerza desde la sesión, se ignora cualquier
 *   id_company que venga en el request).
 * - trabajador: solo lectura, y solo de su propia empresa.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../i18n.php';

const PROYECTOS_ROLES_GESTION = ['administrador', 'administrador_completo', 'cliente', 'jefatura'];
const PROYECTOS_ROLES_LECTURA = ['administrador', 'administrador_completo', 'cliente', 'jefatura', 'trabajador'];

function proyectosIsGlobalAdmin(PDO $pdo): bool
{
    // Solo 'administrador_completo' (super admin) ve/gestiona proyectos de
    // CUALQUIER empresa. 'administrador' es un rol acotado a su propia
    // empresa; antes esta función lo trataba igual que al super admin.
    return currentUserHasCapability($pdo, 'companies.view_all');
}

/**
 * Para endpoints que crean/editan/dan de baja. Corta con 403 si el rol
 * actual no puede gestionar proyectos.
 */
function proyectosRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'projects.manage');
}

/**
 * Para endpoints de solo lectura (listar, detalle, ver trabajadores
 * asociados). Cualquier rol con sesión activa puede ver, el aislamiento
 * por empresa se resuelve en proyectosResolveCompanyId().
 */
function proyectosRequireLecturaApi(PDO $pdo): void
{
    requireRole($pdo, PROYECTOS_ROLES_LECTURA);
}

function proyectosRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'projects.manage', $redirectTo);
}

/**
 * Resuelve qué id_company usar para una operación:
 * - Admin global: usa el que venga en el request (obligatorio, si no
 *   viene o es inválido, corta la ejecución con 400).
 * - Resto de roles: ignora lo que venga en el request y fuerza la
 *   empresa asociada a la sesión actual (aislamiento multiempresa).
 *
 * Termina la ejecución (responderJSON) si no se puede resolver una
 * empresa válida, así que no hace falta volver a chequear el retorno.
 */
function proyectosResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (proyectosIsGlobalAdmin($pdo)) {
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
