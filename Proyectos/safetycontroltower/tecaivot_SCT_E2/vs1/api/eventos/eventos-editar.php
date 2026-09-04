<?php
/**
 * POST /api/eventos/eventos-editar.php
 * Body JSON: { id_security_events, id_company_center, id_project,
 *              id_worker, id_event, event_date, description, criticality, csrf_token }
 *
 * A diferencia de crear.php, este SÍ está restringido a roles de
 * gestión — una vez reportado, corregir el registro queda en manos de
 * quien gestiona la empresa, no de cualquiera que lo reportó.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireGestionApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$csrfToken = (string) ($input['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

$idEvento = filter_var($input['id_security_events'] ?? null, FILTER_VALIDATE_INT);
if (!$idEvento) {
    responderJSON(false, null, 'Evento no válido.', 400);
}

try {
    $evento = eventoObtenerPorId($pdo, $idEvento);
    if (!$evento) {
        responderJSON(false, null, 'Evento no encontrado.', 404);
    }

    $idCompany = (int) $evento['id_company'];
    if (!eventosIsGlobalAdmin($pdo) && $idCompany !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para editar este evento.', 403);
    }

    $faltantes = requerirCampos($input, ['id_company_center', 'id_event', 'event_date', 'description', 'criticality']);
    if ($faltantes) {
        responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
    }

    $idCenter = filter_var($input['id_company_center'], FILTER_VALIDATE_INT);
    $centrosValidos = array_column(centrosActivosDeEmpresa($pdo, $idCompany), 'id_company_center');
    if (!$idCenter || !in_array($idCenter, $centrosValidos, true)) {
        responderJSON(false, null, 'Centro/sede no válido.', 400);
    }

    $idProject = null;
    if (!empty($input['id_project'])) {
        $idProject = filter_var($input['id_project'], FILTER_VALIDATE_INT);
        $proyectosValidos = array_column(proyectosActivosDeEmpresa($pdo, $idCompany), 'id_project');
        if (!$idProject || !in_array($idProject, $proyectosValidos, true)) {
            responderJSON(false, null, 'El proyecto seleccionado no es válido para esta empresa.', 400);
        }
    }

    $idWorker = null;
    $idWorkerName = null;
    if (!empty($input['id_worker'])) {
        $idWorker = filter_var($input['id_worker'], FILTER_VALIDATE_INT);
        $trabajadores = trabajadoresActivosDeEmpresa($pdo, $idCompany);
        $trabajadorEncontrado = null;
        foreach ($trabajadores as $t) {
            if ((int) $t['id_worker'] === $idWorker) {
                $trabajadorEncontrado = $t;
                break;
            }
        }
        if (!$trabajadorEncontrado) {
            responderJSON(false, null, 'El trabajador seleccionado no es válido para esta empresa.', 400);
        }
        $idWorkerName = trim($trabajadorEncontrado['name'] . ' ' . $trabajadorEncontrado['lastname']);
    }

    $tiposValidos = array_column(eventoTipoListar($pdo, 'seguridad'), 'id_event_type');
    $idEvent = filter_var($input['id_event'], FILTER_VALIDATE_INT);
    if (!$idEvent || !in_array($idEvent, $tiposValidos, true)) {
        responderJSON(false, null, 'Tipo de evento no válido.', 400);
    }

    $fechaEvento = DateTime::createFromFormat('Y-m-d\TH:i', (string) $input['event_date'])
        ?: DateTime::createFromFormat('Y-m-d H:i:s', (string) $input['event_date']);
    if (!$fechaEvento) {
        responderJSON(false, null, 'La fecha del evento no es válida.', 400);
    }

    $description = sanitizarTexto((string) $input['description']);
    if ($description === '') {
        responderJSON(false, null, 'La descripción es obligatoria.', 400);
    }

    $criticidadesValidas = ['baja', 'media', 'alta', 'critica'];
    $criticality = (string) $input['criticality'];
    if (!in_array($criticality, $criticidadesValidas, true)) {
        responderJSON(false, null, 'Nivel de criticidad no válido.', 400);
    }

    eventoActualizar($pdo, $idEvento, [
        'id_company_center' => $idCenter,
        'id_project'        => $idProject,
        'id_worker'         => $idWorker,
        'id_worker_name'    => $idWorkerName,
        'id_event'          => $idEvent,
        'event_date'        => $fechaEvento->format('Y-m-d H:i:s'),
        'description'       => $description,
        'criticality'       => $criticality,
    ]);

    responderJSON(true, null, 'Evento actualizado correctamente.');
} catch (PDOException $e) {
    error_log('api/eventos/eventos-editar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar el evento.', 500);
}
