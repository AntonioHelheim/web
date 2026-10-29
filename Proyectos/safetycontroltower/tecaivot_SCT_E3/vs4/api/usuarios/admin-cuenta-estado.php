<?php
require_once __DIR__ . '/admin-directorio-common.php';
$context=sctAdminDirectoryContext($pdo);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);
$targetId=trim((string)($input['id_users'] ?? ''));
$state=filter_var($input['state'] ?? null,FILTER_VALIDATE_INT);
if ($targetId==='' || !in_array($state,[0,1],true)) responderJSON(false,null,t('admin_users_err_request'),400);
$companyId=(int)$context['company_id'];
$repo=sctAdminDirectoryRepo($pdo);
$target=$repo->profile($targetId,$companyId);
if (!$target) responderJSON(false,null,t('admin_users_err_not_found'),404);
$targetLevel=isset($target['access_level']) ? (int)$target['access_level'] : null;
if (!sctDirectoryTargetAllowed($pdo,$context,$target)) {
    responderJSON(false,null,t('jefatura_scope_forbidden'),403);
}
if (strcasecmp($targetId,(string)$context['session_user_id'])===0) responderJSON(false,null,t('admin_users_err_self_state'),400);
try {
    $stmt=$pdo->prepare('UPDATE users SET state=:state,last_update=NOW() WHERE id_users=:id_users AND id_company=:id_company LIMIT 1');
    $stmt->execute(['state'=>$state,'id_users'=>$targetId,'id_company'=>$companyId]);
    responderJSON(true,['state'=>$state],$state===1 ? t('admin_users_state_activated') : t('admin_users_state_deactivated'));
} catch (Throwable $e) {
    error_log('admin-cuenta-estado.php: '.$e->getMessage());
    responderJSON(false,null,t('admin_users_state_error'),500);
}
