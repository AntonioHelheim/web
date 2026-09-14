<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';

requireCapability($pdo, 'self_assessments.questions');
if($_SERVER['REQUEST_METHOD']!=='POST') responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true); if(!is_array($input)) responderJSON(false,null,'Solicitud inválida.',400); requireCsrfToken($input);
$idRel=filter_var($input['id_rel']??null,FILTER_VALIDATE_INT); if(!$idRel) responderJSON(false,null,'Relación inválida.',400);
try{
    $rel=cursoObtenerRelPorId($pdo,$idRel); if(!$rel) responderJSON(false,null,'Pregunta no encontrada en la autoevaluación.',404);
    $test=cursoObtenerPorId($pdo,(int)$rel['id_test']); if(!$test) responderJSON(false,null,'Autoevaluación no encontrada.',404);
    autoevaluacionAssertTest($test); autoevaluacionAssertCompanyAccess($pdo,(int)$test['id_company']);
    if(evaluacionTestTieneAsignaciones($pdo,(int)$test['id_test'])) responderJSON(false,null,'No puedes modificar las preguntas de una autoevaluación que ya tiene ejecuciones asignadas.',409);
    if(cursoPreguntaTieneRespuestas($pdo,$idRel)) responderJSON(false,null,'No se puede quitar una pregunta que ya tiene respuestas registradas.',409);
    cursoQuitarPregunta($pdo,$idRel); auditTrailLogAction($pdo,(int)$test['id_company'],'autoevaluaciones','company_test_rel_questions',$idRel,'id_question',$rel['id_question']??null,null,'unassign',(string)$test['name']); responderJSON(true,null,'Pregunta quitada.');
}catch(PDOException $e){error_log('api/autoevaluaciones/autoevaluacion-preguntas-quitar.php: '.$e->getMessage()); responderJSON(false,null,'No se pudo quitar la pregunta.',500);}
