<?php
/**
 * Safety Control Tower - Dashboard avanzado (Etapa 2)
 *
 * Capa de lectura agregada sobre los módulos ya existentes. No mantiene
 * una tabla de dashboard propia: calcula indicadores desde la fuente de
 * verdad de cada módulo para evitar duplicar estado.
 */

function dashboardTotalEmpresas(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM company WHERE state = 1');
    return (int) $stmt->fetchColumn();
}

function dashboardTotalProyectos(PDO $pdo, int $idCompany): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE id_company = :id AND state = 1');
    $stmt->execute(['id' => $idCompany]);
    return (int) $stmt->fetchColumn();
}

function dashboardTotalCentros(PDO $pdo, int $idCompany): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM company_center WHERE id_company = :id AND state = 1');
    $stmt->execute(['id' => $idCompany]);
    return (int) $stmt->fetchColumn();
}

function dashboardTotalTrabajadores(PDO $pdo, int $idCompany): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM workers WHERE id_company = :id AND state = 1');
    $stmt->execute(['id' => $idCompany]);
    return (int) $stmt->fetchColumn();
}

function dashboardProyectoPerteneceEmpresa(PDO $pdo, int $idCompany, int $idProject): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE id_project = :id_project AND id_company = :id_company');
    $stmt->execute(['id_project' => $idProject, 'id_company' => $idCompany]);
    return (int) $stmt->fetchColumn() > 0;
}

