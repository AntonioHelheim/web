<?php
/**
 * Repositorio del módulo Protocolos MINSAL (Etapa 2).
 *
 * El catálogo vive en `protocols`; la operación se descompone en:
 * - protocol_forms
 * - protocol_assignments
 * - protocol_executions
 * - protocol_execution_submissions
 * - protocol_tracking
 *
 * Las respuestas de formularios se mantienen en el motor existente:
 * dynamic_form_submissions / dynamic_form_answers.
 */

function protocoloSchemaReady(PDO $pdo): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $tables = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.TABLES "
            . "WHERE TABLE_SCHEMA=DATABASE() "
            . "AND TABLE_NAME IN ("
            . "'protocol_forms','protocol_assignments','protocol_executions',"
            . "'protocol_execution_submissions','protocol_tracking'"
            . ")"
        );

        if ((int) $tables->fetchColumn() !== 5) {
            return $ready = false;
        }

        $columns = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS "
            . "WHERE TABLE_SCHEMA=DATABASE() "
            . "AND TABLE_NAME='protocols' "
            . "AND COLUMN_NAME IN ("
            . "'version','authority','normative_reference','source_url',"
            . "'effective_date_from','effective_date_until'"
            . ")"
        );

        return $ready = ((int) $columns->fetchColumn() === 6);
    } catch (Throwable $e) {
        error_log('ProtocoloRepository protocoloSchemaReady: ' . $e->getMessage());
        return $ready = false;
    }
}

function protocoloRequireSchema(PDO $pdo): void
{
    if (!protocoloSchemaReady($pdo)) {
        throw new RuntimeException('MIGRATION_REQUIRED_PROTOCOLS');
    }
}

function protocoloDecodeParameters(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function protocoloNormalizeRow(array $row): array
{
    $row['parameters_decoded'] = protocoloDecodeParameters($row['parameters'] ?? null);
    if (array_key_exists('parameter_overrides', $row)) {
        $row['parameter_overrides_decoded'] = protocoloDecodeParameters($row['parameter_overrides'] ?? null);
    }
    return $row;
}

function protocoloObtener(PDO $pdo, int $idProtocol): ?array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT p.*, c.razon_social AS company_name, '
        . '       (SELECT COUNT(*) FROM protocol_assignments pa WHERE pa.id_protocol=p.id_protocol) AS assignment_count '
        . 'FROM protocols p '
        . 'LEFT JOIN company c ON c.id_company=p.id_company '
        . 'WHERE p.id_protocol=:id_protocol '
        . 'LIMIT 1'
    );
    $stmt->execute(['id_protocol' => $idProtocol]);
    $row = $stmt->fetch();

    return $row ? protocoloNormalizeRow($row) : null;
}

function protocoloListarGestion(PDO $pdo, ?int $idCompany, bool $globalAdmin): array
{
    protocoloRequireSchema($pdo);

    if ($globalAdmin && ($idCompany === null || $idCompany <= 0)) {
        $stmt = $pdo->query(
            'SELECT p.*, c.razon_social AS company_name, '
            . '       (SELECT COUNT(*) FROM protocol_forms pf WHERE pf.id_protocol=p.id_protocol AND pf.id_company IS NULL) AS form_count, '
            . '       (SELECT COUNT(*) FROM protocol_assignments pa WHERE pa.id_protocol=p.id_protocol) AS assignment_count '
            . 'FROM protocols p '
            . 'LEFT JOIN company c ON c.id_company=p.id_company '
            . 'WHERE p.id_company IS NULL '
            . 'ORDER BY p.state DESC,p.name ASC'
        );
        $rows = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare(
            'SELECT p.*, c.razon_social AS company_name, '
            . '       (SELECT COUNT(*) FROM protocol_forms pf '
            . '         WHERE pf.id_protocol=p.id_protocol '
            . '           AND (pf.id_company IS NULL OR pf.id_company=:forms_company)) AS form_count, '
            . '       (SELECT COUNT(*) FROM protocol_assignments pa '
            . '         WHERE pa.id_protocol=p.id_protocol AND pa.id_company=:assign_company) AS assignment_count '
            . 'FROM protocols p '
            . 'LEFT JOIN company c ON c.id_company=p.id_company '
            . 'WHERE p.id_company IS NULL OR p.id_company=:owner_company '
            . 'ORDER BY (p.id_company IS NULL) DESC,p.state DESC,p.name ASC'
        );
        $stmt->execute([
            'forms_company' => $idCompany,
            'assign_company' => $idCompany,
            'owner_company' => $idCompany,
        ]);
        $rows = $stmt->fetchAll();
    }

    $result = [];
    foreach ($rows as $row) {
        $result[] = protocoloNormalizeRow($row);
    }
    return $result;
}

