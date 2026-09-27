<?php
require __DIR__.'/common.php';
formularioRequireGestionApi($pdo);
if(!formularioIsGlobalAdmin($pdo)) responderJSON(true,[]);
try{
    $stmt=$pdo->query('SELECT id_company,razon_social,state FROM company ORDER BY state DESC,razon_social ASC');
    responderJSON(true,$stmt->fetchAll());
}catch(PDOException $e){error_log('formularios/empresas-disponibles: '.$e->getMessage());responderJSON(false,null,'No se pudieron obtener las empresas.',500);}
