<?php
/**
 * api/autoevaluaciones/common.php
 * Reglas compartidas del módulo Self-assessment / Autoevaluaciones (Etapa 2).
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';

const AUTOEVALUACION_TIPO = 'autoevaluacion';

function autoevaluacionIsGlobalAdmin(PDO $pdo): bool
{
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function autoevaluacionRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'self_assessments.manage');
}

function autoevaluacionRequireGestionPage(PDO $pdo, string $redirectTo = '../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'self_assessments.manage', $redirectTo);
}

function autoevaluacionResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (autoevaluacionIsGlobalAdmin($pdo)) {
        if (!$idCompanySolicitado || $idCompanySolicitado <= 0) {
            responderJSON(false, null, 'Debes indicar la empresa de la autoevaluación.', 400);
        }
        return $idCompanySolicitado;
    }

    $idCompany = currentUserCompanyId($pdo);
    if (!$idCompany) {
        responderJSON(false, null, 'Tu cuenta no tiene una empresa asociada.', 403);
    }
    return $idCompany;
}

function autoevaluacionAssertTest(array $test): void
{
    if (($test['type'] ?? '') !== AUTOEVALUACION_TIPO) {
        responderJSON(false, null, 'La evaluación indicada no corresponde a una autoevaluación.', 400);
    }
}

function autoevaluacionAssertCompanyAccess(PDO $pdo, int $idCompany): void
{
    if (!autoevaluacionIsGlobalAdmin($pdo) && currentUserCompanyId($pdo) !== $idCompany) {
        responderJSON(false, null, 'No tienes permisos para acceder a autoevaluaciones de esta empresa.', 403);
    }
}

function autoevaluacionTestVigente(array $test, ?int $timestamp = null): bool
{
    $timestamp = $timestamp ?? time();
    $desde = !empty($test['effective_date_from']) ? strtotime((string) $test['effective_date_from']) : false;
    $hasta = !empty($test['effective_date_until']) ? strtotime((string) $test['effective_date_until']) : false;
    if ($desde !== false && $timestamp < $desde) return false;
    if ($hasta !== false && $timestamp > $hasta) return false;
    return true;
}

function autoevaluacionMigrationMessage(Throwable $e): bool
{
    return $e->getMessage() === 'MIGRATION_REQUIRED_E2_EVALUATIONS';
}
