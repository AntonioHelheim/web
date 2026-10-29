<?php
require_once __DIR__ . '/admin-common.php';

$context=sctAdminCompanyContext($pdo);
sctAdminCompanyRequireScopeWrite($context);

$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);

$companyId=(int)$context['company_id'];
$actor=(string)$context['session_user_id'];
$action=strtolower(trim((string)($input['action'] ?? '')));
$gbu=trim((string)($input['gbu_code'] ?? ''));
$site=trim((string)($input['site_name'] ?? ''));

try {
    $repo=new SctAdminCompanyManagementRepository($pdo);

    if ($action==='rename') {
        $newSite=trim((string)($input['new_site_name'] ?? ''));

        $affected=$repo->renameSite(
            $companyId,
            $gbu,
            $site,
            $newSite,
            $actor
        );

        responderJSON(
            true,
            ['affected_projects'=>$affected],
            t('company_manage_site_renamed')
        );
    }

    if ($action==='deactivate') {
        $affected=$repo->deactivateSite(
            $companyId,
            $gbu,
            $site,
            $actor
        );

        responderJSON(
            true,
            ['affected_projects'=>$affected],
            t('company_manage_site_deleted')
        );
    }

    responderJSON(
        false,
        null,
        t('company_manage_err_action'),
        400
    );
} catch (InvalidArgumentException $e) {
    responderJSON(false,null,$e->getMessage(),400);
} catch (Throwable $e) {
    error_log('admin-sitio-accion.php: '.$e->getMessage());
    responderJSON(false,null,t('company_manage_save_error'),500);
}
