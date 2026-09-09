<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';

requireCapability($pdo, 'self_assessments.execute');
$idAsignacion=filter_input(INPUT_GET,'id_asignacion',FILTER_VALIDATE_INT); if(!$idAsignacion) responderJSON(false,null,'Asignación inválida.',400);
try{
    evaluacionRequireSchemaAsignacion($pdo);
    $asignacion=asignacionObtenerPorId($pdo,$idAsignacion);
    if(!$asignacion || (string)$asignacion['id_users']!==(string)currentUserId()) responderJSON(false,null,'Asignación no encontrada.',404);
    if((int)$asignacion['state']!==ASIGNACION_PENDIENTE) responderJSON(false,null,'Esta autoevaluación ya fue finalizada.',400);
    if(!empty($asignacion['deadline']) && strtotime((string)$asignacion['deadline'])<time()) responderJSON(false,null,'El plazo de esta autoevaluación ya venció.',400);
    $test=cursoObtenerPorId($pdo,(int)$asignacion['id_test']);
    if(!$test || (int)$test['state']!==1) responderJSON(false,null,'La autoevaluación ya no está disponible.',400);
    autoevaluacionAssertTest($test);
    if(!autoevaluacionTestVigente($test)) responderJSON(false,null,'La autoevaluación está fuera de su período de vigencia.',400);
    $intentos=evaluacionIntentosUsadosPorAsignacion($pdo,$idAsignacion);
    if($intentos>=(int)$test['attempts_allowed']) responderJSON(false,null,'No quedan intentos disponibles.',400);
    $preguntasBase=cursoListarPreguntas($pdo,(int)$test['id_test']); if(!$preguntasBase) responderJSON(false,null,'La autoevaluación aún no tiene preguntas configuradas.',400);
    $preguntas=[];
    foreach($preguntasBase as $p){
        $detalle=preguntaObtenerPorId($pdo,(int)$p['id_question']); if(!$detalle) continue;
        $opciones=[]; foreach($detalle['opciones'] as $o){$opciones[]=['id_questions_options'=>(int)$o['id_questions_options'],'text_option'=>(string)$o['text_option']];}
        $preguntas[]=['id_rel'=>(int)$p['id_rel'],'id_question'=>(int)$p['id_question'],'question'=>(string)$detalle['question'],'opciones'=>$opciones];
    }
    responderJSON(true,[
        'id_test'=>(int)$test['id_test'],'name'=>(string)$test['name'],'description'=>(string)$test['description'],
        'approval_percentage'=>(int)$test['approval_percentage'],'intentos_usados'=>$intentos,
        'attempts_allowed'=>(int)$test['attempts_allowed'],'preguntas'=>$preguntas,
    ]);
}catch(Throwable $e){
    if(autoevaluacionMigrationMessage($e)) responderJSON(false,null,'El módulo requiere que la migración de evaluaciones de Etapa 2 esté aplicada.',503);
    error_log('api/autoevaluaciones/rendir-detalle.php: '.$e->getMessage()); responderJSON(false,null,'No se pudo cargar la autoevaluación.',500);
}
