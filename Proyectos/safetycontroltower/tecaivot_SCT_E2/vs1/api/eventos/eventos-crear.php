<?php
/**
 * POST /api/eventos/eventos-crear.php
 * Body JSON: { id_company (solo admin), id_company_center, id_project,
 *              id_worker, id_event, event_date, description, criticality, csrf_token }
 *
 * A propósito accesible a cualquier rol logueado (incluido trabajador):
 * reportar un incidente no es una tarea de gestión, es una práctica de
 * seguridad que cualquiera debería poder hacer — ver el comentario en
 * api/eventos/common.php.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

eventosRequireReportarApi($pdo);

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

$idCompanySolicitado = isset($input['id_company']) ? (int) $input['id_company'] : null;
$idCompany = eventosResolveCompanyId($pdo, $idCompanySolicitado);

$faltantes = requerirCampos($input, ['id_company_center', 'id_event', 'event_date', 'description', 'criticality']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

$idCenter = filter_var($input['id_company_center'], FILTER_VALIDATE_INT);
$centrosValidos = array_column(centrosActivosDeEmpresa($pdo, $idCompany), 'id_company_center');
if (!$idCenter || !in_array($idCenter, $centrosValidos, true)) {
    responderJSON(false, null, 'Debes seleccionar un centro/sede válido de la empresa. Si la empresa todavía no tiene ninguno, créalo primero en Gestión de Centros/Sedes.', 400);
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
    // Snapshot histórico del nombre, tal como documenta la columna en el
    // diccionario de base de datos: si el trabajador se edita o se da
    // de baja después, el evento conserva el nombre tal como era.
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
$manana = (new DateTime())->modify('+1 day');
if ($fechaEvento > $manana) {
    responderJSON(false, null, 'La fecha del evento no puede ser en el futuro.', 400);
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

try {
    $idNuevo = eventoCrear($pdo, [
        'id_company'        => $idCompany,
        'id_company_center' => $idCenter,
        'id_project'        => $idProject,
        'id_worker'         => $idWorker,
        'id_worker_name'    => $idWorkerName,
        'id_event'          => $idEvent,
        'event_date'        => $fechaEvento->format('Y-m-d H:i:s'),
        'description'       => $description,
        'criticality'       => $criticality,
    ], currentUserId());

    responderJSON(true, ['id_security_events' => $idNuevo], 'Evento registrado correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/eventos/eventos-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo registrar el evento.', 500);
}
