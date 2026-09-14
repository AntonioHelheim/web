<?php
/** Safety Control Tower - Programas y seguimiento mensual (Etapa 3). */

function programaListarEmpresas(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT id_company, razon_social, state
         FROM company
         WHERE state=1
         ORDER BY razon_social ASC, id_company ASC'
    );
    return $stmt->fetchAll();
}

function programaListarProyectosEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare(
        'SELECT id_project, name, state
         FROM projects
         WHERE id_company=:id_company
         ORDER BY state DESC, name ASC, id_project ASC'
    );
    $stmt->execute(['id_company'=>$idCompany]);
    return $stmt->fetchAll();
}

function programaListarUsuariosEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare(
        'SELECT id_users, name, lastname, state
         FROM users
         WHERE id_company=:id_company
         ORDER BY state DESC, name ASC, lastname ASC, id_users ASC'
    );
    $stmt->execute(['id_company'=>$idCompany]);
    return $stmt->fetchAll();
}

function programaExisteNombre(PDO $pdo, int $idCompany, string $name, ?int $excludeId=null): bool
{
    $sql = 'SELECT 1 FROM programs WHERE id_company=:id_company AND LOWER(name)=LOWER(:name)';
    $params = ['id_company'=>$idCompany,'name'=>$name];
    if ($excludeId !== null) {
        $sql .= ' AND id_program<>:exclude_id';
        $params['exclude_id']=$excludeId;
    }
    $sql .= ' LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (bool)$stmt->fetchColumn();
}

