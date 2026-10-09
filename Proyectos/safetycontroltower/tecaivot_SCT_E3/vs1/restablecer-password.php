<?php
require __DIR__ . '/session_bootstrap.php';
aplicarCabecerasSeguridad();
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/passwords.php';

$token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? ''));
$tokenRow = $token !== '' ? passwordsFindValidToken($pdo, $token) : null;

// Si conocemos el idioma preferido del usuario, úsalo para esta pantalla.
if ($tokenRow) {
    $preferredLanguage = strtolower(trim((string) ($tokenRow['language'] ?? 'es')));
    if ($preferredLanguage === 'esp') {
        $preferredLanguage = 'es';
    }
    if (in_array($preferredLanguage, ['es', 'en', 'pt', 'fr', 'zh'], true)) {
        $_SESSION['site_lang'] = $preferredLanguage;
    }
}

require __DIR__ . '/i18n.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = false;
$wasActivation = $tokenRow
    ? ((string) ($tokenRow['purpose'] ?? '')) === PASSWORD_PURPOSE_ACTIVATION
    : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    $newPassword = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['password_confirm'] ?? '');

    if (
        $csrfToken === '' ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        $error = 'Tu sesión expiró. Abre nuevamente el enlace recibido por correo.';
    } elseif (!$tokenRow) {
        $error = t('password_invalid_text');
    } elseif (!hash_equals($newPassword, $confirmPassword)) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $policyError = passwordsValidateNewPassword($newPassword, (string) $tokenRow['id_users'], (string) ($tokenRow['rut'] ?? ''));
        if ($policyError !== null) {
            $error = $policyError;
        } else {
            try {
                $updatedUser = passwordsConsumeToken($pdo, $token, $newPassword);
                if ($updatedUser === null) {
                    $error = t('password_invalid_text');
                    $tokenRow = null;
                } else {
                    $success = true;
                    $tokenRow = null;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            } catch (Throwable $e) {
                error_log('restablecer-password.php: ' . $e->getMessage());
                $error = 'No fue posible actualizar la contraseña. Intenta nuevamente.';
            }
        }
    }
}

$langActual = idiomaActual();
$pageTitle = $wasActivation ? t('password_page_activation_title') : t('password_page_reset_title');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langActual, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#002259">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Safety Control Tower</title>

    <link rel="icon" href="./images/logos/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/login.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="sct-login-page">
    <div class="login-language">
        <label class="sr-only" for="resetLanguage"><?= htmlspecialchars(t('login_language_label'), ENT_QUOTES, 'UTF-8') ?></label>
        <select id="resetLanguage" aria-label="<?= htmlspecialchars(t('login_language_label'), ENT_QUOTES, 'UTF-8') ?>" onchange="if (/^(es|en|pt|fr|zh)$/.test(this.value)) { var u = new URLSearchParams(window.location.search); u.set('lang', this.value); window.location.href = '?' + u.toString(); }">
            <?php foreach (IDIOMAS_DISPONIBLES as $codigoIdioma): ?>
                <option value="<?= htmlspecialchars($codigoIdioma, ENT_QUOTES, 'UTF-8') ?>" <?= $codigoIdioma === $langActual ? 'selected' : '' ?>>
                    <?= htmlspecialchars(strtoupper($codigoIdioma), ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <main class="login-stage" aria-labelledby="passwordHeading">
        <section class="login-shell">
            <div class="login-brand" aria-label="Safety Control Tower">
                <img src="./images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                <div class="login-brand-name">SAFETY CONTROL TOWER</div>
            </div>

            <?php if ($success): ?>
                <div class="login-alert login-alert--success" role="status">
                    <?= htmlspecialchars(t('password_success_text'), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div id="passwordSuccess">
                    <h1 id="passwordHeading" class="password-reset-title">
                        <?= htmlspecialchars(t('password_success_title'), ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <a href="index.php" class="login-primary-button password-reset-link">
                        <?= htmlspecialchars(t('password_login_link'), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>

            <?php elseif (!$tokenRow): ?>
                <div class="login-alert login-alert--error" role="alert">
                    <?= htmlspecialchars($error !== '' ? $error : t('password_invalid_text'), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div id="passwordInvalid">
                    <h1 id="passwordHeading" class="password-reset-title">
                        <?= htmlspecialchars(t('password_invalid_title'), ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <a href="index.php" class="login-primary-button password-reset-link">
                        <?= htmlspecialchars(t('password_login_link'), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>

            <?php else: ?>
                <h1 id="passwordHeading" class="password-reset-title">
                    <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <p class="login-code-description">
                    <?= htmlspecialchars(t('password_page_intro'), ENT_QUOTES, 'UTF-8') ?>
                </p>

                <?php if ($error !== ''): ?>
                    <div class="login-alert login-alert--error" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="restablecer-password.php" novalidate>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="login-field">
                        <label class="sr-only" for="newPassword"><?= htmlspecialchars(t('password_new_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="password-field">
                            <input
                                type="password"
                                id="newPassword"
                                name="password"
                                minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                                autocomplete="new-password"
                                placeholder="<?= htmlspecialchars(t('password_new_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                required>
                            <button type="button" class="password-toggle" data-toggle-password="newPassword" aria-label="Mostrar u ocultar contraseña">VER</button>
                        </div>
                    </div>

                    <div class="login-field">
                        <label class="sr-only" for="confirmPassword"><?= htmlspecialchars(t('password_confirm_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="password-field">
                            <input
                                type="password"
                                id="confirmPassword"
                                name="password_confirm"
                                minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                                autocomplete="new-password"
                                placeholder="<?= htmlspecialchars(t('password_confirm_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                required>
                            <button type="button" class="password-toggle" data-toggle-password="confirmPassword" aria-label="Mostrar u ocultar contraseña">VER</button>
                        </div>
                    </div>

                    <p class="login-security-note password-requirements">
                        <?= htmlspecialchars(t('password_requirements'), ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <button type="submit" class="login-primary-button">
                        <?= htmlspecialchars(t('password_save_button'), ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.getAttribute('data-toggle-password'));
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    </script>
</body>
</html>
