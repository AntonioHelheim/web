<?php
require_once __DIR__ . '/admin-directorio-common.php';
$context=sctAdminDirectoryContext($pdo);
$companyId=(int)$context['company_id'];
try {
    $directory=sctAdminDirectoryRepo($pdo);
    $supervision=new SctAdminSupervisionRepository($pdo);

    /*
     * E3 VS2:
     * La carga de Jefaturas, Gerencias y Grupos de perfil NO depende de
     * user_supervision_assignments. Esa tabla se valida recién al guardar
     * una relación de supervisión.
     *
     * No bloquear este endpoint con una comprobación previa del esquema: hacerlo dejaba vacíos
     * todos los catálogos organizacionales aunque users/users_role estuvieran
     * completamente operativos.
     */
    $service=new SctAdminPersonCreationService(
        $pdo,
        $directory,
        $supervision
    );
    responderJSON(
        true,
        $service->options(
            $companyId,
            (string)$context['directory_role'],
            (string)$context['session_user_id']
        )
    );
} catch (Throwable $e) {
    error_log(
        'admin-nuevo-opciones.php [E3 VS2]: '
        . get_class($e)
        . ' - '
        . $e->getMessage()
    );
    responderJSON(false,null,t('admin_users_create_options_error'),500);
}
