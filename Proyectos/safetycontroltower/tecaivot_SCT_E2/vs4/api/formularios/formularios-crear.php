<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.create');
if($_SERVER['REQUEST_METHOD']!=='POST') responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true);if(!is_array($input))responderJSON(false,null,'Solicitud inválida.',400);requireCsrfToken($input);
$name=trim((string)($input['name']??''));$description=trim((string)($input['description']??''));
if($name===''||sctTextLength($name)>150)responderJSON(false,null,'El nombre es obligatorio y admite hasta 150 caracteres.',400);
if(sctTextLength($description)>5000)responderJSON(false,null,'La descripción es demasiado extensa.',400);
$idCompany=null;
if(formularioIsGlobalAdmin($pdo)){
    $scope=(string)($input['scope']??'company');
    if($scope==='global'){$idCompany=null;}else{$idCompany=filter_var($input['id_company']??null,FILTER_VALIDATE_INT);if(!$idCompany)responderJSON(false,null,'Debes seleccionar la empresa del formulario.',400);}
}else{$idCompany=formularioCurrentCompany($pdo);}
try{
    if($idCompany!==null){$st=$pdo->prepare('SELECT COUNT(*) FROM company WHERE id_company=:id AND state=1');$st->execute(['id'=>$idCompany]);if((int)$st->fetchColumn()===0)responderJSON(false,null,'La empresa seleccionada no está disponible.',400);}
    $id=formularioCrear($pdo,$idCompany,$name,$description!==''?$description:null,(string)currentUserId());
    auditTrailLogChanges($pdo,$idCompany,'formularios','dynamic_forms',$id,[],['name'=>$name,'description'=>$description!==''?$description:null,'state'=>1],'create',$name);
    responderJSON(true,['id_form'=>$id],'Formulario creado correctamente.',201);
}catch(PDOException $e){error_log('formularios/formularios-crear: '.$e->getMessage());responderJSON(false,null,'No se pudo crear el formulario.',500);}
