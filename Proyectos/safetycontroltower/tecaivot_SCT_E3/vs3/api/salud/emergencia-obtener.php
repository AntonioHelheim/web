<?php
require_once __DIR__ . '/common.php';
requireLogin();
requireCapability($pdo, 'health_profile.view_emergency');
require_once __DIR__ . '/../../app/Users/UserRepository.php';
$input = saludReadJsonInput();
requireCsrfToken($input);
$target = trim((string)($input['id_users'] ?? ''));
if ($target === '') responderJSON(false, null, t('health_invalid_request'), 422);

try {
    $actorCompany = currentUserCompanyId($pdo);
    $targetUser = (new SctUserRepository($pdo))->findActive($target);
    if (!$targetUser) responderJSON(false, null, t('health_profile_not_found'), 404);
    if (!currentUserHasCapability($pdo,'companies.view_all') && (int)$targetUser['id_company'] !== (int)$actorCompany) {
        responderJSON(false, null, t('health_no_permission'), 403);
    }
    $profile = healthCurrentProfile($pdo, $target);
    if (!$profile) responderJSON(false, null, t('health_current_profile_missing'), 404);

    // Acceso individual únicamente; nunca devuelve listados ni datos administrativos ajenos.
    $data = [
        'id_users'=>$target,
        'name'=>trim((string)$targetUser['name'].' '.(string)$targetUser['lastname']),
        'phone'=>$profile['phone'] ?? null,
        'emergency_name_1'=>$profile['emergency_name_1'] ?? null,
        'emergency_relation_1'=>$profile['emergency_relation_1'] ?? null,
        'emergency_phone_1'=>$profile['emergency_phone_1'] ?? null,
        'emergency_name_2'=>$profile['emergency_name_2'] ?? null,
        'emergency_relation_2'=>$profile['emergency_relation_2'] ?? null,
        'emergency_phone_2'=>$profile['emergency_phone_2'] ?? null,
        'conditions'=>$profile['conditions_json'] ?? [],
        'condition_other'=>$profile['condition_other_enc'] ?? null,
        'medication_choice'=>$profile['medication_choice'] ?? null,
        'medications_text'=>$profile['medications_text_enc'] ?? null,
        'medications_emergency'=>$profile['medications_emergency_enc'] ?? null,
        'allergies'=>$profile['allergies_json'] ?? [],
        'allergy_details'=>$profile['allergy_details_enc'] ?? null,
        'severe_reaction'=>$profile['severe_reaction'] ?? null,
        'severe_reaction_info'=>$profile['severe_reaction_info_enc'] ?? null,
        'restriction_choice'=>$profile['restriction_choice'] ?? null,
        'restriction_details'=>$profile['restriction_details_enc'] ?? null,
    ];
    auditTrailLogAction($pdo,(int)$targetUser['id_company'],'salud','worker_health_profile',(int)$profile['id_health_profile'],'health_profile_access',null,'emergency_view','review',$target,currentUserId());
    responderJSON(true,$data);
} catch (RuntimeException $e) {
    error_log('api/salud/emergencia-obtener.php: '.$e->getMessage());
    responderJSON(false,null,t('health_encryption_missing'),503);
} catch (Throwable $e) {
    error_log('api/salud/emergencia-obtener.php: '.$e->getMessage());
    responderJSON(false,null,t('health_emergency_read_error'),500);
}
