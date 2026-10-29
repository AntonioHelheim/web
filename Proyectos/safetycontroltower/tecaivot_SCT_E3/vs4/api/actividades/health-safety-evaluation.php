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
$inductionId = (string)($manifest['id'] ?? 'health-safety-engie');
$moduleId = 'module-01';
$evaluationId = 'module-01-evaluation-01';
$module = SctHealthSafetyContent::module($manifest, $moduleId);
$evaluation = SctHealthSafetyContent::evaluation($manifest, $evaluationId);
if (!$module || !$evaluation) responderJSON(false, null, 'Evaluación no configurada.', 500);

function hsEvaluationTablesAvailable(PDO $pdo): bool {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='induction_module_evaluation_attempts'");
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        error_log('health-safety-evaluation table check: '.$e->getMessage());
        return false;
    }
}

function hsModuleSlidesCompleted(PDO $pdo, string $userId, string $role, string $inductionId, array $module): bool {
    $slides = array_values(array_map('intval', $module['slide_numbers'] ?? []));
    if (!$slides) return false;
    try {
        $stmt = $pdo->prepare('SELECT completed_slides_json FROM induction_content_progress WHERE id_users=:id_users AND role_name=:role_name AND induction_id=:induction_id AND module_id=:module_id LIMIT 1');
        $stmt->execute(['id_users'=>$userId,'role_name'=>$role,'induction_id'=>$inductionId,'module_id'=>$module['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        $completed = json_decode((string)$row['completed_slides_json'], true);
        if (!is_array($completed)) return false;
        $completed = array_map('intval', $completed);
        foreach ($slides as $slide) if (!in_array($slide, $completed, true)) return false;
        return true;
    } catch (Throwable $e) {
        error_log('health-safety evaluation progress check: '.$e->getMessage());
        return false;
    }
}

if (!hsEvaluationTablesAvailable($pdo)) {
    responderJSON(false, ['migration_required'=>true], 'La migración de evaluación modular aún no está aplicada.', 503);
}

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT evaluation_number, attempt_number, correct_count, question_count, score_percentage, passed, submitted_at FROM induction_module_evaluation_attempts WHERE id_users=:id_users AND role_name=:role_name AND induction_id=:induction_id AND module_id=:module_id AND evaluation_id=:evaluation_id ORDER BY attempt_number DESC');
    $stmt->execute(['id_users'=>$userId,'role_name'=>$role,'induction_id'=>$inductionId,'module_id'=>$moduleId,'evaluation_id'=>$evaluationId]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $passed = false;
    foreach ($attempts as $a) if ((int)$a['passed'] === 1) { $passed = true; break; }
    responderJSON(true, [
        'evaluation_number'=>(int)$evaluation['number'],
        'passed'=>$passed,
        'attempt_count'=>count($attempts),
        'slides_completed'=>hsModuleSlidesCompleted($pdo,$userId,$role,$inductionId,$module),
        'attempts'=>$attempts,
    ]);
}

if ($method !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);
if (!hsModuleSlidesCompleted($pdo,$userId,$role,$inductionId,$module)) {
    responderJSON(false, null, 'Debes completar las láminas del Módulo 1 antes de rendir la evaluación.', 409);
}

$answers = is_array($input['answers'] ?? null) ? $input['answers'] : [];
$questions = $evaluation['questions'] ?? [];
if (count($answers) !== count($questions)) responderJSON(false, null, 'Debes responder las 4 preguntas.', 400);

$sanitized = [];
$correctCount = 0;
foreach ($questions as $question) {
    $qid = (string)$question['id'];
    $selected = (string)($answers[$qid] ?? '');
    $valid = false; $correct = false;
    foreach (($question['options'] ?? []) as $option) {
        if ((string)$option['id'] === $selected) {
            $valid = true;
            $correct = !empty($option['correct']);
            break;
        }
    }
    if (!$valid) responderJSON(false, null, 'Una de las respuestas no es válida.', 422);
    if ($correct) $correctCount++;
    $sanitized[$qid] = ['selected'=>$selected, 'correct'=>$correct];
}

$questionCount = count($questions);
$score = $questionCount > 0 ? round(($correctCount / $questionCount) * 100, 2) : 0.0;
$passed = $score >= (float)$evaluation['pass_percentage'];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(attempt_number),0)+1 FROM induction_module_evaluation_attempts WHERE id_users=:id_users AND role_name=:role_name AND induction_id=:induction_id AND module_id=:module_id AND evaluation_id=:evaluation_id');
    $stmt->execute(['id_users'=>$userId,'role_name'=>$role,'induction_id'=>$inductionId,'module_id'=>$moduleId,'evaluation_id'=>$evaluationId]);
    $attemptNumber = (int)$stmt->fetchColumn();
    if ($attemptNumber < 1) $attemptNumber = 1;
    $stmt = $pdo->prepare('INSERT INTO induction_module_evaluation_attempts (id_users, role_name, induction_id, module_id, evaluation_id, evaluation_number, attempt_number, answers_json, correct_count, question_count, score_percentage, passed, submitted_at) VALUES (:id_users,:role_name,:induction_id,:module_id,:evaluation_id,:evaluation_number,:attempt_number,:answers_json,:correct_count,:question_count,:score_percentage,:passed,NOW())');
    $stmt->execute([
        'id_users'=>$userId,'role_name'=>$role,'induction_id'=>$inductionId,'module_id'=>$moduleId,
        'evaluation_id'=>$evaluationId,'evaluation_number'=>(int)$evaluation['number'],'attempt_number'=>$attemptNumber,
        'answers_json'=>json_encode($sanitized, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        'correct_count'=>$correctCount,'question_count'=>$questionCount,'score_percentage'=>$score,'passed'=>$passed?1:0,
    ]);
    $attemptId = (int)$pdo->lastInsertId();
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('health-safety-evaluation save: '.$e->getMessage());
    responderJSON(false, null, 'No fue posible guardar el resultado de la evaluación.', 500);
}

responderJSON(true, [
    'id_attempt'=>$attemptId,
    'evaluation_number'=>(int)$evaluation['number'],
    'attempt_number'=>$attemptNumber,
    'correct_count'=>$correctCount,
    'question_count'=>$questionCount,
    'score_percentage'=>$score,
    'passed'=>$passed,
    'redirect_url'=>$passed ? 'health-safety.php?module=2&slide=14' : 'health-safety.php?module=1&slide=1',
    'next_module_url'=>$passed ? 'health-safety.php?module=2&slide=14' : null,
]);
