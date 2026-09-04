<?php
/**
 * api/induccion/common.php
 * Mismo criterio que proyectos/centros/trabajadores: usa lib/auth.php,
 * no un sistema de permisos propio.
 *
 * Reglas de negocio específicas de este módulo:
 * - Gestión de cursos/asignaciones: administrador, administrador_completo,
 *   cliente, jefatura (igual que el resto de los módulos).
 * - Banco de preguntas (crear/editar): SOLO administrador/administrador_completo,
 *   porque la tabla questions es global (sin id_company) — ver el
 *   comentario en InduccionRepository.php.
 * - Rendir un curso asignado: cualquier usuario logueado, sin importar
 *   el rol — la asignación es por id_users, no por rol.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

const INDUCCION_ROLES_GESTION = ['administrador', 'administrador_completo', 'cliente', 'jefatura'];
const INDUCCION_ROLES_LECTURA = ['administrador', 'administrador_completo', 'cliente', 'jefatura', 'trabajador'];
const INDUCCION_ROLES_BANCO_PREGUNTAS = ['administrador', 'administrador_completo'];

function induccionIsGlobalAdmin(PDO $pdo): bool
{
    $roles = currentUserRoles($pdo);
    return in_array('administrador', $roles, true) || in_array('administrador_completo', $roles, true);
}

function induccionRequireGestionApi(PDO $pdo): void
{
    requireRole($pdo, INDUCCION_ROLES_GESTION);
}

function induccionRequireLecturaApi(PDO $pdo): void
{
    requireRole($pdo, INDUCCION_ROLES_LECTURA);
}

function induccionRequireBancoPreguntasApi(PDO $pdo): void
{
    requireRole($pdo, INDUCCION_ROLES_BANCO_PREGUNTAS);
}

function induccionRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireRolePage($pdo, INDUCCION_ROLES_GESTION, $redirectTo);
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
