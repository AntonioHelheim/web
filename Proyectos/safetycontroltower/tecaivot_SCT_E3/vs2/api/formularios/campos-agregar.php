<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.fields');
if($_SERVER['REQUEST_METHOD']!=='POST')responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true);if(!is_array($input))responderJSON(false,null,'Solicitud inválida.',400);requireCsrfToken($input);
$idForm=filter_var($input['id_form']??null,FILTER_VALIDATE_INT);if(!$idForm)responderJSON(false,null,'Formulario no válido.',400);$f=formularioValidateFieldPayload($input);
try{$form=formularioObtener($pdo,$idForm);if(!$form)responderJSON(false,null,'Formulario no encontrado.',404);formularioAssertEditable($pdo,$form);$id=formularioCampoCrear($pdo,$idForm,$f['label'],$f['fieldType'],$f['options'],$f['required'],$f['sortOrder']);auditTrailLogChanges($pdo,$form['id_company']!==null?(int)$form['id_company']:null,'formularios','dynamic_form_fields',$id,[],['id_form'=>$idForm,'label'=>$f['label'],'field_type'=>$f['fieldType'],'options'=>$f['options'],'is_required'=>$f['required'],'sort_order'=>$f['sortOrder']],'create',(string)$form['name'].' · '.$f['label']);responderJSON(true,['id_field'=>$id],'Campo agregado correctamente.',201);}
catch(Throwable $e){if(formularioMigrationMessage($e))responderJSON(false,null,'Debes aplicar la migración del Motor de Formularios Dinámicos.',503);error_log('formularios/campos-agregar: '.$e->getMessage());responderJSON(false,null,'No se pudo agregar el campo.',500);}
