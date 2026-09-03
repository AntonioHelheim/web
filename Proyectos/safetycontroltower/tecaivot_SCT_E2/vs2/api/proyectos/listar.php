<?php
/**
 * GET /api/proyectos/listar.php?id_company=7
 *
 * Devuelve los proyectos activos de una empresa.
 * - administrador / administrador_completo: deben indicar id_company.
 * - cliente / jefatura / trabajador: siempre ven su propia empresa,
 *   se ignora cualquier id_company que venga en la URL.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/ProyectoRepository.php';

proyectosRequireLecturaApi($pdo);

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = proyectosResolveCompanyId($pdo, $idCompanySolicitado);

try {
    $proyectos = proyectoListarPorEmpresa($pdo, $idCompany);

    responderJSON(true, $proyectos);
} catch (PDOException $e) {
    error_log('api/proyectos/listar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los proyectos.', 500);
}
