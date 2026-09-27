<?php
/**
 * P73 — Material de apoyo compartido para Formularios y Protocolos.
 * Endpoints: GET ?entity=dynamic_form|protocol&id=... / POST action=upload|delete.
 */
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';

$entity = (string) ($_GET['entity'] ?? $_POST['entity'] ?? '');
$allowedEntities = ['dynamic_form', 'protocol'];
if (!in_array($entity, $allowedEntities, true)) {
    responderJSON(false, null, 'Tipo de actividad no válido.', 400);
}

if ($entity === 'dynamic_form') {
    require_once __DIR__ . '/../formularios/common.php';
    requireCapability($pdo, 'dynamic_forms.manage');
} else {
    require_once __DIR__ . '/../protocolos/common.php';
    requireCapability($pdo, 'protocols.manage');
}

function sctSupportTableExists(PDO $pdo): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='activity_support_materials'");
    $stmt->execute();
    return (int) $stmt->fetchColumn() > 0;
}

function sctSupportEntity(PDO $pdo, string $entity, int $id): array
{
    if ($entity === 'dynamic_form') {
        $form = formularioObtener($pdo, $id);
        if (!$form) responderJSON(false, null, 'Formulario no encontrado.', 404);
        formularioAssertVisible($pdo, $form);
        if (!formularioCanEdit($pdo, $form)) responderJSON(false, null, 'No tienes permisos para administrar este formulario.', 403);
        return ['company' => $form['id_company'] !== null ? (int) $form['id_company'] : null, 'label' => (string) $form['name']];
    }
    $protocol = protocoloObtener($pdo, $id);
    if (!$protocol) responderJSON(false, null, 'Protocolo no encontrado.', 404);
    $implementation = $protocol['id_company'] !== null ? (int) $protocol['id_company'] : (protocoloIsGlobalAdmin($pdo) ? null : protocoloCurrentCompany($pdo));
    protocoloAssertDefinitionManageable($pdo, $protocol, $implementation);
    return ['company' => $protocol['id_company'] !== null ? (int) $protocol['id_company'] : $implementation, 'label' => (string) $protocol['name']];
}

function sctSupportSafeName(string $name): string
{
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename($name));
    $name = trim((string) $name, '._-');
    return $name !== '' ? $name : 'archivo';
}

function sctSupportMoveUpload(array $file, string $relativeDir): array
{
    if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('No se pudo recibir uno de los archivos.');
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > 80 * 1024 * 1024) throw new RuntimeException('Uno de los archivos supera el tamaño permitido de 80 MB.');
    $tmp = (string) ($file['tmp_name'] ?? '');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $allowed = [
        'application/pdf','image/jpeg','image/png','image/webp','video/mp4','video/webm',
        'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ];
    if (!in_array((string) $mime, $allowed, true)) throw new RuntimeException('Uno de los archivos tiene un formato no permitido.');
    $root = realpath(__DIR__ . '/../../');
    if ($root === false) throw new RuntimeException('No se pudo resolver el directorio de la aplicación.');
    $dir = $root . '/' . trim($relativeDir, '/');
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('No se pudo preparar el directorio de carga.');
    $original = sctSupportSafeName((string) ($file['name'] ?? 'archivo'));
    $ext = pathinfo($original, PATHINFO_EXTENSION);
    $stored = bin2hex(random_bytes(10)) . ($ext ? '.' . strtolower($ext) : '');
    if (!move_uploaded_file($tmp, $dir . '/' . $stored)) throw new RuntimeException('No se pudo guardar uno de los archivos.');
    return ['path' => trim($relativeDir, '/') . '/' . $stored, 'name' => $original, 'mime' => (string) $mime];
}

if (!sctSupportTableExists($pdo)) {
    responderJSON(false, null, 'Aplica la migración P73 para habilitar material de apoyo en este módulo.', 409);
}

$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) responderJSON(false, null, 'Actividad no válida.', 400);
$meta = sctSupportEntity($pdo, $entity, (int) $id);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare('SELECT id_activity_material,entity_type,entity_id,title,file_path,original_name,mime_type,sort_order,date_create FROM activity_support_materials WHERE entity_type=:entity AND entity_id=:id ORDER BY sort_order,id_activity_material');
        $stmt->execute(['entity' => $entity, 'id' => $id]);
        responderJSON(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
    $action = (string) ($_POST['action'] ?? 'upload');
    $csrf = (string) ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) responderJSON(false, null, 'Tu sesión expiró. Recarga la página.', 403);

    if ($action === 'delete') {
        $materialId = filter_var($_POST['id_material'] ?? null, FILTER_VALIDATE_INT);
        if (!$materialId) responderJSON(false, null, 'Material no válido.', 400);
        $stmt = $pdo->prepare('SELECT file_path FROM activity_support_materials WHERE id_activity_material=:material AND entity_type=:entity AND entity_id=:id');
        $stmt->execute(['material' => $materialId, 'entity' => $entity, 'id' => $id]);
        $path = $stmt->fetchColumn();
        if ($path === false) responderJSON(false, null, 'Material no encontrado.', 404);
        $pdo->prepare('DELETE FROM activity_support_materials WHERE id_activity_material=:material')->execute(['material' => $materialId]);
        $absolute = realpath(__DIR__ . '/../../') . '/' . ltrim((string) $path, '/');
        if (is_file($absolute)) @unlink($absolute);
        auditTrailLogAction($pdo, $meta['company'], $entity === 'dynamic_form' ? 'formularios' : 'protocolos', 'activity_support_materials', $materialId, 'file_path', (string) $path, null, 'remove', $meta['label']);
        responderJSON(true, null, 'Material eliminado.');
    }

    if (empty($_FILES['files'])) responderJSON(false, null, 'Selecciona al menos un archivo.', 400);
    $files = $_FILES['files'];
    $count = is_array($files['name']) ? count($files['name']) : 1;
    $created = [];
    $pdo->beginTransaction();
    for ($i = 0; $i < $count; $i++) {
        $file = [
            'name' => is_array($files['name']) ? $files['name'][$i] : $files['name'],
            'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
            'error' => is_array($files['error']) ? $files['error'][$i] : $files['error'],
            'size' => is_array($files['size']) ? $files['size'][$i] : $files['size'],
        ];
        $saved = sctSupportMoveUpload($file, 'uploads/activity-builder/apoyo/' . $entity . '/' . $id);
        $orderStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order),0)+1 FROM activity_support_materials WHERE entity_type=:entity AND entity_id=:id');
        $orderStmt->execute(['entity' => $entity, 'id' => $id]);
        $order = (int) $orderStmt->fetchColumn();
        $insert = $pdo->prepare('INSERT INTO activity_support_materials (entity_type,entity_id,title,file_path,original_name,mime_type,sort_order,created_by,date_create) VALUES (:entity,:id,:title,:path,:name,:mime,:sort,:by,NOW())');
        $insert->execute(['entity'=>$entity,'id'=>$id,'title'=>mb_substr($saved['name'],0,150,'UTF-8'),'path'=>$saved['path'],'name'=>$saved['name'],'mime'=>$saved['mime'],'sort'=>$order,'by'=>currentUserId()]);
        $created[] = (int) $pdo->lastInsertId();
    }
    $pdo->commit();
    auditTrailLogAction($pdo, $meta['company'], $entity === 'dynamic_form' ? 'formularios' : 'protocolos', 'activity_support_materials', implode(',', $created), 'files', null, count($created), 'upload', $meta['label']);
    responderJSON(true, ['created' => $created], count($created) . ' archivo(s) cargado(s).', 201);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('support-materials.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo completar la operación de material de apoyo.', 500);
}
