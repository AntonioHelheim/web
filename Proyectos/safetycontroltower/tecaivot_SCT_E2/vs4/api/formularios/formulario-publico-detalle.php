<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.view');
$idForm=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);if(!$idForm)responderJSON(false,null,'Formulario no válido.',400);
try{
    formularioRequireSchema($pdo);
    $form=formularioObtener($pdo,$idForm);if(!$form || (int)$form['state']!==1)responderJSON(false,null,'Formulario no disponible.',404);formularioAssertVisible($pdo,$form);
    $fields=formularioCampos($pdo,$idForm);if(!$fields)responderJSON(false,null,'Este formulario todavía no tiene campos configurados.',400);
    $form['campos']=$fields;responderJSON(true,$form);
}catch(Throwable $e){if(formularioMigrationMessage($e))responderJSON(false,null,'El Motor de Formularios Dinámicos requiere aplicar su migración de Etapa 2.',503);error_log('formularios/formulario-publico-detalle: '.$e->getMessage());responderJSON(false,null,'No se pudo cargar el formulario.',500);}
