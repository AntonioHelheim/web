<?php
require __DIR__.'/common.php';
requireCapability($pdo,'programs.tracking');
programasRequireSchema($pdo);
if ($_SERVER['REQUEST_METHOD']!=='POST') responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true); if (!is_array($input)) $input=$_POST;
programasCsrf($input);
$id=(int)($input['id_program']??0); if ($id<=0) responderJSON(false,null,'Programa inválido.',400);
$program=programaObtener($pdo,$id); if (!$program) responderJSON(false,null,'Programa no encontrado.',404);
programasAssertVisible($pdo,$program); programasAssertTrackingAllowed($program);
$period=programasMonth($input['period_month']??'');
$target=programasPercent($input['target_percentage']??null,'Meta mensual',true);
$progress=(float)programasPercent($input['progress_percentage']??null,'Avance mensual',false);
$comments=programasText($input['comments']??null,5000,'Comentarios');
$monthKey=substr($period,0,7);
if (!empty($program['start_date']) && $monthKey<substr((string)$program['start_date'],0,7)) responderJSON(false,null,'El periodo es anterior al inicio del programa.',400);
if (!empty($program['end_date']) && $monthKey>substr((string)$program['end_date'],0,7)) responderJSON(false,null,'El periodo es posterior al término del programa.',400);
$before=programaTrackingObtenerPeriodo($pdo,$id,$period);
try {
    $pdo->beginTransaction();
    $saved=programaTrackingGuardar($pdo,$id,$period,$target,$progress,$comments,(string)currentUserId());
    $after=programaTrackingObtenerPeriodo($pdo,$id,$period);
    if ((string)$program['status']==='planificado') {
        programaEditar($pdo,$id,[
            'id_project'=>$program['id_project']!==null?(int)$program['id_project']:null,
            'name'=>(string)$program['name'],'description'=>$program['description'],
            'responsible_user'=>(string)$program['responsible_user'],'start_date'=>(string)$program['start_date'],
            'end_date'=>$program['end_date'],'status'=>'en_curso',
        ]);
    }
    $pdo->commit();
    $action=$saved['created']?'create':'update';
    auditTrailLogChanges($pdo,(int)$program['id_company'],'programas','program_monthly_tracking',(int)$saved['id_tracking'],$before?:[],$after?:[],$action,(string)$program['name'].' · '.$monthKey,['period_month','target_percentage','progress_percentage','comments']);
    if ((string)$program['status']==='planificado') {
        auditTrailLogAction($pdo,(int)$program['id_company'],'programas','programs',$id,'status','planificado','en_curso','state_change',(string)$program['name']);
    }
    responderJSON(true,['id_tracking'=>(int)$saved['id_tracking'],'created'=>(bool)$saved['created']],$saved['created']?'Seguimiento mensual registrado.':'Seguimiento mensual actualizado.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('tracking-guardar.php: '.$e->getMessage());
    responderJSON(false,null,'No se pudo guardar el seguimiento mensual.',500);
}
