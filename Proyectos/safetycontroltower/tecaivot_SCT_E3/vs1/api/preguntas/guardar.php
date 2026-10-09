<?php
require __DIR__ . '/common.php';
questionBankRequireApi($pdo);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responderJSON(false,null,t('onboarding_error_method'),405);
}

$input = questionBankInput();
requireCsrfToken($input);

try {
    $id = questionBankRepository($pdo)->save(
        $input,
        (string)currentUserId()
    );
    responderJSON(true,['id_question'=>$id],t('question_bank_saved'));
} catch (InvalidArgumentException $e) {
    responderJSON(false,null,t($e->getMessage()),422);
} catch (Throwable $e) {
    error_log('preguntas/guardar.php: '.$e->getMessage());
    responderJSON(false,null,t('question_bank_error'),500);
}