function dashboardCentroPerteneceEmpresa(PDO $pdo, int $idCompany, int $idCenter): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM company_center WHERE id_company_center = :id_center AND id_company = :id_company');
    $stmt->execute(['id_center' => $idCenter, 'id_company' => $idCompany]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Agrega límites temporales a una consulta. $hasta se usa como límite
 * inclusivo porque siempre corresponde al momento de generación del panel.
 */
function dashboardSqlPeriodo(string $columna, ?string $desde, ?string $hasta, array &$params, string $prefix): string
{
    $sql = '';
    if ($desde !== null) {
        $sql .= ' AND ' . $columna . ' >= :' . $prefix . '_desde';
        $params[$prefix . '_desde'] = $desde;
    }
    if ($hasta !== null) {
        $sql .= ' AND ' . $columna . ' <= :' . $prefix . '_hasta';
        $params[$prefix . '_hasta'] = $hasta;
    }
    return $sql;
}

function dashboardIndicadoresEvaluacion(PDO $pdo, int $idCompany, string $tipo, ?string $desde, ?string $hasta): array
{
    $params = ['id_company' => $idCompany, 'tipo' => $tipo];
    $where = ' WHERE a.id_company = :id_company AND t.type = :tipo';
    $where .= dashboardSqlPeriodo('a.assignamente_date', $desde, $hasta, $params, 'eval');

    $stmt = $pdo->prepare(
        'SELECT a.state, COUNT(*) AS cantidad
         FROM users_test_assigned a
         INNER JOIN company_test t ON t.id_test = a.id_test' . $where . '
         GROUP BY a.state'
    );
    $stmt->execute($params);

    $out = [
        'pendientes' => 0,
        'aprobados' => 0,
        'reprobados' => 0,
        'total' => 0,
        'finalizadas' => 0,
        'vencidas_pendientes' => 0,
        'tasa_aprobacion' => null,
    ];

    foreach ($stmt->fetchAll() as $row) {
        $state = (int) $row['state'];
        $cantidad = (int) $row['cantidad'];
        if ($state === 1) $out['pendientes'] = $cantidad;
        if ($state === 2) $out['aprobados'] = $cantidad;
        if ($state === 3) $out['reprobados'] = $cantidad;
    }

    $out['total'] = $out['pendientes'] + $out['aprobados'] + $out['reprobados'];
    $out['finalizadas'] = $out['aprobados'] + $out['reprobados'];
    $out['tasa_aprobacion'] = $out['finalizadas'] > 0
        ? round(($out['aprobados'] / $out['finalizadas']) * 100, 1)
        : null;

    $paramsVencidas = ['id_company' => $idCompany, 'tipo' => $tipo];
    $whereVencidas = ' WHERE a.id_company = :id_company AND t.type = :tipo AND a.state = 1 AND a.deadline < NOW()';
    $whereVencidas .= dashboardSqlPeriodo('a.assignamente_date', $desde, $hasta, $paramsVencidas, 'eval_due');
    $stmtDue = $pdo->prepare(
        'SELECT COUNT(*)
         FROM users_test_assigned a
         INNER JOIN company_test t ON t.id_test = a.id_test' . $whereVencidas
    );
    $stmtDue->execute($paramsVencidas);
    $out['vencidas_pendientes'] = (int) $stmtDue->fetchColumn();

    return $out;
}

function dashboardIndicadoresAuditorias(PDO $pdo, int $idCompany, ?string $desde, ?string $hasta): array
{
    $evaluacion = dashboardIndicadoresEvaluacion($pdo, $idCompany, 'auditoria', $desde, $hasta);

    $params = ['id_company' => $idCompany];
    $where = ' WHERE id_company = :id_company';
    $where .= dashboardSqlPeriodo('date_create', $desde, $hasta, $params, 'aud');

    $stmt = $pdo->prepare('SELECT status, COUNT(*) AS cantidad FROM audits' . $where . ' GROUP BY status');
    $stmt->execute($params);
    $statuses = ['pendiente' => 0, 'en_curso' => 0, 'completada' => 0, 'cancelada' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $status = (string) $row['status'];
        if (array_key_exists($status, $statuses)) {
            $statuses[$status] = (int) $row['cantidad'];
        }
    }
    $evaluacion['por_estado_operativo'] = $statuses;
    return $evaluacion;
}

function dashboardIndicadoresEventos(PDO $pdo, int $idCompany, ?int $idProject, ?int $idCenter, ?string $desde, ?string $hasta): array
{
    $params = ['id_company' => $idCompany];
    $where = ' WHERE e.id_company = :id_company AND e.module = \'seguridad\'';
    if ($idProject !== null) {
        $where .= ' AND e.id_project = :id_project';
        $params['id_project'] = $idProject;
    }
    if ($idCenter !== null) {
        $where .= ' AND e.id_company_center = :id_center';
        $params['id_center'] = $idCenter;
    }
    $where .= dashboardSqlPeriodo('e.event_date', $desde, $hasta, $params, 'evt');

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM security_events e' . $where);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT e.state, COUNT(*) AS cantidad FROM security_events e' . $where . ' GROUP BY e.state');
    $stmt->execute($params);
    $porEstado = ['abierto' => 0, 'en_proceso' => 0, 'cerrado' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $state = (int) $row['state'];
        if ($state === 1) $porEstado['abierto'] = (int) $row['cantidad'];
        if ($state === 2) $porEstado['en_proceso'] = (int) $row['cantidad'];
        if ($state === 3) $porEstado['cerrado'] = (int) $row['cantidad'];
    }

    $stmt = $pdo->prepare('SELECT e.criticality, COUNT(*) AS cantidad FROM security_events e' . $where . ' GROUP BY e.criticality');
    $stmt->execute($params);
    $porCriticidad = ['baja' => 0, 'media' => 0, 'alta' => 0, 'critica' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $key = (string) $row['criticality'];
        if (array_key_exists($key, $porCriticidad)) {
            $porCriticidad[$key] = (int) $row['cantidad'];
        }
    }

    $stmt = $pdo->prepare(
        'SELECT et.name, COUNT(*) AS cantidad
         FROM security_events e
         INNER JOIN event_types et ON et.id_event_type = e.id_event' . $where . '
         GROUP BY et.id_event_type, et.name
         ORDER BY cantidad DESC, et.name ASC
         LIMIT 6'
    );
    $stmt->execute($params);
    $tipos = array_map(static function (array $row): array {
        return ['label' => (string) $row['name'], 'cantidad' => (int) $row['cantidad']];
    }, $stmt->fetchAll());

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM security_events e' . $where .
        ' AND e.state <> 3 AND e.criticality IN (\'alta\',\'critica\')'
    );
    $stmt->execute($params);
    $abiertosCriticos = (int) $stmt->fetchColumn();

    return [
        'total' => $total,
        'abiertos_criticos' => $abiertosCriticos,
        'por_estado' => $porEstado,
        'por_criticidad' => $porCriticidad,
        'tipos_principales' => $tipos,
    ];
}

