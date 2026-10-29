<?php
/**
 * FormularioSubmissionService.php
 *
 * Servicio reutilizable para validar y persistir envíos del Motor de
 * Formularios Dinámicos. Se usa tanto desde /api/formularios como desde
 * Protocolos MINSAL para evitar duplicar la lógica de validación y archivos.
 */

class FormularioValidationException extends RuntimeException
{
}

function formularioSubmissionAllowedFileTypes(): array
{
    return [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
        'pdf'  => ['application/pdf'],
        'txt'  => ['text/plain'],
        'doc'  => ['application/msword', 'application/octet-stream'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
        ],
        'xls'  => ['application/vnd.ms-excel', 'application/octet-stream'],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/octet-stream',
        ],
    ];
}

function formularioSubmissionOptions(array $field): array
{
    if ($field['options'] === null || trim((string) $field['options']) === '') {
        return [];
    }

    $result = [];
    foreach (explode('|', (string) $field['options']) as $raw) {
        $value = trim($raw);
        if ($value !== '' && !in_array($value, $result, true)) {
            $result[] = $value;
        }
    }
    return $result;
}

/**
 * Valida todas las respuestas de un formulario.
 *
 * @return array<int,array<string,mixed>> indexado por id_field.
 * @throws FormularioValidationException
 */
function formularioSubmissionValidate(
    array $fields,
    array $answersRaw,
    array $files
): array {
    $validated = [];

    foreach ($fields as $field) {
        $idField = (int) ($field['id_field'] ?? 0);
        $label = trim((string) ($field['label'] ?? 'Campo'));
        $type = strtolower(trim((string) ($field['field_type'] ?? '')));
        $required = (int) ($field['is_required'] ?? 0) === 1;
        $options = formularioSubmissionOptions($field);

        if ($idField <= 0) {
            throw new RuntimeException('Definición de campo inválida.');
        }

        if ($type === 'file') {
            $key = 'file_' . $idField;
            $file = $files[$key] ?? null;
            $error = is_array($file) ? (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;

            if (!$file || $error === UPLOAD_ERR_NO_FILE) {
                if ($required) {
                    throw new FormularioValidationException(
                        'Debes adjuntar el archivo solicitado en "' . $label . '".'
                    );
                }

                $validated[$idField] = [
                    'type' => 'file',
                    'file' => null,
                    'value' => null,
                ];
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                throw new FormularioValidationException(
                    'No se pudo recibir el archivo de "' . $label . '".'
                );
            }

            $size = (int) ($file['size'] ?? 0);
            if ($size <= 0 || $size > 5 * 1024 * 1024) {
                throw new FormularioValidationException(
                    'Los archivos deben pesar como máximo 5 MB.'
                );
            }

            $original = basename((string) ($file['name'] ?? 'archivo'));
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $allowed = formularioSubmissionAllowedFileTypes();

            if (!isset($allowed[$ext])) {
                throw new FormularioValidationException(
                    'Tipo de archivo no permitido en "' . $label . '".'
                );
            }

            $mime = 'application/octet-stream';
            if (function_exists('finfo_open')) {
                $fi = finfo_open(FILEINFO_MIME_TYPE);
                if ($fi) {
                    $detected = finfo_file($fi, (string) ($file['tmp_name'] ?? ''));
                    if (is_string($detected) && $detected !== '') {
                        $mime = $detected;
                    }
                    finfo_close($fi);
                }
            }

            if (!in_array($mime, $allowed[$ext], true)) {
                throw new FormularioValidationException(
                    'El contenido del archivo no coincide con un tipo permitido.'
                );
            }

            $validated[$idField] = [
                'type' => 'file',
                'file' => $file,
                'ext' => $ext,
                'mime' => $mime,
                'original' => $original,
                'value' => null,
            ];
            continue;
        }

        $value = $answersRaw[(string) $idField] ?? $answersRaw[$idField] ?? null;

        if ($type === 'checkbox') {
            if (!is_array($value)) {
                $value = ($value === null || $value === '') ? [] : [$value];
            }

            $clean = [];
            foreach ($value as $candidate) {
                $candidate = trim((string) $candidate);
                if ($candidate !== '' && !in_array($candidate, $clean, true)) {
                    $clean[] = $candidate;
                }
            }

            if ($required && !$clean) {
                throw new FormularioValidationException(
                    'Debes completar "' . $label . '".'
                );
            }

            foreach ($clean as $candidate) {
                if (!in_array($candidate, $options, true)) {
                    throw new FormularioValidationException(
                        'Respuesta no válida en "' . $label . '".'
                    );
                }
            }

            $validated[$idField] = [
                'type' => 'checkbox',
                'value' => json_encode($clean, JSON_UNESCAPED_UNICODE),
            ];
            continue;
        }

        $value = is_scalar($value) ? trim((string) $value) : '';

        if ($required && $value === '') {
            throw new FormularioValidationException(
                'Debes completar "' . $label . '".'
            );
        }

        if ($value === '') {
            $validated[$idField] = [
                'type' => $type,
                'value' => null,
            ];
            continue;
        }

        if (sctTextLength($value) > 10000) {
            throw new FormularioValidationException(
                'La respuesta de "' . $label . '" es demasiado extensa.'
            );
        }

        if ($type === 'number' && !is_numeric($value)) {
            throw new FormularioValidationException(
                'Debes ingresar un número válido en "' . $label . '".'
            );
        }

        if ($type === 'date') {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                throw new FormularioValidationException(
                    'Debes ingresar una fecha válida en "' . $label . '".'
                );
            }
        }

        if ($type === 'select' && !in_array($value, $options, true)) {
            throw new FormularioValidationException(
                'Respuesta no válida en "' . $label . '".'
            );
        }

        $validated[$idField] = [
            'type' => $type,
            'value' => $value,
        ];
    }

    return $validated;
}

