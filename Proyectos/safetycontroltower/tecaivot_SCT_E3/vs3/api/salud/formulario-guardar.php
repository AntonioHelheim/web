<?php
require_once __DIR__ . '/common.php';

$profile = saludRequireWorker($pdo);
$input = saludReadJsonInput();
requireCsrfToken($input);

$name = trim((string)($input['profile_name'] ?? ''));
$lastname = trim((string)($input['profile_lastname'] ?? ''));
$language = strtolower(trim((string)($input['profile_language'] ?? '')));

$allowedLanguages = defined('IDIOMAS_DISPONIBLES')
    ? IDIOMAS_DISPONIBLES
    : ['es','en','pt','fr','zh'];

if (
    $name === ''
    || $lastname === ''
    || !in_array($language, $allowedLanguages, true)
) {
    responderJSON(
        false,
        ['fields' => ['profile_name','profile_lastname','profile_language']],
        t('health_required_error'),
        422
    );
}

$validation = healthValidatePayload($input);
if (!$validation['valid']) {
    responderJSON(false, ['fields' => $validation['errors']], t('health_required_error'), 422);
}

try {
    $pdo->beginTransaction();

    $before = $profile;
    $idUsers = (string)$profile['id_users'];
    $idCompany = (int)$profile['id_company'];
    $idWorker = !empty($profile['id_worker']) ? (int)$profile['id_worker'] : null;

    $stmt = $pdo->prepare(
        'UPDATE users
         SET name=:name,
             lastname=:lastname,
             language=:language,
             last_update=NOW()
         WHERE id_users=:id_users'
    );
    $stmt->execute([
        'name' => sctTextSubstr($name,0,50),
        'lastname' => sctTextSubstr($lastname,0,50),
        'language' => $language,
        'id_users' => $idUsers,
    ]);

    if ($idWorker) {
        $stmtWorker = $pdo->prepare(
            'UPDATE workers
             SET name=:name,
                 lastname=:lastname,
                 phone=:phone,
                 last_update=NOW()
             WHERE id_worker=:id_worker
               AND id_company=:id_company'
        );
        $stmtWorker->execute([
            'name' => sctTextSubstr($name,0,100),
            'lastname' => sctTextSubstr($lastname,0,100),
            'phone' => $validation['data']['phone'],
            'id_worker' => $idWorker,
            'id_company' => $idCompany,
        ]);
    }

    $profile['name'] = $name;
    $profile['lastname'] = $lastname;
    $profile['language'] = $language;

    $id = healthSaveProfile(
        $pdo,
        $profile,
        $validation['data'],
        t('health_declaration_text'),
        isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : null,
        false
    );

    auditTrailLogChanges(
        $pdo,
        $idCompany,
        'usuarios',
        'users',
        $idUsers,
        $before,
        [
            'name' => $name,
            'lastname' => $lastname,
            'language' => $language,
        ],
        'update',
        $idUsers,
        ['name','lastname','language'],
        $idUsers
    );

    $pdo->commit();

    responderJSON(
        true,
        [
            'id_health_profile' => $id,
            'required' => false,
        ],
        t('profile_health_saved')
    );
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('api/salud/formulario-guardar.php: ' . $e->getMessage());
    responderJSON(false, null, t('health_encryption_missing'), 503);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('api/salud/formulario-guardar.php: ' . $e->getMessage());
    responderJSON(false, null, t('health_save_error'), 500);
}
