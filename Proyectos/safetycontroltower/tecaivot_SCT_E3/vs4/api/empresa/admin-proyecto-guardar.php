<?php
require_once __DIR__ . '/admin-common.php';

$context=sctAdminCompanyContext($pdo);
sctAdminCompanyRequireScopeWrite($context);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);

try {
    $service=new SctAdminCompanyManagementService();
    $data=$service->validateProject($input);
    $id=filter_var(
        $input['id_project'] ?? null,
        FILTER_VALIDATE_INT
    ) ?: null;

    $saved=(new SctAdminCompanyManagementRepository($pdo))
        ->saveProject(
            (int)$context['company_id'],
            $id,
            $data,
            (string)$context['session_user_id']
        );

    responderJSON(
        true,
        ['id_project'=>$saved],
        t('company_manage_project_saved')
    );
} catch (InvalidArgumentException $e) {
    responderJSON(false,null,$e->getMessage(),400);
} catch (Throwable $e) {
    error_log('admin-proyecto-guardar.php: '.$e->getMessage());
    responderJSON(false,null,t('company_manage_save_error'),500);
}
