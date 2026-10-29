<?php
require_once __DIR__ . '/AdminUserDirectoryPolicy.php';


/**
 * Safety Control Tower — Etapa 3 VS2
 * Directorio de usuarios del Administrador Cliente.
 *
 * Toda consulta se acota por id_company. GBU/Sitio provienen de projects.
 * Para búsquedas administrativas se considera como alcance efectivo tanto
 * worker_projects como user_profile_details.confirmed_project_id. Esto evita
 * que perfiles migrados/editados queden invisibles en una GBU recién asignada.
 */
final class SctAdminUserDirectoryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function filters(int $companyId, ?string $supervisorId=null): array
    {
        if ($companyId <= 0) return ['gbus'=>[], 'sites'=>[], 'projects'=>[]];

        $stmt = $this->pdo->prepare(
            'SELECT id_project,name,gbu_code,site_name
             FROM projects
             WHERE id_company=:id_company
               AND state=1
               ' . (
                   $supervisorId
                       ? 'AND EXISTS (
                            SELECT 1
                            FROM user_supervision_assignments usa_scope
                            INNER JOIN workers w_scope
                              ON w_scope.id_worker=usa_scope.id_worker
                             AND w_scope.id_company=usa_scope.id_company
                            LEFT JOIN worker_projects wp_scope
                              ON wp_scope.id_worker=w_scope.id_worker
                            LEFT JOIN user_profile_details upd_scope_filter
                              ON upd_scope_filter.id_worker=w_scope.id_worker
                             AND upd_scope_filter.id_company=w_scope.id_company
                            WHERE usa_scope.id_company=:scope_company
                              AND usa_scope.id_supervisor_users=:scope_supervisor
                              AND usa_scope.state=1
                              AND (
                                  wp_scope.id_project=projects.id_project
                                  OR upd_scope_filter.confirmed_project_id=projects.id_project
                              )
                        )'
                       : ''
               ) . '
             ORDER BY
               COALESCE(NULLIF(gbu_code, \'\'), \'ZZZ\'),
               COALESCE(NULLIF(site_name, \'\'), name)'
        );
        $filterParams=['id_company'=>$companyId];
        if ($supervisorId) {
            $filterParams['scope_company']=$companyId;
            $filterParams['scope_supervisor']=$supervisorId;
        }
        $stmt->execute($filterParams);
        $projects = $stmt->fetchAll() ?: [];

        return $this->buildFilters($projects);
    }


    /**
     * Catálogo canónico de alcance organizacional de una empresa.
     *
     * Para Administrador y Jefatura los selectores deben salir de projects,
     * no de las asignaciones de personas. Las restricciones sobre qué
     * personas puede ver Jefatura se aplican exclusivamente en search().
     */
    public function companyScopeCatalog(int $companyId): array
    {
        return $this->filters($companyId);
    }


    public function filtersForSupervisor(
        int $companyId,
        string $supervisorId
    ): array {
        if (
            $companyId<=0
            || trim($supervisorId)===''
            || !$this->tableExists('user_supervision_assignments')
        ) {
            return [
                'gbus'=>[],
                'sites'=>[],
                'projects'=>[],
            ];
        }

        /*
         * Alcances visibles para Jefatura:
         * 1. proyectos donde existen trabajadores bajo su responsabilidad;
         * 2. proyecto(s) de su propia ficha/trabajador;
         * 3. GBU/Sitio directo de su perfil, aun cuando Proyecto sea opcional.
         *
         * La selección final se limita siempre a projects de la misma empresa.
         */
        $stmt=$this->pdo->prepare(
            'SELECT DISTINCT
                p.id_project,
                p.name,
                TRIM(COALESCE(p.gbu_code,\'\')) AS gbu_code,
                TRIM(COALESCE(p.site_name,\'\')) AS site_name
             FROM projects p
             WHERE p.id_company=:id_company
               AND p.state=1
               AND (
                   EXISTS (
                       SELECT 1
                       FROM user_supervision_assignments usa
                       INNER JOIN workers sw
                         ON sw.id_worker=usa.id_worker
                        AND sw.id_company=usa.id_company
                       LEFT JOIN worker_projects swp
                         ON swp.id_worker=sw.id_worker
                       LEFT JOIN user_profile_details supd
                         ON supd.id_worker=sw.id_worker
                        AND supd.id_company=sw.id_company
                       WHERE usa.id_company=:assigned_company
                         AND usa.id_supervisor_users=:assigned_supervisor
                         AND usa.state=1
                         AND (
                             swp.id_project=p.id_project
                             OR supd.confirmed_project_id=p.id_project
                             OR (
                                 LOWER(TRIM(COALESCE(supd.gbu_code,\'\')))
                                     =LOWER(TRIM(COALESCE(p.gbu_code,\'\')))
                                 AND LOWER(TRIM(COALESCE(supd.site_name,\'\')))
                                     =LOWER(TRIM(COALESCE(p.site_name,\'\')))
                             )
                         )
                   )
                   OR EXISTS (
                       SELECT 1
                       FROM users su
                       LEFT JOIN worker_projects own_wp
                         ON own_wp.id_worker=su.id_worker
                       LEFT JOIN user_profile_details own_upd
                         ON own_upd.id_users=su.id_users
                        AND own_upd.id_company=su.id_company
                       WHERE su.id_company=:own_company
                         AND su.id_users=:own_supervisor
                         AND (
                             own_wp.id_project=p.id_project
                             OR own_upd.confirmed_project_id=p.id_project
                             OR (
                                 LOWER(TRIM(COALESCE(own_upd.gbu_code,\'\')))
                                     =LOWER(TRIM(COALESCE(p.gbu_code,\'\')))
                                 AND LOWER(TRIM(COALESCE(own_upd.site_name,\'\')))
                                     =LOWER(TRIM(COALESCE(p.site_name,\'\')))
                             )
                         )
                   )
               )
             ORDER BY
                gbu_code,
                site_name,
                p.name,
                p.id_project'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'assigned_company'=>$companyId,
            'assigned_supervisor'=>$supervisorId,
            'own_company'=>$companyId,
            'own_supervisor'=>$supervisorId,
        ]);

        return $this->buildFilters(
            $stmt->fetchAll() ?: []
        );
    }

    public function userScopeDefaults(
        int $companyId,
        string $userId
    ): array {
        $empty=[
            'gbu'=>'',
            'site'=>'',
            'id_project'=>null,
        ];

        if ($companyId<=0 || trim($userId)==='') {
            return $empty;
        }

        /*
         * Fuente prioritaria: user_profile_details, que es exactamente la
         * ficha editada desde "Datos de usuario".
         *
         * Fallback: confirmed_project_id y finalmente worker_projects.
         */
        $stmt=$this->pdo->prepare(
            'SELECT
                u.id_worker,
                NULLIF(TRIM(upd.gbu_code),\'\') AS profile_gbu,
                NULLIF(TRIM(upd.site_name),\'\') AS profile_site,
                upd.confirmed_project_id
             FROM users u
             LEFT JOIN user_profile_details upd
               ON upd.id_users=u.id_users
              AND upd.id_company=u.id_company
             WHERE u.id_company=:id_company
               AND u.id_users=:id_users
             ORDER BY upd.last_update DESC
             LIMIT 1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_users'=>$userId,
        ]);

        $row=$stmt->fetch() ?: [];
        $gbu=trim((string)($row['profile_gbu'] ?? ''));
        $site=trim((string)($row['profile_site'] ?? ''));
        $projectId=isset($row['confirmed_project_id'])
            && $row['confirmed_project_id']!==null
            ? (int)$row['confirmed_project_id']
            : null;

        if ($projectId) {
            $stmt=$this->pdo->prepare(
                'SELECT id_project,gbu_code,site_name
                 FROM projects
                 WHERE id_project=:id_project
                   AND id_company=:id_company
                   AND state=1
                 LIMIT 1'
            );
            $stmt->execute([
                'id_project'=>$projectId,
                'id_company'=>$companyId,
            ]);
            $project=$stmt->fetch() ?: [];

            if ($gbu==='') {
                $gbu=trim((string)($project['gbu_code'] ?? ''));
            }
            if ($site==='') {
                $site=trim((string)($project['site_name'] ?? ''));
            }

            if (!$project) {
                $projectId=null;
            }
        }

        if (
            ($gbu==='' || $site==='' || !$projectId)
            && !empty($row['id_worker'])
        ) {
            $stmt=$this->pdo->prepare(
                'SELECT p.id_project,p.gbu_code,p.site_name
                 FROM worker_projects wp
                 INNER JOIN projects p
                   ON p.id_project=wp.id_project
                  AND p.id_company=:id_company
                  AND p.state=1
                 WHERE wp.id_worker=:id_worker
                 ORDER BY p.id_project
                 LIMIT 1'
            );
            $stmt->execute([
                'id_company'=>$companyId,
                'id_worker'=>(int)$row['id_worker'],
            ]);
            $project=$stmt->fetch() ?: [];

            if ($gbu==='') {
                $gbu=trim((string)($project['gbu_code'] ?? ''));
            }
            if ($site==='') {
                $site=trim((string)($project['site_name'] ?? ''));
            }
            if (!$projectId && !empty($project['id_project'])) {
                $projectId=(int)$project['id_project'];
            }
        }

        return [
            'gbu'=>$gbu,
            'site'=>$site,
            'id_project'=>$projectId,
        ];
    }

    private function buildFilters(array $projects): array
    {
        $gbus=[];
        $sites=[];
        $cleanProjects=[];

        foreach ($projects as $project) {
            $gbu=trim((string)($project['gbu_code'] ?? ''));
            $site=trim((string)($project['site_name'] ?? ''));

            if ($gbu==='') {
                continue;
            }

            $gbus[mb_strtolower($gbu,'UTF-8')]=$gbu;

            if ($site!=='') {
                $sites[
                    mb_strtolower($gbu,'UTF-8')
                    . '|'
                    . mb_strtolower($site,'UTF-8')
                ]=[
                    'gbu'=>$gbu,
                    'site'=>$site,
                ];
            }

            $cleanProjects[]=[
                'id_project'=>(int)$project['id_project'],
                'name'=>(string)$project['name'],
                'gbu'=>$gbu,
                'site'=>$site,
            ];
        }

        natcasesort($gbus);

        usort(
            $cleanProjects,
            static function(array $a,array $b): int {
                return strcasecmp(
                    $a['gbu'].'|'.$a['site'].'|'.$a['name'],
                    $b['gbu'].'|'.$b['site'].'|'.$b['name']
                );
            }
        );

        return [
            'gbus'=>array_values($gbus),
            'sites'=>array_values($sites),
            'projects'=>$cleanProjects,
        ];
    }


    private function supervisorHasAssignments(
        int $companyId,
        string $supervisorId
    ): bool {
        if (
            $companyId<=0
            || trim($supervisorId)===''
            || !$this->tableExists(
                'user_supervision_assignments'
            )
        ) {
            return false;
        }

        $stmt=$this->pdo->prepare(
            'SELECT COUNT(*)
             FROM user_supervision_assignments
             WHERE id_company=:id_company
               AND id_supervisor_users=:id_supervisor
               AND state=1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_supervisor'=>$supervisorId,
        ]);

        return (int)$stmt->fetchColumn()>0;
    }

    private function searchJefaturaByCompanyScope(
        int $companyId,
        string $gbu,
        string $site,
        ?int $projectId,
        string $query,
        int $limit,
        array $managedLevels
    ): array {
        $limit=max(1,min(100,$limit));

        if ($gbu==='' && $site==='' && !$projectId) {
            return [];
        }

        /*
         * PASO 1:
         * Obtener únicamente los usuarios asociados al alcance operacional.
         *
         * No se mezclan roles, credenciales ni supervisión en esta consulta.
         * Así una relación auxiliar no puede convertir una búsqueda válida
         * en cero resultados.
         */
        $scopeWhere=[
            'u_scope.id_company=:scope_company_users',
            'w_scope.id_company=:scope_company_workers',
            'p_scope.id_company=:scope_company_projects',
            'p_scope.state=1',
        ];

        $scopeParams=[
            'scope_company_users'=>$companyId,
            'scope_company_workers'=>$companyId,
            'scope_company_projects'=>$companyId,
        ];

        if ($projectId) {
            $scopeWhere[]='p_scope.id_project=:scope_selected_project';
            $scopeParams['scope_selected_project']=$projectId;
        } elseif ($site!=='') {
            $scopeWhere[]='LOWER(TRIM(p_scope.site_name))
                =LOWER(TRIM(:scope_selected_site))';
            $scopeParams['scope_selected_site']=$site;

            if ($gbu!=='') {
                $scopeWhere[]='LOWER(TRIM(p_scope.gbu_code))
                    =LOWER(TRIM(:scope_selected_gbu_site))';
                $scopeParams['scope_selected_gbu_site']=$gbu;
            }
        } else {
            $scopeWhere[]='LOWER(TRIM(p_scope.gbu_code))
                =LOWER(TRIM(:scope_selected_gbu))';
            $scopeParams['scope_selected_gbu']=$gbu;
        }

        $query=trim($query);

        if ($query!=='') {
            $like='%'.$query.'%';

            $scopeWhere[]='(
                u_scope.id_users LIKE :scope_q_user
                OR u_scope.name LIKE :scope_q_name
                OR u_scope.lastname LIKE :scope_q_lastname
                OR CONCAT_WS(\' \',u_scope.name,u_scope.lastname)
                    LIKE :scope_q_fullname
                OR u_scope.rut LIKE :scope_q_rut
                OR w_scope.name LIKE :scope_q_worker_name
                OR w_scope.lastname LIKE :scope_q_worker_lastname
                OR w_scope.rut LIKE :scope_q_worker_rut
            )';

            $scopeParams['scope_q_user']=$like;
            $scopeParams['scope_q_name']=$like;
            $scopeParams['scope_q_lastname']=$like;
            $scopeParams['scope_q_fullname']=$like;
            $scopeParams['scope_q_rut']=$like;
            $scopeParams['scope_q_worker_name']=$like;
            $scopeParams['scope_q_worker_lastname']=$like;
            $scopeParams['scope_q_worker_rut']=$like;
        }

        $scopeSql='SELECT DISTINCT
                u_scope.id_users
             FROM users u_scope
             INNER JOIN workers w_scope
               ON w_scope.id_company=:scope_company_worker_join
              AND (
                  w_scope.id_worker=u_scope.id_worker
                  OR (
                      (u_scope.id_worker IS NULL OR u_scope.id_worker=0)
                      AND w_scope.email IS NOT NULL
                      AND TRIM(w_scope.email)<>\'\'
                      AND LOWER(TRIM(w_scope.email))
                          =LOWER(TRIM(u_scope.id_users))
                  )
                  OR (
                      (u_scope.id_worker IS NULL OR u_scope.id_worker=0)
                      AND u_scope.rut IS NOT NULL
                      AND TRIM(u_scope.rut)<>\'\'
                      AND LOWER(TRIM(w_scope.rut))
                          =LOWER(TRIM(u_scope.rut))
                  )
              )
             INNER JOIN worker_projects wp_scope
               ON wp_scope.id_worker=w_scope.id_worker
             INNER JOIN projects p_scope
               ON p_scope.id_project=wp_scope.id_project
             WHERE ' . implode(' AND ',$scopeWhere) . '
             ORDER BY u_scope.id_users
             LIMIT ' . $limit;

        $scopeParams['scope_company_worker_join']=$companyId;

        $stmt=$this->pdo->prepare($scopeSql);
        $stmt->execute($scopeParams);

        $userIds=array_values(array_filter(array_map(
            static fn(array $row): string =>
                trim((string)($row['id_users'] ?? '')),
            $stmt->fetchAll() ?: []
        )));

        if (!$userIds) {
            return [];
        }

        /*
         * PASO 2:
         * Recuperar datos del usuario, roles y presentación.
         * Cada id usa placeholder único para prepares nativos MariaDB.
         */
        $idPlaceholders=[];
        $params=[
            'detail_company'=>$companyId,
        ];

        foreach ($userIds as $index=>$userId) {
            $key='detail_user_'.$index;
            $idPlaceholders[]=':'.$key;
            $params[$key]=$userId;
        }

        $levelSql=
            SctAdminUserDirectoryPolicy::roleLevelSql('rg');

        $sql='SELECT
                u.id_users,
                COALESCE(u.name,\'\') AS name,
                COALESCE(u.lastname,\'\') AS lastname,
                COALESCE(u.rut,\'\') AS rut,
                u.state,
                u.id_worker,
                u.last_access,

                COALESCE(
                    MAX(uc.credential_status),
                    \'pending_activation\'
                ) AS credential_status,

                MIN(' . $levelSql . ') AS access_level,

                GROUP_CONCAT(
                    DISTINCT rg.name
                    ORDER BY rg.name
                    SEPARATOR \', \'
                ) AS role_names

             FROM users u

             LEFT JOIN user_credentials uc
               ON uc.id_users=u.id_users

             LEFT JOIN users_role ur
               ON ur.id_users=u.id_users
              AND ur.state=1

             LEFT JOIN users_role_group rg
               ON rg.id_role_group=ur.id_role_group
              AND rg.state=1

             WHERE u.id_company=:detail_company
               AND u.id_users IN (' . implode(',',$idPlaceholders) . ')

             GROUP BY
                u.id_users,
                u.name,
                u.lastname,
                u.rut,
                u.state,
                u.id_worker,
                u.last_access

             HAVING access_level IN (' . implode(
                 ',',
                 array_map('intval',$managedLevels)
             ) . ')

             ORDER BY
                access_level,
                name,
                lastname,
                u.id_users';

        $stmt=$this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows=$stmt->fetchAll() ?: [];

        /*
         * PASO 3:
         * Presentación de alcance. Se resuelve por separado para que nunca
         * condicione la inclusión del usuario en los resultados.
         */
        foreach ($rows as &$row) {
            $row['gbu']=$gbu;
            $row['site']=$site;
            $row['projects']='';
            $row['jefaturas']='';

            if ($projectId) {
                $stmtProject=$this->pdo->prepare(
                    'SELECT name
                     FROM projects
                     WHERE id_company=:project_company
                       AND id_project=:project_id
                     LIMIT 1'
                );
                $stmtProject->execute([
                    'project_company'=>$companyId,
                    'project_id'=>$projectId,
                ]);
                $row['projects']=(string)(
                    $stmtProject->fetchColumn() ?: ''
                );
            }

            if (
                !empty($row['id_worker'])
                && $this->tableExists(
                    'user_supervision_assignments'
                )
            ) {
                $stmtSupervisor=$this->pdo->prepare(
                    'SELECT GROUP_CONCAT(
                        DISTINCT CONCAT_WS(
                            \' \',
                            sj.name,
                            sj.lastname
                        )
                        ORDER BY sj.name,sj.lastname
                        SEPARATOR \', \'
                     )
                     FROM user_supervision_assignments usa
                     INNER JOIN users sj
                       ON sj.id_users=usa.id_supervisor_users
                      AND sj.id_company=usa.id_company
                     WHERE usa.id_company=:supervisor_company
                       AND usa.id_worker=:supervisor_worker
                       AND usa.state=1'
                );
                $stmtSupervisor->execute([
                    'supervisor_company'=>$companyId,
                    'supervisor_worker'=>(int)$row['id_worker'],
                ]);

                $row['jefaturas']=(string)(
                    $stmtSupervisor->fetchColumn() ?: ''
                );
            }

            $level=isset($row['access_level'])
                && $row['access_level']!==null
                ? (int)$row['access_level']
                : null;

            $row['access_level']=$level;
            $row['role_label']=
                SctAdminUserDirectoryPolicy::displayLabel($level);
        }
        unset($row);

        return $rows;
    }

    public function search(
        int $companyId,
        string $gbu='',
        string $site='',
        ?int $projectId=null,
        string $query='',
        int $limit=60,
        ?string $supervisorId=null,
        array $managedLevels=SctAdminUserDirectoryPolicy::MANAGED_LEVELS
    ): array {
        if ($companyId <= 0) return [];
        $limit = max(1, min(100, $limit));

        if ($supervisorId!==null && trim($supervisorId)!=='') {
            return $this->searchJefaturaByCompanyScope(
                $companyId,
                $gbu,
                $site,
                $projectId,
                $query,
                $limit,
                $managedLevels
            );
        }

        /*
         * Administrador mantiene la exigencia de alcance desde el endpoint.
         * Jefatura puede buscar con "Todos"; supervisorId limita siempre los
         * resultados a trabajadores bajo su responsabilidad.
         */
        if (
            $gbu===''
            && $site===''
            && !$projectId
            && !$supervisorId
        ) {
            return [];
        }

        $where = ['u.id_company=:id_company'];
        $params = ['id_company'=>$companyId];

        /*
         * E3 VS2 — Jefatura:
         * la búsqueda se resuelve por GBU / Sitio / Proyecto.
         * Las asignaciones de supervisión no deben vaciar el buscador.
         * Las acciones de gestión continúan protegidas por sus endpoints.
         */

        /*
         * E3 VS2 — búsqueda robusta por alcance.
         *
         * Se usan EXISTS independientes para no depender de cómo se agrupen
         * worker_projects / user_profile_details en la consulta principal.
         * Basta seleccionar GBU, Sitio o Proyecto para obtener coincidencias
         * de la misma empresa.
         */
        if ($projectId) {
            $where[]='(
                EXISTS (
                    SELECT 1
                    FROM worker_projects wp_scope
                    INNER JOIN projects p_scope
                      ON p_scope.id_project=wp_scope.id_project
                     AND p_scope.id_company=u.id_company
                     AND p_scope.state=1
                    WHERE wp_scope.id_worker=u.id_worker
                      AND p_scope.id_project=:scope_project_worker
                )
                OR EXISTS (
                    SELECT 1
                    FROM user_profile_details upd_project
                    WHERE upd_project.id_users=u.id_users
                      AND upd_project.id_company=u.id_company
                      AND upd_project.confirmed_project_id=:scope_project_profile
                )
            )';

            $params['scope_project_worker']=$projectId;
            $params['scope_project_profile']=$projectId;

        } elseif ($site!=='') {
            if ($gbu!=='') {
                $where[]='(
                    EXISTS (
                        SELECT 1
                        FROM worker_projects wp_scope
                        INNER JOIN projects p_scope
                          ON p_scope.id_project=wp_scope.id_project
                         AND p_scope.id_company=u.id_company
                         AND p_scope.state=1
                        WHERE wp_scope.id_worker=u.id_worker
                          AND LOWER(TRIM(p_scope.site_name))
                              =LOWER(TRIM(:scope_site_worker))
                          AND LOWER(TRIM(p_scope.gbu_code))
                              =LOWER(TRIM(:scope_gbu_worker))
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM user_profile_details upd_direct
                        LEFT JOIN projects p_profile
                          ON p_profile.id_project=upd_direct.confirmed_project_id
                         AND p_profile.id_company=u.id_company
                         AND p_profile.state=1
                        WHERE upd_direct.id_users=u.id_users
                          AND upd_direct.id_company=u.id_company
                          AND (
                              LOWER(TRIM(COALESCE(
                                  NULLIF(upd_direct.site_name,\'\'),
                                  p_profile.site_name
                              )))=LOWER(TRIM(:scope_site_profile))
                          )
                          AND (
                              LOWER(TRIM(COALESCE(
                                  NULLIF(upd_direct.gbu_code,\'\'),
                                  p_profile.gbu_code
                              )))=LOWER(TRIM(:scope_gbu_profile))
                          )
                    )
                )';

                $params['scope_site_worker']=$site;
                $params['scope_gbu_worker']=$gbu;
                $params['scope_site_profile']=$site;
                $params['scope_gbu_profile']=$gbu;
            } else {
                $where[]='(
                    EXISTS (
                        SELECT 1
                        FROM worker_projects wp_scope
                        INNER JOIN projects p_scope
                          ON p_scope.id_project=wp_scope.id_project
                         AND p_scope.id_company=u.id_company
                         AND p_scope.state=1
                        WHERE wp_scope.id_worker=u.id_worker
                          AND LOWER(TRIM(p_scope.site_name))
                              =LOWER(TRIM(:scope_site_worker))
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM user_profile_details upd_direct
                        LEFT JOIN projects p_profile
                          ON p_profile.id_project=upd_direct.confirmed_project_id
                         AND p_profile.id_company=u.id_company
                         AND p_profile.state=1
                        WHERE upd_direct.id_users=u.id_users
                          AND upd_direct.id_company=u.id_company
                          AND LOWER(TRIM(COALESCE(
                              NULLIF(upd_direct.site_name,\'\'),
                              p_profile.site_name
                          )))=LOWER(TRIM(:scope_site_profile))
                    )
                )';

                $params['scope_site_worker']=$site;
                $params['scope_site_profile']=$site;
            }

        } elseif ($gbu!=='') {
            $where[]='(
                EXISTS (
                    SELECT 1
                    FROM worker_projects wp_scope
                    INNER JOIN projects p_scope
                      ON p_scope.id_project=wp_scope.id_project
                     AND p_scope.id_company=u.id_company
                     AND p_scope.state=1
                    WHERE wp_scope.id_worker=u.id_worker
                      AND LOWER(TRIM(p_scope.gbu_code))
                          =LOWER(TRIM(:scope_gbu_worker))
                )
                OR EXISTS (
                    SELECT 1
                    FROM user_profile_details upd_direct
                    LEFT JOIN projects p_profile
                      ON p_profile.id_project=upd_direct.confirmed_project_id
                     AND p_profile.id_company=u.id_company
                     AND p_profile.state=1
                    WHERE upd_direct.id_users=u.id_users
                      AND upd_direct.id_company=u.id_company
                      AND LOWER(TRIM(COALESCE(
                          NULLIF(upd_direct.gbu_code,\'\'),
                          p_profile.gbu_code
                      )))=LOWER(TRIM(:scope_gbu_profile))
                )
            )';

            $params['scope_gbu_worker']=$gbu;
            $params['scope_gbu_profile']=$gbu;
        }

        $query=trim($query);
        if ($query!=='') {
            $like='%' . $query . '%';

            $where[] = '(
                u.id_users LIKE :q_user
                OR u.name LIKE :q_user_name
                OR u.lastname LIKE :q_user_lastname
                OR CONCAT_WS(\' \',u.name,u.lastname) LIKE :q_user_fullname
                OR u.rut LIKE :q_user_rut
                OR w.name LIKE :q_worker_name
                OR w.lastname LIKE :q_worker_lastname
                OR w.rut LIKE :q_worker_rut
            )';

            $params['q_user']=$like;
            $params['q_user_name']=$like;
            $params['q_user_lastname']=$like;
            $params['q_user_fullname']=$like;
            $params['q_user_rut']=$like;
            $params['q_worker_name']=$like;
            $params['q_worker_lastname']=$like;
            $params['q_worker_rut']=$like;
        }

        $levelSql=SctAdminUserDirectoryPolicy::roleLevelSql('rg');
        $supervisionReady=$this->tableExists(
            'user_supervision_assignments'
        );

        $jefaturaSelect=$supervisionReady
            ? "GROUP_CONCAT(
                    DISTINCT CONCAT_WS(' ',sj.name,sj.lastname)
                    ORDER BY sj.name,sj.lastname
                    SEPARATOR ', '
               ) AS jefaturas"
            : "'' AS jefaturas";

        $supervisionJoin=$supervisionReady
            ? "LEFT JOIN user_supervision_assignments usa
                 ON usa.id_company=u.id_company
                AND usa.id_worker=u.id_worker
                AND usa.state=1
               LEFT JOIN users sj
                 ON sj.id_users=usa.id_supervisor_users
                AND sj.id_company=u.id_company"
            : '';

        $sql='SELECT
                u.id_users,
                COALESCE(NULLIF(u.name,\'\'),w.name,\'\') AS name,
                COALESCE(NULLIF(u.lastname,\'\'),w.lastname,\'\') AS lastname,
                COALESCE(NULLIF(u.rut,\'\'),w.rut,\'\') AS rut,
                u.state,
                u.id_worker,
                u.last_access,
                COALESCE(
                    MAX(uc.credential_status),
                    \'pending_activation\'
                ) AS credential_status,

                COALESCE(
                    MAX(pp.id_project),
                    MAX(pw.id_project)
                ) AS id_project,

                COALESCE(
                    NULLIF(
                        GROUP_CONCAT(
                            DISTINCT NULLIF(
                                TRIM(
                                    COALESCE(
                                        NULLIF(upd_scope.gbu_code,\'\'),
                                        pp.gbu_code
                                    )
                                ),
                                \'\'
                            )
                            ORDER BY COALESCE(
                                NULLIF(upd_scope.gbu_code,\'\'),
                                pp.gbu_code
                            )
                            SEPARATOR \', \'
                        ),
                        \'\'
                    ),
                    GROUP_CONCAT(
                        DISTINCT NULLIF(TRIM(pw.gbu_code),\'\')
                        ORDER BY pw.gbu_code
                        SEPARATOR \', \'
                    )
                ) AS gbu,

                COALESCE(
                    NULLIF(
                        GROUP_CONCAT(
                            DISTINCT NULLIF(
                                TRIM(
                                    COALESCE(
                                        NULLIF(upd_scope.site_name,\'\'),
                                        pp.site_name
                                    )
                                ),
                                \'\'
                            )
                            ORDER BY COALESCE(
                                NULLIF(upd_scope.site_name,\'\'),
                                pp.site_name
                            )
                            SEPARATOR \', \'
                        ),
                        \'\'
                    ),
                    GROUP_CONCAT(
                        DISTINCT NULLIF(TRIM(pw.site_name),\'\')
                        ORDER BY pw.site_name
                        SEPARATOR \', \'
                    )
                ) AS site,

                COALESCE(
                    NULLIF(
                        GROUP_CONCAT(
                            DISTINCT NULLIF(TRIM(pp.name),\'\')
                            ORDER BY pp.name
                            SEPARATOR \', \'
                        ),
                        \'\'
                    ),
                    GROUP_CONCAT(
                        DISTINCT NULLIF(TRIM(pw.name),\'\')
                        ORDER BY pw.name
                        SEPARATOR \', \'
                    )
                ) AS projects,

                MIN(' . $levelSql . ') AS access_level,

                GROUP_CONCAT(
                    DISTINCT CASE
                        WHEN ur.state=1 AND rg.state=1
                        THEN rg.name
                    END
                    ORDER BY rg.name
                    SEPARATOR \', \'
                ) AS role_names,

                ' . $jefaturaSelect . '

             FROM users u

             LEFT JOIN workers w
               ON w.id_worker=u.id_worker
              AND w.id_company=u.id_company

             LEFT JOIN user_credentials uc
               ON uc.id_users=u.id_users

             LEFT JOIN worker_projects wp
               ON wp.id_worker=w.id_worker

             LEFT JOIN projects pw
               ON pw.id_project=wp.id_project
              AND pw.id_company=u.id_company
              AND pw.state=1

             LEFT JOIN user_profile_details upd_scope
               ON upd_scope.id_users=u.id_users
              AND upd_scope.id_company=u.id_company

             LEFT JOIN projects pp
               ON pp.id_project=upd_scope.confirmed_project_id
              AND pp.id_company=u.id_company
              AND pp.state=1

             ' . $supervisionJoin . '

             LEFT JOIN users_role ur
               ON ur.id_users=u.id_users
              AND ur.state=1

             LEFT JOIN users_role_group rg
               ON rg.id_role_group=ur.id_role_group
              AND rg.state=1
              AND rg.id_company=u.id_company

             WHERE ' . implode(' AND ',$where) . '

             GROUP BY
                u.id_users,
                u.name,
                u.lastname,
                u.rut,
                u.state,
                u.id_worker,
                u.last_access,
                w.name,
                w.lastname,
                w.rut

             HAVING access_level IN (' . implode(
                 ',',
                 array_map('intval',$managedLevels)
             ) . ')

             ORDER BY
                access_level,
                name,
                lastname,
                u.id_users

             LIMIT ' . $limit;

        $stmt=$this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map(static function(array $row): array {
            $level=isset($row['access_level'])
                ? (int)$row['access_level']
                : null;

            $row['access_level']=$level;
            $row['role_label']=
                SctAdminUserDirectoryPolicy::displayLabel($level);

            return $row;
        },$stmt->fetchAll() ?: []);
    }


    public function assignedUserIdsForSupervisor(
        int $companyId,
        string $supervisorId,
        array $userIds
    ): array {
        $supervisorId=trim($supervisorId);

        $userIds=array_values(array_unique(array_filter(
            array_map(
                static fn($value): string => trim((string)$value),
                $userIds
            ),
            static fn(string $value): bool => $value!==''
        )));

        if (
            $companyId<=0
            || $supervisorId===''
            || !$userIds
        ) {
            return [];
        }

        $placeholders=[];
        $params=[
            'assigned_user_company'=>$companyId,
            'assigned_user_supervisor'=>$supervisorId,
            'assigned_worker_company'=>$companyId,
        ];

        foreach ($userIds as $index=>$userId) {
            $key='assigned_user_'.$index;
            $placeholders[]=':'.$key;
            $params[$key]=$userId;
        }

        /*
         * Restricción efectiva de Jefatura.
         *
         * El usuario puede estar enlazado al worker por:
         *  1. users.id_worker (actual);
         *  2. workers.email = users.id_users (histórico);
         *  3. workers.rut = users.rut (histórico).
         *
         * Así usamos la misma población que Administrador y sólo después
         * aplicamos la responsabilidad efectiva de Jefatura.
         */
        $stmt=$this->pdo->prepare(
            'SELECT DISTINCT u.id_users
             FROM users u
             INNER JOIN workers w
               ON w.id_company=:assigned_worker_company
              AND (
                  w.id_worker=u.id_worker
                  OR (
                      (u.id_worker IS NULL OR u.id_worker=0)
                      AND w.email IS NOT NULL
                      AND TRIM(w.email)<>\'\'
                      AND LOWER(TRIM(w.email))
                          =LOWER(TRIM(u.id_users))
                  )
                  OR (
                      (u.id_worker IS NULL OR u.id_worker=0)
                      AND u.rut IS NOT NULL
                      AND TRIM(u.rut)<>\'\'
                      AND LOWER(TRIM(w.rut))
                          =LOWER(TRIM(u.rut))
                  )
              )
             INNER JOIN user_supervision_assignments usa
               ON usa.id_company=u.id_company
              AND usa.id_worker=w.id_worker
              AND usa.state=1
             WHERE u.id_company=:assigned_user_company
               AND LOWER(TRIM(usa.id_supervisor_users))
                   =LOWER(TRIM(:assigned_user_supervisor))
               AND u.id_users IN (' . implode(',',$placeholders) . ')'
        );

        $stmt->execute($params);

        return array_values(array_map(
            static fn($value): string => (string)$value,
            $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []
        ));
    }


    public function assignedWorkerIdsForSupervisor(
        int $companyId,
        string $supervisorId,
        array $workerIds
    ): array {
        $supervisorId=trim($supervisorId);

        $workerIds=array_values(array_unique(array_filter(
            array_map('intval',$workerIds),
            static fn(int $id): bool => $id>0
        )));

        if (
            $companyId<=0
            || $supervisorId===''
            || !$workerIds
        ) {
            return [];
        }

        $placeholders=[];
        $params=[
            'assigned_company'=>$companyId,
            'assigned_supervisor'=>$supervisorId,
        ];

        foreach ($workerIds as $index=>$workerId) {
            $key='assigned_worker_'.$index;
            $placeholders[]=':'.$key;
            $params[$key]=$workerId;
        }

        /*
         * No usar tableExists()/DATABASE() aquí.
         * La tabla fue oficializada por la migración canónica E3 VS2 #19.
         * Si existiera un problema real de esquema debe aparecer como error
         * SQL y no como un falso "sin resultados".
         */
        $stmt=$this->pdo->prepare(
            'SELECT DISTINCT id_worker
             FROM user_supervision_assignments
             WHERE id_company=:assigned_company
               AND LOWER(TRIM(id_supervisor_users))
                   =LOWER(TRIM(:assigned_supervisor))
               AND state=1
               AND id_worker IN (' . implode(',',$placeholders) . ')'
        );
        $stmt->execute($params);

        return array_values(array_map(
            'intval',
            $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []
        ));
    }

    public function isWorkerAssignedToSupervisor(
        int $companyId,
        int $workerId,
        string $supervisorId
    ): bool {
        if (
            $companyId<=0
            || $workerId<=0
            || trim($supervisorId)===''
        ) {
            return false;
        }

        return in_array(
            $workerId,
            $this->assignedWorkerIdsForSupervisor(
                $companyId,
                $supervisorId,
                [$workerId]
            ),
            true
        );
    }

    public function profile(string $userId, int $companyId): ?array
    {
        /*
         * Lectura desacoplada para la edición administrativa.
         * Evita que una columna opcional agregada por migraciones posteriores
         * haga caer todo perfil-completo.php con error SQL 500.
         */
        $mutualSelect=$this->columnExists('users','mutual_code')
            ? 'u.mutual_code'
            : 'NULL AS mutual_code';

        $stmt=$this->pdo->prepare(
            'SELECT
                u.id_users,
                u.id_company,
                u.id_worker,
                u.name,
                u.lastname,
                u.rut,
                u.language,
                u.state,
                ' . $mutualSelect . ',
                u.profile_photo_path,
                w.phone,
                w.position,
                w.photo_path AS worker_photo_path,
                c.razon_social
             FROM users u
             INNER JOIN company c
               ON c.id_company=u.id_company
             LEFT JOIN workers w
               ON w.id_worker=u.id_worker
              AND w.id_company=u.id_company
             WHERE u.id_users=:id_users
               AND u.id_company=:id_company
             LIMIT 1'
        );
        $stmt->execute([
            'id_users'=>$userId,
            'id_company'=>$companyId,
        ]);

        $row=$stmt->fetch();
        if (!$row) return null;

        $levelSql=SctAdminUserDirectoryPolicy::roleLevelSql('rg');
        $stmt=$this->pdo->prepare(
            'SELECT
                MIN(' . $levelSql . ') AS access_level,
                GROUP_CONCAT(
                    DISTINCT rg.name
                    ORDER BY rg.name
                    SEPARATOR \", \"
                ) AS role_names
             FROM users_role ur
             INNER JOIN users_role_group rg
               ON rg.id_role_group=ur.id_role_group
              AND rg.id_company=:role_company
              AND rg.state=1
             WHERE ur.id_users=:role_user
               AND ur.state=1'
        );
        $stmt->execute([
            'role_company'=>$companyId,
            'role_user'=>$userId,
        ]);
        $role=$stmt->fetch() ?: [];

        $level=isset($role['access_level'])
            && $role['access_level']!==null
            ? (int)$role['access_level']
            : null;
        $row['access_level']=$level;
        $row['role_names']=(string)($role['role_names'] ?? '');
        $row['role_label']=SctAdminUserDirectoryPolicy::displayLabel($level);

        $detailDefaults=[
            'confirmed_project_id'=>null,
            'hired_by_contractor'=>0,
            'id_contractor_company'=>null,
            'years_experience_current_role'=>null,
            'emergency_contact_name'=>null,
            'emergency_contact_phone'=>null,
            'emergency_contact_relation'=>null,
        ];
        $row=array_merge($row,$detailDefaults);

        if ($this->tableExists('user_profile_details')) {
            $detailColumns=[];
            foreach (array_keys($detailDefaults) as $column) {
                if ($this->columnExists('user_profile_details',$column)) {
                    $detailColumns[]=$column;
                }
            }

            if ($detailColumns) {
                $stmt=$this->pdo->prepare(
                    'SELECT ' . implode(',',$detailColumns) . '
                     FROM user_profile_details
                     WHERE id_users=:detail_user
                     LIMIT 1'
                );
                $stmt->execute(['detail_user'=>$userId]);
                $details=$stmt->fetch() ?: [];
                foreach ($detailColumns as $column) {
                    if (array_key_exists($column,$details)) {
                        $row[$column]=$details[$column];
                    }
                }
            }
        }

        $row['gbu_code']='';
        $row['site_name']='';
        $row['project_name']='';

        $projectId=(int)($row['confirmed_project_id'] ?? 0);
        if ($projectId>0) {
            $stmt=$this->pdo->prepare(
                'SELECT gbu_code,site_name,name
                 FROM projects
                 WHERE id_project=:project_id
                   AND id_company=:project_company
                 LIMIT 1'
            );
            $stmt->execute([
                'project_id'=>$projectId,
                'project_company'=>$companyId,
            ]);
            $project=$stmt->fetch() ?: [];
            $row['gbu_code']=(string)($project['gbu_code'] ?? '');
            $row['site_name']=(string)($project['site_name'] ?? '');
            $row['project_name']=(string)($project['name'] ?? '');
        }

        return $row;
    }


    public function positions(int $companyId): array
    {
        $stmt=$this->pdo->prepare(
            'SELECT DISTINCT TRIM(position) AS position
             FROM workers
             WHERE id_company=:id_company
               AND state=1
               AND position IS NOT NULL
               AND TRIM(position)<>\'\'
             ORDER BY position'
        );
        $stmt->execute(['id_company'=>$companyId]);

        return array_values(array_map(
            static fn(array $row): string => (string)$row['position'],
            $stmt->fetchAll() ?: []
        ));
    }

    public function projects(int $companyId): array
    {
        return $this->filters($companyId)['projects'];
    }

    public function contractors(int $companyId): array
    {
        try {
            $stmt=$this->pdo->prepare(
                'SELECT id_contractor_company,rut,business_name,trade_name
                 FROM contractor_companies
                 WHERE id_company=:id_company
                   AND state=1
                 ORDER BY business_name'
            );
            $stmt->execute(['id_company'=>$companyId]);
            return $stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    public function projectInCompany(int $companyId, int $projectId): ?array
    {
        $stmt=$this->pdo->prepare(
            'SELECT id_project,name,gbu_code,site_name
             FROM projects
             WHERE id_company=:id_company
               AND id_project=:id_project
               AND state=1
             LIMIT 1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_project'=>$projectId,
        ]);
        $row=$stmt->fetch();
        return $row ?: null;
    }

    public function contractorInCompany(int $companyId, int $contractorId): bool
    {
        if ($contractorId <= 0) return false;
        $stmt=$this->pdo->prepare(
            'SELECT 1
             FROM contractor_companies
             WHERE id_company=:id_company
               AND id_contractor_company=:id
               AND state=1
             LIMIT 1'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id'=>$contractorId,
        ]);
        return (bool)$stmt->fetchColumn();
    }

    public function saveProfile(string $userId, int $companyId, array $data, string $actor): void
    {
        $profile=$this->profile($userId,$companyId);
        if (!$profile) throw new RuntimeException('USER_NOT_FOUND');

        $idWorker=!empty($profile['id_worker']) ? (int)$profile['id_worker'] : null;

        $this->pdo->beginTransaction();
        try {
            if (!$idWorker) {
                $stmt=$this->pdo->prepare(
                    'INSERT INTO workers
                     (id_company,rut,name,lastname,email,phone,position,photo_path,state,created_by,date_create,last_update)
                     VALUES
                     (:id_company,:rut,:name,:lastname,:email,:phone,:position,NULL,1,:created_by,NOW(),NOW())'
                );
                $stmt->execute([
                    'id_company'=>$companyId,
                    'rut'=>$data['rut'],
                    'name'=>$data['name'],
                    'lastname'=>$data['lastname'],
                    'email'=>$userId,
                    'phone'=>$data['phone'],
                    'position'=>$data['position'],
                    'created_by'=>$actor,
                ]);
                $idWorker=(int)$this->pdo->lastInsertId();
            } else {
                $stmt=$this->pdo->prepare(
                    'UPDATE workers SET
                        rut=:rut,
                        name=:name,
                        lastname=:lastname,
                        email=:email,
                        phone=:phone,
                        position=:position,
                        last_update=NOW()
                     WHERE id_worker=:id_worker
                       AND id_company=:id_company
                     LIMIT 1'
                );
                $stmt->execute([
                    'rut'=>$data['rut'],
                    'name'=>$data['name'],
                    'lastname'=>$data['lastname'],
                    'email'=>$userId,
                    'phone'=>$data['phone'],
                    'position'=>$data['position'],
                    'id_worker'=>$idWorker,
                    'id_company'=>$companyId,
                ]);
            }

            $stmt=$this->pdo->prepare(
                'UPDATE users SET
                    id_worker=:id_worker,
                    name=:name,
                    lastname=:lastname,
                    rut=:rut,
                    language=:language,
                    mutual_code=:mutual_code,
                    last_update=NOW()
                 WHERE id_users=:id_users
                   AND id_company=:id_company
                 LIMIT 1'
            );
            $stmt->execute([
                'id_worker'=>$idWorker,
                'name'=>$data['name'],
                'lastname'=>$data['lastname'],
                'rut'=>$data['rut'],
                'language'=>$data['language'],
                'mutual_code'=>$data['mutual_code'],
                'id_users'=>$userId,
                'id_company'=>$companyId,
            ]);

            $stmt=$this->pdo->prepare(
                'INSERT INTO user_profile_details (
                    id_users,id_company,id_worker,confirmed_project_id,
                    hired_by_contractor,id_contractor_company,
                    years_experience_current_role,
                    emergency_contact_name,emergency_contact_phone,
                    emergency_contact_relation,confirmed_at,created_by,
                    date_create,last_update
                 ) VALUES (
                    :id_users,:id_company,:id_worker,:confirmed_project_id,
                    :hired_by_contractor,:id_contractor_company,
                    :years_experience_current_role,
                    :emergency_contact_name,:emergency_contact_phone,
                    :emergency_contact_relation,NOW(),:created_by,NOW(),NOW()
                 )
                 ON DUPLICATE KEY UPDATE
                    id_company=VALUES(id_company),
                    id_worker=VALUES(id_worker),
                    confirmed_project_id=VALUES(confirmed_project_id),
                    hired_by_contractor=VALUES(hired_by_contractor),
                    id_contractor_company=VALUES(id_contractor_company),
                    years_experience_current_role=VALUES(years_experience_current_role),
                    emergency_contact_name=VALUES(emergency_contact_name),
                    emergency_contact_phone=VALUES(emergency_contact_phone),
                    emergency_contact_relation=VALUES(emergency_contact_relation),
                    confirmed_at=NOW(),
                    last_update=NOW()'
            );
            $stmt->execute([
                'id_users'=>$userId,
                'id_company'=>$companyId,
                'id_worker'=>$idWorker,
                'confirmed_project_id'=>$data['confirmed_project_id'],
                'hired_by_contractor'=>$data['hired_by_contractor'] ? 1 : 0,
                'id_contractor_company'=>$data['id_contractor_company'],
                'years_experience_current_role'=>$data['years_experience_current_role'],
                'emergency_contact_name'=>$data['emergency_contact_name'],
                'emergency_contact_phone'=>$data['emergency_contact_phone'],
                'emergency_contact_relation'=>$data['emergency_contact_relation'],
                'created_by'=>$actor,
            ]);

            if (!empty($data['confirmed_project_id'])) {
                $stmt=$this->pdo->prepare(
                    'INSERT INTO worker_projects(id_worker,id_project,date_create)
                     SELECT :id_worker,:id_project,NOW()
                     WHERE NOT EXISTS (
                        SELECT 1 FROM worker_projects
                        WHERE id_worker=:check_worker
                          AND id_project=:check_project
                     )'
                );
                $stmt->execute([
                    'id_worker'=>$idWorker,
                    'id_project'=>$data['confirmed_project_id'],
                    'check_worker'=>$idWorker,
                    'check_project'=>$data['confirmed_project_id'],
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
    private function columnExists(string $table, string $column): bool
    {
        try {
            $stmt=$this->pdo->prepare(
                'SELECT COUNT(*)
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA=DATABASE()
                   AND TABLE_NAME=:table_name
                   AND COLUMN_NAME=:column_name'
            );
            $stmt->execute([
                'table_name'=>$table,
                'column_name'=>$column,
            ]);
            return (int)$stmt->fetchColumn()===1;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            $stmt=$this->pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table_name'
            );
            $stmt->execute(['table_name'=>$table]);
            return (int)$stmt->fetchColumn() === 1;
        } catch (Throwable $e) {
            return false;
        }
    }

}