function dashboardIndicadoresFormularios(PDO $pdo, int $idCompany, ?string $desde, ?string $hasta): array
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM dynamic_forms
         WHERE state = 1 AND (id_company = :id_company OR id_company IS NULL)'
    );
    $stmt->execute(['id_company' => $idCompany]);
    $formsActivos = (int) $stmt->fetchColumn();

    $params = ['id_company' => $idCompany];
    $where = ' WHERE s.id_company = :id_company AND s.status = \'submitted\'';
    $where .= dashboardSqlPeriodo('s.submitted_at', $desde, $hasta, $params, 'frm');

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM dynamic_form_submissions s' . $where);
    $stmt->execute($params);
    $envios = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(DISTINCT s.id_users) FROM dynamic_form_submissions s' . $where);
    $stmt->execute($params);
    $usuarios = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT f.name, COUNT(*) AS cantidad
         FROM dynamic_form_submissions s
         INNER JOIN dynamic_forms f ON f.id_form = s.id_form' . $where . '
         GROUP BY f.id_form, f.name
         ORDER BY cantidad DESC, f.name ASC
         LIMIT 6'
    );
    $stmt->execute($params);
    $top = array_map(static function (array $row): array {
        return ['label' => (string) $row['name'], 'cantidad' => (int) $row['cantidad']];
    }, $stmt->fetchAll());

    return [
        'formularios_activos' => $formsActivos,
        'envios' => $envios,
        'usuarios_participantes' => $usuarios,
        'formularios_mas_usados' => $top,
    ];
}

function dashboardIndicadoresProtocolos(PDO $pdo, int $idCompany, ?int $idProject, ?int $idCenter, ?string $desde, ?string $hasta): array
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM protocols
         WHERE state = 1 AND (id_company = :id_company OR id_company IS NULL)'
    );
    $stmt->execute(['id_company' => $idCompany]);
    $protocolosVisibles = (int) $stmt->fetchColumn();

    $params = ['id_company' => $idCompany];
    $scope = ' WHERE pa.id_company = :id_company';
    if ($idProject !== null) {
        $scope .= ' AND pa.id_project = :id_project';
        $params['id_project'] = $idProject;
    }
    if ($idCenter !== null) {
        $scope .= ' AND pa.id_company_center = :id_center';
        $params['id_center'] = $idCenter;
    }

    $stmt = $pdo->prepare('SELECT pa.state, COUNT(*) AS cantidad FROM protocol_assignments pa' . $scope . ' GROUP BY pa.state');
    $stmt->execute($params);
    $asignaciones = ['activa' => 0, 'suspendida' => 0, 'cerrada' => 0, 'cancelada' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $state = (string) $row['state'];
        if (array_key_exists($state, $asignaciones)) {
            $asignaciones[$state] = (int) $row['cantidad'];
        }
    }
    $asignaciones['total'] = array_sum($asignaciones);

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM protocol_assignments pa' . $scope . ' AND pa.state = \'activa\' AND pa.next_due_at < NOW()');
    $stmt->execute($params);
    $vencidas = (int) $stmt->fetchColumn();

    $paramsExec = $params;
    $whereExec = $scope;
    $whereExec .= dashboardSqlPeriodo('pe.submitted_at', $desde, $hasta, $paramsExec, 'prot_exec');
    $stmt = $pdo->prepare(
        'SELECT pe.result, COUNT(*) AS cantidad
         FROM protocol_executions pe
         INNER JOIN protocol_assignments pa ON pa.id_protocol_assignment = pe.id_protocol_assignment' . $whereExec . '
         GROUP BY pe.result'
    );
    $stmt->execute($paramsExec);
    $resultados = [
        'pendiente_revision' => 0,
        'conforme' => 0,
        'observado' => 0,
        'no_conforme' => 0,
        'no_aplica' => 0,
    ];
    foreach ($stmt->fetchAll() as $row) {
        $result = (string) $row['result'];
        if (array_key_exists($result, $resultados)) {
            $resultados[$result] = (int) $row['cantidad'];
        }
    }
    $resultados['total'] = array_sum($resultados);

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM protocol_executions pe
         INNER JOIN protocol_assignments pa ON pa.id_protocol_assignment = pe.id_protocol_assignment' . $scope .
        ' AND pe.result = \'pendiente_revision\''
    );
    $stmt->execute($params);
    $pendientesRevision = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM protocol_tracking pt
         INNER JOIN protocol_assignments pa ON pa.id_protocol_assignment = pt.id_protocol_assignment' . $scope .
        ' AND pt.status IN (\'pendiente\',\'en_curso\') AND pt.deadline < NOW()'
    );
    $stmt->execute($params);
    $trackingVencido = (int) $stmt->fetchColumn();

    return [
        'protocolos_visibles' => $protocolosVisibles,
        'asignaciones' => $asignaciones,
        'asignaciones_vencidas' => $vencidas,
        'ejecuciones' => $resultados,
        'pendientes_revision' => $pendientesRevision,
        'seguimientos_vencidos' => $trackingVencido,
    ];
}

