<?php
/** POST JSON: id_company, csrf_token */
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}
requireCsrfToken($input);

$idCompany = filter_var($input['id_company'] ?? null, FILTER_VALIDATE_INT);
if (!$idCompany) {
    responderJSON(false, null, 'Empresa no válida.', 400);
}
if (!empresasCanEdit($pdo, (int) $idCompany)) {
    responderJSON(false, null, 'No tienes permisos para modificar el logotipo de esta empresa.', 403);
}

try {
    if (!empresasLogoColumnAvailable($pdo)) {
        responderJSON(false, null, 'La base de datos del servidor aún no tiene habilitada la columna de logotipo de empresa. Ejecuta la migración correspondiente y vuelve a intentar.', 503);
    }

    $empresa = empresaObtenerPorId($pdo, (int) $idCompany);
    if (!$empresa) {
        responderJSON(false, null, 'Empresa no encontrada.', 404);
    }

    $path = (string) ($empresa['logo_path'] ?? '');
    $stmt = $pdo->prepare('UPDATE company SET logo_path = NULL, last_update = NOW() WHERE id_company = :id_company LIMIT 1');
    $stmt->execute(['id_company' => (int) $idCompany]);

    if ($path !== '' && sctStartsWith($path, 'uploads/empresas/')) {
        $directorio = __DIR__ . '/../../uploads/empresas';
        $archivo = __DIR__ . '/../../' . $path;
        $realDir = realpath($directorio);
        $realFile = is_file($archivo) ? realpath($archivo) : false;
        if ($realDir && $realFile && sctStartsWith($realFile, $realDir . DIRECTORY_SEPARATOR)) {
            @unlink($realFile);
        }
    }

    responderJSON(true, null, 'Logotipo eliminado correctamente.');
} catch (Throwable $e) {
    error_log('api/empresas/eliminar-logo.php: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible eliminar el logotipo.', 500);
}
