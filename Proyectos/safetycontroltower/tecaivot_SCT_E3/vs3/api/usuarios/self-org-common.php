<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/validation.php';
require_once __DIR__ . '/../../i18n.php';

function sctSelfOrgContext(PDO $pdo): array
{
    requireLogin();

    $context=currentUserAccessContext($pdo);
    $profile=currentUserProfile($pdo);

    if (!$context || !$profile) {
        responderJSON(
            false,
            null,
            t('self_org_profile_not_found'),
            404
        );
    }

    $context['profile']=$profile;

    return $context;
}

function sctSelfOrgSchemaReady(PDO $pdo): bool
{
    foreach ([
        'physical_location_type',
        'id_company_center',
        'gbu_code',
        'site_name',
    ] as $column) {
        $stmt=$pdo->prepare(
            "SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA=DATABASE()
               AND TABLE_NAME='user_profile_details'
               AND COLUMN_NAME=:column_name"
        );
        $stmt->execute(['column_name'=>$column]);

        if ((int)$stmt->fetchColumn()!==1) {
            return false;
        }
    }

    return true;
}

function sctSelfOrgRequireSchema(PDO $pdo): void
{
    if (!sctSelfOrgSchemaReady($pdo)) {
        responderJSON(
            false,
            null,
            t('self_org_migration_required'),
            503
        );
    }
}

function sctSelfOrgCenters(PDO $pdo,int $companyId): array
{
    $stmt=$pdo->prepare(
        'SELECT id_company_center,name,address,state
         FROM company_center
         WHERE id_company=:id_company
           AND state=1
         ORDER BY name,id_company_center'
    );
    $stmt->execute(['id_company'=>$companyId]);

    return $stmt->fetchAll() ?: [];
}

function sctSelfOrgProjects(PDO $pdo,int $companyId): array
{
    $stmt=$pdo->prepare(
        'SELECT
            id_project,
            name,
            gbu_code,
            site_name
         FROM projects
         WHERE id_company=:id_company
           AND state=1
           AND gbu_code IS NOT NULL
           AND TRIM(gbu_code)<>\'\'
           AND site_name IS NOT NULL
           AND TRIM(site_name)<>\'\'
         ORDER BY
            gbu_code,
            site_name,
            name,
            id_project'
    );
    $stmt->execute(['id_company'=>$companyId]);

    return $stmt->fetchAll() ?: [];
}

function sctSelfOrgCurrent(
    PDO $pdo,
    string $userId,
    int $companyId
): array {
    $stmt=$pdo->prepare(
        'SELECT
            physical_location_type,
            id_company_center,
            gbu_code,
            site_name,
            confirmed_project_id
         FROM user_profile_details
         WHERE id_users=:id_users
           AND id_company=:id_company
         LIMIT 1'
    );
    $stmt->execute([
        'id_users'=>$userId,
        'id_company'=>$companyId,
    ]);

    return $stmt->fetch() ?: [];
}
