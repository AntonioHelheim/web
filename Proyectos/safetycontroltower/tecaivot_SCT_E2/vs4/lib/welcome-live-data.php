<?php
/**
 * Datos vivos de Inicio para roles superiores.
 * Fuente única para bienvenida.php y api/usuarios/bienvenida-vivo.php.
 * No se utiliza para el rol trabajador/Usuario Demo.
 */

if (!function_exists('sctWelcomeLiveScalar')) {
    function sctWelcomeLiveScalar(PDO $pdo, $sql, array $params = [])
    {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('welcome-live scalar: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('sctWelcomeLiveDate')) {
    function sctWelcomeLiveDate(PDO $pdo, $sql, array $params = [])
    {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $value = $stmt->fetchColumn();
            return ($value === false || $value === null || $value === '') ? null : (string) $value;
        } catch (Throwable $e) {
            error_log('welcome-live date: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('sctWelcomeLiveCanAny')) {
    function sctWelcomeLiveCanAny(PDO $pdo, array $capabilities)
    {
        foreach ($capabilities as $capability) {
            if (currentUserHasCapability($pdo, (string) $capability)) return true;
        }
        return false;
    }
}

if (!function_exists('sctWelcomeLiveNearestDate')) {
    function sctWelcomeLiveNearestDate(array $dates)
    {
        $best = null;
        $bestTs = null;
        foreach ($dates as $value) {
            if (!$value) continue;
            $ts = strtotime((string) $value);
            if ($ts === false) continue;
            if ($bestTs === null || $ts < $bestTs) {
                $bestTs = $ts;
                $best = (string) $value;
            }
        }
        return $best;
    }
}

if (!function_exists('sctBuildWelcomeLiveData')) {
    function sctBuildWelcomeLiveData(PDO $pdo, $activityRole, array $profile, array $roles)
    {
        $activityRole = (string) $activityRole;
        $companyId = isset($profile['id_company']) ? (int) $profile['id_company'] : 0;
        $isGlobalAdmin = $activityRole === 'administrador_completo'
            && currentUserHasCapability($pdo, 'companies.view_all')
            && currentUserHasCapability($pdo, 'dashboard.global');
        $hasCompanyScope = $isGlobalAdmin || $companyId > 0;
        $activities = [];
        $nextDates = [];

        $add = static function ($enabled, $key, $value, $href) use (&$activities) {
            if (!$enabled) return;
            $activities[] = [
                'key' => (string) $key,
                'value' => max(0, (int) $value),
                'href' => (string) $href,
            ];
        };

        if ($hasCompanyScope) {
            $eventAccess = sctWelcomeLiveCanAny($pdo, ['events.view', 'events.manage', 'events.create']);
            $protocolAccess = sctWelcomeLiveCanAny($pdo, ['protocols.view', 'protocols.manage']);
            $auditAccess = sctWelcomeLiveCanAny($pdo, ['audits.view', 'audits.manage']);
            $inductionAccess = sctWelcomeLiveCanAny($pdo, ['induction.view', 'induction.manage']);
            $programAccess = currentUserHasCapability($pdo, 'programs.view');

            if ($eventAccess) {
                $count = $isGlobalAdmin
                    ? sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM security_events WHERE state <> 3 AND criticality IN ('alta','critica')")
                    : sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM security_events WHERE id_company = :company AND state <> 3 AND criticality IN ('alta','critica')", [':company' => $companyId]);
                $add(true, 'events', $count, './api/eventos/gestion-eventos.php');
            }

            if ($protocolAccess) {
                $count = $isGlobalAdmin
                    ? sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM protocol_assignments WHERE state = 'activa' AND next_due_at IS NOT NULL AND next_due_at <= DATE_ADD(NOW(), INTERVAL 30 DAY)")
                    : sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM protocol_assignments WHERE id_company = :company AND state = 'activa' AND next_due_at IS NOT NULL AND next_due_at <= DATE_ADD(NOW(), INTERVAL 30 DAY)", [':company' => $companyId]);
                $add(true, 'protocols', $count, './api/protocolos/gestion-protocolos.php');
                $nextDates[] = $isGlobalAdmin
                    ? sctWelcomeLiveDate($pdo, "SELECT MIN(next_due_at) FROM protocol_assignments WHERE state = 'activa' AND next_due_at >= NOW()")
                    : sctWelcomeLiveDate($pdo, "SELECT MIN(next_due_at) FROM protocol_assignments WHERE id_company = :company AND state = 'activa' AND next_due_at >= NOW()", [':company' => $companyId]);
            }

            if ($auditAccess) {
                $count = $isGlobalAdmin
                    ? sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM audits WHERE status IN ('pendiente','en_curso')")
                    : sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM audits WHERE id_company = :company AND status IN ('pendiente','en_curso')", [':company' => $companyId]);
                $add(true, 'audits', $count, './api/auditorias/gestion-auditorias.php');
            }

            if ($activityRole !== 'cliente' && $inductionAccess) {
                $count = $isGlobalAdmin
                    ? sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM users_test_assigned uta INNER JOIN company_test ct ON ct.id_test = uta.id_test WHERE ct.type = 'induccion' AND uta.state = 1")
                    : sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM users_test_assigned uta INNER JOIN company_test ct ON ct.id_test = uta.id_test WHERE uta.id_company = :company AND ct.type = 'induccion' AND uta.state = 1", [':company' => $companyId]);
                $add(true, 'induction', $count, './api/induccion/gestion-induccion.php');
                $nextDates[] = $isGlobalAdmin
                    ? sctWelcomeLiveDate($pdo, "SELECT MIN(uta.deadline) FROM users_test_assigned uta INNER JOIN company_test ct ON ct.id_test = uta.id_test WHERE ct.type = 'induccion' AND uta.state = 1 AND uta.deadline >= NOW()")
                    : sctWelcomeLiveDate($pdo, "SELECT MIN(uta.deadline) FROM users_test_assigned uta INNER JOIN company_test ct ON ct.id_test = uta.id_test WHERE uta.id_company = :company AND ct.type = 'induccion' AND uta.state = 1 AND uta.deadline >= NOW()", [':company' => $companyId]);
            }

            if ($activityRole === 'cliente' && $programAccess) {
                $count = $isGlobalAdmin
                    ? sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM programs WHERE state = 1 AND status IN ('planificado','en_curso')")
                    : sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM programs WHERE id_company = :company AND state = 1 AND status IN ('planificado','en_curso')", [':company' => $companyId]);
                $add(true, 'programs', $count, './api/programas/gestion-programas.php');
            }

            if ($programAccess) {
                $nextDates[] = $isGlobalAdmin
                    ? sctWelcomeLiveDate($pdo, "SELECT MIN(end_date) FROM programs WHERE state = 1 AND status IN ('planificado','en_curso') AND end_date >= CURDATE()")
                    : sctWelcomeLiveDate($pdo, "SELECT MIN(end_date) FROM programs WHERE id_company = :company AND state = 1 AND status IN ('planificado','en_curso') AND end_date >= CURDATE()", [':company' => $companyId]);
            }
        }

        usort($activities, static function ($a, $b) {
            return (int) $b['value'] <=> (int) $a['value'];
        });
        $activities = array_slice($activities, 0, 4);

        $pendingTotal = 0;
        foreach ($activities as $item) $pendingTotal += (int) $item['value'];

        switch ($activityRole) {
            case 'administrador_completo':
                $scopeValue = sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM company WHERE state = 1");
                break;
            case 'administrador':
                $scopeValue = $companyId > 0 ? sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM users WHERE id_company = :company AND state = 1", [':company' => $companyId]) : 0;
                break;
            case 'jefatura':
                $scopeValue = $companyId > 0 ? sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM workers WHERE id_company = :company AND state = 1", [':company' => $companyId]) : 0;
                break;
            case 'cliente':
                $scopeValue = $companyId > 0 ? sctWelcomeLiveScalar($pdo, "SELECT COUNT(*) FROM projects WHERE id_company = :company AND state = 1", [':company' => $companyId]) : 0;
                break;
            default:
                $scopeValue = 0;
                break;
        }

        return [
            'role' => $activityRole,
            'company_id' => $companyId,
            'global' => $isGlobalAdmin,
            'scope_value' => max(0, (int) $scopeValue),
            'pending_total' => max(0, (int) $pendingTotal),
            'next_due' => sctWelcomeLiveNearestDate($nextDates),
            'activities' => $activities,
            'generated_at' => date(DATE_ATOM),
        ];
    }
}
