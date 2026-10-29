<?php

final class SctUserEvaluationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function allForUser(string $userId): array
    {
        $items = array_merge(
            $this->operationalAssignments($userId),
            $this->onboardingAttempts($userId)
        );

        usort($items, static function (array $a, array $b): int {
            return strcmp((string)($b['activity_at'] ?? ''), (string)($a['activity_at'] ?? ''));
        });

        return $items;
    }

    public function latestCompletedForUser(string $userId): ?array
    {
        foreach ($this->allForUser($userId) as $item) {
            $status = (string)($item['status'] ?? '');

            if (
                $item['result_percentage'] !== null
                || in_array($status, ['approved','failed','completed'], true)
            ) {
                return $item;
            }
        }

        return null;
    }

    private function operationalAssignments(string $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                a.id_user_test_assigned,
                a.id_test,
                a.id_company,
                a.assignamente_date,
                a.deadline,
                a.state,
                a.last_update,
                t.name,
                t.description,
                t.type,
                t.approval_percentage,
                t.attempts_allowed
             FROM users_test_assigned a
             INNER JOIN company_test t
                ON t.id_test = a.id_test
             WHERE a.id_users = :id_users
             ORDER BY a.last_update DESC, a.id_user_test_assigned DESC'
        );
        $stmt->execute(['id_users' => $userId]);

        $items = [];

        foreach ($stmt->fetchAll() as $row) {
            $type = strtolower((string)$row['type']);
            $state = (int)$row['state'];
            $attemptsUsed = 0;
            $percentage = null;
            $activityAt = (string)($row['last_update'] ?: $row['assignamente_date']);
            $status = $state === 2 ? 'approved' : ($state === 3 ? 'failed' : 'pending');
            $resumeUrl = null;

            if ($type === 'induccion') {
                $result = $this->inductionLatestResult(
                    $userId,
                    (int)$row['id_test']
                );
                $attemptsUsed = (int)$result['attempts_used'];
                $percentage = $result['percentage'];
                if (!empty($result['activity_at'])) $activityAt = (string)$result['activity_at'];

                if ($state === 1 && $attemptsUsed > 0) {
                    $status = 'in_progress';
                }

                if ($state === 1 && $attemptsUsed < (int)$row['attempts_allowed']) {
                    $resumeUrl = 'api/induccion/mis-induccion.php';
                }
            } elseif ($type === 'autoevaluacion') {
                $result = $this->assignmentLatestResult((int)$row['id_user_test_assigned']);
                $attemptsUsed = (int)$result['attempts_used'];
                $percentage = $result['percentage'];
                if (!empty($result['activity_at'])) $activityAt = (string)$result['activity_at'];

                if ($state === 1 && $attemptsUsed > 0) {
                    $status = 'in_progress';
                }

                if ($state === 1 && $attemptsUsed < (int)$row['attempts_allowed']) {
                    $resumeUrl = 'api/autoevaluaciones/mis-autoevaluaciones.php';
                }
            } elseif ($type === 'auditoria') {
                $audit = $this->auditResult((int)$row['id_user_test_assigned']);
                $attemptsUsed = (int)$audit['attempts_used'];
                $percentage = $audit['percentage'];
                if (!empty($audit['activity_at'])) $activityAt = (string)$audit['activity_at'];

                if (($audit['status'] ?? '') === 'en_curso') {
                    $status = 'in_progress';
                } elseif (($audit['status'] ?? '') === 'completada') {
                    $status = $percentage !== null
                        && $percentage >= (float)$row['approval_percentage']
                        ? 'approved'
                        : 'completed';
                }

                if ($state === 1 && ($audit['status'] ?? '') !== 'completada') {
                    $resumeUrl = 'api/auditorias/mis-auditorias.php';
                }
            } else {
                $result = $this->assignmentLatestResult((int)$row['id_user_test_assigned']);
                $attemptsUsed = (int)$result['attempts_used'];
                $percentage = $result['percentage'];
                if (!empty($result['activity_at'])) $activityAt = (string)$result['activity_at'];
            }

            $items[] = [
                'source' => 'company_test',
                'id' => (int)$row['id_user_test_assigned'],
                'test_id' => (int)$row['id_test'],
                'type' => $type,
                'name' => (string)$row['name'],
                'description' => (string)($row['description'] ?? ''),
                'status' => $status,
                'result_percentage' => $percentage,
                'approval_percentage' => (float)$row['approval_percentage'],
                'attempts_used' => $attemptsUsed,
                'attempts_allowed' => (int)$row['attempts_allowed'],
                'deadline' => $row['deadline'],
                'activity_at' => $activityAt,
                'resume_url' => $resumeUrl,
            ];
        }

        return $items;
    }

    private function onboardingAttempts(string $userId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT
                    id_attempt,
                    status,
                    score,
                    started_at,
                    completed_at
                 FROM onboarding_assessment_attempts
                 WHERE id_users = :id_users
                 ORDER BY COALESCE(completed_at, started_at) DESC, id_attempt DESC'
            );
            $stmt->execute(['id_users' => $userId]);

            $items = [];
            foreach ($stmt->fetchAll() as $row) {
                $items[] = [
                    'source' => 'onboarding',
                    'id' => (int)$row['id_attempt'],
                    'test_id' => null,
                    'type' => 'onboarding',
                    'name' => null,
                    'name_key' => 'evaluations_initial_sst',
                    'description' => '',
                    'status' => $row['status'] === 'completed' ? 'completed' : 'in_progress',
                    'result_percentage' => $row['score'] !== null ? (float)$row['score'] : null,
                    'approval_percentage' => null,
                    'attempts_used' => $row['status'] === 'completed' ? 1 : 0,
                    'attempts_allowed' => 1,
                    'deadline' => null,
                    'activity_at' => (string)($row['completed_at'] ?: $row['started_at']),
                    'resume_url' => null,
                ];
            }

            return $items;
        } catch (Throwable $e) {
            return [];
        }
    }

    private function inductionLatestResult(string $userId, int $testId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COUNT(DISTINCT id_test_try) AS attempts_used,
                MAX(id_test_try) AS latest_try,
                MAX(last_update) AS activity_at
             FROM users_test_answers
             WHERE id_users = :id_users
               AND id_test = :id_test'
        );
        $stmt->execute([
            'id_users' => $userId,
            'id_test' => $testId,
        ]);
        $meta = $stmt->fetch() ?: [];

        $latestTry = (int)($meta['latest_try'] ?? 0);
        $percentage = null;

        if ($latestTry > 0) {
            $percentage = $this->percentageByUserTestTry($userId, $testId, $latestTry);
        }

        return [
            'attempts_used' => (int)($meta['attempts_used'] ?? 0),
            'percentage' => $percentage,
            'activity_at' => $meta['activity_at'] ?? null,
        ];
    }

    private function assignmentLatestResult(int $assignmentId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT
                    COUNT(DISTINCT id_test_try) AS attempts_used,
                    MAX(id_test_try) AS latest_try,
                    MAX(last_update) AS activity_at
                 FROM users_test_answers
                 WHERE id_user_test_assigned = :id_assignment'
            );
            $stmt->execute(['id_assignment' => $assignmentId]);
            $meta = $stmt->fetch() ?: [];

            $latestTry = (int)($meta['latest_try'] ?? 0);
            $percentage = null;

            if ($latestTry > 0) {
                $stmtTest = $this->pdo->prepare(
                    'SELECT id_test
                     FROM users_test_assigned
                     WHERE id_user_test_assigned = :id_assignment
                     LIMIT 1'
                );
                $stmtTest->execute(['id_assignment' => $assignmentId]);
                $testId = (int)$stmtTest->fetchColumn();

                $percentage = $this->percentageByAssignmentTry(
                    $assignmentId,
                    $testId,
                    $latestTry
                );
            }

            return [
                'attempts_used' => (int)($meta['attempts_used'] ?? 0),
                'percentage' => $percentage,
                'activity_at' => $meta['activity_at'] ?? null,
            ];
        } catch (Throwable $e) {
            return [
                'attempts_used' => 0,
                'percentage' => null,
                'activity_at' => null,
            ];
        }
    }

    private function percentageByUserTestTry(string $userId, int $testId, int $try): ?float
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN o.is_it_co = 1 THEN r.assigned_score ELSE 0 END), 0) AS score,
                COALESCE((
                    SELECT SUM(rr.assigned_score)
                    FROM company_test_rel_questions rr
                    WHERE rr.id_test = :id_test_max
                ), 0) AS max_score
             FROM users_test_answers a
             INNER JOIN company_test_rel_questions r
                ON r.id_rel = a.id_rel
             INNER JOIN questions_options o
                ON o.id_questions_options = a.id_questions_options
             WHERE a.id_users = :id_users
               AND a.id_test = :id_test
               AND a.id_test_try = :id_test_try'
        );
        $stmt->execute([
            'id_test_max' => $testId,
            'id_users' => $userId,
            'id_test' => $testId,
            'id_test_try' => $try,
        ]);

        $row = $stmt->fetch() ?: [];
        $max = (float)($row['max_score'] ?? 0);
        if ($max <= 0) return null;

        return round(((float)$row['score'] / $max) * 100, 1);
    }

    private function percentageByAssignmentTry(int $assignmentId, int $testId, int $try): ?float
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN o.is_it_co = 1 THEN r.assigned_score ELSE 0 END), 0) AS score,
                COALESCE((
                    SELECT SUM(rr.assigned_score)
                    FROM company_test_rel_questions rr
                    WHERE rr.id_test = :id_test_max
                ), 0) AS max_score
             FROM users_test_answers a
             INNER JOIN company_test_rel_questions r
                ON r.id_rel = a.id_rel
             INNER JOIN questions_options o
                ON o.id_questions_options = a.id_questions_options
             WHERE a.id_user_test_assigned = :id_assignment
               AND a.id_test_try = :id_test_try'
        );
        $stmt->execute([
            'id_test_max' => $testId,
            'id_assignment' => $assignmentId,
            'id_test_try' => $try,
        ]);

        $row = $stmt->fetch() ?: [];
        $max = (float)($row['max_score'] ?? 0);
        if ($max <= 0) return null;

        return round(((float)$row['score'] / $max) * 100, 1);
    }

    private function auditResult(int $assignmentId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT
                    status,
                    score,
                    completed_at,
                    last_update
                 FROM audits
                 WHERE id_user_test_assigned = :id_assignment
                 ORDER BY id_audits DESC
                 LIMIT 1'
            );
            $stmt->execute(['id_assignment' => $assignmentId]);
            $audit = $stmt->fetch() ?: [];

            $attemptMeta = $this->assignmentLatestResult($assignmentId);

            return [
                'status' => $audit['status'] ?? null,
                'percentage' => $audit['score'] !== null && $audit['score'] !== ''
                    ? (float)$audit['score']
                    : $attemptMeta['percentage'],
                'attempts_used' => $attemptMeta['attempts_used'],
                'activity_at' => $audit['completed_at']
                    ?? $audit['last_update']
                    ?? $attemptMeta['activity_at'],
            ];
        } catch (Throwable $e) {
            return [
                'status' => null,
                'percentage' => null,
                'attempts_used' => 0,
                'activity_at' => null,
            ];
        }
    }
}
