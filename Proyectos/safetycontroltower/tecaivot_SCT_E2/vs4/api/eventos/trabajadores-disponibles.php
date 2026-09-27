<?php
/**
 * GET /api/eventos/trabajadores-disponibles.php?id_company=7
 * Campo opcional del evento (id_worker).
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EventoRepository.php';

requireCapability($pdo, 'events.view');

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = eventosResolveCompanyId($pdo, $idCompanySolicitado);

try {
    if (eventosIsWorkerScope($pdo)) {
        $workerId = eventosCurrentWorkerId($pdo);
        if ($workerId <= 0) {
            responderJSON(true, []);
        }
        $stmt = $pdo->prepare('SELECT id_worker, rut, name, lastname FROM workers WHERE id_worker = :id_worker AND id_company = :id_company AND state = 1 LIMIT 1');
        $stmt->execute(['id_worker' => $workerId, 'id_company' => $idCompany]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        responderJSON(true, $row ? [$row] : []);
    }
    responderJSON(true, trabajadoresActivosDeEmpresa($pdo, $idCompany));
} catch (PDOException $e) {
    error_log('api/eventos/trabajadores-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los trabajadores.', 500);
}
