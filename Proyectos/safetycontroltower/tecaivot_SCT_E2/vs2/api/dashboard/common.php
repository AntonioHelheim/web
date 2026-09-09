<?php
/**
 * Safety Control Tower - Dashboard avanzado / helpers compartidos.
 */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

function dashboardIsGlobalAdmin(PDO $pdo): bool
{
    return currentUserHasCapability($pdo, 'companies.view_all')
        && currentUserHasCapability($pdo, 'dashboard.global');
}

function dashboardResolveCompanyId(PDO $pdo, ?int $idCompanySolicitado): int
{
    if (dashboardIsGlobalAdmin($pdo)) {
        if (!$idCompanySolicitado) {
            responderJSON(false, null, 'Debes indicar la empresa.', 400);
        }
        $stmt = $pdo->prepare('SELECT id_company FROM company WHERE id_company = :id_company AND state = 1 LIMIT 1');
        $stmt->execute(['id_company' => $idCompanySolicitado]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            responderJSON(false, null, 'La empresa seleccionada no existe o está inactiva.', 404);
        }
        return (int) $id;
    }

    $idCompanyPropia = currentUserCompanyId($pdo);
    if (!$idCompanyPropia) {
        responderJSON(false, null, 'Tu cuenta no tiene una empresa asociada.', 403);
    }

    $stmt = $pdo->prepare('SELECT id_company FROM company WHERE id_company = :id_company AND state = 1 LIMIT 1');
    $stmt->execute(['id_company' => $idCompanyPropia]);
    if (!$stmt->fetchColumn()) {
        responderJSON(false, null, 'La empresa asociada a tu cuenta no está disponible.', 403);
    }

    return (int) $idCompanyPropia;
}

/**
 * Presets del dashboard. Se evita aceptar fechas arbitrarias desde el cliente
 * en esta iteración para mantener una API acotada y predecible.
 *
 * @return array{key:string,desde:?string,hasta:string,dias:?int}
 */
function dashboardResolvePeriod(?string $period): array
{
    $key = strtolower(trim((string) ($period ?? '90')));
    $allowed = ['30' => 30, '90' => 90, '180' => 180, '365' => 365, 'all' => null];
    if (!array_key_exists($key, $allowed)) {
        $key = '90';
    }

    $now = new DateTimeImmutable('now');
    $days = $allowed[$key];
    $from = $days === null ? null : $now->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

    return [
        'key' => $key,
        'desde' => $from,
        'hasta' => $now->format('Y-m-d H:i:s'),
        'dias' => $days,
    ];
}
