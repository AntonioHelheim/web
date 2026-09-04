<?php
/**
 * GET /api/induccion/preguntas-listar.php?q=extintor
 *
 * Cualquier rol de gestión puede LEER el banco de preguntas (para armar
 * su curso), aunque solo administrador/administrador_completo puede
 * crear preguntas nuevas — ver api/induccion/common.php.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireGestionApi($pdo);

$busqueda = trim((string) ($_GET['q'] ?? ''));

try {
    $preguntas = preguntaListar($pdo, $busqueda);
    responderJSON(true, $preguntas);
} catch (PDOException $e) {
    error_log('api/induccion/preguntas-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las preguntas.', 500);
}
