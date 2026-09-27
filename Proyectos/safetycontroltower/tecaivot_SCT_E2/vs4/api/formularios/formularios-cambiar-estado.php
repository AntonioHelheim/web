<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.state');
if($_SERVER['REQUEST_METHOD']!=='POST')responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true);if(!is_array($input))responderJSON(false,null,'Solicitud inválida.',400);requireCsrfToken($input);
$idForm=filter_var($input['id_form']??null,FILTER_VALIDATE_INT);$state=filter_var($input['state']??null,FILTER_VALIDATE_INT);
if(!$idForm||!in_array($state,[0,1],true))responderJSON(false,null,'Datos no válidos.',400);
try{$form=formularioObtener($pdo,$idForm);if(!$form)responderJSON(false,null,'Formulario no encontrado.',404);if(!formularioCanEdit($pdo,$form))responderJSON(false,null,'No tienes permisos para modificar este formulario.',403);formularioCambiarEstado($pdo,$idForm,$state);auditTrailLogAction($pdo,$form['id_company']!==null?(int)$form['id_company']:null,'formularios','dynamic_forms',$idForm,'state',$form['state'],$state,'state_change',(string)$form['name']);responderJSON(true,null,$state?'Formulario reactivado correctamente.':'Formulario dado de baja correctamente.');}
catch(Throwable $e){error_log('formularios/formularios-cambiar-estado: '.$e->getMessage());responderJSON(false,null,'No se pudo cambiar el estado del formulario.',500);}