/**
 * Devuelve una serie mensual combinada. El filtro temporal controla la
 * ventana, pero se limita visualmente a un máximo de 12 meses para que el
 * gráfico siga siendo legible. Formularios/evaluaciones no tienen columnas
 * de proyecto/centro; esos filtros solo afectan eventos y protocolos.
 */
function dashboardTendenciaMensual(PDO $pdo, int $idCompany, ?int $idProject, ?int $idCenter, ?string $desde, ?string $hasta): array
{
    $hastaDt = $hasta !== null ? new DateTimeImmutable($hasta) : new DateTimeImmutable('now');
    $desdeDt = $desde !== null ? new DateTimeImmutable($desde) : $hastaDt->modify('-11 months')->modify('first day of this month')->setTime(0, 0);
    $minDesde = $hastaDt->modify('-11 months')->modify('first day of this month')->setTime(0, 0);
    if ($desdeDt < $minDesde) $desdeDt = $minDesde;
    $desdeMes = $desdeDt->modify('first day of this month')->setTime(0, 0);
    $hastaMes = $hastaDt->modify('first day of this month')->setTime(0, 0);

    $buckets = [];
    for ($cursor = $desdeMes; $cursor <= $hastaMes; $cursor = $cursor->modify('+1 month')) {
        $key = $cursor->format('Y-m');
        $buckets[$key] = [
            'periodo' => $key,
            'eventos' => 0,
            'formularios' => 0,
            'protocolos' => 0,
            'evaluaciones' => 0,
        ];
    }

    $paramsEvt = ['id_company' => $idCompany, 'from' => $desdeDt->format('Y-m-d H:i:s'), 'to' => $hastaDt->format('Y-m-d H:i:s')];
    $whereEvt = ' WHERE id_company = :id_company AND module = \'seguridad\' AND event_date BETWEEN :from AND :to';
    if ($idProject !== null) {
        $whereEvt .= ' AND id_project = :id_project';
        $paramsEvt['id_project'] = $idProject;
    }
    if ($idCenter !== null) {
        $whereEvt .= ' AND id_company_center = :id_center';
        $paramsEvt['id_center'] = $idCenter;
    }
    $stmt = $pdo->prepare('SELECT DATE_FORMAT(event_date, \'%Y-%m\') AS periodo, COUNT(*) AS cantidad FROM security_events' . $whereEvt . ' GROUP BY periodo');
    $stmt->execute($paramsEvt);
    foreach ($stmt->fetchAll() as $row) {
        if (isset($buckets[$row['periodo']])) $buckets[$row['periodo']]['eventos'] = (int) $row['cantidad'];
    }

    $stmt = $pdo->prepare(
        'SELECT DATE_FORMAT(submitted_at, \'%Y-%m\') AS periodo, COUNT(*) AS cantidad
         FROM dynamic_form_submissions
         WHERE id_company = :id_company AND status = \'submitted\' AND submitted_at BETWEEN :from AND :to
         GROUP BY periodo'
    );
    $stmt->execute(['id_company' => $idCompany, 'from' => $desdeDt->format('Y-m-d H:i:s'), 'to' => $hastaDt->format('Y-m-d H:i:s')]);
    foreach ($stmt->fetchAll() as $row) {
        if (isset($buckets[$row['periodo']])) $buckets[$row['periodo']]['formularios'] = (int) $row['cantidad'];
    }

    $paramsProt = ['id_company' => $idCompany, 'from' => $desdeDt->format('Y-m-d H:i:s'), 'to' => $hastaDt->format('Y-m-d H:i:s')];
    $whereProt = ' WHERE pa.id_company = :id_company AND pe.submitted_at BETWEEN :from AND :to';
    if ($idProject !== null) {
        $whereProt .= ' AND pa.id_project = :id_project';
        $paramsProt['id_project'] = $idProject;
    }
    if ($idCenter !== null) {
        $whereProt .= ' AND pa.id_company_center = :id_center';
        $paramsProt['id_center'] = $idCenter;
    }
    $stmt = $pdo->prepare(
        'SELECT DATE_FORMAT(pe.submitted_at, \'%Y-%m\') AS periodo, COUNT(*) AS cantidad
         FROM protocol_executions pe
         INNER JOIN protocol_assignments pa ON pa.id_protocol_assignment = pe.id_protocol_assignment' . $whereProt . '
         GROUP BY periodo'
    );
    $stmt->execute($paramsProt);
    foreach ($stmt->fetchAll() as $row) {
        if (isset($buckets[$row['periodo']])) $buckets[$row['periodo']]['protocolos'] = (int) $row['cantidad'];
    }

    $stmt = $pdo->prepare(
        'SELECT DATE_FORMAT(a.last_update, \'%Y-%m\') AS periodo, COUNT(*) AS cantidad
         FROM users_test_assigned a
         INNER JOIN company_test t ON t.id_test = a.id_test
         WHERE a.id_company = :id_company
           AND t.type IN (\'induccion\',\'auditoria\',\'autoevaluacion\')
           AND a.state IN (2,3)
           AND a.last_update BETWEEN :from AND :to
         GROUP BY periodo'
    );
    $stmt->execute(['id_company' => $idCompany, 'from' => $desdeDt->format('Y-m-d H:i:s'), 'to' => $hastaDt->format('Y-m-d H:i:s')]);
    foreach ($stmt->fetchAll() as $row) {
        if (isset($buckets[$row['periodo']])) $buckets[$row['periodo']]['evaluaciones'] = (int) $row['cantidad'];
    }

    return array_values($buckets);
}

function dashboardActividadReciente(PDO $pdo, int $idCompany, ?int $idProject, ?int $idCenter, ?string $desde, ?string $hasta, int $limit = 10): array
{
    $rows = [];

    $paramsEvt = ['id_company' => $idCompany];
    $whereEvt = ' WHERE e.id_company = :id_company AND e.module = \'seguridad\'';
    if ($idProject !== null) {
        $whereEvt .= ' AND e.id_project = :id_project';
        $paramsEvt['id_project'] = $idProject;
    }
    if ($idCenter !== null) {
        $whereEvt .= ' AND e.id_company_center = :id_center';
        $paramsEvt['id_center'] = $idCenter;
    }
    $whereEvt .= dashboardSqlPeriodo('e.event_date', $desde, $hasta, $paramsEvt, 'recent_evt');
    $stmt = $pdo->prepare(
        'SELECT e.event_date AS fecha, \'evento\' AS tipo, et.name AS titulo,
                CONCAT(e.criticality, \' · \' , CASE e.state WHEN 1 THEN \'abierto\' WHEN 2 THEN \'en_proceso\' ELSE \'cerrado\' END) AS detalle
         FROM security_events e
         INNER JOIN event_types et ON et.id_event_type = e.id_event' . $whereEvt . '
         ORDER BY e.event_date DESC LIMIT 12'
    );
    $stmt->execute($paramsEvt);
    $rows = array_merge($rows, $stmt->fetchAll());

    $paramsForms = ['id_company' => $idCompany];
    $whereForms = ' WHERE s.id_company = :id_company AND s.status = \'submitted\'';
    $whereForms .= dashboardSqlPeriodo('s.submitted_at', $desde, $hasta, $paramsForms, 'recent_form');
    $stmt = $pdo->prepare(
        'SELECT s.submitted_at AS fecha, \'formulario\' AS tipo, f.name AS titulo, s.id_users AS detalle
         FROM dynamic_form_submissions s
         INNER JOIN dynamic_forms f ON f.id_form = s.id_form' . $whereForms . '
         ORDER BY s.submitted_at DESC LIMIT 12'
    );
    $stmt->execute($paramsForms);
    $rows = array_merge($rows, $stmt->fetchAll());

    $paramsProt = ['id_company' => $idCompany];
    $whereProt = ' WHERE pa.id_company = :id_company';
    if ($idProject !== null) {
        $whereProt .= ' AND pa.id_project = :id_project';
        $paramsProt['id_project'] = $idProject;
    }
    if ($idCenter !== null) {
        $whereProt .= ' AND pa.id_company_center = :id_center';
        $paramsProt['id_center'] = $idCenter;
    }
    $whereProt .= dashboardSqlPeriodo('pe.submitted_at', $desde, $hasta, $paramsProt, 'recent_prot');
    $stmt = $pdo->prepare(
        'SELECT pe.submitted_at AS fecha, \'protocolo\' AS tipo, p.name AS titulo, pe.result AS detalle
         FROM protocol_executions pe
         INNER JOIN protocol_assignments pa ON pa.id_protocol_assignment = pe.id_protocol_assignment
         INNER JOIN protocols p ON p.id_protocol = pa.id_protocol' . $whereProt . '
         ORDER BY pe.submitted_at DESC LIMIT 12'
    );
    $stmt->execute($paramsProt);
    $rows = array_merge($rows, $stmt->fetchAll());

    $paramsAud = ['id_company' => $idCompany];
    $whereAud = ' WHERE au.id_company = :id_company AND au.status = \'completada\'';
    $whereAud .= dashboardSqlPeriodo('COALESCE(au.completed_at, au.last_update)', $desde, $hasta, $paramsAud, 'recent_aud');
    $stmt = $pdo->prepare(
        'SELECT COALESCE(au.completed_at, au.last_update) AS fecha, \'auditoria\' AS tipo,
                COALESCE(t.name, au.name_auditor) AS titulo, au.score AS detalle
         FROM audits au
         LEFT JOIN company_test t ON t.id_test = au.id_test' . $whereAud . '
         ORDER BY fecha DESC LIMIT 12'
    );
    $stmt->execute($paramsAud);
    $rows = array_merge($rows, $stmt->fetchAll());

    usort($rows, static function (array $a, array $b): int {
        return strcmp((string) $b['fecha'], (string) $a['fecha']);
    });

    return array_slice(array_map(static function (array $row): array {
        return [
            'fecha' => (string) $row['fecha'],
            'tipo' => (string) $row['tipo'],
            'titulo' => (string) $row['titulo'],
            'detalle' => (string) ($row['detalle'] ?? ''),
        ];
    }, $rows), 0, max(1, min($limit, 25)));
}
