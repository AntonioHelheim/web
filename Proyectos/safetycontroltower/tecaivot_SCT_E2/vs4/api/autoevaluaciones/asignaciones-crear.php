<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';

requireCapability($pdo, 'self_assessments.assign');
if($_SERVER['REQUEST_METHOD']!=='POST') responderJSON(false,null,'Método no permitido.',405);
$input=json_decode(file_get_contents('php://input'),true); if(!is_array($input)) responderJSON(false,null,'Solicitud inválida.',400); requireCsrfToken($input);
$idTest=filter_var($input['id_test']??null,FILTER_VALIDATE_INT); $idUsuario=trim((string)($input['id_users']??'')); $deadline=DateTime::createFromFormat('Y-m-d',(string)($input['deadline']??''));
if(!$idTest || $idUsuario==='' || !$deadline) responderJSON(false,null,'Usuario, autoevaluación o plazo no válidos.',400);
try{
    evaluacionRequireSchemaAsignacion($pdo);
    $test=cursoObtenerPorId($pdo,$idTest); if(!$test) responderJSON(false,null,'Autoevaluación no encontrada.',404);
    autoevaluacionAssertTest($test); autoevaluacionAssertCompanyAccess($pdo,(int)$test['id_company']);
    if((int)$test['state']!==1) responderJSON(false,null,'La autoevaluación está inactiva.',400);
    $deadlineTs=strtotime($deadline->format('Y-m-d 23:59:59')); $untilTs=!empty($test['effective_date_until'])?strtotime((string)$test['effective_date_until']):false;
    if($deadlineTs!==false && $deadlineTs<time()) responderJSON(false,null,'El plazo de la autoevaluación no puede estar vencido.',400);
    if($untilTs!==false && $deadlineTs!==false && $deadlineTs>$untilTs) responderJSON(false,null,'El plazo no puede superar la fecha de término de vigencia de la autoevaluación.',400);
    if(cursoPuntajeMaximo($pdo,$idTest)<=0) responderJSON(false,null,'Agrega al menos una pregunta antes de asignar la autoevaluación.',400);
    $stmt=$pdo->prepare('SELECT id_users,id_company FROM users WHERE id_users=:id_users AND state=1 LIMIT 1'); $stmt->execute(['id_users'=>$idUsuario]); $usuario=$stmt->fetch();
    if(!$usuario) responderJSON(false,null,'El usuario seleccionado no está disponible.',400);
    if((int)$usuario['id_company']!==(int)$test['id_company']) responderJSON(false,null,'Solo puedes asignar usuarios pertenecientes a la empresa de la autoevaluación.',403);
    if(evaluacionAsignacionPendienteExiste($pdo,$idTest,$idUsuario)) responderJSON(false,null,'Este usuario ya tiene una ejecución pendiente de esta autoevaluación.',409);
    $idAsignacion=asignacionCrear($pdo,$idTest,$idUsuario,(int)$test['id_company'],$deadline->format('Y-m-d 23:59:59'),(string)currentUserId());
    auditTrailLogAction($pdo,(int)$test['id_company'],'autoevaluaciones','users_test_assigned',$idAsignacion,'assignment',null,$idUsuario.' · '.$deadline->format('Y-m-d'),'assign',(string)$test['name']);
    responderJSON(true,['id_user_test_assigned'=>$idAsignacion],'Autoevaluación asignada correctamente.',201);
}catch(Throwable $e){
    if(autoevaluacionMigrationMessage($e)) responderJSON(false,null,'El módulo requiere que la migración de evaluaciones de Etapa 2 esté aplicada.',503);
    error_log('api/autoevaluaciones/asignaciones-crear.php: '.$e->getMessage()); responderJSON(false,null,'No se pudo asignar la autoevaluación.',500);
}
