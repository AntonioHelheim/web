<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/servicios/FormularioSubmissionService.php';

requireCapability($pdo, 'dynamic_forms.submit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

requireCsrfToken($_POST);

$idForm = filter_var($_POST['id_form'] ?? null, FILTER_VALIDATE_INT);
if (!$idForm) {
    responderJSON(false, null, 'Formulario no válido.', 400);
}

$answersRaw = json_decode((string) ($_POST['answers'] ?? '{}'), true);
if (!is_array($answersRaw)) {
    responderJSON(false, null, 'Las respuestas no son válidas.', 400);
}

$createdFiles = [];

try {
    formularioRequireSchema($pdo);

    $form = formularioObtener($pdo, (int) $idForm);
    if (!$form || (int) $form['state'] !== 1) {
        responderJSON(false, null, 'Formulario no disponible.', 404);
    }

    formularioAssertVisible($pdo, $form);

    $company = formularioCurrentCompany($pdo);
    $assignment = formularioAssertExecutionAccess(
        $pdo,
        $form,
        $company,
        (string) currentUserId()
    );
    $fields = formularioCampos($pdo, (int) $idForm);
    if (!$fields) {
        responderJSON(false, null, 'Este formulario no tiene campos configurados.', 400);
    }

    $validated = formularioSubmissionValidate($fields, $answersRaw, $_FILES);

    $pdo->beginTransaction();

    $idSubmission = formularioSubmissionPersist(
        $pdo,
        (int) $idForm,
        $company,
        (string) currentUserId(),
        $fields,
        $validated,
        $createdFiles
    );

    // P76: si el formulario usa asignación explícita, cerrar exactamente la
    // asignación validada. Así la notificación, Mi espacio y el resultado usan
    // el mismo registro y no se mezclan empresas/usuarios.
    if ($assignment && !empty($assignment['id_form_assignment'])) {
        $assignmentUpdate = $pdo->prepare(
            "UPDATE dynamic_form_assignments SET status='submitted', last_update=NOW() "
            . "WHERE id_form_assignment=:id AND id_company=:company AND id_users=:user AND status='pending'"
        );
        $assignmentUpdate->execute([
            'id'=>(int)$assignment['id_form_assignment'],
            'company'=>$company,
            'user'=>(string)currentUserId(),
        ]);
        if ($assignmentUpdate->rowCount() !== 1) {
            throw new RuntimeException('La asignación cambió de estado antes de completar el envío.');
        }
    }

    $pdo->commit();

    responderJSON(
        true,
        ['id_submission' => $idSubmission],
        'Formulario enviado correctamente.',
        201
    );
} catch (FormularioValidationException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    formularioSubmissionCleanupFiles($createdFiles);
    responderJSON(false, null, $e->getMessage(), 400);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    formularioSubmissionCleanupFiles($createdFiles);

    if (formularioMigrationMessage($e)) {
        responderJSON(
            false,
            null,
            'El Motor de Formularios Dinámicos requiere aplicar su migración de Etapa 2.',
            503
        );
    }

    error_log('formularios/formulario-enviar: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo registrar el formulario.', 500);
}
