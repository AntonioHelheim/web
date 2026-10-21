<?php
require_once __DIR__ . '/admin-common.php';

$context=sctAdminCompanyContext($pdo);
sctAdminCompanyRequireScopeWrite($context);

$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);

$projectId=filter_var(
    $input['id_project'] ?? null,
    FILTER_VALIDATE_INT
) ?: 0;

try {
    $affected=(new SctAdminCompanyManagementRepository($pdo))
        ->deactivateProject(
            (int)$context['company_id'],
            $projectId,
            (string)$context['session_user_id']
        );

    responderJSON(
        true,
        ['affected_projects'=>$affected],
        t('company_manage_project_deleted')
    );
} catch (InvalidArgumentException $e) {
    responderJSON(false,null,$e->getMessage(),400);
} catch (Throwable $e) {
    error_log('admin-proyecto-eliminar.php: '.$e->getMessage());
    responderJSON(false,null,t('company_manage_save_error'),500);
}
