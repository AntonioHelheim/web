<?php
require __DIR__ . '/common.php';

requireCapability($pdo, 'audits.assign');

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;

try {
    if (auditoriaIsGlobalAdmin($pdo)) {
        $sql = 'SELECT u.id_users, u.id_company, u.name, u.lastname, c.razon_social AS company_name
                FROM users u
                INNER JOIN company c ON c.id_company = u.id_company
                WHERE u.state = 1 AND c.state = 1
                ORDER BY c.razon_social, u.name, u.lastname, u.id_users';
        $stmt = $pdo->query($sql);
    } else {
        $idCompany = auditoriaResolveCompanyId($pdo, $idCompanySolicitado);
        $stmt = $pdo->prepare(
            'SELECT u.id_users, u.id_company, u.name, u.lastname, c.razon_social AS company_name
             FROM users u
             INNER JOIN company c ON c.id_company = u.id_company
             WHERE u.state = 1 AND u.id_company = :id_company
             ORDER BY u.name, u.lastname, u.id_users'
        );
        $stmt->execute(['id_company' => $idCompany]);
    }

    responderJSON(true, $stmt->fetchAll());
} catch (PDOException $e) {
    error_log('api/auditorias/auditores-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los auditores disponibles.', 500);
}
