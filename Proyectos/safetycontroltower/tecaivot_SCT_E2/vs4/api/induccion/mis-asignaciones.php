<?php
/**
 * GET /api/induccion/mis-asignaciones.php
 *
 * Cualquier usuario logueado puede ver sus propias asignaciones —
 * la asignación es por cuenta (id_users), no por rol.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'induction.execute');

try {
    $asignaciones = asignacionListarPorUsuario($pdo, currentUserId());
    // Este endpoint alimenta exclusivamente "Mis inducciones". El motor de
    // asignaciones es compartido con auditorías y autoevaluaciones, por lo que
    // filtramos explícitamente el tipo para no mezclar actividades de otros módulos.
    $asignaciones = array_values(array_filter($asignaciones, static function (array $a) use ($pdo): bool {
        static $typeCache = [];
        $idTest = (int) ($a['id_test'] ?? 0);
        if ($idTest <= 0) return false;
        if (!array_key_exists($idTest, $typeCache)) {
            $stmtType = $pdo->prepare('SELECT type FROM company_test WHERE id_test = :id_test LIMIT 1');
            $stmtType->execute(['id_test' => $idTest]);
            $typeCache[$idTest] = (string) ($stmtType->fetchColumn() ?: '');
        }
        return $typeCache[$idTest] === 'induccion';
    }));

    foreach ($asignaciones as &$a) {
        if ((int) $a['state'] === ASIGNACION_APROBADA) {
            $certificado = certificadoObtenerPorAsignacion($pdo, (int) $a['id_user_test_assigned']);
            $a['certificado_disponible'] = $certificado !== null;
        } else {
            $a['certificado_disponible'] = false;
        }
        $a['intentos_usados'] = induccionIntentosUsadosPorAsignacion($pdo, (int) $a['id_user_test_assigned']);
    }
    unset($a);

    responderJSON(true, $asignaciones);
} catch (PDOException $e) {
    error_log('api/induccion/mis-asignaciones.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener tus cursos asignados.', 500);
}
