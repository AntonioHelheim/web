<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'protocols.forms');

$idCompany = filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT);

try {
    if (protocoloIsGlobalAdmin($pdo) && (!$idCompany || $idCompany <= 0)) {
        $stmt = $pdo->query(
            'SELECT f.id_form,f.id_company,f.name,f.description,f.state,NULL AS company_name,'
            . '       (SELECT COUNT(*) FROM dynamic_form_fields ff WHERE ff.id_form=f.id_form) AS field_count '
            . 'FROM dynamic_forms f WHERE f.state=1 AND f.id_company IS NULL ORDER BY f.name ASC'
        );
        responderJSON(true, $stmt->fetchAll());
    }

    $company = protocoloResolveCompany($pdo, $idCompany ? (int) $idCompany : null);
    responderJSON(true, protocoloFormulariosDisponibles($pdo, $company));
} catch (Throwable $e) {
    if (protocoloMigrationMessage($e)) responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    error_log('protocolos/formularios-disponibles: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron cargar los formularios.', 500);
}
