<?php
require_once __DIR__ . '/common.php';
$profile = saludRequireWorker($pdo);
$input = saludReadJsonInput();
requireCsrfToken($input);

try {
    $current = healthCurrentProfile($pdo, (string)$profile['id_users']);
    $phone = $current['phone'] ?? healthWorkerPhone($pdo, !empty($profile['id_worker']) ? (int)$profile['id_worker'] : null);
    if ($current) {
        $current['condition_other'] = $current['condition_other_enc'];
        $current['medications_text'] = $current['medications_text_enc'];
        $current['medications_emergency'] = $current['medications_emergency_enc'];
        $current['allergy_details'] = $current['allergy_details_enc'];
        $current['severe_reaction_info'] = $current['severe_reaction_info_enc'];
        $current['occupational_other'] = $current['occupational_other_enc'];
        $current['restriction_details'] = $current['restriction_details_enc'];
        $current['conditions'] = $current['conditions_json'];
        $current['allergies'] = $current['allergies_json'];
        $current['occupational_diseases'] = $current['occupational_diseases_json'];
        foreach (['condition_other_enc','medications_text_enc','medications_emergency_enc','allergy_details_enc','severe_reaction_info_enc','occupational_other_enc','restriction_details_enc','conditions_json','allergies_json','occupational_diseases_json'] as $f) unset($current[$f]);
    }
    responderJSON(true, [
        'required' => healthUserRequiresForm($pdo, (string)$profile['id_users']),
        'profile' => $current,
        'phone' => $phone,
        'user_profile' => [
            'name' => (string)($profile['name'] ?? ''),
            'lastname' => (string)($profile['lastname'] ?? ''),
            'email' => (string)($profile['id_users'] ?? ''),
            'rut' => (string)($profile['rut'] ?? ''),
            'language' => (string)($profile['language'] ?? idiomaActual()),
        ],
        'declaration_version' => HEALTH_DECLARATION_VERSION,
    ]);
} catch (RuntimeException $e) {
    error_log('api/salud/formulario-obtener.php: ' . $e->getMessage());
    responderJSON(false, null, t('health_encryption_missing'), 503);
} catch (Throwable $e) {
    error_log('api/salud/formulario-obtener.php: ' . $e->getMessage());
    responderJSON(false, null, t('health_save_error'), 500);
}
