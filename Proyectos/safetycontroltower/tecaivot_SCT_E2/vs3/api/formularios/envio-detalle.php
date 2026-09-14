<?php
require __DIR__.'/common.php';
requireLogin();
$id=filter_input(INPUT_GET,'id_submission',FILTER_VALIDATE_INT);if(!$id)responderJSON(false,null,'Envío no válido.',400);
try{
    formularioRequireSchema($pdo);$submission=formularioEnvioObtener($pdo,$id);if(!$submission)responderJSON(false,null,'Envío no encontrado.',404);
    $self=(string)$submission['id_users']===(string)currentUserId();$allowed=$self;
    if(!$allowed&&currentUserHasCapability($pdo,'dynamic_forms.manage')){$allowed=formularioIsGlobalAdmin($pdo)||(int)$submission['id_company']===formularioCurrentCompany($pdo);}if(!$allowed)responderJSON(false,null,'No tienes permisos para consultar este envío.',403);
    $answers=formularioRespuestasEnvio($pdo,$id);foreach($answers as &$a)$a['display_value']=formularioDecodeStoredValue($a);unset($a);$submission['respuestas']=$answers;responderJSON(true,$submission);
}catch(Throwable $e){if(formularioMigrationMessage($e))responderJSON(false,null,'El Motor de Formularios Dinámicos requiere aplicar su migración de Etapa 2.',503);error_log('formularios/envio-detalle: '.$e->getMessage());responderJSON(false,null,'No se pudo cargar el detalle del envío.',500);}
