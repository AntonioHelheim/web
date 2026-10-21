<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.edit');
if($_SERVER['REQUEST_METHOD']!=='POST')responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true);if(!is_array($input))responderJSON(false,null,'Solicitud inválida.',400);requireCsrfToken($input);
$idForm=filter_var($input['id_form']??null,FILTER_VALIDATE_INT);$name=trim((string)($input['name']??''));$description=trim((string)($input['description']??''));
if(!$idForm||$name===''||sctTextLength($name)>150)responderJSON(false,null,'Datos del formulario no válidos.',400);
if(sctTextLength($description)>5000)responderJSON(false,null,'La descripción es demasiado extensa.',400);
try{$form=formularioObtener($pdo,$idForm);if(!$form)responderJSON(false,null,'Formulario no encontrado.',404);formularioAssertEditable($pdo,$form);formularioEditar($pdo,$idForm,$name,$description!==''?$description:null);auditTrailLogChanges($pdo,$form['id_company']!==null?(int)$form['id_company']:null,'formularios','dynamic_forms',$idForm,$form,array_merge($form,['name'=>$name,'description'=>$description!==''?$description:null]),'update',$name,['name','description']);responderJSON(true,null,'Formulario actualizado correctamente.');}
catch(Throwable $e){if(formularioMigrationMessage($e))responderJSON(false,null,'Debes aplicar la migración del Motor de Formularios Dinámicos.',503);error_log('formularios/formularios-editar: '.$e->getMessage());responderJSON(false,null,'No se pudo actualizar el formulario.',500);}
