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
$rawUsers = $input['id_users'] ?? [];
$legacySingle = !is_array($rawUsers);
if (!is_array($rawUsers)) $rawUsers = [$rawUsers];
$auditores = [];
foreach ($rawUsers as $rawUser) {
    $value = trim((string) $rawUser);
    if ($value !== '') $auditores[$value] = true;
}
$auditores = array_keys($auditores);
$deadline = DateTime::createFromFormat('Y-m-d', (string) ($input['deadline'] ?? ''));

// P73_START_AT — inicio opcional de acceso para asignaciones masivas.
$startAtRaw = trim((string) ($input['start_at'] ?? ''));
$startAt = null;
if ($startAtRaw !== '') {
    $startAt = DateTime::createFromFormat('Y-m-d\TH:i', $startAtRaw);
    if (!$startAt) $startAt = DateTime::createFromFormat('Y-m-d H:i:s', $startAtRaw);
    if (!$startAt) responderJSON(false, null, 'La fecha de inicio de acceso no es válida.', 400);
}
if (!$idTest || !$auditores || !$deadline) responderJSON(false, null, 'Auditores, auditoría o plazo no válidos.', 400);

try {
    evaluacionRequireSchemaAsignacion($pdo);
    auditoriaRequireSchema($pdo);
    $test = cursoObtenerPorId($pdo, $idTest);
    if (!$test) responderJSON(false, null, 'Auditoría no encontrada.', 404);
    auditoriaAssertTest($test);
    auditoriaAssertCompanyAccess($pdo, (int) $test['id_company']);
    if ((int) $test['state'] !== 1) responderJSON(false, null, 'La auditoría está inactiva.', 400);

    $deadlineTs = strtotime($deadline->format('Y-m-d 23:59:59'));

    if ($startAt && $startAt->getTimestamp() > $deadlineTs) responderJSON(false, null, 'La fecha de inicio no puede ser posterior al plazo de realización.', 400);
    $untilTs = !empty($test['effective_date_until']) ? strtotime((string) $test['effective_date_until']) : false;
    if ($deadlineTs !== false && $deadlineTs < time()) responderJSON(false, null, 'El plazo de la auditoría no puede estar vencido.', 400);
    if ($untilTs !== false && $deadlineTs !== false && $deadlineTs > $untilTs) responderJSON(false, null, 'El plazo no puede superar la fecha de término de vigencia de la auditoría.', 400);
    if (!cursoTieneContenidoEjecutable($pdo, $idTest)) responderJSON(false, null, 'La auditoría todavía no tiene preguntas y alternativas ejecutables.', 400);

    $placeholders = implode(',', array_fill(0, count($auditores), '?'));
    $stmtAuditor = $pdo->prepare('SELECT id_users, id_company FROM users WHERE state=1 AND id_users IN (' . $placeholders . ')');
    $stmtAuditor->execute($auditores);
    $encontrados = [];
    foreach ($stmtAuditor->fetchAll() as $row) $encontrados[(string) $row['id_users']] = $row;
    if (count($encontrados) !== count($auditores)) responderJSON(false, null, 'Uno o más auditores no están disponibles.', 400);
    if (!auditoriaIsGlobalAdmin($pdo)) {
        foreach ($auditores as $idAuditor) {
            if ((int) $encontrados[$idAuditor]['id_company'] !== (int) $test['id_company']) {
                responderJSON(false, null, 'Solo puedes asignar auditores pertenecientes a tu empresa.', 403);
            }
        }
    }
    if ($legacySingle && count($auditores) === 1 && evaluacionAsignacionPendienteExiste($pdo, $idTest, $auditores[0])) {
        responderJSON(false, null, 'Este auditor ya tiene una ejecución pendiente de esta auditoría.', 409);
    }

    $creados = [];
    $omitidos = [];
    $pdo->beginTransaction();
    foreach ($auditores as $idAuditor) {
        if (evaluacionAsignacionPendienteExiste($pdo, $idTest, $idAuditor)) {
            $omitidos[] = $idAuditor;
            continue;
        }
        $idAsignacion = asignacionCrear($pdo, $idTest, $idAuditor, (int) $test['id_company'], $deadline->format('Y-m-d 23:59:59'), (string) currentUserId());
        if ($startAt) {
            $startStmt = $pdo->prepare('UPDATE users_test_assigned SET assignamente_date = :start_at, last_update = NOW() WHERE id_user_test_assigned = :id');
            $startStmt->execute([':start_at' => $startAt->format('Y-m-d H:i:s'), ':id' => $idAsignacion]);
        }
        $idAudit = auditoriaCrearDesdeAsignacion($pdo, (int) $test['id_company'], $idTest, $idAsignacion, $idAuditor, (string) currentUserId());
        $creados[] = ['id_users' => $idAuditor, 'id_user_test_assigned' => $idAsignacion, 'id_audits' => $idAudit];
        auditTrailLogAction($pdo, (int) $test['id_company'], 'auditorias', 'users_test_assigned', $idAsignacion,
            'assignment', null, $idAuditor . ' · ' . $deadline->format('Y-m-d'), 'assign', (string) $test['name']);
    }
    $pdo->commit();

    $mensaje = count($creados) . ' auditoría(s) asignada(s).';
    if ($omitidos) $mensaje .= ' ' . count($omitidos) . ' asignación(es) pendiente(s) ya existente(s) fueron omitida(s).';
    responderJSON(true, [
        'created_count' => count($creados),
        'skipped_count' => count($omitidos),
        'assignments' => $creados,
        'skipped_users' => $omitidos,
        'id_user_test_assigned' => $creados ? $creados[0]['id_user_test_assigned'] : null,
        'id_audits' => $creados ? $creados[0]['id_audits'] : null,
    ], $mensaje, $creados ? 201 : 200);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (auditoriaMigrationMessage($e)) responderJSON(false, null, 'El módulo requiere ejecutar la migración 20260908_stage2_audits.sql.', 503);
    error_log('api/auditorias/asignaciones-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo asignar la auditoría.', 500);
}
