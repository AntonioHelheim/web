<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.forms');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) responderJSON(false, null, 'Solicitud inválida.', 400);
requireCsrfToken($input);

$idProtocolForm = filter_var($input['id_protocol_form'] ?? null, FILTER_VALIDATE_INT);
if (!$idProtocolForm) responderJSON(false, null, 'Vínculo no válido.', 400);

try {
    $link = protocoloFormularioObtener($pdo, (int) $idProtocolForm);
    if (!$link) responderJSON(false, null, 'Vínculo no encontrado.', 404);

    $scopeCompany = $link['id_company'] !== null ? (int) $link['id_company'] : null;
    if ($scopeCompany === null && !protocoloIsGlobalAdmin($pdo)) {
        responderJSON(false, null, 'Solo Administrador Completo puede modificar vínculos globales.', 403);
    }
    if ($scopeCompany !== null && !protocoloIsGlobalAdmin($pdo)
        && $scopeCompany !== protocoloCurrentCompany($pdo)) {
        responderJSON(false, null, 'El vínculo pertenece a otra empresa.', 403);
    }

    if (protocoloTieneAsignaciones($pdo, (int) $link['id_protocol'], $scopeCompany)) {
        responderJSON(false, null, 'Existen asignaciones que dependen de este formulario; no puede retirarse.', 409);
    }

    protocoloFormularioEliminar($pdo, (int) $idProtocolForm);
    $protocol = protocoloObtener($pdo, (int)$link['id_protocol']);
    auditTrailLogAction($pdo, $scopeCompany, 'protocolos', 'protocol_forms', $idProtocolForm,
        'id_form', $link['id_form'] ?? null, null, 'unassign', (string)($protocol['name'] ?? ('Protocolo #' . $link['id_protocol'])));
    responderJSON(true, null, 'Formulario desvinculado correctamente.');
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/protocolo-formularios-quitar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo desvincular el formulario.', 500);
}
