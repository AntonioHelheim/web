<?php
/**
 * GET /api/dashboard/indicadores.php
 *   ?id_company=4&id_project=7&id_center=8&period=90
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/DashboardRepository.php';
require_once __DIR__ . '/../../lib/personal-activities.php';
require_once __DIR__ . '/../../lib/sct-notifications.php';

requireCapability($pdo, 'dashboard.view');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');


if (!function_exists('dashboardCalendarItems')) {
    function dashboardCalendarItems(PDO $pdo, $idCompany, $idProject = null, $idCenter = null)
    {
        $idCompany = (int) $idCompany;
        if ($idCompany <= 0) return [];

        $from = date('Y-m-01 00:00:00');
        $to = date('Y-m-01 00:00:00', strtotime('+1 month'));
        $items = [];

        $push = function ($dateTime, $title, $type, $meta, $href, $sourceId = null, $semanticState = 'scheduled') use (&$items) {
            if (!$dateTime) return;
            $ts = strtotime((string) $dateTime);
            if ($ts === false) return;
            $items[] = [
                'date' => date('Y-m-d', $ts),
                'datetime' => date('Y-m-d H:i:s', $ts),
                'title' => (string) $title,
                'type' => (string) $type,
                'meta' => (string) $meta,
                'href' => (string) $href,
                'source_id' => $sourceId === null ? null : (string) $sourceId,
                'semantic_state' => in_array((string) $semanticState, ['critical', 'warning', 'scheduled', 'complete'], true) ? (string) $semanticState : 'scheduled',
            ];
        };

        try {
            $stmt = $pdo->prepare("SELECT MIN(uta.id_user_test_assigned) AS id_user_test_assigned, uta.deadline, ct.name, ct.type,
                       COUNT(*) AS assignment_count,
                       SUM(CASE WHEN uta.state = 1 THEN 1 ELSE 0 END) AS pending_count,
                       SUM(CASE WHEN uta.state = 2 THEN 1 ELSE 0 END) AS completed_count,
                       SUM(CASE WHEN uta.state = 3 THEN 1 ELSE 0 END) AS failed_count
                FROM users_test_assigned uta
                INNER JOIN company_test ct ON ct.id_test = uta.id_test
                WHERE uta.id_company = :company
                  AND uta.deadline >= :from_date AND uta.deadline < :to_date
                GROUP BY uta.id_test, uta.deadline, ct.name, ct.type
                ORDER BY uta.deadline ASC");
            $stmt->execute([':company' => $idCompany, ':from_date' => $from, ':to_date' => $to]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $type = (string) ($row['type'] ?? 'otro');
                $href = '../usuarios/gestiones.php';
                if ($type === 'induccion') $href = '../induccion/gestion-induccion.php';
                if ($type === 'auditoria') $href = '../auditorias/gestion-auditorias.php';
                if ($type === 'autoevaluacion') $href = '../autoevaluaciones/gestion-autoevaluaciones.php';
                $pendingCount = (int) ($row['pending_count'] ?? 0);
                $completedCount = (int) ($row['completed_count'] ?? 0);
                $failedCount = (int) ($row['failed_count'] ?? 0);
                $assignmentCount = max(1, (int) ($row['assignment_count'] ?? 1));
                $deadlineTs = !empty($row['deadline']) ? strtotime((string) $row['deadline']) : false;
                $semanticState = 'scheduled';
                if ($pendingCount > 0 && $deadlineTs !== false && $deadlineTs < time()) $semanticState = 'critical';
                elseif ($failedCount > 0) $semanticState = 'warning';
                elseif ($completedCount >= $assignmentCount) $semanticState = 'complete';
                $push($row['deadline'] ?? null, $row['name'] ?? '', $type, '', $href, $row['id_user_test_assigned'] ?? null, $semanticState);
                $lastIndex = count($items) - 1;
                if ($lastIndex >= 0) $items[$lastIndex]['count'] = $assignmentCount;
            }
        } catch (Throwable $e) {
            error_log('dashboardCalendarItems tests: ' . $e->getMessage());
        }

        try {
            $sql = "SELECT pa.id_protocol_assignment, pa.next_due_at, pa.responsible_user, pa.state, p.name
                FROM protocol_assignments pa
                INNER JOIN protocols p ON p.id_protocol = pa.id_protocol
                WHERE pa.id_company = :company
                  AND pa.next_due_at >= :from_date AND pa.next_due_at < :to_date";
            $params = [':company' => $idCompany, ':from_date' => $from, ':to_date' => $to];
            if ($idProject !== null) { $sql .= ' AND pa.id_project = :project'; $params[':project'] = (int) $idProject; }
            if ($idCenter !== null) { $sql .= ' AND pa.id_company_center = :center'; $params[':center'] = (int) $idCenter; }
            $sql .= ' ORDER BY pa.next_due_at ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $protocolState = (string) ($row['state'] ?? 'activa');
                $dueTs = !empty($row['next_due_at']) ? strtotime((string) $row['next_due_at']) : false;
                $semanticState = in_array($protocolState, ['cerrada', 'cancelada'], true) ? 'complete' : ($protocolState === 'suspendida' ? 'warning' : (($dueTs !== false && $dueTs < time()) ? 'critical' : 'scheduled'));
                $push($row['next_due_at'] ?? null, $row['name'] ?? '', 'protocolo', $row['responsible_user'] ?? '', '../protocolos/gestion-protocolos.php', $row['id_protocol_assignment'] ?? null, $semanticState);
            }
        } catch (Throwable $e) {
            error_log('dashboardCalendarItems protocols: ' . $e->getMessage());
        }

        try {
            $stmt = $pdo->prepare("SELECT id_program, name, start_date, end_date, status
                FROM programs
                WHERE id_company = :company
                  AND ((start_date >= :from_date AND start_date < :to_date)
                    OR (end_date >= :from_date AND end_date < :to_date))
                ORDER BY COALESCE(end_date, start_date) ASC");
            $stmt->execute([':company' => $idCompany, ':from_date' => substr($from, 0, 10), ':to_date' => substr($to, 0, 10)]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['start_date']) && $row['start_date'] >= substr($from, 0, 10) && $row['start_date'] < substr($to, 0, 10)) {
                    $programStatus = (string) ($row['status'] ?? 'planificado');
                    $semanticState = $programStatus === 'completado' ? 'complete' : (in_array($programStatus, ['suspendido', 'cancelado'], true) ? 'warning' : ((strtotime((string) $row['start_date'] . ' 23:59:59') < time() && $programStatus === 'planificado') ? 'critical' : 'scheduled'));
                    $push($row['start_date'] . ' 09:00:00', $row['name'] ?? '', 'programa_inicio', $row['status'] ?? '', '../programas/gestion-programas.php', $row['id_program'] ?? null, $semanticState);
                }
                if (!empty($row['end_date']) && $row['end_date'] >= substr($from, 0, 10) && $row['end_date'] < substr($to, 0, 10)) {
                    $programStatus = (string) ($row['status'] ?? 'planificado');
                    $endTs = strtotime((string) $row['end_date'] . ' 23:59:59');
                    $semanticState = $programStatus === 'completado' ? 'complete' : (in_array($programStatus, ['suspendido', 'cancelado'], true) ? 'warning' : (($endTs !== false && $endTs < time()) ? 'critical' : 'scheduled'));
                    $push($row['end_date'] . ' 18:00:00', $row['name'] ?? '', 'programa_fin', $row['status'] ?? '', '../programas/gestion-programas.php', $row['id_program'] ?? null, $semanticState);
                }
            }
        } catch (Throwable $e) {
            error_log('dashboardCalendarItems programs: ' . $e->getMessage());
        }

        try {
            $sql = "SELECT setr.id_security_event_tracking, setr.deadline, setr.tracking_description, setr.person_charge, se.state AS event_state, se.criticality
                FROM security_event_tracking setr
                INNER JOIN security_events se ON se.id_security_events = setr.id_security_events
                WHERE se.id_company = :company
                  AND setr.deadline >= :from_date AND setr.deadline < :to_date";
            $params = [':company' => $idCompany, ':from_date' => $from, ':to_date' => $to];
            if ($idProject !== null) { $sql .= ' AND se.id_project = :project'; $params[':project'] = (int) $idProject; }
            if ($idCenter !== null) { $sql .= ' AND se.id_company_center = :center'; $params[':center'] = (int) $idCenter; }
            $sql .= ' ORDER BY setr.deadline ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventState = (int) ($row['event_state'] ?? 0);
                $criticality = (string) ($row['criticality'] ?? 'media');
                $deadlineTs = !empty($row['deadline']) ? strtotime((string) $row['deadline']) : false;
                $semanticState = $eventState === 3 ? 'complete' : (in_array($criticality, ['alta', 'critica'], true) || ($deadlineTs !== false && $deadlineTs < time()) ? 'critical' : 'scheduled');
                $push($row['deadline'] ?? null, $row['tracking_description'] ?? '', 'evento_seguimiento', $row['person_charge'] ?? '', '../eventos/gestion-eventos.php', $row['id_security_event_tracking'] ?? null, $semanticState);
            }
        } catch (Throwable $e) {
            error_log('dashboardCalendarItems event tracking: ' . $e->getMessage());
        }

        usort($items, function ($a, $b) { return strcmp((string) $a['datetime'], (string) $b['datetime']); });
        return $items;
    }
}


$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = dashboardResolveCompanyId($pdo, $idCompanySolicitado);
$idProject = filter_input(INPUT_GET, 'id_project', FILTER_VALIDATE_INT) ?: null;
$idCenter = filter_input(INPUT_GET, 'id_center', FILTER_VALIDATE_INT) ?: null;
$period = dashboardResolvePeriod((string) ($_GET['period'] ?? '90'));
$accessContext = resolveCurrentUserAccessContext($pdo);
$currentUserId = (string) ($_SESSION['user_email'] ?? '');
$currentRole = $accessContext !== null ? (string) ($accessContext['primary_role'] ?? 'trabajador') : 'trabajador';
$currentRoles = $accessContext !== null && isset($accessContext['roles']) && is_array($accessContext['roles']) ? $accessContext['roles'] : currentUserRoles($pdo);
$currentProfile = $accessContext !== null && isset($accessContext['profile']) && is_array($accessContext['profile']) ? $accessContext['profile'] : [];

try {
    if ($currentRole === 'trabajador') {
        if ($currentUserId === '') {
            responderJSON(false, null, 'No se pudo determinar el usuario actual.', 403);
        }

        $personalItems = sctPersonalActivityItems($pdo, $idCompany, $currentUserId, 'trabajador', false);
        $personalSummary = sctPersonalActivitySummary($personalItems);
        $personalStatuses = ['overdue' => 0, 'in_progress' => 0, 'approved' => 0, 'failed' => 0];
        $personalItemDetails = [];
        foreach ($personalItems as $personalItem) {
            $detailStatus = sctPersonalActivityDetailStatus($personalItem);
            if ($detailStatus === 'complete') $detailStatus = 'approved';
            if (isset($personalStatuses[$detailStatus])) $personalStatuses[$detailStatus]++;
            $personalItemDetails[] = [
                'category' => (string) ($personalItem['category'] ?? ''),
                'source' => (string) ($personalItem['source'] ?? ''),
                'source_id' => (int) ($personalItem['source_id'] ?? 0),
                'name' => (string) ($personalItem['name'] ?? ''),
                'status' => $detailStatus,
                'status_label' => sctPersonalActivityStatusLabel($personalItem),
                // El modelo no guarda avance parcial persistente para todos los motores.
                // Se expone sólo progreso verificable: 0 pendiente / 100 completada.
                'progress' => !empty($personalItem['complete']) ? 100 : 0,
                'deadline' => (string) ($personalItem['deadline'] ?? ''),
                'assigned_at' => (string) ($personalItem['assigned_at'] ?? ''),
                'completed_at' => (string) ($personalItem['completed_at'] ?? ''),
                'attempts_used' => (int) ($personalItem['attempts_used'] ?? 0),
                'attempts_allowed' => isset($personalItem['attempts_allowed']) ? (int) $personalItem['attempts_allowed'] : null,
            ];
        }
        $datos = [
            'scope' => [
                'id_company' => $idCompany,
                'period' => $period['key'],
                'desde' => $period['desde'],
                'hasta' => $period['hasta'],
                'personal' => true,
                'operation_filters_note' => 'Vista personal: los contadores muestran el estado actual de las actividades del usuario. El período sólo modifica la evolución histórica.',
            ],
            'personal' => [
                'activities' => $personalSummary,
                'categories' => $personalSummary['by_category'],
                'statuses' => $personalStatuses,
                'items' => $personalItemDetails,
            ],
            'role' => 'trabajador',
            'tendencia' => sctPersonalActivityTimeline($pdo, $idCompany, $currentUserId, $period['desde'], $period['hasta']),
        ];

        responderJSON(true, $datos);
    }

    if ($idProject !== null && !dashboardProyectoPerteneceEmpresa($pdo, $idCompany, $idProject)) {
        responderJSON(false, null, 'El proyecto seleccionado no pertenece a la empresa.', 400);
    }
    if ($idCenter !== null && !dashboardCentroPerteneceEmpresa($pdo, $idCompany, $idCenter)) {
        responderJSON(false, null, 'El centro seleccionado no pertenece a la empresa.', 400);
    }

    $induccion = dashboardIndicadoresEvaluacion($pdo, $idCompany, 'induccion', $period['desde'], $period['hasta']);
    $auditorias = dashboardIndicadoresAuditorias($pdo, $idCompany, $period['desde'], $period['hasta']);
    $autoevaluaciones = dashboardIndicadoresEvaluacion($pdo, $idCompany, 'autoevaluacion', $period['desde'], $period['hasta']);
    $eventos = dashboardIndicadoresEventos($pdo, $idCompany, $idProject, $idCenter, $period['desde'], $period['hasta']);
    $formularios = dashboardIndicadoresFormularios($pdo, $idCompany, $period['desde'], $period['hasta']);
    $protocolos = dashboardIndicadoresProtocolos($pdo, $idCompany, $idProject, $idCenter, $period['desde'], $period['hasta']);

    $datos = [
        'scope' => [
            'id_company' => $idCompany,
            'id_project' => $idProject,
            'id_center' => $idCenter,
            'period' => $period['key'],
            'desde' => $period['desde'],
            'hasta' => $period['hasta'],
            'operation_filters_note' => 'Los filtros de proyecto y centro se aplican a eventos y protocolos. Evaluaciones y formularios no almacenan proyecto/centro en su modelo actual.',
        ],
        'totales' => [
            'usuarios' => dashboardTotalUsuarios($pdo, $idCompany),
            'proyectos' => dashboardTotalProyectos($pdo, $idCompany),
            'centros' => dashboardTotalCentros($pdo, $idCompany),
            'trabajadores' => dashboardTotalTrabajadores($pdo, $idCompany),
        ],
        'eventos' => $eventos,
        'induccion' => $induccion,
        'auditorias' => $auditorias,
        'autoevaluaciones' => $autoevaluaciones,
        'formularios' => $formularios,
        'protocolos' => $protocolos,
        'programas' => dashboardIndicadoresProgramas($pdo, $idCompany),
        'personal' => $currentUserId !== ''
            ? dashboardIndicadoresPersonales($pdo, $idCompany, $currentUserId, $period['desde'], $period['hasta'])
            : null,
        'role' => $currentRole,
        'tendencia' => dashboardTendenciaMensual($pdo, $idCompany, $idProject, $idCenter, $period['desde'], $period['hasta']),
        'actividad_reciente' => dashboardActividadReciente($pdo, $idCompany, $idProject, $idCenter, $period['desde'], $period['hasta'], 10),
        'calendario' => dashboardCalendarItems($pdo, $idCompany, $idProject, $idCenter),
        'notificaciones' => $currentUserId !== '' ? sctBuildPendingNotifications($pdo, $currentUserId, $currentRoles, $currentProfile, '../../') : [],
    ];

    if (dashboardIsGlobalAdmin($pdo)) {
        $datos['totales']['empresas'] = dashboardTotalEmpresas($pdo);
    }

    responderJSON(true, $datos);
} catch (PDOException $e) {
    error_log('api/dashboard/indicadores.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los indicadores del dashboard.', 500);
} catch (Throwable $e) {
    error_log('api/dashboard/indicadores.php runtime: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron procesar los indicadores del dashboard.', 500);
}
