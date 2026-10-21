<?php
require __DIR__ . '/common.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    responderJSON(false,null,'Método no permitido.',405);
}

try {
    $context = usuariosRequireAccessContext($pdo);

    if (!authCanCreateUsers($context)) {
        responderJSON(false,null,t('users_worker_lookup_forbidden'),403);
    }

    $query = trim((string)($_GET['q'] ?? ''));
    $companyId = isset($_GET['id_company'])
        ? (int)$_GET['id_company']
        : (int)$context['company_id'];

    if (sctTextLength($query) < 2) {
        responderJSON(false,null,t('users_worker_lookup_min'),422);
    }

    if ($companyId <= 0) {
        responderJSON(false,null,t('users_worker_lookup_company'),400);
    }

    if (
        empty($context['is_global_admin'])
        && $companyId !== (int)$context['company_id']
    ) {
        responderJSON(false,null,t('users_worker_lookup_company'),403);
    }

    $rows = usuariosRepository($pdo)->workerCandidates(
        $companyId,
        $query,
        10
    );

    $workers = array_map(static function(array $row): array {
        return [
            'id_worker'=>(int)$row['id_worker'],
            'id_company'=>(int)$row['id_company'],
            'rut'=>(string)$row['rut'],
            'name'=>(string)$row['name'],
            'lastname'=>(string)$row['lastname'],
            'email'=>$row['email'] !== null ? (string)$row['email'] : '',
            'phone'=>$row['phone'] !== null ? (string)$row['phone'] : '',
            'position'=>$row['position'] !== null ? (string)$row['position'] : '',
            'linked_user_id'=>$row['linked_user_id'] !== null
                ? (string)$row['linked_user_id']
                : null,
            'available'=>$row['linked_user_id'] === null,
        ];
    }, $rows);

    responderJSON(true,[
        'workers'=>$workers,
        'found'=>count($workers)>0,
    ]);
} catch (Throwable $e) {
    error_log('api/usuarios/trabajadores-validar.php: '.$e->getMessage());
    responderJSON(false,null,t('users_worker_lookup_error'),500);
}