function protocoloCrear(
    PDO $pdo,
    ?int $idCompany,
    string $code,
    string $name,
    ?string $description,
    int $version,
    ?string $authority,
    ?string $normativeReference,
    ?string $sourceUrl,
    ?string $effectiveFrom,
    ?string $effectiveUntil,
    ?string $parameters,
    string $createdBy
): int {
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO protocols '
        . '(id_company,code,name,description,version,authority,normative_reference,source_url,'
        . ' effective_date_from,effective_date_until,parameters,state,created_by,date_create,last_update) '
        . 'VALUES '
        . '(:id_company,:code,:name,:description,:version,:authority,:normative_reference,:source_url,'
        . ' :effective_date_from,:effective_date_until,:parameters,1,:created_by,NOW(),NOW())'
    );
    $stmt->execute([
        'id_company' => $idCompany,
        'code' => $code,
        'name' => $name,
        'description' => $description,
        'version' => $version,
        'authority' => $authority,
        'normative_reference' => $normativeReference,
        'source_url' => $sourceUrl,
        'effective_date_from' => $effectiveFrom,
        'effective_date_until' => $effectiveUntil,
        'parameters' => $parameters,
        'created_by' => $createdBy,
    ]);
    return (int) $pdo->lastInsertId();
}

function protocoloEditar(
    PDO $pdo,
    int $idProtocol,
    string $code,
    string $name,
    ?string $description,
    int $version,
    ?string $authority,
    ?string $normativeReference,
    ?string $sourceUrl,
    ?string $effectiveFrom,
    ?string $effectiveUntil,
    ?string $parameters
): void {
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'UPDATE protocols SET '
        . 'code=:code,name=:name,description=:description,version=:version,authority=:authority,'
        . 'normative_reference=:normative_reference,source_url=:source_url,'
        . 'effective_date_from=:effective_date_from,effective_date_until=:effective_date_until,'
        . 'parameters=:parameters,last_update=NOW() '
        . 'WHERE id_protocol=:id_protocol'
    );
    $stmt->execute([
        'code' => $code,
        'name' => $name,
        'description' => $description,
        'version' => $version,
        'authority' => $authority,
        'normative_reference' => $normativeReference,
        'source_url' => $sourceUrl,
        'effective_date_from' => $effectiveFrom,
        'effective_date_until' => $effectiveUntil,
        'parameters' => $parameters,
        'id_protocol' => $idProtocol,
    ]);
}

function protocoloCambiarEstado(PDO $pdo, int $idProtocol, int $state): void
{
    protocoloRequireSchema($pdo);
    $stmt = $pdo->prepare(
        'UPDATE protocols SET state=:state,last_update=NOW() WHERE id_protocol=:id_protocol'
    );
    $stmt->execute([
        'state' => $state,
        'id_protocol' => $idProtocol,
    ]);
}

