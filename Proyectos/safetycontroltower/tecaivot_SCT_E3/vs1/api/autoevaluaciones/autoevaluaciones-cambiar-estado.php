<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'self_assessments.state');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true); if(!is_array($input))$input=$_POST; requireCsrfToken($input);
$idTest=filter_var($input['id_test']??null,FILTER_VALIDATE_INT); $state=filter_var($input['state']??null,FILTER_VALIDATE_INT);
if(!$idTest || !in_array($state,[0,1],true)) responderJSON(false,null,'Parámetros inválidos.',400);
try{
    $test=cursoObtenerPorId($pdo,$idTest); if(!$test) responderJSON(false,null,'Autoevaluación no encontrada.',404);
    autoevaluacionAssertTest($test); autoevaluacionAssertCompanyAccess($pdo,(int)$test['id_company']);
    cursoCambiarEstado($pdo,$idTest,$state);
    auditTrailLogAction($pdo,(int)$test['id_company'],'autoevaluaciones','company_test',$idTest,'state',$test['state'],$state,'state_change',(string)$test['name']);
    responderJSON(true,['state'=>$state],$state===1?'Autoevaluación reactivada.':'Autoevaluación desactivada.');
}catch(PDOException $e){error_log('api/autoevaluaciones/autoevaluaciones-cambiar-estado.php: '.$e->getMessage()); responderJSON(false,null,'No se pudo cambiar el estado.',500);}
