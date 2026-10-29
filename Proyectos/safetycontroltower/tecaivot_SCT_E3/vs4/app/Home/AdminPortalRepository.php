<?php

/**
 * Lecturas de apoyo para bienvenida.php del Administrador Cliente.
 * No modifica datos y respeta el aislamiento por id_company.
 */
final class SctAdminPortalRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function activeSiteCatalog(int $companyId): array
    {
        if ($companyId<=0 || !$this->projectsSupportSiteScope()) {
            return [];
        }

        $stmt=$this->pdo->prepare(
            'SELECT
                p.id_project,
                p.name,
                p.gbu_code,
                p.site_name,
                COUNT(DISTINCT wp.id_worker) AS worker_count,
                COUNT(DISTINCT u.id_users) AS user_count
             FROM projects p
             LEFT JOIN worker_projects wp
               ON wp.id_project=p.id_project
             LEFT JOIN users u
               ON u.id_worker=wp.id_worker
              AND u.id_company=p.id_company
              AND u.state=1
             WHERE p.id_company=:id_company
               AND p.state=1
               AND p.gbu_code IS NOT NULL
               AND TRIM(p.gbu_code)<>\'\'
               AND p.site_name IS NOT NULL
               AND TRIM(p.site_name)<>\'\'
             GROUP BY
                p.id_project,p.name,p.gbu_code,p.site_name
             ORDER BY
                p.gbu_code,p.site_name,p.name,p.id_project'
        );
        $stmt->execute(['id_company'=>$companyId]);

        $catalog=[];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $gbu=trim((string)$row['gbu_code']);
            $site=trim((string)$row['site_name']);
            $siteKey=self::normalizeName($site);

            if (!isset($catalog[$gbu])) {
                $catalog[$gbu]=[];
            }
            if (!isset($catalog[$gbu][$siteKey])) {
                $catalog[$gbu][$siteKey]=[
                    'site_name'=>$site,
                    'projects'=>[],
                ];
            }

            $catalog[$gbu][$siteKey]['projects'][]=[
                'id_project'=>(int)$row['id_project'],
                'name'=>(string)$row['name'],
                'worker_count'=>(int)$row['worker_count'],
                'user_count'=>(int)$row['user_count'],
            ];
        }

        foreach ($catalog as $gbu=>&$sites) {
            $sites=array_values($sites);
        }
        unset($sites);

        uksort($catalog,'strnatcasecmp');
        return $catalog;
    }

    public function activeProjectsByName(int $companyId): array
    {
        if ($companyId <= 0) {
            return [];
        }

        $hasSiteScope = $this->projectsSupportSiteScope();

        if ($hasSiteScope) {
            $sql = 'SELECT
                        p.id_project,
                        p.name,
                        p.gbu_code,
                        p.site_name,
                        COUNT(DISTINCT wp.id_worker) AS worker_count,
                        COUNT(DISTINCT u.id_users) AS user_count
                    FROM projects p
                    LEFT JOIN worker_projects wp
                        ON wp.id_project=p.id_project
                    LEFT JOIN users u
                        ON u.id_worker=wp.id_worker
                       AND u.id_company=p.id_company
                       AND u.state=1
                    WHERE p.id_company=:id_company
                      AND p.state=1
                    GROUP BY
                        p.id_project,
                        p.name,
                        p.gbu_code,
                        p.site_name
                    ORDER BY
                        COALESCE(NULLIF(p.gbu_code, \'\'), \'ZZZ\'),
                        COALESCE(NULLIF(p.site_name, \'\'), p.name)';
        } else {
            $sql = 'SELECT
                        p.id_project,
                        p.name,
                        NULL AS gbu_code,
                        p.name AS site_name,
                        COUNT(DISTINCT wp.id_worker) AS worker_count,
                        COUNT(DISTINCT u.id_users) AS user_count
                    FROM projects p
                    LEFT JOIN worker_projects wp
                        ON wp.id_project=p.id_project
                    LEFT JOIN users u
                        ON u.id_worker=wp.id_worker
                       AND u.id_company=p.id_company
                       AND u.state=1
                    WHERE p.id_company=:id_company
                      AND p.state=1
                    GROUP BY p.id_project,p.name
                    ORDER BY p.name';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_company' => $companyId]);

        $projects = [];

        foreach ($stmt->fetchAll() as $row) {
            $siteName = trim((string)($row['site_name'] ?? $row['name'] ?? ''));
            $normalized = self::normalizeName($siteName);

            if ($normalized === '') {
                continue;
            }

            $projects[$normalized] = [
                'id_project' => (int)$row['id_project'],
                'name' => (string)$row['name'],
                'gbu_code' => trim((string)($row['gbu_code'] ?? '')),
                'site_name' => $siteName,
                'worker_count' => (int)($row['worker_count'] ?? 0),
                'user_count' => (int)($row['user_count'] ?? 0),
            ];
        }

        return $projects;
    }

    private function projectsSupportSiteScope(): bool
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COUNT(*)
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA=DATABASE()
                   AND TABLE_NAME='projects'
                   AND COLUMN_NAME IN ('gbu_code','site_name')"
            );

            return (int)$stmt->fetchColumn() === 2;
        } catch (Throwable $e) {
            error_log(
                'AdminPortalRepository::projectsSupportSiteScope: '
                . $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Mide cumplimiento de la regla E3-VS2: usuario activo debe estar
     * asociado a un trabajador y ese trabajador a, al menos, un proyecto
     * activo de su propia empresa.
     */
    public function projectAssignmentCoverage(int $companyId): array
    {
        if ($companyId <= 0) {
            return [
                'total' => 0,
                'assigned' => 0,
                'pending' => 0,
            ];
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(
                    CASE WHEN u.id_worker IS NOT NULL AND (
                        EXISTS (
                            SELECT 1
                            FROM worker_projects wp
                            INNER JOIN projects p
                              ON p.id_project=wp.id_project
                             AND p.id_company=u.id_company
                             AND p.state=1
                            WHERE wp.id_worker=u.id_worker
                        )
                        OR EXISTS (
                            SELECT 1
                            FROM user_profile_details upd
                            INNER JOIN projects p2
                              ON p2.id_project=upd.confirmed_project_id
                             AND p2.id_company=u.id_company
                             AND p2.state=1
                            WHERE upd.id_users=u.id_users
                        )
                    ) THEN 1 ELSE 0 END
                ) AS assigned
             FROM users u
             WHERE u.id_company=:id_company
               AND u.state=1'
        );
        $stmt->execute(['id_company' => $companyId]);
        $row = $stmt->fetch() ?: [];

        $total = (int)($row['total'] ?? 0);
        $assigned = (int)($row['assigned'] ?? 0);

        return [
            'total' => $total,
            'assigned' => $assigned,
            'pending' => max(0, $total - $assigned),
        ];
    }

    public static function normalizeName(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        return strtoupper($value);
    }
}
