<?php
/**
 * GET /api/induccion/rendir-detalle.php?id_asignacion=7
 *
 * Devuelve las preguntas del curso asignado para que el usuario las
 * responda. A propósito NO incluye is_it_co ni add_expl_opt de las
 * opciones — eso solo se conoce corrigiendo en el servidor, nunca se le
 * manda al navegador antes de que responda.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'induction.execute');

$idAsignacion = filter_input(INPUT_GET, 'id_asignacion', FILTER_VALIDATE_INT);
if (!$idAsignacion) {
    responderJSON(false, null, 'Parámetro "id_asignacion" inválido.', 400);
}

try {
    $asignacion = asignacionObtenerPorId($pdo, $idAsignacion);
    if (!$asignacion || $asignacion['id_users'] !== currentUserId()) {
        // No se distingue "no existe" de "no es tuya", mismo criterio
        // que el resto de los módulos: no filtrar qué IDs existen.
        responderJSON(false, null, 'Asignación no encontrada.', 404);
    }

    if ((int) $asignacion['state'] !== ASIGNACION_PENDIENTE) {
        responderJSON(false, null, 'Este curso ya no está pendiente de rendir.', 400);
    }

    $inicioAsignacion = trim((string) ($asignacion['assignamente_date'] ?? ''));
    if ($inicioAsignacion !== '' && strtotime($inicioAsignacion) !== false && strtotime($inicioAsignacion) > time()) {
        responderJSON(false, null, 'Este curso todavía no está disponible para rendir.', 409);
    }

    $curso = cursoObtenerPorId($pdo, (int) $asignacion['id_test']);
    if (!$curso || (int) $curso['state'] !== 1 || (string) ($curso['type'] ?? '') !== 'induccion') {
        responderJSON(false, null, 'El curso ya no está disponible.', 400);
    }
    if (!cursoTieneContenidoEjecutable($pdo, (int) $curso['id_test'])) {
        responderJSON(false, null, 'El curso todavía no tiene contenido evaluable completo.', 400);
    }

    $intentosUsados = induccionIntentosUsadosPorAsignacion($pdo, $idAsignacion);
    if ($intentosUsados >= (int) $curso['attempts_allowed']) {
        responderJSON(false, null, 'Ya usaste todos los intentos disponibles para este curso.', 400);
    }

    $preguntasCrudas = cursoListarPreguntas($pdo, (int) $curso['id_test']);
    if (empty($preguntasCrudas)) {
        responderJSON(false, null, 'Este curso todavía no tiene preguntas configuradas.', 400);
    }

    $preguntas = [];
    $mediaStmt = null;
    try {
        $hasMedia = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='question_media'")->fetchColumn() > 0;
        if ($hasMedia) $mediaStmt = $pdo->prepare("SELECT media_type,file_path,original_name,mime_type,sort_order FROM question_media WHERE id_question=:id_question ORDER BY sort_order,id_question_media");
    } catch (Throwable $ignore) { $mediaStmt = null; }
    foreach ($preguntasCrudas as $p) {
        $detallePregunta = preguntaObtenerPorId($pdo, (int) $p['id_question']);
        if (!$detallePregunta || empty($detallePregunta['opciones'])) {
            responderJSON(false, null, 'El curso contiene una pregunta incompleta.', 400);
        }
        $opciones = array_map(function ($o) {
            return [
                'id_questions_options' => $o['id_questions_options'],
                'text_option'          => $o['text_option'],
                // is_it_co y add_expl_opt quedan afuera a propósito.
            ];
        }, $detallePregunta['opciones']);

        $media = [];
        if ($mediaStmt) { $mediaStmt->execute(['id_question'=>(int)$p['id_question']]); $media=$mediaStmt->fetchAll(PDO::FETCH_ASSOC); }
        $preguntas[] = [
            'media' => $media,
            'id_rel'      => $p['id_rel'],
            'id_question' => $p['id_question'],
            'question'    => $detallePregunta['question'],
            'url_add_material' => $detallePregunta['url_add_material'],
            'opciones'    => $opciones,
        ];
    }

    $materiales = [];
    foreach (materialListarPorCurso($pdo, (int) $curso['id_test']) as $material) {
        $materiales[] = [
            'id_material' => (int) ($material['id_material'] ?? 0),
            'title' => (string) ($material['title'] ?? ''),
            'material_type' => (string) ($material['material_type'] ?? ''),
            'file_path' => (string) ($material['file_path'] ?? ''),
            'content_text' => (string) ($material['content_text'] ?? ''),
        ];
    }

    responderJSON(true, [
        'id_test'             => $curso['id_test'],
        'name'                => $curso['name'],
        'description'         => (string) ($curso['description'] ?? ''),
        'approval_percentage' => $curso['approval_percentage'],
        'intentos_usados'     => $intentosUsados,
        'attempts_allowed'    => $curso['attempts_allowed'],
        'materiales'          => $materiales,
        'preguntas'           => $preguntas,
    ]);
} catch (PDOException $e) {
    error_log('api/induccion/rendir-detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cargar el curso.', 500);
}