function protocoloTieneAsignaciones(PDO $pdo, int $idProtocol, ?int $idCompany = null): bool
{
    protocoloRequireSchema($pdo);

    $sql = 'SELECT COUNT(*) FROM protocol_assignments WHERE id_protocol=:id_protocol';
    $params = ['id_protocol' => $idProtocol];

    if ($idCompany !== null) {
        $sql .= ' AND id_company=:id_company';
        $params['id_company'] = $idCompany;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

function protocoloFormularios(PDO $pdo, int $idProtocol, int $idCompany): array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT pf.*,f.name AS form_name,f.description AS form_description,f.state AS form_state,'
        . '       f.id_company AS form_company,c.razon_social AS form_company_name,'
        . '       (SELECT COUNT(*) FROM dynamic_form_fields ff WHERE ff.id_form=f.id_form) AS field_count '
        . 'FROM protocol_forms pf '
        . 'INNER JOIN dynamic_forms f ON f.id_form=pf.id_form '
        . 'LEFT JOIN company c ON c.id_company=f.id_company '
        . 'WHERE pf.id_protocol=:id_protocol '
        . '  AND (pf.id_company IS NULL OR pf.id_company=:id_company) '
        . '  AND NOT (pf.id_company IS NULL AND EXISTS ('
        . '      SELECT 1 FROM protocol_forms pf2 '
        . '      WHERE pf2.id_protocol=pf.id_protocol '
        . '        AND pf2.id_company=:id_company_override '
        . '        AND pf2.id_form=pf.id_form'
        . '  )) '
        . 'ORDER BY pf.sort_order ASC,pf.id_protocol_form ASC'
    );
    $stmt->execute([
        'id_protocol' => $idProtocol,
        'id_company' => $idCompany,
        'id_company_override' => $idCompany,
    ]);

    return $stmt->fetchAll();
}

function protocoloFormularioObtener(PDO $pdo, int $idProtocolForm): ?array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT pf.*,p.id_company AS protocol_company,p.state AS protocol_state,'
        . '       f.id_company AS form_company,f.state AS form_state,f.name AS form_name '
        . 'FROM protocol_forms pf '
        . 'INNER JOIN protocols p ON p.id_protocol=pf.id_protocol '
        . 'INNER JOIN dynamic_forms f ON f.id_form=pf.id_form '
        . 'WHERE pf.id_protocol_form=:id_protocol_form LIMIT 1'
    );
    $stmt->execute(['id_protocol_form' => $idProtocolForm]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function protocoloFormularioAgregar(
    PDO $pdo,
    int $idProtocol,
    ?int $implementationCompany,
    int $idForm,
    int $required,
    int $sortOrder,
    string $createdBy
): int {
    protocoloRequireSchema($pdo);

    $check = $pdo->prepare(
        'SELECT id_protocol_form FROM protocol_forms '
        . 'WHERE id_protocol=:id_protocol '
        . '  AND id_form=:id_form '
        . '  AND ((id_company IS NULL AND :company_null=1) OR id_company=:id_company) '
        . 'LIMIT 1'
    );
    $check->execute([
        'id_protocol' => $idProtocol,
        'id_form' => $idForm,
        'company_null' => $implementationCompany === null ? 1 : 0,
        'id_company' => $implementationCompany,
    ]);
    $existing = $check->fetchColumn();
    if ($existing) {
        return (int) $existing;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO protocol_forms '
        . '(id_protocol,id_company,id_form,is_required,sort_order,created_by,date_create,last_update) '
        . 'VALUES (:id_protocol,:id_company,:id_form,:is_required,:sort_order,:created_by,NOW(),NOW())'
    );
    $stmt->execute([
        'id_protocol' => $idProtocol,
        'id_company' => $implementationCompany,
        'id_form' => $idForm,
        'is_required' => $required,
        'sort_order' => $sortOrder,
        'created_by' => $createdBy,
    ]);
    return (int) $pdo->lastInsertId();
}

function protocoloFormularioEliminar(PDO $pdo, int $idProtocolForm): void
{
    protocoloRequireSchema($pdo);
    $stmt = $pdo->prepare('DELETE FROM protocol_forms WHERE id_protocol_form=:id_protocol_form');
    $stmt->execute(['id_protocol_form' => $idProtocolForm]);
}

function protocoloFormulariosDisponibles(PDO $pdo, int $idCompany): array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT f.id_form,f.id_company,f.name,f.description,f.state,'
        . '       c.razon_social AS company_name,'
        . '       (SELECT COUNT(*) FROM dynamic_form_fields ff WHERE ff.id_form=f.id_form) AS field_count '
        . 'FROM dynamic_forms f '
        . 'LEFT JOIN company c ON c.id_company=f.id_company '
        . 'WHERE f.state=1 AND (f.id_company IS NULL OR f.id_company=:id_company) '
        . 'ORDER BY (f.id_company IS NULL) DESC,f.name ASC'
    );
    $stmt->execute(['id_company' => $idCompany]);
    return $stmt->fetchAll();
}

