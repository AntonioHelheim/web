<?php
require_once __DIR__ . '/admin-directorio-common.php';

$context=sctAdminDirectoryContext($pdo);
$companyId=(int)$context['company_id'];
$repo=sctAdminDirectoryRepo($pdo);

$mode=strtolower(trim((string)($_GET['mode'] ?? 'search')));

try {
    /*
     * Los selectores muestran todos los GBU/Sitios/Proyectos activos de la
     * misma empresa. La restricción de Jefatura se aplica a los resultados,
     * no al catálogo organizacional.
     */
    $scopeCatalog=new SctAdminUserScopeCatalogService($pdo);
    $filters=$scopeCatalog->activeCompanyCatalog($companyId);

    if ($mode === 'filters') {
        responderJSON(true,[
            'filters'=>$filters,
            'users'=>[],
        ]);
    }

    $gbu=trim((string)($_GET['gbu'] ?? ''));
    $site=trim((string)($_GET['site'] ?? ''));
    $projectId=filter_input(
        INPUT_GET,
        'id_project',
        FILTER_VALIDATE_INT
    ) ?: null;
    $query=trim((string)($_GET['q'] ?? ''));

    if (
        $gbu===''
        && $site===''
        && !$projectId
        && (string)$context['directory_role']!=='jefatura'
    ) {
        responderJSON(
            false,
            ['filters'=>$filters,'users'=>[]],
            t('admin_users_scope_required'),
            400
        );
    }

    /*
     * E3 VS3 — el directorio usa el mismo motor para los perfiles de
     * gestión autorizados, pero recibe los niveles permitidos por política.
     * Gerente queda limitado a niveles 3..7 dentro de su empresa.
     */
    $users=$repo->search(
        $companyId,
        $gbu,
        $site,
        $projectId,
        $query,
        60,
        null,
        (array)$context['managed_levels']
    );

    /*
     * Después se aplica exclusivamente la autorización de Jefatura:
     *  - nivel permitido;
     *  - usuario realmente relacionado a un worker bajo esta Jefatura.
     *
     * La relación se resuelve por user-id, no sólo por users.id_worker,
     * para incluir registros históricos que Administrador sí encuentra.
     */
    if ((string)$context['directory_role']==='jefatura') {
        $allowedLevels=
            SctAdminUserDirectoryPolicy::managedLevelsForActorRole(
                'jefatura'
            );

        $candidateUserIds=array_values(array_filter(array_map(
            static fn(array $user): string =>
                trim((string)($user['id_users'] ?? '')),
            $users
        )));

        $assignedUserIds=
            $repo->assignedUserIdsForSupervisor(
                $companyId,
                (string)$context['session_user_id'],
                $candidateUserIds
            );

        $assignedLookup=[];
        foreach ($assignedUserIds as $assignedUserId) {
            $assignedLookup[
                mb_strtolower(trim($assignedUserId),'UTF-8')
            ]=true;
        }

        $users=array_values(array_filter(
            $users,
            static function(array $user) use (
                $allowedLevels,
                $assignedLookup
            ): bool {
                $level=isset($user['access_level'])
                    && $user['access_level']!==null
                    ? (int)$user['access_level']
                    : null;

                $userId=mb_strtolower(
                    trim((string)($user['id_users'] ?? '')),
                    'UTF-8'
                );

                return $level!==null
                    && in_array($level,$allowedLevels,true)
                    && $userId!==''
                    && isset($assignedLookup[$userId]);
            }
        ));
    }

    responderJSON(true,[
        'filters'=>$filters,
        'users'=>array_map(static fn(array $u): array => [
            'id_users'=>(string)$u['id_users'],
            'name'=>(string)$u['name'],
            'lastname'=>(string)$u['lastname'],
            'rut'=>(string)$u['rut'],
            'state'=>(int)$u['state'],
            'gbu'=>(string)($u['gbu'] ?? ''),
            'site'=>(string)($u['site'] ?? ''),
            'projects'=>(string)($u['projects'] ?? ''),
            'access_level'=>$u['access_level'],
            'role_names'=>(string)($u['role_names'] ?? ''),
            'role_label'=>(string)($u['role_label'] ?? ''),
            'jefaturas'=>(string)($u['jefaturas'] ?? ''),
            'credential_status'=>(string)($u['credential_status'] ?? 'pending_activation'),
            'last_access'=>(string)($u['last_access'] ?? ''),
        ],$users),
    ]);
} catch (Throwable $e) {
    error_log(
        'admin-directorio.php search: '
        . get_class($e)
        . ' - '
        . $e->getMessage()
    );
    responderJSON(
        false,
        null,
        t('admin_users_search_error'),
        500
    );
}
