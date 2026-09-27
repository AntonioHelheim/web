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
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../i18n.php';

const EVENTOS_ROLES_GESTION = ['administrador', 'administrador_completo', 'cliente', 'jefatura'];
const EVENTOS_ROLES_REPORTAR = ['administrador', 'administrador_completo', 'cliente', 'jefatura', 'trabajador'];

function eventosIsGlobalAdmin(PDO $pdo): bool
{
    // Solo 'administrador_completo' (super admin) ve/gestiona eventos de
    // CUALQUIER empresa. 'administrador' es un rol acotado a su propia
    // empresa; antes esta función lo trataba igual que al super admin.
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function eventosRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'events.manage');
}

function eventosRequireReportarApi(PDO $pdo): void
{
    requireRole($pdo, EVENTOS_ROLES_REPORTAR);
}

function eventosRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireRolePage($pdo, EVENTOS_ROLES_REPORTAR, $redirectTo);
}

function eventosCurrentRole(PDO $pdo): string
{
    $context = resolveCurrentUserAccessContext($pdo);
    return $context !== null ? (string) ($context['primary_role'] ?? 'trabajador') : 'trabajador';
}

function eventosIsWorkerScope(PDO $pdo): bool
{
    return eventosCurrentRole($pdo) === 'trabajador';
}

function eventosCurrentWorkerId(PDO $pdo): int
{
    $profile = currentUserProfile($pdo);
    return $profile && !empty($profile['id_worker']) ? (int) $profile['id_worker'] : 0;
}

function eventosCanViewEvent(PDO $pdo, array $evento): bool
{
    if (eventosIsGlobalAdmin($pdo)) return true;

    $companyId = currentUserCompanyId($pdo);
    if (!$companyId || (int) ($evento['id_company'] ?? 0) !== $companyId) return false;

    if (!eventosIsWorkerScope($pdo)) return true;

    $userId = (string) (currentUserId() ?? '');
    $workerId = eventosCurrentWorkerId($pdo);
    if ($userId !== '' && (string) ($evento['created_by'] ?? '') === $userId) return true;
    return $workerId > 0 && (int) ($evento['id_worker'] ?? 0) === $workerId;
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
