<?php
require_once __DIR__ . '/personal-activities.php';
/**
 * Fuente compartida de avisos pendientes para Bienvenida, Dashboard y app-navbar.
 * P77: cada pendiente se expone como una notificación individual; no se agrupan
 * por tipo ni se recorta artificialmente el listado. Leer un aviso no modifica estados.
 */

if (!function_exists('sctNotificationRows')) {
    function sctNotificationRows(PDO $pdo, string $sql, array $params = []): array
    {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('sctNotificationRows: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('sctNotificationShortText')) {
    function sctNotificationShortText(string $value, int $max = 110): string
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        if ($value === '') return '';
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($value, 'UTF-8') > $max ? rtrim(mb_substr($value, 0, $max - 1, 'UTF-8')) . '…' : $value;
        }
        return strlen($value) > $max ? rtrim(substr($value, 0, $max - 3)) . '...' : $value;
    }
}

if (!function_exists('sctNotificationDateText')) {
    function sctNotificationDateText($value, string $prefix = ''): string
    {
        if (!$value) return '';
        $timestamp = strtotime((string) $value);
        if ($timestamp === false) return '';
        return trim($prefix . ($prefix !== '' ? ' ' : '') . date('d/m/Y', $timestamp));
    }
}

if (!function_exists('sctBuildPendingNotifications')) {
    function sctBuildPendingNotifications(PDO $pdo, string $userId, array $roles, array $profile, string $basePath = './'): array
    {
        $items = [];
        $companyId = (int) ($profile['id_company'] ?? 0);
        $role = function_exists('primaryRoleName') ? (primaryRoleName($roles) ?: 'trabajador') : 'trabajador';
        $roleLabel = function_exists('roleDisplayLabel') ? roleDisplayLabel($role) : ucfirst(str_replace('_', ' ', $role));
        $duePrefix = function_exists('t') ? t('welcome_metric_due_prefix') : 'Vence';

        $can = static function (PDO $pdo, string $capability): bool {
            return !function_exists('currentUserHasCapability') || currentUserHasCapability($pdo, $capability);
        };

        if ($userId !== '' && $role === 'trabajador' && $companyId > 0) {
            $personalItems = sctPersonalActivityItems($pdo, $companyId, $userId, 'trabajador', false);
            foreach ($personalItems as $item) {
                if (!empty($item['complete'])) continue;

                $source = (string) ($item['source'] ?? '');
                $sourceId = (int) ($item['source_id'] ?? 0);
                if ($sourceId <= 0) continue;

                $scheduled = !empty($item['scheduled']);
                $href = $scheduled
                    ? $basePath . 'api/usuarios/mis-actividades.php'
                    : sctPersonalActivityHubHref($item, $basePath . 'api/usuarios/mis-actividades.php', 'navbar');

                $deadline = '';
                if ($scheduled && !empty($item['available_at'])) {
                    $availableTs = strtotime((string) $item['available_at']);
                    if ($availableTs !== false) {
                        $deadline = (function_exists('t') ? t('activities_available_from') : 'Disponible desde') . ' ' . date('d/m/Y H:i', $availableTs);
                    }
                } elseif (!empty($item['deadline'])) {
                    $deadline = sctNotificationDateText($item['deadline'], $duePrefix);
                }
                if (!empty($item['overdue'])) {
                    $statusText = function_exists('t') ? t('activities_status_overdue') : 'Vencida';
                    $deadline = trim($statusText . ($deadline !== '' ? ' · ' . $deadline : ''));
                } elseif ($source === 'form' && $deadline === '') {
                    $deadline = function_exists('t') ? t('activities_status_available') : 'Disponible';
                }

                $typeLabel = function_exists('t') ? t((string) ($item['type_label_key'] ?? 'welcome_activity_type_other')) : '';
                $items[] = [
                    'key' => $source . ':' . $sourceId,
                    'icon' => (string) ($item['icon'] ?? 'bi-list-task'),
                    'title' => (string) ($item['name'] ?? $typeLabel),
                    'type_label' => $typeLabel,
                    'meta' => $deadline,
                    'href' => $href,
                    'count' => 1,
                    'show_count' => false,
                    '_sort' => (string) ($item['assigned_at'] ?? $item['available_at'] ?? $item['deadline'] ?? ''),
                ];
            }
        }

        $appendAuditRows = static function (array $rows) use (&$items, $basePath, $roleLabel): void {
            foreach ($rows as $row) {
                $id = (int) ($row['id_audits'] ?? 0);
                if ($id <= 0) continue;
                $activityName = trim((string) ($row['activity_name'] ?? ''));
                $title = $activityName !== '' ? $activityName : ((function_exists('t') ? t('welcome_role_audits_open') : 'Auditorías abiertas') . ' #' . $id);
                $auditor = trim((string) ($row['name_auditor'] ?? ''));
                if ($auditor === '') $auditor = trim((string) ($row['email'] ?? ''));
                $status = (string) ($row['status'] ?? 'pendiente');
                $statusKey = [
                    'pendiente' => 'audit_status_pending',
                    'en_curso' => 'audit_status_in_progress',
                    'completada' => 'audit_status_completed',
                    'cancelada' => 'audit_status_cancelled',
                ][$status] ?? '';
                $statusText = $statusKey !== '' && function_exists('t') ? t($statusKey) : str_replace('_', ' ', $status);
                $meta = trim($statusText . ($auditor !== '' ? ' · ' . $auditor : ''));
                $items[] = [
                    'key' => 'audit:' . $id,
                    'icon' => 'bi-shield-exclamation',
                    'title' => $title,
                    'type_label' => $roleLabel,
                    'meta' => $meta,
                    'href' => $basePath . 'api/auditorias/gestion-auditorias.php',
                    'count' => 1,
                    'show_count' => false,
                    '_sort' => (string) ($row['last_update'] ?? $row['date_create'] ?? ''),
                ];
            }
        };

        $appendEventRows = static function (array $rows) use (&$items, $basePath, $roleLabel): void {
            $generic = function_exists('t') ? t('welcome_role_critical_events') : 'Eventos críticos';
            foreach ($rows as $row) {
                $id = (int) ($row['id_security_events'] ?? 0);
                if ($id <= 0) continue;
                $description = sctNotificationShortText((string) ($row['description'] ?? ''), 115);
                $criticality = trim((string) ($row['criticality'] ?? ''));
                $date = sctNotificationDateText($row['event_date'] ?? null);
                $metaParts = array_values(array_filter([$criticality, $description, $date], static function ($v) { return $v !== ''; }));
                $items[] = [
                    'key' => 'event:' . $id,
                    'icon' => 'bi-exclamation-triangle',
                    'title' => $generic . ' #' . $id,
                    'type_label' => $roleLabel,
                    'meta' => implode(' · ', $metaParts),
                    'href' => $basePath . 'api/eventos/gestion-eventos.php',
                    'count' => 1,
                    'show_count' => false,
                    '_sort' => (string) ($row['last_update'] ?? $row['date_create'] ?? $row['event_date'] ?? ''),
                ];
            }
        };

        if ($role === 'administrador_completo') {
            if ($can($pdo, 'audits.manage')) {
                $appendAuditRows(sctNotificationRows($pdo, "SELECT a.id_audits, a.name_auditor, a.email, a.status, a.date_create, a.last_update, ct.name AS activity_name
                    FROM audits a
                    LEFT JOIN company_test ct ON ct.id_test = a.id_test
                    WHERE a.status IN ('pendiente','en_curso')
                    ORDER BY a.last_update DESC, a.id_audits DESC"));
            }
            if ($can($pdo, 'events.manage')) {
                $appendEventRows(sctNotificationRows($pdo, "SELECT id_security_events, description, criticality, event_date, date_create, last_update
                    FROM security_events
                    WHERE criticality IN ('alta','critica') AND state <> 3
                    ORDER BY last_update DESC, id_security_events DESC"));
            }
        } elseif ($companyId > 0 && in_array($role, ['administrador', 'jefatura', 'cliente'], true)) {
            if (in_array($role, ['administrador', 'jefatura'], true) && $can($pdo, 'induction.manage')) {
                $courseRows = sctNotificationRows($pdo, "SELECT uta.id_user_test_assigned, uta.id_users, uta.deadline, uta.last_update, ct.name AS course_name, u.name, u.lastname
                    FROM users_test_assigned uta
                    INNER JOIN company_test ct ON ct.id_test = uta.id_test
                    LEFT JOIN users u ON u.id_users = uta.id_users
                    WHERE uta.id_company = :company AND uta.state = 1 AND ct.type = 'induccion'
                    ORDER BY uta.last_update DESC, uta.id_user_test_assigned DESC", [':company' => $companyId]);
                foreach ($courseRows as $row) {
                    $id = (int) ($row['id_user_test_assigned'] ?? 0);
                    if ($id <= 0) continue;
                    $person = trim((string) ($row['name'] ?? '') . ' ' . (string) ($row['lastname'] ?? ''));
                    if ($person === '') $person = (string) ($row['id_users'] ?? '');
                    $due = sctNotificationDateText($row['deadline'] ?? null, $duePrefix);
                    $meta = trim($person . ($due !== '' ? ' · ' . $due : ''));
                    $items[] = [
                        'key' => 'induction:' . $id,
                        'icon' => 'bi-journal-check',
                        'title' => (string) ($row['course_name'] ?? (function_exists('t') ? t('welcome_role_team_courses') : 'Inducción pendiente')),
                        'type_label' => $roleLabel,
                        'meta' => $meta,
                        'href' => $basePath . 'api/induccion/gestion-induccion.php',
                        'count' => 1,
                        'show_count' => false,
                        '_sort' => (string) ($row['last_update'] ?? ''),
                    ];
                }
            }

            if ($can($pdo, 'audits.manage')) {
                $appendAuditRows(sctNotificationRows($pdo, "SELECT a.id_audits, a.name_auditor, a.email, a.status, a.date_create, a.last_update, ct.name AS activity_name
                    FROM audits a
                    LEFT JOIN company_test ct ON ct.id_test = a.id_test
                    WHERE a.id_company = :company AND a.status IN ('pendiente','en_curso')
                    ORDER BY a.last_update DESC, a.id_audits DESC", [':company' => $companyId]));
            }

            if (in_array($role, ['jefatura', 'cliente'], true) && $can($pdo, 'protocols.manage')) {
                $protocolRows = sctNotificationRows($pdo, "SELECT pa.id_protocol_assignment, pa.responsible_user, pa.next_due_at, pa.last_update, p.name
                    FROM protocol_assignments pa
                    INNER JOIN protocols p ON p.id_protocol = pa.id_protocol
                    WHERE pa.id_company = :company AND pa.state = 'activa' AND pa.next_due_at <= DATE_ADD(NOW(), INTERVAL 30 DAY)
                    ORDER BY pa.next_due_at ASC, pa.id_protocol_assignment DESC", [':company' => $companyId]);
                foreach ($protocolRows as $row) {
                    $id = (int) ($row['id_protocol_assignment'] ?? 0);
                    if ($id <= 0) continue;
                    $due = sctNotificationDateText($row['next_due_at'] ?? null, $duePrefix);
                    $responsible = trim((string) ($row['responsible_user'] ?? ''));
                    $items[] = [
                        'key' => 'protocol:' . $id,
                        'icon' => 'bi-clipboard-pulse',
                        'title' => (string) ($row['name'] ?? (function_exists('t') ? t('welcome_role_protocols_due') : 'Protocolo')),
                        'type_label' => $roleLabel,
                        'meta' => trim($due . ($responsible !== '' ? ' · ' . $responsible : '')),
                        'href' => $basePath . 'api/protocolos/gestion-protocolos.php',
                        'count' => 1,
                        'show_count' => false,
                        '_sort' => (string) ($row['last_update'] ?? $row['next_due_at'] ?? ''),
                    ];
                }
            }

            if ($role === 'cliente' && $can($pdo, 'events.manage')) {
                $appendEventRows(sctNotificationRows($pdo, "SELECT id_security_events, description, criticality, event_date, date_create, last_update
                    FROM security_events
                    WHERE id_company = :company AND criticality IN ('alta','critica') AND state <> 3
                    ORDER BY last_update DESC, id_security_events DESC", [':company' => $companyId]));
            }
        }

        usort($items, static function (array $a, array $b): int {
            return strcmp((string) ($b['_sort'] ?? ''), (string) ($a['_sort'] ?? ''));
        });
        foreach ($items as &$item) unset($item['_sort']);
        unset($item);

        return $items;
    }
}
