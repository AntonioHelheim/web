<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../app/Onboarding/OnboardingQuestionAdminRepository.php';

function questionBankRequireApi(PDO $pdo): void
{
    requireLogin();
    requireCapability($pdo,'questions.manage');
}

function questionBankRequirePage(PDO $pdo): void
{
    requireCapabilityPage($pdo,'questions.manage','../../acceso-denegado.php');
}

function questionBankInput(): array
{
    $raw = file_get_contents('php://input');
    $decoded = $raw !== false ? json_decode($raw,true) : null;
    return is_array($decoded) ? $decoded : $_POST;
}

function questionBankRepository(PDO $pdo): SctOnboardingQuestionAdminRepository
{
    static $repos = [];
    $key = spl_object_id($pdo);
    return $repos[$key] ??= new SctOnboardingQuestionAdminRepository($pdo);
}
