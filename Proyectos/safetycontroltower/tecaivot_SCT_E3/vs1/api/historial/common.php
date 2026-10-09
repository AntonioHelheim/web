<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/repositorios/HistorialRepository.php';

function historialIsGlobal(PDO $pdo): bool
{
    return currentUserHasCapability($pdo, 'change_history.global');
}

function historialRequireViewApi(PDO $pdo): void
{
    requireCapability($pdo, 'change_history.view');
}

function historialRequireViewPage(PDO $pdo, string $redirect='../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'change_history.view', $redirect);
}

function historialReadInput(): array
{
    $input = json_decode((string) file_get_contents('php://input'), true);
    return is_array($input) ? $input : $_REQUEST;
}

function historialResolveCompany(PDO $pdo, ?int $requested): ?int
{
    if (historialIsGlobal($pdo)) {
        if ($requested !== null && $requested > 0 && !historialEmpresaExiste($pdo, $requested)) {
            responderJSON(false, null, 'Empresa no válida.', 400);
        }
        return ($requested && $requested > 0) ? $requested : null;
    }

    $own = currentUserCompanyId($pdo);
    if (!$own) {
        responderJSON(false, null, 'Tu cuenta no tiene una empresa asociada.', 403);
    }
    return $own;
}

function historialValidDate(?string $value): ?string
{
    $value = trim((string) $value);
    if ($value === '') return null;
    $d = DateTime::createFromFormat('Y-m-d', $value);
    if (!$d || $d->format('Y-m-d') !== $value) return null;
    return $value;
}
