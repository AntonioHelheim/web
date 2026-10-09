<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';

requireCapability($pdo, 'self_assessments.assign');
$idTest=filter_input(INPUT_GET,'id_test',FILTER_VALIDATE_INT); if(!$idTest) responderJSON(false,null,'Parámetro inválido.',400);
try{
    $test=cursoObtenerPorId($pdo,$idTest); if(!$test) responderJSON(false,null,'Autoevaluación no encontrada.',404);
    autoevaluacionAssertTest($test); autoevaluacionAssertCompanyAccess($pdo,(int)$test['id_company']);
    responderJSON(true,evaluacionListarAsignacionesPorTest($pdo,$idTest));
}catch(Throwable $e){
    if(autoevaluacionMigrationMessage($e)) responderJSON(false,null,'El módulo requiere que la migración de evaluaciones de Etapa 2 esté aplicada.',503);
    error_log('api/autoevaluaciones/asignaciones-listar.php: '.$e->getMessage()); responderJSON(false,null,'No se pudieron obtener las asignaciones.',500);
}
