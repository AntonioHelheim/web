<?php
/**
 * SAFETY CONTROL TOWER — actividades personales compartidas.
 *
 * Una sola fuente de lectura para Mi espacio, Bienvenida, Panel y navbar.
 * No crea asignaciones ni modifica estados: sólo refleja datos reales ya
 * persistidos en users_test_assigned, protocol_assignments y formularios.
 */

if (!function_exists('sctPersonalActivityCapabilities')) {
    function sctPersonalActivityCapabilities(PDO $pdo, string $roleKey): array
    {
        $can = [
            'induction' => function_exists('currentUserHasCapability') && currentUserHasCapability($pdo, 'induction.execute'),
            'audits' => function_exists('currentUserHasCapability') && currentUserHasCapability($pdo, 'audits.execute'),
            'self' => function_exists('currentUserHasCapability') && currentUserHasCapability($pdo, 'self_assessments.execute'),
            'forms' => function_exists('currentUserHasCapability') && currentUserHasCapability($pdo, 'dynamic_forms.submit'),
            'protocols' => function_exists('currentUserHasCapability') && currentUserHasCapability($pdo, 'protocols.execute'),
        ];

        // Gerencia/Cliente mantiene el criterio definido: no convertir
        // supervisión de inducciones en un curso personal.
        if ($roleKey === 'cliente') {
            $can['induction'] = false;
        }

        return $can;
    }
}

