<?php
/**
 * api/proyectos/common.php
 * Helpers de acceso para el módulo de Proyectos. Reutiliza lib/auth.php
 * (requireRole, currentUserRoles, currentUserCompanyId) igual que hace
 * api/empresas/ — a propósito NO se reimplementa un sistema de permisos
 * propio como el de api/usuarios/common.php, para no sumar un tercer
 * esquema de control de acceso al proyecto.
 *
 * Reglas de negocio:
 * - administrador / administrador_completo: gestionan proyectos de
 *   CUALQUIER empresa (deben indicar id_company explícitamente).
 * - cliente / jefatura: gestionan solo los proyectos de su propia
 *   empresa (id_company se fuerza desde la sesión, se ignora cualquier
 *   id_company que venga en el request).
 * - trabajador: solo lectura, y solo de su propia empresa.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

const PROYECTOS_ROLES_GESTION = ['administrador', 'administrador_completo', 'cliente', 'jefatura'];
const PROYECTOS_ROLES_LECTURA = ['administrador', 'administrador_completo', 'cliente', 'jefatura', 'trabajador'];

function proyectosIsGlobalAdmin(PDO $pdo): bool
{
    $roles = currentUserRoles($pdo);
    return in_array('administrador', $roles, true) || in_array('administrador_completo', $roles, true);
}

/**
 * Para endpoints que crean/editan/dan de baja. Corta con 403 si el rol
 * actual no puede gestionar proyectos.
 */
function proyectosRequireGestionApi(PDO $pdo): void
{
    requireRole($pdo, PROYECTOS_ROLES_GESTION);
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
    requireRolePage($pdo, PROYECTOS_ROLES_GESTION, $redirectTo);
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
