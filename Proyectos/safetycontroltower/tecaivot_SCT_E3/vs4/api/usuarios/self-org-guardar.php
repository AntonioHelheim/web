<?php
require_once __DIR__ . '/self-org-common.php';

$context=sctSelfOrgContext($pdo);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);
sctSelfOrgRequireSchema($pdo);

$companyId=(int)$context['company_id'];
$userId=(string)$context['session_user_id'];
$workerId=(int)($context['profile']['id_worker'] ?? 0);

$location=trim((string)($input['physical_location'] ?? ''));
$gbu=trim((string)($input['gbu_code'] ?? ''));
$site=trim((string)($input['site_name'] ?? ''));
$projectId=filter_var(
    $input['id_project'] ?? null,
    FILTER_VALIDATE_INT
) ?: null;

if ($gbu==='' || $site==='') {
    responderJSON(
        false,
        null,
        t('self_org_scope_required'),
        422
    );
}

$locationType=null;
$centerId=null;

if ($location!=='') {
    if ($location==='matrix') {
        $locationType='matrix';
    } elseif (str_starts_with($location,'center:')) {
        $candidate=filter_var(
            substr($location,7),
            FILTER_VALIDATE_INT
        );

        if (!$candidate) {
            responderJSON(
                false,
                null,
                t('self_org_location_invalid'),
                422
            );
        }

        $stmt=$pdo->prepare(
            'SELECT COUNT(*)
             FROM company_center
             WHERE id_company_center=:id
               AND id_company=:id_company
               AND state=1'
        );
        $stmt->execute([
            'id'=>$candidate,
            'id_company'=>$companyId,
        ]);

        if ((int)$stmt->fetchColumn()!==1) {
            responderJSON(
                false,
                null,
                t('self_org_location_invalid'),
                422
            );
        }

        $locationType='center';
        $centerId=(int)$candidate;
    } else {
        responderJSON(
            false,
            null,
            t('self_org_location_invalid'),
            422
        );
    }
}

$stmt=$pdo->prepare(
    'SELECT id_project
     FROM projects
     WHERE id_company=:id_company
       AND state=1
       AND LOWER(TRIM(gbu_code))=LOWER(TRIM(:gbu))
       AND LOWER(TRIM(site_name))=LOWER(TRIM(:site))
     ORDER BY id_project'
);
$stmt->execute([
    'id_company'=>$companyId,
    'gbu'=>$gbu,
    'site'=>$site,
]);
$scopeProjects=array_map(
    'intval',
    array_column($stmt->fetchAll() ?: [],'id_project')
);

if (!$scopeProjects) {
    responderJSON(
        false,
        null,
        t('self_org_scope_invalid'),
        422
    );
}

if (
    $projectId!==null
    && !in_array((int)$projectId,$scopeProjects,true)
) {
    responderJSON(
        false,
        null,
        t('self_org_project_invalid'),
        422
    );
}

try {
    $pdo->beginTransaction();

    $stmt=$pdo->prepare(
        'SELECT COUNT(*)
         FROM user_profile_details
         WHERE id_users=:id_users
           AND id_company=:id_company'
    );
    $stmt->execute([
        'id_users'=>$userId,
        'id_company'=>$companyId,
    ]);
    $exists=(int)$stmt->fetchColumn()>0;

    if ($exists) {
        $stmt=$pdo->prepare(
            'UPDATE user_profile_details
             SET
                physical_location_type=:location_type,
                id_company_center=:center_id,
                gbu_code=:gbu_code,
                site_name=:site_name,
                confirmed_project_id=:id_project,
                last_update=NOW()
             WHERE id_users=:id_users
               AND id_company=:id_company'
        );
        $stmt->execute([
            'location_type'=>$locationType,
            'center_id'=>$centerId,
            'gbu_code'=>$gbu,
            'site_name'=>$site,
            'id_project'=>$projectId,
            'id_users'=>$userId,
            'id_company'=>$companyId,
        ]);
    } else {
        if ($workerId<=0) {
            throw new RuntimeException(
                'SELF_ORG_WORKER_NOT_FOUND'
            );
        }

        $stmt=$pdo->prepare(
            'INSERT INTO user_profile_details (
                id_users,
                id_company,
                id_worker,
                physical_location_type,
                id_company_center,
                gbu_code,
                site_name,
                confirmed_project_id,
                confirmed_at,
                created_by,
                date_create,
                last_update
             ) VALUES (
                :id_users,
                :id_company,
                :id_worker,
                :location_type,
                :center_id,
                :gbu_code,
                :site_name,
                :id_project,
                NOW(),
                :created_by,
                NOW(),
                NOW()
             )'
        );
        $stmt->execute([
            'id_users'=>$userId,
            'id_company'=>$companyId,
            'id_worker'=>$workerId,
            'location_type'=>$locationType,
            'center_id'=>$centerId,
            'gbu_code'=>$gbu,
            'site_name'=>$site,
            'id_project'=>$projectId,
            'created_by'=>$userId,
        ]);
    }

    $pdo->commit();

    responderJSON(
        true,
        null,
        t('self_org_saved')
    );
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'self-org-guardar.php: '
        . $e->getMessage()
    );

    responderJSON(
        false,
        null,
        t('self_org_save_error'),
        500
    );
}
