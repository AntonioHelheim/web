<?php
require __DIR__.'/common.php';
requireCapability($pdo,'programs.state');
programasRequireSchema($pdo);
if ($_SERVER['REQUEST_METHOD']!=='POST') responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true); if (!is_array($input)) $input=$_POST;
programasCsrf($input);
$id=(int)($input['id_program']??0); $state=(int)($input['state']??-1);
if ($id<=0 || !in_array($state,[0,1],true)) responderJSON(false,null,'Datos inválidos.',400);
$program=programaObtener($pdo,$id); if (!$program) responderJSON(false,null,'Programa no encontrado.',404);
programasAssertVisible($pdo,$program);
if ((int)$program['state']===$state) responderJSON(true,['id_program'=>$id,'state'=>$state],'El programa ya tiene ese estado.');
try {
    programaCambiarEstado($pdo,$id,$state);
    auditTrailLogAction($pdo,(int)$program['id_company'],'programas','programs',$id,'state',(int)$program['state'],$state,'state_change',(string)$program['name']);
    responderJSON(true,['id_program'=>$id,'state'=>$state],$state?'Programa activado.':'Programa desactivado.');
} catch (Throwable $e) {
    error_log('programas-cambiar-estado.php: '.$e->getMessage());
    responderJSON(false,null,'No se pudo cambiar el estado del programa.',500);
}
