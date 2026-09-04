<?php
/**
 * api/eventos/common.php
 * Mismo criterio que el resto: usa lib/auth.php, no un sistema propio.
 *
 * Diferencia deliberada con Proyectos/Centros/Trabajadores/Inducción:
 * acá CUALQUIER rol (incluido trabajador) puede REPORTAR un evento —
 * es una práctica estándar de seguridad ocupacional que cualquier
 * persona pueda reportar un incidente o casi-accidente que presenció,
 * no solo quienes gestionan la empresa. Editar, cambiar de estado y
 * agregar seguimiento sí queda restringido a los roles de gestión.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

const EVENTOS_ROLES_GESTION = ['administrador', 'administrador_completo', 'cliente', 'jefatura'];
const EVENTOS_ROLES_REPORTAR = ['administrador', 'administrador_completo', 'cliente', 'jefatura', 'trabajador'];

function eventosIsGlobalAdmin(PDO $pdo): bool
{
    $roles = currentUserRoles($pdo);
    return in_array('administrador', $roles, true) || in_array('administrador_completo', $roles, true);
}

function eventosRequireGestionApi(PDO $pdo): void
{
    requireRole($pdo, EVENTOS_ROLES_GESTION);
}

function eventosRequireReportarApi(PDO $pdo): void
{
    requireRole($pdo, EVENTOS_ROLES_REPORTAR);
}

function eventosRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireRolePage($pdo, EVENTOS_ROLES_REPORTAR, $redirectTo);
}

function eventosResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (eventosIsGlobalAdmin($pdo)) {
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
