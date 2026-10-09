<?php
require __DIR__ . '/common.php';
questionBankRequireApi($pdo);

try {
    responderJSON(true,[
        'languages'=>SctOnboardingQuestionAdminRepository::LANGUAGES,
        'questions'=>questionBankRepository($pdo)->listAll(),
    ]);
} catch (Throwable $e) {
    error_log('preguntas/listar.php: '.$e->getMessage());
    responderJSON(false,null,t('question_bank_error'),500);
}
