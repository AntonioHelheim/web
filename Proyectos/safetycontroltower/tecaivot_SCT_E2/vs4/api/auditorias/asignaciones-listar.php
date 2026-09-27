<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/AuditoriaRepository.php';

requireCapability($pdo, 'audits.assign');
$idTest = filter_input(INPUT_GET, 'id_test', FILTER_VALIDATE_INT);
if (!$idTest) responderJSON(false, null, 'Parámetro inválido.', 400);

try {
    $test = cursoObtenerPorId($pdo, $idTest);
    if (!$test) responderJSON(false, null, 'Auditoría no encontrada.', 404);
    auditoriaAssertTest($test);
    auditoriaAssertCompanyAccess($pdo, (int) $test['id_company']);
    responderJSON(true, auditoriaListarPorTest($pdo, $idTest));
} catch (Throwable $e) {
    if (auditoriaMigrationMessage($e)) responderJSON(false, null, 'El módulo requiere ejecutar la migración 20260908_stage2_audits.sql.', 503);
    error_log('api/auditorias/asignaciones-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las ejecuciones de auditoría.', 500);
}
