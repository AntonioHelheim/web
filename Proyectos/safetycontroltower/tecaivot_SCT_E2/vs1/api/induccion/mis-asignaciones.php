<?php
/**
 * GET /api/induccion/mis-asignaciones.php
 *
 * Cualquier usuario logueado puede ver sus propias asignaciones —
 * la asignación es por cuenta (id_users), no por rol.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireLogin();

try {
    $asignaciones = asignacionListarPorUsuario($pdo, currentUserId());

    foreach ($asignaciones as &$a) {
        if ((int) $a['state'] === ASIGNACION_APROBADA) {
            $certificado = certificadoObtenerPorAsignacion($pdo, (int) $a['id_user_test_assigned']);
            $a['certificado_disponible'] = $certificado !== null;
        } else {
            $a['certificado_disponible'] = false;
        }
        $a['intentos_usados'] = intentosUsados($pdo, currentUserId(), (int) $a['id_test']);
    }
    unset($a);

    responderJSON(true, $asignaciones);
} catch (PDOException $e) {
    error_log('api/induccion/mis-asignaciones.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener tus cursos asignados.', 500);
}