function programaObtener(PDO $pdo, int $idProgram): ?array
{
    $stmt = $pdo->prepare(
        'SELECT p.id_program,p.id_company,p.id_project,p.name,p.description,p.responsible_user,
                p.start_date,p.end_date,p.status,p.state,p.created_by,p.date_create,p.last_update,
                c.razon_social AS company_name,
                pr.name AS project_name,
                TRIM(CONCAT(COALESCE(u.name,\'\'),\' \',COALESCE(u.lastname,\'\'))) AS responsible_name,
                lt.id_tracking AS latest_tracking_id,
                lt.period_month AS latest_period,
                lt.target_percentage AS latest_target,
                lt.progress_percentage AS latest_progress,
                lt.comments AS latest_comments
         FROM programs p
         INNER JOIN company c ON c.id_company=p.id_company
         LEFT JOIN projects pr ON pr.id_project=p.id_project
         LEFT JOIN users u ON u.id_users=p.responsible_user
         LEFT JOIN program_monthly_tracking lt ON lt.id_tracking=(
             SELECT t2.id_tracking
             FROM program_monthly_tracking t2
             WHERE t2.id_program=p.id_program
             ORDER BY t2.period_month DESC,t2.id_tracking DESC
             LIMIT 1
         )
         WHERE p.id_program=:id_program
         LIMIT 1'
    );
    $stmt->execute(['id_program'=>$idProgram]);
    $row=$stmt->fetch();
    return $row ?: null;
}

/** @return array{where:string,params:array<string,mixed>} */
function programaBuildFilters(int $idCompany, array $filters): array
{
    $where=['p.id_company=:id_company'];
    $params=['id_company'=>$idCompany];

    if (isset($filters['state']) && $filters['state'] !== '' && $filters['state'] !== null) {
        $where[]='p.state=:state';
        $params['state']=(int)$filters['state'];
    }
    if (!empty($filters['status'])) {
        $where[]='p.status=:status';
        $params['status']=(string)$filters['status'];
    }
    if (!empty($filters['id_project'])) {
        $where[]='p.id_project=:id_project';
        $params['id_project']=(int)$filters['id_project'];
    }
    if (!empty($filters['search'])) {
        $where[]='(p.name LIKE :q1 OR p.description LIKE :q2 OR p.responsible_user LIKE :q3 OR pr.name LIKE :q4)';
        $like='%'.(string)$filters['search'].'%';
        $params['q1']=$like;$params['q2']=$like;$params['q3']=$like;$params['q4']=$like;
    }
    return ['where'=>implode(' AND ',$where),'params'=>$params];
}

function programaListar(PDO $pdo, int $idCompany, array $filters=[]): array
{
    $f=programaBuildFilters($idCompany,$filters);
    $stmt=$pdo->prepare(
        'SELECT p.id_program,p.id_company,p.id_project,p.name,p.description,p.responsible_user,
                p.start_date,p.end_date,p.status,p.state,p.date_create,p.last_update,
                pr.name AS project_name,
                TRIM(CONCAT(COALESCE(u.name,\'\'),\' \',COALESCE(u.lastname,\'\'))) AS responsible_name,
                lt.period_month AS latest_period,
                lt.target_percentage AS latest_target,
                lt.progress_percentage AS latest_progress,
                (SELECT COUNT(*) FROM program_monthly_tracking tc WHERE tc.id_program=p.id_program) AS tracking_count
         FROM programs p
         LEFT JOIN projects pr ON pr.id_project=p.id_project
         LEFT JOIN users u ON u.id_users=p.responsible_user
         LEFT JOIN program_monthly_tracking lt ON lt.id_tracking=(
             SELECT t2.id_tracking
             FROM program_monthly_tracking t2
             WHERE t2.id_program=p.id_program
             ORDER BY t2.period_month DESC,t2.id_tracking DESC
             LIMIT 1
         )
         WHERE '.$f['where'].'
         ORDER BY p.state DESC,
                  CASE p.status WHEN \'en_curso\' THEN 1 WHEN \'planificado\' THEN 2 WHEN \'suspendido\' THEN 3 WHEN \'completado\' THEN 4 ELSE 5 END,
                  p.name ASC,p.id_program ASC'
    );
    $stmt->execute($f['params']);
    return $stmt->fetchAll();
}

function programaResumen(PDO $pdo, int $idCompany, array $filters=[]): array
{
    $rows=programaListar($pdo,$idCompany,$filters);
    $sum=0.0;$withProgress=0;$behind=0;$completed=0;$active=0;
    foreach ($rows as $row) {
        if ((int)$row['state']===1) $active++;
        if ((string)$row['status']==='completado') $completed++;
        if ($row['latest_progress'] !== null) {
            $progress=(float)$row['latest_progress'];
            $sum+=$progress;$withProgress++;
            if ($row['latest_target'] !== null && $progress < (float)$row['latest_target']) $behind++;
        }
    }
    return [
        'total'=>count($rows),
        'active'=>$active,
        'completed'=>$completed,
        'behind_target'=>$behind,
        'average_progress'=>$withProgress ? round($sum/$withProgress,1) : 0.0,
    ];
}

function programaCrear(PDO $pdo, array $data, string $actor): int
{
    $stmt=$pdo->prepare(
        'INSERT INTO programs
         (id_company,id_project,name,description,responsible_user,start_date,end_date,status,state,created_by,date_create,last_update)
         VALUES
         (:id_company,:id_project,:name,:description,:responsible_user,:start_date,:end_date,:status,1,:created_by,NOW(),NOW())'
    );
    $stmt->execute([
        'id_company'=>$data['id_company'],'id_project'=>$data['id_project'],'name'=>$data['name'],
        'description'=>$data['description'],'responsible_user'=>$data['responsible_user'],
        'start_date'=>$data['start_date'],'end_date'=>$data['end_date'],'status'=>$data['status'],
        'created_by'=>$actor,
    ]);
    return (int)$pdo->lastInsertId();
}

function programaEditar(PDO $pdo, int $idProgram, array $data): bool
{
    $stmt=$pdo->prepare(
        'UPDATE programs
         SET id_project=:id_project,name=:name,description=:description,responsible_user=:responsible_user,
             start_date=:start_date,end_date=:end_date,status=:status,last_update=NOW()
         WHERE id_program=:id_program'
    );
    return $stmt->execute([
        'id_project'=>$data['id_project'],'name'=>$data['name'],'description'=>$data['description'],
        'responsible_user'=>$data['responsible_user'],'start_date'=>$data['start_date'],
        'end_date'=>$data['end_date'],'status'=>$data['status'],'id_program'=>$idProgram,
    ]);
}

function programaCambiarEstado(PDO $pdo, int $idProgram, int $state): bool
{
    $stmt=$pdo->prepare('UPDATE programs SET state=:state,last_update=NOW() WHERE id_program=:id_program');
    return $stmt->execute(['state'=>$state,'id_program'=>$idProgram]);
}

function programaTrackingListar(PDO $pdo, int $idProgram): array
{
    $stmt=$pdo->prepare(
        'SELECT id_tracking,id_program,period_month,target_percentage,progress_percentage,comments,created_by,date_create,last_update
         FROM program_monthly_tracking
         WHERE id_program=:id_program
         ORDER BY period_month ASC,id_tracking ASC'
    );
    $stmt->execute(['id_program'=>$idProgram]);
    return $stmt->fetchAll();
}

function programaTrackingObtenerPeriodo(PDO $pdo, int $idProgram, string $periodMonth): ?array
{
    $stmt=$pdo->prepare(
        'SELECT id_tracking,id_program,period_month,target_percentage,progress_percentage,comments,created_by,date_create,last_update
         FROM program_monthly_tracking
         WHERE id_program=:id_program AND period_month=:period_month
         LIMIT 1'
    );
    $stmt->execute(['id_program'=>$idProgram,'period_month'=>$periodMonth]);
    $row=$stmt->fetch();
    return $row ?: null;
}

/** @return array{id_tracking:int,created:bool} */
function programaTrackingGuardar(PDO $pdo, int $idProgram, string $periodMonth, ?float $target, float $progress, ?string $comments, string $actor): array
{
    $existing=programaTrackingObtenerPeriodo($pdo,$idProgram,$periodMonth);
    if ($existing) {
        $stmt=$pdo->prepare(
            'UPDATE program_monthly_tracking
             SET target_percentage=:target_percentage,progress_percentage=:progress_percentage,comments=:comments,last_update=NOW()
             WHERE id_tracking=:id_tracking'
        );
        $stmt->execute([
            'target_percentage'=>$target,'progress_percentage'=>$progress,'comments'=>$comments,
            'id_tracking'=>(int)$existing['id_tracking'],
        ]);
        return ['id_tracking'=>(int)$existing['id_tracking'],'created'=>false];
    }

    $stmt=$pdo->prepare(
        'INSERT INTO program_monthly_tracking
         (id_program,period_month,target_percentage,progress_percentage,comments,created_by,date_create,last_update)
         VALUES (:id_program,:period_month,:target_percentage,:progress_percentage,:comments,:created_by,NOW(),NOW())'
    );
    $stmt->execute([
        'id_program'=>$idProgram,'period_month'=>$periodMonth,'target_percentage'=>$target,
        'progress_percentage'=>$progress,'comments'=>$comments,'created_by'=>$actor,
    ]);
    return ['id_tracking'=>(int)$pdo->lastInsertId(),'created'=>true];
}

function programaUltimoAvance(PDO $pdo, int $idProgram): ?float
{
    $stmt=$pdo->prepare(
        'SELECT progress_percentage FROM program_monthly_tracking
         WHERE id_program=:id_program
         ORDER BY period_month DESC,id_tracking DESC LIMIT 1'
    );
    $stmt->execute(['id_program'=>$idProgram]);
    $value=$stmt->fetchColumn();
    return $value===false ? null : (float)$value;
}