function protocoloAsignacionCrear(
    PDO $pdo,
    int $idProtocol,
    int $idCompany,
    ?int $idCenter,
    ?int $idProject,
    ?int $idWorker,
    string $responsibleUser,
    string $startAt,
    string $nextDueAt,
    string $recurrenceUnit,
    ?int $recurrenceInterval,
    ?string $overrides,
    ?string $notes,
    string $createdBy
): int {
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO protocol_assignments '
        . '(id_protocol,id_company,id_company_center,id_project,id_worker,responsible_user,'
        . ' start_at,next_due_at,recurrence_unit,recurrence_interval,parameter_overrides,notes,state,'
        . ' created_by,date_create,last_update) '
        . 'VALUES '
        . '(:id_protocol,:id_company,:id_company_center,:id_project,:id_worker,:responsible_user,'
        . ' :start_at,:next_due_at,:recurrence_unit,:recurrence_interval,:parameter_overrides,:notes,'
        . " 'activa',:created_by,NOW(),NOW())"
    );
    $stmt->execute([
        'id_protocol' => $idProtocol,
        'id_company' => $idCompany,
        'id_company_center' => $idCenter,
        'id_project' => $idProject,
        'id_worker' => $idWorker,
        'responsible_user' => $responsibleUser,
        'start_at' => $startAt,
        'next_due_at' => $nextDueAt,
        'recurrence_unit' => $recurrenceUnit,
        'recurrence_interval' => $recurrenceInterval,
        'parameter_overrides' => $overrides,
        'notes' => $notes,
        'created_by' => $createdBy,
    ]);
    return (int) $pdo->lastInsertId();
}

function protocoloAsignacionObtener(PDO $pdo, int $idAssignment): ?array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT pa.*,p.code,p.name AS protocol_name,p.description AS protocol_description,'
        . '       p.parameters,p.version,p.authority,p.normative_reference,p.source_url,p.effective_date_from,p.effective_date_until,p.state AS protocol_state,'
        . '       c.razon_social AS company_name,cc.name AS center_name,pr.name AS project_name,'
        . '       w.name AS worker_name,w.lastname AS worker_lastname,w.rut AS worker_rut,'
        . '       u.name AS responsible_name,u.lastname AS responsible_lastname,'
        . '       CASE WHEN pa.state=\'activa\' AND pa.next_due_at<NOW() THEN 1 ELSE 0 END AS is_overdue,'
        . '       (SELECT COUNT(*) FROM protocol_executions pe WHERE pe.id_protocol_assignment=pa.id_protocol_assignment) AS execution_count,'
        . '       (SELECT pe2.result FROM protocol_executions pe2 '
        . '         WHERE pe2.id_protocol_assignment=pa.id_protocol_assignment '
        . '         ORDER BY pe2.cycle_number DESC LIMIT 1) AS latest_result '
        . 'FROM protocol_assignments pa '
        . 'INNER JOIN protocols p ON p.id_protocol=pa.id_protocol '
        . 'INNER JOIN company c ON c.id_company=pa.id_company '
        . 'LEFT JOIN company_center cc ON cc.id_company_center=pa.id_company_center '
        . 'LEFT JOIN projects pr ON pr.id_project=pa.id_project '
        . 'LEFT JOIN workers w ON w.id_worker=pa.id_worker '
        . 'INNER JOIN users u ON u.id_users=pa.responsible_user '
        . 'WHERE pa.id_protocol_assignment=:id_assignment '
        . 'LIMIT 1'
    );
    $stmt->execute(['id_assignment' => $idAssignment]);
    $row = $stmt->fetch();

    return $row ? protocoloNormalizeRow($row) : null;
}

