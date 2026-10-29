<?php
require_once __DIR__ . '/admin-common.php';

$context=sctAdminCompanyContext($pdo);
sctAdminCompanyRequireCenterWrite($context);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);

try {
    $service=new SctAdminCompanyManagementService();
    $data=$service->validateCenter($input);
    $id=filter_var(
        $input['id_company_center'] ?? null,
        FILTER_VALIDATE_INT
    ) ?: null;

    $saved=(new SctAdminCompanyManagementRepository($pdo))
        ->saveCenter(
            (int)$context['company_id'],
            $id,
            $data,
            (string)$context['session_user_id']
        );

    responderJSON(
        true,
        ['id_company_center'=>$saved],
        t('company_manage_center_saved')
    );
} catch (InvalidArgumentException $e) {
    responderJSON(false,null,$e->getMessage(),400);
} catch (Throwable $e) {
    error_log('admin-sede-guardar.php: '.$e->getMessage());
    responderJSON(false,null,t('company_manage_save_error'),500);
}
