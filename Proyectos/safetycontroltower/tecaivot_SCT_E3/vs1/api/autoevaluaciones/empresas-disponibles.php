<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

autoevaluacionRequireGestionApi($pdo);

if (!autoevaluacionIsGlobalAdmin($pdo)) {
    responderJSON(true, []);
}

try {
    $empresas = array_values(array_filter(empresaListar($pdo), static function (array $empresa): bool {
        return (int) ($empresa['state'] ?? 0) === 1;
    }));
    responderJSON(true, $empresas);
} catch (PDOException $e) {
    error_log('api/autoevaluaciones/empresas-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las empresas.', 500);
}
