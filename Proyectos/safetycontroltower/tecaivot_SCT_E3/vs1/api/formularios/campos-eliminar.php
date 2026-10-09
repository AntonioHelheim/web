<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.fields');
if($_SERVER['REQUEST_METHOD']!=='POST')responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true);if(!is_array($input))responderJSON(false,null,'Solicitud inválida.',400);requireCsrfToken($input);
$idField=filter_var($input['id_field']??null,FILTER_VALIDATE_INT);if(!$idField)responderJSON(false,null,'Campo no válido.',400);
try{$field=formularioCampoObtener($pdo,$idField);if(!$field)responderJSON(false,null,'Campo no encontrado.',404);$form=formularioObtener($pdo,(int)$field['id_form']);if(!$form)responderJSON(false,null,'Formulario no encontrado.',404);formularioAssertEditable($pdo,$form);formularioCampoEliminar($pdo,$idField);auditTrailLogAction($pdo,$form['id_company']!==null?(int)$form['id_company']:null,'formularios','dynamic_form_fields',$idField,'field',(string)($field['label']??$idField),null,'delete',(string)$form['name']);responderJSON(true,null,'Campo eliminado correctamente.');}
catch(Throwable $e){if(formularioMigrationMessage($e))responderJSON(false,null,'Debes aplicar la migración del Motor de Formularios Dinámicos.',503);error_log('formularios/campos-eliminar: '.$e->getMessage());responderJSON(false,null,'No se pudo eliminar el campo.',500);}
