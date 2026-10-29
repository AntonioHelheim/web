<?php

require __DIR__ . '/common.php';

requireLogin();

$userId = currentUserId();
if (!$userId) {
    responderJSON(false, null, t('onboarding_error_session'), 401);
}

try {
    responderJSON(true, sctOnboarding($pdo)->assessment($userId));
} catch (Throwable $e) {
    error_log('onboarding/evaluacion-obtener.php: ' . $e->getMessage());

    $status = $e instanceof SctOnboardingDomainException ? 400 : 500;
    responderJSON(false, null, onboardingErrorMessage($e, 'onboarding_error_assessment_unavailable'), $status);
}
