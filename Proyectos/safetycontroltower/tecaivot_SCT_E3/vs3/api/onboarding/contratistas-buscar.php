<?php

require __DIR__ . '/common.php';

requireLogin();

$userId = currentUserId();
if (!$userId) {
    responderJSON(false, null, t('onboarding_error_session'), 401);
}

$term = trim((string)($_GET['q'] ?? ''));

try {
    $results = sctOnboarding($pdo)->searchContractors($userId, $term, 20);

    responderJSON(true, [
        'results' => array_map(
            static function (array $row): array {
                return [
                    'id' => (int)$row['id_contractor_company'],
                    'rut' => (string)$row['rut'],
                    'business_name' => (string)$row['business_name'],
                    'trade_name' => (string)($row['trade_name'] ?? ''),
                ];
            },
            $results
        ),
    ]);
} catch (Throwable $e) {
    error_log('onboarding/contratistas-buscar.php: ' . $e->getMessage());
    responderJSON(false, null, onboardingErrorMessage($e, 'onboarding_error_contractor_search'), 500);
}
