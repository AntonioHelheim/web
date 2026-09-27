<?php
/**
 * index.php
 * Pantalla de acceso de Safety Control Tower.
 *
 * Flujo de autenticación:
 *   1) correo + contraseña
 *   2) código de un solo uso enviado por correo
 */
require __DIR__ . '/session_bootstrap.php';
aplicarCabecerasSeguridad();
require __DIR__ . '/i18n.php';
require_once __DIR__ . '/lib/passwords.php';

// Una sesión ya autenticada no necesita volver a pasar por el login.
if (!empty($_SESSION['logged_in'])) {
    header('Location: bienvenida.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$langActual = idiomaActual();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langActual, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="helheim.cl">
    <meta name="copyright" content="Tecaivot">
    <meta name="theme-color" content="#002259">
    <meta name="robots" content="noindex, nofollow">

    <title><?= htmlspecialchars(t('login_title'), ENT_QUOTES, 'UTF-8') ?> — Safety Control Tower</title>
    <meta name="description" content="Safety Control Tower — acceso seguro a la plataforma.">

    <link rel="icon" href="./images/logos/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="./images/logos/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

    <!-- build: <?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?> -->
    <link rel="stylesheet" href="css/login.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="sct-login-page">

    <div class="login-language">
        <label class="sr-only" for="loginLanguage"><?= htmlspecialchars(t('login_language_label'), ENT_QUOTES, 'UTF-8') ?></label>
        <select id="loginLanguage" aria-label="<?= htmlspecialchars(t('login_language_label'), ENT_QUOTES, 'UTF-8') ?>">
            <?php foreach (IDIOMAS_DISPONIBLES as $codigoIdioma): ?>
                <option value="<?= htmlspecialchars($codigoIdioma, ENT_QUOTES, 'UTF-8') ?>"
                    <?= $codigoIdioma === $langActual ? 'selected' : '' ?>>
                    <?= htmlspecialchars(strtoupper($codigoIdioma), ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <main class="login-stage" aria-labelledby="loginHeading">
        <section class="login-shell">

            <div class="login-brand" aria-label="Safety Control Tower">
                <img src="./images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                <div class="login-brand-name">SAFETY CONTROL TOWER</div>
            </div>

            <div id="loginAlert" class="login-alert d-none" role="alert" aria-live="polite"></div>

            <form
                id="loginForm"
                method="post"
                action="login.php"
                data-password-request-url="password-request.php"
                data-password-update-url="password-update.php"
                data-local-reset-ready="<?= htmlspecialchars(t('login_reset_local_ready'), ENT_QUOTES, 'UTF-8') ?>"
                data-local-reset-saved="<?= htmlspecialchars(t('login_reset_local_saved'), ENT_QUOTES, 'UTF-8') ?>"
                data-password-mismatch="<?= htmlspecialchars(t('password_mismatch'), ENT_QUOTES, 'UTF-8') ?>"
                novalidate>
                <input
                    type="hidden"
                    name="csrf_token"
                    id="csrfToken"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <!-- PASO 1: PRIMER FACTOR (CONTRASEÑA) -->
                <div id="loginStepCredentials">
                    <h1 id="loginHeading" class="sr-only"><?= htmlspecialchars(t('login_title'), ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="login-intro"><?= htmlspecialchars(t('login_subtitle'), ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="login-field">
                        <label class="sr-only" for="loginEmail"><?= htmlspecialchars(t('login_email_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input
                            type="email"
                            id="loginEmail"
                            name="email"
                            placeholder="<?= htmlspecialchars(t('login_email_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="username"
                            maxlength="50"
                            aria-describedby="loginEmailError"
                            required>
                        <div id="loginEmailError" class="field-error d-none" aria-live="polite"></div>
                    </div>

                    <div class="login-field">
                        <label class="sr-only" for="loginPassword"><?= htmlspecialchars(t('login_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="password-field">
                            <input
                                type="password"
                                id="loginPassword"
                                name="password"
                                placeholder="<?= htmlspecialchars(t('login_password_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="current-password"
                                maxlength="255"
                                aria-describedby="loginPasswordError"
                                required>
                            <button
                                type="button"
                                id="loginPasswordToggle"
                                class="password-toggle"
                                aria-label="<?= htmlspecialchars(t('login_password_show'), ENT_QUOTES, 'UTF-8') ?>"
                                title="<?= htmlspecialchars(t('login_password_show'), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(t('login_password_show_short'), ENT_QUOTES, 'UTF-8') ?>
                            </button>
                        </div>
                        <div id="loginPasswordError" class="field-error d-none" aria-live="polite"></div>
                    </div>

                    <div class="login-inline-action">
                        <a
                            href="recuperar-password.php"
                            id="loginForgotPassword"
                            class="login-link-button"
                            data-inline-reset="true">
                            <?= htmlspecialchars(t('login_forgot_password'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </div>

                    <button type="submit" id="loginSendCode" class="login-primary-button">
                        <span class="btn-label"><?= htmlspecialchars(t('login_send_code_button'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="login-spinner d-none" aria-hidden="true"></span>
                    </button>

                    <p class="login-security-note"><?= htmlspecialchars(t('login_security_note'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <!-- PASO 2: SEGUNDO FACTOR (CÓDIGO POR CORREO) -->
                <div id="loginStepCode" class="d-none">
                    <h2><?= htmlspecialchars(t('login_step2_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="login-code-description">
                        <?= htmlspecialchars(t('login_step2_subtitle'), ENT_QUOTES, 'UTF-8') ?>
                        <strong id="loginStepCodeEmail"></strong>
                    </p>

                    <div id="loginDisplayedCode" class="login-displayed-code d-none" role="status" aria-live="polite">
                        <span class="login-displayed-code-label"><?= htmlspecialchars(t('login_demo_code_label'), ENT_QUOTES, 'UTF-8') ?></span>
                        <strong id="loginDisplayedCodeValue" class="login-displayed-code-value"></strong>
                        <small id="loginDisplayedCodeHelp" class="login-displayed-code-help"><?= htmlspecialchars(t('login_demo_code_help'), ENT_QUOTES, 'UTF-8') ?></small>
                    </div>

                    <div class="login-field">
                        <label class="sr-only" for="loginCode"><?= htmlspecialchars(t('login_code_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input
                            type="text"
                            id="loginCode"
                            name="code"
                            class="login-code-input"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="6"
                            placeholder="<?= htmlspecialchars(t('login_code_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                            aria-describedby="loginCodeError"
                            autocomplete="one-time-code">
                        <div id="loginCodeError" class="field-error d-none" aria-live="polite"></div>
                    </div>

                    <button type="submit" id="loginVerifyCode" class="login-primary-button">
                        <span class="btn-label"><?= htmlspecialchars(t('login_verify_button'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="login-spinner d-none" aria-hidden="true"></span>
                    </button>

                    <div class="login-secondary-actions">
                        <button type="button" id="loginResendCode" class="login-link-button">
                            <?= htmlspecialchars(t('login_resend_code'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                        <button type="button" id="loginChangeEmail" class="login-link-button">
                            <?= htmlspecialchars(t('login_change_email'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </div>
                </div>

                <!-- RECUPERACIÓN / ACTIVACIÓN DE CONTRASEÑA -->
                <div id="loginStepReset" class="d-none">
                    <h2><?= htmlspecialchars(t('login_reset_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="login-code-description">
                        <?= htmlspecialchars(t('login_reset_subtitle'), ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <div class="login-field">
                        <label class="sr-only" for="loginResetEmail"><?= htmlspecialchars(t('login_email_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input
                            type="email"
                            id="loginResetEmail"
                            name="reset_email"
                            placeholder="<?= htmlspecialchars(t('login_email_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="email"
                            maxlength="50"
                            aria-describedby="loginResetEmailError"
                            required>
                        <div id="loginResetEmailError" class="field-error d-none" aria-live="polite"></div>
                    </div>

                    <button type="submit" id="loginResetSend" class="login-primary-button">
                        <span class="btn-label"><?= htmlspecialchars(t('login_reset_send_button'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="login-spinner d-none" aria-hidden="true"></span>
                    </button>

                    <!--
                        En local no mostramos una URL técnica. Cuando el backend
                        devuelve el token de desarrollo, auth.js despliega estos
                        campos directamente y permite terminar el proceso aquí.
                    -->
                    <div id="loginResetLocalPassword" class="login-reset-local d-none">
                        <div class="login-reset-separator" aria-hidden="true"></div>

                        <h3 class="login-reset-local-title">
                            <?= htmlspecialchars(t('password_page_reset_title'), ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p class="login-code-description login-reset-local-description">
                            <?= htmlspecialchars(t('password_page_intro'), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="login-field">
                            <label class="sr-only" for="loginResetPassword"><?= htmlspecialchars(t('password_new_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <div class="password-field">
                                <input
                                    type="password"
                                    id="loginResetPassword"
                                    name="reset_password"
                                    minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                    maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                                    autocomplete="new-password"
                                    placeholder="<?= htmlspecialchars(t('password_new_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-reset-password-toggle="loginResetPassword"
                                    aria-label="<?= htmlspecialchars(t('login_password_show'), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(t('login_password_show_short'), ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </div>
                            <div id="loginResetPasswordError" class="field-error d-none" aria-live="polite"></div>
                        </div>

                        <div class="login-field">
                            <label class="sr-only" for="loginResetPasswordConfirm"><?= htmlspecialchars(t('password_confirm_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <div class="password-field">
                                <input
                                    type="password"
                                    id="loginResetPasswordConfirm"
                                    name="reset_password_confirm"
                                    minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                    maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                                    autocomplete="new-password"
                                    placeholder="<?= htmlspecialchars(t('password_confirm_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-reset-password-toggle="loginResetPasswordConfirm"
                                    aria-label="<?= htmlspecialchars(t('login_password_show'), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(t('login_password_show_short'), ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </div>
                            <div id="loginResetPasswordConfirmError" class="field-error d-none" aria-live="polite"></div>
                        </div>

                        <p class="login-security-note password-requirements">
                            <?= htmlspecialchars(t('password_requirements'), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <button type="submit" id="loginResetSave" class="login-primary-button">
                            <span class="btn-label"><?= htmlspecialchars(t('password_save_button'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="login-spinner d-none" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="login-secondary-actions login-secondary-actions--center">
                        <button type="button" id="loginResetBack" class="login-link-button">
                            <?= htmlspecialchars(t('login_back_to_login'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <div class="login-build" aria-hidden="true">
        SCT · <?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>
    </div>

    <script src="js/auth.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
