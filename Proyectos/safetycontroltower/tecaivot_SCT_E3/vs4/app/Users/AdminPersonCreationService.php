<?php
require_once __DIR__ . '/../Config/MutualityOptions.php';
require_once __DIR__ . '/../Config/ProfileOptions.php';
require_once __DIR__ . '/AdminUserScopeCatalogService.php';

final class SctAdminPersonCreationService
{
    public function __construct(
        private PDO $pdo,
        private SctAdminUserDirectoryRepository $directory,
        private SctAdminSupervisionRepository $supervision,
        private ?SctAdminManagementRepository $management=null
    ) {
        $this->management ??= new SctAdminManagementRepository($pdo);
    }

    public function options(
        int $companyId,
        string $actorRole='administrador_cliente',
        string $actorUserId=''
    ): array {
        $filters=[
            'gbus'=>[],
            'sites'=>[],
            'projects'=>[],
        ];
        $defaults=[
            'gbu'=>'',
            'site'=>'',
            'id_project'=>null,
        ];
        $jefaturas=[];
        $gerencias=[];
        $roles=[];
        $positions=[];
        $contractors=[];

        /*
         * Cada bloque se carga de forma independiente. Gestión de Usuarios ya
         * posee el catálogo GBU/Sitio/Proyecto de bienvenida.php; por lo tanto
         * un fallo de ese catálogo no debe impedir cargar jerarquías.
         */
        try {
            $scopeCatalog=
                new SctAdminUserScopeCatalogService($this->pdo);
            $filters=$scopeCatalog->activeCompanyCatalog($companyId);
        } catch (Throwable $e) {
            error_log(
                'Creation options scope: '
                . get_class($e)
                . ' - '
                . $e->getMessage()
            );
        }

        if ($actorRole==='jefatura') {
            try {
                $defaults=$this->directory->userScopeDefaults(
                    $companyId,
                    $actorUserId
                );
            } catch (Throwable $e) {
                error_log(
                    'Creation options defaults: '
                    . get_class($e)
                    . ' - '
                    . $e->getMessage()
                );
            }
        }

        try {
            // Todas las Jefaturas activas de la empresa.
            $jefaturas=$this->supervision->jefaturas($companyId);
        } catch (Throwable $e) {
            error_log(
                'Creation options jefaturas: '
                . get_class($e)
                . ' - '
                . $e->getMessage()
            );
        }

        try {
            // Todas las Gerencias activas de la empresa.
            $gerencias=$this->management->gerencias($companyId);
        } catch (Throwable $e) {
            error_log(
                'Creation options gerencias: '
                . get_class($e)
                . ' - '
                . $e->getMessage()
            );
        }

        try {
            // Jefatura sólo puede crear perfiles subordinados 5/6/7.
            $roles=$this->availableRoles(
                $companyId,
                $actorRole
            );
        } catch (Throwable $e) {
            error_log(
                'Creation options roles: '
                . get_class($e)
                . ' - '
                . $e->getMessage()
            );
        }

        try {
            $positions=$this->directory->positions($companyId);
        } catch (Throwable $e) {
            error_log(
                'Creation options positions: '
                . $e->getMessage()
            );
        }

        try {
            $contractors=$this->directory->contractors($companyId);
        } catch (Throwable $e) {
            error_log(
                'Creation options contractors: '
                . $e->getMessage()
            );
        }

        return [
            'filters'=>$filters,
            'defaults'=>$defaults,
            'jefaturas'=>$jefaturas,
            'default_jefatura'=>$actorRole==='jefatura'
                ? $actorUserId
                : '',
            'gerencias'=>$gerencias,
            'positions'=>$positions,
            'contractors'=>$contractors,
            'roles'=>$roles,
            'catalog_counts'=>[
                'jefaturas'=>count($jefaturas),
                'gerencias'=>count($gerencias),
                'roles'=>count($roles),
            ],
        ];
    }

