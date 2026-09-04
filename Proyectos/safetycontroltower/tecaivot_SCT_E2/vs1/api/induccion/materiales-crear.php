<?php
/**
 * POST /api/induccion/materiales-crear.php
 * Body JSON: { id_test, title, material_type, content_text, file_path, sort_order, csrf_token }
 *
 * Alcance de esta entrega: 'texto' guarda contenido inline; 'documento'/
 * 'video'/'otro' guardan un enlace externo (file_path como URL), no hay
 * subida de archivo propio todavía — mismo patrón seguro de subida que
 * ya existe para la foto de trabajadores se puede sumar después si hace
 * falta alojar los documentos en el propio servidor.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/validation.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

induccionRequireGestionApi($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$csrfToken = (string) ($input['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
}

$idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
if (!$idTest) {
    responderJSON(false, null, 'Curso no válido.', 400);
}

$faltantes = requerirCampos($input, ['title', 'material_type']);
if ($faltantes) {
    responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400);
}

$tiposValidos = ['documento', 'texto', 'video', 'otro'];
$materialType = (string) $input['material_type'];
if (!in_array($materialType, $tiposValidos, true)) {
    responderJSON(false, null, 'Tipo de material no válido.', 400);
}

$title = sanitizarTexto((string) $input['title']);
if (mb_strlen($title) > 150) {
    responderJSON(false, null, 'El título no puede superar los 150 caracteres.', 400);
}

$contentText = sanitizarTexto((string) ($input['content_text'] ?? ''));
$filePath = trim((string) ($input['file_path'] ?? ''));

if ($materialType === 'texto') {
    if ($contentText === '') {
        responderJSON(false, null, 'El contenido de texto es obligatorio para este tipo de material.', 400);
    }
} else {
    if ($filePath === '' || !filter_var($filePath, FILTER_VALIDATE_URL)) {
        responderJSON(false, null, 'Debes indicar un enlace válido (https://...) para este tipo de material.', 400);
    }
}

$sortOrder = filter_var($input['sort_order'] ?? 0, FILTER_VALIDATE_INT) ?: 0;

try {
    $curso = cursoObtenerPorId($pdo, $idTest);
    if (!$curso) {
        responderJSON(false, null, 'Curso no encontrado.', 404);
    }

    if (!induccionIsGlobalAdmin($pdo) && (int) $curso['id_company'] !== currentUserCompanyId($pdo)) {
        responderJSON(false, null, 'No tienes permisos para modificar este curso.', 403);
    }

    $idNuevo = materialCrear($pdo, [
        'id_test'       => $idTest,
        'title'         => $title,
        'material_type' => $materialType,
        'file_path'     => $filePath,
        'content_text'  => $contentText,
        'sort_order'    => $sortOrder,
    ], currentUserId());

    responderJSON(true, ['id_material' => $idNuevo], 'Material agregado correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/induccion/materiales-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo agregar el material.', 500);
}
