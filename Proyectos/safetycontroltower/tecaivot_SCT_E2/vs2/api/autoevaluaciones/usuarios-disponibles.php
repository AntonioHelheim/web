<?php
require __DIR__ . '/common.php';

requireCapability($pdo, 'self_assessments.assign');
$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;

try {
    $idCompany = autoevaluacionResolveCompanyId($pdo, $idCompanySolicitado);
    $stmt = $pdo->prepare(
        'SELECT u.id_users, u.id_company, u.name, u.lastname, c.razon_social AS company_name
         FROM users u
         INNER JOIN company c ON c.id_company = u.id_company
         WHERE u.state = 1 AND c.state = 1 AND u.id_company = :id_company
         ORDER BY u.name, u.lastname, u.id_users'
    );
    $stmt->execute(['id_company' => $idCompany]);
    responderJSON(true, $stmt->fetchAll());
} catch (PDOException $e) {
    error_log('api/autoevaluaciones/usuarios-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los usuarios disponibles.', 500);
}