    public function availableRoles(
        int $companyId,
        string $actorRole='administrador_cliente'
    ): array {
        if ($actorRole!=='jefatura') {
            $levelSql=
                SctAdminUserDirectoryPolicy::roleLevelSql('rg');

            $allowedLevels=
                SctAdminUserDirectoryPolicy::managedLevelsForActorRole(
                    $actorRole
                );

            $stmt=$this->pdo->prepare(
                'SELECT
                    rg.id_role_group,
                    rg.name,
                    (' . $levelSql . ') AS access_level
                 FROM users_role_group rg
                 WHERE rg.id_company=:id_company
                   AND rg.state=1
                 HAVING access_level IN (' . implode(
                     ',',
                     $allowedLevels
                 ) . ')
                 ORDER BY access_level,rg.name'
            );
            $stmt->execute(['id_company'=>$companyId]);

            return array_map(
                static function(array $row): array {
                    $level=(int)$row['access_level'];

                    return [
                        'id_role_group'=>(int)$row['id_role_group'],
                        'name'=>(string)$row['name'],
                        'access_level'=>$level,
                        'label'=>
                            SctAdminUserDirectoryPolicy::displayLabel(
                                $level
                            ),
                    ];
                },
                $stmt->fetchAll() ?: []
            );
        }

        $stmt=$this->pdo->prepare(
            "SELECT DISTINCT
                rg.id_role_group,
                rg.name
             FROM users_role_group rg
             WHERE rg.state=1
               AND (
                   rg.id_company=:role_company
                   OR EXISTS (
                       SELECT 1
                       FROM users_role ur_scope
                       INNER JOIN users u_scope
                         ON u_scope.id_users=ur_scope.id_users
                        AND u_scope.id_company=:role_user_company
                       WHERE ur_scope.id_role_group=rg.id_role_group
                         AND ur_scope.state=1
                   )
               )
               AND (
                   LOWER(TRIM(rg.name)) LIKE '%usuario%'
                   OR LOWER(TRIM(rg.name)) LIKE '%trabajador%'
                   OR LOWER(TRIM(rg.name)) LIKE '%paramedico%'
                   OR LOWER(TRIM(rg.name)) LIKE '%paramédico%'
                   OR LOWER(TRIM(rg.name)) LIKE '%contratista%'
               )
               AND LOWER(TRIM(rg.name)) NOT LIKE '%jefatura%'
               AND LOWER(TRIM(rg.name)) NOT LIKE '%gerente%'
               AND LOWER(TRIM(rg.name)) NOT LIKE '%gerencia%'
               AND LOWER(TRIM(rg.name)) NOT LIKE '%administrador%'
               AND LOWER(TRIM(rg.name)) NOT LIKE '%superusuario%'
               AND LOWER(TRIM(rg.name)) NOT LIKE '%super user%'
             ORDER BY rg.name"
        );
        $stmt->execute([
            'role_company'=>$companyId,
            'role_user_company'=>$companyId,
        ]);

        $out=[];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $name=mb_strtolower(
                trim((string)$row['name']),
                'UTF-8'
            );

            if (
                str_contains($name,'paramedico')
                || str_contains($name,'paramédico')
            ) {
                $level=6;
            } elseif (str_contains($name,'contratista')) {
                $level=7;
            } else {
                $level=5;
            }

            $out[]=[
                'id_role_group'=>(int)$row['id_role_group'],
                'name'=>(string)$row['name'],
                'access_level'=>$level,
                'label'=>
                    SctAdminUserDirectoryPolicy::displayLabel($level),
            ];
        }