function protocoloAsignacionesListar(PDO $pdo, int $idProtocol, int $idCompany): array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT pa.*,cc.name AS center_name,pr.name AS project_name,'
        . '       w.name AS worker_name,w.lastname AS worker_lastname,w.rut AS worker_rut,'
        . '       u.name AS responsible_name,u.lastname AS responsible_lastname,'
        . '       CASE WHEN pa.state=\'activa\' AND pa.next_due_at<NOW() THEN 1 ELSE 0 END AS is_overdue,'
        . '       (SELECT COUNT(*) FROM protocol_executions pe WHERE pe.id_protocol_assignment=pa.id_protocol_assignment) AS execution_count,'
        . '       (SELECT pe2.result FROM protocol_executions pe2 '
        . '         WHERE pe2.id_protocol_assignment=pa.id_protocol_assignment '
        . '         ORDER BY pe2.cycle_number DESC LIMIT 1) AS latest_result '
        . 'FROM protocol_assignments pa '
        . 'LEFT JOIN company_center cc ON cc.id_company_center=pa.id_company_center '
        . 'LEFT JOIN projects pr ON pr.id_project=pa.id_project '
        . 'LEFT JOIN workers w ON w.id_worker=pa.id_worker '
        . 'INNER JOIN users u ON u.id_users=pa.responsible_user '
        . 'WHERE pa.id_protocol=:id_protocol AND pa.id_company=:id_company '
        . 'ORDER BY pa.state=\'activa\' DESC,pa.next_due_at ASC,pa.id_protocol_assignment DESC'
    );
    $stmt->execute([
        'id_protocol' => $idProtocol,
        'id_company' => $idCompany,
    ]);

    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        $rows[] = protocoloNormalizeRow($row);
    }
    return $rows;
}

function protocoloAsignacionesUsuario(PDO $pdo, string $idUsers): array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT pa.*,p.code,p.name AS protocol_name,p.description AS protocol_description,'
        . '       p.version,p.authority,p.normative_reference,p.source_url,p.state AS protocol_state,'
        . '       c.razon_social AS company_name,cc.name AS center_name,pr.name AS project_name,'
        . '       w.name AS worker_name,w.lastname AS worker_lastname,w.rut AS worker_rut,'
        . '       CASE WHEN pa.state=\'activa\' AND pa.next_due_at<NOW() THEN 1 ELSE 0 END AS is_overdue,'
        . '       (SELECT COUNT(*) FROM protocol_executions pe WHERE pe.id_protocol_assignment=pa.id_protocol_assignment) AS execution_count,'
        . '       (SELECT pe2.result FROM protocol_executions pe2 '
        . '         WHERE pe2.id_protocol_assignment=pa.id_protocol_assignment '
        . '         ORDER BY pe2.cycle_number DESC LIMIT 1) AS latest_result '
        . 'FROM protocol_assignments pa '
        . 'INNER JOIN protocols p ON p.id_protocol=pa.id_protocol '
        . 'INNER JOIN company c ON c.id_company=pa.id_company '
        . 'LEFT JOIN company_center cc ON cc.id_company_center=pa.id_company_center '
        . 'LEFT JOIN projects pr ON pr.id_project=pa.id_project '
        . 'LEFT JOIN workers w ON w.id_worker=pa.id_worker '
        . 'WHERE pa.responsible_user=:id_users '
        . '   OR EXISTS (SELECT 1 FROM users target_u '
        . '              WHERE target_u.id_worker=pa.id_worker '
        . '                AND target_u.id_users=:target_id AND target_u.state=1) '
        . 'ORDER BY pa.state=\'activa\' DESC,pa.next_due_at ASC,pa.id_protocol_assignment DESC'
    );
    $stmt->execute([
        'id_users' => $idUsers,
        'target_id' => $idUsers,
    ]);
    return $stmt->fetchAll();
}

function protocoloAsignacionCambiarEstado(PDO $pdo, int $idAssignment, string $state): void
{
    protocoloRequireSchema($pdo);
    $stmt = $pdo->prepare(
        'UPDATE protocol_assignments SET state=:state,last_update=NOW() '
        . 'WHERE id_protocol_assignment=:id_assignment'
    );
    $stmt->execute([
        'state' => $state,
        'id_assignment' => $idAssignment,
    ]);
}

