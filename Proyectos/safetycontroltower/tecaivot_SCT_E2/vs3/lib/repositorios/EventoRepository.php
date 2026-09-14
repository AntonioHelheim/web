<?php
/**
 * lib/repositorios/EventoRepository.php
 * Registro de eventos/incidentes de seguridad, su seguimiento
 * (security_event_tracking) y sus evidencias (security_event_evidence,
 * tabla nueva — ver safetyco_SCT_evidencias_vs1.sql).
 *
 * Nota de diseño: security_events.id_company_center es NOT NULL en el
 * esquema real — todo evento tiene que estar asociado a un centro/sede.
 * Se interpretó como la forma en que el sistema captura "ubicación del
 * evento" (que pide el plan de trabajo): no hay un campo de dirección
 * libre en la tabla, la ubicación ES el centro/sede. id_project e
 * id_worker son opcionales (no todo evento involucra un proyecto o un
 * trabajador identificado).
 *
 * "state" es un int genérico igual que en el resto de las tablas, pero
 * acá se usa con 3 valores con significado propio (mismo criterio que
 * ya se usó en users_test_assigned.state):
 *   1 = Abierto, 2 = En proceso, 3 = Cerrado
 */

const EVENTO_ABIERTO = 1;
const EVENTO_EN_PROCESO = 2;
const EVENTO_CERRADO = 3;

/* =========================================================
   TIPOS DE EVENTO (event_types)
   ========================================================= */

function eventoTipoListar(PDO $pdo, string $modulo = 'seguridad'): array
{
    $stmt = $pdo->prepare(
        'SELECT id_event_type, name, description FROM event_types WHERE module = :module AND state = 1 ORDER BY name'
    );
    $stmt->execute(['module' => $modulo]);

    return $stmt->fetchAll();
}

/* =========================================================
   EVENTOS (security_events)
   ========================================================= */

