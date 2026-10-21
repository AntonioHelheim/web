<?php
require_once __DIR__ . '/admin-directorio-common.php';
$context=sctAdminDirectoryContext($pdo);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);
$companyId=(int)$context['company_id'];
$actor=(string)$context['session_user_id'];
try {
    $directory=sctAdminDirectoryRepo($pdo);
    $supervision=new SctAdminSupervisionRepository($pdo);

    // La existencia real de la relación se valida ejecutando el SQL.
    // No bloquear por information_schema/DATABASE(), que puede producir
    // falsos negativos aun con la migración aplicada.
    $service=new SctAdminPersonCreationService(
        $pdo,
        $directory,
        $supervision
    );
    /*
     * Jefatura:
     * - validate() conserva sus restricciones de rol/permisos.
     * - create() utiliza el mismo flujo transaccional validado para
     *   Administrador Cliente.
     */
    $data=$service->validate(
        $input,
        $companyId,
        (string)$context['directory_role'],
        (string)$context['session_user_id']
    );

    $created=$service->createForActor(
        $companyId,
        $data,
        $actor,
        (string)$context['directory_role']
    );

    $message=$data['record_type']==='user'
        ? t('admin_users_created_user')
        : t('admin_users_created_worker');

    if (!empty($created['warnings'])) {
        $message.=' '.t(
            'admin_users_created_org_warning'
        );
    }

    responderJSON(
        true,
        $created,
        $message
    );
} catch (InvalidArgumentException $e) {
    responderJSON(false,null,$e->getMessage(),400);
} catch (Throwable $e) {
    error_log(
        'admin-nuevo-guardar.php [E3 VS2]: '
        . get_class($e)
        . ' - '
        . $e->getMessage()
    );

    $message=t('admin_users_create_error');

    if (str_starts_with($e->getMessage(),'CREATE_CORE_')) {
        $stage=(string)preg_replace(
            '/^CREATE_CORE_([A-Z_]+):.*$/',
            '$1',
            $e->getMessage()
        );

        $message=t('admin_users_create_stage_error')
            . ' '
            . $stage;
    }

    responderJSON(false,null,$message,500);
}
