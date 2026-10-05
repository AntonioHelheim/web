<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/servicios/FormularioSubmissionService.php';

requireCapability($pdo, 'protocols.execute');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}
requireCsrfToken($_POST);

$idAssignment = filter_var($_POST['id_protocol_assignment'] ?? null, FILTER_VALIDATE_INT);
if (!$idAssignment) {
    responderJSON(false, null, 'Asignación no válida.', 400);
}

$answersRaw = json_decode((string) ($_POST['answers'] ?? '{}'), true);
$includedForms = json_decode((string) ($_POST['included_forms'] ?? '[]'), true);

if (!is_array($answersRaw) || !is_array($includedForms)) {
    responderJSON(false, null, 'Las respuestas enviadas no son válidas.', 400);
}

$included = [];
foreach ($includedForms as $id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id && !in_array((int) $id, $included, true)) {
        $included[] = (int) $id;
    }
}

$createdFiles = [];

try {
    protocoloRequireSchema($pdo);
    formularioRequireSchema($pdo);

    $assignment = protocoloAsignacionObtener($pdo, (int) $idAssignment);
    if (!$assignment) {
        responderJSON(false, null, 'Asignación no encontrada.', 404);
    }
    if (!protocoloUserCanExecuteAssignment($pdo, $assignment)) {
        responderJSON(false, null, 'Esta asignación no corresponde a tu cuenta.', 403);
    }
    if ((string) $assignment['state'] !== 'activa') {
        responderJSON(false, null, 'La asignación no se encuentra activa.', 409);
    }
    if ((int) $assignment['protocol_state'] !== 1) {
        responderJSON(false, null, 'El protocolo se encuentra inactivo.', 409);
    }
    if (!protocoloAssignmentProtocolIsEffective($assignment)) {
        responderJSON(false, null, 'El protocolo se encuentra fuera de su período de vigencia.', 409);
    }

    $forms = protocoloFormularios(
        $pdo,
        (int) $assignment['id_protocol'],
        (int) $assignment['id_company']
    );
    if (!$forms) {
        responderJSON(false, null, 'El protocolo no tiene formularios configurados.', 409);
    }

    $prepared = [];
    foreach ($forms as $link) {
        $idProtocolForm = (int) $link['id_protocol_form'];
        $required = (int) $link['is_required'] === 1;
        $selected = in_array($idProtocolForm, $included, true);

        if ($required && !$selected) {
            responderJSON(
                false,
                null,
                'Debes completar el formulario obligatorio "' . $link['form_name'] . '".',
                400
            );
        }
        if (!$selected) {
            continue;
        }
        if ((int) $link['form_state'] !== 1) {
            responderJSON(false, null, 'El formulario "' . $link['form_name'] . '" está inactivo.', 409);
        }

        $fields = formularioCampos($pdo, (int) $link['id_form']);
        if (!$fields) {
            responderJSON(false, null, 'El formulario "' . $link['form_name'] . '" no tiene campos.', 409);
        }

        $validated = formularioSubmissionValidate($fields, $answersRaw, $_FILES);
        $prepared[] = [
            'link' => $link,
            'fields' => $fields,
            'validated' => $validated,
        ];
    }

    if (!$prepared) {
        responderJSON(false, null, 'Selecciona al menos un formulario para registrar la ejecución.', 400);
    }

    $pdo->beginTransaction();

    // Serializa ciclos sobre la misma asignación.
    $lock = $pdo->prepare(
        'SELECT id_protocol_assignment FROM protocol_assignments '
        . 'WHERE id_protocol_assignment=:id_assignment FOR UPDATE'
    );
    $lock->execute(['id_assignment' => (int) $idAssignment]);
    if (!$lock->fetchColumn()) {
        throw new RuntimeException('Asignación no disponible durante el registro.');
    }

    if (protocoloAsignacionTieneRevisionPendiente($pdo, (int) $idAssignment)) {
        $pdo->rollBack();
        responderJSON(
            false,
            null,
            'Ya existe una ejecución pendiente de revisión. Debe revisarse antes de iniciar un nuevo ciclo.',
            409
        );
    }

    $cycle = protocoloSiguienteCiclo($pdo, (int) $idAssignment);
    $idExecution = protocoloEjecucionCrear(
        $pdo,
        (int) $idAssignment,
        $cycle,
        (string) currentUserId()
    );

    foreach ($prepared as $item) {
        $idSubmission = formularioSubmissionPersist(
            $pdo,
            (int) $item['link']['id_form'],
            (int) $assignment['id_company'],
            (string) currentUserId(),
            $item['fields'],
            $item['validated'],
            $createdFiles
        );

        protocoloEjecucionVincularSubmission(
            $pdo,
            $idExecution,
            (int) $item['link']['id_protocol_form'],
            $idSubmission,
            (string) currentUserId()
        );
    }

    $pdo->commit();

    responderJSON(
        true,
        [
            'id_protocol_execution' => $idExecution,
            'cycle_number' => $cycle,
        ],
        'Ejecución registrada y enviada a revisión.',
        201
    );
} catch (FormularioValidationException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    formularioSubmissionCleanupFiles($createdFiles);
    responderJSON(false, null, $e->getMessage(), 400);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    formularioSubmissionCleanupFiles($createdFiles);

    if (protocoloMigrationMessage($e)) {
        responderJSON(false, null, 'Debes aplicar la actualización SQL de Protocolos MINSAL.', 503);
    }
    error_log('protocolos/ejecucion-enviar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo registrar la ejecución del protocolo.', 500);
}
