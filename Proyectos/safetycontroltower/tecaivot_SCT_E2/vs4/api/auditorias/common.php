<?php
/**
 * api/auditorias/common.php
 * Reglas compartidas del módulo Auditorías (Etapa 2).
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';

const AUDITORIA_TIPO = 'auditoria';
const AUDITORIA_ROLES_LECTURA = ['administrador_completo', 'administrador', 'cliente', 'jefatura', 'trabajador'];

function auditoriaIsGlobalAdmin(PDO $pdo): bool
{
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function auditoriaRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'audits.manage');
}

function auditoriaRequireLecturaApi(PDO $pdo): void
{
    requireRole($pdo, AUDITORIA_ROLES_LECTURA);
}

function auditoriaRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'audits.manage', $redirectTo);
}

function auditoriaResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (auditoriaIsGlobalAdmin($pdo)) {
        if (!$idCompanySolicitado || $idCompanySolicitado <= 0) {
            responderJSON(false, null, 'Debes indicar la empresa auditada.', 400);
        }
        return $idCompanySolicitado;
    }

    $idCompany = currentUserCompanyId($pdo);
    if (!$idCompany) {
        responderJSON(false, null, 'Tu cuenta no tiene una empresa asociada.', 403);
    }

    return $idCompany;
}

function auditoriaAssertTest(array $test): void
{
    if (($test['type'] ?? '') !== AUDITORIA_TIPO) {
        responderJSON(false, null, 'La evaluación indicada no corresponde a una auditoría.', 400);
    }
}

function auditoriaTestVigente(array $test, ?int $timestamp = null): bool
{
    $timestamp = $timestamp ?? time();
    $desde = !empty($test['effective_date_from']) ? strtotime((string) $test['effective_date_from']) : false;
    $hasta = !empty($test['effective_date_until']) ? strtotime((string) $test['effective_date_until']) : false;

    if ($desde !== false && $timestamp < $desde) return false;
    if ($hasta !== false && $timestamp > $hasta) return false;
    return true;
}

function auditoriaAssertCompanyAccess(PDO $pdo, int $idCompany): void
{
    if (!auditoriaIsGlobalAdmin($pdo) && currentUserCompanyId($pdo) !== $idCompany) {
        responderJSON(false, null, 'No tienes permisos para acceder a auditorías de esta empresa.', 403);
    }
}

function auditoriaMigrationMessage(Throwable $e): bool
{
    $message = $e->getMessage();
    return $message === 'MIGRATION_REQUIRED_AUDITS' || $message === 'MIGRATION_REQUIRED_E2_EVALUATIONS';
}
