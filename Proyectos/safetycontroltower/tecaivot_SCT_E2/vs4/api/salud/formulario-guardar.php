<?php
require_once __DIR__ . '/common.php';
$profile = saludRequireWorker($pdo);
$input = saludReadJsonInput();
requireCsrfToken($input);

$validation = healthValidatePayload($input);
if (!$validation['valid']) {
    responderJSON(false, ['fields' => $validation['errors']], t('health_required_error'), 422);
}

try {
    $id = healthSaveProfile(
        $pdo,
        $profile,
        $validation['data'],
        t('health_declaration_text'),
        isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : null
    );
    responderJSON(true, ['id_health_profile' => $id, 'required' => false], t('health_saved'));
} catch (RuntimeException $e) {
    error_log('api/salud/formulario-guardar.php: ' . $e->getMessage());
    responderJSON(false, null, t('health_encryption_missing'), 503);
} catch (Throwable $e) {
    error_log('api/salud/formulario-guardar.php: ' . $e->getMessage());
    responderJSON(false, null, t('health_save_error'), 500);
}
