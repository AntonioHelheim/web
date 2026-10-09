<?php
final class SctUserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findCompany(int $companyId): ?array
    {
        $stmt=$this->pdo->prepare('SELECT id_company,razon_social,state FROM company WHERE id_company=:id_company LIMIT 1');
        $stmt->execute(['id_company'=>$companyId]);
        $row=$stmt->fetch(); return $row?:null;
    }

    public function visibleCompanies(bool $global,int $companyId): array
    {
        if ($global) {
            return $this->pdo->query('SELECT id_company,razon_social FROM company WHERE state=1 ORDER BY razon_social ASC')->fetchAll();
        }
        $stmt=$this->pdo->prepare('SELECT id_company,razon_social FROM company WHERE id_company=:id_company AND state=1 LIMIT 1');
        $stmt->execute(['id_company'=>$companyId]); return $stmt->fetchAll();
    }

    public function activeRole(int $companyId,int $roleGroupId): ?array
    {
        $stmt=$this->pdo->prepare('SELECT id_role_group,id_company,name,description FROM users_role_group WHERE id_role_group=:id_role_group AND id_company=:id_company AND state=1 LIMIT 1');
        $stmt->execute(['id_role_group'=>$roleGroupId,'id_company'=>$companyId]);
        $row=$stmt->fetch(); return $row?:null;
    }

    public function activeRoles(?int $companyId,bool $global): array
    {
        $params=[];$where=['state=1'];
        if (!$global || $companyId!==null) { $where[]='id_company=:id_company'; $params['id_company']=$companyId; }
        $stmt=$this->pdo->prepare('SELECT id_role_group,id_company,name,description FROM users_role_group WHERE '.implode(' AND ',$where).' ORDER BY id_company,name');
        $stmt->execute($params); return $stmt->fetchAll();
    }

    public function findUserWithAccess(string $userId): ?array
    {
        $stmt=$this->pdo->prepare(
            'SELECT u.id_users,u.id_company,u.id_worker,u.name,u.lastname,u.rut,u.state,u.language,u.profile_photo_path,u.last_access,u.created_by,u.date_create,u.last_update,
                    c.razon_social,
                    MAX(CASE WHEN uc.credential_status="active" AND COALESCE(uc.password_hash,"")<>"" THEN 1 ELSE 0 END) password_configured,
                    COALESCE(MAX(uc.credential_status),"pending_activation") credential_status,
                    COALESCE(NULLIF(GROUP_CONCAT(DISTINCT CASE WHEN ur.state=1 AND urg.state=1 AND urg.id_company=u.id_company THEN urg.name END ORDER BY urg.name SEPARATOR ", "),""),"Sin rol") role_name,
                    MIN(CASE WHEN ur.state=1 AND urg.state=1 AND urg.id_company=u.id_company THEN ur.id_role_group ELSE NULL END) primary_role_id
             FROM users u INNER JOIN company c ON c.id_company=u.id_company
             LEFT JOIN user_credentials uc ON uc.id_users=u.id_users
             LEFT JOIN users_role ur ON ur.id_users=u.id_users
             LEFT JOIN users_role_group urg ON urg.id_role_group=ur.id_role_group
             WHERE u.id_users=:id_users
             GROUP BY u.id_users,u.id_company,u.id_worker,u.name,u.lastname,u.rut,u.state,u.language,u.profile_photo_path,u.last_access,u.created_by,u.date_create,u.last_update,c.razon_social
             LIMIT 1'
        );
        $stmt->execute(['id_users'=>$userId]); $row=$stmt->fetch(); return $row?:null;
    }

    public function updateSelf(string $userId,string $name,string $lastname,string $language): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET name=:name,lastname=:lastname,language=:language,last_update=NOW() WHERE id_users=:id_users LIMIT 1');
        $stmt->execute(compact('name','lastname','language')+['id_users'=>$userId]);
    }

    public function listRoleGroupsForCompany(int $companyId): array
    {
        $stmt=$this->pdo->prepare('SELECT id_role_group,id_company,name,description FROM users_role_group WHERE id_company=:id_company AND state=1 ORDER BY name');
        $stmt->execute(['id_company'=>$companyId]); return $stmt->fetchAll();
    }


    public function supervisionCandidates(int $companyId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                u.id_users,
                u.name,
                u.lastname,
                g.name AS role_name
             FROM users u
             INNER JOIN users_role ur
                ON ur.id_users=u.id_users
               AND ur.state=1
             INNER JOIN users_role_group g
                ON g.id_role_group=ur.id_role_group
               AND g.id_company=u.id_company
               AND g.state=1
             WHERE u.id_company=:id_company
               AND u.state=1
               AND LOWER(g.name) IN ('jefatura','paramedico','contratista')
             ORDER BY
                FIELD(LOWER(g.name),'jefatura','paramedico','contratista'),
                u.lastname,
                u.name,
                u.id_users"
        );
        $stmt->execute(['id_company'=>$companyId]);

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $row['role_name'] = SctRolePolicy::canonical(
                (string)$row['role_name']
            );
            $rows[] = $row;
        }
        return $rows;
    }

    public function assignedSupervisors(
        string $targetUserId,
        int $companyId
    ): array {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT DISTINCT
                    a.supervisor_user_id,
                    u.name,
                    u.lastname,
                    g.name AS role_name
                 FROM user_supervision_assignments a
                 INNER JOIN users u
                    ON u.id_users=a.supervisor_user_id
                   AND u.state=1
                 INNER JOIN users_role ur
                    ON ur.id_users=u.id_users
                   AND ur.state=1
                 INNER JOIN users_role_group g
                    ON g.id_role_group=ur.id_role_group
                   AND g.id_company=u.id_company
                   AND g.state=1
                 WHERE a.target_user_id=:target_user_id
                   AND a.id_company=:id_company
                   AND a.state=1
                   AND LOWER(g.name) IN (
                       "jefatura","paramedico","contratista"
                   )
                 ORDER BY u.lastname,u.name,u.id_users'
            );
            $stmt->execute([
                'target_user_id'=>$targetUserId,
                'id_company'=>$companyId,
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function replaceSupervisionAssignments(
        string $targetUserId,
        int $companyId,
        array $supervisorUserIds,
        string $actor
    ): void {
        $supervisorUserIds = array_values(array_unique(array_filter(
            array_map(
                static fn($value): string => strtolower(trim((string)$value)),
                $supervisorUserIds
            ),
            static fn(string $value): bool => $value !== ''
        )));

        if ($supervisorUserIds) {
            $placeholders = [];
            $params = ['id_company'=>$companyId];

            foreach ($supervisorUserIds as $index=>$userId) {
                $key = 'supervisor_'.$index;
                $placeholders[] = ':'.$key;
                $params[$key] = $userId;
            }

            $sql =
                "SELECT DISTINCT u.id_users
                 FROM users u
                 INNER JOIN users_role ur
                    ON ur.id_users=u.id_users
                   AND ur.state=1
                 INNER JOIN users_role_group g
                    ON g.id_role_group=ur.id_role_group
                   AND g.id_company=u.id_company
                   AND g.state=1
                 WHERE u.id_company=:id_company
                   AND u.state=1
                   AND LOWER(g.name) IN (
                       'jefatura','paramedico','contratista'
                   )
                   AND LOWER(u.id_users) IN (" .
                   implode(',', $placeholders) .
                   ')';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $valid = array_map(
                static fn($value): string => strtolower((string)$value),
                $stmt->fetchAll(PDO::FETCH_COLUMN)
            );

            sort($valid);
            $expected = $supervisorUserIds;
            sort($expected);

            if ($valid !== $expected) {
                throw new InvalidArgumentException(
                    'Uno o más responsables asignados no son válidos.'
                );
            }
        }

        // Desactiva relaciones anteriores incluso si SuperUsuario movió al
        // usuario de empresa. Así no quedan alcances cruzados obsoletos.
        $disable = $this->pdo->prepare(
            'UPDATE user_supervision_assignments
             SET state=0,
                 last_update=NOW()
             WHERE target_user_id=:target_user_id
               AND state=1'
        );
        $disable->execute([
            'target_user_id'=>$targetUserId,
        ]);

        if (!$supervisorUserIds) {
            return;
        }

        $upsert = $this->pdo->prepare(
            'INSERT INTO user_supervision_assignments(
                id_company,
                supervisor_user_id,
                target_user_id,
                state,
                assigned_by,
                date_create,
                last_update
             ) VALUES(
                :id_company,
                :supervisor_user_id,
                :target_user_id,
                1,
                :assigned_by,
                NOW(),
                NOW()
             )
             ON DUPLICATE KEY UPDATE
                id_company=VALUES(id_company),
                state=1,
                assigned_by=VALUES(assigned_by),
                last_update=NOW()'
        );

        foreach ($supervisorUserIds as $supervisorId) {
            if ($supervisorId === strtolower($targetUserId)) {
                continue;
            }

            $upsert->execute([
                'id_company'=>$companyId,
                'supervisor_user_id'=>$supervisorId,
                'target_user_id'=>$targetUserId,
                'assigned_by'=>$actor,
            ]);
        }
    }

    public function workerCandidates(
        int $companyId,
        string $query,
        int $limit = 10
    ): array {
        $query = trim($query);
        if ($companyId <= 0 || $query === '') {
            return [];
        }

        $limit = max(1, min(20, $limit));
        $normalizedRut = strtoupper(
            preg_replace('/[^0-9Kk]/', '', $query) ?? ''
        );
        $like = '%' . $query . '%';

        $sql =
            'SELECT
                w.id_worker,
                w.id_company,
                w.rut,
                w.name,
                w.lastname,
                w.email,
                w.phone,
                w.position,
                w.state,
                (
                    SELECT u.id_users
                    FROM users u
                    WHERE u.id_worker=w.id_worker
                       OR (
                           w.email IS NOT NULL
                           AND w.email<>""
                           AND LOWER(u.id_users)=LOWER(w.email)
                       )
                    ORDER BY
                        CASE WHEN u.id_worker=w.id_worker THEN 0 ELSE 1 END,
                        u.id_users
                    LIMIT 1
                ) AS linked_user_id
             FROM workers w
             WHERE w.id_company=:id_company
               AND w.state=1
               AND (
                    w.rut LIKE :search_rut
                    OR REPLACE(REPLACE(UPPER(w.rut),".",""),"-","")
                       = :normalized_rut_filter
                    OR w.email LIKE :search_email
                    OR w.name LIKE :search_name
                    OR w.lastname LIKE :search_lastname
                    OR CONCAT(w.name," ",w.lastname) LIKE :search_fullname
               )
             ORDER BY
                CASE
                    WHEN REPLACE(REPLACE(UPPER(w.rut),".",""),"-","")
                         = :normalized_rut
                    THEN 0
                    WHEN LOWER(COALESCE(w.email,""))=LOWER(:exact_query)
                    THEN 1
                    ELSE 2
                END,
                w.lastname,
                w.name
             LIMIT ' . $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_company'=>$companyId,
            'search_rut'=>$like,
            'search_email'=>$like,
            'search_name'=>$like,
            'search_lastname'=>$like,
            'search_fullname'=>$like,
            'normalized_rut_filter'=>$normalizedRut,
            'normalized_rut'=>$normalizedRut,
            'exact_query'=>$query,
        ]);

        return $stmt->fetchAll();
    }

    public function workerForUserCreation(
        int $workerId,
        int $companyId
    ): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT
                w.id_worker,
                w.id_company,
                w.rut,
                w.name,
                w.lastname,
                w.email,
                w.phone,
                w.position,
                w.state,
                (
                    SELECT u.id_users
                    FROM users u
                    WHERE u.id_worker=w.id_worker
                       OR (
                           w.email IS NOT NULL
                           AND w.email<>""
                           AND LOWER(u.id_users)=LOWER(w.email)
                       )
                    ORDER BY
                        CASE WHEN u.id_worker=w.id_worker THEN 0 ELSE 1 END,
                        u.id_users
                    LIMIT 1
                ) AS linked_user_id
             FROM workers w
             WHERE w.id_worker=:id_worker
               AND w.id_company=:id_company
               AND w.state=1
             LIMIT 1'
        );
        $stmt->execute([
            'id_worker'=>$workerId,
            'id_company'=>$companyId,
        ]);
        $row=$stmt->fetch();
        return $row ?: null;
    }

    public function exists(string $userId): bool
    {
        $stmt=$this->pdo->prepare('SELECT 1 FROM users WHERE id_users=:id_users LIMIT 1');
        $stmt->execute(['id_users'=>$userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function createUser(array $data): void
    {
        $stmt=$this->pdo->prepare(
            'INSERT INTO users (
                id_users,id_company,id_worker,name,lastname,rut,state,language,
                profile_photo_path,last_access,created_by,date_create,last_update
             ) VALUES (
                :id_users,:id_company,:id_worker,:name,:lastname,:rut,1,:language,
                NULL,:last_access,:created_by,NOW(),NOW()
             )'
        );
        $stmt->execute($data);
    }

    public function createPendingCredential(string $userId,string $status): void
    {
        $stmt=$this->pdo->prepare(
            'INSERT INTO user_credentials (id_users,password_hash,credential_status,password_changed_at,legacy_password_invalidated_at,created_at,updated_at)
             VALUES (:id_users,NULL,:credential_status,NULL,NULL,NOW(),NOW())'
        );
        $stmt->execute(['id_users'=>$userId,'credential_status'=>$status]);
    }

    public function assignRole(string $userId,int $roleGroupId,string $actor): void
    {
        $stmt=$this->pdo->prepare(
            'INSERT INTO users_role (id_users,id_role_group,state,create_by,date_create,last_update)
             VALUES (:id_users,:id_role_group,1,:create_by,NOW(),NOW())'
        );
        $stmt->execute(['id_users'=>$userId,'id_role_group'=>$roleGroupId,'create_by'=>$actor]);
    }

    public function updateBasicProfile(string $userId,string $name,string $lastname,string $language): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET name=:name,lastname=:lastname,language=:language,last_update=NOW() WHERE id_users=:id_users LIMIT 1');
        $stmt->execute(['name'=>$name,'lastname'=>$lastname,'language'=>$language,'id_users'=>$userId]);
    }

    public function updateManagedProfile(string $userId,string $name,string $lastname,string $rut,string $language): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET name=:name,lastname=:lastname,rut=:rut,language=:language,last_update=NOW() WHERE id_users=:id_users LIMIT 1');
        $stmt->execute(['name'=>$name,'lastname'=>$lastname,'rut'=>$rut,'language'=>$language,'id_users'=>$userId]);
    }

    public function moveCompany(string $userId,int $companyId): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET id_company=:id_company,last_update=NOW() WHERE id_users=:id_users LIMIT 1');
        $stmt->execute(['id_company'=>$companyId,'id_users'=>$userId]);
        $stmt=$this->pdo->prepare(
            'UPDATE users_role ur INNER JOIN users_role_group urg ON urg.id_role_group=ur.id_role_group
             SET ur.state=0,ur.last_update=NOW()
             WHERE ur.id_users=:id_users AND ur.state=1 AND urg.id_company<>:new_company'
        );
        $stmt->execute(['id_users'=>$userId,'new_company'=>$companyId]);
    }

    public function setSoleActiveRole(string $userId,int $companyId,int $roleGroupId,string $actor): void
    {
        $stmt=$this->pdo->prepare(
            'UPDATE users_role ur INNER JOIN users_role_group urg ON urg.id_role_group=ur.id_role_group
             SET ur.state=0,ur.last_update=NOW()
             WHERE ur.id_users=:id_users AND ur.state=1 AND urg.id_company=:role_company_id AND ur.id_role_group<>:id_role_group'
        );
        $stmt->execute(['id_users'=>$userId,'role_company_id'=>$companyId,'id_role_group'=>$roleGroupId]);
        $stmt=$this->pdo->prepare('SELECT id_users_role FROM users_role WHERE id_users=:id_users AND id_role_group=:id_role_group ORDER BY id_users_role DESC LIMIT 1');
        $stmt->execute(['id_users'=>$userId,'id_role_group'=>$roleGroupId]);
        $existing=$stmt->fetchColumn();
        if ($existing!==false) {
            $stmt=$this->pdo->prepare('UPDATE users_role SET state=1,last_update=NOW() WHERE id_users_role=:id LIMIT 1');
            $stmt->execute(['id'=>(int)$existing]);
            return;
        }
        $this->assignRole($userId,$roleGroupId,$actor);
    }

    public function setState(string $userId,int $state): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET state=:state,last_update=NOW() WHERE id_users=:id_users LIMIT 1');
        $stmt->execute(['state'=>$state,'id_users'=>$userId]);
    }

    public function setPhotoPath(string $userId,?string $path): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET profile_photo_path=:path,last_update=NOW() WHERE id_users=:id_users LIMIT 1');
        $stmt->execute(['path'=>$path,'id_users'=>$userId]);
    }

    public function setLanguage(string $userId,string $language): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET language=:language,last_update=NOW() WHERE id_users=:id_users LIMIT 1');
        $stmt->execute(['language'=>$language,'id_users'=>$userId]);
    }

    public function setMutualCode(string $userId,?string $code): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET mutual_code=:mutual_code,last_update=NOW() WHERE id_users=:id_users');
        $stmt->execute(['mutual_code'=>$code,'id_users'=>$userId]);
    }

    public function findCompanyAndMutual(string $userId): ?array
    {
        $stmt=$this->pdo->prepare('SELECT id_company,mutual_code FROM users WHERE id_users=:id_users AND state=1 LIMIT 1');
        $stmt->execute(['id_users'=>$userId]);
        $row=$stmt->fetch(); return $row?:null;
    }

    public function updateLinkedWorker(int $workerId,int $companyId,string $name,string $lastname,?string $phone): void
    {
        $stmt=$this->pdo->prepare('UPDATE workers SET name=:name,lastname=:lastname,phone=:phone,last_update=NOW() WHERE id_worker=:id_worker AND id_company=:id_company');
        $stmt->execute(['name'=>$name,'lastname'=>$lastname,'phone'=>$phone,'id_worker'=>$workerId,'id_company'=>$companyId]);
    }

    public function listUsers(?int $companyId,?int $state,string $search): array
    {
        $params=[];$where=['1=1'];
        if ($companyId!==null) { $where[]='u.id_company=:id_company';$params['id_company']=$companyId; }
        if ($state!==null) { $where[]='u.state=:state';$params['state']=$state; }
        if ($search!=='') {
            $where[]='(u.id_users LIKE :search OR u.name LIKE :search OR u.lastname LIKE :search OR u.rut LIKE :search OR c.razon_social LIKE :search OR urg.name LIKE :search)';
            $params['search']='%'.$search.'%';
        }
        $stmt=$this->pdo->prepare(
            'SELECT u.id_users,u.name,u.lastname,u.id_company,u.rut,u.profile_photo_path,c.razon_social,u.state,u.last_access,
                    MAX(CASE WHEN uc.credential_status="active" AND COALESCE(uc.password_hash,"")<>"" THEN 1 ELSE 0 END) password_configured,
                    COALESCE(MAX(uc.credential_status),"pending_activation") credential_status,
                    COALESCE(NULLIF(GROUP_CONCAT(DISTINCT CASE WHEN ur.state=1 AND urg.state=1 AND urg.id_company=u.id_company THEN urg.name END ORDER BY urg.name SEPARATOR ", "),""),"Sin rol") role_name
             FROM users u INNER JOIN company c ON c.id_company=u.id_company
             LEFT JOIN user_credentials uc ON uc.id_users=u.id_users
             LEFT JOIN users_role ur ON ur.id_users=u.id_users
             LEFT JOIN users_role_group urg ON urg.id_role_group=ur.id_role_group
             WHERE '.implode(' AND ',$where).'
             GROUP BY u.id_users,u.name,u.lastname,u.id_company,u.rut,u.profile_photo_path,c.razon_social,u.state,u.last_access
             ORDER BY u.lastname,u.name,u.id_users'
        );
        $stmt->execute($params); return $stmt->fetchAll();
    }

    public function findActive(string $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_users,id_company,id_worker,name,lastname,email,state FROM users WHERE id_users=:id_users AND state=1 LIMIT 1'
        );
        $stmt->execute(['id_users' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function isActiveInCompany(string $userId, int $companyId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM users WHERE id_users=:id_users AND id_company=:id_company AND state=1 LIMIT 1'
        );
        $stmt->execute(['id_users' => $userId, 'id_company' => $companyId]);
        return (bool) $stmt->fetchColumn();
    }

    public function isActiveLinkedToWorker(string $userId, int $workerId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM users WHERE id_users=:id_users AND id_worker=:id_worker AND state=1 LIMIT 1'
        );
        $stmt->execute(['id_users' => $userId, 'id_worker' => $workerId]);
        return (bool) $stmt->fetchColumn();
    }

    public function listActiveWithCompany(?int $companyId = null): array
    {
        $sql = 'SELECT u.id_users,u.id_company,u.name,u.lastname,c.razon_social AS company_name '
             . 'FROM users u INNER JOIN company c ON c.id_company=u.id_company '
             . 'WHERE u.state=1 AND c.state=1';
        $params = [];
        if ($companyId !== null) {
            $sql .= ' AND u.id_company=:id_company';
            $params['id_company'] = $companyId;
        }
        $sql .= $companyId === null
            ? ' ORDER BY c.razon_social,u.name,u.lastname,u.id_users'
            : ' ORDER BY u.name,u.lastname,u.id_users';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
