<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EvaluacionRepository.php';
require __DIR__ . '/../../lib/repositorios/AuditoriaRepository.php';

requireCapability($pdo, 'audits.execute');

try {
    $asignaciones = evaluacionListarAsignacionesUsuarioPorTipo($pdo, (string) currentUserId(), AUDITORIA_TIPO);
    foreach ($asignaciones as &$asignacion) {
        $audit = auditoriaObtenerPorAsignacion($pdo, (int) $asignacion['id_user_test_assigned']);
        $asignacion['audit_status'] = $audit['status'] ?? null;
        $asignacion['score'] = $audit['score'] ?? '';
        $asignacion['obs'] = $audit['obs'] ?? '';
        $asignacion['completed_at'] = $audit['completed_at'] ?? null;
    }
    unset($asignacion);
    responderJSON(true, $asignaciones);
} catch (Throwable $e) {
    if (auditoriaMigrationMessage($e)) responderJSON(false, null, 'El módulo requiere ejecutar la migración 20260908_stage2_audits.sql.', 503);
    error_log('api/auditorias/mis-asignaciones.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener tus auditorías.', 500);
}
