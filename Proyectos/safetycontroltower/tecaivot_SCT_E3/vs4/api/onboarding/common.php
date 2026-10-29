<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';

function onboardingInput(): array
{
    $input = json_decode((string)file_get_contents('php://input'), true);
    return is_array($input) ? $input : $_POST;
}

function onboardingCsrf(array $input): void
{
    $token = (string)($input['csrf_token'] ?? '');

    if (
        empty($_SESSION['csrf_token'])
        || $token === ''
        || !hash_equals((string)$_SESSION['csrf_token'], $token)
    ) {
        responderJSON(false, null, t('onboarding_error_session_expired'), 403);
    }
}

function onboardingErrorMessage(Throwable $error, string $fallbackKey): string
{
    if ($error instanceof SctOnboardingDomainException) {
        return t($error->getMessage());
    }

    return t($fallbackKey);
}
