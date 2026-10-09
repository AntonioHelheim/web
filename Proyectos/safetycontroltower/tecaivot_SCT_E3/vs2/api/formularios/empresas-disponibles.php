<?php
require __DIR__.'/common.php';
formularioRequireGestionApi($pdo);
if(!formularioIsGlobalAdmin($pdo)) responderJSON(true,[]);
try{
    require_once __DIR__.'/../../lib/repositorios/EmpresaRepository.php';
    responderJSON(true,empresaListar($pdo,null,true));
}catch(PDOException $e){error_log('formularios/empresas-disponibles: '.$e->getMessage());responderJSON(false,null,'No se pudieron obtener las empresas.',500);}