/**
 * Persiste una cabecera dynamic_form_submissions y todas sus respuestas.
 * La transacción debe ser administrada por el caller.
 *
 * @param string[] $createdFiles archivos físicos a limpiar si la transacción falla.
 */
function formularioSubmissionPersist(
    PDO $pdo,
    int $idForm,
    int $idCompany,
    string $idUsers,
    array $fields,
    array $validated,
    array &$createdFiles
): int {
    formularioRequireSchema($pdo);

    $stmt = $pdo->prepare(
        "INSERT INTO dynamic_form_submissions "
        . "(id_form,id_company,id_users,status,submitted_at,created_by,date_create,last_update) "
        . "VALUES (:id_form,:id_company,:id_users,'submitted',NOW(),:created_by,NOW(),NOW())"
    );
    $stmt->execute([
        'id_form' => $idForm,
        'id_company' => $idCompany,
        'id_users' => $idUsers,
        'created_by' => $idUsers,
    ]);
    $idSubmission = (int) $pdo->lastInsertId();

    $insert = $pdo->prepare(
        'INSERT INTO dynamic_form_answers '
        . '(id_submission,id_field,value_text,file_path,original_name,mime_type,created_by,date_create,last_update) '
        . 'VALUES (:id_submission,:id_field,:value_text,:file_path,:original_name,:mime_type,:created_by,NOW(),NOW())'
    );

    $uploadDir = __DIR__ . '/../../uploads/formularios';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('No se pudo crear el directorio de formularios.');
    }

    foreach ($fields as $field) {
        $idField = (int) $field['id_field'];
        if (!isset($validated[$idField])) {
            throw new RuntimeException('La validación del formulario está incompleta.');
        }

        $value = $validated[$idField];
        $filePath = null;
        $originalName = null;
        $mimeType = null;
        $valueText = $value['value'] ?? null;

        if (($value['type'] ?? '') === 'file' && !empty($value['file'])) {
            $name = 'form_' . $idForm
                . '_submission_' . $idSubmission
                . '_field_' . $idField
                . '_' . bin2hex(random_bytes(8))
                . '.' . $value['ext'];

            $destination = $uploadDir . '/' . $name;
            if (!move_uploaded_file((string) $value['file']['tmp_name'], $destination)) {
                throw new RuntimeException('No se pudo guardar el archivo adjunto.');
            }

            $createdFiles[] = $destination;
            $filePath = 'uploads/formularios/' . $name;
            $originalName = (string) $value['original'];
            $mimeType = (string) $value['mime'];
        }

        $insert->execute([
            'id_submission' => $idSubmission,
            'id_field' => $idField,
            'value_text' => $valueText,
            'file_path' => $filePath,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'created_by' => $idUsers,
        ]);
    }

    return $idSubmission;
}

function formularioSubmissionCleanupFiles(array $createdFiles): void
{
    foreach ($createdFiles as $path) {
        if (is_string($path) && is_file($path)) {
            @unlink($path);
        }
    }
}
