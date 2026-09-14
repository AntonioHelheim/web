<?php
/**
 * GET /api/induccion/certificado-descargar.php?id_asignacion=7
 *
 * Puede descargarlo el dueño de la asignación, o alguien con rol de
 * gestión de la misma empresa (para verificar/archivar). El archivo
 * vive fuera del directorio con ejecución de PHP bloqueada
 * (uploads/certificados/.htaccess), pero además pasa por este endpoint
 * en vez de servirse por URL directa, para poder aplicar este control
 * de acceso — la ruta guardada en certificates.file_path no es
 * suficiente por sí sola para restringir quién lo ve.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireLogin();

$idAsignacion = filter_input(INPUT_GET, 'id_asignacion', FILTER_VALIDATE_INT);
if (!$idAsignacion) {
    responderJSON(false, null, 'Parámetro "id_asignacion" inválido.', 400);
}

try {
    $asignacion = asignacionObtenerPorId($pdo, $idAsignacion);
    if (!$asignacion) {
        responderJSON(false, null, 'Certificado no encontrado.', 404);
    }

    $esDueno = $asignacion['id_users'] === currentUserId();
    $esGestionDeLaEmpresa = count(array_intersect(currentUserRoles($pdo), INDUCCION_ROLES_GESTION)) > 0
        && (induccionIsGlobalAdmin($pdo) || (int) $asignacion['id_company'] === currentUserCompanyId($pdo));

    if (!$esDueno && !$esGestionDeLaEmpresa) {
        responderJSON(false, null, 'No tienes permisos para descargar este certificado.', 403);
    }

    $certificado = certificadoObtenerPorAsignacion($pdo, $idAsignacion);
    if (!$certificado) {
        responderJSON(false, null, 'Todavía no se generó un certificado para esta asignación.', 404);
    }

    $rutaArchivo = __DIR__ . '/../../' . $certificado['file_path'];
    if (!is_file($rutaArchivo)) {
        error_log('certificado-descargar.php: archivo no encontrado en disco: ' . $rutaArchivo);
        responderJSON(false, null, 'El archivo del certificado no está disponible. Contacta a soporte.', 500);
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="certificado-' . $certificado['code'] . '.pdf"');
    header('Content-Length: ' . filesize($rutaArchivo));
    readfile($rutaArchivo);
    exit;
} catch (PDOException $e) {
    error_log('api/induccion/certificado-descargar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo obtener el certificado.', 500);
}
