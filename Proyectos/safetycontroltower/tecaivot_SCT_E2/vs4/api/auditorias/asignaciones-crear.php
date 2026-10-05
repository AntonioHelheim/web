<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';
require __DIR__ . '/../../lib/repositorios/AuditoriaRepository.php';

requireCapability($pdo, 'audits.assign');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
$idAuditor = trim((string) ($input['id_users'] ?? ''));
$deadline = DateTime::createFromFormat('Y-m-d', (string) ($input['deadline'] ?? ''));
if (!$idTest || $idAuditor === '' || !$deadline) responderJSON(false, null, 'Auditor, auditoría o plazo no válidos.', 400);

try {
    evaluacionRequireSchemaAsignacion($pdo);
    auditoriaRequireSchema($pdo);

    $test = cursoObtenerPorId($pdo, $idTest);
    if (!$test) responderJSON(false, null, 'Auditoría no encontrada.', 404);
    auditoriaAssertTest($test);
    auditoriaAssertCompanyAccess($pdo, (int) $test['id_company']);

    if ((int) $test['state'] !== 1) responderJSON(false, null, 'La auditoría está inactiva.', 400);
    $deadlineTs = strtotime($deadline->format('Y-m-d 23:59:59'));
    $untilTs = !empty($test['effective_date_until']) ? strtotime((string) $test['effective_date_until']) : false;
    if ($deadlineTs !== false && $deadlineTs < time()) responderJSON(false, null, 'El plazo de la auditoría no puede estar vencido.', 400);
    if ($untilTs !== false && $deadlineTs !== false && $deadlineTs > $untilTs) responderJSON(false, null, 'El plazo no puede superar la fecha de término de vigencia de la auditoría.', 400);
    if (cursoPuntajeMaximo($pdo, $idTest) <= 0) responderJSON(false, null, 'Agrega al menos una pregunta antes de asignar la auditoría.', 400);

    $stmtAuditor = $pdo->prepare('SELECT id_users, id_company FROM users WHERE id_users = :id_users AND state = 1 LIMIT 1');
    $stmtAuditor->execute(['id_users' => $idAuditor]);
    $auditor = $stmtAuditor->fetch();
    if (!$auditor) responderJSON(false, null, 'El auditor seleccionado no está disponible.', 400);

    if (!auditoriaIsGlobalAdmin($pdo) && (int) $auditor['id_company'] !== (int) $test['id_company']) {
        responderJSON(false, null, 'Solo puedes asignar auditores pertenecientes a tu empresa.', 403);
    }

    if (evaluacionAsignacionPendienteExiste($pdo, $idTest, $idAuditor)) {
        responderJSON(false, null, 'Este auditor ya tiene una ejecución pendiente de esta auditoría.', 409);
    }

    $pdo->beginTransaction();
    $idAsignacion = asignacionCrear(
        $pdo,
        $idTest,
        $idAuditor,
        (int) $test['id_company'],
        $deadline->format('Y-m-d 23:59:59'),
        (string) currentUserId()
    );

    $idAudit = auditoriaCrearDesdeAsignacion(
        $pdo,
        (int) $test['id_company'],
        $idTest,
        $idAsignacion,
        $idAuditor,
        (string) currentUserId()
    );
    auditTrailLogAction($pdo, (int)$test['id_company'], 'auditorias', 'users_test_assigned', $idAsignacion,
        'assignment', null, $idAuditor . ' · ' . $deadline->format('Y-m-d'), 'assign', (string)$test['name']);
    $pdo->commit();

    responderJSON(true, [
        'id_user_test_assigned' => $idAsignacion,
        'id_audits' => $idAudit,
    ], 'Auditoría asignada correctamente.', 201);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (auditoriaMigrationMessage($e)) {
        responderJSON(false, null, 'El módulo requiere ejecutar la migración 20260908_stage2_audits.sql.', 503);
    }
    error_log('api/auditorias/asignaciones-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo asignar la auditoría.', 500);
}
