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

try {
    $submittedLanguage = strtolower(trim((string)($input['language'] ?? idiomaActual())));
    if (!in_array($submittedLanguage, IDIOMAS_DISPONIBLES, true)) {
        throw new SctOnboardingDomainException('onboarding_error_invalid_language');
    }

    $submittedStrings = require __DIR__ . '/../../lang/' . $submittedLanguage . '.php';
    $consentText = (string)(
        $submittedStrings['onboarding_unified_consent_text']
        ?? t('onboarding_unified_consent_text')
    );

    $result = sctOnboarding($pdo)->saveProfile(
        $userId,
        $input,
        (string)($_SERVER['REMOTE_ADDR'] ?? ''),
        (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
        $consentText,
        $submittedLanguage,
        $consentText
    );

    // La persistencia de BD ya ocurrió dentro de saveProfile(). Aquí sólo se
    // sincroniza la sesión; no se vuelve a escribir el registro users.
    $_SESSION['site_lang'] = $submittedLanguage;

    $savedMessage = (string)(
        $submittedStrings['onboarding_saved']
        ?? t('onboarding_saved')
    );

    responderJSON(true, $result, $savedMessage);
} catch (Throwable $e) {
    error_log(
        'onboarding/perfil-guardar.php [' . get_class($e) . ']: ' .
        $e->getMessage()
    );

    if ($e instanceof SctOnboardingDomainException) {
        responderJSON(false, null, onboardingErrorMessage($e, 'onboarding_error_save'), 422);
    }

    $message = t('onboarding_error_save');

    if (
        stripos($e->getMessage(), 'SCT_HEALTH_ENCRYPTION_KEY') !== false
        || stripos($e->getMessage(), 'clave') !== false
        || stripos($e->getMessage(), 'cifrad') !== false
    ) {
        $message = t('onboarding_error_health_storage');
    }

    responderJSON(false, null, $message, 500);
}
