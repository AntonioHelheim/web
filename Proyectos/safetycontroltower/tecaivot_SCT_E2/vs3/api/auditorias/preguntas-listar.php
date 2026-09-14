<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'audits.questions');
$q = trim((string) ($_GET['q'] ?? ''));

try {
    responderJSON(true, preguntaListar($pdo, $q));
} catch (PDOException $e) {
    error_log('api/auditorias/preguntas-listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo consultar el banco de preguntas.', 500);
}
