<?php
/**
 * POST /api/induccion/asignaciones-crear.php
 * Body JSON compatible:
 * - individual: { id_test, id_users: "correo", deadline, csrf_token }
 * - masivo:     { id_test, id_users: ["correo1","correo2"], deadline, csrf_token }
 */
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'induction.assign');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST;
$csrfToken = (string) ($input['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

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
if (!$idTest || !$usuarios) responderJSON(false, null, 'Curso o usuarios no válidos.', 400);

$deadline = DateTime::createFromFormat('Y-m-d', (string) ($input['deadline'] ?? ''));

// P73_START_AT — inicio opcional de acceso para asignaciones masivas.
$startAtRaw = trim((string) ($input['start_at'] ?? ''));
$startAt = null;
if ($startAtRaw !== '') {
    $startAt = DateTime::createFromFormat('Y-m-d\TH:i', $startAtRaw);
    if (!$startAt) $startAt = DateTime::createFromFormat('Y-m-d H:i:s', $startAtRaw);
    if (!$startAt) responderJSON(false, null, 'La fecha de inicio de acceso no es válida.', 400);
}
if (!$deadline) responderJSON(false, null, 'El plazo de vencimiento no es válido.', 400);

try {
    $curso = cursoObtenerPorId($pdo, $idTest);
    if (!$curso) responderJSON(false, null, 'Curso no encontrado.', 404);
    if ((string) ($curso['type'] ?? '') !== 'induccion') responderJSON(false, null, 'La actividad seleccionada no corresponde a una inducción.', 400);
    if ((int) ($curso['state'] ?? 0) !== 1) responderJSON(false, null, 'El curso está inactivo y no puede asignarse.', 400);

    $idCompany = (int) $curso['id_company'];
    if (!induccionIsGlobalAdmin($pdo) && $idCompany !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para asignar este curso.', 403);
    }

    $deadlineTs = strtotime($deadline->format('Y-m-d 23:59:59'));

    if ($startAt && $startAt->getTimestamp() > $deadlineTs) responderJSON(false, null, 'La fecha de inicio no puede ser posterior al plazo de realización.', 400);
    $untilTs = !empty($curso['effective_date_until']) ? strtotime((string) $curso['effective_date_until']) : false;
    if ($deadlineTs !== false && $deadlineTs < time()) responderJSON(false, null, 'El plazo del curso no puede estar vencido.', 400);
    if ($untilTs !== false && $deadlineTs !== false && $deadlineTs > $untilTs) responderJSON(false, null, 'El plazo no puede superar la fecha de término de vigencia del curso.', 400);
    if (!cursoTieneContenidoEjecutable($pdo, $idTest)) responderJSON(false, null, 'El curso todavía no tiene una evaluación ejecutable. Revisa sus preguntas y alternativas antes de asignarlo.', 400);

    $usuariosValidos = array_column(usuariosActivosDeEmpresa($pdo, $idCompany), 'id_users');
    $invalidos = array_values(array_diff($usuarios, $usuariosValidos));
    if ($invalidos) responderJSON(false, ['invalid_users' => $invalidos], 'Uno o más usuarios no existen, están inactivos o no pertenecen a esta empresa.', 400);
    if ($legacySingle && count($usuarios) === 1 && asignacionYaExisteActiva($pdo, $idTest, $usuarios[0])) {
        responderJSON(false, null, 'Este usuario ya tiene este curso asignado (pendiente o aprobado).', 409);
    }

    $creados = [];
    $omitidos = [];
    $pdo->beginTransaction();
    foreach ($usuarios as $idUsuario) {
        if (asignacionYaExisteActiva($pdo, $idTest, $idUsuario)) {
            $omitidos[] = $idUsuario;
            continue;
        }
        $idNueva = asignacionCrear($pdo, $idTest, $idUsuario, $idCompany, $deadline->format('Y-m-d 23:59:59'), currentUserId());
        if ($startAt) {
            $startStmt = $pdo->prepare('UPDATE users_test_assigned SET assignamente_date = :start_at, last_update = NOW() WHERE id_user_test_assigned = :id');
            $startStmt->execute([':start_at' => $startAt->format('Y-m-d H:i:s'), ':id' => $idNueva]);
        }
        $creados[] = ['id_users' => $idUsuario, 'id_user_test_assigned' => $idNueva];
        auditTrailLogAction($pdo, $idCompany, 'induccion', 'users_test_assigned', $idNueva, 'assignment', null,
            $idUsuario . ' · ' . $deadline->format('Y-m-d'), 'assign', (string) $curso['name']);
    }
    $pdo->commit();

    $mensaje = count($creados) . ' asignación(es) creada(s).';
    if ($omitidos) $mensaje .= ' ' . count($omitidos) . ' ya existente(s) fueron omitida(s).';
    responderJSON(true, [
        'created_count' => count($creados),
        'skipped_count' => count($omitidos),
        'assignments' => $creados,
        'skipped_users' => $omitidos,
        'id_user_test_assigned' => $creados ? $creados[0]['id_user_test_assigned'] : null,
    ], $mensaje, $creados ? 201 : 200);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('api/induccion/asignaciones-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo asignar el curso.', 500);
}
