<?php
require_once __DIR__ . '/admin-directorio-common.php';

$context=sctAdminDirectoryContext($pdo);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);

$targetId=trim((string)($input['id_users'] ?? ''));

if ($targetId==='') {
    responderJSON(false,null,t('admin_users_delete_invalid'),400);
}

$companyId=(int)$context['company_id'];
$selfId=(string)$context['session_user_id'];
$repo=sctAdminDirectoryRepo($pdo);
$target=$repo->profile($targetId,$companyId);

if (!$target) {
    responderJSON(false,null,t('admin_users_delete_not_found'),404);
}

$targetLevel=isset($target['access_level'])
    ? (int)$target['access_level']
    : null;

if (!sctDirectoryTargetAllowed($pdo,$context,$target)) {
    responderJSON(false,null,t('jefatura_scope_forbidden'),403);
}

if (strcasecmp($targetId,$selfId)===0) {
    responderJSON(false,null,t('admin_users_delete_self'),400);
}

/*
 * Eliminación administrativa E3 VS2:
 * - elimina ACCESO SCT;
 * - NO elimina la persona operacional (workers);
 * - preserva worker_projects, perfil extendido y trazabilidad histórica;
 * - invalida credenciales y roles activos.
 *
 * No se ejecuta DELETE sobre users porque múltiples módulos históricos
 * pueden referenciar id_users y deben conservar trazabilidad.
 */
try {
    $pdo->beginTransaction();

    $stmt=$pdo->prepare(
        'UPDATE users
         SET state=0,last_update=NOW()
         WHERE id_users=:id_users
           AND id_company=:id_company
         LIMIT 1'
    );
    $stmt->execute([
        'id_users'=>$targetId,
        'id_company'=>$companyId,
    ]);

    $stmt=$pdo->prepare(
        'UPDATE users_role
         SET state=0,last_update=NOW()
         WHERE id_users=:id_users'
    );
    $stmt->execute(['id_users'=>$targetId]);

    $stmt=$pdo->prepare(
        'DELETE FROM user_credentials
         WHERE id_users=:id_users'
    );
    $stmt->execute(['id_users'=>$targetId]);

    $pdo->commit();

    responderJSON(
        true,
        [
            'id_users'=>$targetId,
            'id_worker'=>isset($target['id_worker'])
                ? (int)$target['id_worker']
                : null,
        ],
        t('admin_users_delete_success')
    );
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'admin-cuenta-eliminar.php: '
        . $e->getMessage()
    );

    responderJSON(
        false,
        null,
        t('admin_users_delete_error'),
        500
    );
}
