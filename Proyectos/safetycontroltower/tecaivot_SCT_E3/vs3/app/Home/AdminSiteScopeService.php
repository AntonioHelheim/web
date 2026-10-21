<?php

/**
 * Safety Control Tower — Etapa 3 VS2
 * Contexto validado de navegación Administrador por GBU / Sitio.
 */
final class SctAdminSiteScopeService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function resolveForAdministrator(
        int $companyId,
        int $projectId
    ): ?array {
        if ($companyId <= 0 || $projectId <= 0) {
            return null;
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT
                    id_project,
                    id_company,
                    name,
                    gbu_code,
                    site_name
                 FROM projects
                 WHERE id_project=:id_project
                   AND id_company=:id_company
                   AND state=1
                 LIMIT 1'
            );
            $stmt->execute([
                'id_project' => $projectId,
                'id_company' => $companyId,
            ]);

            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }

            $gbu = trim((string)($row['gbu_code'] ?? ''));
            $site = trim((string)($row['site_name'] ?? ''));

            if ($gbu === '' || $site === '') {
                return null;
            }

            return [
                'id_project' => (int)$row['id_project'],
                'id_company' => (int)$row['id_company'],
                'project_name' => (string)$row['name'],
                'gbu' => $gbu,
                'site' => $site,
            ];
        } catch (Throwable $e) {
            error_log(
                'SctAdminSiteScopeService::resolveForAdministrator: '
                . $e->getMessage()
            );

            return null;
        }
    }

    public static function saveToSession(array $scope): void
    {
        $_SESSION['sct_admin_site_scope'] = [
            'id_project' => (int)($scope['id_project'] ?? 0),
            'id_company' => (int)($scope['id_company'] ?? 0),
            'project_name' => (string)($scope['project_name'] ?? ''),
            'gbu' => (string)($scope['gbu'] ?? ''),
            'site' => (string)($scope['site'] ?? ''),
            'selected_at' => time(),
        ];
    }

    public static function currentFromSession(): ?array
    {
        $scope = $_SESSION['sct_admin_site_scope'] ?? null;

        if (!is_array($scope)) {
            return null;
        }

        return $scope;
    }
}
