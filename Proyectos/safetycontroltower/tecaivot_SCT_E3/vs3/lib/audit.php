<?php
/**
 * Safety Control Tower - Historial de cambios / auditoría a nivel de campo.
 *
 * Esta capa registra cambios administrativos sin almacenar secretos. El código
 * puede desplegarse antes que la migración: mientras falten las columnas nuevas
 * de change_history, las funciones de auditoría quedan en no-op seguro.
 */

/** @return array<string,bool> */
function auditTrailSchemaColumns(PDO $pdo): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }

    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM change_history');
        $columns = [];
        foreach ($stmt->fetchAll() as $row) {
            $field = (string) ($row['Field'] ?? '');
            if ($field !== '') {
                $columns[$field] = true;
            }
        }
        return $cache = $columns;
    } catch (Throwable $e) {
        error_log('auditTrailSchemaColumns: ' . $e->getMessage());
        return $cache = [];
    }
}

function auditTrailSchemaReady(PDO $pdo): bool
{
    $columns = auditTrailSchemaColumns($pdo);
    foreach (['id_company','module','action','record_label','request_id'] as $required) {
        if (!isset($columns[$required])) {
            return false;
        }
    }
    return isset($columns['id_change'],$columns['table_name'],$columns['record_id'],$columns['field_name'],$columns['old_value'],$columns['new_value'],$columns['changed_by'],$columns['changed_at']);
}

function auditTrailRequestId(): string
{
    static $id = null;
    if ($id !== null) {
        return $id;
    }
    try {
        $id = bin2hex(random_bytes(16));
    } catch (Throwable $e) {
        $id = str_replace('.', '', uniqid('sct', true));
    }
    return $id;
}

function auditTrailFieldIsSecret(string $field): bool
{
    $field = strtolower(trim($field));
    if ($field === '') return false;

    $exact = [
        'password','password_hash','password_confirmation','current_password',
        'code_hash','token','token_hash','reset_token','activation_token',
        'otp','otp_code','csrf','csrf_token','secret','api_key','authorization'
    ];
    if (in_array($field, $exact, true)) return true;

    return (bool) preg_match('/(?:password|passwd|token|secret|code_hash|otp|csrf|api[_-]?key|authorization)/i', $field);
}

function auditTrailFieldIgnored(string $field): bool
{
    $field = strtolower(trim($field));
    return in_array($field, [
        'date_create','date_created','created_at','last_update','updated_at',
        'created_by','create_by','changed_at','last_access'
    ], true);
}


function auditTrailClip(string $value, int $maxChars): string
{
    if ($maxChars <= 0 || $value === '') return '';
    if (function_exists('sctTextSubstr')) {
        return sctTextSubstr($value, 0, $maxChars);
    }
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxChars, 'UTF-8');
    }
    return substr($value, 0, $maxChars);
}

function auditTrailNormalizeValue($value): ?string
{
    if ($value === null) return null;
    if (is_bool($value)) return $value ? '1' : '0';
    if (is_array($value) || is_object($value)) {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $value = is_string($encoded) ? $encoded : '[valor no serializable]';
    } elseif (is_scalar($value)) {
        $value = (string) $value;
    } else {
        $value = '[valor no representable]';
    }

    // Limita el crecimiento del historial sin perder la utilidad de auditoría.
    if (function_exists('sctTextLength') && function_exists('sctTextSubstr')) {
        if (sctTextLength($value) > 8000) {
            $value = sctTextSubstr($value, 0, 8000) . '… [truncado]';
        }
    } elseif (strlen($value) > 12000) {
        $value = substr($value, 0, 12000) . '… [truncado]';
    }
    return $value;
}

/**
 * Inserta una fila de auditoría. Nunca registra campos sensibles.
 */
