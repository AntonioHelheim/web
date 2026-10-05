<?php
/** Safety Control Tower - Programas + seguimiento mensual (Etapa 3). */

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/repositorios/ProgramaRepository.php';
require_once __DIR__ . '/../../i18n.php';

const PROGRAM_STATUS = ['planificado','en_curso','completado','suspendido','cancelado'];

function programasIsGlobalAdmin(PDO $pdo): bool
{
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function programasCurrentCompany(PDO $pdo): int
{
    $id=currentUserCompanyId($pdo);
    if (!$id) responderJSON(false,null,'Tu cuenta no tiene una empresa asociada.',403);
    return $id;
}

function programasResolveCompany(PDO $pdo, ?int $requested): int
{
    if (programasIsGlobalAdmin($pdo)) {
        if (!$requested || $requested<=0) responderJSON(false,null,'Selecciona una empresa para continuar.',400);
        $stmt=$pdo->prepare('SELECT 1 FROM company WHERE id_company=:id_company AND state=1 LIMIT 1');
        $stmt->execute(['id_company'=>$requested]);
        if (!$stmt->fetchColumn()) responderJSON(false,null,'La empresa seleccionada no existe o está inactiva.',400);
        return $requested;
    }
    return programasCurrentCompany($pdo);
}

function programasRequirePage(PDO $pdo, string $redirect='../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo,'programs.view',$redirect);
}

function programasSchemaReady(PDO $pdo): bool
{
    static $ready=null;
    if ($ready!==null) return $ready;
    try {
        $programCols=[];
        foreach ($pdo->query('SHOW COLUMNS FROM programs')->fetchAll() as $r) $programCols[(string)$r['Field']]=true;
        $trackingCols=[];
        foreach ($pdo->query('SHOW COLUMNS FROM program_monthly_tracking')->fetchAll() as $r) $trackingCols[(string)$r['Field']]=true;
        foreach (['responsible_user','start_date','end_date','status'] as $c) if (!isset($programCols[$c])) return $ready=false;
        if (!isset($trackingCols['target_percentage'])) return $ready=false;
        return $ready=true;
    } catch (Throwable $e) {
        error_log('programasSchemaReady: '.$e->getMessage());
        return $ready=false;
    }
}

function programasRequireSchema(PDO $pdo): void
{
    if (!programasSchemaReady($pdo)) responderJSON(false,null,'Debes ejecutar la migración de Programas y seguimiento mensual.',409);
}

function programasCsrf(array $input): void
{
    $token=(string)($input['csrf_token']??'');
    if (empty($_SESSION['csrf_token']) || !hash_equals((string)$_SESSION['csrf_token'],$token)) {
        responderJSON(false,null,'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.',403);
    }
}

function programasText($value, int $max, string $label, bool $required=false): ?string
{
    $value=trim((string)($value??''));
    if ($value==='') {
        if ($required) responderJSON(false,null,$label.' es obligatorio.',400);
        return null;
    }
    if (sctTextLength($value)>$max) responderJSON(false,null,$label.' admite hasta '.$max.' caracteres.',400);
    return $value;
}

function programasDate($value, string $label, bool $required=false): ?string
{
    $value=trim((string)($value??''));
    if ($value==='') {
        if ($required) responderJSON(false,null,$label.' es obligatoria.',400);
        return null;
    }
    $d=DateTime::createFromFormat('Y-m-d',$value);
    if (!$d || $d->format('Y-m-d')!==$value) responderJSON(false,null,$label.' no es una fecha válida.',400);
    return $value;
}

function programasMonth($value): string
{
    $value=trim((string)$value);
    if (preg_match('/^\d{4}-\d{2}$/',$value)) $value.='-01';
    $d=DateTime::createFromFormat('Y-m-d',$value);
    if (!$d || $d->format('Y-m-d')!==$value || substr($value,8,2)!=='01') {
        responderJSON(false,null,'El periodo debe corresponder a un mes válido.',400);
    }
    return $value;
}

function programasPercent($value, string $label, bool $nullable=false): ?float
{
    if ($nullable && ($value===null || trim((string)$value)==='')) return null;
    if (!is_numeric($value)) responderJSON(false,null,$label.' debe ser un porcentaje válido.',400);
    $v=round((float)$value,2);
    if ($v<0 || $v>100) responderJSON(false,null,$label.' debe estar entre 0 y 100.',400);
    return $v;
}

function programasValidateProject(PDO $pdo, ?int $idProject, int $company, bool $requireActive=true): ?int
{
    if (!$idProject) return null;
    $sql='SELECT state FROM projects WHERE id_project=:id_project AND id_company=:id_company LIMIT 1';
    $stmt=$pdo->prepare($sql);
    $stmt->execute(['id_project'=>$idProject,'id_company'=>$company]);
    $state=$stmt->fetchColumn();
    if ($state===false) responderJSON(false,null,'El proyecto no pertenece a la empresa seleccionada.',400);
    if ($requireActive && (int)$state!==1) responderJSON(false,null,'El proyecto está inactivo y no admite nuevos programas.',400);
    return $idProject;
}

function programasValidateResponsible(PDO $pdo, string $idUsers, int $company): string
{
    $idUsers=trim($idUsers);
    if ($idUsers==='') responderJSON(false,null,'Selecciona un responsable.',400);
    $stmt=$pdo->prepare('SELECT 1 FROM users WHERE id_users=:id_users AND id_company=:id_company AND state=1 LIMIT 1');
    $stmt->execute(['id_users'=>$idUsers,'id_company'=>$company]);
    if (!$stmt->fetchColumn()) responderJSON(false,null,'El responsable no pertenece a la empresa o está inactivo.',400);
    return $idUsers;
}

function programasValidatePayload(PDO $pdo, array $input, int $company, bool $creating): array
{
    $name=programasText($input['name']??null,150,'Nombre',true);
    $description=programasText($input['description']??null,5000,'Descripción');
    $idProject=isset($input['id_project']) && (int)$input['id_project']>0 ? (int)$input['id_project'] : null;
    $idProject=programasValidateProject($pdo,$idProject,$company,$creating);
    $responsible=programasValidateResponsible($pdo,(string)($input['responsible_user']??''),$company);
    $start=programasDate($input['start_date']??null,'Fecha de inicio',true);
    $end=programasDate($input['end_date']??null,'Fecha de término');
    if ($end!==null && $end<$start) responderJSON(false,null,'La fecha de término no puede ser anterior a la fecha de inicio.',400);
    $status=trim((string)($input['status']??'planificado'));
    if (!in_array($status,PROGRAM_STATUS,true)) responderJSON(false,null,'El estado operativo no es válido.',400);
    if ($creating && !in_array($status,['planificado','en_curso'],true)) {
        responderJSON(false,null,'Un programa nuevo debe comenzar planificado o en curso.',400);
    }
    return [
        'id_company'=>$company,'id_project'=>$idProject,'name'=>$name,'description'=>$description,
        'responsible_user'=>$responsible,'start_date'=>$start,'end_date'=>$end,'status'=>$status,
    ];
}

function programasAssertVisible(PDO $pdo, array $program): void
{
    if (programasIsGlobalAdmin($pdo)) return;
    if ((int)$program['id_company']!==programasCurrentCompany($pdo)) responderJSON(false,null,'El programa pertenece a otra empresa.',403);
}

function programasAssertStatusTransition(string $from, string $to): void
{
    if ($from===$to) return;
    $allowed=[
        'planificado'=>['en_curso','suspendido','cancelado'],
        'en_curso'=>['suspendido','completado','cancelado'],
        'suspendido'=>['en_curso','cancelado'],
        'completado'=>[],
        'cancelado'=>[],
    ];
    if (!in_array($to,$allowed[$from]??[],true)) {
        responderJSON(false,null,'La transición de estado del programa no está permitida.',409);
    }
}

function programasAssertTrackingAllowed(array $program): void
{
    if ((int)$program['state']!==1) responderJSON(false,null,'El programa está inactivo.',409);
    if (trim((string)($program['responsible_user']??''))==='' || empty($program['start_date'])) {
        responderJSON(false,null,'Completa responsable y fecha de inicio antes de registrar seguimiento.',409);
    }
    if (in_array((string)$program['status'],['suspendido','completado','cancelado'],true)) {
        responderJSON(false,null,'El estado actual del programa no admite nuevos seguimientos.',409);
    }
}
