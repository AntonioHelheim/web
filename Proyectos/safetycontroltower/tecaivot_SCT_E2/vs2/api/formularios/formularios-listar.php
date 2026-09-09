<?php
require __DIR__.'/common.php';
requireCapability($pdo, 'dynamic_forms.view');
$idRequested=filter_input(INPUT_GET,'id_company',FILTER_VALIDATE_INT);
$idCompany=formularioResolveManagementCompany($pdo,$idRequested?:null);
try{
    $items=formularioListarGestion($pdo,$idCompany,formularioIsGlobalAdmin($pdo));
    $own=formularioCurrentCompany($pdo);
    foreach($items as &$item){
        $item['editable']=formularioCanEdit($pdo,$item);
        $item['scope_label']=$item['id_company']===null?'global':((int)$item['id_company']===$own?'own':'company');
        $item['locked']=formularioSchemaReady($pdo) && (int)($item['submission_count']??0)>0;
    }
    unset($item);
    responderJSON(true,$items);
}catch(Throwable $e){error_log('formularios/formularios-listar: '.$e->getMessage());responderJSON(false,null,'No se pudieron obtener los formularios.',500);}
