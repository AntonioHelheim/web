<?php
require_once __DIR__ . '/admin-common.php';

$context=sctAdminCompanyContext($pdo);
$companyId=(int)$context['company_id'];

try {
    $repo=new SctAdminCompanyManagementRepository($pdo);

    responderJSON(true,[
        'company'=>$repo->company($companyId),
        'centers'=>$repo->centers($companyId),
        'projects'=>$repo->projects($companyId),
        'scope_catalog'=>$repo->scopeCatalog($companyId),
        'profiles'=>$repo->profileSummary($companyId),
    ]);
} catch (Throwable $e) {
    error_log('admin-datos.php: '.$e->getMessage());
    responderJSON(false,null,t('company_manage_load_error'),500);
}