        return $out;
    }

    private function tableColumns(string $table): array
    {
        static $cache=[];

        if (array_key_exists($table,$cache)) {
            return $cache[$table];
        }

        try {
            $stmt=$this->pdo->query(
                'SHOW COLUMNS FROM `' . str_replace(
                    '`',
                    '``',
                    $table
                ) . '`'
            );

            $columns=[];

            foreach ($stmt->fetchAll() ?: [] as $row) {
                $field=(string)($row['Field'] ?? '');

                if ($field!=='') {
                    $columns[$field]=true;
                }
            }

            return $cache[$table]=$columns;
        } catch (Throwable $e) {
            error_log(
                'Creation tableColumns '
                . $table
                . ': '
                . $e->getMessage()
            );

            return $cache[$table]=[];
        }
    }

    private function tableHasColumn(
        string $table,
        string $column
    ): bool {
        return isset($this->tableColumns($table)[$column]);
    }

    private function saveOptionalProfileDetails(
        string $userId,
        int $companyId,
        int $workerId,
        array $data,
        string $actor
    ): void {
        $columns=$this->tableColumns('user_profile_details');

        if (!$columns) {
            return;
        }

        $values=[
            'id_users'=>$userId,
            'id_company'=>$companyId,
            'id_worker'=>$workerId,
            'confirmed_project_id'=>$data['id_project'],
            'gbu_code'=>$data['gbu'],
            'site_name'=>$data['site'],
            'hired_by_contractor'=>
                !empty($data['profile']['hired_by_contractor']) ? 1 : 0,
            'id_contractor_company'=>
                $data['profile']['id_contractor_company'] ?? null,
            'years_experience_current_role'=>
                $data['profile']['years_experience_current_role'] ?? null,
            'emergency_contact_name'=>
                $data['profile']['emergency_contact_name'] ?? null,
            'emergency_contact_phone'=>
                $data['profile']['emergency_contact_phone'] ?? null,
            'emergency_contact_relation'=>
                $data['profile']['emergency_contact_relation'] ?? null,
            'confirmed_at'=>'__NOW__',
            'created_by'=>$actor,
            'date_create'=>'__NOW__',
            'last_update'=>'__NOW__',
        ];

        $insertColumns=[];
        $insertValues=[];
        $params=[];

        foreach ($values as $column=>$value) {
            if (!isset($columns[$column])) {
                continue;
            }

            $insertColumns[]='`'.$column.'`';

            if ($value==='__NOW__') {
                $insertValues[]='NOW()';
                continue;
            }

            $placeholder='profile_'.$column;
            $insertValues[]=':'.$placeholder;
            $params[$placeholder]=$value;
        }

        if (
            !in_array('`id_users`',$insertColumns,true)
            || !in_array('`id_company`',$insertColumns,true)
        ) {
            return;
        }

        $stmt=$this->pdo->prepare(
            'INSERT INTO user_profile_details ('
            . implode(',',$insertColumns)
            . ') VALUES ('
            . implode(',',$insertValues)
            . ')'
        );
        $stmt->execute($params);
    }

    public function validate(array $input,int $companyId,string $actorRole='administrador_cliente',string $actorUserId=''): array
    {
        $mode=strtolower(trim((string)($input['record_type'] ?? 'worker')));
        if (!in_array($mode,['worker','user'],true)) throw new InvalidArgumentException(t('admin_users_err_record_type'));

        $name=trim((string)($input['name'] ?? ''));
        $lastname=trim((string)($input['lastname'] ?? ''));
        $rut=strtoupper(trim((string)($input['rut'] ?? '')));
        // El correo/ID sólo existe para cuentas SCT. Un trabajador
        // operacional sin acceso no debe persistir un identificador users.
        $email=$mode==='user'
            ? strtolower(trim((string)($input['email'] ?? '')))
            : '';
        $phone=trim((string)($input['phone'] ?? ''));
        $position=trim((string)($input['position'] ?? ''));
        $language=strtolower(trim((string)($input['language'] ?? 'es')));
        $gbu=trim((string)($input['gbu'] ?? ''));
        $site=trim((string)($input['site'] ?? ''));
        $projectId=filter_var($input['id_project'] ?? null,FILTER_VALIDATE_INT) ?: 0;
        $jefaturaId=trim((string)($input['id_jefatura'] ?? ''));
        $gerenciaId=trim((string)($input['id_gerencia'] ?? ''));
        $roleId=filter_var($input['id_role_group'] ?? null,FILTER_VALIDATE_INT) ?: 0;

        if ($name==='') {
            throw new InvalidArgumentException(
                t('admin_users_err_name_required')
            );
        }

        if (
            $actorRole!=='jefatura'
            && $position!==''
            && !in_array(
                $position,
                $this->directory->positions($companyId),
                true
            )
        ) {
            throw new InvalidArgumentException(
                t('admin_users_err_position')
            );
        }

        if (
            $rut!==''
            && !preg_match('/^[0-9]{7,8}-[0-9K]$/i',$rut)
        ) {
            throw new InvalidArgumentException(
                t('admin_users_err_rut')
            );
        }
        if ($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException(t('admin_users_err_email'));
        if ($mode==='user' && $email==='') throw new InvalidArgumentException(t('admin_users_err_user_email'));
        if ($phone!=='' && !preg_match('/^\+?[0-9][0-9\s().-]{6,24}$/',$phone)) throw new InvalidArgumentException(t('admin_users_err_phone'));

        if ($gbu==='') {
            throw new InvalidArgumentException(t('admin_users_err_gbu_required'));
        }
        if ($site==='') {
            throw new InvalidArgumentException(t('admin_users_err_site_required'));
        }

        $project=$this->directory->projectInCompany($companyId,$projectId);
        if (!$project) {
            throw new InvalidArgumentException(t('admin_users_err_project'));
        }

        // GBU y Sitio no se aceptan como texto libre: deben coincidir con
        // el proyecto seleccionado dentro de la empresa del Administrador.
        if (
            strcasecmp(trim((string)($project['gbu_code'] ?? '')),$gbu)!==0
            || strcasecmp(trim((string)($project['site_name'] ?? '')),$site)!==0
        ) {
            throw new InvalidArgumentException(
                t('admin_users_err_scope_mismatch')
            );
        }

        $allowedLanguages=defined('IDIOMAS_DISPONIBLES') ? IDIOMAS_DISPONIBLES : ['es','en','pt','fr','zh'];
        if (!in_array($language,$allowedLanguages,true)) throw new InvalidArgumentException(t('admin_users_err_language'));

        $profileData=null;

        // Regla de creación E3 VS2:
        // GBU + Sitio + Proyecto + Jefatura son obligatorios para toda persona.
        // Para cuenta SCT se agregan email + grupo de perfil.
        $requiresJefatura=true;
        $requiresGerencia=false;

        if ($mode==='user') {
            $role=$this->roleInCompany($companyId,$roleId,$actorRole);
            if (!$role) throw new InvalidArgumentException(t('admin_users_err_role'));

            $level=(int)$role['access_level'];

            // La Jefatura es obligatoria en la creación de toda cuenta SCT.
            // Gerencia permanece como dato organizacional opcional.
            $requiresJefatura=true;
            $requiresGerencia=$gerenciaId!=='';

            $mutual=strtolower(trim((string)($input['mutual_code'] ?? '')));
            $yearsRaw=trim((string)($input['years_experience_current_role'] ?? ''));
            $years=$yearsRaw==='' ? null : filter_var(
                $yearsRaw,
                FILTER_VALIDATE_INT,
                ['options'=>['min_range'=>0,'max_range'=>80]]
            );
            $emName=trim((string)($input['emergency_contact_name'] ?? ''));
            $emPhone=trim((string)($input['emergency_contact_phone'] ?? ''));
            $emRelation=strtolower(trim((string)($input['emergency_contact_relation'] ?? '')));
            $hired=strtolower(trim((string)($input['hired_by_contractor'] ?? 'no')));
            $contractorId=filter_var($input['id_contractor_company'] ?? null,FILTER_VALIDATE_INT) ?: 0;

            if ($mutual!=='' && !SctMutualityOptions::isValid($mutual)) {
                throw new InvalidArgumentException(t('admin_users_err_mutual'));
            }
            if ($years===false) {
                throw new InvalidArgumentException(t('admin_users_err_years'));
            }
            if (
                $emPhone!==''
                && !preg_match('/^\+?[0-9][0-9\s().-]{6,24}$/',$emPhone)
            ) {
                throw new InvalidArgumentException(
                    t('admin_users_err_emergency_phone')
                );
            }
            if (
                $emRelation!==''
                && !SctProfileOptions::isEmergencyRelation($emRelation)
            ) {
                throw new InvalidArgumentException(
                    t('admin_users_err_emergency_relation')
                );
            }
            if (!SctProfileOptions::isContractorFlag($hired)) {
                throw new InvalidArgumentException(
                    t('admin_users_err_contractor_flag')
                );
            }

            $isContractor=$hired==='yes';
            if (
                $isContractor
                && !$this->directory->contractorInCompany(
                    $companyId,
                    $contractorId
                )
            ) {
                throw new InvalidArgumentException(
                    t('admin_users_err_contractor')
                );
            }

            $profileData=[
                'id_role_group'=>$roleId,
                'access_level'=>$level,
                'mutual_code'=>$mutual!=='' ? $mutual : null,
                'years_experience_current_role'=>$years,
                'emergency_contact_name'=>$emName!=='' ? $emName : null,
                'emergency_contact_phone'=>$emPhone!=='' ? $emPhone : null,
                'emergency_contact_relation'=>$emRelation!=='' ? $emRelation : null,
                'hired_by_contractor'=>$isContractor,
                'id_contractor_company'=>$isContractor ? (int)$contractorId : null,
            ];
        }

        if ($requiresJefatura) {
            if (
                $jefaturaId===''
                || !$this->supervision->validJefatura(
                    $companyId,
                    $jefaturaId
                )
            ) {
                throw new InvalidArgumentException(
                    t('admin_users_err_jefatura')
                );
            }
        } else {
            $jefaturaId='';
        }

        if ($requiresGerencia) {
            if (
                $gerenciaId===''
                || !$this->management->validGerencia(
                    $companyId,
                    $gerenciaId
                )
            ) {
                throw new InvalidArgumentException(
                    t('admin_users_err_gerencia')
                );
            }
        } else {
            $gerenciaId='';
        }

        return [
            'record_type'=>$mode,'name'=>$name,'lastname'=>$lastname,'rut'=>$rut,
            'email'=>$email!=='' ? $email : null,'phone'=>$phone!=='' ? $phone : null,
            'position'=>$position!=='' ? $position : null,
            'language'=>$language,'gbu'=>$gbu,'site'=>$site,
            'id_project'=>$projectId,'project'=>$project,
            'id_jefatura'=>$jefaturaId,
            'requires_jefatura'=>$requiresJefatura,
            'id_gerencia'=>$gerenciaId,
            'requires_gerencia'=>$requiresGerencia,
            'profile'=>$profileData,
        ];
    }

    public function create(int $companyId,array $data,string $actor): array
    {
        $this->assertUniqueIdentity($companyId,$data['rut'],$data['email']);
        $this->pdo->beginTransaction();
        try {
            $stmt=$this->pdo->prepare('INSERT INTO workers (id_company,rut,name,lastname,email,phone,position,photo_path,state,created_by,date_create,last_update) VALUES (:id_company,:rut,:name,:lastname,:email,:phone,:position,NULL,1,:actor,NOW(),NOW())');
            $stmt->execute(['id_company'=>$companyId,'rut'=>$data['rut'],'name'=>$data['name'],'lastname'=>$data['lastname'],'email'=>$data['email'],'phone'=>$data['phone'],'position'=>$data['position'],'actor'=>$actor]);
            $workerId=(int)$this->pdo->lastInsertId();

            $stmt=$this->pdo->prepare('INSERT INTO worker_projects (id_worker,id_project,date_create) VALUES (:id_worker,:id_project,NOW())');
            $stmt->execute(['id_worker'=>$workerId,'id_project'=>$data['id_project']]);

            $userId=null;
            if ($data['record_type']==='user') {
                $userId=(string)$data['email'];
                $stmt=$this->pdo->prepare("INSERT INTO users (id_users,id_company,id_worker,name,lastname,rut,state,language,profile_photo_path,last_access,created_by,date_create,last_update,mutual_code) VALUES (:id_users,:id_company,:id_worker,:name,:lastname,:rut,1,:language,NULL,'1970-01-01 00:00:00',:actor,NOW(),NOW(),:mutual_code)");
                $stmt->execute(['id_users'=>$userId,'id_company'=>$companyId,'id_worker'=>$workerId,'name'=>$data['name'],'lastname'=>$data['lastname'],'rut'=>$data['rut'],'language'=>$data['language'],'actor'=>$actor,'mutual_code'=>$data['profile']['mutual_code']]);

                $stmt=$this->pdo->prepare("INSERT INTO user_credentials (id_users,password_hash,credential_status,password_changed_at,legacy_password_invalidated_at,created_at,updated_at) VALUES (:id_users,NULL,'pending_activation',NULL,NULL,NOW(),NOW())");
                $stmt->execute(['id_users'=>$userId]);

                $stmt=$this->pdo->prepare('INSERT INTO users_role (id_users,id_role_group,state,create_by,date_create,last_update) VALUES (:id_users,:id_role_group,1,:actor,NOW(),NOW())');
                $stmt->execute(['id_users'=>$userId,'id_role_group'=>$data['profile']['id_role_group'],'actor'=>$actor]);

                $stmt=$this->pdo->prepare('INSERT INTO user_profile_details (id_users,id_company,id_worker,confirmed_project_id,hired_by_contractor,id_contractor_company,years_experience_current_role,emergency_contact_name,emergency_contact_phone,emergency_contact_relation,confirmed_at,created_by,date_create,last_update) VALUES (:id_users,:id_company,:id_worker,:id_project,:hired_by_contractor,:id_contractor_company,:years_experience_current_role,:emergency_contact_name,:emergency_contact_phone,:emergency_contact_relation,NOW(),:actor,NOW(),NOW())');
                $stmt->execute(['id_users'=>$userId,'id_company'=>$companyId,'id_worker'=>$workerId,'id_project'=>$data['id_project'],'hired_by_contractor'=>$data['profile']['hired_by_contractor'] ? 1 : 0,'id_contractor_company'=>$data['profile']['id_contractor_company'],'years_experience_current_role'=>$data['profile']['years_experience_current_role'],'emergency_contact_name'=>$data['profile']['emergency_contact_name'],'emergency_contact_phone'=>$data['profile']['emergency_contact_phone'],'emergency_contact_relation'=>$data['profile']['emergency_contact_relation'],'actor'=>$actor]);
            }

            if (!empty($data['requires_jefatura'])) {
                $this->supervision->replacePrimary(
                    $companyId,
                    $workerId,
                    (string)$data['id_jefatura'],
                    $actor
                );
            }

            if (!empty($data['requires_gerencia'])) {
                $this->management->replacePrimary(
                    $companyId,
                    $workerId,
                    (string)$data['id_gerencia'],
                    $actor
                );
            }

            if (!empty($data['position'])) {
                SctAdminUserActionLog::write(
                    $this->pdo,
                    $actor,
                    SctAdminUserActionLog::ACTION_POSITION,
                    $workerId
                );
            }

            if ($data['record_type']==='user') {
                SctAdminUserActionLog::write(
                    $this->pdo,
                    $actor,
                    SctAdminUserActionLog::ACTION_ROLE,
                    $workerId
                );

                if (!empty($data['requires_jefatura'])) {
                    SctAdminUserActionLog::write(
                        $this->pdo,
                        $actor,
                        SctAdminUserActionLog::ACTION_JEFATURA,
                        $workerId
                    );
                }

                if (!empty($data['requires_gerencia'])) {
                    SctAdminUserActionLog::write(
                        $this->pdo,
                        $actor,
                        SctAdminUserActionLog::ACTION_GERENCIA,
                        $workerId
                    );
                }
            }

            $this->pdo->commit();
            return ['id_worker'=>$workerId,'id_users'=>$userId,'record_type'=>$data['record_type']];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }


    /**
     * Jefatura usa el mismo núcleo transaccional que Administrador Cliente.
     *
     * Diferencia operacional:
     * las relaciones Jefatura/Gerencia se escriben directamente sobre las
     * tablas canónicas E3 VS2 ya validadas por migraciones 19 y 21. La
     * autorización ya fue resuelta por validate().
     *
     * La auditoría se ejecuta después del commit para no invalidar una alta
     * correcta por un fallo auxiliar del log.
     */
    public function createForActor(
        int $companyId,
        array $data,
        string $actor,
        string $actorRole='administrador_cliente'
    ): array {
        if (
            mb_strtolower(
                trim($actorRole),
                'UTF-8'
            )!=='jefatura'
        ) {
            return $this->create(
                $companyId,
                $data,
                $actor
            );
        }

        $this->assertUniqueIdentity(
            $companyId,
            $data['rut'],
            $data['email']
        );

        $stage='worker';
        $this->pdo->beginTransaction();

        try {
            /*
             * Núcleo equivalente a Administrador.
             * Para campos opcionales del worker usamos texto vacío en vez
             * de NULL para conservar compatibilidad con esquemas históricos
             * que los definieron NOT NULL.
             */
            $stmt=$this->pdo->prepare(
                'INSERT INTO workers (
                    id_company,rut,name,lastname,email,phone,position,
                    photo_path,state,created_by,date_create,last_update
                 ) VALUES (
                    :id_company,:rut,:name,:lastname,:email,:phone,
                    :position,NULL,1,:actor,NOW(),NOW()
                 )'
            );
            $stmt->execute([
                'id_company'=>$companyId,
                'rut'=>(string)($data['rut'] ?? ''),
                'name'=>(string)$data['name'],
                'lastname'=>(string)($data['lastname'] ?? ''),
                'email'=>(string)($data['email'] ?? ''),
                'phone'=>(string)($data['phone'] ?? ''),
                'position'=>(string)($data['position'] ?? ''),
                'actor'=>$actor,
            ]);

            $workerId=(int)$this->pdo->lastInsertId();

            $stage='project';
            $stmt=$this->pdo->prepare(
                'INSERT INTO worker_projects (
                    id_worker,id_project,date_create
                 ) VALUES (
                    :id_worker,:id_project,NOW()
                 )'
            );
            $stmt->execute([
                'id_worker'=>$workerId,
                'id_project'=>$data['id_project'],
            ]);

            $userId=null;

            if ($data['record_type']==='user') {
                $userId=(string)$data['email'];

                /*
                 * Cuenta base: mismos campos estables usados por Administrador.
                 * mutual_code se aplica después sólo si la columna existe.
                 */
                $stage='user';
                $stmt=$this->pdo->prepare(
                    "INSERT INTO users (
                        id_users,id_company,id_worker,name,lastname,rut,
                        state,language,profile_photo_path,last_access,
                        created_by,date_create,last_update
                     ) VALUES (
                        :id_users,:id_company,:id_worker,:name,:lastname,
                        :rut,1,:language,NULL,'1970-01-01 00:00:00',
                        :actor,NOW(),NOW()
                     )"
                );
                $stmt->execute([
                    'id_users'=>$userId,
                    'id_company'=>$companyId,
                    'id_worker'=>$workerId,
                    'name'=>$data['name'],
                    'lastname'=>$data['lastname'],
                    'rut'=>$data['rut'],
                    'language'=>$data['language'],
                    'actor'=>$actor,
                ]);

                if (
                    $this->tableHasColumn('users','mutual_code')
                    && !empty($data['profile']['mutual_code'])
                ) {
                    try {
                        $stmt=$this->pdo->prepare(
                            'UPDATE users
                             SET mutual_code=:mutual_code
                             WHERE id_users=:mutual_user'
                        );
                        $stmt->execute([
                            'mutual_code'=>
                                $data['profile']['mutual_code'],
                            'mutual_user'=>$userId,
                        ]);
                    } catch (Throwable $optionalError) {
                        error_log(
                            'Jefatura optional mutual: '
                            . $optionalError->getMessage()
                        );
                    }
                }

                $stage='credentials';
                $stmt=$this->pdo->prepare(
                    "INSERT INTO user_credentials (
                        id_users,password_hash,credential_status,
                        password_changed_at,legacy_password_invalidated_at,
                        created_at,updated_at
                     ) VALUES (
                        :id_users,NULL,'pending_activation',
                        NULL,NULL,NOW(),NOW()
                     )"
                );
                $stmt->execute([
                    'id_users'=>$userId,
                ]);

                $stage='role';
                $stmt=$this->pdo->prepare(
                    'INSERT INTO users_role (
                        id_users,id_role_group,state,create_by,
                        date_create,last_update
                     ) VALUES (
                        :id_users,:id_role_group,1,:actor,NOW(),NOW()
                     )'
                );
                $stmt->execute([
                    'id_users'=>$userId,
                    'id_role_group'=>
                        $data['profile']['id_role_group'],
                    'actor'=>$actor,
                ]);
            }

            /*
             * Confirmamos el núcleo principal antes de datos extendidos y
             * jerarquías. Esto garantiza que trabajador/usuario no se pierda
             * por una diferencia de esquema auxiliar.
             */
            $this->pdo->commit();

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new RuntimeException(
                'CREATE_CORE_'
                . strtoupper($stage)
                . ': '
                . $e->getMessage(),
                0,
                $e
            );
        }

        $warnings=[];

        /*
         * Perfil extendido: mismo contenido funcional de Administrador, pero
         * adaptado a las columnas realmente disponibles.
         */
        if ($data['record_type']==='user') {
            try {
                $this->saveOptionalProfileDetails(
                    (string)$userId,
                    $companyId,
                    $workerId,
                    $data,
                    $actor
                );
            } catch (Throwable $e) {
                error_log(
                    'Jefatura optional profile: '
                    . $e->getMessage()
                );
                $warnings[]='profile';
            }
        }

        /*
         * Jefatura/Gerencia ya fueron validadas antes de crear.
         * Se guardan después del núcleo para no revertir una alta correcta.
         */
        if (!empty($data['requires_jefatura'])) {
            try {
                $this->supervision->replacePrimary(
                    $companyId,
                    $workerId,
                    (string)$data['id_jefatura'],
                    $actor
                );
            } catch (Throwable $e) {
                error_log(
                    'Jefatura relation after create: '
                    . $e->getMessage()
                );
                $warnings[]='jefatura';
            }
        }

        if (!empty($data['requires_gerencia'])) {
            try {
                $this->management->replacePrimary(
                    $companyId,
                    $workerId,
                    (string)$data['id_gerencia'],
                    $actor
                );
            } catch (Throwable $e) {
                error_log(
                    'Gerencia relation after create: '
                    . $e->getMessage()
                );
                $warnings[]='gerencia';
            }
        }

        $actions=[];

        if (!empty($data['position'])) {
            $actions[]=SctAdminUserActionLog::ACTION_POSITION;
        }

        if ($data['record_type']==='user') {
            $actions[]=SctAdminUserActionLog::ACTION_ROLE;
        }

        if (!empty($data['requires_jefatura'])) {
            $actions[]=SctAdminUserActionLog::ACTION_JEFATURA;
        }

        if (!empty($data['requires_gerencia'])) {
            $actions[]=SctAdminUserActionLog::ACTION_GERENCIA;
        }

        foreach ($actions as $action) {
            try {
                SctAdminUserActionLog::write(
                    $this->pdo,
                    $actor,
                    $action,
                    $workerId
                );
            } catch (Throwable $e) {
                error_log(
                    'Jefatura create audit: '
                    . $e->getMessage()
                );
            }
        }

        return [
            'id_worker'=>$workerId,
            'id_users'=>$userId,
            'record_type'=>$data['record_type'],
            'warnings'=>array_values(array_unique($warnings)),
        ];
    }

    public function changeUserRole(
        string $userId,
        int $companyId,
        int $roleId,
        string $actor,
        string $actorRole='administrador_cliente'
    ): void {
        $role=$this->roleInCompany(
            $companyId,
            $roleId,
            $actorRole
        );
        if (!$role) {
            throw new InvalidArgumentException(t('admin_users_err_role'));
        }

        $levelSql=SctAdminUserDirectoryPolicy::roleLevelSql('rg');
        $stmt=$this->pdo->prepare(
            'UPDATE users_role ur
             INNER JOIN users_role_group rg
               ON rg.id_role_group=ur.id_role_group
              AND rg.id_company=:id_company
             SET ur.state=0,ur.last_update=NOW()
             WHERE ur.id_users=:id_users
               AND (' . $levelSql . ') IN (2,3,4,5,6,7)'
        );
        $stmt->execute([
            'id_company'=>$companyId,
            'id_users'=>$userId,
        ]);

        $stmt=$this->pdo->prepare(
            'UPDATE users_role
             SET state=1,create_by=:actor,last_update=NOW()
             WHERE id_users=:id_users
               AND id_role_group=:id_role_group'
        );
        $stmt->execute([
            'id_users'=>$userId,
            'id_role_group'=>$roleId,
            'actor'=>$actor,
        ]);

        if ($stmt->rowCount()===0) {
            $stmt=$this->pdo->prepare(
                'INSERT INTO users_role (
                    id_users,id_role_group,state,create_by,
                    date_create,last_update
                 ) VALUES (
                    :id_users,:id_role_group,1,:actor,NOW(),NOW()
                 )'
            );
            $stmt->execute([
                'id_users'=>$userId,
                'id_role_group'=>$roleId,
                'actor'=>$actor,
            ]);
        }
    }

    private function roleInCompany(int $companyId,int $roleId,string $actorRole='administrador_cliente'): ?array
    {
        foreach ($this->availableRoles($companyId,$actorRole) as $role) if ((int)$role['id_role_group']===$roleId) return $role;
        return null;
    }

    private function assertUniqueIdentity(int $companyId,string $rut,?string $email): void
    {
        $conditions=[];
        $params=['id_company'=>$companyId];

        if ($rut!=='') {
            $conditions[]='UPPER(TRIM(rut))=UPPER(TRIM(:rut))';
            $params['rut']=$rut;
        }

        if ($email) {
            $conditions[]='(
                email IS NOT NULL
                AND LOWER(TRIM(email))=LOWER(TRIM(:worker_email))
            )';
            $params['worker_email']=$email;
        }

        if ($conditions) {
            $stmt=$this->pdo->prepare(
                'SELECT COUNT(*)
                 FROM workers
                 WHERE id_company=:id_company
                   AND (' . implode(' OR ',$conditions) . ')'
            );
            $stmt->execute($params);

            if ((int)$stmt->fetchColumn()>0) {
                throw new InvalidArgumentException(
                    t('admin_users_err_duplicate_worker')
                );
            }
        }

        if ($email) {
            $stmt=$this->pdo->prepare('SELECT COUNT(*) FROM users WHERE LOWER(TRIM(id_users))=LOWER(TRIM(:id_users))');
            $stmt->execute(['id_users'=>$email]);
            if ((int)$stmt->fetchColumn()>0) throw new InvalidArgumentException(t('admin_users_err_duplicate_user'));
        }
    }
}