function protocoloAsignacionTieneRevisionPendiente(PDO $pdo, int $idAssignment): bool
{
    protocoloRequireSchema($pdo);
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM protocol_executions "
        . "WHERE id_protocol_assignment=:id_assignment "
        . "AND result='pendiente_revision'"
    );
    $stmt->execute(['id_assignment' => $idAssignment]);
    return (int) $stmt->fetchColumn() > 0;
}

function protocoloSiguienteCiclo(PDO $pdo, int $idAssignment): int
{
    protocoloRequireSchema($pdo);
    $stmt = $pdo->prepare(
        'SELECT COALESCE(MAX(cycle_number),0)+1 '
        . 'FROM protocol_executions '
        . 'WHERE id_protocol_assignment=:id_assignment'
    );
    $stmt->execute(['id_assignment' => $idAssignment]);
    return max(1, (int) $stmt->fetchColumn());
}

function protocoloEjecucionCrear(
    PDO $pdo,
    int $idAssignment,
    int $cycleNumber,
    string $createdBy
): int {
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO protocol_executions '
        . '(id_protocol_assignment,cycle_number,started_at,submitted_at,result,created_by,date_create,last_update) '
        . "VALUES (:id_assignment,:cycle_number,NOW(),NOW(),'pendiente_revision',:created_by,NOW(),NOW())"
    );
    $stmt->execute([
        'id_assignment' => $idAssignment,
        'cycle_number' => $cycleNumber,
        'created_by' => $createdBy,
    ]);
    return (int) $pdo->lastInsertId();
}

function protocoloEjecucionVincularSubmission(
    PDO $pdo,
    int $idExecution,
    int $idProtocolForm,
    int $idSubmission,
    string $createdBy
): void {
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO protocol_execution_submissions '
        . '(id_protocol_execution,id_protocol_form,id_submission,created_by,date_create) '
        . 'VALUES (:id_execution,:id_protocol_form,:id_submission,:created_by,NOW())'
    );
    $stmt->execute([
        'id_execution' => $idExecution,
        'id_protocol_form' => $idProtocolForm,
        'id_submission' => $idSubmission,
        'created_by' => $createdBy,
    ]);
}

function protocoloEjecucionesAsignacion(PDO $pdo, int $idAssignment): array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT pe.*,u.name AS reviewer_name,u.lastname AS reviewer_lastname,'
        . '       (SELECT COUNT(*) FROM protocol_execution_submissions pes '
        . '         WHERE pes.id_protocol_execution=pe.id_protocol_execution) AS submission_count '
        . 'FROM protocol_executions pe '
        . 'LEFT JOIN users u ON u.id_users=pe.reviewed_by '
        . 'WHERE pe.id_protocol_assignment=:id_assignment '
        . 'ORDER BY pe.cycle_number DESC'
    );
    $stmt->execute(['id_assignment' => $idAssignment]);
    return $stmt->fetchAll();
}

function protocoloEjecucionObtener(PDO $pdo, int $idExecution): ?array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT pe.*,pa.id_protocol_assignment,pa.id_protocol,pa.id_company,pa.responsible_user,'
        . '       pa.recurrence_unit,pa.recurrence_interval,pa.state AS assignment_state,pa.next_due_at,'
        . '       p.name AS protocol_name,p.code,'
        . '       u.name AS reviewer_name,u.lastname AS reviewer_lastname '
        . 'FROM protocol_executions pe '
        . 'INNER JOIN protocol_assignments pa ON pa.id_protocol_assignment=pe.id_protocol_assignment '
        . 'INNER JOIN protocols p ON p.id_protocol=pa.id_protocol '
        . 'LEFT JOIN users u ON u.id_users=pe.reviewed_by '
        . 'WHERE pe.id_protocol_execution=:id_execution LIMIT 1'
    );
    $stmt->execute(['id_execution' => $idExecution]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $submissions = $pdo->prepare(
        'SELECT pes.id_protocol_execution_submission,pes.id_protocol_form,pes.id_submission,'
        . '       f.name AS form_name '
        . 'FROM protocol_execution_submissions pes '
        . 'INNER JOIN protocol_forms pf ON pf.id_protocol_form=pes.id_protocol_form '
        . 'INNER JOIN dynamic_forms f ON f.id_form=pf.id_form '
        . 'WHERE pes.id_protocol_execution=:id_execution '
        . 'ORDER BY pf.sort_order ASC,pf.id_protocol_form ASC'
    );
    $submissions->execute(['id_execution' => $idExecution]);
    $row['submissions'] = $submissions->fetchAll();

    return $row;
}

