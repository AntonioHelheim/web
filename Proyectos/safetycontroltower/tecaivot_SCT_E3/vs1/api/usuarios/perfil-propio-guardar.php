<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/SaludRepository.php';
requireLogin();
$input=usuariosReadJsonInput();usuariosRequireCsrf($input);
$id=currentUserId();$before=currentUserProfile($pdo);
if(!$id||!$before)responderJSON(false,null,t('health_save_error'),404);
$name=trim((string)($input['name']??''));$lastname=trim((string)($input['lastname']??''));$language=strtolower(trim((string)($input['language']??'')));$phone=trim((string)($input['phone']??''));
if($name===''||$lastname===''||!in_array($language,['es','en','pt','fr','zh'],true)||($phone!==''&&!healthValidPhone($phone)))responderJSON(false,null,t('health_required_error'),422);
try{
 $pdo->beginTransaction();
 $repo=usuariosRepository($pdo);$repo->updateBasicProfile($id,sctTextSubstr($name,0,50),sctTextSubstr($lastname,0,50),$language);
 if(!empty($before['id_worker'])){$repo->updateLinkedWorker((int)$before['id_worker'],(int)$before['id_company'],sctTextSubstr($name,0,100),sctTextSubstr($lastname,0,100),$phone!==''?$phone:null);}
 auditTrailLogChanges($pdo,(int)$before['id_company'],'usuarios','users',$id,$before,['name'=>$name,'lastname'=>$lastname,'language'=>$language],'update',$id,['name','lastname','language'],$id);
 $pdo->commit();responderJSON(true,null,t('profile_saved'));
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('perfil-propio-guardar.php: '.$e->getMessage());responderJSON(false,null,t('health_save_error'),500);}
