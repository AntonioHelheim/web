<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'dynamic_forms.manage');
$idCompany = formularioIsGlobalAdmin($pdo)
    ? (filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: 0)
    : formularioCurrentCompany($pdo);
if ($idCompany <= 0) responderJSON(true, []);
try {
    $stmt = $pdo->prepare('SELECT id_users,name,lastname,email FROM users WHERE id_company=:company AND state=1 ORDER BY lastname,name,id_users');
    $stmt->execute(['company'=>$idCompany]);
    responderJSON(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    error_log('formularios/usuarios-disponibles.php: '.$e->getMessage());
    responderJSON(false,null,'No se pudieron obtener los usuarios.',500);
}
