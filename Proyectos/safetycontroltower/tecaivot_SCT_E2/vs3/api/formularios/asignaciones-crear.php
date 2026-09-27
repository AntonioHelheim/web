<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'dynamic_forms.manage');
if($_SERVER['REQUEST_METHOD']!=='POST')responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true);if(!is_array($input))$input=$_POST;requireCsrfToken($input);
$idForm=filter_var($input['id_form']??null,FILTER_VALIDATE_INT);
$users=$input['id_users']??[];if(!is_array($users))$users=[$users];$users=array_values(array_unique(array_filter(array_map('strval',$users))));
if(!$idForm||!$users)responderJSON(false,null,'Selecciona al menos un usuario.',400);
$start=trim((string)($input['access_start']??''));$deadline=trim((string)($input['deadline']??''));
$parse=function($value,$end=false){if($value==='')return null;$d=DateTime::createFromFormat('Y-m-d',$value);if(!$d)return false;return $d->format('Y-m-d '.($end?'23:59:59':'00:00:00'));};
$startDb=$parse($start,false);$deadlineDb=$parse($deadline,true);if($startDb===false||$deadlineDb===false)responderJSON(false,null,'Las fechas indicadas no son válidas.',400);if($startDb&&$deadlineDb&&$deadlineDb<$startDb)responderJSON(false,null,'La fecha límite no puede ser anterior al inicio.',400);
try{
    $form=formularioObtener($pdo,(int)$idForm);if(!$form)responderJSON(false,null,'Formulario no encontrado.',404);formularioAssertVisible($pdo,$form);
    $requestedCompany=filter_var($input['id_company']??null,FILTER_VALIDATE_INT);
    if($form['id_company']!==null){
        $company=(int)$form['id_company'];
    }elseif(formularioIsGlobalAdmin($pdo)){
        $company=$requestedCompany?(int)$requestedCompany:0;
        if($company<=0) responderJSON(false,null,'Selecciona una empresa para asignar el formulario.',400);
    }else{
        $company=formularioCurrentCompany($pdo);
    }
    $ready=(int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='dynamic_form_assignments'")->fetchColumn()>0;
    if(!$ready)responderJSON(false,null,'Aplica la migración P73 para habilitar asignaciones de formularios.',409);
    $valid=$pdo->prepare('SELECT COUNT(*) FROM users WHERE id_users=:user AND id_company=:company AND state=1');
    $exists=$pdo->prepare("SELECT id_form_assignment,status FROM dynamic_form_assignments WHERE id_form=:form AND id_users=:user LIMIT 1");
    $insert=$pdo->prepare("INSERT INTO dynamic_form_assignments(id_form,id_company,id_users,access_start,deadline,status,created_by,date_create,last_update) VALUES(:form,:company,:user,:start,:deadline,'pending',:by,NOW(),NOW())");
    $reactivate=$pdo->prepare("UPDATE dynamic_form_assignments SET id_company=:company,access_start=:start,deadline=:deadline,status='pending',created_by=:by,last_update=NOW() WHERE id_form_assignment=:id");
    $created=0;$skipped=0;$pdo->beginTransaction();
    foreach($users as $user){
        $valid->execute(['user'=>$user,'company'=>$company]);
        if(!(int)$valid->fetchColumn()){$skipped++;continue;}
        $exists->execute(['form'=>$idForm,'user'=>$user]);
        $existing=$exists->fetch(PDO::FETCH_ASSOC);
        if($existing){
            if((string)$existing['status']!=='cancelled'){$skipped++;continue;}
            $reactivate->execute(['company'=>$company,'start'=>$startDb,'deadline'=>$deadlineDb,'by'=>currentUserId(),'id'=>(int)$existing['id_form_assignment']]);
            $created++;
            continue;
        }
        $insert->execute(['form'=>$idForm,'company'=>$company,'user'=>$user,'start'=>$startDb,'deadline'=>$deadlineDb,'by'=>currentUserId()]);
        $created++;
    }
    $pdo->commit();
    auditTrailLogAction($pdo,$company,'formularios','dynamic_form_assignments',$idForm,'assigned_users',null,$created,'assign',(string)$form['name']);
    responderJSON(true,['created_count'=>$created,'skipped_count'=>$skipped],$created.' asignación(es) creadas; '.$skipped.' omitidas.');
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('formularios/asignaciones-crear.php: '.$e->getMessage());responderJSON(false,null,'No se pudo completar la asignación.',500);}
