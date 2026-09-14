<?php
require __DIR__.'/common.php';
requireCapability($pdo,'programs.edit');
programasRequireSchema($pdo);
if ($_SERVER['REQUEST_METHOD']!=='POST') responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true); if (!is_array($input)) $input=$_POST;
programasCsrf($input);
$id=(int)($input['id_program']??0); if ($id<=0) responderJSON(false,null,'Programa inválido.',400);
$before=programaObtener($pdo,$id); if (!$before) responderJSON(false,null,'Programa no encontrado.',404);
programasAssertVisible($pdo,$before);
$data=programasValidatePayload($pdo,$input,(int)$before['id_company'],false);
programasAssertStatusTransition((string)$before['status'],(string)$data['status']);
if ((string)$data['status']==='completado') {
    $latest=programaUltimoAvance($pdo,$id);
    if ($latest===null || $latest<100) responderJSON(false,null,'Para completar el programa, el último avance debe ser 100%.',409);
}
if (programaExisteNombre($pdo,(int)$before['id_company'],(string)$data['name'],$id)) responderJSON(false,null,'Ya existe otro programa con ese nombre en la empresa.',409);
try {
    programaEditar($pdo,$id,$data);
    $after=programaObtener($pdo,$id) ?: array_merge($before,$data);
    auditTrailLogChanges($pdo,(int)$before['id_company'],'programas','programs',$id,$before,$after,'update',(string)$data['name'],['id_project','name','description','responsible_user','start_date','end_date','status']);
    responderJSON(true,['id_program'=>$id],'Programa actualizado correctamente.');
} catch (Throwable $e) {
    error_log('programas-editar.php: '.$e->getMessage());
    responderJSON(false,null,'No se pudo actualizar el programa.',500);
}
