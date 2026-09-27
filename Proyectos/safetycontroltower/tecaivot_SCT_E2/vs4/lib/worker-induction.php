<?php
/**
 * Lecturas simples para el perfil Trabajador / Usuario Demo.
 *
 * Regla de experiencia P42:
 * - El perfil sólo resume inducciones personales.
 * - Estado 1 + plazo vigente => En curso.
 * - Estado 1 + plazo vencido => Vencida.
 * - Estado 2 => Completada aprobada.
 * - Estado 3 => Completada reprobada.
 *
 * No mezcla auditorías, autoevaluaciones, formularios, protocolos ni eventos.
 */

if (!function_exists('sctWorkerInductionSummary')) {
    function sctWorkerInductionSummary(PDO $pdo, $companyId, $userId)
    {
        $companyId = (int) $companyId;
        $userId = trim((string) $userId);

        $empty = [
            'total' => 0,
            'en_curso' => 0,
            'vencidas' => 0,
            'aprobadas' => 0,
            'reprobadas' => 0,
            'completadas' => 0,
            'avance' => 0.0,
            'proximo_vencimiento' => null,
        ];

        if ($companyId <= 0 || $userId === '') {
            return $empty;
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE
                        WHEN a.state = 1
                         AND (a.deadline IS NULL OR a.deadline >= NOW())
                        THEN 1 ELSE 0 END) AS en_curso,
                    SUM(CASE
                        WHEN a.state = 1
                         AND a.deadline IS NOT NULL
                         AND a.deadline < NOW()
                        THEN 1 ELSE 0 END) AS vencidas,
                    SUM(CASE WHEN a.state = 2 THEN 1 ELSE 0 END) AS aprobadas,
                    SUM(CASE WHEN a.state = 3 THEN 1 ELSE 0 END) AS reprobadas,
                    MIN(CASE
                        WHEN a.state = 1 AND a.deadline >= NOW()
                        THEN a.deadline ELSE NULL END) AS proximo_vencimiento
                 FROM users_test_assigned a
                 INNER JOIN company_test t ON t.id_test = a.id_test
                 WHERE a.id_company = :id_company
                   AND a.id_users = :id_user
                   AND t.type = 'induccion'"
            );
            $stmt->execute([
                ':id_company' => $companyId,
                ':id_user' => $userId,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $total = (int) ($row['total'] ?? 0);
            $approved = (int) ($row['aprobadas'] ?? 0);
            $failed = (int) ($row['reprobadas'] ?? 0);
            $completed = $approved + $failed;

            return [
                'total' => $total,
                'en_curso' => (int) ($row['en_curso'] ?? 0),
                'vencidas' => (int) ($row['vencidas'] ?? 0),
                'aprobadas' => $approved,
                'reprobadas' => $failed,
                'completadas' => $completed,
                'avance' => $total > 0 ? round(($completed / $total) * 100, 1) : 0.0,
                'proximo_vencimiento' => $row['proximo_vencimiento'] ?? null,
            ];
        } catch (Throwable $e) {
            error_log('lib/worker-induction.php summary: ' . $e->getMessage());
            return $empty;
        }
    }
}

if (!function_exists('sctWorkerInductionPending')) {
    function sctWorkerInductionPending(PDO $pdo, $companyId, $userId, $limit = 3)
    {
        $companyId = (int) $companyId;
        $userId = trim((string) $userId);
        $limit = max(1, min((int) $limit, 6));

        if ($companyId <= 0 || $userId === '') {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT
                    a.id_user_test_assigned,
                    a.id_test,
                    a.deadline,
                    a.assignamente_date,
                    t.name,
                    t.description,
                    CASE
                        WHEN a.deadline IS NOT NULL AND a.deadline < NOW() THEN 'vencida'
                        ELSE 'en_curso'
                    END AS visual_state
                 FROM users_test_assigned a
                 INNER JOIN company_test t ON t.id_test = a.id_test
                 WHERE a.id_company = :id_company
                   AND a.id_users = :id_user
                   AND t.type = 'induccion'
                   AND a.state = 1
                 ORDER BY
                    CASE WHEN a.deadline IS NOT NULL AND a.deadline < NOW() THEN 0 ELSE 1 END,
                    a.deadline ASC,
                    a.assignamente_date ASC
                 LIMIT " . $limit
            );
            $stmt->execute([
                ':id_company' => $companyId,
                ':id_user' => $userId,
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('lib/worker-induction.php pending: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('sctWorkerInductionItems')) {
    /**
     * Listado completo y personal de inducciones del Usuario Demo.
     * Se usa en Bienvenida para filtrar sin duplicar otro catálogo visual.
     */
    function sctWorkerInductionItems(PDO $pdo, $companyId, $userId)
    {
        $companyId = (int) $companyId;
        $userId = trim((string) $userId);

        if ($companyId <= 0 || $userId === '') {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT
                    a.id_user_test_assigned,
                    a.id_test,
                    a.deadline,
                    a.assignamente_date,
                    a.state,
                    t.name,
                    t.description,
                    t.attempts_allowed,
                    COALESCE(ans.attempts_used, 0) AS attempts_used
                 FROM users_test_assigned a
                 INNER JOIN company_test t ON t.id_test = a.id_test
                 LEFT JOIN (
                    SELECT id_users, id_test, COUNT(DISTINCT id_test_try) AS attempts_used
                    FROM users_test_answers
                    GROUP BY id_users, id_test
                 ) ans ON ans.id_users = a.id_users AND ans.id_test = a.id_test
                 WHERE a.id_company = :id_company
                   AND a.id_users = :id_user
                   AND t.type = 'induccion'
                 ORDER BY
                    CASE
                        WHEN a.state = 1 AND a.deadline IS NOT NULL AND a.deadline < NOW() THEN 0
                        WHEN a.state = 1 THEN 1
                        WHEN a.state = 2 THEN 2
                        WHEN a.state = 3 THEN 3
                        ELSE 4
                    END,
                    a.deadline ASC,
                    a.assignamente_date DESC"
            );
            $stmt->execute([
                ':id_company' => $companyId,
                ':id_user' => $userId,
            ]);

            $items = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $state = (int) ($row['state'] ?? 1);
                $deadline = trim((string) ($row['deadline'] ?? ''));
                $deadlineTs = $deadline !== '' ? strtotime($deadline) : false;
                $overdue = $state === 1 && $deadlineTs !== false && $deadlineTs < time();

                if ($state === 2) {
                    $visualState = 'approved';
                } elseif ($state === 3) {
                    $visualState = 'failed';
                } elseif ($overdue) {
                    $visualState = 'overdue';
                } else {
                    $visualState = 'in_progress';
                }

                $items[] = [
                    'id_user_test_assigned' => (int) ($row['id_user_test_assigned'] ?? 0),
                    'id_test' => (int) ($row['id_test'] ?? 0),
                    'name' => (string) ($row['name'] ?? ''),
                    'description' => (string) ($row['description'] ?? ''),
                    'deadline' => $deadline,
                    'assignamente_date' => (string) ($row['assignamente_date'] ?? ''),
                    'state' => $state,
                    'visual_state' => $visualState,
                    'attempts_used' => (int) ($row['attempts_used'] ?? 0),
                    'attempts_allowed' => isset($row['attempts_allowed']) ? (int) $row['attempts_allowed'] : null,
                ];
            }

            return $items;
        } catch (Throwable $e) {
            error_log('lib/worker-induction.php items: ' . $e->getMessage());
            return [];
        }
    }
}
