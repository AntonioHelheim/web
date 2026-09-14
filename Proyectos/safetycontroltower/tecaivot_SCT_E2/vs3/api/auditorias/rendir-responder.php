<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';
require __DIR__ . '/../../lib/repositorios/AuditoriaRepository.php';

requireCapability($pdo, 'audits.execute');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idAsignacion = filter_var($input['id_asignacion'] ?? null, FILTER_VALIDATE_INT);
$respuestasInput = $input['respuestas'] ?? [];
$observaciones = trim((string) ($input['observaciones'] ?? ''));
if (!$idAsignacion || !is_array($respuestasInput) || !$respuestasInput) responderJSON(false, null, 'Debes responder la auditoría.', 400);
if (sctTextLength($observaciones) > 5000) responderJSON(false, null, 'Las observaciones no pueden superar 5000 caracteres.', 400);

try {
    evaluacionRequireSchemaAsignacion($pdo);
    auditoriaRequireSchema($pdo);

    $asignacion = asignacionObtenerPorId($pdo, $idAsignacion);
    if (!$asignacion || (string) $asignacion['id_users'] !== (string) currentUserId()) responderJSON(false, null, 'Asignación no encontrada.', 404);
    if ((int) $asignacion['state'] !== ASIGNACION_PENDIENTE) responderJSON(false, null, 'Esta auditoría ya fue finalizada.', 400);
    if (!empty($asignacion['deadline']) && strtotime((string) $asignacion['deadline']) < time()) responderJSON(false, null, 'El plazo de esta auditoría ya venció.', 400);

    $test = cursoObtenerPorId($pdo, (int) $asignacion['id_test']);
    if (!$test || (int) $test['state'] !== 1) responderJSON(false, null, 'La auditoría ya no está disponible.', 400);
    auditoriaAssertTest($test);
    if (!auditoriaTestVigente($test)) responderJSON(false, null, 'La auditoría está fuera de su período de vigencia.', 400);

    $preguntas = cursoListarPreguntas($pdo, (int) $test['id_test']);
    $relPorId = [];
    foreach ($preguntas as $p) $relPorId[(int) $p['id_rel']] = $p;
    if (count($respuestasInput) !== count($relPorId)) responderJSON(false, null, 'Debes responder todas las preguntas.', 400);

    $validadas = [];
    $vistos = [];
    foreach ($respuestasInput as $r) {
        $idRel = filter_var($r['id_rel'] ?? null, FILTER_VALIDATE_INT);
        $idQuestion = filter_var($r['id_question'] ?? null, FILTER_VALIDATE_INT);
        $idOpcion = filter_var($r['id_questions_options'] ?? null, FILTER_VALIDATE_INT);
        if (!$idRel || !$idQuestion || !$idOpcion || !isset($relPorId[$idRel]) || isset($vistos[$idRel])) responderJSON(false, null, 'Respuesta no válida.', 400);
        if ((int) $relPorId[$idRel]['id_question'] !== $idQuestion) responderJSON(false, null, 'Respuesta no válida.', 400);

        $detalle = preguntaObtenerPorId($pdo, $idQuestion);
        if (!$detalle) responderJSON(false, null, 'Respuesta no válida.', 400);
        $opcionOk = false;
        foreach ($detalle['opciones'] as $opcion) {
            if ((int) $opcion['id_questions_options'] === $idOpcion) { $opcionOk = true; break; }
        }
        if (!$opcionOk) responderJSON(false, null, 'Respuesta no válida.', 400);
        $vistos[$idRel] = true;
        $validadas[] = ['id_rel' => $idRel, 'id_question' => $idQuestion, 'id_questions_options' => $idOpcion];
    }

    $puntajeMaximo = cursoPuntajeMaximo($pdo, (int) $test['id_test']);

    $pdo->beginTransaction();

    // Serializa envíos concurrentes de la misma asignación para impedir
    // duplicar un intento por doble clic o solicitudes simultáneas.
    $lock = $pdo->prepare(
        'SELECT state, deadline
         FROM users_test_assigned
         WHERE id_user_test_assigned = :id_asignacion
         FOR UPDATE'
    );
    $lock->execute(['id_asignacion' => $idAsignacion]);
    $asignacionBloqueada = $lock->fetch();
    if (!$asignacionBloqueada || (int) $asignacionBloqueada['state'] !== ASIGNACION_PENDIENTE) {
        $pdo->rollBack();
        responderJSON(false, null, 'Esta auditoría ya fue finalizada.', 409);
    }
    if (!empty($asignacionBloqueada['deadline']) && strtotime((string) $asignacionBloqueada['deadline']) < time()) {
        $pdo->rollBack();
        responderJSON(false, null, 'El plazo de esta auditoría ya venció.', 400);
    }

    $intentosUsados = evaluacionIntentosUsadosPorAsignacion($pdo, $idAsignacion);
    if ($intentosUsados >= (int) $test['attempts_allowed']) {
        $pdo->rollBack();
        responderJSON(false, null, 'No quedan intentos disponibles.', 400);
    }

    $intento = evaluacionSiguienteIntentoPorAsignacion($pdo, $idAsignacion);

    evaluacionRegistrarRespuestasPorAsignacion(
        $pdo,
        $idAsignacion,
        (string) currentUserId(),
        (int) $asignacion['id_company'],
        (int) $test['id_test'],
        $intento,
        $validadas
    );
    $resultado = evaluacionCalcularResultadoPorAsignacion($pdo, $idAsignacion, $intento, $puntajeMaximo);

    $cumple = $resultado['porcentaje'] >= (float) $test['approval_percentage'];
    $intentosAhora = $intentosUsados + 1;
    $sinIntentos = $intentosAhora >= (int) $test['attempts_allowed'];
    $finalizada = $cumple || $sinIntentos;

    if ($finalizada) {
        asignacionActualizarEstado($pdo, $idAsignacion, $cumple ? ASIGNACION_APROBADA : ASIGNACION_REPROBADA);
        auditoriaCompletar($pdo, $idAsignacion, (float) $resultado['porcentaje'], $observaciones);
    } else {
        auditoriaActualizarProgreso($pdo, $idAsignacion, (float) $resultado['porcentaje'], $observaciones);
    }
    $pdo->commit();

    responderJSON(true, [
        'cumple' => $cumple,
        'finalizada' => $finalizada,
        'porcentaje' => $resultado['porcentaje'],
        'puntaje_obtenido' => $resultado['puntaje_obtenido'],
        'puntaje_maximo' => $resultado['puntaje_maximo'],
        'intentos_usados' => $intentosAhora,
        'attempts_allowed' => (int) $test['attempts_allowed'],
    ], $finalizada ? 'Auditoría finalizada y registrada.' : 'Intento registrado. La auditoría permanece pendiente.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (auditoriaMigrationMessage($e)) responderJSON(false, null, 'El módulo requiere ejecutar la migración 20260908_stage2_audits.sql.', 503);
    error_log('api/auditorias/rendir-responder.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo procesar la auditoría.', 500);
}
