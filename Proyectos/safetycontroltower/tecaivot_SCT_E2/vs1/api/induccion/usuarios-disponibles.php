<?php
/**
 * GET /api/induccion/usuarios-disponibles.php?id_company=7
 *
 * Nota: la asignación es por cuenta de acceso (users), no por ficha de
 * trabajador — para tomar un curso hay que poder iniciar sesión. Un
 * trabajador sin cuenta de usuario todavía no puede rendir inducción;
 * eso queda documentado, no es un bug de este endpoint.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireGestionApi($pdo);

$idCompanySolicitado = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: null;
$idCompany = induccionResolveCompanyId($pdo, $idCompanySolicitado);

try {
    responderJSON(true, usuariosActivosDeEmpresa($pdo, $idCompany));
} catch (PDOException $e) {
    error_log('api/induccion/usuarios-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener los usuarios.', 500);
}
