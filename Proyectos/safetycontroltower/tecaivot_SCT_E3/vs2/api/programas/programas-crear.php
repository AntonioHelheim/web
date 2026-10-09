<?php
require __DIR__.'/common.php';
requireCapability($pdo,'programs.create');
programasRequireSchema($pdo);
if ($_SERVER['REQUEST_METHOD']!=='POST') responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true); if (!is_array($input)) $input=$_POST;
programasCsrf($input);
$requested=isset($input['id_company'])?(int)$input['id_company']:null;
$idCompany=programasResolveCompany($pdo,$requested);
$data=programasValidatePayload($pdo,$input,$idCompany,true);
if (programaExisteNombre($pdo,$idCompany,(string)$data['name'])) responderJSON(false,null,'Ya existe un programa con ese nombre en la empresa.',409);
try {
    $id=programaCrear($pdo,$data,(string)currentUserId());
    auditTrailLogChanges($pdo,$idCompany,'programas','programs',$id,[],array_merge($data,['state'=>1]),'create',(string)$data['name']);
    responderJSON(true,['id_program'=>$id],'Programa creado correctamente.',201);
} catch (Throwable $e) {
    error_log('programas-crear.php: '.$e->getMessage());
    responderJSON(false,null,'No se pudo crear el programa.',500);
}
