<?php
require_once __DIR__ . '/self-org-common.php';

$context=sctSelfOrgContext($pdo);
$input=json_decode(file_get_contents('php://input'),true);
if (!is_array($input)) $input=$_POST;
requireCsrfToken($input);
sctSelfOrgRequireSchema($pdo);

$companyId=(int)$context['company_id'];
$userId=(string)$context['session_user_id'];

try {
    $stmt=$pdo->prepare(
        'SELECT address
         FROM company
         WHERE id_company=:id_company
         LIMIT 1'
    );
    $stmt->execute(['id_company'=>$companyId]);
    $matrixAddress=(string)($stmt->fetchColumn() ?: '');

    responderJSON(true,[
        'matrix'=>[
            'value'=>'matrix',
            'address'=>$matrixAddress,
        ],
        'centers'=>sctSelfOrgCenters($pdo,$companyId),
        'projects'=>sctSelfOrgProjects($pdo,$companyId),
        'current'=>sctSelfOrgCurrent(
            $pdo,
            $userId,
            $companyId
        ),
    ]);
} catch (Throwable $e) {
    error_log(
        'self-org-obtener.php: '
        . $e->getMessage()
    );
    responderJSON(
        false,
        null,
        t('self_org_load_error'),
        500
    );
}
