<?php

require __DIR__ . '/common.php';

requireLogin();

$userId = currentUserId();
if (!$userId) {
    responderJSON(false, null, t('onboarding_error_session'), 401);
}

$service = sctOnboarding($pdo);

responderJSON(true, [
    'status' => $service->status($userId),
    'profile' => $service->profile($userId),
    'presentation' => $service->profilePresentation($userId),
    'consent_version' => SctOnboardingService::CONSENT_VERSION,
    'consent_text' => t('onboarding_consent_text'),
    'language' => idiomaActual(),
]);
