<?php
require __DIR__.'/common.php';
requireLogin();
$idForm=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);if(!$idForm)responderJSON(false,null,'Formulario no válido.',400);
try{
    $form=formularioObtener($pdo,$idForm);if(!$form)responderJSON(false,null,'Formulario no encontrado.',404);formularioAssertVisible($pdo,$form);
    $form['campos']=formularioCampos($pdo,$idForm);$form['editable']=formularioCanEdit($pdo,$form);$form['locked']=formularioSchemaReady($pdo)&&formularioTieneEnvios($pdo,$idForm);
    responderJSON(true,$form);
}catch(Throwable $e){error_log('formularios/formularios-detalle: '.$e->getMessage());responderJSON(false,null,'No se pudo cargar el formulario.',500);}
