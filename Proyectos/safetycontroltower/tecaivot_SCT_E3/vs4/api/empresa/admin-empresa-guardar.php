<?php
require_once __DIR__ . '/admin-common.php';

$context=sctAdminCompanyContext($pdo);
sctAdminCompanyRequireCompanyWrite($context);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);

try {
    $service=new SctAdminCompanyManagementService();
    $data=$service->validateCompany($input);

    (new SctAdminCompanyManagementRepository($pdo))
        ->saveCompany(
            (int)$context['company_id'],
            $data
        );

    responderJSON(true,null,t('company_manage_company_saved'));
} catch (InvalidArgumentException $e) {
    responderJSON(false,null,$e->getMessage(),400);
} catch (Throwable $e) {
    error_log('admin-empresa-guardar.php: '.$e->getMessage());
    responderJSON(false,null,t('company_manage_save_error'),500);
}
