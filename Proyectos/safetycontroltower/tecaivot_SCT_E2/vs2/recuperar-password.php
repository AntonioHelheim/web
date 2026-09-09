<?php
/**
 * recuperar-password.php
 * Fallback de recuperación sin JavaScript.
 *
 * El index abre normalmente la recuperación en línea. Este archivo existe
 * para que "¿Olvidaste tu contraseña?" siga funcionando aunque auth.js no
 * cargue, esté cacheado o falle por cualquier motivo.
 */
require __DIR__ . '/session_bootstrap.php';
aplicarCabecerasSeguridad();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/passwords.php';
require __DIR__ . '/i18n.php';

if (!empty($_SESSION['logged_in'])) {
    header('Location: bienvenida.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$submitted = false;
$localToken = '';
$localPurpose = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    if (
        $csrfToken === '' ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        $error = 'Tu sesión expiró. Recarga la página e intenta nuevamente.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || passwordsStringLength($email) > 50) {
        $error = 'Ingresa un correo electrónico válido.';
    } else {
        try {
            if (passwordsRecentRequestsByIp($pdo, $ip) >= PASSWORD_RESET_MAX_PER_IP) {
                $error = 'Demasiadas solicitudes desde esta conexión. Intenta nuevamente más tarde.';
            } else {
                $stmt = $pdo->prepare(
                    'SELECT id_users
                     FROM users
                     WHERE id_users = :id_users
                       AND state = 1
                     LIMIT 1'
                );
                $stmt->execute(['id_users' => $email]);
                $user = $stmt->fetch();

                if ($user) {
                    $idUsers = (string) $user['id_users'];

                    if (passwordsRecentRequestsByUser($pdo, $idUsers) < PASSWORD_RESET_MAX_PER_USER) {
                        $credential = passwordsEnsureCredentialRecord($pdo, $idUsers);
                        $status = passwordsNormalizeCredentialStatus($credential['credential_status'] ?? null);
                        $purpose = passwordsPurposeForCredentialStatus($status);
                        $tokenData = passwordsCreateToken(
                            $pdo,
                            $idUsers,
                            $ip,
                            passwordsTtlForPurpose($purpose),
                            $purpose
                        );

                        if (passwordsIsLocal()) {
                            // En local continuamos en la misma pantalla: no se
                            // presenta ninguna URL técnica al usuario.
                            $localToken = $tokenData['token'];
                            $localPurpose = $purpose;
                        } elseif (!passwordsSendLinkEmail($idUsers, $tokenData['url'], $purpose)) {
                            error_log('recuperar-password.php: mail() no pudo aceptar el mensaje para entrega.');
                        }
                    }
                }

                // Respuesta idéntica exista o no la cuenta.
                $submitted = true;
                if ($localToken === '') {
                    $email = '';
                }
            }
        } catch (Throwable $e) {
            error_log('recuperar-password.php: ' . $e->getMessage());
            $error = 'No fue posible procesar la solicitud. Intenta nuevamente.';
        }
    }
}

$langActual = idiomaActual();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langActual, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#002259">
    <title><?= htmlspecialchars(t('login_reset_title'), ENT_QUOTES, 'UTF-8') ?> — Safety Control Tower</title>

    <link rel="icon" href="./images/logos/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/login.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="sct-login-page">
    <div class="login-language">
        <label class="sr-only" for="recoveryLanguage"><?= htmlspecialchars(t('login_language_label'), ENT_QUOTES, 'UTF-8') ?></label>
        <select id="recoveryLanguage" aria-label="<?= htmlspecialchars(t('login_language_label'), ENT_QUOTES, 'UTF-8') ?>" onchange="if (/^(es|en|pt|fr|zh)$/.test(this.value)) window.location.href='?lang='+encodeURIComponent(this.value)">
            <?php foreach (IDIOMAS_DISPONIBLES as $codigoIdioma): ?>
                <option value="<?= htmlspecialchars($codigoIdioma, ENT_QUOTES, 'UTF-8') ?>" <?= $codigoIdioma === $langActual ? 'selected' : '' ?>>
                    <?= htmlspecialchars(strtoupper($codigoIdioma), ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <main class="login-stage" aria-labelledby="recoveryHeading">
        <section class="login-shell">
            <div class="login-brand" aria-label="Safety Control Tower">
                <img src="./images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                <div class="login-brand-name">SAFETY CONTROL TOWER</div>
            </div>

            <h1 id="recoveryHeading" class="password-reset-title">
                <?= htmlspecialchars(t('login_reset_title'), ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <?php if ($error !== ''): ?>
                <div class="login-alert login-alert--error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php elseif ($submitted && $localToken === ''): ?>
                <div class="login-alert login-alert--success" role="status">
                    <?= htmlspecialchars(t('login_reset_confirmation'), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($localToken !== ''): ?>
                <div class="login-alert login-alert--success" role="status">
                    <?= htmlspecialchars(t('login_reset_local_ready'), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <p class="login-code-description">
                <?= htmlspecialchars(t('login_reset_subtitle'), ENT_QUOTES, 'UTF-8') ?>
            </p>

            <form method="post" action="recuperar-password.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="login-field">
                    <label class="sr-only" for="recoveryEmail"><?= htmlspecialchars(t('login_email_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input
                        type="email"
                        id="recoveryEmail"
                        name="email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="<?= htmlspecialchars(t('login_email_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="email"
                        maxlength="50"
                        <?= $localToken !== '' ? 'readonly' : '' ?>
                        required>
                </div>

                <?php if ($localToken === ''): ?>
                    <button type="submit" class="login-primary-button">
                        <?= htmlspecialchars(t('login_reset_send_button'), ENT_QUOTES, 'UTF-8') ?>
                    </button>
                <?php endif; ?>
            </form>

            <?php if ($localToken !== ''): ?>
                <div class="login-reset-local">
                    <div class="login-reset-separator" aria-hidden="true"></div>
                    <h2 class="login-reset-local-title">
                        <?= htmlspecialchars(t('password_page_reset_title'), ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <p class="login-code-description login-reset-local-description">
                        <?= htmlspecialchars(t('password_page_intro'), ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <form method="post" action="restablecer-password.php" novalidate>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($localToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="login-field">
                            <div class="password-field">
                                <input
                                    type="password"
                                    id="localRecoveryPassword"
                                    name="password"
                                    minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                    maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                                    autocomplete="new-password"
                                    placeholder="<?= htmlspecialchars(t('password_new_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <button type="button" class="password-toggle" data-local-toggle="localRecoveryPassword" aria-label="<?= htmlspecialchars(t('login_password_show'), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(t('login_password_show_short'), ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </div>
                        </div>

                        <div class="login-field">
                            <div class="password-field">
                                <input
                                    type="password"
                                    id="localRecoveryPasswordConfirm"
                                    name="password_confirm"
                                    minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                    maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                                    autocomplete="new-password"
                                    placeholder="<?= htmlspecialchars(t('password_confirm_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <button type="button" class="password-toggle" data-local-toggle="localRecoveryPasswordConfirm" aria-label="<?= htmlspecialchars(t('login_password_show'), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(t('login_password_show_short'), ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </div>
                        </div>

                        <p class="login-security-note password-requirements">
                            <?= htmlspecialchars(t('password_requirements'), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <button type="submit" class="login-primary-button">
                            <?= htmlspecialchars(t('password_save_button'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="login-secondary-actions login-secondary-actions--center">
                <a href="index.php" class="login-link-button">
                    <?= htmlspecialchars(t('login_back_to_login'), ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>
        </section>
    </main>
    <script>
        document.querySelectorAll('[data-local-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.getAttribute('data-local-toggle'));
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    </script>
</body>
</html>