function protocoloEjecucionRevisar(
    PDO $pdo,
    int $idExecution,
    string $result,
    ?string $notes,
    string $reviewedBy
): void {
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'UPDATE protocol_executions '
        . 'SET result=:result,review_notes=:review_notes,reviewed_at=NOW(),reviewed_by=:reviewed_by,last_update=NOW() '
        . 'WHERE id_protocol_execution=:id_execution'
    );
    $stmt->execute([
        'result' => $result,
        'review_notes' => $notes,
        'reviewed_by' => $reviewedBy,
        'id_execution' => $idExecution,
    ]);
}

function protocoloAsignacionActualizarProximoVencimiento(
    PDO $pdo,
    int $idAssignment,
    ?string $nextDueAt,
    string $state
): void {
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'UPDATE protocol_assignments '
        . 'SET next_due_at=COALESCE(:next_due_at,next_due_at),state=:state,last_update=NOW() '
        . 'WHERE id_protocol_assignment=:id_assignment'
    );
    $stmt->execute([
        'next_due_at' => $nextDueAt,
        'state' => $state,
        'id_assignment' => $idAssignment,
    ]);
}

function protocoloTrackingListar(PDO $pdo, int $idAssignment): array
{
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT pt.*,u.name AS responsible_name,u.lastname AS responsible_lastname '
        . 'FROM protocol_tracking pt '
        . 'LEFT JOIN users u ON u.id_users=pt.responsible_user '
        . 'WHERE pt.id_protocol_assignment=:id_assignment '
        . 'ORDER BY pt.status=\'completado\' ASC,pt.deadline ASC,pt.id_protocol_tracking DESC'
    );
    $stmt->execute(['id_assignment' => $idAssignment]);
    return $stmt->fetchAll();
}

function protocoloTrackingCrear(
    PDO $pdo,
    int $idAssignment,
    ?int $idExecution,
    string $description,
    ?string $responsibleUser,
    string $commitmentDate,
    string $deadline,
    string $createdBy
): int {
    protocoloRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO protocol_tracking '
        . '(id_protocol_assignment,id_protocol_execution,description,responsible_user,'
        . ' commitment_date,deadline,status,completed_at,created_by,date_create,last_update) '
        . "VALUES (:id_assignment,:id_execution,:description,:responsible_user,"
        . " :commitment_date,:deadline,'pendiente',NULL,:created_by,NOW(),NOW())"
    );
    $stmt->execute([
        'id_assignment' => $idAssignment,
        'id_execution' => $idExecution,
        'description' => $description,
        'responsible_user' => $responsibleUser,
        'commitment_date' => $commitmentDate,
        'deadline' => $deadline,
        'created_by' => $createdBy,
    ]);
    return (int) $pdo->lastInsertId();
}

function protocoloTrackingObtener(PDO $pdo, int $idTracking): ?array
{
    protocoloRequireSchema($pdo);
    $stmt = $pdo->prepare(
        'SELECT * FROM protocol_tracking WHERE id_protocol_tracking=:id_tracking LIMIT 1'
    );
    $stmt->execute(['id_tracking' => $idTracking]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function protocoloTrackingCambiarEstado(PDO $pdo, int $idTracking, string $status): void
{
    protocoloRequireSchema($pdo);

    $completed = $status === 'completado' ? 'NOW()' : 'NULL';
    $sql = 'UPDATE protocol_tracking SET status=:status,completed_at=' . $completed
        . ',last_update=NOW() WHERE id_protocol_tracking=:id_tracking';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'status' => $status,
        'id_tracking' => $idTracking,
    ]);
}
