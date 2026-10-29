<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../app/Home/AdminPortalRegistry.php';
require_once __DIR__ . '/../../app/Home/AdminSiteScopeService.php';

requireLoginPage('../../acceso-denegado.php');

$roles = currentUserRoles($pdo);
$primaryRole = primaryRoleName($roles);

if (!in_array(
    $primaryRole,
    ['administrador_cliente','jefatura','gerente'],
    true
)) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

$companyId = currentUserCompanyId($pdo);
$projectId = filter_input(
    INPUT_GET,
    'id_project',
    FILTER_VALIDATE_INT
) ?: 0;
$moduleId = trim((string)($_GET['module'] ?? ''));

$module = SctAdminPortalRegistry::moduleById($moduleId);

if (!$module || empty($module['href'])) {
    header('Location: ../../bienvenida.php#admin-portal');
    exit;
}

if (
    !empty($module['cap'])
    && !currentUserHasCapability(
        $pdo,
        (string)$module['cap']
    )
) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

$scope = (new SctAdminSiteScopeService($pdo))
    ->resolveForAdministrator(
        (int)$companyId,
        (int)$projectId
    );

if ($scope === null) {
    header('Location: ../../bienvenida.php#admin-portal');
    exit;
}

SctAdminSiteScopeService::saveToSession($scope);

$query = http_build_query([
    'scope' => 'site',
    'id_project' => $scope['id_project'],
    'gbu' => $scope['gbu'],
    'site' => $scope['site'],
]);

$target = '../../' . ltrim((string)$module['href'], '/');
$separator = str_contains($target, '?') ? '&' : '?';

header('Location: ' . $target . $separator . $query);
exit;
