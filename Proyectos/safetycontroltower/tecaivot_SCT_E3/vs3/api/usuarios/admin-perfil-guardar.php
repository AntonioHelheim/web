<?php
require_once __DIR__ . '/admin-directorio-common.php';

$context=sctAdminDirectoryContext($pdo);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);

$targetId=trim((string)($input['id_users'] ?? ''));
if ($targetId==='') responderJSON(false,null,'Usuario objetivo inválido.',400);

$companyId=(int)$context['company_id'];
$repo=sctAdminDirectoryRepo($pdo);
$target=$repo->profile($targetId,$companyId);
if (!$target) responderJSON(false,null,'Usuario no encontrado en tu empresa.',404);

$targetLevel=isset($target['access_level']) ? (int)$target['access_level'] : null;
if (!sctDirectoryTargetAllowed($pdo,$context,$target)) {
    responderJSON(
        false,
        null,
        t('jefatura_scope_forbidden'),
        403
    );
}

try {
    $service=new SctAdminUserProfileService($pdo,$repo);
    $data=$service->validate($input,$companyId);
    $actor=(string)$context['session_user_id'];

    $supervision=new SctAdminSupervisionRepository($pdo);
    $management=new SctAdminManagementRepository($pdo);

    if (!$management->schemaReady()) {
        responderJSON(
            false,
            null,
            t('admin_users_migration_management_required'),
            503
        );
    }

    $workerId=(int)($target['id_worker'] ?? 0);
    if ($workerId<=0) {
        throw new InvalidArgumentException(
            t('admin_users_err_worker')
        );
    }

    $oldPosition=(string)($target['position'] ?? '');
    $oldLevel=(int)($target['access_level'] ?? 0);
    $oldJefaturas=$supervision->currentForWorker(
        $companyId,
        $workerId
    );
    $oldJefatura=(string)(
        $oldJefaturas[0]['id_supervisor_users'] ?? ''
    );
    $oldGerencias=$management->currentForWorker(
        $companyId,
        $workerId
    );
    $oldGerencia=(string)(
        $oldGerencias[0]['id_manager_users'] ?? ''
    );

    $roleId=filter_var(
        $input['id_role_group'] ?? null,
        FILTER_VALIDATE_INT
    ) ?: 0;

    $creation=new SctAdminPersonCreationService(
        $pdo,
        $repo,
        $supervision,
        $management
    );

    $newLevel=$oldLevel;

    if (strcasecmp($targetId,$actor)!==0) {
        if ($roleId<=0) {
            throw new InvalidArgumentException(
                t('admin_users_err_role')
            );
        }

        foreach ($creation->availableRoles($companyId,(string)$context['directory_role']) as $role) {
            if ((int)$role['id_role_group']===$roleId) {
                $newLevel=(int)$role['access_level'];
                break;
            }
        }

        if ($newLevel<=0) {
            throw new InvalidArgumentException(
                t('admin_users_err_role')
            );
        }
    }

    $requiresJefatura=($newLevel===5);
    $requiresGerencia=in_array($newLevel,[4,5],true);

    $jefaturaId=trim((string)($input['id_jefatura'] ?? ''));
    $gerenciaId=trim((string)($input['id_gerencia'] ?? ''));

    if ((string)$context['directory_role']==='jefatura') {
        $jefaturaId=(string)$context['session_user_id'];
        $requiresJefatura=true;
    }

    if (
        $requiresJefatura
        && (
            $jefaturaId===''
            || !$supervision->validJefatura(
                $companyId,
                $jefaturaId
            )
        )
    ) {
        throw new InvalidArgumentException(
            t('admin_users_err_jefatura')
        );
    }

    if (
        $requiresGerencia
        && (
            $gerenciaId===''
            || !$management->validGerencia(
                $companyId,
                $gerenciaId
            )
        )
    ) {
        throw new InvalidArgumentException(
            t('admin_users_err_gerencia')
        );
    }

    $repo->saveProfile(
        $targetId,
        $companyId,
        $data,
        $actor
    );

    if ($requiresJefatura) {
        $supervision->replacePrimary(
            $companyId,
            $workerId,
            $jefaturaId,
            $actor
        );
    }

    if ($requiresGerencia) {
        $management->replacePrimary(
            $companyId,
            $workerId,
            $gerenciaId,
            $actor
        );
    } else {
        $management->clearPrimary(
            $companyId,
            $workerId
        );
    }

    if (strcasecmp($targetId,$actor)!==0) {
        $creation->changeUserRole(
            $targetId,
            $companyId,
            $roleId,
            $actor,
            (string)$context['directory_role']
        );
    }

    if (strcasecmp($oldPosition,(string)$data['position'])!==0) {
        SctAdminUserActionLog::write(
            $pdo,
            $actor,
            SctAdminUserActionLog::ACTION_POSITION,
            $workerId
        );
    }

    if ($newLevel!==$oldLevel) {
        SctAdminUserActionLog::write(
            $pdo,
            $actor,
            SctAdminUserActionLog::ACTION_ROLE,
            $workerId
        );
    }

    if (
        $requiresJefatura
        && strcasecmp($oldJefatura,$jefaturaId)!==0
    ) {
        SctAdminUserActionLog::write(
            $pdo,
            $actor,
            SctAdminUserActionLog::ACTION_JEFATURA,
            $workerId
        );
    }

    if (
        $requiresGerencia
        && strcasecmp($oldGerencia,$gerenciaId)!==0
    ) {
        SctAdminUserActionLog::write(
            $pdo,
            $actor,
            SctAdminUserActionLog::ACTION_GERENCIA,
            $workerId
        );
    }

    responderJSON(
        true,
        null,
        t('admin_users_profile_saved')
    );
} catch (InvalidArgumentException $e) {
    responderJSON(false,null,$e->getMessage(),400);
} catch (Throwable $e) {
    error_log('admin-perfil-guardar.php: '.$e->getMessage());
    responderJSON(false,null,'No fue posible actualizar el perfil.',500);
}
