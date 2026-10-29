<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.submissions');
$idForm=filter_input(INPUT_GET,'id_form',FILTER_VALIDATE_INT);if(!$idForm)responderJSON(false,null,'Formulario no válido.',400);
try{
    formularioRequireSchema($pdo);$form=formularioObtener($pdo,$idForm);if(!$form)responderJSON(false,null,'Formulario no encontrado.',404);formularioAssertVisible($pdo,$form);
    $restrict=null;if(!formularioIsGlobalAdmin($pdo))$restrict=formularioCurrentCompany($pdo);
    responderJSON(true,formularioEnviosGestion($pdo,$idForm,$restrict));
}catch(Throwable $e){if(formularioMigrationMessage($e))responderJSON(false,null,'El Motor de Formularios Dinámicos requiere aplicar su migración de Etapa 2.',503);error_log('formularios/envios-listar: '.$e->getMessage());responderJSON(false,null,'No se pudieron cargar las respuestas registradas.',500);}