if (!function_exists('sctPersonalActivityWorkerId')) {
    function sctPersonalActivityWorkerId(PDO $pdo, int $companyId, string $userId): int
    {
        if ($companyId <= 0 || $userId === '') return 0;
        try {
            $stmt = $pdo->prepare('SELECT id_worker FROM users WHERE id_users = :id_user AND id_company = :id_company LIMIT 1');
            $stmt->execute([':id_user' => $userId, ':id_company' => $companyId]);
            $value = $stmt->fetchColumn();
            return ($value === false || $value === null) ? 0 : (int) $value;
        } catch (Throwable $e) {
            error_log('personal-activities worker id: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('sctPersonalActivityHubHref')) {
    /**
     * Enlace canónico al HUB "Mi espacio" para una actividad personal.
     * Mantiene un único punto de entrada UX sin duplicar motores de ejecución.
     */
    function sctPersonalActivityHubHref(array $item, string $hubPath = 'api/usuarios/mis-actividades.php', string $from = ''): string
    {
        $source = preg_replace('/[^a-z_]/', '', (string) ($item['source'] ?? ''));
        $sourceId = (int) ($item['source_id'] ?? 0);
        if ($source === '' || $sourceId <= 0) return $hubPath;

        $params = [
            'activity_source' => $source,
            'activity_id' => $sourceId,
        ];
        if ($from !== '') $params['from'] = preg_replace('/[^a-z_\-]/', '', $from);
        return $hubPath . (strpos($hubPath, '?') === false ? '?' : '&') . http_build_query($params);
    }
}

if (!function_exists('sctPersonalActivityModuleHref')) {
    /**
     * URL del motor existente que ejecuta la actividad. Se utiliza únicamente
     * dentro del contenedor integrado de Mi espacio para no duplicar lógica.
     */
    function sctPersonalActivityModuleHref(array $item, string $basePath = '../'): string
    {
        $source = (string) ($item['source'] ?? '');
        $category = (string) ($item['category'] ?? '');
        $sourceId = (int) ($item['source_id'] ?? 0);
        if ($sourceId <= 0) return '';

        $basePath = rtrim($basePath, '/') . '/';
        if ($source === 'test' && $category === 'induction') {
            return $basePath . 'induccion/mis-induccion.php?start=' . $sourceId . '&embedded=1';
        }
        if ($source === 'test' && $category === 'audits') {
            return $basePath . 'auditorias/mis-auditorias.php?start=' . $sourceId . '&embedded=1';
        }
        if ($source === 'test' && $category === 'self') {
            return $basePath . 'autoevaluaciones/mis-autoevaluaciones.php?start=' . $sourceId . '&embedded=1';
        }
        if ($source === 'form') {
            return $basePath . 'formularios/mis-formularios.php?start=' . $sourceId . '&embedded=1';
        }
        if ($source === 'protocol') {
            return $basePath . 'protocolos/ejecutar-protocolo.php?id_assignment=' . $sourceId . '&embedded=1';
        }
        return '';
    }
}

if (!function_exists('sctPersonalActivityStatusLabel')) {
    /** Etiqueta visible única para Bienvenida, Mi espacio y notificaciones. */
    function sctPersonalActivityStatusLabel(array $item): string
    {
        $translate = static function (string $key, string $fallback): string {
            return function_exists('t') ? (string) t($key) : $fallback;
        };

        $source = (string) ($item['source'] ?? '');
        $category = (string) ($item['category'] ?? '');
        $state = $item['state'] ?? null;
        $overdue = !empty($item['overdue']);
        $complete = !empty($item['complete']);
        $scheduled = !empty($item['scheduled']);
        $attemptsUsed = (int) ($item['attempts_used'] ?? 0);

        if (!$complete && $scheduled) {
            return $translate('activities_status_scheduled', 'Programada');
        }

        if ($source === 'test') {
            $stateInt = (int) $state;
            if ($stateInt === 2) {
                if ($category === 'induction') return $translate('worker_status_approved', 'Completada aprobada');
                if ($category === 'audits') return $translate('my_audits_compliant', 'Conforme');
                if ($category === 'self') return $translate('my_self_approved', 'Aprobada');
                return $translate('activities_status_complete', 'Completada');
            }
            if ($stateInt === 3) {
                if ($category === 'induction') return $translate('worker_status_failed', 'Completada reprobada');
                if ($category === 'audits') return $translate('my_audits_non_compliant', 'No conforme');
                if ($category === 'self') return $translate('my_self_failed', 'Reprobada');
                return $translate('activities_status_complete', 'Completada');
            }
            if ($overdue) return $translate('worker_status_overdue', 'Vencida');
            return $attemptsUsed > 0
                ? $translate('activities_status_progress', 'En progreso')
                : $translate('worker_status_in_progress', 'En curso');
        }

        if ($source === 'form') {
            return $complete
                ? $translate('activities_status_complete', 'Completada')
                : $translate('activities_status_available', 'Disponible');
        }

        if ($complete) return $translate('activities_status_complete', 'Completada');
        if ($overdue) return $translate('activities_status_overdue', 'Vencida');
        return $translate('activities_status_pending', 'Pendiente');
    }
}

if (!function_exists('sctPersonalActivityDetailStatus')) {
    /** Estado normalizado para que Bienvenida y Mi espacio filtren igual. */
    function sctPersonalActivityDetailStatus(array $item): string
    {
        if (!empty($item['overdue'])) return 'overdue';
        if (($item['source'] ?? '') === 'test') {
            $state = (int) ($item['state'] ?? 1);
            if ($state === 2) return 'approved';
            if ($state === 3) return 'failed';
            return 'in_progress';
        }
        if (!empty($item['complete'])) return 'complete';
        return 'in_progress';
    }
}

if (!function_exists('sctPersonalActivityItems')) {
    function sctPersonalActivityItems(PDO $pdo, int $companyId, string $userId, string $roleKey = 'trabajador', bool $isGlobalAdmin = false): array
    {
        $userId = trim($userId);
        if ($userId === '' || (!$isGlobalAdmin && $companyId <= 0)) return [];

        $can = sctPersonalActivityCapabilities($pdo, $roleKey);
        $workerId = sctPersonalActivityWorkerId($pdo, $companyId, $userId);
        $items = [];

        // Evaluaciones personales con asignación explícita.
        try {
            $scopeSql = $isGlobalAdmin ? '' : ' AND uta.id_company = :company';
            $params = [':user' => $userId];
            if (!$isGlobalAdmin) $params[':company'] = $companyId;

            $stmt = $pdo->prepare(
                "SELECT uta.id_user_test_assigned, uta.id_test, uta.id_company, uta.deadline, uta.assignamente_date, uta.last_update, uta.state,
                        ct.name, ct.description, ct.type, ct.attempts_allowed,
                        (SELECT COUNT(DISTINCT ans.id_test_try)
                           FROM users_test_answers ans
                          WHERE ans.id_user_test_assigned = uta.id_user_test_assigned
                             OR (ans.id_user_test_assigned IS NULL
                                 AND ans.id_users = uta.id_users
                                 AND ans.id_test = uta.id_test
                                 AND ans.date_create >= uta.assignamente_date
                                 AND (NOT EXISTS (
                                        SELECT 1 FROM users_test_assigned uta2
                                         WHERE uta2.id_users = uta.id_users
                                           AND uta2.id_test = uta.id_test
                                           AND uta2.id_user_test_assigned > uta.id_user_test_assigned
                                           AND uta2.assignamente_date <= ans.date_create
                                     )))) AS attempts_used
                 FROM users_test_assigned uta
                 INNER JOIN company_test ct ON ct.id_test = uta.id_test
                 WHERE uta.id_users = :user" . $scopeSql . "
                   AND ct.type IN ('induccion','auditoria','autoevaluacion')
                 ORDER BY CASE WHEN uta.deadline IS NULL THEN 1 ELSE 0 END, uta.deadline ASC, uta.assignamente_date DESC"
            );
            $stmt->execute($params);

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $type = (string) ($row['type'] ?? '');
                if ($type === 'induccion') {
                    $category = 'induction';
                    $icon = 'bi-journal-check';
                    $labelKey = 'welcome_activity_type_course';
                    $baseHref = '../induccion/mis-induccion.php';
                } elseif ($type === 'auditoria') {
                    $category = 'audits';
                    $icon = 'bi-clipboard2-check';
                    $labelKey = 'welcome_activity_type_audit';
                    $baseHref = '../auditorias/mis-auditorias.php';
                } elseif ($type === 'autoevaluacion') {
                    $category = 'self';
                    $icon = 'bi-person-check';
                    $labelKey = 'welcome_activity_type_self';
                    $baseHref = '../autoevaluaciones/mis-autoevaluaciones.php';
                } else {
                    continue;
                }

                if (empty($can[$category])) continue;

                $state = (int) ($row['state'] ?? 1);
                if (!in_array($state, [1, 2, 3], true)) continue;

                $deadline = trim((string) ($row['deadline'] ?? ''));
                $deadlineTs = $deadline !== '' ? strtotime($deadline) : false;
                $availableAt = trim((string) ($row['assignamente_date'] ?? ''));
                $availableTs = $availableAt !== '' ? strtotime($availableAt) : false;
                $complete = $state === 2 || $state === 3;
                $scheduled = !$complete && $state === 1 && $availableTs !== false && $availableTs > time();
                $overdue = !$complete && !$scheduled && $state === 1 && $deadlineTs !== false && $deadlineTs < time();
                $bucket = $complete ? 'complete' : ($overdue ? 'overdue' : 'incomplete');
                $assignmentId = (int) ($row['id_user_test_assigned'] ?? 0);

                $items[] = [
                    'source' => 'test',
                    'source_id' => $assignmentId,
                    'category' => $category,
                    'type' => $type,
                    'type_label_key' => $labelKey,
                    'icon' => $icon,
                    'name' => (string) ($row['name'] ?? ''),
                    'description' => (string) ($row['description'] ?? ''),
                    'deadline' => $deadline,
                    'assigned_at' => (string) ($row['assignamente_date'] ?? ''),
                    'available_at' => $availableAt,
                    'scheduled' => $scheduled,
                    'completed_at' => $complete ? (string) ($row['last_update'] ?? '') : '',
                    'state' => $state,
                    'status_bucket' => $bucket,
                    'complete' => $complete,
                    'overdue' => $overdue,
                    'href' => $complete ? $baseHref : ($baseHref . '?start=' . $assignmentId),
                    'attempts_used' => (int) ($row['attempts_used'] ?? 0),
                    'attempts_allowed' => isset($row['attempts_allowed']) ? (int) $row['attempts_allowed'] : null,
                    'result' => $state === 2 ? 'positive' : ($state === 3 ? 'negative' : ''),
                ];
            }
        } catch (Throwable $e) {
            error_log('personal-activities tests: ' . $e->getMessage());
        }

        // Protocolos con asignación explícita al usuario o a su ficha de trabajador.
        if (!empty($can['protocols']) && $companyId > 0) {
            try {
                $whereResponsible = 'pa.responsible_user = :user';
                $params = [':company' => $companyId, ':user' => $userId];
                if ($workerId > 0) {
                    $whereResponsible = '(' . $whereResponsible . ' OR pa.id_worker = :worker)';
                    $params[':worker'] = $workerId;
                }

                $stmt = $pdo->prepare(
                    "SELECT pa.id_protocol_assignment, pa.start_at, pa.next_due_at, pa.state, pa.last_update,
                            p.name, p.description,
                            (SELECT MAX(pe.submitted_at) FROM protocol_executions pe WHERE pe.id_protocol_assignment = pa.id_protocol_assignment) AS last_execution_at
                     FROM protocol_assignments pa
                     INNER JOIN protocols p ON p.id_protocol = pa.id_protocol
                     WHERE pa.id_company = :company
                       AND " . $whereResponsible . "
                       AND pa.state IN ('activa','cerrada')
                     ORDER BY CASE WHEN pa.next_due_at IS NULL THEN 1 ELSE 0 END, pa.next_due_at ASC"
                );
                $stmt->execute($params);

                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                    $state = (string) ($row['state'] ?? '');
                    $complete = $state === 'cerrada';
                    $deadline = trim((string) ($row['next_due_at'] ?? ''));
                    $deadlineTs = $deadline !== '' ? strtotime($deadline) : false;
                    $availableAt = trim((string) ($row['start_at'] ?? ''));
                    $availableTs = $availableAt !== '' ? strtotime($availableAt) : false;
                    $scheduled = !$complete && $state === 'activa' && $availableTs !== false && $availableTs > time();
                    $overdue = !$complete && !$scheduled && $state === 'activa' && $deadlineTs !== false && $deadlineTs < time();
                    $bucket = $complete ? 'complete' : ($overdue ? 'overdue' : 'incomplete');
                    $assignmentId = (int) ($row['id_protocol_assignment'] ?? 0);

                    $items[] = [
                        'source' => 'protocol',
                        'source_id' => $assignmentId,
                        'category' => 'protocols',
                        'type' => 'protocolo',
                        'type_label_key' => 'mgmt_my_protocols_title',
                        'icon' => 'bi-clipboard2-pulse',
                        'name' => (string) ($row['name'] ?? ''),
                        'description' => (string) ($row['description'] ?? ''),
                        'deadline' => $deadline,
                        'assigned_at' => (string) ($row['start_at'] ?? ''),
                        'available_at' => $availableAt,
                        'scheduled' => $scheduled,
                        'completed_at' => $complete ? (string) (($row['last_execution_at'] ?? '') ?: ($row['last_update'] ?? '')) : '',
                        'state' => $state,
                        'status_bucket' => $bucket,
                        'complete' => $complete,
                        'overdue' => $overdue,
                        'href' => $complete
                            ? '../protocolos/mis-protocolos.php'
                            : '../protocolos/ejecutar-protocolo.php?id_assignment=' . $assignmentId,
                        'attempts_used' => null,
                        'attempts_allowed' => null,
                        'result' => '',
                    ];
                }
            } catch (Throwable $e) {
                error_log('personal-activities protocols: ' . $e->getMessage());
            }
        }

        // Formularios: P73 incorporó asignación individual. P76 usa esa misma
        // relación para Mi espacio y notificaciones, manteniendo compatibilidad
        // con formularios históricos que nunca tuvieron asignaciones.
        if (!empty($can['forms']) && $companyId > 0) {
            try {
                $hasAssignments = false;
                try {
                    $hasAssignments = (int) $pdo->query(
                        "SELECT COUNT(*) FROM information_schema.TABLES "
                        . "WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dynamic_form_assignments'"
                    )->fetchColumn() === 1;
                } catch (Throwable $ignore) {
                    $hasAssignments = false;
                }

                if ($hasAssignments) {
                    $stmt = $pdo->prepare(
                        "SELECT f.id_form, f.name, f.description, f.date_create,
                                a.id_form_assignment,a.access_start,a.deadline,a.status AS assignment_status,
                                a.date_create AS assignment_created_at,a.last_update AS assignment_updated_at,
                                (SELECT MAX(s.submitted_at)
                                   FROM dynamic_form_submissions s
                                  WHERE s.id_form=f.id_form
                                    AND s.id_company=:submission_company
                                    AND s.id_users=:submission_user
                                    AND s.status='submitted'
                                    AND (a.id_form_assignment IS NULL OR s.submitted_at>=COALESCE(a.access_start,a.date_create))) AS submitted_at
                           FROM dynamic_forms f
                           LEFT JOIN dynamic_form_assignments a
                             ON a.id_form=f.id_form
                            AND a.id_company=:assignment_company
                            AND a.id_users=:assignment_user
                          WHERE f.state=1
                            AND (f.id_company=:company OR f.id_company IS NULL)
                            AND NOT EXISTS (SELECT 1 FROM protocol_forms pf WHERE pf.id_form=f.id_form)
                            AND (
                                (a.id_form_assignment IS NOT NULL AND a.status IN ('pending','submitted'))
                                OR NOT EXISTS (
                                    SELECT 1 FROM dynamic_form_assignments ax
                                     WHERE ax.id_form=f.id_form AND ax.id_company=:legacy_company
                                )
                            )
                          ORDER BY CASE WHEN a.deadline IS NULL THEN 1 ELSE 0 END,a.deadline ASC,(f.id_company IS NULL) DESC,f.name ASC"
                    );
                    $stmt->execute([
                        ':submission_company' => $companyId,
                        ':submission_user' => $userId,
                        ':assignment_company' => $companyId,
                        ':assignment_user' => $userId,
                        ':company' => $companyId,
                        ':legacy_company' => $companyId,
                    ]);
                } else {
                    $stmt = $pdo->prepare(
                        "SELECT f.id_form, f.name, f.description, f.date_create,
                                NULL AS id_form_assignment,NULL AS access_start,NULL AS deadline,
                                NULL AS assignment_status,NULL AS assignment_created_at,NULL AS assignment_updated_at,
                                (SELECT MAX(s.submitted_at)
                                   FROM dynamic_form_submissions s
                                  WHERE s.id_form=f.id_form
                                    AND s.id_company=:submission_company
                                    AND s.id_users=:submission_user
                                    AND s.status='submitted') AS submitted_at
                           FROM dynamic_forms f
                          WHERE f.state=1
                            AND (f.id_company=:company OR f.id_company IS NULL)
                            AND NOT EXISTS (SELECT 1 FROM protocol_forms pf WHERE pf.id_form=f.id_form)
                          ORDER BY (f.id_company IS NULL) DESC,f.name ASC"
                    );
                    $stmt->execute([
                        ':submission_company' => $companyId,
                        ':submission_user' => $userId,
                        ':company' => $companyId,
                    ]);
                }

                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                    $submittedAt = trim((string) ($row['submitted_at'] ?? ''));
                    $assignmentStatus = trim((string) ($row['assignment_status'] ?? ''));
                    $hasExplicitAssignment = (int) ($row['id_form_assignment'] ?? 0) > 0;
                    $complete = $hasExplicitAssignment ? $assignmentStatus === 'submitted' : $submittedAt !== '';
                    $formId = (int) ($row['id_form'] ?? 0);
                    $deadline = trim((string) ($row['deadline'] ?? ''));
                    $deadlineTs = $deadline !== '' ? strtotime($deadline) : false;
                    $accessStart = trim((string) ($row['access_start'] ?? ''));
                    $scheduled = !$complete && $accessStart !== '' && strtotime($accessStart) !== false && strtotime($accessStart) > time();
                    $overdue = !$complete && !$scheduled && $deadlineTs !== false && $deadlineTs < time();

                    $items[] = [
                        'source' => 'form',
                        'source_id' => $formId,
                        'assignment_id' => isset($row['id_form_assignment']) ? (int) $row['id_form_assignment'] : 0,
                        'category' => 'forms',
                        'type' => 'formulario',
                        'type_label_key' => 'mgmt_my_forms_title',
                        'icon' => 'bi-card-text',
                        'name' => (string) ($row['name'] ?? ''),
                        'description' => (string) ($row['description'] ?? ''),
                        'deadline' => $deadline,
                        'assigned_at' => (string) (($row['assignment_created_at'] ?? '') ?: ($row['date_create'] ?? '')),
                        'available_at' => $accessStart,
                        'completed_at' => $submittedAt !== '' ? $submittedAt : ($complete ? (string) ($row['assignment_updated_at'] ?? '') : ''),
                        'state' => $complete ? 'submitted' : ($scheduled ? 'scheduled' : 'available'),
                        'status_bucket' => $complete ? 'complete' : ($overdue ? 'overdue' : 'incomplete'),
                        'complete' => $complete,
                        'overdue' => $overdue,
                        'scheduled' => $scheduled,
                        'href' => $complete
                            ? '../formularios/mis-formularios.php'
                            : '../formularios/mis-formularios.php?start=' . $formId,
                        'attempts_used' => null,
                        'attempts_allowed' => null,
                        'result' => '',
                    ];
                }
            } catch (Throwable $e) {
                error_log('personal-activities forms: ' . $e->getMessage());
            }
        }

        usort($items, static function (array $a, array $b): int {
            $rank = ['overdue' => 0, 'incomplete' => 1, 'complete' => 2];
            $ra = $rank[$a['status_bucket'] ?? 'incomplete'] ?? 1;
            $rb = $rank[$b['status_bucket'] ?? 'incomplete'] ?? 1;
            if ($ra !== $rb) return $ra <=> $rb;

            $da = !empty($a['deadline']) && strtotime((string) $a['deadline']) !== false
                ? strtotime((string) $a['deadline'])
                : PHP_INT_MAX;
            $db = !empty($b['deadline']) && strtotime((string) $b['deadline']) !== false
                ? strtotime((string) $b['deadline'])
                : PHP_INT_MAX;
            if ($da !== $db) return $da <=> $db;
            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return $items;
    }
}

if (!function_exists('sctPersonalActivitySummary')) {
    function sctPersonalActivitySummary(array $items): array
    {
        $summary = [
            'total' => 0,
            'pending' => 0,
            'overdue' => 0,
            'complete' => 0,
            'progress' => 0.0,
            'next_due' => null,
            'by_category' => [],
            'test_positive' => 0,
            'test_negative' => 0,
        ];

        $categories = ['induction', 'audits', 'self', 'forms', 'protocols'];
        foreach ($categories as $category) {
            $summary['by_category'][$category] = ['total' => 0, 'pending' => 0, 'overdue' => 0, 'complete' => 0];
        }

        $nextTs = null;
        foreach ($items as $item) {
            $summary['total']++;
            $bucket = (string) ($item['status_bucket'] ?? 'incomplete');
            $category = (string) ($item['category'] ?? '');
            if (!isset($summary['by_category'][$category])) {
                $summary['by_category'][$category] = ['total' => 0, 'pending' => 0, 'overdue' => 0, 'complete' => 0];
            }
            $summary['by_category'][$category]['total']++;

            if ($bucket === 'complete') {
                $summary['complete']++;
                $summary['by_category'][$category]['complete']++;
            } else {
                $summary['pending']++;
                $summary['by_category'][$category]['pending']++;
                if ($bucket === 'overdue') {
                    $summary['overdue']++;
                    $summary['by_category'][$category]['overdue']++;
                }

                $deadline = trim((string) ($item['deadline'] ?? ''));
                $deadlineTs = $deadline !== '' ? strtotime($deadline) : false;
                if ($deadlineTs !== false && $deadlineTs >= time() && ($nextTs === null || $deadlineTs < $nextTs)) {
                    $nextTs = $deadlineTs;
                    $summary['next_due'] = $deadline;
                }
            }

            if (($item['source'] ?? '') === 'test') {
                if (($item['result'] ?? '') === 'positive') $summary['test_positive']++;
                if (($item['result'] ?? '') === 'negative') $summary['test_negative']++;
            }
        }

        $summary['progress'] = $summary['total'] > 0
            ? round(($summary['complete'] / $summary['total']) * 100, 1)
            : 0.0;

        return $summary;
    }
}

if (!function_exists('sctPersonalActivityTimeline')) {
    /**
     * Evolución temporal real del usuario.
     * - Evaluaciones: finalización por users_test_assigned.last_update.
     * - Protocolos: ejecución por protocol_executions.submitted_at.
     * - Formularios: envío por dynamic_form_submissions.submitted_at; cuando P73 está
     *   disponible, la asignación usa dynamic_form_assignments.
     */
    function sctPersonalActivityTimeline(PDO $pdo, int $companyId, string $userId, ?string $desde, ?string $hasta): array
    {
        $hastaDt = $hasta !== null ? new DateTimeImmutable($hasta) : new DateTimeImmutable('now');
        $workerId = ($companyId > 0 && $userId !== '') ? sctPersonalActivityWorkerId($pdo, $companyId, $userId) : 0;
        if ($desde !== null) {
            $desdeDt = new DateTimeImmutable($desde);
        } else {
            // "Todo" empieza en el primer movimiento histórico real del usuario.
            $earliest = [];
            if ($companyId > 0 && $userId !== '') {
                try {
                    $stmt = $pdo->prepare("SELECT MIN(a.last_update) FROM users_test_assigned a INNER JOIN company_test t ON t.id_test=a.id_test WHERE a.id_company=:company AND a.id_users=:user AND t.type IN ('induccion','auditoria','autoevaluacion') AND a.state IN (2,3)");
                    $stmt->execute([':company'=>$companyId, ':user'=>$userId]);
                    $value = $stmt->fetchColumn(); if ($value) $earliest[] = (string) $value;
                } catch (Throwable $e) { error_log('personal-activities earliest evaluations: ' . $e->getMessage()); }
                try {
                    $stmt = $pdo->prepare("SELECT MIN(s.submitted_at) FROM dynamic_form_submissions s WHERE s.id_company=:company AND s.id_users=:user AND s.status='submitted' AND NOT EXISTS (SELECT 1 FROM protocol_forms pf WHERE pf.id_form=s.id_form)");
                    $stmt->execute([':company'=>$companyId, ':user'=>$userId]);
                    $value = $stmt->fetchColumn(); if ($value) $earliest[] = (string) $value;
                } catch (Throwable $e) { error_log('personal-activities earliest forms: ' . $e->getMessage()); }
                try {
                    $responsibleSql = 'pa.responsible_user = :user';
                    $params = [':company'=>$companyId, ':user'=>$userId];
                    if ($workerId > 0) { $responsibleSql = '(' . $responsibleSql . ' OR pa.id_worker = :worker)'; $params[':worker']=$workerId; }
                    $stmt = $pdo->prepare("SELECT MIN(pe.submitted_at) FROM protocol_executions pe INNER JOIN protocol_assignments pa ON pa.id_protocol_assignment=pe.id_protocol_assignment WHERE pa.id_company=:company AND " . $responsibleSql);
                    $stmt->execute($params);
                    $value = $stmt->fetchColumn(); if ($value) $earliest[] = (string) $value;
                } catch (Throwable $e) { error_log('personal-activities earliest protocols: ' . $e->getMessage()); }
            }
            $earliestTs = null;
            foreach ($earliest as $value) { $ts = strtotime($value); if ($ts !== false && ($earliestTs === null || $ts < $earliestTs)) $earliestTs = $ts; }
            $desdeDt = $earliestTs !== null ? (new DateTimeImmutable())->setTimestamp($earliestTs) : $hastaDt->modify('-11 months');
        }
        $rangeDays = (int) $desdeDt->diff($hastaDt)->format('%a');
        $useDailyBuckets = $rangeDays <= 31;
        $bucketFormat = $useDailyBuckets ? 'Y-m-d' : 'Y-m';
        $sqlDateFormat = $useDailyBuckets ? '%Y-%m-%d' : '%Y-%m';

        $buckets = [];
        if ($useDailyBuckets) {
            $desdeBucket = $desdeDt->setTime(0, 0);
            $hastaBucket = $hastaDt->setTime(0, 0);
            for ($cursor = $desdeBucket; $cursor <= $hastaBucket; $cursor = $cursor->modify('+1 day')) {
                $key = $cursor->format($bucketFormat);
                $buckets[$key] = [
                    'periodo' => $key,
                    'induction' => 0,
                    'audits' => 0,
                    'self' => 0,
                    'protocols' => 0,
                    'forms' => 0,
                ];
            }
        } else {
            $desdeBucket = $desdeDt->modify('first day of this month')->setTime(0, 0);
            $hastaBucket = $hastaDt->modify('first day of this month')->setTime(0, 0);
            for ($cursor = $desdeBucket; $cursor <= $hastaBucket; $cursor = $cursor->modify('+1 month')) {
                $key = $cursor->format($bucketFormat);
                $buckets[$key] = [
                    'periodo' => $key,
                    'induction' => 0,
                    'audits' => 0,
                    'self' => 0,
                    'protocols' => 0,
                    'forms' => 0,
                ];
            }
        }

        if ($companyId <= 0 || $userId === '') return array_values($buckets);
        $from = $desdeDt->format('Y-m-d H:i:s');
        $to = $hastaDt->format('Y-m-d H:i:s');

        try {
            $stmt = $pdo->prepare(
                "SELECT DATE_FORMAT(a.last_update, '" . $sqlDateFormat . "') AS periodo, t.type, COUNT(*) AS cantidad
                 FROM users_test_assigned a
                 INNER JOIN company_test t ON t.id_test = a.id_test
                 WHERE a.id_company = :company
                   AND a.id_users = :user
                   AND t.type IN ('induccion','auditoria','autoevaluacion')
                   AND a.state IN (2,3)
                   AND a.last_update BETWEEN :from AND :to
                 GROUP BY periodo, t.type"
            );
            $stmt->execute([':company' => $companyId, ':user' => $userId, ':from' => $from, ':to' => $to]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $period = (string) ($row['periodo'] ?? '');
                $type = (string) ($row['type'] ?? '');
                if (!isset($buckets[$period])) continue;
                if ($type === 'induccion') $buckets[$period]['induction'] = (int) ($row['cantidad'] ?? 0);
                elseif ($type === 'auditoria') $buckets[$period]['audits'] = (int) ($row['cantidad'] ?? 0);
                elseif ($type === 'autoevaluacion') $buckets[$period]['self'] = (int) ($row['cantidad'] ?? 0);
            }
        } catch (Throwable $e) {
            error_log('personal-activities timeline evaluations: ' . $e->getMessage());
        }

        try {
            $responsibleSql = 'pa.responsible_user = :user';
            $params = [':company' => $companyId, ':user' => $userId, ':from' => $from, ':to' => $to];
            if ($workerId > 0) {
                $responsibleSql = '(' . $responsibleSql . ' OR pa.id_worker = :worker)';
                $params[':worker'] = $workerId;
            }
            $stmt = $pdo->prepare(
                "SELECT DATE_FORMAT(pe.submitted_at, '" . $sqlDateFormat . "') AS periodo, COUNT(*) AS cantidad
                 FROM protocol_executions pe
                 INNER JOIN protocol_assignments pa ON pa.id_protocol_assignment = pe.id_protocol_assignment
                 WHERE pa.id_company = :company
                   AND " . $responsibleSql . "
                   AND pe.submitted_at BETWEEN :from AND :to
                 GROUP BY periodo"
            );
            $stmt->execute($params);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $period = (string) ($row['periodo'] ?? '');
                if (isset($buckets[$period])) $buckets[$period]['protocols'] = (int) ($row['cantidad'] ?? 0);
            }
        } catch (Throwable $e) {
            error_log('personal-activities timeline protocols: ' . $e->getMessage());
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT DATE_FORMAT(s.submitted_at, '" . $sqlDateFormat . "') AS periodo, COUNT(*) AS cantidad
                 FROM dynamic_form_submissions s
                 INNER JOIN dynamic_forms f ON f.id_form = s.id_form
                 WHERE s.id_company = :company
                   AND s.id_users = :user
                   AND s.status = 'submitted'
                   AND NOT EXISTS (SELECT 1 FROM protocol_forms pf WHERE pf.id_form = s.id_form)
                   AND s.submitted_at BETWEEN :from AND :to
                 GROUP BY periodo"
            );
            $stmt->execute([':company' => $companyId, ':user' => $userId, ':from' => $from, ':to' => $to]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $period = (string) ($row['periodo'] ?? '');
                if (isset($buckets[$period])) $buckets[$period]['forms'] = (int) ($row['cantidad'] ?? 0);
            }
        } catch (Throwable $e) {
            error_log('personal-activities timeline forms: ' . $e->getMessage());
        }

        return array_values($buckets);
    }
}
