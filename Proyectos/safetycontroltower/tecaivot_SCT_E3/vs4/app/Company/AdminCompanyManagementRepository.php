<?php

/**
 * Safety Control Tower — Etapa 3 VS2
 * Gestión de Empresa para Administrador Cliente.
 *
 * Alcance:
 * - datos de empresa
 * - casa matriz
 * - sedes
 * - GBU / Sitios / Proyectos
 * - resumen de perfiles
 *
 * Usuarios y trabajadores se administran en Gestión de Usuarios.
 */
final class SctAdminCompanyManagementRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function company(int $companyId): ?array
    {
        $stmt=$this->pdo->prepare(
            'SELECT
                id_company,
                rut,
                razon_social,
                address,
                email,
                logo_path,
                state,
                COALESCE(is_contractor,0) AS is_contractor
             FROM company
             WHERE id_company=:id_company
             LIMIT 1'
        );
        $stmt->execute(['id_company'=>$companyId]);
        $row=$stmt->fetch();
        return $row ?: null;
    }

    public function centers(int $companyId): array
    {
        $stmt=$this->pdo->prepare(
            'SELECT
                id_company_center,
                name,
                description,
                COALESCE(address,\'\') AS address,
                state
             FROM company_center
             WHERE id_company=:id_company
             ORDER BY state DESC,name,id_company_center'
        );
        $stmt->execute(['id_company'=>$companyId]);
        return $stmt->fetchAll() ?: [];
    }

    public function projects(int $companyId): array
    {
        $stmt=$this->pdo->prepare(
            'SELECT
                id_project,
                name,
                description,
                gbu_code,
                site_name,
                state
             FROM projects
             WHERE id_company=:id_company
             ORDER BY
                state DESC,
                COALESCE(NULLIF(gbu_code,\'\'),\'ZZZ\'),
                COALESCE(NULLIF(site_name,\'\'),name),
                id_project'
        );
        $stmt->execute(['id_company'=>$companyId]);
        return $stmt->fetchAll() ?: [];
    }

    public function scopeCatalog(int $companyId): array
    {
        $projects=$this->projects($companyId);

        $gbus=[];
        $sites=[];
        foreach ($projects as $project) {
            $gbu=trim((string)($project['gbu_code'] ?? ''));
            $site=trim((string)($project['site_name'] ?? ''));

            if ($gbu!=='') {
                $gbus[$gbu]=$gbu;
            }

            if ($gbu!=='' && $site!=='') {
                $sites[$gbu . "\0" . $site]=[
                    'gbu_code'=>$gbu,
                    'site_name'=>$site,
                ];
            }
        }

        natcasesort($gbus);

        uasort($sites,static function(array $a,array $b): int {
            $gbuCmp=strcasecmp($a['gbu_code'],$b['gbu_code']);
            return $gbuCmp!==0
                ? $gbuCmp
                : strcasecmp($a['site_name'],$b['site_name']);
        });

        return [
            'gbus'=>array_values($gbus),
            'sites'=>array_values($sites),
            'projects'=>array_map(static fn(array $row): array => [
                'id_project'=>(int)$row['id_project'],
                'name'=>(string)$row['name'],
                'description'=>(string)($row['description'] ?? ''),
                'gbu_code'=>(string)($row['gbu_code'] ?? ''),
                'site_name'=>(string)($row['site_name'] ?? ''),
                'state'=>(int)$row['state'],
            ],$projects),
        ];
    }

    public function renameGbu(
        int $companyId,
        string $oldGbu,
        string $newGbu,
        string $actor
    ): int {
        $oldGbu=trim($oldGbu);
        $newGbu=trim($newGbu);

        if ($oldGbu==='' || $newGbu==='') {
            throw new InvalidArgumentException(
                t('company_manage_err_gbu_required')
            );
        }

        if (strcasecmp($oldGbu,$newGbu)===0) {
            return 0;
        }

        $this->pdo->beginTransaction();

        try {
            $stmt=$this->pdo->prepare(
                'SELECT id_project,gbu_code
                 FROM projects
                 WHERE id_company=:id_company
                   AND gbu_code=:gbu_code
                 FOR UPDATE'
            );
            $stmt->execute([
                'id_company'=>$companyId,
                'gbu_code'=>$oldGbu,
            ]);
            $rows=$stmt->fetchAll() ?: [];

            if (!$rows) {
                throw new InvalidArgumentException(
                    t('company_manage_err_gbu_not_found')
                );
            }

            $stmt=$this->pdo->prepare(
                'UPDATE projects
                 SET gbu_code=:new_gbu,last_update=NOW()
                 WHERE id_company=:id_company
                   AND gbu_code=:old_gbu'
            );
            $stmt->execute([
                'new_gbu'=>$newGbu,
                'id_company'=>$companyId,
                'old_gbu'=>$oldGbu,
            ]);

            foreach ($rows as $row) {
                $this->logChange(
                    'projects',
                    (string)$row['id_project'],
                    'gbu_code',
                    (string)$row['gbu_code'],
                    $newGbu,
                    $actor
                );
            }

            $count=count($rows);
            $this->pdo->commit();
            return $count;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deactivateGbu(
        int $companyId,
        string $gbu,
        string $actor
    ): int {
        $gbu=trim($gbu);

        if ($gbu==='') {
            throw new InvalidArgumentException(
                t('company_manage_err_gbu_required')
            );
        }

        $this->pdo->beginTransaction();

        try {
            $stmt=$this->pdo->prepare(
                'SELECT id_project,state
                 FROM projects
                 WHERE id_company=:id_company
                   AND gbu_code=:gbu_code
                 FOR UPDATE'
            );
            $stmt->execute([
                'id_company'=>$companyId,
                'gbu_code'=>$gbu,
            ]);
            $rows=$stmt->fetchAll() ?: [];

            if (!$rows) {
                throw new InvalidArgumentException(
                    t('company_manage_err_gbu_not_found')
                );
            }

            $stmt=$this->pdo->prepare(
                'UPDATE projects
                 SET state=0,last_update=NOW()
                 WHERE id_company=:id_company
                   AND gbu_code=:gbu_code
                   AND state<>0'
            );
            $stmt->execute([
                'id_company'=>$companyId,
                'gbu_code'=>$gbu,
            ]);

            foreach ($rows as $row) {
                if ((int)$row['state']===0) continue;

                $this->logChange(
                    'projects',
                    (string)$row['id_project'],
                    'state',
                    (string)$row['state'],
                    '0',
                    $actor
                );

                $this->logChange(
                    'projects',
                    (string)$row['id_project'],
                    'gbu_deactivation',
                    $gbu,
                    'inactive',
                    $actor
                );
            }

            $count=(int)$stmt->rowCount();
            $this->pdo->commit();
            return $count;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function renameSite(
        int $companyId,
        string $gbu,
        string $oldSite,
        string $newSite,
        string $actor
    ): int {
        $gbu=trim($gbu);
        $oldSite=trim($oldSite);
        $newSite=trim($newSite);

        if ($gbu==='' || $oldSite==='' || $newSite==='') {
            throw new InvalidArgumentException(
                t('company_manage_err_site_required')
            );
        }

        if (strcasecmp($oldSite,$newSite)===0) {
            return 0;
        }

        $this->pdo->beginTransaction();

        try {
            $stmt=$this->pdo->prepare(
                'SELECT id_project,site_name
                 FROM projects
                 WHERE id_company=:id_company
                   AND gbu_code=:gbu_code
                   AND site_name=:site_name
                 FOR UPDATE'
            );
            $stmt->execute([
                'id_company'=>$companyId,
                'gbu_code'=>$gbu,
                'site_name'=>$oldSite,
            ]);
            $rows=$stmt->fetchAll() ?: [];

            if (!$rows) {
                throw new InvalidArgumentException(
                    t('company_manage_err_site_not_found')
                );
            }

            $stmt=$this->pdo->prepare(
                'UPDATE projects
                 SET site_name=:new_site,last_update=NOW()
                 WHERE id_company=:id_company
                   AND gbu_code=:gbu_code
                   AND site_name=:old_site'
            );
            $stmt->execute([
                'new_site'=>$newSite,
                'id_company'=>$companyId,
                'gbu_code'=>$gbu,
                'old_site'=>$oldSite,
            ]);

            foreach ($rows as $row) {
                $this->logChange(
                    'projects',
                    (string)$row['id_project'],
                    'site_name',
                    (string)$row['site_name'],
                    $newSite,
                    $actor
                );
            }

            $this->pdo->commit();
            return count($rows);
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deactivateSite(
        int $companyId,
        string $gbu,
        string $site,
        string $actor
    ): int {
        $gbu=trim($gbu);
        $site=trim($site);

        if ($gbu==='' || $site==='') {
            throw new InvalidArgumentException(
                t('company_manage_err_site_required')
            );
        }

        $this->pdo->beginTransaction();

        try {
            $stmt=$this->pdo->prepare(
                'SELECT id_project,state
                 FROM projects
                 WHERE id_company=:id_company
                   AND gbu_code=:gbu_code
                   AND site_name=:site_name
                 FOR UPDATE'
            );
            $stmt->execute([
                'id_company'=>$companyId,
                'gbu_code'=>$gbu,
                'site_name'=>$site,
            ]);
            $rows=$stmt->fetchAll() ?: [];

            if (!$rows) {
                throw new InvalidArgumentException(
                    t('company_manage_err_site_not_found')
                );
            }

            $stmt=$this->pdo->prepare(
                'UPDATE projects
                 SET state=0,last_update=NOW()
                 WHERE id_company=:id_company
                   AND gbu_code=:gbu_code
                   AND site_name=:site_name
                   AND state<>0'
            );
            $stmt->execute([
                'id_company'=>$companyId,
                'gbu_code'=>$gbu,
                'site_name'=>$site,
            ]);

            foreach ($rows as $row) {
                if ((int)$row['state']===0) continue;

                $this->logChange(
                    'projects',
                    (string)$row['id_project'],
                    'site_deactivation',
                    $site,
                    'inactive',
                    $actor
                );
            }

            $count=(int)$stmt->rowCount();
            $this->pdo->commit();
            return $count;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deactivateProject(
        int $companyId,
        int $projectId,
        string $actor
    ): int {
        if ($companyId<=0 || $projectId<=0) {
            throw new InvalidArgumentException(
                t('company_manage_err_project')
            );
        }

        $stmt=$this->pdo->prepare(
            'SELECT id_project,state,name
             FROM projects
             WHERE id_company=:id_company
               AND id_project=:id_project
             LIMIT 1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_project'=>$projectId,
        ]);
        $row=$stmt->fetch();

        if (!$row) {
            throw new InvalidArgumentException(
                t('company_manage_err_project_not_found')
            );
        }

        if ((int)$row['state']===0) {
            return 0;
        }

        $stmt=$this->pdo->prepare(
            'UPDATE projects
             SET state=0,last_update=NOW()
             WHERE id_company=:id_company
               AND id_project=:id_project
             LIMIT 1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_project'=>$projectId,
        ]);

        $this->logChange(
            'projects',
            (string)$projectId,
            'project_deactivation',
            (string)$row['name'],
            'inactive',
            $actor
        );

        return (int)$stmt->rowCount();
    }


    private function logChange(
        string $tableName,
        string $recordId,
        string $fieldName,
        ?string $oldValue,
        ?string $newValue,
        string $actor
    ): void {
        $stmt=$this->pdo->prepare(
            'INSERT INTO change_history (
                table_name,
                record_id,
                field_name,
                old_value,
                new_value,
                changed_by,
                changed_at
             ) VALUES (
                :table_name,
                :record_id,
                :field_name,
                :old_value,
                :new_value,
                :changed_by,
                NOW()
             )'
        );
        $stmt->execute([
            'table_name'=>$tableName,
            'record_id'=>$recordId,
            'field_name'=>$fieldName,
            'old_value'=>$oldValue,
            'new_value'=>$newValue,
            'changed_by'=>$actor,
        ]);
    }

    public function profileSummary(int $companyId): array
    {
        $levelSql=SctAdminUserDirectoryPolicy::roleLevelSql('rg');

        $stmt=$this->pdo->prepare(
            'SELECT
                (' . $levelSql . ') AS access_level,
                COUNT(DISTINCT u.id_users) AS total
             FROM users u
             INNER JOIN users_role ur
               ON ur.id_users=u.id_users
              AND ur.state=1
             INNER JOIN users_role_group rg
               ON rg.id_role_group=ur.id_role_group
              AND rg.id_company=u.id_company
              AND rg.state=1
             WHERE u.id_company=:id_company
               AND u.state=1
             GROUP BY access_level
             HAVING access_level IN (2,3,4,5,6,7)
             ORDER BY access_level'
        );
        $stmt->execute(['id_company'=>$companyId]);

        $out=[];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $level=(int)$row['access_level'];
            $out[]=[
                'access_level'=>$level,
                'label'=>SctAdminUserDirectoryPolicy::displayLabel($level),
                'total'=>(int)$row['total'],
            ];
        }

        return $out;
    }

    public function saveCompany(
        int $companyId,
        array $data
    ): void {
        $stmt=$this->pdo->prepare(
            'UPDATE company
             SET
                rut=:rut,
                razon_social=:razon_social,
                address=:address,
                email=:email,
                is_contractor=:is_contractor,
                last_update=NOW()
             WHERE id_company=:id_company
             LIMIT 1'
        );
        $stmt->execute([
            'rut'=>$data['rut'],
            'razon_social'=>$data['razon_social'],
            'address'=>$data['address'],
            'email'=>$data['email'],
            'is_contractor'=>$data['is_contractor'] ? 1 : 0,
            'id_company'=>$companyId,
        ]);
    }

    public function saveCenter(
        int $companyId,
        ?int $centerId,
        array $data,
        string $actor
    ): int {
        if ($centerId) {
            $stmt=$this->pdo->prepare(
                'UPDATE company_center
                 SET
                    name=:name,
                    description=:description,
                    address=:address,
                    state=:state,
                    last_update=NOW()
                 WHERE id_company_center=:id
                   AND id_company=:id_company
                 LIMIT 1'
            );
            $stmt->execute([
                'name'=>$data['name'],
                'description'=>$data['description'],
                'address'=>$data['address'],
                'state'=>$data['state'],
                'id'=>$centerId,
                'id_company'=>$companyId,
            ]);

            return $centerId;
        }

        $stmt=$this->pdo->prepare(
            'INSERT INTO company_center (
                id_company,name,description,address,state,
                create_by,date_create,last_update
             ) VALUES (
                :id_company,:name,:description,:address,1,
                :actor,NOW(),NOW()
             )'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'name'=>$data['name'],
            'description'=>$data['description'],
            'address'=>$data['address'],
            'actor'=>$actor,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function saveProject(
        int $companyId,
        ?int $projectId,
        array $data,
        string $actor
    ): int {
        if ($projectId) {
            $stmt=$this->pdo->prepare(
                'UPDATE projects
                 SET
                    name=:name,
                    description=:description,
                    gbu_code=:gbu_code,
                    site_name=:site_name,
                    state=:state,
                    last_update=NOW()
                 WHERE id_project=:id
                   AND id_company=:id_company
                 LIMIT 1'
            );
            $stmt->execute([
                'name'=>$data['name'],
                'description'=>$data['description'],
                'gbu_code'=>$data['gbu_code'],
                'site_name'=>$data['site_name'],
                'state'=>$data['state'],
                'id'=>$projectId,
                'id_company'=>$companyId,
            ]);

            return $projectId;
        }

        $stmt=$this->pdo->prepare(
            'INSERT INTO projects (
                id_company,name,description,gbu_code,site_name,
                state,created_by,date_create,last_update
             ) VALUES (
                :id_company,:name,:description,:gbu_code,:site_name,
                1,:actor,NOW(),NOW()
             )'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'name'=>$data['name'],
            'description'=>$data['description'],
            'gbu_code'=>$data['gbu_code'],
            'site_name'=>$data['site_name'],
            'actor'=>$actor,
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}
