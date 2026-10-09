<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.view');
try{
    formularioRequireSchema($pdo);
    $company=formularioCurrentCompany($pdo);
    responderJSON(true,[
        'disponibles'=>formularioDisponiblesUsuario($pdo,$company),
        'historial'=>formularioHistorialUsuario($pdo,(string)currentUserId()),
    ]);
}catch(Throwable $e){if(formularioMigrationMessage($e))responderJSON(false,null,'El Motor de Formularios Dinámicos requiere aplicar su migración de Etapa 2.',503);error_log('formularios/mis-formularios-listar: '.$e->getMessage());responderJSON(false,null,'No se pudieron cargar tus formularios.',500);}
