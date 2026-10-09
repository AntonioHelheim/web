<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'audits.view');

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = auditoriaResolveCompanyId($pdo, $idCompanySolicitado);

try {
    responderJSON(true, cursoListarPorEmpresa($pdo, $idCompany, AUDITORIA_TIPO));
} catch (PDOException $e) {
    error_log('api/auditorias/auditorias-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las auditorías.', 500);
}
