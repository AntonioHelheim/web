<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../app/Induction/HealthSafetyContent.php';

requireLogin();
if (!currentUserHasAnyCapability($pdo, ['protocols.view','dynamic_forms.view','audits.view','self_assessments.view'])) {
    responderJSON(false, null, 'No tienes permisos para acceder a esta inducción.', 403);
}

$userId = currentUserId();
$role = primaryRoleName(currentUserRoles($pdo)) ?? 'usuario';
if (!$userId) responderJSON(false, null, 'Sesión de usuario no válida.', 401);

$manifest = SctHealthSafetyContent::manifest();
$allowedInduction = (string)($manifest['id'] ?? 'health-safety-engie');
$modules = is_array($manifest['modules'] ?? null) ? $manifest['modules'] : [];

function hsResolveModule(array $modules, string $moduleId): ?array {
    foreach ($modules as $module) {
        if ((string)($module['id'] ?? '') === $moduleId) return $module;
    }
    return null;
}

function hsProgressTableAvailable(PDO $pdo): bool {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'induction_content_progress'");
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        error_log('health-safety-progress table check: '.$e->getMessage());
        return false;
    }
}

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (!in_array($method, ['GET','POST'], true)) responderJSON(false, null, 'Método no permitido.', 405);

if (!hsProgressTableAvailable($pdo)) {
    responderJSON(false, ['storage' => 'local_fallback'], 'La migración de progreso de inducción aún no está aplicada.', 503);
}

if ($method === 'GET') {
    $inductionId = trim((string)($_GET['induction_id'] ?? $allowedInduction));
    $moduleId = trim((string)($_GET['module_id'] ?? 'module-01'));
    if ($inductionId !== $allowedInduction) responderJSON(false, null, 'Inducción no válida.', 422);
    $module = hsResolveModule($modules, $moduleId);
    if (!$module) responderJSON(false, null, 'Módulo no válido.', 422);

    $stmt = $pdo->prepare('SELECT last_slide, completed_slides_json, updated_at FROM induction_content_progress WHERE id_users=:id_users AND role_name=:role_name AND induction_id=:induction_id AND module_id=:module_id LIMIT 1');
    $stmt->execute([
        'id_users' => $userId,
        'role_name' => $role,
        'induction_id' => $inductionId,
        'module_id' => $moduleId,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $allowedSlides = array_values(array_map('intval', $module['slide_numbers'] ?? []));
    if (!$allowedSlides) responderJSON(false, null, 'Este módulo aún no tiene láminas habilitadas.', 422);
    $start = $allowedSlides[0];
    $end = $allowedSlides[count($allowedSlides) - 1];
    if (!$row) {
        responderJSON(true, [
            'last_slide' => $start,
            'completed' => [],
            'updated_at' => null,
            'module_start' => $start,
            'module_end' => $end,
        ]);
    }
    $completed = json_decode((string)($row['completed_slides_json'] ?? '[]'), true);
    if (!is_array($completed)) $completed = [];
    $completed = array_values(array_unique(array_filter(array_map('intval', $completed), static fn($n) => in_array($n, $allowedSlides, true))));
    sort($completed);
    $rowLast = (int)$row['last_slide'];
    $lastSlide = in_array($rowLast, $allowedSlides, true) ? $rowLast : $start;
    responderJSON(true, [
        'last_slide' => $lastSlide,
        'completed' => $completed,
        'updated_at' => $row['updated_at'] ?? null,
        'module_start' => $start,
        'module_end' => $end,
    ]);
}

$raw = file_get_contents('php://input');
$input = json_decode((string)$raw, true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$inductionId = trim((string)($input['induction_id'] ?? ''));
$moduleId = trim((string)($input['module_id'] ?? ''));
if ($inductionId !== $allowedInduction) responderJSON(false, null, 'Inducción no válida.', 422);
$module = hsResolveModule($modules, $moduleId);
if (!$module) responderJSON(false, null, 'Módulo no válido.', 422);

$allowedSlides = array_values(array_map('intval', $module['slide_numbers'] ?? []));
if (!$allowedSlides) responderJSON(false, null, 'Este módulo aún no tiene láminas habilitadas.', 422);
$start = $allowedSlides[0];
$end = $allowedSlides[count($allowedSlides) - 1];
$requestedLast = (int)($input['last_slide'] ?? $start);
$lastSlide = in_array($requestedLast, $allowedSlides, true) ? $requestedLast : $start;
$completed = is_array($input['completed'] ?? null) ? $input['completed'] : [];
$completed = array_values(array_unique(array_filter(array_map('intval', $completed), static fn($n) => in_array($n, $allowedSlides, true))));
sort($completed);
$completedJson = json_encode($completed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$sql = 'INSERT INTO induction_content_progress (id_users, role_name, induction_id, module_id, last_slide, completed_slides_json, created_at, updated_at)
        VALUES (:id_users, :role_name, :induction_id, :module_id, :last_slide, :completed_json, NOW(), NOW())
        ON DUPLICATE KEY UPDATE last_slide=VALUES(last_slide), completed_slides_json=VALUES(completed_slides_json), updated_at=NOW()';
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'id_users' => $userId,
    'role_name' => $role,
    'induction_id' => $inductionId,
    'module_id' => $moduleId,
    'last_slide' => $lastSlide,
    'completed_json' => $completedJson,
]);

responderJSON(true, [
    'last_slide' => $lastSlide,
    'completed' => $completed,
    'updated_at' => date('Y-m-d H:i:s'),
]);
