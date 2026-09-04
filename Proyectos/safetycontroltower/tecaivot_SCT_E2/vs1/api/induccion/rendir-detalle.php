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

requireLogin();

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

    $curso = cursoObtenerPorId($pdo, (int) $asignacion['id_test']);
    if (!$curso || (int) $curso['state'] !== 1) {
        responderJSON(false, null, 'El curso ya no está disponible.', 400);
    }

    $intentosUsados = intentosUsados($pdo, currentUserId(), (int) $curso['id_test']);
    if ($intentosUsados >= (int) $curso['attempts_allowed']) {
        responderJSON(false, null, 'Ya usaste todos los intentos disponibles para este curso.', 400);
    }

    $preguntasCrudas = cursoListarPreguntas($pdo, (int) $curso['id_test']);
    if (empty($preguntasCrudas)) {
        responderJSON(false, null, 'Este curso todavía no tiene preguntas configuradas.', 400);
    }

    $preguntas = [];
    foreach ($preguntasCrudas as $p) {
        $detallePregunta = preguntaObtenerPorId($pdo, (int) $p['id_question']);
        $opciones = array_map(function ($o) {
            return [
                'id_questions_options' => $o['id_questions_options'],
                'text_option'          => $o['text_option'],
                // is_it_co y add_expl_opt quedan afuera a propósito.
            ];
        }, $detallePregunta['opciones']);

        $preguntas[] = [
            'id_rel'      => $p['id_rel'],
            'id_question' => $p['id_question'],
            'question'    => $detallePregunta['question'],
            'url_add_material' => $detallePregunta['url_add_material'],
            'opciones'    => $opciones,
        ];
    }

    responderJSON(true, [
        'id_test'             => $curso['id_test'],
        'name'                => $curso['name'],
        'approval_percentage' => $curso['approval_percentage'],
        'intentos_usados'     => $intentosUsados,
        'attempts_allowed'    => $curso['attempts_allowed'],
        'preguntas'           => $preguntas,
    ]);
} catch (PDOException $e) {
    error_log('api/induccion/rendir-detalle.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo cargar el curso.', 500);
}