function auditTrailInsert(
    PDO $pdo,
    ?int $idCompany,
    string $module,
    string $tableName,
    $recordId,
    string $fieldName,
    $oldValue,
    $newValue,
    string $action,
    ?string $recordLabel = null,
    ?string $actor = null,
    ?string $requestId = null
): bool {
    if (!auditTrailSchemaReady($pdo)) {
        return false;
    }
    if (auditTrailFieldIsSecret($fieldName) || auditTrailFieldIgnored($fieldName)) {
        return false;
    }

    $module = trim($module);
    $tableName = trim($tableName);
    $fieldName = trim($fieldName);
    $action = trim($action);
    if ($module === '' || $tableName === '' || $fieldName === '' || $action === '') {
        return false;
    }

    $actor = trim((string) ($actor ?? (function_exists('currentUserId') ? currentUserId() : '')));
    if ($actor === '') $actor = 'system';
    $requestId = trim((string) ($requestId ?? auditTrailRequestId()));
    if ($requestId === '') $requestId = auditTrailRequestId();

    $old = auditTrailNormalizeValue($oldValue);
    $new = auditTrailNormalizeValue($newValue);
    if ($old === $new && !in_array($action, ['create','assign','unassign','delete','issue_access','upload','remove','review','track'], true)) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO change_history
             (id_company,module,action,table_name,record_id,record_label,field_name,old_value,new_value,changed_by,changed_at,request_id)
             VALUES
             (:id_company,:module,:action,:table_name,:record_id,:record_label,:field_name,:old_value,:new_value,:changed_by,NOW(),:request_id)'
        );
        return $stmt->execute([
            'id_company' => $idCompany,
            'module' => auditTrailClip($module, 50),
            'action' => auditTrailClip($action, 30),
            'table_name' => auditTrailClip($tableName, 64),
            'record_id' => auditTrailClip((string) $recordId, 50),
            'record_label' => $recordLabel !== null ? auditTrailClip(trim($recordLabel), 255) : null,
            'field_name' => auditTrailClip($fieldName, 64),
            'old_value' => $old,
            'new_value' => $new,
            'changed_by' => auditTrailClip($actor, 50),
            'request_id' => auditTrailClip($requestId, 64),
        ]);
    } catch (Throwable $e) {
        // El historial nunca debe dejar inutilizable la operación principal.
        // El error queda en log para diagnóstico y QA/hardening.
        error_log('auditTrailInsert: ' . $e->getMessage());
        return false;
    }
}

/**
 * Registra diferencias por campo entre dos snapshots.
 * @param array<string,mixed> $before
 * @param array<string,mixed> $after
 * @param string[]|null $fields
 */
function auditTrailLogChanges(
    PDO $pdo,
    ?int $idCompany,
    string $module,
    string $tableName,
    $recordId,
    array $before,
    array $after,
    string $action = 'update',
    ?string $recordLabel = null,
    ?array $fields = null,
    ?string $actor = null,
    ?string $requestId = null
): int {
    if (!auditTrailSchemaReady($pdo)) return 0;

    $fields = $fields ?? array_values(array_unique(array_merge(array_keys($before), array_keys($after))));
    $requestId = $requestId ?? auditTrailRequestId();
    $count = 0;
    foreach ($fields as $field) {
        $field = (string) $field;
        if (auditTrailFieldIsSecret($field) || auditTrailFieldIgnored($field)) continue;
        $old = $before[$field] ?? null;
        $new = $after[$field] ?? null;
        if (auditTrailNormalizeValue($old) === auditTrailNormalizeValue($new) && $action !== 'create') continue;
        if (auditTrailInsert($pdo, $idCompany, $module, $tableName, $recordId, $field, $old, $new, $action, $recordLabel, $actor, $requestId)) {
            $count++;
        }
    }
    return $count;
}

/**
 * Registra una acción que no se representa como UPDATE directo de una entidad.
 */
function auditTrailLogAction(
    PDO $pdo,
    ?int $idCompany,
    string $module,
    string $tableName,
    $recordId,
    string $fieldName,
    $oldValue,
    $newValue,
    string $action,
    ?string $recordLabel = null,
    ?string $actor = null,
    ?string $requestId = null
): bool {
    return auditTrailInsert(
        $pdo,
        $idCompany,
        $module,
        $tableName,
        $recordId,
        $fieldName,
        $oldValue,
        $newValue,
        $action,
        $recordLabel,
        $actor,
        $requestId
    );
}