function eventoListarPorEmpresa(PDO $pdo, int $idCompany, array $filtros = []): array
{
    $sql = 'SELECT e.id_security_events, e.id_company, e.id_company_center, e.id_project, e.id_worker,
                   e.id_worker_name, e.id_event, e.event_date, e.description, e.criticality, e.state,
                   e.date_create, e.last_update,
                   c.name AS center_name, p.name AS project_name, t.name AS event_type_name
            FROM security_events e
            INNER JOIN company_center c ON c.id_company_center = e.id_company_center
            LEFT JOIN projects p ON p.id_project = e.id_project
            INNER JOIN event_types t ON t.id_event_type = e.id_event
            WHERE e.id_company = :id_company AND e.module = \'seguridad\'';
    $params = ['id_company' => $idCompany];

    if (!empty($filtros['id_project'])) {
        $sql .= ' AND e.id_project = :id_project';
        $params['id_project'] = $filtros['id_project'];
    }
    if (!empty($filtros['criticality'])) {
        $sql .= ' AND e.criticality = :criticality';
        $params['criticality'] = $filtros['criticality'];
    }
    if (!empty($filtros['state'])) {
        $sql .= ' AND e.state = :state';
        $params['state'] = $filtros['state'];
    }
    if (!empty($filtros['busqueda'])) {
        $sql .= ' AND e.description LIKE :busqueda';
        $params['busqueda'] = '%' . $filtros['busqueda'] . '%';
    }

    $sql .= ' ORDER BY e.event_date DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function eventoObtenerPorId(PDO $pdo, int $idEvento): ?array
{
    $stmt = $pdo->prepare(
        'SELECT e.*, c.name AS center_name, p.name AS project_name, t.name AS event_type_name
         FROM security_events e
         INNER JOIN company_center c ON c.id_company_center = e.id_company_center
         LEFT JOIN projects p ON p.id_project = e.id_project
         INNER JOIN event_types t ON t.id_event_type = e.id_event
         WHERE e.id_security_events = :id LIMIT 1'
    );
    $stmt->execute(['id' => $idEvento]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function eventoCrear(PDO $pdo, array $datos, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO security_events
            (id_company, id_company_center, id_project, module, id_worker, id_worker_name, id_event,
             event_date, description, criticality, state, created_by, date_create, last_update)
         VALUES
            (:id_company, :id_company_center, :id_project, \'seguridad\', :id_worker, :id_worker_name, :id_event,
             :event_date, :description, :criticality, :state, :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_company'        => $datos['id_company'],
        'id_company_center' => $datos['id_company_center'],
        'id_project'        => $datos['id_project'] ?: null,
        'id_worker'         => $datos['id_worker'] ?: null,
        'id_worker_name'    => $datos['id_worker_name'] ?: null,
        'id_event'          => $datos['id_event'],
        'event_date'        => $datos['event_date'],
        'description'       => $datos['description'],
        'criticality'       => $datos['criticality'],
        'state'             => EVENTO_ABIERTO,
        'created_by'        => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function eventoActualizar(PDO $pdo, int $idEvento, array $datos): bool
{
    $stmt = $pdo->prepare(
        'UPDATE security_events
         SET id_company_center = :id_company_center, id_project = :id_project, id_worker = :id_worker,
             id_worker_name = :id_worker_name, id_event = :id_event, event_date = :event_date,
             description = :description, criticality = :criticality, last_update = NOW()
         WHERE id_security_events = :id'
    );

    return $stmt->execute([
        'id_company_center' => $datos['id_company_center'],
        'id_project'        => $datos['id_project'] ?: null,
        'id_worker'         => $datos['id_worker'] ?: null,
        'id_worker_name'    => $datos['id_worker_name'] ?: null,
        'id_event'          => $datos['id_event'],
        'event_date'        => $datos['event_date'],
        'description'       => $datos['description'],
        'criticality'       => $datos['criticality'],
        'id'                => $idEvento,
    ]);
}

function eventoCambiarEstado(PDO $pdo, int $idEvento, int $nuevoEstado): bool
{
    $stmt = $pdo->prepare('UPDATE security_events SET state = :state, last_update = NOW() WHERE id_security_events = :id');
    return $stmt->execute(['state' => $nuevoEstado, 'id' => $idEvento]);
}

/* =========================================================
   SEGUIMIENTO (security_event_tracking)
   ========================================================= */

function trackingListarPorEvento(PDO $pdo, int $idEvento): array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM security_event_tracking WHERE id_security_events = :id ORDER BY date_create DESC'
    );
    $stmt->execute(['id' => $idEvento]);

    return $stmt->fetchAll();
}

function trackingCrear(PDO $pdo, array $datos, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO security_event_tracking
            (id_security_events, tracking_description, person_charge, commitment_date, deadline, created_by, date_create, last_update)
         VALUES
            (:id_security_events, :tracking_description, :person_charge, :commitment_date, :deadline, :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_security_events'    => $datos['id_security_events'],
        'tracking_description'  => $datos['tracking_description'],
        'person_charge'         => $datos['person_charge'],
        'commitment_date'       => $datos['commitment_date'],
        'deadline'               => $datos['deadline'],
        'created_by'            => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

/* =========================================================
   EVIDENCIAS (security_event_evidence)
   ========================================================= */

function evidenciaListarPorEvento(PDO $pdo, int $idEvento): array
{
    $stmt = $pdo->prepare(
        'SELECT id_evidence, file_path, original_name, file_type, uploaded_by, date_create
         FROM security_event_evidence WHERE id_security_events = :id ORDER BY date_create'
    );
    $stmt->execute(['id' => $idEvento]);

    return $stmt->fetchAll();
}

function evidenciaObtenerPorId(PDO $pdo, int $idEvidencia): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM security_event_evidence WHERE id_evidence = :id LIMIT 1');
    $stmt->execute(['id' => $idEvidencia]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function evidenciaCrear(PDO $pdo, int $idEvento, string $filePath, string $originalName, string $fileType, string $subidoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO security_event_evidence (id_security_events, file_path, original_name, file_type, uploaded_by, date_create)
         VALUES (:id_security_events, :file_path, :original_name, :file_type, :uploaded_by, NOW())'
    );
    $stmt->execute([
        'id_security_events' => $idEvento,
        'file_path'          => $filePath,
        'original_name'      => $originalName,
        'file_type'          => $fileType,
        'uploaded_by'        => $subidoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function evidenciaEliminar(PDO $pdo, int $idEvidencia): bool
{
    $stmt = $pdo->prepare('DELETE FROM security_event_evidence WHERE id_evidence = :id');
    return $stmt->execute(['id' => $idEvidencia]);
}

/* =========================================================
   CENTROS / PROYECTOS / TRABAJADORES ACTIVOS (para los selects del formulario)
   ========================================================= */

function centrosActivosDeEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare('SELECT id_company_center, name FROM company_center WHERE id_company = :id AND state = 1 ORDER BY name');
    $stmt->execute(['id' => $idCompany]);

    return $stmt->fetchAll();
}

function proyectosActivosDeEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare('SELECT id_project, name FROM projects WHERE id_company = :id AND state = 1 ORDER BY name');
    $stmt->execute(['id' => $idCompany]);

    return $stmt->fetchAll();
}

function trabajadoresActivosDeEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare('SELECT id_worker, rut, name, lastname FROM workers WHERE id_company = :id AND state = 1 ORDER BY lastname, name');
    $stmt->execute(['id' => $idCompany]);

    return $stmt->fetchAll();
}
