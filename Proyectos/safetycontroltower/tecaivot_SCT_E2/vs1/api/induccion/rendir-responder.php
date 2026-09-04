<?php
/**
 * POST /api/induccion/rendir-responder.php
 * Body JSON: {
 *   id_asignacion,
 *   respuestas: [ { id_rel, id_question, id_questions_options }, ... ],
 *   csrf_token
 * }
 *
 * El puntaje SIEMPRE se calcula acá, comparando contra
 * questions_options.is_it_co en la base de datos — nunca se confía en
 * un resultado que venga calculado desde el navegador.
 *
 * Si el resultado aprueba, genera el certificado PDF en el mismo
 * request (conecta lib/pdf_certificado.php, antes solo probado con un
 * PDF de ejemplo, al flujo real por primera vez).
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';
require __DIR__ . '/../../lib/pdf_certificado.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    responderJSON(false, null, 'Cuerpo de la solicitud inválido.', 400);
}

$csrfToken = (string) ($input['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

$idAsignacion = filter_var($input['id_asignacion'] ?? null, FILTER_VALIDATE_INT);
if (!$idAsignacion) {
    responderJSON(false, null, 'Asignación no válida.', 400);
}

$respuestasInput = $input['respuestas'] ?? [];
if (!is_array($respuestasInput) || empty($respuestasInput)) {
    responderJSON(false, null, 'Debes responder el curso antes de enviarlo.', 400);
}

try {
    $asignacion = asignacionObtenerPorId($pdo, $idAsignacion);
    if (!$asignacion || $asignacion['id_users'] !== currentUserId()) {
        responderJSON(false, null, 'Asignación no encontrada.', 404);
    }

    if ((int) $asignacion['state'] !== ASIGNACION_PENDIENTE) {
        responderJSON(false, null, 'Este curso ya no está pendiente de rendir.', 400);
    }

    $curso = cursoObtenerPorId($pdo, (int) $asignacion['id_test']);
    if (!$curso || (int) $curso['state'] !== 1) {
        responderJSON(false, null, 'El curso ya no está disponible.', 400);
    }

    $idUsuario = currentUserId();
    $idTest = (int) $curso['id_test'];

    $intentosUsados = intentosUsados($pdo, $idUsuario, $idTest);
    if ($intentosUsados >= (int) $curso['attempts_allowed']) {
        responderJSON(false, null, 'Ya usaste todos los intentos disponibles para este curso.', 400);
    }

    // Validar que las respuestas correspondan exactamente a las preguntas
    // del curso (ni de más ni de menos), y que cada opción pertenezca a
    // la pregunta que dice responder — nunca se confía en lo que venga
    // del cliente sin cruzarlo contra la base de datos.
    $preguntasDelCurso = cursoListarPreguntas($pdo, $idTest);
    $relPorId = [];
    foreach ($preguntasDelCurso as $p) {
        $relPorId[(int) $p['id_rel']] = $p;
    }

    if (count($respuestasInput) !== count($relPorId)) {
        responderJSON(false, null, 'Debes responder todas las preguntas del curso.', 400);
    }

    $respuestasValidadas = [];
    $idsRelRecibidos = [];

    foreach ($respuestasInput as $r) {
        $idRel = filter_var($r['id_rel'] ?? null, FILTER_VALIDATE_INT);
        $idQuestion = filter_var($r['id_question'] ?? null, FILTER_VALIDATE_INT);
        $idOpcion = filter_var($r['id_questions_options'] ?? null, FILTER_VALIDATE_INT);

        if (!$idRel || !$idQuestion || !$idOpcion || !isset($relPorId[$idRel])) {
            responderJSON(false, null, 'Respuesta no válida.', 400);
        }
        if ((int) $relPorId[$idRel]['id_question'] !== $idQuestion) {
            responderJSON(false, null, 'Respuesta no válida.', 400);
        }

        $pregunta = preguntaObtenerPorId($pdo, $idQuestion);
        $opcionValida = false;
        foreach ($pregunta['opciones'] as $o) {
            if ((int) $o['id_questions_options'] === $idOpcion) {
                $opcionValida = true;
                break;
            }
        }
        if (!$opcionValida) {
            responderJSON(false, null, 'Respuesta no válida.', 400);
        }

        $idsRelRecibidos[$idRel] = true;
        $respuestasValidadas[] = [
            'id_rel'               => $idRel,
            'id_question'          => $idQuestion,
            'id_questions_options' => $idOpcion,
        ];
    }

    if (count($idsRelRecibidos) !== count($relPorId)) {
        responderJSON(false, null, 'Debes responder todas las preguntas del curso, sin repetir.', 400);
    }

    $idIntento = siguienteIntento($pdo, $idUsuario, $idTest);

    $pdo->beginTransaction();

    registrarRespuestas($pdo, $idUsuario, (int) $asignacion['id_company'], $idTest, $idIntento, $respuestasValidadas);
    $resultado = calcularResultadoIntento($pdo, $idUsuario, $idTest, $idIntento);

    $aprobado = $resultado['porcentaje'] >= (float) $curso['approval_percentage'];
    $intentosUsadosAhora = $intentosUsados + 1;

    $certificadoInfo = null;

    if ($aprobado) {
        asignacionActualizarEstado($pdo, $idAsignacion, ASIGNACION_APROBADA);

        // Genera el PDF DENTRO de la transacción de datos, pero el
        // archivo en disco no participa del rollback de MySQL — si algo
        // falla después de escribir el PDF, queda un archivo huérfano
        // en vez de un certificado sin registro. Se prioriza no dejar
        // un certificado "fantasma" sin archivo (peor caso: link roto)
        // antes que un archivo sin registro en BD (peor caso: espacio
        // en disco desperdiciado, no afecta al usuario).
        $empresa = empresaObtenerPorId($pdo, (int) $asignacion['id_company']);
        $stmtUsuario = $pdo->prepare('SELECT name, lastname FROM users WHERE id_users = :id LIMIT 1');
        $stmtUsuario->execute(['id' => $idUsuario]);
        $usuario = $stmtUsuario->fetch();

        $codigo = generarCodigoCertificado();
        $rutaPdf = generarCertificadoPdf([
            'nombre_trabajador' => trim(($usuario['name'] ?? '') . ' ' . ($usuario['lastname'] ?? '')),
            'nombre_curso'      => $curso['name'],
            'nombre_empresa'    => $empresa['razon_social'] ?? '',
            'codigo'            => $codigo,
            'fecha'             => date('d-m-Y'),
            'porcentaje'        => $resultado['porcentaje'],
        ]);

        certificadoCrear($pdo, $idAsignacion, $codigo, $rutaPdf, $idUsuario);
        $certificadoInfo = ['codigo' => $codigo];
    } elseif ($intentosUsadosAhora >= (int) $curso['attempts_allowed']) {
        asignacionActualizarEstado($pdo, $idAsignacion, ASIGNACION_REPROBADA);
    }
    // si no aprobó pero le quedan intentos, el estado sigue en Pendiente (1)

    $pdo->commit();

    responderJSON(true, [
        'aprobado'         => $aprobado,
        'porcentaje'       => $resultado['porcentaje'],
        'puntaje_obtenido' => $resultado['puntaje_obtenido'],
        'puntaje_maximo'   => $resultado['puntaje_maximo'],
        'intentos_usados'  => $intentosUsadosAhora,
        'attempts_allowed' => (int) $curso['attempts_allowed'],
        'certificado'      => $certificadoInfo,
    ], $aprobado ? '¡Curso aprobado! Certificado generado.' : 'Respuestas registradas.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('api/induccion/rendir-responder.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo procesar tu respuesta. Intenta nuevamente.', 500);
}
