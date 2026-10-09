<?php

require __DIR__ . '/common.php';

requireLogin();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responderJSON(false, null, t('onboarding_error_method'), 405);
}

$input = onboardingInput();
onboardingCsrf($input);

$userId = currentUserId();
if (!$userId) {
    responderJSON(false, null, t('onboarding_error_session'), 401);
}

$attemptId = filter_var(
    $input['attempt_id'] ?? null,
    FILTER_VALIDATE_INT
);
$answers = $input['answers'] ?? [];

if (!$attemptId || !is_array($answers)) {
    responderJSON(false, null, t('onboarding_error_invalid_assessment'), 400);
}

try {
    $result = sctOnboarding($pdo)->saveAssessmentDraft(
        $userId,
        (int)$attemptId,
        $answers
    );

    responderJSON(
        true,
        $result,
        t('onboarding_assessment_draft_saved')
    );
} catch (Throwable $e) {
    error_log(
        'onboarding/evaluacion-borrador-guardar.php: ' . $e->getMessage()
    );

    $status = $e instanceof SctOnboardingDomainException ? 422 : 500;

    responderJSON(
        false,
        null,
        onboardingErrorMessage(
            $e,
            'onboarding_error_assessment_draft'
        ),
        $status
    );
}
