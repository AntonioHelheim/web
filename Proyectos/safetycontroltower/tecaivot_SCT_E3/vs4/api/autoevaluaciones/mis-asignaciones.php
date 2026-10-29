<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';

requireCapability($pdo, 'self_assessments.execute');
try{
    $asignaciones=evaluacionListarAsignacionesUsuarioPorTipo($pdo,(string)currentUserId(),AUTOEVALUACION_TIPO);
    foreach($asignaciones as &$a){
        $ultimo=evaluacionResultadoUltimoIntentoPorAsignacion($pdo,(int)$a['id_user_test_assigned'],(int)$a['id_test']);
        $a['porcentaje_ultimo']=$ultimo['porcentaje']??null;
    }
    unset($a);
    responderJSON(true,$asignaciones);
}catch(Throwable $e){
    if(autoevaluacionMigrationMessage($e)) responderJSON(false,null,'El módulo requiere que la migración de evaluaciones de Etapa 2 esté aplicada.',503);
    error_log('api/autoevaluaciones/mis-asignaciones.php: '.$e->getMessage()); responderJSON(false,null,'No se pudieron obtener tus autoevaluaciones.',500);
}
