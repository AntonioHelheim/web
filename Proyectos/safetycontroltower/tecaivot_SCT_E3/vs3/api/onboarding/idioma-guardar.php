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

$language = strtolower(trim((string)($input['language'] ?? '')));

if (!in_array($language, IDIOMAS_DISPONIBLES, true)) {
    responderJSON(false, null, t('onboarding_error_invalid_language'), 422);
}

try {
    $stmt = $pdo->prepare(
        'UPDATE users
         SET language=:language,
             last_update=NOW()
         WHERE id_users=:id_users'
    );
    $stmt->execute([
        'language' => $language,
        'id_users' => $userId,
    ]);

    $_SESSION['site_lang'] = $language;
    $GLOBALS['__strings'] = require __DIR__ . '/../../lang/' . $language . '.php';
    $GLOBALS['__lang_actual'] = $language;

    responderJSON(
        true,
        ['language' => $language],
        t('onboarding_language_changed')
    );
} catch (Throwable $e) {
    error_log('onboarding/idioma-guardar.php: ' . $e->getMessage());
    responderJSON(false, null, t('onboarding_error_language_change'), 500);
}
