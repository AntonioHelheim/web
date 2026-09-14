<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';

requireCapability($pdo, 'self_assessments.edit');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST;
requireCsrfToken($input);

$idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
$name = trim((string)($input['name'] ?? ''));
$description = trim((string)($input['description'] ?? ''));
$attemptsAllowed = filter_var($input['attempts_allowed'] ?? null, FILTER_VALIDATE_INT);
$approvalPercentage = filter_var($input['approval_percentage'] ?? null, FILTER_VALIDATE_INT);
$from = DateTime::createFromFormat('Y-m-d', (string)($input['effective_date_from'] ?? ''));
$until = DateTime::createFromFormat('Y-m-d', (string)($input['effective_date_until'] ?? ''));
if (!$idTest || $name==='' || $description==='') responderJSON(false,null,'Datos de autoevaluación incompletos.',400);
if (sctTextLength($name)>50 || sctTextLength($description)>255) responderJSON(false,null,'Nombre o descripción exceden el largo permitido.',400);
if ($attemptsAllowed===false || $attemptsAllowed<1 || $attemptsAllowed>20 || $approvalPercentage===false || $approvalPercentage<1 || $approvalPercentage>100) responderJSON(false,null,'Parámetros de evaluación no válidos.',400);
if (!$from || !$until || $until<$from) responderJSON(false,null,'El rango de vigencia no es válido.',400);

try {
    $test=cursoObtenerPorId($pdo,$idTest);
    if(!$test) responderJSON(false,null,'Autoevaluación no encontrada.',404);
    autoevaluacionAssertTest($test);
    autoevaluacionAssertCompanyAccess($pdo,(int)$test['id_company']);
    if(evaluacionTestTieneAsignaciones($pdo,$idTest)) responderJSON(false,null,'Esta autoevaluación ya tiene ejecuciones asignadas. Para preservar la trazabilidad, crea una nueva autoevaluación si necesitas cambiar su definición.',409);
    $stmt=$pdo->prepare('SELECT 1 FROM company_test WHERE id_company=:id_company AND type=:type AND name=:name AND state=1 AND id_test<>:id_test LIMIT 1');
    $stmt->execute(['id_company'=>(int)$test['id_company'],'type'=>AUTOEVALUACION_TIPO,'name'=>$name,'id_test'=>$idTest]);
    if($stmt->fetchColumn()) responderJSON(false,null,'Ya existe otra autoevaluación activa con ese nombre.',409);
    cursoActualizar($pdo,$idTest,[
        'name'=>$name,'description'=>$description,'attempts_allowed'=>$attemptsAllowed,
        'approval_percentage'=>$approvalPercentage,
        'effective_date_from'=>$from->format('Y-m-d 00:00:00'),
        'effective_date_until'=>$until->format('Y-m-d 23:59:59'),
    ]);
    auditTrailLogChanges($pdo, (int)$test['id_company'], 'autoevaluaciones', 'company_test', $idTest, $test, [
        'name'=>$name,'description'=>$description,'attempts_allowed'=>$attemptsAllowed,'approval_percentage'=>$approvalPercentage,
        'effective_date_from'=>$from->format('Y-m-d 00:00:00'),'effective_date_until'=>$until->format('Y-m-d 23:59:59')
    ], 'update', $name, ['name','description','attempts_allowed','approval_percentage','effective_date_from','effective_date_until']);
    responderJSON(true,['id_test'=>$idTest],'Autoevaluación actualizada.');
} catch(PDOException $e){
    error_log('api/autoevaluaciones/autoevaluaciones-editar.php: '.$e->getMessage());
    responderJSON(false,null,'No se pudo actualizar la autoevaluación.',500);
}
