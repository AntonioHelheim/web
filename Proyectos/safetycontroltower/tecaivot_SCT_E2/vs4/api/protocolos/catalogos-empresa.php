<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.assign');

$idCompany = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT);

try {
    $company = protocoloResolveCompany($pdo, $idCompany ? (int) $idCompany : null);

    $center = $pdo->prepare(
        'SELECT id_company_center AS id,name FROM company_center '
        . 'WHERE id_company=:id_company AND state=1 ORDER BY name ASC'
    );
    $center->execute(['id_company' => $company]);

    $projects = $pdo->prepare(
        'SELECT id_project AS id,name FROM projects '
        . 'WHERE id_company=:id_company AND state=1 ORDER BY name ASC'
    );
    $projects->execute(['id_company' => $company]);

    $workers = $pdo->prepare(
        "SELECT id_worker AS id,CONCAT(name,' ',lastname) AS name,rut,position "
        . 'FROM workers WHERE id_company=:id_company AND state=1 '
        . 'ORDER BY name ASC,lastname ASC'
    );
    $workers->execute(['id_company' => $company]);

    $users = $pdo->prepare(
        "SELECT id_users AS id,CONCAT(name,' ',lastname) AS name "
        . 'FROM users WHERE id_company=:id_company AND state=1 '
        . 'ORDER BY name ASC,lastname ASC'
    );
    $users->execute(['id_company' => $company]);

    responderJSON(true, [
        'centers' => $center->fetchAll(),
        'projects' => $projects->fetchAll(),
        'workers' => $workers->fetchAll(),
        'users' => $users->fetchAll(),
    ]);
} catch (Throwable $e) {
    error_log('protocolos/catalogos-empresa: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar los catálogos de la empresa.', 500);
}
