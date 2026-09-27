<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';

requireCapability($pdo, 'self_assessments.assign');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
$rawUsers = $input['id_users'] ?? [];
$legacySingle = !is_array($rawUsers);
if (!is_array($rawUsers)) $rawUsers = [$rawUsers];
$usuarios = [];
foreach ($rawUsers as $rawUser) {
    $value = trim((string) $rawUser);
    if ($value !== '') $usuarios[$value] = true;
}
$usuarios = array_keys($usuarios);
$deadline = DateTime::createFromFormat('Y-m-d', (string) ($input['deadline'] ?? ''));

// P73_START_AT — inicio opcional de acceso para asignaciones masivas.
$startAtRaw = trim((string) ($input['start_at'] ?? ''));
$startAt = null;
if ($startAtRaw !== '') {
    $startAt = DateTime::createFromFormat('Y-m-d\TH:i', $startAtRaw);
    if (!$startAt) $startAt = DateTime::createFromFormat('Y-m-d H:i:s', $startAtRaw);
    if (!$startAt) responderJSON(false, null, 'La fecha de inicio de acceso no es válida.', 400);
}
if (!$idTest || !$usuarios || !$deadline) responderJSON(false, null, 'Usuarios, autoevaluación o plazo no válidos.', 400);

try {
    evaluacionRequireSchemaAsignacion($pdo);
    $test = cursoObtenerPorId($pdo, $idTest);
    if (!$test) responderJSON(false, null, 'Autoevaluación no encontrada.', 404);
    autoevaluacionAssertTest($test);
    autoevaluacionAssertCompanyAccess($pdo, (int) $test['id_company']);
    if ((int) $test['state'] !== 1) responderJSON(false, null, 'La autoevaluación está inactiva.', 400);

    $deadlineTs = strtotime($deadline->format('Y-m-d 23:59:59'));

    if ($startAt && $startAt->getTimestamp() > $deadlineTs) responderJSON(false, null, 'La fecha de inicio no puede ser posterior al plazo de realización.', 400);
    $untilTs = !empty($test['effective_date_until']) ? strtotime((string) $test['effective_date_until']) : false;
    if ($deadlineTs !== false && $deadlineTs < time()) responderJSON(false, null, 'El plazo de la autoevaluación no puede estar vencido.', 400);
    if ($untilTs !== false && $deadlineTs !== false && $deadlineTs > $untilTs) responderJSON(false, null, 'El plazo no puede superar la fecha de término de vigencia de la autoevaluación.', 400);
    if (!cursoTieneContenidoEjecutable($pdo, $idTest)) responderJSON(false, null, 'La autoevaluación todavía no tiene preguntas y alternativas ejecutables.', 400);

    $placeholders = implode(',', array_fill(0, count($usuarios), '?'));
    $stmt = $pdo->prepare('SELECT id_users,id_company FROM users WHERE state=1 AND id_users IN (' . $placeholders . ')');
    $stmt->execute($usuarios);
    $encontrados = [];
    foreach ($stmt->fetchAll() as $row) $encontrados[(string) $row['id_users']] = $row;
    if (count($encontrados) !== count($usuarios)) responderJSON(false, null, 'Uno o más usuarios no están disponibles.', 400);
    foreach ($usuarios as $idUsuario) {
        if ((int) $encontrados[$idUsuario]['id_company'] !== (int) $test['id_company']) {
            responderJSON(false, null, 'Solo puedes asignar usuarios pertenecientes a la empresa de la autoevaluación.', 403);
        }
    }
    if ($legacySingle && count($usuarios) === 1 && evaluacionAsignacionPendienteExiste($pdo, $idTest, $usuarios[0])) {
        responderJSON(false, null, 'Este usuario ya tiene una ejecución pendiente de esta autoevaluación.', 409);
    }

    $creados = [];
    $omitidos = [];
    $pdo->beginTransaction();
    foreach ($usuarios as $idUsuario) {
        if (evaluacionAsignacionPendienteExiste($pdo, $idTest, $idUsuario)) {
            $omitidos[] = $idUsuario;
            continue;
        }
        $idAsignacion = asignacionCrear($pdo, $idTest, $idUsuario, (int) $test['id_company'], $deadline->format('Y-m-d 23:59:59'), (string) currentUserId());
        if ($startAt) {
            $startStmt = $pdo->prepare('UPDATE users_test_assigned SET assignamente_date = :start_at, last_update = NOW() WHERE id_user_test_assigned = :id');
            $startStmt->execute([':start_at' => $startAt->format('Y-m-d H:i:s'), ':id' => $idAsignacion]);
        }
        $creados[] = ['id_users' => $idUsuario, 'id_user_test_assigned' => $idAsignacion];
        auditTrailLogAction($pdo, (int) $test['id_company'], 'autoevaluaciones', 'users_test_assigned', $idAsignacion,
            'assignment', null, $idUsuario . ' · ' . $deadline->format('Y-m-d'), 'assign', (string) $test['name']);
    }
    $pdo->commit();

    $mensaje = count($creados) . ' autoevaluación(es) asignada(s).';
    if ($omitidos) $mensaje .= ' ' . count($omitidos) . ' asignación(es) pendiente(s) ya existente(s) fueron omitida(s).';
    responderJSON(true, [
        'created_count' => count($creados),
        'skipped_count' => count($omitidos),
        'assignments' => $creados,
        'skipped_users' => $omitidos,
        'id_user_test_assigned' => $creados ? $creados[0]['id_user_test_assigned'] : null,
    ], $mensaje, $creados ? 201 : 200);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (autoevaluacionMigrationMessage($e)) responderJSON(false, null, 'El módulo requiere que la migración de evaluaciones de Etapa 2 esté aplicada.', 503);
    error_log('api/autoevaluaciones/asignaciones-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo asignar la autoevaluación.', 500);
}
