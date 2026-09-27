<?php
require __DIR__.'/common.php';
requireLoginPage('../../acceso-denegado.php');
$idAnswer=filter_input(INPUT_GET,'id_answer',FILTER_VALIDATE_INT);if(!$idAnswer){http_response_code(400);exit('Archivo no válido.');}
try{
    formularioRequireSchema($pdo);
    $stmt=$pdo->prepare('SELECT a.id_answer,a.file_path,a.original_name,a.mime_type,s.id_users,s.id_company FROM dynamic_form_answers a INNER JOIN dynamic_form_submissions s ON s.id_submission=a.id_submission WHERE a.id_answer=:id_answer LIMIT 1');$stmt->execute(['id_answer'=>$idAnswer]);$row=$stmt->fetch();if(!$row||empty($row['file_path'])){http_response_code(404);exit('Archivo no encontrado.');}
    $allowed=(string)$row['id_users']===(string)currentUserId();if(!$allowed&&currentUserHasCapability($pdo,'dynamic_forms.manage'))$allowed=formularioIsGlobalAdmin($pdo)||(int)$row['id_company']===formularioCurrentCompany($pdo);if(!$allowed){http_response_code(403);exit('Acceso denegado.');}
    $base=realpath(__DIR__.'/../../uploads/formularios');$path=realpath(__DIR__.'/../../'.ltrim((string)$row['file_path'],'/'));if(!$base||!$path||strncmp($path,$base.DIRECTORY_SEPARATOR,strlen($base.DIRECTORY_SEPARATOR))!==0||!is_file($path)){http_response_code(404);exit('Archivo no encontrado.');}
    $name=preg_replace('/[\r\n"]+/','_',basename((string)($row['original_name']?:basename($path))));header('Content-Type: '.((string)($row['mime_type']?:'application/octet-stream')));header('Content-Length: '.filesize($path));header('Content-Disposition: attachment; filename="'.$name.'"');header('X-Content-Type-Options: nosniff');readfile($path);exit;
}catch(Throwable $e){error_log('formularios/archivo-descargar: '.$e->getMessage());http_response_code(500);exit('No se pudo descargar el archivo.');}
