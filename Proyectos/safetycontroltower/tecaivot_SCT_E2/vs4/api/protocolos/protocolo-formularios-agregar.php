<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.forms');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idProtocol = filter_var($input['id_protocol'] ?? null, FILTER_VALIDATE_INT);
$idForm = filter_var($input['id_form'] ?? null, FILTER_VALIDATE_INT);
$requestedCompany = filter_var($input['id_company'] ?? null, FILTER_VALIDATE_INT);
$required = !empty($input['is_required']) ? 1 : 0;
$sortOrder = filter_var($input['sort_order'] ?? 0, FILTER_VALIDATE_INT);

if (!$idProtocol || !$idForm || $sortOrder === false || $sortOrder < 0 || $sortOrder > 10000) {
    responderJSON(false, null, 'Datos de formulario no válidos.', 400);
}

try {
    $protocol = protocoloObtener($pdo, (int) $idProtocol);
    if (!$protocol) responderJSON(false, null, 'Protocolo no encontrado.', 404);

    if (protocoloIsGlobalAdmin($pdo)) {
        $company = ($requestedCompany && $requestedCompany > 0) ? (int) $requestedCompany : 0;
    } else {
        $company = protocoloCurrentCompany($pdo);
    }

    $implementationCompany = $company > 0 ? $company : null;
    protocoloAssertDefinitionManageable($pdo, $protocol, $implementationCompany);

    if (protocoloTieneAsignaciones($pdo, (int) $idProtocol, $implementationCompany)) {
        responderJSON(false, null, 'Ya existen asignaciones en este alcance; la composición quedó bloqueada.', 409);
    }

    $stmt = $pdo->prepare(
        'SELECT * FROM dynamic_forms WHERE id_form=:id_form AND state=1 LIMIT 1'
    );
    $stmt->execute(['id_form' => (int) $idForm]);
    $form = $stmt->fetch();
    if (!$form) responderJSON(false, null, 'Formulario no encontrado o inactivo.', 404);

    $formOwner = $form['id_company'];
    if ($implementationCompany === null) {
        if (!protocoloIsGlobalAdmin($pdo) || $formOwner !== null) {
            responderJSON(false, null, 'Un vínculo global solo puede usar un formulario global.', 400);
        }
    } else {
        if ($formOwner !== null && (int) $formOwner !== $implementationCompany) {
            responderJSON(false, null, 'El formulario pertenece a otra empresa.', 400);
        }
    }

    $id = protocoloFormularioAgregar(
        $pdo,
        (int) $idProtocol,
        $implementationCompany,
        (int) $idForm,
        $required,
        (int) $sortOrder,
        (string) currentUserId()
    );
    auditTrailLogAction($pdo, $implementationCompany, 'protocolos', 'protocol_forms', $id,
        'id_form', null, $idForm . ($required ? ' · obligatorio' : ' · opcional'), 'assign', (string)$protocol['name']);
    responderJSON(true, ['id_protocol_form' => $id], 'Formulario vinculado correctamente.', 201);
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/protocolo-formularios-agregar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo vincular el formulario.', 500);
}
